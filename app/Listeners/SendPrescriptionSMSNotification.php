<?php

namespace App\Listeners;

use App\Events\PrescriptionCreated;
use App\Jobs\SendSMSNotification;

/**
 * Listener untuk event pembuatan resep
 * Mengirim SMS notification ke pasien
 */
class SendPrescriptionSMSNotification
{
    public function handle(PrescriptionCreated $event)
    {
        $prescription = $event->prescription;

        // Send SMS to patient when prescription is ready
        if ($prescription->patient->phone_number) {
            SendSMSNotification::dispatch(
                $prescription->patient->id,
                $prescription->patient->phone_number,
                'prescription_ready',
                [
                    'doctor_name' => $prescription->doctor->name,
                    'link' => route('prescriptions.show', $prescription->id),
                ]
            );
        }
    }
}
