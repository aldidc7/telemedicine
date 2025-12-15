<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pasien;
use App\Models\RekamMedis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasienControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $pasienUser;
    private Pasien $pasien;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->pasienUser = User::factory()->create(['role' => 'pasien']);
        $this->pasien = Pasien::factory()->create([
            'user_id' => $this->pasienUser->id,
        ]);
    }

    /**
     * Test - Get semua pasien (hanya admin)
     * GET /api/v1/pasien
     */
    public function test_get_all_patients_as_admin()
    {
        Pasien::factory()->count(5)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/pasien');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                '*' => [
                    'id',
                    'nomor_identitas',
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'alamat',
                    'user' => ['id', 'name', 'email'],
                ],
            ],
        ]);
    }

    /**
     * Test - Get pasien tanpa autentikasi
     */
    public function test_get_all_patients_unauthorized()
    {
        $response = $this->getJson('/api/v1/pasien');

        $response->assertStatus(401);
    }

    /**
     * Test - Get detail pasien berdasarkan ID
     * GET /api/v1/pasien/{id}
     */
    public function test_get_patient_detail()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/v1/pasien/{$this->pasien->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'id',
                'nomor_identitas',
                'tanggal_lahir',
                'user',
            ],
        ]);
    }

    /**
     * Test - Get pasien yang tidak ada
     */
    public function test_get_patient_not_found()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/pasien/99999');

        $response->assertStatus(404);
    }

    /**
     * Test - Update data pasien
     * PUT /api/v1/pasien/{id}
     */
    public function test_update_patient()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/v1/pasien/{$this->pasien->id}", [
                'tanggal_lahir' => '1985-05-20',
                'jenis_kelamin' => 'perempuan',
                'alamat' => 'Jl. Baru No. 456',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Data pasien berhasil diperbarui',
        ]);

        $this->assertDatabaseHas('patients', [
            'id' => $this->pasien->id,
            'alamat' => 'Jl. Baru No. 456',
        ]);
    }

    /**
     * Test - Delete pasien
     * DELETE /api/v1/pasien/{id}
     */
    public function test_delete_patient()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/v1/pasien/{$this->pasien->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Pasien berhasil dihapus',
        ]);

        $this->assertDatabaseMissing('patients', [
            'id' => $this->pasien->id,
        ]);
    }

    /**
     * Test - Get rekam medis pasien
     * GET /api/v1/pasien/{id}/rekam-medis
     */
    public function test_get_patient_medical_records()
    {
        RekamMedis::factory()->count(3)->create([
            'patient_id' => $this->pasien->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/v1/pasien/{$this->pasien->id}/rekam-medis");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                '*' => [
                    'id',
                    'keluhan',
                    'diagnosis',
                    'tanggal_periksa',
                ],
            ],
        ]);
    }

    /**
     * Test - Get konsultasi pasien
     * GET /api/v1/pasien/{id}/konsultasi
     */
    public function test_get_patient_consultations()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/v1/pasien/{$this->pasien->id}/konsultasi");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data',
        ]);
    }

    /**
     * Test - Pasien lain tidak bisa akses data pasien
     */
    public function test_patient_cannot_access_other_patient_data()
    {
        $otherPasienUser = User::factory()->create(['role' => 'pasien']);
        $otherPasien = Pasien::factory()->create([
            'user_id' => $otherPasienUser->id,
        ]);

        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->getJson("/api/v1/pasien/{$otherPasien->id}");

        $response->assertStatus(403);
    }

    /**
     * Test - Update pasien dengan data invalid
     */
    public function test_update_patient_invalid_data()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/v1/pasien/{$this->pasien->id}", [
                'tanggal_lahir' => 'invalid-date',
                'jenis_kelamin' => 'invalid',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tanggal_lahir', 'jenis_kelamin']);
    }
}
