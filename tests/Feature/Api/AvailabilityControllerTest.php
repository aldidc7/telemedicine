<?php

namespace Tests\Feature\Api;

use App\Models\DoctorAvailability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;
    private User $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = User::factory()->create(['role' => 'dokter']);
        $this->patient = User::factory()->create(['role' => 'pasien']);
    }

    // ===== GET DOCTOR AVAILABILITY =====

    /**
     * Test patient dapat lihat availability dokter
     */
    public function test_patient_can_view_doctor_availability()
    {
        // Create availability untuk dokter
        DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1, // Monday
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'slot_duration_minutes' => 30,
            'max_appointments_per_day' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/doctors/{$this->doctor->id}/availability");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'doctor_id',
            'schedule' => [
                [
                    'id',
                    'day_of_week',
                    'day_name',
                    'time_range',
                    'slot_duration',
                    'max_appointments',
                    'is_active',
                ],
            ],
        ]);
    }

    /**
     * Test cannot view non-existent doctor
     */
    public function test_cannot_view_non_existent_doctor_availability()
    {
        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/doctors/99999/availability');

        $response->assertStatus(404);
    }

    // ===== GET AVAILABLE SLOTS =====

    /**
     * Test get available slots for date range
     */
    public function test_get_available_slots()
    {
        DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1, // Monday
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ]);

        $startDate = Carbon::now()->addDays(1)->format('Y-m-d');
        $endDate = Carbon::now()->addDays(7)->format('Y-m-d');

        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/doctors/{$this->doctor->id}/available-slots?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'doctor_id',
            'date_range',
            'total_slots',
            'slots' => [
                [
                    'date',
                    'start_time',
                    'end_time',
                    'datetime',
                    'duration_minutes',
                ],
            ],
        ]);
    }

    /**
     * Test invalid date range validation
     */
    public function test_available_slots_requires_valid_dates()
    {
        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/doctors/' . $this->doctor->id . '/available-slots?start_date=invalid&end_date=invalid');

        $response->assertStatus(422);
    }

    /**
     * Test cannot book past dates
     */
    public function test_cannot_get_slots_for_past_dates()
    {
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');

        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/doctors/{$this->doctor->id}/available-slots?start_date={$yesterday}&end_date={$yesterday}");

        $response->assertStatus(422);
    }

    // ===== SET DOCTOR AVAILABILITY (Doctor only) =====

    /**
     * Test doctor dapat set availability
     */
    public function test_doctor_can_set_availability()
    {
        $response = $this->actingAs($this->doctor)
            ->postJson('/api/v1/doctors/availability', [
                'day_of_week' => 1,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'slot_duration_minutes' => 30,
                'max_appointments_per_day' => 20,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'availability' => [
                'id',
                'day_name',
                'time_range',
            ],
        ]);

        $this->assertDatabaseHas('doctor_availabilities', [
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'is_active' => true,
        ]);
    }

    /**
     * Test patient cannot set availability
     */
    public function test_patient_cannot_set_availability()
    {
        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/doctors/availability', [
                'day_of_week' => 1,
                'start_time' => '09:00',
                'end_time' => '17:00',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test invalid time validation
     */
    public function test_invalid_time_format_rejected()
    {
        $response = $this->actingAs($this->doctor)
            ->postJson('/api/v1/doctors/availability', [
                'day_of_week' => 1,
                'start_time' => 'invalid',
                'end_time' => '17:00',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test end_time harus lebih besar dari start_time
     */
    public function test_end_time_must_be_after_start_time()
    {
        $response = $this->actingAs($this->doctor)
            ->postJson('/api/v1/doctors/availability', [
                'day_of_week' => 1,
                'start_time' => '17:00',
                'end_time' => '09:00',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test update existing availability (upsert)
     */
    public function test_set_availability_updates_existing()
    {
        // Set initial
        $this->actingAs($this->doctor)
            ->postJson('/api/v1/doctors/availability', [
                'day_of_week' => 1,
                'start_time' => '09:00',
                'end_time' => '17:00',
            ]);

        // Update same day
        $response = $this->actingAs($this->doctor)
            ->postJson('/api/v1/doctors/availability', [
                'day_of_week' => 1,
                'start_time' => '10:00',
                'end_time' => '18:00',
            ]);

        $response->assertStatus(200);

        // Check hanya 1 record untuk Monday
        $this->assertEquals(
            1,
            DoctorAvailability::where('doctor_id', $this->doctor->id)
                ->where('day_of_week', 1)
                ->count()
        );
    }

    // ===== UPDATE AVAILABILITY STATUS =====

    /**
     * Test doctor dapat update availability status
     */
    public function test_doctor_can_update_availability_status()
    {
        $availability = DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->doctor)
            ->patchJson("/api/v1/doctors/availability/{$availability->id}", [
                'is_active' => false,
            ]);

        $response->assertStatus(200);
        $this->assertFalse($availability->fresh()->is_active);
    }

    /**
     * Test patient cannot update other's availability
     */
    public function test_patient_cannot_update_availability()
    {
        $availability = DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->patient)
            ->patchJson("/api/v1/doctors/availability/{$availability->id}", [
                'is_active' => false,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test doctor cannot update other doctor's availability
     */
    public function test_doctor_cannot_update_other_doctor_availability()
    {
        $otherDoctor = User::factory()->create(['role' => 'dokter']);

        $availability = DoctorAvailability::create([
            'doctor_id' => $otherDoctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->doctor)
            ->patchJson("/api/v1/doctors/availability/{$availability->id}", [
                'is_active' => false,
            ]);

        $response->assertStatus(404);
    }

    // ===== LIST AVAILABILITY =====

    /**
     * Test doctor dapat lihat semua availability-nya
     */
    public function test_doctor_can_list_own_availability()
    {
        DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 2,
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->doctor)
            ->getJson('/api/v1/doctors/availability/list');

        $response->assertStatus(200);
        $response->assertJsonPath('total', 2);
        $this->assertCount(2, $response['schedule']);
    }

    /**
     * Test patient cannot list availability
     */
    public function test_patient_cannot_list_availability()
    {
        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/doctors/availability/list');

        $response->assertStatus(403);
    }

    // ===== BULK SET AVAILABILITY =====

    /**
     * Test doctor dapat set availability untuk multiple hari sekaligus
     */
    public function test_doctor_can_bulk_set_availability()
    {
        $response = $this->actingAs($this->doctor)
            ->postJson('/api/v1/doctors/availability/bulk', [
                'schedule' => [
                    [
                        'day_of_week' => 1,
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                    ],
                    [
                        'day_of_week' => 2,
                        'start_time' => '10:00',
                        'end_time' => '18:00',
                    ],
                    [
                        'day_of_week' => 3,
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('count', 3);

        $this->assertEquals(
            3,
            DoctorAvailability::where('doctor_id', $this->doctor->id)->count()
        );
    }

    /**
     * Test bulk requires non-empty schedule
     */
    public function test_bulk_set_requires_schedule()
    {
        $response = $this->actingAs($this->doctor)
            ->postJson('/api/v1/doctors/availability/bulk', [
                'schedule' => [],
            ]);

        $response->assertStatus(422);
    }

    // ===== DELETE AVAILABILITY =====

    /**
     * Test doctor dapat delete availability
     */
    public function test_doctor_can_delete_availability()
    {
        $availability = DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->doctor)
            ->deleteJson("/api/v1/doctors/availability/{$availability->id}");

        $response->assertStatus(200);
        $this->assertTrue($availability->fresh()->trashed());
    }

    /**
     * Test patient cannot delete availability
     */
    public function test_patient_cannot_delete_availability()
    {
        $availability = DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->patient)
            ->deleteJson("/api/v1/doctors/availability/{$availability->id}");

        $response->assertStatus(403);
    }

    /**
     * Test cannot delete non-existent availability
     */
    public function test_cannot_delete_non_existent_availability()
    {
        $response = $this->actingAs($this->doctor)
            ->deleteJson('/api/v1/doctors/availability/99999');

        $response->assertStatus(404);
    }
}
