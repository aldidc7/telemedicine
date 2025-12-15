<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * Date & Time Helper Functions
 * 
 * Centralize semua date/time calculations untuk DRY principle
 * 
 * Updated: Session 5 - Code Refactoring Phase
 */
class DateHelper
{
    /**
     * Get working hours start time (dalam format HH:mm)
     * 
     * @return int Hour (0-23)
     */
    public static function getWorkingHoursStart(): int
    {
        return config('appointment.WORKING_HOURS.START', 9);
    }
    
    /**
     * Get working hours end time (dalam format HH:mm)
     * 
     * @return int Hour (0-23)
     */
    public static function getWorkingHoursEnd(): int
    {
        return config('appointment.WORKING_HOURS.END', 17);
    }
    
    /**
     * Get default slot duration dalam menit
     * 
     * @return int Durasi dalam menit
     */
    public static function getSlotDuration(): int
    {
        return config('appointment.SLOT_DURATION_MINUTES', 30);
    }
    
    /**
     * Check if given time is within working hours
     * 
     * @param Carbon $time Carbon instance dari waktu yang dicek
     * @return bool True jika dalam working hours
     */
    public static function isWorkingHours(Carbon $time): bool
    {
        $hour = $time->hour;
        $startHour = self::getWorkingHoursStart();
        $endHour = self::getWorkingHoursEnd();
        
        return $hour >= $startHour && $hour < $endHour;
    }
    
    /**
     * Get start of working day
     * 
     * @param Carbon|null $date Date untuk calculate (default today)
     * @return Carbon Start of working hours
     */
    public static function getWorkingDayStart(?Carbon $date = null): Carbon
    {
        $date = $date ?? Carbon::now();
        return $date->clone()->setHour(self::getWorkingHoursStart())->setMinute(0)->setSecond(0);
    }
    
    /**
     * Get end of working day
     * 
     * @param Carbon|null $date Date untuk calculate (default today)
     * @return Carbon End of working hours
     */
    public static function getWorkingDayEnd(?Carbon $date = null): Carbon
    {
        $date = $date ?? Carbon::now();
        return $date->clone()->setHour(self::getWorkingHoursEnd())->setMinute(0)->setSecond(0);
    }
    
    /**
     * Check if date is weekend
     * 
     * @param Carbon $date Carbon instance dari date
     * @return bool True jika weekend (Saturday=6, Sunday=7)
     */
    public static function isWeekend(Carbon $date): bool
    {
        // Carbon: Monday = 1, Sunday = 7
        return $date->dayOfWeek === Carbon::SATURDAY || $date->dayOfWeek === Carbon::SUNDAY;
    }
    
    /**
     * Get next available working day dari given date
     * 
     * Jika given date adalah weekend/holiday, return next working day
     * 
     * @param Carbon|null $date Carbon instance (default today)
     * @return Carbon Next working day
     */
    public static function getNextWorkingDay(?Carbon $date = null): Carbon
    {
        $date = $date ?? Carbon::now();
        
        // Skip weekends
        while (self::isWeekend($date)) {
            $date = $date->addDay();
        }
        
        return $date;
    }
    
    /**
     * Generate appointment slots untuk given date
     * 
     * Contoh: Jam 9-17 dengan durasi 30 menit
     * Result: [09:00, 09:30, 10:00, 10:30, ...]
     * 
     * @param Carbon|null $date Date untuk generate slots (default today)
     * @param int|null $durationMinutes Durasi slot (default dari config)
     * @return array Array of time strings (format: "HH:mm")
     */
    public static function generateDaySlots(?Carbon $date = null, ?int $durationMinutes = null): array
    {
        $date = $date ?? Carbon::now();
        $durationMinutes = $durationMinutes ?? self::getSlotDuration();
        
        $slots = [];
        $start = self::getWorkingDayStart($date);
        $end = self::getWorkingDayEnd($date);
        
        $current = $start->clone();
        
        while ($current < $end) {
            $slots[] = $current->format('H:i');
            $current->addMinutes($durationMinutes);
        }
        
        return $slots;
    }
    
    /**
     * Generate appointment slots sebagai Carbon instances
     * 
     * @param Carbon|null $date Date untuk generate slots
     * @param int|null $durationMinutes Durasi slot
     * @return array Array of Carbon instances
     */
    public static function generateDaySlotsTimes(?Carbon $date = null, ?int $durationMinutes = null): array
    {
        $date = $date ?? Carbon::now();
        $durationMinutes = $durationMinutes ?? self::getSlotDuration();
        
        $slots = [];
        $start = self::getWorkingDayStart($date);
        $end = self::getWorkingDayEnd($date);
        
        $current = $start->clone();
        
        while ($current < $end) {
            $slots[] = $current->clone();
            $current->addMinutes($durationMinutes);
        }
        
        return $slots;
    }
    
