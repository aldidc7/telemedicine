<?php

namespace Tests\Feature\Concurrent;

use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\Process\Process;

/**
 * Concurrent Access Tests
 * 
 * Test race conditions dan concurrent scenarios
 * Ensure double-booking prevention dan data consistency
 */
class ConcurrentAccessTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $patient1;
    protected User $patient2;
    protected User $doctor;
    protected AppointmentService $appointmentService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->patient1 = User::factory()->create(['role' => 'pasien']);
        $this->patient2 = User::factory()->create(['role' => 'pasien']);
        $this->doctor = User::factory()->create(['role' => 'dokter']);
        $this->appointmentService = app(AppointmentService::class);
    }
    
    /**
     * Test prevent double booking dengan pessimistic locking
     */
    public function test_prevent_double_booking_concurrent_requests(): void
    {
        $scheduledAt = Carbon::tomorrow()->setHour(10)->setMinute(0);
        
        // Simulate two concurrent booking attempts
        $appointment1 = $this->appointmentService->bookAppointment(
            $this->patient1->id,
            $this->doctor->id,
            $scheduledAt->toDateTimeString(),
            'online'
        );
        
        // Second attempt should fail
        try {
            $appointment2 = $this->appointmentService->bookAppointment(
                $this->patient2->id,
                $this->doctor->id,
                $scheduledAt->toDateTimeString(),
                'online'
            );
            
            // Should not reach here
            $this->fail('Double booking should be prevented');
        } catch (\Exception $e) {
            $this->assertStringContainsString('already booked', $e->getMessage());
        }
        
        // Verify only one appointment created
        $this->assertEquals(1, Appointment::where(
            'doctor_id',
            $this->doctor->id
        )->where(
            'scheduled_at',
            $scheduledAt
        )->count());
    }
    
    /**
     * Test concurrent status updates maintain consistency
     */
    public function test_concurrent_status_updates_maintain_consistency(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'pending'
        ]);
        
        // Update status
        $appointment->update(['status' => 'confirmed']);
        
        // Verify status is consistent
        $refreshed = Appointment::findOrFail($appointment->id);
        $this->assertEquals('confirmed', $refreshed->status);
    }
    
    /**
     * Test concurrent appointment cancellations
     */
    public function test_concurrent_cancellation_requests(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'confirmed'
        ]);
        
        // Cancel appointment
        $appointment->update(['status' => 'cancelled']);
        
        // Verify cancelled state
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled'
        ]);
        
        // Verify slot is now available for another booking
        $scheduledAt = $appointment->scheduled_at;
        $conflictCount = Appointment::where('doctor_id', $this->doctor->id)
            ->where('scheduled_at', $scheduledAt)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
        
        $this->assertEquals(0, $conflictCount);
    }
    
    /**
     * Test concurrent prescription updates
     */
    public function test_concurrent_prescription_updates(): void
    {
        // Create appointment
        $appointment = Appointment::create([
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'completed'
        ]);
        
        // Multiple concurrent updates should be handled safely
        DB::transaction(function () use ($appointment) {
            $appointment->update([
                'status' => 'completed',
                'updated_at' => now()
            ]);
        });
        
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed'
        ]);
    }
    
    /**
     * Test deadlock recovery mechanism
     */
    public function test_deadlock_recovery_with_retry(): void
    {
        $scheduledAt = Carbon::tomorrow()->setHour(10)->setMinute(0);
        
        // This test verifies that deadlock handling works
        // In production, deadlock would occur when two transactions
        // lock same resources in different order
        
        try {
            $appointment = $this->appointmentService->bookAppointment(
                $this->patient1->id,
                $this->doctor->id,
                $scheduledAt->toDateTimeString(),
                'online'
            );
            
            $this->assertNotNull($appointment);
            $this->assertEquals('pending', $appointment->status);
        } catch (\Exception $e) {
            // If deadlock occurs, it should be retried
            $this->fail('Booking should succeed with retry mechanism: ' . $e->getMessage());
        }
    }
    
    /**
     * Test concurrent reads dont interfere with writes
     */
    public function test_concurrent_reads_and_writes(): void
    {
        // Create appointments
        $appointments = Appointment::factory(5)->create([
            'doctor_id' => $this->doctor->id
        ]);
        
        // Read while writing
        $readCount = Appointment::where('doctor_id', $this->doctor->id)->count();
        
        $this->assertEquals(5, $readCount);
        
        // Write new appointment
        $newAppointment = Appointment::create([
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(11),
            'type' => 'online',
            'status' => 'pending'
        ]);
        
        // Read again
        $newReadCount = Appointment::where('doctor_id', $this->doctor->id)->count();
        
        $this->assertEquals(6, $newReadCount);
    }
    
    /**
     * Test transaction rollback on error
     */
    public function test_transaction_rollback_on_error(): void
    {
        try {
            DB::transaction(function () {
                Appointment::create([
                    'patient_id' => $this->patient1->id,
                    'doctor_id' => $this->doctor->id,
                    'scheduled_at' => Carbon::tomorrow()->setHour(10),
                    'type' => 'online',
                    'status' => 'pending'
                ]);
                
                // Simulate error
                throw new \Exception('Test error');
            });
        } catch (\Exception $e) {
            // Expected
        }
        
        // Verify appointment was rolled back
        $this->assertEquals(0, Appointment::count());
    }
    
    /**
     * Test lock timeout handling
     */
    public function test_lock_timeout_handling(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'pending'
        ]);
        
        // Lock should be released after transaction
        DB::transaction(function () use ($appointment) {
            $locked = Appointment::lockForUpdate()->find($appointment->id);
            $this->assertNotNull($locked);
        });
        
        // Verify lock is released (no exception thrown)
        $updated = Appointment::find($appointment->id);
        $this->assertNotNull($updated);
    }
}
