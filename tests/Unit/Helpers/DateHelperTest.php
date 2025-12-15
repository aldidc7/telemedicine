<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use App\Helpers\DateHelper;

/**
 * Unit Tests untuk DateHelper
 * 
 * Test semua date/time operations untuk ensure accuracy
 * Coverage: 100%
 */
class DateHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Set fixed time untuk consistent testing
        Carbon::setTestNow('2025-12-16 10:00:00');
    }
    
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
    
    /**
     * Test get working hours start
     */
    public function test_get_working_hours_start(): void
    {
        $start = DateHelper::getWorkingHoursStart();
        $this->assertEquals(9, $start);
    }
    
    /**
     * Test get working hours end
     */
    public function test_get_working_hours_end(): void
    {
        $end = DateHelper::getWorkingHoursEnd();
        $this->assertEquals(17, $end);
    }
    
    /**
     * Test get slot duration
     */
    public function test_get_slot_duration(): void
    {
        $duration = DateHelper::getSlotDuration();
        $this->assertEquals(30, $duration);
    }
    
    /**
     * Test is working hours true
     */
    public function test_is_working_hours_true(): void
    {
        $time = Carbon::create(2025, 12, 16, 10, 0);  // 10:00 AM
        $this->assertTrue(DateHelper::isWorkingHours($time));
        
        $time = Carbon::create(2025, 12, 16, 9, 0);   // 9:00 AM (start)
        $this->assertTrue(DateHelper::isWorkingHours($time));
        
        $time = Carbon::create(2025, 12, 16, 16, 59); // 4:59 PM
        $this->assertTrue(DateHelper::isWorkingHours($time));
    }
    
    /**
     * Test is working hours false
     */
    public function test_is_working_hours_false(): void
    {
        $time = Carbon::create(2025, 12, 16, 8, 0);   // 8:00 AM (before start)
        $this->assertFalse(DateHelper::isWorkingHours($time));
        
        $time = Carbon::create(2025, 12, 16, 17, 0);  // 5:00 PM (at end, not working)
        $this->assertFalse(DateHelper::isWorkingHours($time));
        
        $time = Carbon::create(2025, 12, 16, 20, 0);  // 8:00 PM (after end)
        $this->assertFalse(DateHelper::isWorkingHours($time));
    }
    
    /**
     * Test get working day start
     */
    public function test_get_working_day_start(): void
    {
        $date = Carbon::create(2025, 12, 16, 14, 30);
        $start = DateHelper::getWorkingDayStart($date);
        
        $this->assertEquals(9, $start->hour);
        $this->assertEquals(0, $start->minute);
        $this->assertEquals(0, $start->second);
        $this->assertEquals(2025, $start->year);
        $this->assertEquals(12, $start->month);
        $this->assertEquals(16, $start->day);
    }
    
    /**
     * Test get working day end
     */
    public function test_get_working_day_end(): void
    {
        $date = Carbon::create(2025, 12, 16, 14, 30);
        $end = DateHelper::getWorkingDayEnd($date);
        
        $this->assertEquals(17, $end->hour);
        $this->assertEquals(0, $end->minute);
        $this->assertEquals(0, $end->second);
    }
    
    /**
     * Test is weekend true
     */
    public function test_is_weekend_true(): void
    {
        $saturday = Carbon::create(2025, 12, 20); // Saturday
        $this->assertTrue(DateHelper::isWeekend($saturday));
        
        $sunday = Carbon::create(2025, 12, 21);   // Sunday
        $this->assertTrue(DateHelper::isWeekend($sunday));
    }
    
    /**
     * Test is weekend false
     */
    public function test_is_weekend_false(): void
    {
        $monday = Carbon::create(2025, 12, 15);   // Monday
        $this->assertFalse(DateHelper::isWeekend($monday));
        
        $tuesday = Carbon::create(2025, 12, 16);  // Tuesday
        $this->assertFalse(DateHelper::isWeekend($tuesday));
        
        $wednesday = Carbon::create(2025, 12, 17);// Wednesday
        $this->assertFalse(DateHelper::isWeekend($wednesday));
        
        $thursday = Carbon::create(2025, 12, 18); // Thursday
        $this->assertFalse(DateHelper::isWeekend($thursday));
        
        $friday = Carbon::create(2025, 12, 19);   // Friday
        $this->assertFalse(DateHelper::isWeekend($friday));
    }
    
    /**
     * Test get next working day from weekday
     */
    public function test_get_next_working_day_from_weekday(): void
    {
        $monday = Carbon::create(2025, 12, 15);   // Monday
        $next = DateHelper::getNextWorkingDay($monday);
        
        $this->assertEquals(15, $next->day); // Same day, still working day
    }
    
    /**
     * Test get next working day from saturday
     */
    public function test_get_next_working_day_from_saturday(): void
    {
        $saturday = Carbon::create(2025, 12, 20); // Saturday
        $next = DateHelper::getNextWorkingDay($saturday);
        
        $this->assertEquals(22, $next->day); // Skip to Monday
    }
    
    /**
     * Test get next working day from sunday
     */
    public function test_get_next_working_day_from_sunday(): void
    {
        $sunday = Carbon::create(2025, 12, 21);   // Sunday
        $next = DateHelper::getNextWorkingDay($sunday);
        
        $this->assertEquals(22, $next->day); // Skip to Monday
    }
    
    /**
     * Test generate day slots
     */
    public function test_generate_day_slots(): void
    {
        $date = Carbon::create(2025, 12, 16);
        $slots = DateHelper::generateDaySlots($date);
        
        $this->assertIsArray($slots);
        $this->assertGreaterThan(0, count($slots));
        
        // First slot should be 9:00
        $this->assertEquals('09:00', $slots[0]);
        
        // Should have slots for 8 working hours with 30-min intervals = 16 slots
        $this->assertCount(16, $slots);
    }
    
    /**
     * Test generate day slots with custom duration
     */
    public function test_generate_day_slots_custom_duration(): void
    {
        $date = Carbon::create(2025, 12, 16);
        $slots = DateHelper::generateDaySlots($date, 60); // 1-hour slots
        
        // 8 hours / 1 hour = 8 slots
        $this->assertCount(8, $slots);
        $this->assertEquals('09:00', $slots[0]);
        $this->assertEquals('10:00', $slots[1]);
    }
    
    /**
     * Test get slot end time
     */
    public function test_get_slot_end_time(): void
    {
        $start = Carbon::create(2025, 12, 16, 10, 0);
        $end = DateHelper::getSlotEndTime($start, 30);
        
        $this->assertEquals(10, $end->hour);
        $this->assertEquals(30, $end->minute);
    }
    
    /**
     * Test get slot end time with custom duration
     */
    public function test_get_slot_end_time_custom_duration(): void
    {
        $start = Carbon::create(2025, 12, 16, 10, 0);
        $end = DateHelper::getSlotEndTime($start, 60);
        
        $this->assertEquals(11, $end->hour);
        $this->assertEquals(0, $end->minute);
    }
    
    /**
     * Test filter available slots
     */
    public function test_filter_available_slots(): void
    {
        $allSlots = ['09:00', '09:30', '10:00', '10:30', '11:00'];
        $bookedSlots = ['09:30', '11:00'];
        
        $available = DateHelper::filterAvailableSlots($allSlots, $bookedSlots);
        
        $this->assertContains('09:00', $available);
        $this->assertContains('10:00', $available);
        $this->assertContains('10:30', $available);
        $this->assertNotContains('09:30', $available);
        $this->assertNotContains('11:00', $available);
        $this->assertCount(3, $available);
    }
    
    /**
     * Test calculate age
     */
    public function test_calculate_age(): void
    {
        $birthDate = Carbon::create(2000, 1, 1);
        $age = DateHelper::calculateAge($birthDate);
        
        $this->assertEquals(25, $age);
    }
    
    /**
     * Test calculate age edge case (birthday today)
     */
    public function test_calculate_age_birthday_today(): void
    {
        $birthDate = Carbon::create(2000, 12, 16); // Birthday today
        $age = DateHelper::calculateAge($birthDate);
        
        $this->assertEquals(25, $age);
    }
    
    /**
     * Test is lansia false
     */
    public function test_is_lansia_false(): void
    {
        $birthDate = Carbon::create(2000, 1, 1); // 25 years old
        $this->assertFalse(DateHelper::isLansia($birthDate));
    }
    
    /**
     * Test is lansia true
     */
    public function test_is_lansia_true(): void
    {
        $birthDate = Carbon::create(1960, 1, 1); // 65 years old
        $this->assertTrue(DateHelper::isLansia($birthDate));
    }
    
    /**
     * Test is passed true
     */
    public function test_is_passed_true(): void
    {
        $pastTime = Carbon::now()->subHours(1);
        $this->assertTrue(DateHelper::isPassed($pastTime));
    }
    
    /**
     * Test is passed false
     */
    public function test_is_passed_false(): void
    {
        $futureTime = Carbon::now()->addHours(1);
        $this->assertFalse(DateHelper::isPassed($futureTime));
    }
    
    /**
     * Test is upcoming true
     */
    public function test_is_upcoming_true(): void
    {
        $upcomingTime = Carbon::now()->addMinutes(30);
        $this->assertTrue(DateHelper::isUpcoming($upcomingTime, 60));
    }
    
    /**
     * Test is upcoming false (too far)
     */
    public function test_is_upcoming_false_too_far(): void
    {
        $farFutureTime = Carbon::now()->addHours(2);
        $this->assertFalse(DateHelper::isUpcoming($farFutureTime, 60));
    }
    
    /**
     * Test get date range 7 days
     */
    public function test_get_date_range_7_days(): void
    {
        [$start, $end] = DateHelper::getDateRange('7_days');
        
        $this->assertInstanceOf(Carbon::class, $start);
        $this->assertInstanceOf(Carbon::class, $end);
        $this->assertEquals(7, $end->diffInDays($start));
    }
    
    /**
     * Test get date range 30 days
     */
    public function test_get_date_range_30_days(): void
    {
        [$start, $end] = DateHelper::getDateRange('30_days');
        
        $this->assertEquals(30, $end->diffInDays($start));
    }
    
    /**
     * Test format date
     */
    public function test_format_date(): void
    {
        $date = Carbon::create(2025, 12, 16);
        $formatted = DateHelper::formatDate($date);
        
        $this->assertStringContainsString('16', $formatted);
        $this->assertStringContainsString('2025', $formatted);
    }
    
    /**
     * Test format time
     */
    public function test_format_time(): void
    {
        $time = Carbon::create(2025, 12, 16, 14, 30);
        $formatted = DateHelper::formatTime($time);
        
        $this->assertEquals('14:30', $formatted);
    }
    
    /**
     * Test get time ago
     */
    public function test_get_time_ago(): void
    {
        $pastTime = Carbon::now()->subHours(1);
        $timeAgo = DateHelper::getTimeAgo($pastTime);
        
        $this->assertStringContainsString('hour', $timeAgo);
        $this->assertStringContainsString('ago', $timeAgo);
    }
}
