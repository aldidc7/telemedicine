<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class KonsultasiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Pasien $patient;
    protected Dokter $doctor;
    protected User $patientUser;
    protected User $doctorUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->patientUser = User::factory()->create(['role' => 'pasien']);
        $this->doctorUser = User::factory()->create(['role' => 'dokter']);
        
        $this->patient = Pasien::create([
            'user_id' => $this->patientUser->id,
            'medical_record_number' => 'RM-2025-00001',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
        ]);
        
        $this->doctor = Dokter::create([
            'user_id' => $this->doctorUser->id,
            'license_number' => 'SIP-12345678',
            'specialization' => 'Dokter Umum',
        ]);
    }

    /**
     * Test get consultations list
     */
    public function test_get_consultations_list(): void
    {
        Sanctum::actingAs($this->patientUser);

        Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Sakit kepala',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        $response = $this->getJson('/api/v1/konsultasi');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'pesan',
                'data' => ['*' => ['id', 'complaint', 'status']],
                'status_code'
            ]);
    }

    /**
     * Test create consultation
     */
    public function test_create_consultation(): void
    {
        Sanctum::actingAs($this->patientUser);

        $data = [
            'doctor_id' => $this->doctor->id,
            'complaint' => 'Demam tinggi',
            'description' => 'Demam sejak 3 hari',
        ];

        $response = $this->postJson('/api/v1/konsultasi', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'pesan', 'data', 'status_code']);

        $this->assertDatabaseHas('konsultasi', [
            'complaint' => 'Demam tinggi',
        ]);
    }

    /**
     * Test create consultation unauthenticated
     */
    public function test_create_consultation_unauthenticated(): void
    {
        $data = [
            'doctor_id' => $this->doctor->id,
            'complaint' => 'Sakit gigi',
        ];

        $response = $this->postJson('/api/v1/konsultasi', $data);

        $response->assertStatus(401);
    }

    /**
     * Test get consultation detail
     */
    public function test_get_consultation_detail(): void
    {
        Sanctum::actingAs($this->patientUser);

        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Sakit perut',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        $response = $this->getJson("/api/v1/konsultasi/{$consultation->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'pesan', 'data', 'status_code']);
    }

    /**
     * Test update consultation status
     */
    public function test_update_consultation_status(): void
    {
        Sanctum::actingAs($this->doctorUser);

        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Sakit telinga',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        $data = [
            'status' => 'ongoing',
            'diagnosis' => 'Otitis media',
        ];

        $response = $this->putJson("/api/v1/konsultasi/{$consultation->id}", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('konsultasi', [
            'id' => $consultation->id,
            'status' => 'ongoing',
        ]);
    }

    /**
     * Test send message in consultation
     */
    public function test_send_consultation_message(): void
    {
        Sanctum::actingAs($this->patientUser);

        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Sakit mata',
            'status' => 'ongoing',
            'consultation_date' => now(),
        ]);

        $data = [
            'message' => 'Mata saya masih gatal',
        ];

        $response = $this->postJson("/api/v1/konsultasi/{$consultation->id}/messages", $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'pesan', 'data', 'status_code']);
    }

    /**
     * Test get consultation messages
     */
    public function test_get_consultation_messages(): void
    {
        Sanctum::actingAs($this->patientUser);

        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Follow-up',
            'status' => 'ongoing',
            'consultation_date' => now(),
        ]);

        $response = $this->getJson("/api/v1/konsultasi/{$consultation->id}/messages");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'pesan', 'data', 'status_code']);
    }

    /**
     * Test filter consultations by status
     */
    public function test_filter_consultations_by_status(): void
    {
        Sanctum::actingAs($this->patientUser);

        Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Konsultasi 1',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Konsultasi 2',
            'status' => 'completed',
            'consultation_date' => now(),
        ]);

        $response = $this->getJson('/api/v1/konsultasi?status=pending');

        $response->assertStatus(200);
    }

    /**
     * Test complete consultation
     */
    public function test_complete_consultation(): void
    {
        Sanctum::actingAs($this->doctorUser);

        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Sakit telinga kanan',
            'status' => 'ongoing',
            'consultation_date' => now(),
        ]);

        $data = [
            'status' => 'completed',
            'diagnosis' => 'Infeksi telinga',
            'treatment' => 'Antibiotik',
        ];

        $response = $this->putJson("/api/v1/konsultasi/{$consultation->id}", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('konsultasi', [
            'id' => $consultation->id,
            'status' => 'completed',
        ]);
    }

    /**
     * Test get non-existent consultation returns 404
     */
    public function test_get_non_existent_consultation(): void
    {
        Sanctum::actingAs($this->patientUser);

        $response = $this->getJson('/api/v1/konsultasi/9999');

        $response->assertStatus(404);
    }

    /**
     * Test create consultation with missing field
     */
    public function test_create_consultation_missing_field(): void
    {
        Sanctum::actingAs($this->patientUser);

        $data = [
            'doctor_id' => $this->doctor->id,
            // Missing 'complaint'
        ];

        $response = $this->postJson('/api/v1/konsultasi', $data);

        $response->assertStatus(422);
    }
}
