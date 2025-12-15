<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Konsultasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Feature Tests untuk Consultation API
 * 
 * Test semua consultation endpoints dengan real database
 * Coverage: CRUD + messaging + status transitions
 */
class ConsultationControllerTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $patient;
    protected User $doctor;
    protected Konsultasi $consultation;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->patient = User::factory()->create(['role' => 'pasien']);
        $this->doctor = User::factory()->create(['role' => 'dokter']);
        
        $this->consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'keluhan' => 'Sakit kepala',
            'status' => 'pending'
        ]);
    }
    
    /**
     * Test start consultation
     */
    public function test_doctor_can_start_consultation(): void
    {
        $response = $this->actingAs($this->doctor)
            ->putJson("/api/v1/consultations/{$this->consultation->id}/start");
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('konsultasis', [
            'id' => $this->consultation->id,
            'status' => 'aktif'
        ]);
    }
    
    /**
     * Test end consultation
     */
    public function test_doctor_can_end_consultation(): void
    {
        $this->consultation->update(['status' => 'aktif']);
        
        $response = $this->actingAs($this->doctor)
            ->putJson("/api/v1/consultations/{$this->consultation->id}/end", [
                'diagnosis' => 'Migraine',
                'treatment' => 'Rest and medication'
            ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('konsultasis', [
            'id' => $this->consultation->id,
            'status' => 'selesai'
        ]);
    }
    
    /**
     * Test get consultation details
     */
    public function test_get_consultation_details(): void
    {
        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/consultations/{$this->consultation->id}");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->consultation->id)
            ->assertJsonPath('data.status', 'pending');
    }
    
    /**
     * Test list consultations for patient
     */
    public function test_list_patient_consultations(): void
    {
        Konsultasi::factory(3)->create([
            'pasien_id' => $this->patient->id
        ]);
        
        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/consultations');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'pasien_id',
                        'dokter_id',
                        'status'
                    ]
                ]
            ]);
    }
    
    /**
     * Test list consultations for doctor
     */
    public function test_list_doctor_consultations(): void
    {
        Konsultasi::factory(3)->create([
            'dokter_id' => $this->doctor->id
        ]);
        
        $response = $this->actingAs($this->doctor)
            ->getJson('/api/v1/consultations');
        
        $response->assertStatus(200);
    }
    
    /**
     * Test patient cannot start consultation
     */
    public function test_patient_cannot_start_consultation(): void
    {
        $response = $this->actingAs($this->patient)
            ->putJson("/api/v1/consultations/{$this->consultation->id}/start");
        
        $response->assertStatus(403);
    }
    
    /**
     * Test cancel consultation
     */
    public function test_cancel_consultation(): void
    {
        $response = $this->actingAs($this->patient)
            ->putJson("/api/v1/consultations/{$this->consultation->id}/cancel", [
                'reason' => 'Cannot make it'
            ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('konsultasis', [
            'id' => $this->consultation->id,
            'status' => 'dibatalkan'
        ]);
    }
    
    /**
     * Test cannot access others consultation
     */
    public function test_cannot_access_others_consultation(): void
    {
        $otherPatient = User::factory()->create(['role' => 'pasien']);
        
        $response = $this->actingAs($otherPatient)
            ->getJson("/api/v1/consultations/{$this->consultation->id}");
        
        $response->assertStatus(403);
    }
    
    /**
     * Test unauthenticated access denied
     */
    public function test_unauthenticated_cannot_access_consultations(): void
    {
        $response = $this->getJson('/api/v1/consultations');
        
        $response->assertStatus(401);
    }
}
