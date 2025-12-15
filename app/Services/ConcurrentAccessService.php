<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Concurrent Access Control Service
 * 
 * Handles pessimistic locking untuk mencegah race conditions
 * dalam operasi critical yang melibatkan limited resources
 * (slots appointment, inventory, etc.)
 * 
 * Gunakan untuk:
 * - Appointment booking (prevent double-booking)
 * - Prescription management (prevent duplicate updates)
 * - Resource allocation (prevent over-allocation)
 */
class ConcurrentAccessService
{
    /**
     * Safely execute operation with pessimistic locking
     * 
     * Usage:
     * $service->withLock(Appointment::class, $appointmentId, function($model) {
     *     $model->update(['status' => 'confirmed']);
     * });
     */
    public function withLock(string $modelClass, $id, callable $operation, string $lockType = 'forUpdate')
    {
        return DB::transaction(function () use ($modelClass, $id, $operation, $lockType) {
            // Get model with lock
            $query = $modelClass::query();
            
            if ($lockType === 'forUpdate') {
                $model = $query->lockForUpdate()->findOrFail($id);
            } elseif ($lockType === 'sharedLock') {
                $model = $query->sharedLock()->findOrFail($id);
            } else {
                $model = $query->findOrFail($id);
            }

            // Execute operation
            return $operation($model);
        });
    }

    /**
     * Check appointment slot availability dengan locking
     * 
     * Prevents double-booking oleh checking & locking atomically
     */
    public function checkAndLockAppointmentSlot(
        int $doctorId,
        \DateTime $scheduledAt,
        array $excludeStatuses = ['completed', 'cancelled']
    ): bool {
        return DB::transaction(function () use ($doctorId, $scheduledAt, $excludeStatuses) {
            // Lock all appointments untuk doctor pada waktu tersebut
            $conflictingAppointment = \App\Models\Appointment::where('doctor_id', $doctorId)
                ->where('scheduled_at', $scheduledAt)
                ->whereNotIn('status', $excludeStatuses)
                ->lockForUpdate()
                ->exists();

            // Return true jika slot available (tidak ada konflik)
            return !$conflictingAppointment;
        });
    }

    /**
     * Book appointment dengan atomicity guarantee
     * 
     * Tiga kemungkinan outcome:
     * 1. Success - appointment created
     * 2. Slot taken - exception thrown
     * 3. Doctor unavailable - exception thrown
     */
    public function bookAppointmentAtomic(
        int $patientId,
        int $doctorId,
        string $scheduledAt,
        string $type,
        ?string $reason = null
    ): \App\Models\Appointment {
        return DB::transaction(function () use ($patientId, $doctorId, $scheduledAt, $type, $reason) {
            // 1. Lock doctor record
            $doctor = \App\Models\User::lockForUpdate()->findOrFail($doctorId);
            
            if ($doctor->role !== 'dokter' || $doctor->status !== 'active') {
                throw new \Exception('Doctor tidak tersedia');
            }

            // 2. Check & lock appointment slot
            $scheduledTime = \Carbon\Carbon::parse($scheduledAt);
            $hasConflict = \App\Models\Appointment::where('doctor_id', $doctorId)
                ->where('scheduled_at', $scheduledTime)
                ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                ->lockForUpdate()
                ->exists();

            if ($hasConflict) {
                throw new \Exception('Slot sudah di-booking oleh pasien lain. Silakan pilih waktu lain.');
            }

            // 3. Lock patient record
            $patient = \App\Models\User::lockForUpdate()->findOrFail($patientId);
            
            if ($patient->role !== 'pasien' || $patient->status !== 'active') {
                throw new \Exception('Pasien tidak valid');
            }

            // 4. Create appointment (this happens atomically within transaction)
            $appointment = \App\Models\Appointment::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'scheduled_at' => $scheduledTime,
                'type' => $type,
                'reason' => $reason,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            Log::info("Appointment created atomically", [
                'appointment_id' => $appointment->id,
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'scheduled_at' => $scheduledTime,
            ]);

            return $appointment;
        }, maxAttempts: 3);
    }

