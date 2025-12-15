<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\PesanChat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesanChatControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $pasienUser;
    private User $dokterUser;
    private Pasien $pasien;
    private Dokter $dokter;
    private Konsultasi $konsultasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pasienUser = User::factory()->create(['role' => 'pasien']);
        $this->dokterUser = User::factory()->create(['role' => 'dokter']);

        $this->pasien = Pasien::factory()->create(['user_id' => $this->pasienUser->id]);
        $this->dokter = Dokter::factory()->create(['user_id' => $this->dokterUser->id]);

        $this->konsultasi = Konsultasi::factory()->create([
            'patient_id' => $this->pasien->id,
            'doctor_id' => $this->dokter->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test - Get semua pesan dalam konsultasi
     * GET /api/v1/pesan-chat/{konsultasi_id}
     */
    public function test_get_consultation_messages()
    {
        PesanChat::factory()->count(5)->create([
            'consultation_id' => $this->konsultasi->id,
        ]);

        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->getJson("/api/v1/pesan-chat/{$this->konsultasi->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                '*' => [
                    'id',
                    'pesan',
                    'tipe_pengirim',
                    'read_at',
                    'created_at',
                ],
            ],
        ]);
    }

    /**
     * Test - Get pesan dari konsultasi yang tidak ada
     */
    public function test_get_messages_consultation_not_found()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->getJson('/api/v1/pesan-chat/99999');

        $response->assertStatus(404);
    }

    /**
     * Test - Get detail pesan
     * GET /api/v1/pesan-chat/detail/{id}
     */
    public function test_get_message_detail()
    {
        $pesan = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
        ]);

        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->getJson("/api/v1/pesan-chat/detail/{$pesan->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'id',
                'pesan',
                'tipe_pengirim',
                'read_at',
            ],
        ]);
    }

    /**
     * Test - Send pesan ke konsultasi
     * POST /api/v1/pesan-chat
     */
    public function test_send_message()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->postJson('/api/v1/pesan-chat', [
                'consultation_id' => $this->konsultasi->id,
                'pesan' => 'Dokter, saya masih merasa demam',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Pesan berhasil dikirim',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'consultation_id' => $this->konsultasi->id,
            'pesan' => 'Dokter, saya masih merasa demam',
            'tipe_pengirim' => 'pasien',
        ]);
    }

    /**
     * Test - Dokter send pesan
     */
    public function test_doctor_send_message()
    {
        $response = $this->actingAs($this->dokterUser, 'sanctum')
            ->postJson('/api/v1/pesan-chat', [
                'consultation_id' => $this->konsultasi->id,
                'pesan' => 'Silakan minum obat yang telah saya resepkan',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('chat_messages', [
            'consultation_id' => $this->konsultasi->id,
            'tipe_pengirim' => 'dokter',
        ]);
    }

    /**
     * Test - Send pesan dengan konsultasi yang tidak ada
     */
    public function test_send_message_invalid_consultation()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->postJson('/api/v1/pesan-chat', [
                'consultation_id' => 99999,
                'pesan' => 'Test message',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test - Send pesan kosong
     */
    public function test_send_empty_message()
    {
        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->postJson('/api/v1/pesan-chat', [
                'consultation_id' => $this->konsultasi->id,
                'pesan' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['pesan']);
    }

    /**
     * Test - Update pesan
     * PUT /api/v1/pesan-chat/{id}
     */
    public function test_update_message()
    {
        $pesan = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
            'tipe_pengirim' => 'pasien',
        ]);

        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->putJson("/api/v1/pesan-chat/{$pesan->id}", [
                'pesan' => 'Pesan yang sudah diubah',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Pesan berhasil diperbarui',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'id' => $pesan->id,
            'pesan' => 'Pesan yang sudah diubah',
        ]);
    }

    /**
     * Test - Pengguna lain tidak bisa edit pesan
     */
    public function test_user_cannot_update_other_user_message()
    {
        $pesan = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
            'tipe_pengirim' => 'pasien',
        ]);

        $response = $this->actingAs($this->dokterUser, 'sanctum')
            ->putJson("/api/v1/pesan-chat/{$pesan->id}", [
                'pesan' => 'Ubah pesan milik orang lain',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test - Delete pesan
     * DELETE /api/v1/pesan-chat/{id}
     */
    public function test_delete_message()
    {
        $pesan = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
            'tipe_pengirim' => 'pasien',
        ]);

        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->deleteJson("/api/v1/pesan-chat/{$pesan->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Pesan berhasil dihapus',
        ]);

        $this->assertDatabaseMissing('chat_messages', [
            'id' => $pesan->id,
        ]);
    }

    /**
     * Test - Mark pesan sebagai terbaca
     * PATCH /api/v1/pesan-chat/{id}/baca
     */
    public function test_mark_message_as_read()
    {
        $pesan = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->patchJson("/api/v1/pesan-chat/{$pesan->id}/baca");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Pesan berhasil ditandai sudah dibaca',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'id' => $pesan->id,
            'read_at' => now(),
        ]);
    }

    /**
     * Test - Get jumlah pesan yang belum dibaca
     * GET /api/v1/pesan-chat/{konsultasi_id}/belum-dibaca
     */
    public function test_get_unread_message_count()
    {
        PesanChat::factory()->count(3)->create([
            'consultation_id' => $this->konsultasi->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->getJson("/api/v1/pesan-chat/{$this->konsultasi->id}/belum-dibaca");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'total_belum_dibaca',
            ],
        ]);
    }

    /**
     * Test - Pasien tidak bisa akses pesan dari konsultasi pasien lain
     */
    public function test_patient_cannot_access_other_patient_messages()
    {
        $otherPasienUser = User::factory()->create(['role' => 'pasien']);
        $otherPasien = Pasien::factory()->create(['user_id' => $otherPasienUser->id]);

        $otherKonsultasi = Konsultasi::factory()->create([
            'patient_id' => $otherPasien->id,
            'doctor_id' => $this->dokter->id,
        ]);

        $response = $this->actingAs($this->pasienUser, 'sanctum')
            ->getJson("/api/v1/pesan-chat/{$otherKonsultasi->id}");

        $response->assertStatus(403);
    }
}
