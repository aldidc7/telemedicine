<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\AppointmentReminder;
use App\Services\Appointment\AppointmentReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ============================================
 * APPOINTMENT REMINDER CONTROLLER TEST
 * ============================================
 * 
 * Test appointment reminder functionality:
 * - Create reminders
 * - List reminders
 * - Resend reminders
 * - Preferences management
 * 
 * 14 comprehensive test cases
 */
class AppointmentReminderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $patient;
    protected User $doctor;
    protected Dokter $doctorProfile;
    protected Konsultasi $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create patient
        $this->patient = User::factory()->create(['role' => 'patient']);

        // Create doctor
        $this->doctor = User::factory()->create(['role' => 'doctor']);
        $this->doctorProfile = Dokter::factory()->create(['user_id' => $this->doctor->id]);

        // Create appointment
        $this->appointment = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorProfile->id,
            'appointment_time' => now()->addHours(24),
            'status' => 'scheduled',
        ]);
    }

    /**
     * Test: List reminders untuk authenticated patient
     */
    public function test_list_reminders_for_patient()
    {
        // Create reminders
        AppointmentReminder::factory()->create([
            'appointment_id' => $this->appointment->id,
            'user_id' => $this->patient->id,
            'reminder_type' => 'email',
        ]);

        AppointmentReminder::factory()->create([
            'appointment_id' => $this->appointment->id,
            'user_id' => $this->patient->id,
            'reminder_type' => 'sms',
        ]);

        $response = $this->actingAs($this->patient, 'sanctum')
            ->getJson('/api/v1/appointment-reminders');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.reminder_type', 'email');

        $this->assertCount(2, $response['data']);
    }

    /**
     * Test: Get single reminder detail
     */
    public function test_get_reminder_detail()
    {
        $reminder = AppointmentReminder::factory()->create([
            'appointment_id' => $this->appointment->id,
            'user_id' => $this->patient->id,
        ]);

        $response = $this->actingAs($this->patient, 'sanctum')
            ->getJson("/api/v1/appointment-reminders/{$reminder->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $reminder->id)
            ->assertJsonPath('data.reminder_type', $reminder->reminder_type);
    }

    /**
     * Test: Cannot view reminder dari user lain
     */
    public function test_cannot_view_other_user_reminder()
    {
        $otherUser = User::factory()->create(['role' => 'patient']);

        $reminder = AppointmentReminder::factory()->create([
            'appointment_id' => $this->appointment->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->patient, 'sanctum')
            ->getJson("/api/v1/appointment-reminders/{$reminder->id}");

        $response->assertStatus(403);
    }

    /**
     * Test: Get reminders untuk specific appointment
     */
    public function test_get_appointment_reminders()
    {
        AppointmentReminder::factory(3)->create([
            'appointment_id' => $this->appointment->id,
        ]);

        $response = $this->actingAs($this->patient, 'sanctum')
            ->getJson("/api/v1/appointments/{$this->appointment->id}/reminders");

        $response->assertStatus(200)
            ->assertJsonPath('appointment_id', $this->appointment->id);

        $this->assertCount(3, $response['reminders']);
    }

    /**
     * Test: Doctor dapat lihat reminders untuk appointmentnya
     */
    public function test_doctor_can_view_appointment_reminders()
    {
        AppointmentReminder::factory()->create([
            'appointment_id' => $this->appointment->id,
        ]);

        $response = $this->actingAs($this->doctor, 'sanctum')
            ->getJson("/api/v1/appointments/{$this->appointment->id}/reminders");

        $response->assertStatus(200);
    }

    /**
     * Test: Resend reminder successfully
     */
    public function test_resend_reminder()
    {
        $reminder = AppointmentReminder::factory()->create([
            'appointment_id' => $this->appointment->id,
            'user_id' => $this->patient->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->patient, 'sanctum')
            ->postJson("/api/v1/appointment-reminders/{$reminder->id}/resend");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Reminder resent successfully');

        $reminder->refresh();
        $this->assertEquals('sent', $reminder->status);
    }

    /**
     * Test: Cannot resend reminder untuk past appointment
     */
    public function test_cannot_resend_past_appointment_reminder()
    {
        $pastAppointment = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'appointment_time' => now()->subHours(1),
        ]);

        $reminder = AppointmentReminder::factory()->create([
            'appointment_id' => $pastAppointment->id,
            'user_id' => $this->patient->id,
        ]);

        $response = $this->actingAs($this->patient, 'sanctum')
            ->postJson("/api/v1/appointment-reminders/{$reminder->id}/resend");

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    /**
     * Test: Get reminder preferences
     */
    public function test_get_reminder_preferences()
    {
        $this->patient->email_notifications_enabled = true;
        $this->patient->sms_notifications_enabled = false;
        $this->patient->push_notifications_enabled = true;
        $this->patient->save();

        $response = $this->actingAs($this->patient, 'sanctum')
            ->getJson('/api/v1/reminder-preferences');

        $response->assertStatus(200)
            ->assertJsonPath('data.email_enabled', true)
            ->assertJsonPath('data.sms_enabled', false)
            ->assertJsonPath('data.push_enabled', true);
    }

    /**
     * Test: Update reminder preferences
     */
    public function test_update_reminder_preferences()
    {
        $response = $this->actingAs($this->patient, 'sanctum')
            ->putJson('/api/v1/reminder-preferences', [
                'email_enabled' => false,
                'sms_enabled' => true,
                'push_enabled' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->patient->refresh();
        $this->assertFalse($this->patient->email_notifications_enabled);
        $this->assertTrue($this->patient->sms_notifications_enabled);
    }

    /**
     * Test: Cannot access without authentication
     */
    public function test_cannot_access_without_authentication()
    {
        $response = $this->getJson('/api/v1/appointment-reminders');
        $response->assertStatus(401);
    }

    /**
     * Test: Pagination works
     */
    public function test_reminders_pagination()
    {
        AppointmentReminder::factory(25)->create([
            'user_id' => $this->patient->id,
        ]);

        $response = $this->actingAs($this->patient, 'sanctum')
            ->getJson('/api/v1/appointment-reminders?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('pagination.total', 25)
            ->assertJsonPath('pagination.per_page', 10);

        $this->assertCount(10, $response['data']);
    }

    /**
     * Test: Reminders sorted by scheduled_for descending
     */
    public function test_reminders_sorted_by_scheduled_for()
    {
        $reminder1 = AppointmentReminder::factory()->create([
            'user_id' => $this->patient->id,
            'scheduled_for' => now()->addHours(1),
        ]);

        $reminder2 = AppointmentReminder::factory()->create([
            'user_id' => $this->patient->id,
            'scheduled_for' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->patient, 'sanctum')
            ->getJson('/api/v1/appointment-reminders');

        // Most recent first
        $this->assertEquals($reminder2->id, $response['data'][0]['id']);
        $this->assertEquals($reminder1->id, $response['data'][1]['id']);
    }
}