    /**
     * Get slot end time
     * 
     * Dengan start time dan duration, hitung end time
     * 
     * @param Carbon $startTime Start time
     * @param int|null $durationMinutes Durasi (default dari config)
     * @return Carbon End time
     */
    public static function getSlotEndTime(Carbon $startTime, ?int $durationMinutes = null): Carbon
    {
        $durationMinutes = $durationMinutes ?? self::getSlotDuration();
        return $startTime->clone()->addMinutes($durationMinutes);
    }
    
    /**
     * Get available slots untuk doctor di date tertentu
     * 
     * Exclude yang sudah di-booked
     * 
     * @param array $allSlots Semua slots untuk hari tersebut
     * @param array $bookedSlots Slots yang sudah di-booked
     * @return array Available slots
     */
    public static function filterAvailableSlots(array $allSlots, array $bookedSlots): array
    {
        return array_filter($allSlots, function ($slot) use ($bookedSlots) {
            return !in_array($slot, $bookedSlots);
        });
    }
    
    /**
     * Calculate umur dari tanggal lahir
     * 
     * @param Carbon $birthDate Tanggal lahir
     * @return int Umur dalam tahun
     */
    public static function calculateAge(Carbon $birthDate): int
    {
        return $birthDate->diffInYears(Carbon::now());
    }
    
    /**
     * Check if person sudah masuk kategori lansia
     * 
     * @param Carbon $birthDate Tanggal lahir
     * @return bool True jika sudah lansia
     */
    public static function isLansia(Carbon $birthDate): bool
    {
        $lansiaThreshold = config('application.LANSIA_AGE_THRESHOLD', 60);
        return self::calculateAge($birthDate) >= $lansiaThreshold;
    }
    
    /**
     * Get time range untuk period tertentu
     * 
     * Contoh: period='30_days' return [30 hari lalu, hari ini]
     * 
     * @param string $period Period key (7_days, 30_days, 90_days, year, all_time)
     * @return array [$startDate, $endDate] as Carbon instances
     */
    public static function getDateRange(string $period): array
    {
        $now = Carbon::now();
        
        return match ($period) {
            '7_days' => [
                $now->clone()->subDays(7)->startOfDay(),
                $now->clone()->endOfDay(),
            ],
            '30_days' => [
                $now->clone()->subDays(30)->startOfDay(),
                $now->clone()->endOfDay(),
            ],
            '90_days' => [
                $now->clone()->subDays(90)->startOfDay(),
                $now->clone()->endOfDay(),
            ],
            'year' => [
                $now->clone()->startOfYear(),
                $now->clone()->endOfYear(),
            ],
            'all_time' => [
                Carbon::now()->subYears(100)->startOfDay(),
                $now->clone()->endOfDay(),
            ],
            default => [
                $now->clone()->subDays(30)->startOfDay(),
                $now->clone()->endOfDay(),
            ],
        };
    }
    
    /**
     * Format date untuk human-readable display
     * 
     * @param Carbon $date Date yang ingin diformat
     * @param string $format Format string (default: 'd M Y')
     * @return string Formatted date string
     */
    public static function formatDate(Carbon $date, string $format = 'd M Y'): string
    {
        return $date->format($format);
    }
    
    /**
     * Format time untuk display
     * 
     * @param Carbon $time Time yang ingin diformat
     * @param string $format Format string (default: 'H:i')
     * @return string Formatted time string
     */
    public static function formatTime(Carbon $time, string $format = 'H:i'): string
    {
        return $time->format($format);
    }
    
    /**
     * Check if given time sudah passed
     * 
     * @param Carbon $time Time yang dicek
     * @return bool True jika sudah passed
     */
    public static function isPassed(Carbon $time): bool
    {
        return $time < Carbon::now();
    }
    
    /**
     * Check if given time sudah upcoming (dalam X menit)
     * 
     * @param Carbon $time Time yang dicek
     * @param int $minutesBefore Berapa menit sebelum waktu tersebut (default 60)
     * @return bool True jika upcoming dalam X menit
     */
    public static function isUpcoming(Carbon $time, int $minutesBefore = 60): bool
    {
        $now = Carbon::now();
        $upcomingTime = $time->clone()->subMinutes($minutesBefore);
        
        return $now >= $upcomingTime && $now < $time;
    }
    
    /**
     * Get human-readable time difference
     * 
     * Contoh: "2 hours ago", "in 30 minutes"
     * 
     * @param Carbon $date Date untuk dibanding dengan sekarang
     * @return string Human-readable time difference
     */
    public static function getTimeAgo(Carbon $date): string
    {
        return $date->diffForHumans();
    }
}
