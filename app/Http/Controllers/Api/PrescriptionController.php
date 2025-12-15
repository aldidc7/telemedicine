<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PrescriptionService;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    protected $prescriptionService;

    public function __construct(PrescriptionService $prescriptionService)
    {
        $this->prescriptionService = $prescriptionService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Create prescription (doctor only)
     * POST /api/v1/prescriptions
     */
    public function store(Request $request)
    {
        try {
            // Validasi
            $validated = $request->validate([
                'appointment_id' => 'required|integer|exists:appointments,id',
                'medications' => 'required|array|min:1',
                'medications.*.name' => 'required|string|max:100',
                'medications.*.dosage' => 'required|string|max:50',
                'medications.*.frequency' => 'required|string|max:100',
                'medications.*.duration' => 'required|string|max:100',
                'medications.*.quantity' => 'required|integer|min:1',
                'medications.*.instructions' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'instructions' => 'nullable|string|max:1000',
                'expires_at' => 'nullable|date|after:now',
            ]);

            // Check user adalah doctor
            if (auth()->user()->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter yang dapat membuat resep'], 403);
            }

            // Create prescription
            $prescription = $this->prescriptionService->createPrescription(
                $validated['appointment_id'],
                auth()->user()->id,
                auth()->user()->id, // Will be replaced from appointment
                $validated['medications'],
                $validated['notes'] ?? null,
                $validated['instructions'] ?? null,
                $validated['expires_at'] ?? null
            );

            // Get patient ID from appointment
            $appointment = \App\Models\Appointment::findOrFail($validated['appointment_id']);
            $prescription->patient_id = $appointment->patient_id;
            $prescription->save();

            return response()->json([
                'message' => 'Resep berhasil dibuat',
                'data' => $prescription,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get prescription detail
     * GET /api/v1/prescriptions/{id}
     */
    public function show($id)
    {
        try {
            $prescription = $this->prescriptionService->getPrescriptionDetail($id);

            // Check if user authorized
            $user = auth()->user();
            if ($prescription->patient_id !== $user->id && $prescription->doctor_id !== $user->id) {
                return response()->json(['error' => 'Anda tidak berhak mengakses resep ini'], 403);
            }

            return response()->json([
                'data' => $prescription,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get user prescriptions (patient or doctor)
     * GET /api/v1/prescriptions
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

            $user = auth()->user();

            if ($search || $dateFrom || $dateTo) {
                // Advanced search
                $prescriptions = $this->prescriptionService->searchPrescriptions(
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
                    $prescriptions = $this->prescriptionService->getPatientPrescriptions(
                        $user->id,
                        $status,
                        $page,
                        $perPage
                    );
                } else {
                    $prescriptions = $this->prescriptionService->getDoctorPrescriptions(
                        $user->id,
                        $status,
                        $page,
                        $perPage
                    );
                }
            }

            return response()->json([
                'data' => $prescriptions->items(),
                'pagination' => [
                    'current_page' => $prescriptions->currentPage(),
                    'per_page' => $prescriptions->perPage(),
                    'total' => $prescriptions->total(),
                    'last_page' => $prescriptions->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get active prescriptions for patient
     * GET /api/v1/prescriptions/active
     */
    public function active()
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'pasien') {
                return response()->json(['error' => 'Hanya pasien yang bisa akses ini'], 403);
            }

            $prescriptions = $this->prescriptionService->getActivePrescriptions($user->id);

            return response()->json([
                'data' => $prescriptions,
                'count' => $prescriptions->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get unacknowledged prescriptions (patient)
     * GET /api/v1/prescriptions/unacknowledged
     */
    public function unacknowledged()
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'pasien') {
                return response()->json(['error' => 'Hanya pasien yang bisa akses ini'], 403);
            }

            $prescriptions = $this->prescriptionService->getUnacknowledgedPrescriptions($user->id);

            return response()->json([
                'data' => $prescriptions,
                'count' => $prescriptions->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Acknowledge prescription (patient)
     * POST /api/v1/prescriptions/{id}/acknowledge
     */
    public function acknowledge($id)
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'pasien') {
                return response()->json(['error' => 'Hanya pasien yang bisa acknowledge resep'], 403);
            }

            $prescription = $this->prescriptionService->acknowledgePrescription($id, $user->id);

            return response()->json([
                'message' => 'Resep berhasil di-acknowledge',
                'data' => $prescription,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Update prescription (doctor only)
     * PUT /api/v1/prescriptions/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'medications' => 'nullable|array|min:1',
                'medications.*.name' => 'required_with:medications|string|max:100',
                'medications.*.dosage' => 'required_with:medications|string|max:50',
                'medications.*.frequency' => 'required_with:medications|string|max:100',
                'medications.*.duration' => 'required_with:medications|string|max:100',
                'medications.*.quantity' => 'required_with:medications|integer|min:1',
                'medications.*.instructions' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'instructions' => 'nullable|string|max:1000',
            ]);

            $user = auth()->user();

            if ($user->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter yang dapat update resep'], 403);
            }

            $prescription = $this->prescriptionService->updatePrescription(
                $id,
                $user->id,
                $validated['medications'] ?? null,
                $validated['notes'] ?? null,
                $validated['instructions'] ?? null
            );

            return response()->json([
                'message' => 'Resep berhasil diupdate',
                'data' => $prescription,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Mark prescription as completed (patient)
     * POST /api/v1/prescriptions/{id}/complete
     */
    public function complete($id)
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'pasien') {
                return response()->json(['error' => 'Hanya pasien yang bisa mark resep'], 403);
            }

            $prescription = $this->prescriptionService->completePrescription($id, $user->id);

            return response()->json([
                'message' => 'Resep berhasil ditandai selesai',
                'data' => $prescription,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete prescription (doctor only)
     * DELETE /api/v1/prescriptions/{id}
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter yang dapat delete resep'], 403);
            }

            $this->prescriptionService->deletePrescription($id, $user->id);

            return response()->json([
                'message' => 'Resep berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get prescription statistics
     * GET /api/v1/prescriptions/stats
     */
    public function stats()
    {
        try {
            $user = auth()->user();
            $stats = $this->prescriptionService->getPrescriptionStats($user->id, $user->role);

            return response()->json([
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get prescriptions for specific appointment
     * GET /api/v1/appointments/{appointmentId}/prescriptions
     */
    public function byAppointment($appointmentId)
    {
        try {
            $user = auth()->user();
            
            // Verify user is authorized for this appointment
            $appointment = \App\Models\Appointment::findOrFail($appointmentId);
            if ($appointment->patient_id !== $user->id && $appointment->doctor_id !== $user->id) {
                return response()->json(['error' => 'Anda tidak berhak mengakses appointment ini'], 403);
            }

            $prescriptions = $this->prescriptionService->getAppointmentPrescriptions($appointmentId);

            return response()->json([
                'data' => $prescriptions,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Check if appointment has prescription
     * GET /api/v1/appointments/{appointmentId}/has-prescription
     */
    public function appointmentHasPrescription($appointmentId)
    {
        try {
            $hasPrescription = $this->prescriptionService->appointmentHasPrescription($appointmentId);

            return response()->json([
                'has_prescription' => $hasPrescription,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
