<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Integration Tests
 * 
 * Test interactions between services, cache, database
 * Verify end-to-end workflows work correctly
 */
class AppointmentIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $patient;
    protected User $doctor;
    protected AppointmentService $appointmentService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->patient = User::factory()->create(['role' => 'pasien']);
        $this->doctor = User::factory()->create(['role' => 'dokter']);
        $this->appointmentService = app(AppointmentService::class);
    }
    
    /**
     * Test full appointment booking workflow
     */
    public function test_full_appointment_booking_workflow(): void
    {
        $scheduledAt = Carbon::tomorrow()->setHour(10)->setMinute(0);
        
        // Step 1: Get available slots
        $slots = $this->appointmentService->getAvailableSlots(
            $this->doctor->id,
            $scheduledAt->format('Y-m-d')
        );
        
        $this->assertNotEmpty($slots);
        $this->assertContains($scheduledAt->format('H:i'), $slots);
        
        // Step 2: Book appointment
        $appointment = $this->appointmentService->bookAppointment(
            $this->patient->id,
            $this->doctor->id,
            $scheduledAt->toDateTimeString(),
            'online',
            'General checkup'
        );
        
        $this->assertNotNull($appointment);
        $this->assertEquals('pending', $appointment->status);
        
        // Step 3: Verify appointment in database
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'pending'
        ]);
        
        // Step 4: Verify slot is no longer available
        $slotsAfterBooking = $this->appointmentService->getAvailableSlots(
            $this->doctor->id,
            $scheduledAt->format('Y-m-d')
        );
        
        $this->assertNotContains($scheduledAt->format('H:i'), $slotsAfterBooking);
    }
    
    /**
     * Test appointment status workflow
     */
    public function test_appointment_status_workflow(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'pending'
        ]);
        
        // pending → confirmed
        $appointment->update(['status' => 'confirmed']);
        $this->assertEquals('confirmed', $appointment->fresh()->status);
        
        // confirmed → completed
        $appointment->update(['status' => 'completed']);
        $this->assertEquals('completed', $appointment->fresh()->status);
    }
    
    /**
     * Test cache invalidation on appointment update
     */
    public function test_cache_invalidation_on_appointment_update(): void
    {
        $date = Carbon::tomorrow()->format('Y-m-d');
        
        // Get and cache slots
        $slotsBeforeBooking = $this->appointmentService->getAvailableSlots(
            $this->doctor->id,
            $date
        );
        
        $slotCount = count($slotsBeforeBooking);
        
        // Book appointment (which should invalidate cache)
        $appointment = $this->appointmentService->bookAppointment(
            $this->patient->id,
            $this->doctor->id,
            Carbon::parse($date)->setHour(10)->toDateTimeString(),
            'online'
        );
        
        // Get slots again (should be from fresh cache)
        $slotsAfterBooking = $this->appointmentService->getAvailableSlots(
            $this->doctor->id,
            $date
        );
        
        $this->assertLessThan($slotCount, count($slotsAfterBooking));
    }
    
    /**
     * Test appointment retrieval includes relationships
     */
    public function test_appointment_retrieval_includes_relationships(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'pending'
        ]);
        
        $retrieved = Appointment::with(['patient', 'doctor'])
            ->find($appointment->id);
        
        $this->assertNotNull($retrieved->patient);
        $this->assertNotNull($retrieved->doctor);
        $this->assertEquals($this->patient->id, $retrieved->patient->id);
        $this->assertEquals($this->doctor->id, $retrieved->doctor->id);
    }
    
    /**
     * Test query optimization with eager loading
     */
    public function test_query_optimization_with_eager_loading(): void
    {
        // Create multiple appointments
        Appointment::factory(10)->create([
            'doctor_id' => $this->doctor->id
        ]);
        
        // Count queries without eager loading
        $this->resetQueryCount();
        $appointments = Appointment::where('doctor_id', $this->doctor->id)->get();
        
        foreach ($appointments as $appt) {
            $appt->patient; // N+1 problem without eager loading
        }
        
        $queriesWithoutEager = $this->getQueryCount();
        
        // Count queries with eager loading
        $this->resetQueryCount();
        $appointments = Appointment::with('patient')
            ->where('doctor_id', $this->doctor->id)
            ->get();
        
        foreach ($appointments as $appt) {
            $appt->patient;
        }
        
        $queriesWithEager = $this->getQueryCount();
        
        // Eager loading should use fewer queries
        $this->assertLessThan($queriesWithoutEager, $queriesWithEager + 5);
    }
    
    /**
     * Test pagination works correctly
     */
    public function test_pagination_works_correctly(): void
    {
        // Create 25 appointments
        Appointment::factory(25)->create([
            'patient_id' => $this->patient->id
        ]);
        
        // Get first page
        $page1 = $this->appointmentService->getPatientAppointments(
            $this->patient->id,
            null,
            1,
            15
        );
        
        $this->assertEquals(15, $page1->count());
        $this->assertEquals(1, $page1->currentPage());
        $this->assertEquals(2, $page1->lastPage());
        
        // Get second page
        $page2 = $this->appointmentService->getPatientAppointments(
            $this->patient->id,
            null,
            2,
            15
        );
        
        $this->assertEquals(10, $page2->count());
        $this->assertEquals(2, $page2->currentPage());
    }
    
    /**
     * Test filtering by status
     */
    public function test_filtering_appointments_by_status(): void
    {
        // Create appointments with different statuses
        Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'pending'
        ]);
        
        Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(11),
            'type' => 'online',
            'status' => 'confirmed'
        ]);
        
        // Filter by status
        $pending = $this->appointmentService->getPatientAppointments(
            $this->patient->id,
            'pending'
        );
        
        $this->assertEquals(1, $pending->count());
        $this->assertEquals('pending', $pending->first()->status);
    }
}
