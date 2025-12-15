<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KonsultasiControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $pasienUser;
    private User $dokterUser;
    private User $adminUser;
    private Pasien $pasien;
    private Dokter $dokter;
    private Konsultasi $konsultasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->pasienUser = User::factory()->create(['role' => 'pasien']);
        $this->dokterUser = User::factory()->create(['role' => 'dokter']);

        $this->pasien = Pasien::factory()->create(['user_id' => $this->pasienUser->id]);
        $this->dokter = Dokter::factory()->create(['user_id' => $this->dokterUser->id]);

        $this->konsultasi = Konsultasi::factory()->create([
            'patient_id' => $this->pasien->id,
            'doctor_id' => $this->dokter->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test - Get semua konsultasi
     * GET /api/v1/konsultasi
     */
    public function test_get_all_consultations()
    {
        Konsultasi::factory()->count(5)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/konsultasi');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                '*' => [
                    'id',
                    'keluhan',
                    'status',
                    'waktu_mulai',
                    'pasien' => ['user'],
                    'dokter' => ['user'],
                ],
            ],
        ]);
    }

    /**
     * Test - Get konsultasi dengan filter status
     */
    public function test_get_consultations_by_status()
    {
        Konsultasi::factory()->create(['status' => 'pending']);
        Konsultasi::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/konsultasi?status=diterima');

        $response->assertStatus(200);
    }

    /**
     * Test - Get detail konsultasi
     * GET /api/v1/konsultasi/{id}
     */
    public function test_get_consultation_detail()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->getJson("/api/v1/konsultasi/{$this->konsultasi->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'id',
                'keluhan',
                'status',
                'pasien',
                'dokter',
            ],
        ]);
    }

    /**
     * Test - Get konsultasi yang tidak ada
     */
    public function test_get_consultation_not_found()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->getJson('/api/v1/konsultasi/99999');

        $response->assertStatus(404);
    }

    /**
     * Test - Create konsultasi baru
     * POST /api/v1/konsultasi
     */
    public function test_create_consultation()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->postJson('/api/v1/konsultasi', [
                'doctor_id' => $this->dokter->id,
                'keluhan' => 'Demam tinggi selama 3 hari',
                'gejala' => 'Panas badan, pusing',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Konsultasi berhasil dibuat',
        ]);

        $this->assertDatabaseHas('consultations', [
            'patient_id' => $this->pasien->id,
            'doctor_id' => $this->dokter->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test - Create konsultasi tanpa data yang lengkap
     */
    public function test_create_consultation_missing_required_fields()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->postJson('/api/v1/konsultasi', [
                'doctor_id' => $this->dokter->id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['keluhan']);
    }

    /**
     * Test - Update konsultasi
     * PUT /api/v1/konsultasi/{id}
     */
    public function test_update_consultation()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->putJson("/api/v1/konsultasi/{$this->konsultasi->id}", [
                'keluhan' => 'Demam tinggi dan sakit kepala',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Konsultasi berhasil diperbarui',
        ]);
    }

    /**
     * Test - Delete konsultasi
     * DELETE /api/v1/konsultasi/{id}
     */
    public function test_delete_consultation()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->deleteJson("/api/v1/konsultasi/{$this->konsultasi->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Konsultasi berhasil dihapus',
        ]);

        $this->assertDatabaseMissing('consultations', [
            'id' => $this->konsultasi->id,
        ]);
    }

    /**
     * Test - Terima konsultasi (doctor)
     * POST /api/v1/konsultasi/{id}/terima
     */
    public function test_accept_consultation()
    {
        $response = $this->actingAs($this->dokterUser, 'sanctum')
            ->postJson("/api/v1/konsultasi/{$this->konsultasi->id}/terima");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Konsultasi berhasil diterima',
        ]);

        $this->assertDatabaseHas('consultations', [
            'id' => $this->konsultasi->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test - Tolak konsultasi (doctor)
     * POST /api/v1/konsultasi/{id}/tolak
     */
    public function test_reject_consultation()
    {
        $response = $this->actingAs($this->dokterUser, 'sanctum')
            ->postJson("/api/v1/konsultasi/{$this->konsultasi->id}/tolak", [
                'alasan' => 'Sedang sibuk dengan konsultasi lain',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Konsultasi berhasil ditolak',
        ]);

        $this->assertDatabaseHas('consultations', [
            'id' => $this->konsultasi->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * Test - Selesaikan konsultasi
     * POST /api/v1/konsultasi/{id}/selesaikan
     */
    public function test_complete_consultation()
    {
        $konsultasi = Konsultasi::factory()->create([
            'patient_id' => $this->pasien->id,
            'doctor_id' => $this->dokter->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->dokterUser, 'sanctum')
            ->postJson("/api/v1/konsultasi/{$konsultasi->id}/selesaikan", [
                'diagnosis' => 'Influenza',
                'resep' => 'Paracetamol 500mg x 3 hari',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Konsultasi berhasil diselesaikan',
        ]);

        $this->assertDatabaseHas('consultations', [
            'id' => $konsultasi->id,
            'status' => 'closed',
        ]);
    }

    /**
     * Test - Pasien tidak bisa accept konsultasi
     */
    public function test_patient_cannot_accept_consultation()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->postJson("/api/v1/konsultasi/{$this->konsultasi->id}/terima");

        $response->assertStatus(403);
    }

    /**
     * Test - Dokter tidak bisa terima konsultasi milik dokter lain
     */
    public function test_doctor_cannot_accept_other_doctor_consultation()
    {
        /** @var User $otherDokterUser */
        $otherDokterUser = User::factory()->create(['role' => 'dokter']);
        $otherDokter = Dokter::factory()->create(['user_id' => $otherDokterUser->id]);

        $konsultasi = Konsultasi::factory()->create([
            'patient_id' => $this->pasien->id,
            'doctor_id' => $this->dokter->id,
        ]);

        $response = $this->actingAs($otherDokterUser, 'sanctum')
            ->postJson("/api/v1/konsultasi/{$konsultasi->id}/terima");

        $response->assertStatus(403);
    }

    /**
     * Test - Pasien tidak bisa update konsultasi yang sudah diterima
     */
    public function test_patient_cannot_update_accepted_consultation()
    {
        $konsultasi = Konsultasi::factory()->create([
            'patient_id' => $this->pasien->id,
            'doctor_id' => $this->dokter->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->putJson("/api/v1/konsultasi/{$konsultasi->id}", [
                'keluhan' => 'Demam sangat tinggi',
            ]);

        $response->assertStatus(403);
    }
}
