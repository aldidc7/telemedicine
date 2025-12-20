<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Dokter;
use App\Models\DoctorPatientRelationship;
use App\Models\ConsentRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ============================================
 * CONSENT & DOCTOR-PATIENT RELATIONSHIP TESTS
 * ============================================
 * 
 * Feature tests untuk Phase 1 & Phase 2 implementations.
 * Tests Informed Consent Modal dan Doctor-Patient Relationship endpoints.
 * 
 * Run: php artisan test tests/Feature/ConsentAndRelationshipTest.php
 */
class ConsentAndRelationshipTest extends TestCase
{
    use RefreshDatabase;

    protected $patient;
    protected $doctor;
    protected $doctorUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users (role harus 'dokter' & 'patient', bukan 'doctor')
        $this->doctorUser = User::factory()->create(['role' => 'dokter']);
        $this->doctor = Dokter::factory()->create(['user_id' => $this->doctorUser->id]);
        
        $this->patient = User::factory()->create(['role' => 'patient']);
    }

    // ========== CONSENT TESTS ==========

    /** @test */
    public function patient_can_get_required_consents()
    {
        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/consent/required');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['*' => ['type', 'description', 'required']]
            ]);
    }

    /** @test */
    public function patient_can_accept_consent()
    {
        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/consent/accept', [
                'consent_type' => 'telemedicine',
                'consent_text' => 'Saya setuju dengan persyaratan telemedicine',
                'ip_address' => '127.0.0.1',
            ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
        
        // Verify consent recorded
        $this->assertDatabaseHas('consent_records', [
            'user_id' => $this->patient->id,
            'consent_type' => 'telemedicine',
            'accepted' => true,
        ]);
    }

    /** @test */
    public function patient_can_check_consent_status()
    {
        // First accept consent
        $this->actingAs($this->patient)
            ->postJson('/api/v1/consent/accept', [
                'consent_type' => 'privacy_policy',
                'consent_text' => 'I agree',
                'ip_address' => '127.0.0.1',
            ]);
        
        // Then check status
        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/consent/check/privacy_policy');
        
        $response->assertStatus(200)
            ->assertJson(['success' => true, 'accepted' => true]);
    }

    /** @test */
    public function patient_can_view_consent_history()
    {
        // Create multiple consents
        ConsentRecord::factory(3)->create(['user_id' => $this->patient->id]);
        
        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/consent/history');
        
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function patient_can_revoke_consent()
    {
        $consent = ConsentRecord::factory()->create(['user_id' => $this->patient->id]);
        
        $response = $this->actingAs($this->patient)
            ->postJson("/api/v1/consent/revoke/{$consent->id}");
        
        $response->assertStatus(200);
        
        $this->assertNotNull($consent->refresh()->revoked_at);
    }

    // ========== DOCTOR-PATIENT RELATIONSHIP TESTS ==========

    /** @test */
    public function doctor_can_establish_relationship_with_patient()
    {
        $response = $this->actingAs($this->doctorUser)
            ->postJson('/api/v1/doctor-patient-relationships', [
                'patient_id' => $this->patient->id,
                'establishment_method' => 'consultation',
                'notes' => 'Initial consultation',
            ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
        
        $this->assertDatabaseHas('doctor_patient_relationships', [
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function doctor_cannot_establish_duplicate_relationship()
    {
        // Create first relationship
        DoctorPatientRelationship::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'establishment_method' => 'consultation',
            'status' => 'active',
            'established_at' => now(),
        ]);
        
        // Try to create duplicate
        $response = $this->actingAs($this->doctorUser)
            ->postJson('/api/v1/doctor-patient-relationships', [
                'patient_id' => $this->patient->id,
                'establishment_method' => 'consultation',
            ]);
        
        $response->assertStatus(422);
    }

    /** @test */
    public function doctor_can_get_patient_list()
    {
        // Create relationships
        DoctorPatientRelationship::factory(5)->create(['doctor_id' => $this->doctor->id]);
        
        $response = $this->actingAs($this->doctorUser)
            ->getJson('/api/v1/doctor-patient-relationships/my-patients');
        
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    /** @test */
    public function doctor_can_check_relationship_exists()
    {
        DoctorPatientRelationship::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'establishment_method' => 'consultation',
            'status' => 'active',
            'established_at' => now(),
        ]);
        
        $response = $this->actingAs($this->doctorUser)
            ->getJson("/api/v1/doctor-patient-relationships/check/{$this->patient->id}");
        
        $response->assertStatus(200)
            ->assertJson(['success' => true, 'has_relationship' => true]);
    }

    /** @test */
    public function patient_can_get_doctor_list()
    {
        DoctorPatientRelationship::factory(3)->create(['patient_id' => $this->patient->id]);
        
        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/doctor-patient-relationships/my-doctors');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function doctor_can_terminate_relationship()
    {
        $relationship = DoctorPatientRelationship::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'establishment_method' => 'consultation',
            'status' => 'active',
            'established_at' => now(),
        ]);
        
        $response = $this->actingAs($this->doctorUser)
            ->putJson("/api/v1/doctor-patient-relationships/{$relationship->id}/terminate", [
                'reason' => 'Patient requested termination',
            ]);
        
        $response->assertStatus(200);
        
        $this->assertEquals('terminated', $relationship->refresh()->status);
        $this->assertNotNull($relationship->terminated_at);
    }

    /** @test */
    public function relationship_history_tracks_all_actions()
    {
        $relationship = DoctorPatientRelationship::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'establishment_method' => 'consultation',
            'status' => 'active',
            'established_at' => now(),
        ]);
        
        $response = $this->actingAs($this->doctorUser)
            ->getJson("/api/v1/doctor-patient-relationships/{$relationship->id}/history");
        
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'relationship', 'activity_logs']);
    }

    // ========== AUTHORIZATION TESTS ==========

    /** @test */
    public function patient_cannot_establish_relationship()
    {
        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/doctor-patient-relationships', [
                'patient_id' => $this->patient->id,
                'establishment_method' => 'consultation',
            ]);
        
        // Should fail because patient tidak punya dokter record
        $response->assertStatus(404);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_endpoints()
    {
        $this->getJson('/api/v1/doctor-patient-relationships/my-patients')
            ->assertStatus(401);
    }

    /** @test */
    public function patient_cannot_access_other_patient_relationships()
    {
        $otherPatient = User::factory()->create(['role' => 'patient']);
        $relationship = DoctorPatientRelationship::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $otherPatient->id,
            'establishment_method' => 'consultation',
            'status' => 'active',
            'established_at' => now(),
        ]);
        
        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/doctor-patient-relationships/{$relationship->id}/history");
        
        $response->assertStatus(403);
    }
}
