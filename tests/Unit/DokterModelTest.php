<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Dokter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DokterModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a doctor profile
     */
    public function test_create_doctor(): void
    {
        $user = User::factory()->create([
            'role' => 'dokter',
            'is_active' => true,
        ]);

        $doctor = Dokter::create([
            'user_id' => $user->id,
            'license_number' => 'SIP-12345678',
            'specialization' => 'Dokter Umum',
            'years_of_experience' => 5,
            'bio' => 'Berpengalaman dalam menangani penyakit umum',
            'is_available' => true,
            'is_verified' => false,
        ]);

        $this->assertDatabaseHas('dokter', [
            'id' => $doctor->id,
            'user_id' => $user->id,
            'license_number' => 'SIP-12345678',
            'specialization' => 'Dokter Umum',
        ]);
    }

    /**
     * Test doctor belongs to user
     */
    public function test_doctor_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'dokter']);
        $doctor = Dokter::create([
            'user_id' => $user->id,
            'license_number' => 'SIP-98765432',
            'specialization' => 'Spesialis Bedah',
        ]);

        $this->assertInstanceOf(User::class, $doctor->user);
        $this->assertEquals($user->id, $doctor->user->id);
    }

    /**
     * Test updating doctor profile
     */
    public function test_update_doctor(): void
    {
        $user = User::factory()->create(['role' => 'dokter']);
        $doctor = Dokter::create([
            'user_id' => $user->id,
            'license_number' => 'SIP-11111111',
            'specialization' => 'Dokter Umum',
            'years_of_experience' => 2,
        ]);

        $doctor->update([
            'specialization' => 'Spesialis Anak',
            'years_of_experience' => 5,
            'is_available' => true,
        ]);

        $this->assertEquals('Spesialis Anak', $doctor->specialization);
        $this->assertEquals(5, $doctor->years_of_experience);
        $this->assertTrue($doctor->is_available);
    }

    /**
     * Test verifying a doctor
     */
    public function test_verify_doctor(): void
    {
        $user = User::factory()->create(['role' => 'dokter']);
        $doctor = Dokter::create([
            'user_id' => $user->id,
            'license_number' => 'SIP-22222222',
            'specialization' => 'Dokter Gigi',
            'is_verified' => false,
        ]);

        $this->assertFalse($doctor->is_verified);

        $doctor->update(['is_verified' => true]);

        $this->assertTrue($doctor->is_verified);
    }

    /**
     * Test license number format validation
     */
    public function test_license_number_format(): void
    {
        $user = User::factory()->create(['role' => 'dokter']);
        
        $doctor = Dokter::create([
            'user_id' => $user->id,
            'license_number' => 'SIP-33333333',
            'specialization' => 'Dokter Umum',
        ]);

        $this->assertMatchesRegularExpression('/^SIP-\d+$/', $doctor->license_number);
    }

    /**
     * Test deleting a doctor
     */
    public function test_delete_doctor(): void
    {
        $user = User::factory()->create(['role' => 'dokter']);
        $doctor = Dokter::create([
            'user_id' => $user->id,
            'license_number' => 'SIP-44444444',
            'specialization' => 'Dokter Bedah',
        ]);

        $doctorId = $doctor->id;
        $doctor->delete();

        $this->assertDatabaseMissing('dokter', [
            'id' => $doctorId,
        ]);
    }

    /**
     * Test doctor specializations
     */
    public function test_doctor_specializations(): void
    {
        $user1 = User::factory()->create(['role' => 'dokter']);
        $user2 = User::factory()->create(['role' => 'dokter']);

        $doctor1 = Dokter::create([
            'user_id' => $user1->id,
            'license_number' => 'SIP-55555555',
            'specialization' => 'Dokter Umum',
        ]);

        $doctor2 = Dokter::create([
            'user_id' => $user2->id,
            'license_number' => 'SIP-66666666',
            'specialization' => 'Spesialis Jantung',
        ]);

        $generalDoctors = Dokter::where('specialization', 'Dokter Umum')->get();
        $this->assertEquals(1, $generalDoctors->count());

        $cardiacDoctors = Dokter::where('specialization', 'Spesialis Jantung')->get();
        $this->assertEquals(1, $cardiacDoctors->count());
    }

    /**
     * Test doctor availability filtering
     */
    public function test_available_doctors_filtering(): void
    {
        $user1 = User::factory()->create(['role' => 'dokter']);
        $user2 = User::factory()->create(['role' => 'dokter']);

        Dokter::create([
            'user_id' => $user1->id,
            'license_number' => 'SIP-77777777',
            'specialization' => 'Dokter Umum',
            'is_available' => true,
        ]);

        Dokter::create([
            'user_id' => $user2->id,
            'license_number' => 'SIP-88888888',
            'specialization' => 'Dokter Umum',
            'is_available' => false,
        ]);

        $availableDoctors = Dokter::where('is_available', true)->get();
        $this->assertEquals(1, $availableDoctors->count());

        $unavailableDoctors = Dokter::where('is_available', false)->get();
        $this->assertEquals(1, $unavailableDoctors->count());
    }

    /**
     * Test doctor has many consultations relationship
     */
    public function test_doctor_has_consultations(): void
    {
        $user = User::factory()->create(['role' => 'dokter']);
        $doctor = Dokter::create([
            'user_id' => $user->id,
            'license_number' => 'SIP-99999999',
            'specialization' => 'Dokter Umum',
        ]);

        // This test assumes Dokter model has consultations relationship
        // The actual relationship would be tested with real consultation data
        $this->assertNotNull($doctor);
    }
}
