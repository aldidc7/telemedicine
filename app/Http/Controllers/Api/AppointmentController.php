<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AppointmentController extends Controller
{
    use AuthorizesRequests;
    
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
        $this->middleware('auth:sanctum')->except(['getAvailableSlots']);
    }

    /**
     * Book appointment
     * POST /api/v1/appointments
     */
    public function store(Request $request)
    {
        try {
            // Validasi
            $validated = $request->validate([
                'doctor_id' => 'required|integer|exists:users,id',
                'scheduled_at' => 'required|date|after:now',
                'type' => 'required|in:text_consultation,video_call,phone_call',
                'reason' => 'nullable|string|max:500',
                'price' => 'nullable|numeric|min:0',
            ]);

            // Check user adalah patient
            if (Auth::user()->role !== 'pasien') {
                return response()->json(['error' => 'Hanya pasien dapat membuat appointment'], 403);
            }

            // Book appointment dengan concurrent access control
            $appointment = $this->appointmentService->bookAppointment(
                Auth::user()->id,
                $validated['doctor_id'],
                $validated['scheduled_at'],
                $validated['type'],
                $validated['reason'] ?? null,
                $validated['price'] ?? null
            );

            return response()->json([
                'message' => 'Appointment berhasil dibuat',
                'data' => $appointment,
            ], 201);
        } catch (\PDOException $e) {
            // Handle database deadlock
            if (strpos($e->getMessage(), 'Deadlock') !== false) {
                return response()->json([
                    'error' => 'Terjadi konflik akses, silahkan coba lagi',
                    'code' => 'DEADLOCK_DETECTED'
                ], 409);
            }
            return response()->json(['error' => 'Kesalahan database: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            // Handle other exceptions (doctor inactive, slot booked, etc)
            $statusCode = 422;
            
            // Check specific error messages from ConcurrentAccessService
            if (strpos($e->getMessage(), 'tidak aktif') !== false) {
                $statusCode = 409;
            } elseif (strpos($e->getMessage(), 'sudah memiliki appointment') !== false) {
                $statusCode = 409;
            } elseif (strpos($e->getMessage(), 'belum dibayar') !== false) {
                $statusCode = 402;
            }
            
            return response()->json([
                'error' => $e->getMessage(),
                'code' => 'BOOKING_FAILED'
            ], $statusCode);
        }
    }

    /**
     * Get user's appointments
     * GET /api/v1/appointments
     */
    public function index(Request $request)
    {
        try {
            $status = $request->query('status');
            $page = $request->query('page', 1);
            $perPage = $request->query('per_page', 15);
            $search = $request->query('search');
            $dateFrom = $request->query('date_from');
            $dateTo = $request->query('date_to');

            $user = Auth::user();

            if ($search || $dateFrom || $dateTo) {
                // Advanced search
                $appointments = $this->appointmentService->searchAppointments(
                    $user->id,
                    $user->role,
                    $search,
                    $status,
                    $dateFrom,
                    $dateTo,
                    $page,
                    $perPage
                );
            } else {
                // Simple filter
                if ($user->role === 'pasien') {
                    $appointments = $this->appointmentService->getPatientAppointments(
                        $user->id,
                        $status,
                        $page,
                        $perPage
                    );
                } else {
                    $appointments = $this->appointmentService->getDoctorAppointments(
                        $user->id,
                        $status,
                        $page,
                        $perPage
                    );
                }
            }

            return response()->json([
                'data' => $appointments->items(),
                'pagination' => [
                    'current_page' => $appointments->currentPage(),
                    'per_page' => $appointments->perPage(),
                    'total' => $appointments->total(),
                    'last_page' => $appointments->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get appointment detail
     * GET /api/v1/appointments/{id}
     */
    public function show($id)
    {
        try {
            $appointment = $this->appointmentService->getAppointmentDetail($id);

            // Check if user berhak lihat
            $user = Auth::user();
            if ($appointment->patient_id !== $user->id && $appointment->doctor_id !== $user->id) {
                return response()->json(['error' => 'Anda tidak berhak mengakses appointment ini'], 403);
            }

            return response()->json([
                'data' => $appointment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get available slots for doctor
     * GET /api/v1/appointments/doctor/{doctorId}/available-slots
     */
    public function getAvailableSlots($doctorId, Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date|after:today',
            ]);

            $slots = $this->appointmentService->getAvailableSlots(
                $doctorId,
                $validated['date']
            );

            return response()->json([
                'date' => $validated['date'],
                'available_slots' => $slots,
                'total_slots' => count($slots),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Confirm appointment (doctor only)
     * POST /api/v1/appointments/{id}/confirm
     */
    public function confirm($id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter dapat confirm appointment'], 403);
            }

            $appointment = $this->appointmentService->confirmAppointment($id, $user->id);

            return response()->json([
                'message' => 'Appointment berhasil di-confirm',
                'data' => $appointment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Reject appointment (doctor only)
     * POST /api/v1/appointments/{id}/reject
     */
    public function reject($id, Request $request)
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $user = Auth::user();

            if ($user->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter dapat reject appointment'], 403);
            }

            $appointment = $this->appointmentService->rejectAppointment(
                $id,
                $user->id,
                $validated['reason']
            );

            return response()->json([
                'message' => 'Appointment berhasil di-reject',
                'data' => $appointment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel appointment
     * POST /api/v1/appointments/{id}/cancel
     */
    public function cancel($id, Request $request)
    {
        try {
            $appointment = $this->appointmentService->getAppointmentDetail($id);
            
            // Authorize using policy
            $this->authorize('cancel', $appointment);

            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $appointment = $this->appointmentService->cancelAppointment(
                $id,
                Auth::user()->id,
                $validated['reason']
            );

            return response()->json([
                'message' => 'Appointment berhasil di-cancel',
                'data' => $appointment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Reschedule appointment (patient only)
     * POST /api/v1/appointments/{id}/reschedule
     */
    public function reschedule($id, Request $request)
    {
        try {
            $appointment = $this->appointmentService->getAppointmentDetail($id);
            
            // Authorize using policy
            $this->authorize('reschedule', $appointment);

            $validated = $request->validate([
                'scheduled_at' => 'required|date|after:now',
            ]);

            $appointment = $this->appointmentService->rescheduleAppointment(
                $id,
                $validated['scheduled_at'],
                Auth::user()->id
            );

            return response()->json([
                'message' => 'Appointment berhasil di-reschedule',
                'data' => $appointment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Start appointment (doctor only)
     * POST /api/v1/appointments/{id}/start
     */
    public function start($id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter dapat start appointment'], 403);
            }

            $appointment = $this->appointmentService->startAppointment($id, $user->id);

            return response()->json([
                'message' => 'Appointment dimulai',
                'data' => $appointment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * End appointment (doctor only)
     * POST /api/v1/appointments/{id}/end
     */
    public function end($id, Request $request)
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:1000',
            ]);

            $user = Auth::user();

            if ($user->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter dapat end appointment'], 403);
            }

            $appointment = $this->appointmentService->endAppointment(
                $id,
                $user->id,
                $validated['notes'] ?? null
            );

            return response()->json([
                'message' => 'Appointment selesai',
                'data' => $appointment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get appointment statistics
     * GET /api/v1/appointments/stats
     */
    public function stats()
    {
        try {
            $user = Auth::user();
            $stats = $this->appointmentService->getAppointmentStats($user->id, $user->role);

            return response()->json([
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get today's appointments
     * GET /api/v1/appointments/today
     */
    public function today()
    {
        try {
            $user = Auth::user();
            $appointments = $this->appointmentService->getTodayAppointments($user->id, $user->role);

            return response()->json([
                'data' => $appointments,
                'count' => $appointments->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
