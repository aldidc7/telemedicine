<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorAvailability;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    /**
     * Get doctor's availability schedule
     * GET /api/v1/doctors/{id}/availability
     */
    public function getDoctorAvailability($doctorId)
    {
        try {
            $doctor = User::find($doctorId);
            if (!$doctor || $doctor->role !== 'dokter') {
                return response()->json(['error' => 'Dokter tidak ditemukan'], 404);
            }

            $availability = DoctorAvailability::forDoctor($doctorId)
                ->active()
                ->get()
                ->map(function ($avail) {
                    return [
                        'id' => $avail->id,
                        'day_of_week' => $avail->day_of_week,
                        'day_name' => $avail->getDayName(),
                        'time_range' => $avail->formatTimeRange(),
                        'slot_duration' => $avail->slot_duration_minutes,
                        'max_appointments' => $avail->max_appointments_per_day,
                        'is_active' => $avail->is_active,
                    ];
                });

            return response()->json([
                'doctor_id' => $doctorId,
                'schedule' => $availability,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get available time slots for booking
     * GET /api/v1/doctors/{id}/available-slots
     */
    public function getAvailableSlots($doctorId, Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date|after:today',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $doctor = User::find($doctorId);
            if (!$doctor || $doctor->role !== 'dokter') {
                return response()->json(['error' => 'Dokter tidak ditemukan'], 404);
            }

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $slots = [];

            // Iterate through each date in range
            for ($date = $startDate; $date <= $endDate; $date->addDay()) {
                $dayOfWeek = $date->dayOfWeek;

                $availability = DoctorAvailability::forDoctor($doctorId)
                    ->forDayOfWeek($dayOfWeek)
                    ->active()
                    ->first();

                if ($availability) {
                    $availableSlots = $availability->getAvailableSlots($date);
                    
                    foreach ($availableSlots as $slot) {
                        $slots[] = [
                            'date' => $date->format('Y-m-d'),
                            'start_time' => $slot['start']->format('H:i'),
                            'end_time' => $slot['end']->format('H:i'),
                            'datetime' => $slot['start']->toIso8601String(),
                            'duration_minutes' => $slot['duration'],
                        ];
                    }
                }
            }

            return response()->json([
                'doctor_id' => $doctorId,
                'date_range' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ],
                'total_slots' => count($slots),
                'slots' => $slots,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Set doctor's availability (Doctor only)
     * POST /api/v1/doctors/availability
     */
    public function setAvailability(Request $request)
    {
        try {
            if (Auth::user()->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter yang dapat mengatur availability'], 403);
            }

            $validated = $request->validate([
                'day_of_week' => 'required|integer|between:0,6',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'break_start' => 'nullable|date_format:H:i',
                'break_end' => 'nullable|date_format:H:i|after:break_start',
                'slot_duration_minutes' => 'integer|min:15|max:240',
                'max_appointments_per_day' => 'integer|min:1|max:100',
            ]);

            $availability = DoctorAvailability::updateOrCreate(
                [
                    'doctor_id' => Auth::id(),
                    'day_of_week' => $validated['day_of_week'],
                ],
                [
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'break_start' => $validated['break_start'] ?? null,
                    'break_end' => $validated['break_end'] ?? null,
                    'slot_duration_minutes' => $validated['slot_duration_minutes'] ?? 30,
                    'max_appointments_per_day' => $validated['max_appointments_per_day'] ?? 20,
                    'is_active' => true,
                ]
            );

            return response()->json([
                'message' => 'Availability berhasil diatur',
                'availability' => [
                    'id' => $availability->id,
                    'day_name' => $availability->getDayName(),
                    'time_range' => $availability->formatTimeRange(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update availability status
     * PATCH /api/v1/doctors/availability/{id}
     */
    public function updateAvailability($id, Request $request)
    {
        try {
            if (Auth::user()->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter yang dapat mengubah availability'], 403);
            }

            $availability = DoctorAvailability::find($id);
            if (!$availability || $availability->doctor_id !== Auth::id()) {
                return response()->json(['error' => 'Availability tidak ditemukan'], 404);
            }

            $validated = $request->validate([
                'is_active' => 'boolean',
                'start_time' => 'date_format:H:i',
                'end_time' => 'date_format:H:i',
            ]);

            $availability->update($validated);

            return response()->json([
                'message' => 'Availability berhasil diubah',
                'availability' => [
                    'id' => $availability->id,
                    'is_active' => $availability->is_active,
                    'time_range' => $availability->formatTimeRange(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all availability for doctor (Doctor only)
     * GET /api/v1/doctors/availability/list
     */
    public function listAvailability()
    {
        try {
            if (Auth::user()->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter yang dapat melihat list'], 403);
            }

            $availability = DoctorAvailability::forDoctor(Auth::id())
                ->get()
                ->map(function ($avail) {
                    return [
                        'id' => $avail->id,
                        'day_of_week' => $avail->day_of_week,
                        'day_name' => $avail->getDayName(),
                        'start_time' => $avail->start_time,
                        'end_time' => $avail->end_time,
                        'break_start' => $avail->break_start,
                        'break_end' => $avail->break_end,
                        'slot_duration' => $avail->slot_duration_minutes,
                        'max_appointments' => $avail->max_appointments_per_day,
                        'is_active' => $avail->is_active,
                    ];
                });

            return response()->json([
                'total' => $availability->count(),
                'schedule' => $availability,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk set availability (Doctor only)
     * POST /api/v1/doctors/availability/bulk
     */
    public function bulkSetAvailability(Request $request)
    {
        try {
            if (Auth::user()->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter yang dapat mengatur availability'], 403);
            }

            $validated = $request->validate([
                'schedule' => 'required|array|min:1',
                'schedule.*.day_of_week' => 'required|integer|between:0,6',
                'schedule.*.start_time' => 'required|date_format:H:i',
                'schedule.*.end_time' => 'required|date_format:H:i',
                'schedule.*.break_start' => 'nullable|date_format:H:i',
                'schedule.*.break_end' => 'nullable|date_format:H:i',
            ]);

            $created = 0;
            foreach ($validated['schedule'] as $sched) {
                DoctorAvailability::updateOrCreate(
                    [
                        'doctor_id' => Auth::id(),
                        'day_of_week' => $sched['day_of_week'],
                    ],
                    [
                        'start_time' => $sched['start_time'],
                        'end_time' => $sched['end_time'],
                        'break_start' => $sched['break_start'] ?? null,
                        'break_end' => $sched['break_end'] ?? null,
                        'is_active' => true,
                    ]
                );
                $created++;
            }

            return response()->json([
                'message' => "{$created} availability berhasil diatur",
                'count' => $created,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete availability
     * DELETE /api/v1/doctors/availability/{id}
     */
    public function deleteAvailability($id)
    {
        try {
            if (Auth::user()->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter yang dapat menghapus availability'], 403);
            }

            $availability = DoctorAvailability::find($id);
            if (!$availability || $availability->doctor_id !== Auth::id()) {
                return response()->json(['error' => 'Availability tidak ditemukan'], 404);
            }

            $availability->delete();

            return response()->json(['message' => 'Availability berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
