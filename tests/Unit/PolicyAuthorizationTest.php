<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\PesanChat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test - Admin dapat melihat semua pasien
     */
    public function test_admin_can_view_all_patients()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pasien = Pasien::factory()->create();

        $this->assertTrue($admin->can('view', $pasien));
    }

    /**
     * Test - Pasien dapat melihat datanya sendiri
     */
    public function test_patient_can_view_own_data()
    {
        $user = User::factory()->create(['role' => 'pasien']);
        $pasien = Pasien::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('view', $pasien));
    }

    /**
     * Test - Pasien tidak dapat melihat data pasien lain
     */
    public function test_patient_cannot_view_other_patient_data()
    {
        $user1 = User::factory()->create(['role' => 'pasien']);
        $user2 = User::factory()->create(['role' => 'pasien']);

        $pasien1 = Pasien::factory()->create(['user_id' => $user1->id]);
        $pasien2 = Pasien::factory()->create(['user_id' => $user2->id]);

        $this->assertFalse($user1->can('view', $pasien2));
    }

    /**
     * Test - Admin dapat update dokter
     */
    public function test_admin_can_update_doctor()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dokter = Dokter::factory()->create();

        $this->assertTrue($admin->can('update', $dokter));
    }

    /**
     * Test - Dokter dapat update data diri sendiri
     */
    public function test_doctor_can_update_own_data()
    {
        $user = User::factory()->create(['role' => 'dokter']);
        $dokter = Dokter::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('update', $dokter));
    }

    /**
     * Test - Dokter tidak dapat update dokter lain
     */
    public function test_doctor_cannot_update_other_doctor()
    {
        $user1 = User::factory()->create(['role' => 'dokter']);
        $user2 = User::factory()->create(['role' => 'dokter']);

        $dokter1 = Dokter::factory()->create(['user_id' => $user1->id]);
        $dokter2 = Dokter::factory()->create(['user_id' => $user2->id]);

        $this->assertFalse($user1->can('update', $dokter2));
    }

    /**
     * Test - Admin dapat accept konsultasi (override)
     */
    public function test_admin_can_accept_consultation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $konsultasi = Konsultasi::factory()->create(['status' => 'pending']);

        $this->assertTrue($admin->can('accept', $konsultasi));
    }

    /**
     * Test - Dokter yang ditugaskan dapat accept konsultasi
     */
    public function test_assigned_doctor_can_accept_consultation()
    {
        $user = User::factory()->create(['role' => 'dokter']);
        $dokter = Dokter::factory()->create(['user_id' => $user->id]);
        $konsultasi = Konsultasi::factory()->create([
            'doctor_id' => $dokter->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($user->can('accept', $konsultasi));
    }

    /**
     * Test - Dokter lain tidak dapat accept konsultasi
     */
    public function test_other_doctor_cannot_accept_consultation()
    {
        $user1 = User::factory()->create(['role' => 'dokter']);
        $user2 = User::factory()->create(['role' => 'dokter']);

        $dokter1 = Dokter::factory()->create(['user_id' => $user1->id]);
        $dokter2 = Dokter::factory()->create(['user_id' => $user2->id]);

        $konsultasi = Konsultasi::factory()->create([
            'doctor_id' => $dokter1->id,
            'status' => 'pending',
        ]);

        $this->assertFalse($user2->can('accept', $konsultasi));
    }

    /**
     * Test - Pasien tidak dapat accept konsultasi
     */
    public function test_patient_cannot_accept_consultation()
    {
        $user = User::factory()->create(['role' => 'pasien']);
        $konsultasi = Konsultasi::factory()->create(['status' => 'pending']);

        $this->assertFalse($user->can('accept', $konsultasi));
    }

    /**
     * Test - Pasien yang membuat konsultasi dapat update
     */
    public function test_patient_can_update_own_consultation()
    {
        $user = User::factory()->create(['role' => 'pasien']);
        $pasien = Pasien::factory()->create(['user_id' => $user->id]);
        $konsultasi = Konsultasi::factory()->create([
            'patient_id' => $pasien->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($user->can('update', $konsultasi));
    }

    /**
     * Test - Pasien tidak dapat update konsultasi yang sudah diterima
     */
    public function test_patient_cannot_update_accepted_consultation()
    {
        $user = User::factory()->create(['role' => 'pasien']);
        $pasien = Pasien::factory()->create(['user_id' => $user->id]);
        $konsultasi = Konsultasi::factory()->create([
            'patient_id' => $pasien->id,
            'status' => 'active',
        ]);

        $this->assertFalse($user->can('update', $konsultasi));
    }

    /**
     * Test - Pengirim pesan dapat delete pesan sendiri
     */
    public function test_message_sender_can_delete_message()
    {
        $pasienUser = User::factory()->create(['role' => 'pasien']);
        $pasien = Pasien::factory()->create(['user_id' => $pasienUser->id]);
        $konsultasi = Konsultasi::factory()->create(['patient_id' => $pasien->id]);

        $pesan = PesanChat::factory()->create([
            'consultation_id' => $konsultasi->id,
            'tipe_pengirim' => 'pasien',
        ]);

        $this->assertTrue($pasienUser->can('delete', $pesan));
    }

    /**
     * Test - Penerima pesan tidak dapat delete pesan pengirim
     */
    public function test_non_sender_cannot_delete_message()
    {
        $pasienUser = User::factory()->create(['role' => 'pasien']);
        $dokterUser = User::factory()->create(['role' => 'dokter']);

        $pasien = Pasien::factory()->create(['user_id' => $pasienUser->id]);
        $konsultasi = Konsultasi::factory()->create(['patient_id' => $pasien->id]);

        $pesan = PesanChat::factory()->create([
            'consultation_id' => $konsultasi->id,
            'tipe_pengirim' => 'pasien',
        ]);

        $this->assertFalse($dokterUser->can('delete', $pesan));
    }

    /**
     * Test - Non-admin tidak dapat akses admin panel
     */
    public function test_non_admin_cannot_access_admin_panel()
    {
        $pasienUser = User::factory()->create(['role' => 'pasien']);

        $this->assertFalse($pasienUser->can('viewAny', User::class));
    }

    /**
     * Test - Admin dapat akses admin panel
     */
    public function test_admin_can_access_admin_panel()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->can('viewAny', User::class));
    }
}
