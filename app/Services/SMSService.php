<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * SMS Service
 * 
 * Handle sending notifications via SMS (integrates with Twilio or similar)
 */
class SMSService
{
    /**
     * Send appointment reminder SMS
     */
    public function sendAppointmentReminder($appointment): void
    {
        try {
            $patient = $appointment->patient;
            $doctor = $appointment->doctor;
            $scheduledDate = $appointment->scheduled_at->format('d/m/Y H:i');

            $message = "Pengingat: Janji temu Anda dengan Dr. {$doctor->nama} pada {$scheduledDate}. " .
                      "Pastikan hadir tepat waktu. Hubungi kami jika perlu membatalkan.";

            $this->sendSMS($patient->nomor_telepon, $message);
        } catch (\Exception $e) {
            Log::error("Failed to send appointment reminder SMS: " . $e->getMessage());
        }
    }

    /**
     * Send payment confirmation SMS
     */
    public function sendPaymentConfirmation($payment, User $user): void
    {
        try {
            $amount = number_format($payment->amount, 0, ',', '.');
            $transactionId = $payment->transaction_id;

            $message = "Pembayaran Rp {$amount} berhasil diproses. ID Transaksi: {$transactionId}. " .
                      "Terima kasih telah menggunakan layanan kami.";

            $this->sendSMS($user->nomor_telepon, $message);
        } catch (\Exception $e) {
            Log::error("Failed to send payment confirmation SMS: " . $e->getMessage());
        }
    }

    /**
     * Send appointment confirmation SMS
     */
    public function sendAppointmentConfirmation($appointment): void
    {
        try {
            $patient = $appointment->patient;
            $doctor = $appointment->doctor;
            $scheduledDate = $appointment->scheduled_at->format('d/m/Y H:i');

            $message = "Janji temu Anda dengan Dr. {$doctor->nama} telah dikonfirmasi untuk {$scheduledDate}. " .
                      "Silakan datang 15 menit lebih awal.";

            $this->sendSMS($patient->nomor_telepon, $message);
        } catch (\Exception $e) {
            Log::error("Failed to send appointment confirmation SMS: " . $e->getMessage());
        }
    }

    /**
     * Send appointment cancelled SMS
     */
    public function sendAppointmentCancelled($appointment, $reason = null): void
    {
        try {
            $patient = $appointment->patient;
            $doctor = $appointment->doctor;

            $message = "Janji temu Anda dengan Dr. {$doctor->nama} telah dibatalkan.";
            if ($reason) {
                $message .= " Alasan: {$reason}";
            }
            $message .= " Hubungi kami untuk informasi lebih lanjut.";

            $this->sendSMS($patient->nomor_telepon, $message);
        } catch (\Exception $e) {
            Log::error("Failed to send appointment cancelled SMS: " . $e->getMessage());
        }
    }

    /**
     * Send verification code SMS
     */
    public function sendVerificationCode(User $user, string $code): void
    {
        try {
            $message = "Kode verifikasi Anda: {$code}. Jangan bagikan kode ini kepada siapa pun. " .
                      "Kode berlaku selama 10 menit.";

            $this->sendSMS($user->nomor_telepon, $message);
        } catch (\Exception $e) {
            Log::error("Failed to send verification code SMS: " . $e->getMessage());
        }
    }

    /**
     * Send consultation started SMS
     */
    public function sendConsultationStarted($consultation): void
    {
        try {
            $patient = $consultation->patient;
            $doctor = $consultation->doctor;

            $message = "Konsultasi Anda dengan Dr. {$doctor->nama} telah dimulai. " .
                      "Masuk ke aplikasi kami untuk memulai video call.";

            $this->sendSMS($patient->nomor_telepon, $message);
        } catch (\Exception $e) {
            Log::error("Failed to send consultation started SMS: " . $e->getMessage());
        }
    }

    /**
     * Send emergency alert SMS
     */
    public function sendEmergencyAlert(User $user, string $message): void
    {
        try {
            // Add urgency indicator
            $smsMessage = "⚠️ ALERT: {$message}";
            $this->sendSMS($user->nomor_telepon, $smsMessage);
        } catch (\Exception $e) {
            Log::error("Failed to send emergency alert SMS: " . $e->getMessage());
        }
    }

    /**
     * Generic SMS sender
     * TODO: Integrate with Twilio or similar service
     */
    private function sendSMS(string $phoneNumber, string $message): void
    {
        try {
            // Format phone number if needed
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // TODO: Implement actual SMS sending via Twilio
            // Example:
            // $client = new Twilio\Rest\Client(config('services.twilio.account_sid'), config('services.twilio.auth_token'));
            // $client->messages->create($phoneNumber, ['from' => config('services.twilio.from'), 'body' => $message]);

            Log::info("SMS prepared for {$phoneNumber}: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to {$phoneNumber}: " . $e->getMessage());
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If starts with 0, replace with +62
        if (str_starts_with($cleaned, '0')) {
            return '+62' . substr($cleaned, 1);
        }

        // If doesn't have country code, add +62
        if (!str_starts_with($cleaned, '62')) {
            return '+62' . $cleaned;
        }

        return '+' . $cleaned;
    }
}
