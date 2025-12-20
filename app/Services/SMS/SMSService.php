<?php

namespace App\Services\SMS;

use App\Models\SMSLog;
use App\Models\SMSTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS Notification Service
 * 
 * Handles SMS delivery via Twilio
 * - Send SMS with template
 * - Track delivery status
 * - Webhook handling for delivery status
 */
class SMSService
{
    /**
     * Twilio API endpoint
     */
    private $twilioUrl;

    /**
     * Account SID
     */
    private $accountSid;

    /**
     * Auth Token
     */
    private $authToken;

    /**
     * From phone number
     */
    private $fromNumber;

    public function __construct()
    {
        $this->twilioUrl = 'https://api.twilio.com/2010-04-01';
        $this->accountSid = config('services.twilio.account_sid');
        $this->authToken = config('services.twilio.auth_token');
        $this->fromNumber = config('services.twilio.from_number');
    }

    /**
     * Send SMS notification
     */
    public function send($phoneNumber, $templateType, $templateData = []): ?SMSLog
    {
        try {
            // Validate phone number
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            if (!$this->isValidPhoneNumber($phoneNumber)) {
                throw new \Exception('Invalid phone number format');
            }

            // Get template
            $template = SMSTemplate::where('type', $templateType)->firstOrFail();

            // Build message
            $message = $this->buildMessage($template->template, $templateData);

            // Validate message length
            if (strlen($message) > 160) {
                $message = substr($message, 0, 157) . '...';
            }

            // Send via Twilio
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->post("{$this->twilioUrl}/Accounts/{$this->accountSid}/Messages.json", [
                    'From' => $this->fromNumber,
                    'To' => $phoneNumber,
                    'Body' => $message,
                ])
                ->json();

            if (!isset($response['sid'])) {
                throw new \Exception('Failed to send SMS: ' . ($response['message'] ?? 'Unknown error'));
            }

            // Log SMS
            $smsLog = SMSLog::create([
                'user_id' => $templateData['user_id'] ?? null,
                'phone_number' => $phoneNumber,
                'message' => $message,
                'template_type' => $templateType,
                'status' => 'sent',
                'external_id' => $response['sid'],
                'sent_at' => now(),
            ]);

            Log::info("SMS sent successfully", [
                'sms_id' => $smsLog->id,
                'external_id' => $response['sid'],
                'phone' => $phoneNumber,
            ]);

            return $smsLog;
        } catch (\Exception $e) {
            Log::error("Failed to send SMS", [
                'phone' => $phoneNumber ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            // Log failed SMS
            if (isset($templateData['user_id'])) {
                SMSLog::create([
                    'user_id' => $templateData['user_id'],
                    'phone_number' => $phoneNumber ?? 'invalid',
                    'message' => $message ?? '',
                    'template_type' => $templateType,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            return null;
        }
    }

    /**
     * Handle delivery status webhook from Twilio
     */
    public function handleStatusCallback($data)
    {
        $externalId = $data['MessageSid'] ?? null;
        $status = $data['MessageStatus'] ?? null;

        if (!$externalId || !$status) {
            throw new \Exception('Invalid webhook data');
        }

        $smsLog = SMSLog::where('external_id', $externalId)->first();

        if (!$smsLog) {
            Log::warning("Webhook received for unknown SMS", ['external_id' => $externalId]);
            return;
        }

        // Map Twilio status to our status
        $statusMap = [
            'queued' => 'queued',
            'sending' => 'sending',
            'sent' => 'sent',
            'delivered' => 'delivered',
            'failed' => 'failed',
            'undelivered' => 'failed',
        ];

        $mappedStatus = $statusMap[$status] ?? $status;

        $smsLog->update([
            'status' => $mappedStatus,
            'delivered_at' => in_array($mappedStatus, ['delivered', 'sent']) ? now() : null,
            'error_message' => $data['ErrorCode'] ?? null,
        ]);

        Log::info("SMS status updated", [
            'sms_id' => $smsLog->id,
            'new_status' => $mappedStatus,
        ]);
    }

    /**
     * Retry failed SMS
     */
    public function retry(SMSLog $smsLog): ?SMSLog
    {
        if ($smsLog->status !== 'failed' || $smsLog->retry_count >= 3) {
            return null;
        }

        try {
            // Resend
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->post("{$this->twilioUrl}/Accounts/{$this->accountSid}/Messages.json", [
                    'From' => $this->fromNumber,
                    'To' => $smsLog->phone_number,
                    'Body' => $smsLog->message,
                ])
                ->json();

            if (isset($response['sid'])) {
                $smsLog->update([
                    'status' => 'sent',
                    'external_id' => $response['sid'],
                    'retry_count' => $smsLog->retry_count + 1,
                    'sent_at' => now(),
                ]);

                return $smsLog;
            }

            throw new \Exception('Failed to resend SMS');
        } catch (\Exception $e) {
            Log::error("SMS retry failed", [
                'sms_id' => $smsLog->id,
                'error' => $e->getMessage(),
            ]);

            $smsLog->increment('retry_count');
            return null;
        }
    }

    /**
     * Send SMS to multiple recipients
     */
    public function sendBulk($phoneNumbers, $templateType, $templateData = [])
    {
        $results = [];

        foreach ($phoneNumbers as $phone) {
            $data = array_merge($templateData, ['phone' => $phone]);
            $result = $this->send($phone, $templateType, $data);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Build message from template
     */
    private function buildMessage($template, $data = []): string
    {
        $message = $template;

        foreach ($data as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        return $message;
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // If starts with 0 (Indonesian), replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with 62, add it
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Validate phone number format
     */
    private function isValidPhoneNumber($phone): bool
    {
        // Must start with +
        if (!str_starts_with($phone, '+')) {
            return false;
        }

        // Must have 10-15 digits
        $digits = preg_replace('/\D/', '', $phone);
        return strlen($digits) >= 10 && strlen($digits) <= 15;
    }

    /**
     * Verify Twilio webhook signature
     */
    public static function verifyTwilioSignature($signature, $url, $params): bool
    {
        $authToken = config('services.twilio.auth_token');

        $data = '';
        foreach ($params as $key => $value) {
            $data .= $key . $value;
        }

        $hash = hash_hmac('sha1', $url . $data, $authToken, true);
        $computedSignature = base64_encode($hash);

        return hash_equals($signature, $computedSignature);
    }
}
