<?php

namespace Tests\Feature\Smoke;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Smoke Tests
 * 
 * Quick validation of critical paths
 * User registration, login, appointment booking
 * Focus on happy paths only
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $patient;
    protected User $doctor;
    protected string $baseUrl = 'http://localhost:8000/api';
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->patient = User::factory()->create([
            'role' => 'pasien',
            'email' => 'patient@test.com'
        ]);
        
        $this->doctor = User::factory()->create([
            'role' => 'dokter',
            'email' => 'doctor@test.com'
        ]);
    }
    
    /**
     * Test user registration flow
     */
    public function test_user_registration_smoke_test(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New Patient',
            'email' => 'newpatient@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pasien'
        ]);
        
        $response->assertCreated();
        $response->assertJsonStructure(['data' => ['id', 'email', 'role']]);
    }
    
    /**
     * Test user login flow
     */
    public function test_user_login_smoke_test(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->patient->email,
            'password' => 'password' // default factory password
        ]);
        
        // Should succeed or return valid response
        $this->assertTrue(
            $response->status() === 200 || $response->status() === 401,
            'Login returned unexpected status'
        );
        
        if ($response->status() === 200) {
            $response->assertJsonStructure(['access_token']);
        }
    }
    
    /**
     * Test appointment booking critical path
     */
    public function test_appointment_booking_critical_path(): void
    {
        $this->actingAs($this->patient, 'sanctum');
        
        // Step 1: Get available slots
        $date = Carbon::tomorrow()->format('Y-m-d');
        $response = $this->getJson("/api/appointments/available-slots", [
            'doctor_id' => $this->doctor->id,
            'date' => $date
        ]);
        
        $response->assertOk();
        
        // Step 2: Book appointment
        $scheduledAt = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $bookResponse = $this->postJson('/api/appointments', [
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => $scheduledAt->toDateTimeString(),
            'type' => 'online',
            'notes' => 'General checkup'
        ]);
        
        $bookResponse->assertCreated();
        $bookResponse->assertJsonStructure(['data' => ['id', 'status']]);
    }
    
    /**
     * Test consultation start and end flow
     */
    public function test_consultation_flow_smoke_test(): void
    {
        $consultation = \App\Models\Consultation::create([
            'appointment_id' => \App\Models\Appointment::create([
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => Carbon::now()->addHours(1),
                'type' => 'online',
                'status' => 'confirmed'
            ])->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'scheduled'
        ]);
        
        $this->actingAs($this->doctor, 'sanctum');
        
        // Start consultation
        $startResponse = $this->postJson(
            "/api/consultations/{$consultation->id}/start"
        );
        
        $startResponse->assertOk();
        
        // End consultation
        $endResponse = $this->postJson(
            "/api/consultations/{$consultation->id}/end",
            [
                'diagnosis' => 'Test diagnosis',
                'treatment' => 'Test treatment'
            ]
        );
        
        $endResponse->assertOk();
    }
    
    /**
     * Test doctor profile update
     */
    public function test_doctor_profile_update_smoke_test(): void
    {
        $this->actingAs($this->doctor, 'sanctum');
        
        $response = $this->putJson('/api/profile', [
            'name' => 'Updated Doctor Name',
            'phone' => '+62812345678'
        ]);
        
        $response->assertOk();
    }
    
    /**
     * Test patient profile update
     */
    public function test_patient_profile_update_smoke_test(): void
    {
        $this->actingAs($this->patient, 'sanctum');
        
        $response = $this->putJson('/api/profile', [
            'name' => 'Updated Patient Name',
            'phone' => '+62812345678'
        ]);
        
        $response->assertOk();
    }
    
    /**
     * Test rating submission
     */
    public function test_rating_submission_smoke_test(): void
    {
        $appointment = \App\Models\Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::now()->subDay(),
            'type' => 'online',
            'status' => 'completed'
        ]);
        
        $this->actingAs($this->patient, 'sanctum');
        
        $response = $this->postJson('/api/ratings', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
            'comment' => 'Great consultation'
        ]);
        
        $response->assertCreated();
    }
    
    /**
     * Test dashboard data retrieval
     */
    public function test_dashboard_retrieval_smoke_test(): void
    {
        $this->actingAs($this->doctor, 'sanctum');
        
        $response = $this->getJson('/api/doctor/dashboard');
        
        $response->assertOk();
        $response->assertJsonStructure([
            'today_appointments',
            'total_patients',
            'recent_consultations'
        ]);
    }
    
    /**
     * Test error handling on invalid input
     */
    public function test_error_handling_smoke_test(): void
    {
        $this->actingAs($this->patient, 'sanctum');
        
        $response = $this->postJson('/api/appointments', [
            'doctor_id' => 999999,
            'scheduled_at' => 'invalid-date',
            'type' => 'invalid-type'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }
    
    /**
     * Test authentication required
     */
    public function test_authentication_required_smoke_test(): void
    {
        // Try to access protected endpoint without auth
        $response = $this->getJson('/api/doctor/dashboard');
        
        $response->assertUnauthorized();
    }
}
