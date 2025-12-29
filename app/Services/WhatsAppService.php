<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\TwiML\MessagingResponse;

/**
 * WhatsApp Service
 * 
 * Menangani pengiriman OTP via WhatsApp menggunakan Twilio
 */
class WhatsAppService
{
    private Client $twilioClient;

    public function __construct()
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');

        if (!$accountSid || !$authToken) {
            Log::warning('Twilio credentials not configured properly');
        }

        $this->twilioClient = new Client($accountSid, $authToken);
    }

    /**
     * Kirim OTP via WhatsApp
     *
     * @param string $phoneNumber Nomor tujuan (format: +62xxx)
     * @param string $otp OTP code (6 digit)
     * @return bool
     */
    public function sendOtp(string $phoneNumber, string $otp): bool
    {
        try {
            // Validate phone number format
            if (!$this->isValidPhoneNumber($phoneNumber)) {
                Log::error('Invalid phone number format for WhatsApp OTP', [
                    'phone' => $phoneNumber,
                    'otp' => $otp
                ]);
                return false;
            }

            $message = "Kode reset password Anda: *{$otp}*\n\n" .
                "Kode berlaku selama 30 menit.\n\n" .
                "Jangan bagikan kode ini dengan siapa pun.";

            // Send message via Twilio WhatsApp
            $response = $this->twilioClient->messages->create(
                "whatsapp:{$phoneNumber}",
                [
                    "from" => "whatsapp:" . config('services.twilio.whatsapp_number'),
                    "body" => $message
                ]
            );

            Log::info('WhatsApp OTP sent successfully', [
                'phone' => $phoneNumber,
                'message_sid' => $response->sid,
                'status' => $response->status,
                'timestamp' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp OTP via Twilio', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return false;
        }
    }

    /**
     * Validate phone number format
     *
     * @param string $phoneNumber
     * @return bool
     */
    private function isValidPhoneNumber(string $phoneNumber): bool
    {
        // Check if starts with +
        if (strpos($phoneNumber, '+') !== 0) {
            return false;
        }

        // Remove + and check if all digits
        $digits = preg_replace('/[^\d]/', '', $phoneNumber);

        // Must be 9-15 digits (ITU-T E.164 standard)
        return strlen($digits) >= 9 && strlen($digits) <= 15;
    }

    /**
     * Check if Twilio is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty(config('services.twilio.account_sid')) &&
            !empty(config('services.twilio.auth_token')) &&
            !empty(config('services.twilio.whatsapp_number'));
    }

    /**
     * Get Twilio status (for debugging)
     *
     * @return array
     */
    public function getStatus(): array
    {
        $configured = $this->isConfigured();

        return [
            'configured' => $configured,
            'account_sid' => $configured ? substr(config('services.twilio.account_sid'), 0, 5) . '...' : null,
            'whatsapp_number' => $configured ? config('services.twilio.whatsapp_number') : null,
            'timestamp' => now()
        ];
    }
}
