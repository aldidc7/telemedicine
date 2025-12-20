<?php

namespace Tests\Unit\Services;

use App\Models\DoctorAvailability;
use App\Models\User;
use App\Services\Doctor\DoctorAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private DoctorAvailabilityService $service;
    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DoctorAvailabilityService::class);
        $this->doctor = User::factory()->create(['role' => 'dokter']);
    }

    /**
     * Test set availability
     */
    public function test_set_availability()
    {
        $availability = $this->service->setAvailability($this->doctor->id, [
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
        ]);

        $this->assertEquals($this->doctor->id, $availability->doctor_id);
        $this->assertEquals(1, $availability->day_of_week);
        $this->assertEquals('09:00', $availability->start_time);
        $this->assertTrue($availability->is_active);
    }

    /**
     * Test invalid time format throws exception
     */
    public function test_invalid_time_format_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->setAvailability($this->doctor->id, [
            'day_of_week' => 1,
            'start_time' => 'invalid',
            'end_time' => '17:00',
        ]);
    }

    /**
     * Test bulk set availability
     */
    public function test_bulk_set_availability()
    {
        $count = $this->service->bulkSetAvailability($this->doctor->id, [
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
        ]);

        $this->assertEquals(2, $count);
        $this->assertEquals(2, DoctorAvailability::where('doctor_id', $this->doctor->id)->count());
    }

    /**
     * Test get availability
     */
    public function test_get_availability()
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
            'is_active' => false,
        ]);

        $availability = $this->service->getAvailability($this->doctor->id, $onlyActive = true);

        $this->assertCount(1, $availability);
        $this->assertTrue($availability[0]->is_active);
    }

    /**
     * Test get availability for specific day
     */
    public function test_get_availability_for_day()
    {
        $avail = DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $result = $this->service->getAvailabilityForDay($this->doctor->id, 1);

        $this->assertNotNull($result);
        $this->assertEquals($avail->id, $result->id);
    }

    /**
     * Test get available slots for date range
     */
    public function test_get_available_slots()
    {
        DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(7);

        $slots = $this->service->getAvailableSlots($this->doctor->id, $startDate, $endDate);

        $this->assertIsArray($slots);
        // Should have slots for at least one Monday
        $this->assertGreaterThanOrEqual(1, count($slots));
    }

    /**
     * Test toggle availability
     */
    public function test_toggle_availability()
    {
        $avail = DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $this->service->toggleAvailability($avail->id, $this->doctor->id, false);

        $this->assertFalse($avail->fresh()->is_active);
    }

    /**
     * Test toggle unauthorized doctor throws exception
     */
    public function test_toggle_unauthorized_throws_exception()
    {
        $otherDoctor = User::factory()->create(['role' => 'dokter']);

        $avail = DoctorAvailability::create([
            'doctor_id' => $otherDoctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->service->toggleAvailability($avail->id, $this->doctor->id, false);
    }

    /**
     * Test delete availability
     */
    public function test_delete_availability()
    {
        $avail = DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $this->service->deleteAvailability($avail->id, $this->doctor->id);

        $this->assertTrue($avail->fresh()->trashed());
    }

    /**
     * Test is available for specific datetime
     */
    public function test_is_available_for_datetime()
    {
        DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        // Create a Monday at 10:00 AM
        $monday = Carbon::now()->next(Carbon::MONDAY)->setHour(10);

        $this->assertTrue($this->service->isAvailable($this->doctor->id, $monday));
    }

    /**
     * Test not available outside business hours
     */
    public function test_not_available_outside_hours()
    {
        DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        // Monday at 8:00 AM (before start time)
        $monday = Carbon::now()->next(Carbon::MONDAY)->setHour(8);

        $this->assertFalse($this->service->isAvailable($this->doctor->id, $monday));
    }

    /**
     * Test get statistics
     */
    public function test_get_statistics()
    {
        DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 2,
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
            'slot_duration' => 30,
            'is_active' => true,
        ]);

        $stats = $this->service->getStatistics($this->doctor->id);

        $this->assertEquals(2, $stats['total_days']);
        $this->assertEquals(16, $stats['total_hours_per_week']); // 8 + 8 hours
        $this->assertEquals(2, $stats['active_count']);
    }
}
