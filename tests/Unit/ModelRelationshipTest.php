<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\PesanChat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test - User memiliki many Pasien
     */
    public function test_user_has_pasien()
    {
        $user = User::factory()->create(['role' => 'pasien']);
        $pasien = Pasien::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->pasien()->exists());
        $this->assertEquals($pasien->id, $user->pasien->id);
    }

    /**
     * Test - User memiliki many Dokter
     */
    public function test_user_has_dokter()
    {
        $user = User::factory()->create(['role' => 'dokter']);
        $dokter = Dokter::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->dokter()->exists());
        $this->assertEquals($dokter->id, $user->dokter->id);
    }

    /**
     * Test - Pasien memiliki many Konsultasi
     */
    public function test_pasien_has_many_consultations()
    {
        $pasien = Pasien::factory()->create();
        $konsultasi = Konsultasi::factory()->count(3)->create([
            'patient_id' => $pasien->id,
        ]);

        $this->assertEquals(3, $pasien->konsultasi()->count());
        $this->assertTrue($pasien->konsultasi()->exists());
    }

    /**
     * Test - Dokter memiliki many Konsultasi
     */
    public function test_dokter_has_many_consultations()
    {
        $dokter = Dokter::factory()->create();
        $konsultasi = Konsultasi::factory()->count(2)->create([
            'doctor_id' => $dokter->id,
        ]);

        $this->assertEquals(2, $dokter->konsultasi()->count());
    }

    /**
     * Test - Konsultasi belongs to Pasien
     */
    public function test_consultation_belongs_to_pasien()
    {
        $pasien = Pasien::factory()->create();
        $konsultasi = Konsultasi::factory()->create(['patient_id' => $pasien->id]);

        $this->assertEquals($pasien->id, $konsultasi->pasien->id);
    }

    /**
     * Test - Konsultasi belongs to Dokter
     */
    public function test_consultation_belongs_to_dokter()
    {
        $dokter = Dokter::factory()->create();
        $konsultasi = Konsultasi::factory()->create(['doctor_id' => $dokter->id]);

        $this->assertEquals($dokter->id, $konsultasi->dokter->id);
    }

    /**
     * Test - Konsultasi has many PesanChat
     */
    public function test_consultation_has_many_messages()
    {
        $konsultasi = Konsultasi::factory()->create();
        $pesan = PesanChat::factory()->count(5)->create([
            'consultation_id' => $konsultasi->id,
        ]);

        $this->assertEquals(5, $konsultasi->pesan_chat()->count());
    }

    /**
     * Test - PesanChat belongs to Konsultasi
     */
    public function test_message_belongs_to_consultation()
    {
        $konsultasi = Konsultasi::factory()->create();
        $pesan = PesanChat::factory()->create([
            'consultation_id' => $konsultasi->id,
        ]);

        $this->assertEquals($konsultasi->id, $pesan->konsultasi->id);
    }

    /**
     * Test - Get unread messages count
     */
    public function test_get_unread_messages_count()
    {
        $konsultasi = Konsultasi::factory()->create();

        PesanChat::factory()->count(3)->create([
            'consultation_id' => $konsultasi->id,
            'read_at' => null,
        ]);

        PesanChat::factory()->count(2)->create([
            'consultation_id' => $konsultasi->id,
            'read_at' => now(),
        ]);

        $unreadCount = $konsultasi->pesan_chat()
            ->where('read_at', false)
            ->count();

        $this->assertEquals(3, $unreadCount);
    }

    /**
     * Test - Get konsultasi by status
     */
    public function test_get_consultations_by_status()
    {
        Konsultasi::factory()->create(['status' => 'pending']);
        Konsultasi::factory()->create(['status' => 'active']);
        Konsultasi::factory()->create(['status' => 'closed']);

        $accepted = Konsultasi::where('status', 'active')->count();

        $this->assertEquals(1, $accepted);
    }

    /**
     * Test - Get dokter by specialty
     */
    public function test_get_doctors_by_specialty()
    {
        Dokter::factory()->create(['spesialisasi' => 'Kardiologi']);
        Dokter::factory()->create(['spesialisasi' => 'Kardiologi']);
        Dokter::factory()->create(['spesialisasi' => 'Neurologi']);

        $kardiologi = Dokter::where('spesialisasi', 'Kardiologi')->count();

        $this->assertEquals(2, $kardiologi);
    }

    /**
     * Test - Get available doctors
     */
    public function test_get_available_doctors()
    {
        Dokter::factory()->count(3)->create(['is_tersedia' => true]);
        Dokter::factory()->count(2)->create(['is_tersedia' => false]);

        $available = Dokter::where('is_tersedia', true)->count();

        $this->assertEquals(3, $available);
    }
}
