<?php

namespace App\Console\Commands;

use App\Services\AdvancedCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Cache Warming Command
 * 
 * Pre-populates cache with frequently accessed data
 * Reduces first-hit latency and database load
 */
class WarmCacheCommand extends Command
{
    protected $signature = 'cache:warm {--force : Force re-warming of cache}';
    protected $description = 'Warm application cache with frequently accessed data';

    protected AdvancedCacheService $cacheService;

    public function __construct(AdvancedCacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    public function handle(): int
    {
        $this->info('🔥 Starting cache warming process...');
        $startTime = now();

        try {
            // Check if cache is already warm
            if (!$this->option('force') && Cache::has('cache_warmed_at')) {
                $lastWarmed = Cache::get('cache_warmed_at');
                $minutesAgo = now()->diffInMinutes($lastWarmed);

                if ($minutesAgo < 60) {
                    $this->warn("⚠️  Cache was warmed {$minutesAgo} minutes ago. Use --force to re-warm.");
                    return Command::SUCCESS;
                }
            }

            $itemsWarmed = 0;

            // Warm doctor availability and slots
            $doctors = \App\Models\User::where('role', 'dokter')->get();
            $this->info("Warming cache for {$doctors->count()} doctors...");

            foreach ($doctors as $doctor) {
                $this->output->write('.');

                // Warm slots for next 7 days
                for ($i = 0; $i < 7; $i++) {
                    $date = Carbon::now()->addDays($i)->format('Y-m-d');
                    
                    $this->cacheService->getAvailableSlots(
                        $doctor->id,
                        $date,
                        fn() => $this->fetchSlots($doctor->id, $date)
                    );
                    
                    $itemsWarmed++;
                }

                // Warm doctor availability
                $this->cacheService->getDoctorAvailability(
                    $doctor->id,
                    fn() => $this->fetchAvailability($doctor->id)
                );
                $itemsWarmed++;

                // Warm doctor statistics
                $this->cacheService->getDoctorStatistics(
                    $doctor->id,
                    fn() => $this->fetchStatistics($doctor->id)
                );
                $itemsWarmed++;

                // Warm doctor rating average
                $this->cacheService->getDoctorRatingAverage(
                    $doctor->id,
                    fn() => $this->fetchRatingAverage($doctor->id)
                );
                $itemsWarmed++;
            }

            $this->newLine();

            // Warm user profiles for active users
            $activeUsers = \App\Models\User::where('active', true)
                ->limit(100)
                ->get();

            $this->info("Warming {$activeUsers->count()} active user profiles...");

            foreach ($activeUsers as $user) {
                $this->output->write('.');

                $this->cacheService->getUserProfile(
                    $user->id,
                    fn() => $user->toArray()
                );

                $itemsWarmed++;
            }

            $this->newLine();

            // Record warming timestamp
            Cache::put('cache_warmed_at', now(), 86400 * 7); // 7 days

            $duration = now()->diffInSeconds($startTime);

            $this->info("✅ Cache warming completed successfully!");
            $this->info("📊 Items warmed: {$itemsWarmed}");
            $this->info("⏱️  Duration: {$duration} seconds");
            $this->info("🎯 Cache size estimate: " . $this->cacheService->getCacheStats()['size_estimate']);

            Log::info("Cache warming command completed", [
                'items_warmed' => $itemsWarmed,
                'duration_seconds' => $duration,
                'timestamp' => now()
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Cache warming failed: {$e->getMessage()}");
            Log::error("Cache warming failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    private function fetchSlots(int $doctorId, string $date): array
    {
        // Fetch available appointment slots for doctor on date
        $doctor = \App\Models\User::find($doctorId);
        
        if (!$doctor) {
            return [];
        }

        $bookedAppointments = \App\Models\Appointment::where('doctor_id', $doctorId)
            ->whereDate('scheduled_at', $date)
            ->pluck('scheduled_at')
            ->map(fn($time) => $time->format('H:i'))
            ->toArray();

        // Generate all possible slots (30-minute intervals)
        $slots = [];
        for ($hour = 9; $hour < 17; $hour++) {
            foreach ([0, 30] as $minute) {
                $slot = sprintf('%02d:%02d', $hour, $minute);
                if (!in_array($slot, $bookedAppointments)) {
                    $slots[] = $slot;
                }
            }
        }

        return $slots;
    }

    private function fetchAvailability(int $doctorId): array
    {
        $doctor = \App\Models\User::find($doctorId);
        
        if (!$doctor) {
            return [];
        }

        $upcomingAppointments = \App\Models\Appointment::where('doctor_id', $doctorId)
            ->where('scheduled_at', '>=', now())
            ->count();

        return [
            'doctor_id' => $doctorId,
            'upcoming_appointments' => $upcomingAppointments,
            'working_hours' => '09:00-17:00',
            'available_slots_next_7_days' => $this->countAvailableSlots($doctorId),
            'last_updated' => now(),
        ];
    }

    private function fetchStatistics(int $doctorId): array
    {
        $doctor = \App\Models\User::find($doctorId);
        
        if (!$doctor) {
            return [];
        }

        $totalAppointments = \App\Models\Appointment::where('doctor_id', $doctorId)->count();
        $completedAppointments = \App\Models\Appointment::where('doctor_id', $doctorId)
            ->where('status', 'completed')
            ->count();
        $todayAppointments = \App\Models\Appointment::where('doctor_id', $doctorId)
            ->whereDate('scheduled_at', now())
            ->count();

        return [
            'doctor_id' => $doctorId,
            'total_appointments' => $totalAppointments,
            'completed_appointments' => $completedAppointments,
            'today_appointments' => $todayAppointments,
            'completion_rate' => $totalAppointments > 0 
                ? ($completedAppointments / $totalAppointments) * 100 
                : 0,
        ];
    }

    private function fetchRatingAverage(int $doctorId): float
    {
        $avgRating = \App\Models\Rating::where('doctor_id', $doctorId)
            ->avg('rating');

        return $avgRating ?? 0;
    }

    private function countAvailableSlots(int $doctorId): int
    {
        $count = 0;
        
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->addDays($i)->format('Y-m-d');
            $slots = $this->fetchSlots($doctorId, $date);
            $count += count($slots);
        }

        return $count;
    }
}
