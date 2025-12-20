<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\SMSLog;
use App\Services\SMS\SMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Send SMS Notification Job
 * 
 * Queue job untuk mengirim SMS notifications secara asynchronous
 */
class SendSMSNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $phoneNumber;
    protected $templateType;
    protected $templateData;
    protected $retryCount = 0;
    
    /**
     * Number of times the job may be attempted
     */
    public $tries = 3;
    
    /**
     * Number of seconds the job can run before timing out
     */
    public $timeout = 30;

    /**
     * Constructor
     */
    public function __construct(
        $userId,
        $phoneNumber,
        $templateType,
        array $templateData = []
    ) {
        $this->userId = $userId;
        $this->phoneNumber = $phoneNumber;
        $this->templateType = $templateType;
        $this->templateData = $templateData;
        
        // Set job configuration
        $this->queue = 'sms';
    }

    /**
     * Execute the job
     */
    public function handle(SMSService $smsService)
    {
        try {
            // Get user to check SMS notification preference
            $user = User::find($this->userId);
            
            if (!$user) {
                Log::warning("User not found for SMS notification", [
                    'user_id' => $this->userId,
                ]);
                return;
            }

            // Check if user has enabled SMS notifications
            if (!$this->isSMSEnabled($user)) {
                Log::info("SMS notification disabled for user", [
                    'user_id' => $user->id,
                ]);
                return;
            }

            // Validate phone number
            if (!$this->isValidPhoneNumber($this->phoneNumber)) {
                Log::error("Invalid phone number for SMS", [
                    'phone_number' => $this->phoneNumber,
                    'user_id' => $user->id,
                ]);
                
                $this->createSMSLog('failed', 'Invalid phone number format');
                return;
            }

            // Send SMS
            $result = $smsService->send(
                $this->phoneNumber,
                $this->templateType,
                $this->templateData,
                $this->userId
            );

            // Log successful send
            Log::info("SMS notification sent successfully", [
                'user_id' => $user->id,
                'template_type' => $this->templateType,
                'external_id' => $result['external_id'] ?? null,
            ]);

        } catch (\Exception $e) {
            // Log error
            Log::error("Failed to send SMS notification", [
                'user_id' => $this->userId,
                'template_type' => $this->templateType,
                'error' => $e->getMessage(),
                'retry_count' => $this->attempts(),
            ]);

            // Create failed SMS log
            $this->createSMSLog('failed', $e->getMessage());

            // Retry or fail
            if ($this->attempts() < $this->tries) {
                $this->release(60 * $this->attempts()); // Exponential backoff
            }
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::critical("SMS notification job permanently failed", [
            'user_id' => $this->userId,
            'template_type' => $this->templateType,
            'error' => $exception->getMessage(),
        ]);

        // Create failed SMS log
        $this->createSMSLog('failed', $exception->getMessage());
    }

    /**
     * Check if SMS is enabled for user
     */
    private function isSMSEnabled(User $user): bool
    {
        $settings = $user->notification_settings ?? [];
        
        // Default to enabled if not specified
        return $settings['sms_enabled'] ?? true;
    }

    /**
     * Validate phone number format
     */
    private function isValidPhoneNumber(string $phone): bool
    {
        // Check if phone starts with +62 and has digits
        return preg_match('/^\+62\d{9,}$/', $phone) === 1;
    }

    /**
     * Create SMS log entry
     */
    private function createSMSLog(string $status, ?string $errorMessage = null)
    {
        try {
            SMSLog::create([
                'user_id' => $this->userId,
                'phone_number' => $this->phoneNumber,
                'message' => $this->buildMessage(),
                'template_type' => $this->templateType,
                'status' => $status,
                'error_message' => $errorMessage,
                'retry_count' => 0,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create SMS log", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build message from template
     */
    private function buildMessage(): string
    {
        $messages = [
            'consultation_booked' => "Hi {doctor_name}! You have a new consultation booked with {patient_name} on {consultation_time}.",
            'consultation_reminder' => "Reminder: Your consultation with Dr. {doctor_name} is {consultation_time}. See you soon!",
            'consultation_confirmed' => "Your consultation with Dr. {doctor_name} on {consultation_time} has been confirmed.",
            'prescription_ready' => "Your prescription from Dr. {doctor_name} is ready. Download it now: {link}",
            'payment_confirmed' => "Payment of {amount} for consultation with Dr. {doctor_name} confirmed. Invoice: {invoice_id}",
        ];

        $template = $messages[$this->templateType] ?? "Notification from {app_name}";
        
        // Replace placeholders
        foreach ($this->templateData as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return substr($template, 0, 160); // SMS 160 character limit
    }
}
