<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Feature Tests untuk Appointment API
 * 
 * Test semua appointment endpoints dengan real database
 * Coverage: CRUD + business logic + error cases
 */
class AppointmentControllerTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $patient;
    protected User $doctor;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->patient = User::factory()->create(['role' => 'pasien']);
        $this->doctor = User::factory()->create(['role' => 'dokter']);
    }
    
    /**
     * Test get available slots successfully
     */
    public function test_get_available_slots_success(): void
    {
        $date = Carbon::tomorrow()->format('Y-m-d');
        
        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/appointments/slots/{$this->doctor->id}", [
                'date' => $date
            ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'slots' => []
                ]
            ]);
    }
    
    /**
     * Test get available slots invalid date
     */
    public function test_get_available_slots_invalid_date(): void
    {
        $date = Carbon::yesterday()->format('Y-m-d');
        
        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/appointments/slots/{$this->doctor->id}", [
                'date' => $date
            ]);
        
        $response->assertStatus(422);
    }
    
    /**
     * Test book appointment successfully
     */
    public function test_book_appointment_success(): void
    {
        $scheduledAt = Carbon::tomorrow()->setHour(10)->setMinute(0);
        
        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/appointments', [
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => $scheduledAt,
                'type' => 'online',
                'reason' => 'Checkup'
            ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'patient_id',
                    'doctor_id',
                    'status',
                    'scheduled_at'
                ]
            ]);
        
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'pending'
        ]);
    }
    
    /**
     * Test book appointment double booking prevention
     */
    public function test_book_appointment_prevent_double_booking(): void
    {
        $scheduledAt = Carbon::tomorrow()->setHour(10)->setMinute(0);
        
        // Create first appointment
        Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => $scheduledAt,
            'type' => 'online',
            'status' => 'confirmed'
        ]);
        
        // Try to book same slot
        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/appointments', [
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => $scheduledAt,
                'type' => 'online',
                'reason' => 'Checkup'
            ]);
        
        $response->assertStatus(409); // Conflict
    }
    
    /**
     * Test confirm appointment by doctor
     */
    public function test_confirm_appointment_by_doctor(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'pending'
        ]);
        
        $response = $this->actingAs($this->doctor)
            ->putJson("/api/v1/appointments/{$appointment->id}/confirm");
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'confirmed'
        ]);
    }
    
    /**
     * Test reject appointment by doctor
     */
    public function test_reject_appointment_by_doctor(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'pending'
        ]);
        
        $response = $this->actingAs($this->doctor)
            ->putJson("/api/v1/appointments/{$appointment->id}/reject", [
                'reason' => 'Not available'
            ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'rejected'
        ]);
    }
    
    /**
     * Test get appointment details
     */
    public function test_get_appointment_details(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'confirmed'
        ]);
        
        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/appointments/{$appointment->id}");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $appointment->id)
            ->assertJsonPath('data.status', 'confirmed');
    }
    
    /**
     * Test list patient appointments
     */
    public function test_list_patient_appointments(): void
    {
        // Create multiple appointments
        Appointment::factory(3)->create([
            'patient_id' => $this->patient->id
        ]);
        
        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/appointments');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'patient_id',
                        'doctor_id',
                        'status'
                    ]
                ],
                'meta' => [
                    'total',
                    'per_page',
                    'current_page'
                ]
            ]);
    }
    
    /**
     * Test cancel appointment by patient
     */
    public function test_cancel_appointment_by_patient(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'confirmed'
        ]);
        
        $response = $this->actingAs($this->patient)
            ->putJson("/api/v1/appointments/{$appointment->id}/cancel");
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled'
        ]);
    }
    
    /**
     * Test unauthorized access
     */
    public function test_cannot_access_others_appointment(): void
    {
        $otherPatient = User::factory()->create(['role' => 'pasien']);
        $appointment = Appointment::create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'confirmed'
        ]);
        
        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/appointments/{$appointment->id}");
        
        $response->assertStatus(403);
    }
    
    /**
     * Test unauthenticated access
     */
    public function test_unauthenticated_cannot_access_appointments(): void
    {
        $response = $this->getJson('/api/v1/appointments');
        
        $response->assertStatus(401);
    }
    
    /**
     * Test invalid input validation
     */
    public function test_book_appointment_invalid_input(): void
    {
        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/appointments', [
                'doctor_id' => 'invalid', // Invalid type
                'scheduled_at' => 'invalid-date', // Invalid date
                'type' => 'invalid-type', // Invalid enum
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['doctor_id', 'scheduled_at', 'type']);
    }
    
    /**
     * Test mark appointment completed
     */
    public function test_mark_appointment_completed(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::now()->subHours(1),
            'type' => 'online',
            'status' => 'in_progress'
        ]);
        
        $response = $this->actingAs($this->doctor)
            ->putJson("/api/v1/appointments/{$appointment->id}/complete");
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed'
        ]);
    }
    
    /**
     * Test invalid status transition
     */
    public function test_invalid_status_transition(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(10),
            'type' => 'online',
            'status' => 'completed' // Final status
        ]);
        
        $response = $this->actingAs($this->doctor)
            ->putJson("/api/v1/appointments/{$appointment->id}/confirm"); // Cannot transition from completed
        
        $response->assertStatus(422);
    }
}
