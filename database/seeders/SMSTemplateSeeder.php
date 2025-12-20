<?php

namespace Database\Seeders;

use App\Models\SMSTemplate;
use Illuminate\Database\Seeder;

/**
 * SMS Templates Seeder
 * 
 * Seed SMS templates untuk berbagai notifikasi
 */
class SMSTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'type' => 'consultation_booked',
                'template' => 'Hi Dr. {doctor_name}! New consultation booking from {patient_name} on {consultation_date}. Confirm: {link}',
                'description' => 'Notifikasi ketika pasien membooking konsultasi baru',
                'active' => true,
            ],
            [
                'type' => 'consultation_reminder',
                'template' => 'Reminder: Your consultation with Dr. {doctor_name} is on {consultation_date}. See you soon!',
                'description' => 'Pengingat konsultasi untuk pasien',
                'active' => true,
            ],
            [
                'type' => 'consultation_confirmed',
                'template' => 'Your consultation with Dr. {doctor_name} on {consultation_date} has been confirmed.',
                'description' => 'Konfirmasi konsultasi',
                'active' => true,
            ],
            [
                'type' => 'consultation_cancelled',
                'template' => 'Consultation with Dr. {doctor_name} on {consultation_date} has been cancelled. Reason: {reason}',
                'description' => 'Notifikasi pembatalan konsultasi',
                'active' => true,
            ],
            [
                'type' => 'prescription_ready',
                'template' => 'Your prescription from Dr. {doctor_name} is ready. Download now: {link}',
                'description' => 'Notifikasi resep siap',
                'active' => true,
            ],
            [
                'type' => 'payment_confirmed',
                'template' => 'Payment of {amount} for consultation confirmed. Invoice: {invoice_id}',
                'description' => 'Konfirmasi pembayaran',
                'active' => true,
            ],
            [
                'type' => 'payment_pending',
                'template' => 'Your consultation fee of {amount} is pending payment. Pay now: {link}',
                'description' => 'Pengingat pembayaran',
                'active' => true,
            ],
            [
                'type' => 'appointment_approaching',
                'template' => 'Your appointment with Dr. {doctor_name} is in {minutes} minutes',
                'description' => 'Notifikasi imminence appointment',
                'active' => true,
            ],
            [
                'type' => 'doctor_verified',
                'template' => 'Congratulations! Your doctor verification has been approved. You can now accept consultations.',
                'description' => 'Notifikasi verifikasi dokter diterima',
                'active' => true,
            ],
            [
                'type' => 'verification_rejected',
                'template' => 'Your doctor verification was rejected. Reason: {reason} Please resubmit: {link}',
                'description' => 'Notifikasi verifikasi dokter ditolak',
                'active' => true,
            ],
            [
                'type' => 'message_received',
                'template' => 'New message from Dr. {doctor_name}: {preview}',
                'description' => 'Notifikasi pesan baru',
                'active' => true,
            ],
            [
                'type' => 'video_consultation_starting',
                'template' => 'Video consultation with Dr. {doctor_name} is starting now. Join: {link}',
                'description' => 'Notifikasi konsultasi video dimulai',
                'active' => true,
            ],
        ];

        foreach ($templates as $template) {
            SMSTemplate::updateOrCreate(
                ['type' => $template['type']],
                $template
            );
        }

        $this->command->info('SMS templates seeded successfully');
    }
}
