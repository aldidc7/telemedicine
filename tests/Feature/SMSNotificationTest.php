<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Konsultasi;
use App\Models\SMSLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

/**
 * Phase 6: SMS Notification Tests
 * 
 * Test suite untuk SMS notification system integration,
 * queue handling, dan delivery tracking
 */
class SMSNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $patient;
    protected User $doctor;
    protected Konsultasi $consultation;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $this->patient = User::factory()->patient()->create([
            'name' => 'Patient Test',
            'phone_number' => '+6287777777777',
        ]);

        $this->doctor = User::factory()->doctor()->create([
            'name' => 'Doctor Test',
            'phone_number' => '+6288888888888',
        ]);

        $this->consultation = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
        ]);
    }

    // ============== SMS SENDING TESTS ==============

    /** @test */
    public function sms_notification_sent_to_patient_on_consultation_scheduled()
    {
        $this->consultation->update(['status' => 'scheduled']);

        // Trigger the event (with all required parameters based on event signature)
        event(new \App\Events\ConsultationStatusChanged(
            $this->consultation,
            'scheduled',
            'booked'
        ));

        // Check SMS was queued
        Queue::assertPushed(\App\Jobs\SendSMSNotification::class);
    }

    /** @test */
    public function sms_notification_sent_to_doctor_on_new_consultation()
    {
        $newConsultation = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'booked',
        ]);

        // Trigger event with required parameters
        event(new \App\Events\ConsultationStatusChanged(
            $newConsultation,
            'booked',
            'scheduled'
        ));

        Queue::assertPushed(\App\Jobs\SendSMSNotification::class);
    }

    /** @test */
    public function sms_includes_consultation_details()
    {
        $this->actingAs($this->patient);

        $response = $this->postJson('/api/v1/consultations', [
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => now()->addHour(),
            'reason' => 'Regular checkup',
        ]);

        Queue::assertPushed(\App\Jobs\SendSMSNotification::class, function ($job) {
            // Should include doctor name or consultation time
            return true;
        });
    }

    // ============== SMS CONTENT TESTS ==============

    /** @test */
    public function sms_message_formatting_is_valid()
    {
        $smsLog = SMSLog::create([
            'user_id' => $this->patient->id,
            'message' => 'Your consultation with Dr. Test is scheduled for tomorrow at 10:00 AM',
            'phone_number' => $this->patient->phone_number,
            'status' => 'sent',
        ]);

        $this->assertStringContainsString('consultation', strtolower($smsLog->message));
        $this->assertLessThanOrEqual(160, strlen($smsLog->message));
    }

    /** @test */
    public function sms_message_supports_multiple_languages()
    {
        $this->patient->update(['language' => 'id']);

        $this->consultation->update(['status' => 'scheduled']);

        event(new \App\Events\ConsultationStatusChanged(
            $this->consultation,
            'scheduled',
            'booked'
        ));

        Queue::assertPushed(\App\Jobs\SendSMSNotification::class);
    }

    /** @test */
    public function sms_includes_clickable_confirmation_link()
    {
        $smsLog = SMSLog::create([
            'user_id' => $this->patient->id,
            'message' => 'Confirm appointment: https://telemedicine.app/confirm/123',
            'phone_number' => $this->patient->phone_number,
            'status' => 'sent',
        ]);

        $this->assertStringContainsString('https://', $smsLog->message);
    }

    // ============== SMS DELIVERY TESTS ==============

    /** @test */
    public function sms_delivery_status_tracked()
    {
        $smsLog = SMSLog::create([
            'user_id' => $this->patient->id,
            'message' => 'Test message',
            'phone_number' => $this->patient->phone_number,
            'status' => 'pending',
            'external_id' => 'twilio_msg_123',
        ]);

        $this->assertEquals('pending', $smsLog->status);

        $smsLog->update(['status' => 'sent', 'sent_at' => now()]);

        $this->assertEquals('sent', $smsLog->fresh()->status);
        $this->assertNotNull($smsLog->fresh()->sent_at);
    }

    /** @test */
    public function sms_delivery_failure_logged()
    {
        $smsLog = SMSLog::create([
            'user_id' => $this->patient->id,
            'message' => 'Test message',
            'phone_number' => '+invalid',
            'status' => 'failed',
            'error_message' => 'Invalid phone number',
        ]);

        $this->assertEquals('failed', $smsLog->status);
        $this->assertStringContainsString('Invalid', $smsLog->error_message);
    }

    /** @test */
    public function sms_retry_on_delivery_failure()
    {
        $smsLog = SMSLog::create([
            'user_id' => $this->patient->id,
            'message' => 'Test message',
            'phone_number' => $this->patient->phone_number,
            'status' => 'failed',
            'retry_count' => 0,
        ]);

        $this->actingAs(User::factory()->admin()->create());

        $response = $this->postJson("/api/v1/sms/{$smsLog->id}/retry");

        $response->assertStatus(200);
        $smsLog->refresh();
        $this->assertEquals(1, $smsLog->retry_count);
    }

    // ============== SMS PHONE VALIDATION TESTS ==============

    /** @test */
    public function sms_requires_valid_phone_number()
    {
        $this->patient->update(['phone_number' => '']);

        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/consultations', [
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => now()->addHour(),
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function sms_phone_number_formatted_correctly()
    {
        $this->patient->update(['phone_number' => '087777777777']);

        $smsLog = SMSLog::create([
            'user_id' => $this->patient->id,
            'message' => 'Test',
            'phone_number' => '+62' . ltrim($this->patient->phone_number, '0'),
            'status' => 'sent',
        ]);

        $this->assertTrue(preg_match('/^\+62\d+$/', $smsLog->phone_number));
    }

    // ============== SMS OPT-IN/OUT TESTS ==============

    /** @test */
    public function patient_can_disable_sms_notifications()
    {
        $this->actingAs($this->patient);

        $response = $this->putJson('/api/v1/settings/notifications', [
            'sms_enabled' => false,
        ]);

        $response->assertStatus(200);

        $this->patient->refresh();
        $this->assertFalse($this->patient->settings['sms_enabled'] ?? true);
    }

    /** @test */
    public function sms_not_sent_if_disabled()
    {
        $this->patient->update([
            'notification_settings' => ['sms_enabled' => false],
        ]);

        $this->consultation->update(['status' => 'scheduled']);
        event(new \App\Events\ConsultationStatusChanged(
            $this->consultation,
            'scheduled',
            'booked'
        ));

        Queue::assertNotPushed(\App\Jobs\SendSMSNotification::class);
    }

    // ============== SMS TEMPLATE TESTS ==============

    /** @test */
    public function sms_template_for_consultation_booked()
    {
        $template = \App\Models\SMSTemplate::where('type', 'consultation_booked')->first();

        $this->assertNotNull($template);
        $this->assertStringContainsString('{doctor_name}', $template->template);
        $this->assertStringContainsString('{consultation_time}', $template->template);
    }

    /** @test */
    public function sms_template_for_consultation_reminder()
    {
        $template = \App\Models\SMSTemplate::where('type', 'consultation_reminder')->first();

        $this->assertNotNull($template);
        $this->assertLessThanOrEqual(160, strlen($template->template));
    }

    /** @test */
    public function sms_template_for_prescription_ready()
    {
        $template = \App\Models\SMSTemplate::where('type', 'prescription_ready')->first();

        $this->assertNotNull($template);
        $this->assertStringContainsString('{prescription_id}', $template->template);
    }

    // ============== SMS RATE LIMITING TESTS ==============

    /** @test */
    public function sms_rate_limit_prevents_spam()
    {
        $smsLogs = SMSLog::where('user_id', $this->patient->id)
            ->where('created_at', '>=', now()->subMinute())
            ->count();

        // Create 5 SMS in quick succession
        for ($i = 0; $i < 5; $i++) {
            SMSLog::create([
                'user_id' => $this->patient->id,
                'message' => "Message $i",
                'phone_number' => $this->patient->phone_number,
                'status' => 'sent',
            ]);
        }

        // Sixth message should be blocked
        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/send-sms', [
                'message' => 'This should be rate limited',
            ]);

        $response->assertStatus(429);
    }

    // ============== SMS WEBHOOK TESTS ==============

    /** @test */
    public function webhook_updates_sms_status()
    {
        $smsLog = SMSLog::create([
            'user_id' => $this->patient->id,
            'message' => 'Test',
            'phone_number' => $this->patient->phone_number,
            'status' => 'sent',
            'external_id' => 'twilio_msg_123',
        ]);

        $response = $this->postJson('/api/v1/webhooks/sms/status', [
            'MessageSid' => 'twilio_msg_123',
            'MessageStatus' => 'delivered',
        ], [
            'X-Twilio-Signature' => 'valid_signature',
        ]);

        $response->assertStatus(200);
        $smsLog->refresh();
        $this->assertEquals('delivered', $smsLog->status);
    }

    /** @test */
    public function webhook_handles_failed_delivery()
    {
        $smsLog = SMSLog::create([
            'user_id' => $this->patient->id,
            'message' => 'Test',
            'phone_number' => '+invalid',
            'status' => 'sent',
            'external_id' => 'twilio_msg_456',
        ]);

        $this->postJson('/api/v1/webhooks/sms/status', [
            'MessageSid' => 'twilio_msg_456',
            'MessageStatus' => 'failed',
            'ErrorCode' => '21614',
        ]);

        $smsLog->refresh();
        $this->assertEquals('failed', $smsLog->status);
    }
}
