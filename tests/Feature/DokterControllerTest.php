<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Dokter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class DokterControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->dokterUser = User::factory()->create(['role' => 'dokter']);
        $this->dokter = Dokter::factory()->create([
            'user_id' => $this->dokterUser->id,
        ]);
    }

    /**
     * Test - Get semua dokter
     * GET /api/v1/dokter
     */
    public function test_get_all_doctors()
    {
        Dokter::factory()->count(5)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/dokter');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                '*' => [
                    'id',
                    'spesialisasi',
                    'nomor_praktik',
                    'tahun_kelulusan',
                    'is_tersedia',
                    'user' => ['id', 'name', 'email'],
                ],
            ],
        ]);
    }

    /**
     * Test - Filter dokter berdasarkan spesialisasi
     */
    public function test_get_doctors_by_specialty()
    {
        Dokter::factory()->create([
            'spesialisasi' => 'Kardiologi',
        ]);
        Dokter::factory()->create([
            'spesialisasi' => 'Neurologi',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/dokter?spesialisasi=Kardiologi');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data',
        ]);
    }

    /**
     * Test - Get detail dokter
     * GET /api/v1/dokter/{id}
     */
    public function test_get_doctor_detail()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/v1/dokter/{$this->dokter->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'id',
                'spesialisasi',
                'nomor_praktik',
                'user',
            ],
        ]);
    }

    /**
     * Test - Get dokter yang tidak ada
     */
    public function test_get_doctor_not_found()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/dokter/99999');

        $response->assertStatus(404);
    }

    /**
     * Test - Create dokter baru (admin only)
     * POST /api/v1/dokter
     */
    public function test_create_doctor_as_admin()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/v1/dokter', [
                'user_id' => $this->dokterUser->id,
                'spesialisasi' => 'Dermatologi',
                'nomor_praktik' => 'SIP987654',
                'tahun_kelulusan' => 2015,
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Dokter berhasil ditambahkan',
        ]);
    }

    /**
     * Test - Dokter lain tidak bisa create dokter
     */
    public function test_create_doctor_as_non_admin()
    {
        $response = $this->actingAs($this->dokterUser, 'sanctum')
            ->postJson('/api/v1/dokter', [
                'user_id' => $this->dokterUser->id,
                'spesialisasi' => 'Dermatologi',
                'nomor_praktik' => 'SIP987654',
                'tahun_kelulusan' => 2015,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test - Update dokter
     * PUT /api/v1/dokter/{id}
     */
    public function test_update_doctor()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/v1/dokter/{$this->dokter->id}", [
                'spesialisasi' => 'Oftalmologi',
                'tahun_kelulusan' => 2018,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Data dokter berhasil diperbarui',
        ]);

        $this->assertDatabaseHas('doctors', [
            'id' => $this->dokter->id,
            'spesialisasi' => 'Oftalmologi',
        ]);
    }

    /**
     * Test - Delete dokter
     * DELETE /api/v1/dokter/{id}
     */
    public function test_delete_doctor()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/v1/dokter/{$this->dokter->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Dokter berhasil dihapus',
        ]);

        $this->assertDatabaseMissing('doctors', [
            'id' => $this->dokter->id,
        ]);
    }

    /**
     * Test - Set ketersediaan dokter
     * PATCH /api/v1/dokter/{id}/ketersediaan
     */
    public function test_set_doctor_availability()
    {
        $response = $this->actingAs($this->dokterUser, 'sanctum')
            ->patchJson("/api/v1/dokter/{$this->dokter->id}/ketersediaan", [
                'is_tersedia' => false,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Status ketersediaan dokter berhasil diperbarui',
        ]);

        $this->assertDatabaseHas('doctors', [
            'id' => $this->dokter->id,
            'is_tersedia' => false,
        ]);
    }

    /**
     * Test - Dokter tidak bisa update dokter lain
     */
    public function test_doctor_cannot_update_other_doctor()
    {
        $otherDokterUser = User::factory()->create(['role' => 'dokter']);
        $otherDokter = Dokter::factory()->create([
            'user_id' => $otherDokterUser->id,
        ]);

        $response = $this->actingAs($this->dokterUser, 'sanctum')
            ->patchJson("/api/v1/dokter/{$otherDokter->id}/ketersediaan", [
                'is_tersedia' => false,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test - Create dokter dengan data invalid
     */
    public function test_create_doctor_invalid_data()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/v1/dokter', [
                'user_id' => 99999,
                'spesialisasi' => '',
                'tahun_kelulusan' => 'invalid',
            ]);

        $response->assertStatus(422);
    }
}
