<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pasien;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasienModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test create patient
     */
    public function test_create_patient()
    {
        $user = User::factory()->create(['role' => 'pasien']);
        
        $pasien = Pasien::create([
            'user_id' => $user->id,
            'medical_record_number' => 'RM-2025-00001',
            'encrypted_nik' => 'encrypted_value',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Jl. Test 123',
            'phone_number' => '081234567890',
            'blood_type' => 'O',
        ]);

        $this->assertDatabaseHas('patients', [
            'user_id' => $user->id,
            'medical_record_number' => 'RM-2025-00001',
        ]);
    }

    /**
     * Test patient relationship with user
     */
    public function test_patient_belongs_to_user()
    {
        $user = User::factory()->create(['role' => 'pasien']);
        $pasien = Pasien::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($pasien->user->is($user));
    }

    /**
     * Test patient update
     */
    public function test_update_patient()
    {
        $pasien = Pasien::factory()->create();
        
        $pasien->update(['phone_number' => '081234567890']);

        $this->assertEquals('081234567890', $pasien->fresh()->phone_number);
    }

    /**
     * Test patient delete
     */
    public function test_delete_patient()
    {
        $pasien = Pasien::factory()->create();
        $id = $pasien->id;

        $pasien->delete();

        $this->assertDatabaseMissing('patients', ['id' => $id]);
    }

    /**
     * Test MRN format
     */
    public function test_mrn_format()
    {
        $pasien = Pasien::factory()->create([
            'medical_record_number' => 'RM-2025-00001'
        ]);

        $this->assertMatchesRegularExpression('/^RM-\d{4}-\d{5}$/', $pasien->medical_record_number);
    }
}