    /**
     * Update appointment status dengan concurrent safety
     * 
     * Ensures status transitions happen atomically
     */
    public function updateAppointmentStatusAtomic(
        int $appointmentId,
        string $newStatus,
        int $actorId,
        string $actorRole
    ): \App\Models\Appointment {
        return DB::transaction(function () use ($appointmentId, $newStatus, $actorId, $actorRole) {
            // Lock appointment
            $appointment = \App\Models\Appointment::lockForUpdate()->findOrFail($appointmentId);

            // Validate status transition
            $validTransitions = $this->getValidStatusTransitions($appointment->status, $actorRole);
            
            if (!in_array($newStatus, $validTransitions)) {
                throw new \Exception(
                    "Cannot transition from {$appointment->status} to {$newStatus} as {$actorRole}"
                );
            }

            // For confirmation, also lock doctor to prevent overbooking
            if ($newStatus === 'confirmed' && $actorRole === 'dokter') {
                \App\Models\User::lockForUpdate()->findOrFail($appointment->doctor_id);
            }

            // Update status
            $appointment->update([
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

            Log::info("Appointment status updated atomically", [
                'appointment_id' => $appointmentId,
                'from_status' => $appointment->getOriginal('status'),
                'to_status' => $newStatus,
                'actor' => $actorId,
            ]);

            return $appointment;
        }, maxAttempts: 3);
    }

    /**
     * Confirm multiple related operations atomically
     * 
     * Example: Update appointment + create prescription simultaneously
     */
    public function executeAtomicOperations(callable $operations): mixed
    {
        return DB::transaction(function () use ($operations) {
            return $operations();
        }, maxAttempts: 3);
    }

    /**
     * Retry mechanism untuk handling deadlocks
     * 
     * Automatic retry jika terjadi deadlock (max 3 kali)
     */
    public function executeWithRetry(callable $operation, int $maxAttempts = 3)
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts < $maxAttempts) {
            try {
                return DB::transaction($operation, maxAttempts: 1);
            } catch (\Exception $e) {
                $attempts++;
                $lastException = $e;

                // Check if it's a deadlock error
                if ($this->isDeadlockError($e)) {
                    Log::warning("Deadlock detected, retrying... (attempt {$attempts}/{$maxAttempts})", [
                        'error' => $e->getMessage(),
                    ]);
                    
                    if ($attempts < $maxAttempts) {
                        usleep(random_int(100000, 500000)); // 100-500ms backoff
                        continue;
                    }
                } else {
                    // Not a deadlock, throw immediately
                    throw $e;
                }
            }
        }

        throw $lastException ?? new \Exception('Failed after retries');
    }

    /**
     * Get valid status transitions per role
     */
    private function getValidStatusTransitions(string $currentStatus, string $role): array
    {
        $transitions = [
            'pending' => [
                'dokter' => ['confirmed', 'rejected', 'cancelled'],
                'pasien' => ['cancelled'],
                'admin' => ['confirmed', 'rejected', 'cancelled'],
            ],
            'confirmed' => [
                'dokter' => ['in_progress', 'cancelled'],
                'pasien' => ['cancelled'],
                'admin' => ['in_progress', 'cancelled'],
            ],
            'in_progress' => [
                'dokter' => ['completed', 'cancelled'],
                'pasien' => [],
                'admin' => ['completed', 'cancelled'],
            ],
            'completed' => [
                'dokter' => [],
                'pasien' => [],
                'admin' => [],
            ],
            'cancelled' => [
                'dokter' => [],
                'pasien' => [],
                'admin' => [],
            ],
            'rejected' => [
                'dokter' => [],
                'pasien' => [],
                'admin' => [],
            ],
        ];

        return $transitions[$currentStatus][$role] ?? [];
    }

    /**
     * Check if exception is deadlock
     */
    private function isDeadlockError(\Exception $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, 'Deadlock') || 
               str_contains($message, 'deadlock') ||
               str_contains($message, '1213'); // MySQL error code for deadlock
    }

    /**
     * Get lock statistics untuk monitoring
     */
    public function getLockStatistics(): array
    {
        return [
            'active_locks' => DB::select(DB::raw("
                SELECT COUNT(*) as count FROM information_schema.processlist 
                WHERE command = 'Query' AND state LIKE '%lock%'
            ")),
            'waiting_locks' => DB::select(DB::raw("
                SELECT COUNT(*) as count FROM information_schema.processlist 
                WHERE state = 'waiting for table metadata lock'
            ")),
            'recent_deadlocks' => \Illuminate\Support\Facades\Cache::get('deadlock_count', 0),
        ];
    }
}
