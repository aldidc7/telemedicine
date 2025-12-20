<?php

namespace App\Listeners;

use App\Events\ConsultationStatusChanged;
use App\Jobs\SendSMSNotification;

/**
 * Listener untuk event perubahan status konsultasi
 * Mengirim SMS notification ke dokter dan pasien
 */
class SendConsultationSMSNotification
{
    public function handle(ConsultationStatusChanged $event)
    {
        $consultation = $event->konsultasi;  // Use 'konsultasi' property

        // Determine SMS template based on new status
        $smsTemplate = $this->getTemplateForStatus($event->newStatus);
        
        if (!$smsTemplate) {
            return;
        }

        // Send SMS to patient
        if ($consultation->patient->phone_number) {
            SendSMSNotification::dispatch(
                $consultation->patient->id,
                $consultation->patient->phone_number,
                $smsTemplate,
                [
                    'doctor_name' => $consultation->doctor->name,
                    'patient_name' => $consultation->patient->name,
                ]
            );
        }

        // Send SMS to doctor
        if ($consultation->doctor->phone_number) {
            SendSMSNotification::dispatch(
                $consultation->doctor->id,
                $consultation->doctor->phone_number,
                $smsTemplate,
                [
                    'patient_name' => $consultation->patient->name,
                    'doctor_name' => $consultation->doctor->name,
                ]
            );
        }
    }

    private function getTemplateForStatus(string $status): ?string
    {
        $templates = [
            'booked' => 'consultation_booked',
            'confirmed' => 'consultation_confirmed',
            'in_progress' => 'consultation_starting',
            'completed' => 'consultation_completed',
            'cancelled' => 'consultation_cancelled',
        ];

        return $templates[$status] ?? null;
    }
}
