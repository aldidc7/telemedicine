<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\AppointmentReminder;
use App\Services\Appointment\AppointmentReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * ============================================
 * APPOINTMENT REMINDER SERVICE TEST
 * ============================================
 * 
 * Test reminder service functionality:
 * - Create reminders
 * - Send reminders
 * - Retry failed reminders
 * - Cleanup old reminders
 * 
 * 12 comprehensive test cases
 */
class AppointmentReminderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AppointmentReminderService $reminderService;
    protected User $patient;
    protected Dokter $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->reminderService = app(AppointmentReminderService::class);

        $this->patient = User::factory()->create([
            'role' => 'patient',
            'email_notifications_enabled' => true,
            'sms_notifications_enabled' => true,
            'push_notifications_enabled' => true,
        ]);

        $this->doctor = Dokter::factory()->create([
            'user_id' => User::factory()->create(['role' => 'doctor'])->id,
        ]);
    }

    /**
     * Test: Create reminders untuk appointment
     */
    public function test_create_reminders_for_appointment()
    {
        $appointment = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_time' => now()->addHours(24),
        ]);

        $this->reminderService->createReminders($appointment);

        // Should create 3 reminders (email, sms, push)
        $this->assertCount(3, AppointmentReminder::where('appointment_id', $appointment->id)->get());
    }

    /**
     * Test: Reminders scheduled pada waktu yang benar
     */
    public function test_reminders_scheduled_correctly()
    {
        $appointmentTime = now()->addHours(24);
        $appointment = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'appointment_time' => $appointmentTime,
        ]);

        $this->reminderService->createReminders($appointment);

        $reminders = AppointmentReminder::where('appointment_id', $appointment->id)
            ->orderBy('reminder_type')
            ->get();

        // Email: 24 hours before
        $this->assertTrue(
            $reminders[0]->scheduled_for->isSameAs('minute', $appointmentTime->copy()->subHours(24))
        );

        // Push: 1 hour before
        $this->assertTrue(
            $reminders[1]->scheduled_for->isSameAs('minute', $appointmentTime->copy()->subHours(1))
        );

        // SMS: 2 hours before
        $this->assertTrue(
            $reminders[2]->scheduled_for->isSameAs('minute', $appointmentTime->copy()->subHours(2))
        );
    }

    /**
     * Test: Don't create reminders jika email disabled
     */
    public function test_skip_email_reminder_if_disabled()
    {
        $this->patient->email_notifications_enabled = false;
        $this->patient->save();

        $appointment = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'appointment_time' => now()->addHours(24),
        ]);

        $this->reminderService->createReminders($appointment);

        $emailReminders = AppointmentReminder::where('appointment_id', $appointment->id)
            ->where('reminder_type', 'email')
            ->count();

        $this->assertEquals(0, $emailReminders);
    }

    /**
     * Test: Mark reminder as sent
     */
    public function test_mark_reminder_as_sent()
    {
        $reminder = AppointmentReminder::factory()->create([
            'status' => 'pending',
            'sent_at' => null,
        ]);

        $reminder->markAsSent();

        $this->assertEquals('sent', $reminder->status);
        $this->assertNotNull($reminder->sent_at);
    }

    /**
     * Test: Mark reminder as failed dengan retry
     */
    public function test_mark_reminder_as_failed()
    {
        $reminder = AppointmentReminder::factory()->create([
            'status' => 'pending',
            'retry_count' => 0,
        ]);

        $reminder->markAsFailed('Email service down');

        $this->assertEquals(1, $reminder->retry_count);
        $this->assertEquals('pending', $reminder->status); // Still pending for retry
        $this->assertEquals('Email service down', $reminder->error_message);
    }

    /**
     * Test: Mark as failed setelah 3 retries
     */
    public function test_mark_as_failed_after_max_retries()
    {
        $reminder = AppointmentReminder::factory()->create([
            'status' => 'pending',
            'retry_count' => 2,
        ]);

        $reminder->markAsFailed('Max retries reached');

        $this->assertEquals(3, $reminder->retry_count);
        $this->assertEquals('failed', $reminder->status);
    }

    /**
     * Test: Get ready to send reminders
     */
    public function test_get_ready_to_send_reminders()
    {
        // Ready to send (past scheduled time)
        AppointmentReminder::factory()->create([
            'status' => 'pending',
            'scheduled_for' => now()->subMinutes(5),
            'retry_count' => 0,
        ]);

        // Not ready yet
        AppointmentReminder::factory()->create([
            'status' => 'pending',
            'scheduled_for' => now()->addMinutes(5),
        ]);

        // Failed (too many retries)
        AppointmentReminder::factory()->create([
            'status' => 'pending',
            'scheduled_for' => now()->subMinutes(5),
            'retry_count' => 3,
        ]);

        $ready = AppointmentReminder::readyToSend()->count();

        $this->assertEquals(1, $ready);
    }

    /**
     * Test: Send pending reminders
     */
    public function test_send_pending_reminders()
    {
        $appointment = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'appointment_time' => now()->addHours(2),
        ]);

        $reminder = AppointmentReminder::factory()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $this->patient->id,
            'reminder_type' => 'email',
            'status' => 'pending',
            'scheduled_for' => now()->subMinutes(5),
        ]);

        $this->reminderService->sendPendingReminders();

        $reminder->refresh();
        $this->assertEquals('sent', $reminder->status);
    }

    /**
     * Test: Skip expired appointment reminders
     */
    public function test_skip_expired_appointment()
    {
        $appointment = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'appointment_time' => now()->subHours(1), // Past
        ]);

        $reminder = AppointmentReminder::factory()->create([
            'appointment_id' => $appointment->id,
            'status' => 'pending',
            'scheduled_for' => now()->subMinutes(5),
        ]);

        $this->reminderService->sendPendingReminders();

        $reminder->refresh();
        $this->assertEquals('cancelled', $reminder->status);
    }

    /**
     * Test: Retry failed reminders
     */
    public function test_retry_failed_reminders()
    {
        $reminder = AppointmentReminder::factory()->create([
            'status' => 'pending',
            'retry_count' => 1,
            'updated_at' => now()->subHours(2),
        ]);

        $this->reminderService->retryFailedReminders();

        $reminder->refresh();
        $this->assertEquals('sent', $reminder->status);
    }

    /**
     * Test: Cleanup old reminders
     */
    public function test_cleanup_old_reminders()
    {
        // Old sent reminder
        AppointmentReminder::factory()->create([
            'status' => 'sent',
            'sent_at' => now()->subDays(100),
        ]);

        // Recent sent reminder
        AppointmentReminder::factory()->create([
            'status' => 'sent',
            'sent_at' => now()->subDays(10),
        ]);

        $this->reminderService->cleanupOldReminders();

        $this->assertCount(1, AppointmentReminder::all());
    }

    /**
     * Test: Get reminder type label
     */
    public function test_get_reminder_type_label()
    {
        $emailReminder = AppointmentReminder::factory()->create([
            'reminder_type' => 'email',
        ]);

        $smsReminder = AppointmentReminder::factory()->create([
            'reminder_type' => 'sms',
        ]);

        $this->assertEquals('Email Reminder', $emailReminder->getReminderTypeLabel());
        $this->assertEquals('SMS Reminder', $smsReminder->getReminderTypeLabel());
    }

    /**
     * Test: Get status label
     */
    public function test_get_status_label()
    {
        $reminder = AppointmentReminder::factory()->create([
            'status' => 'pending',
        ]);

        $this->assertEquals('Pending', $reminder->getStatusLabel());

        $reminder->markAsSent();
        $this->assertEquals('Sent', $reminder->getStatusLabel());
    }
}
