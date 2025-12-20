<?php

namespace App\Http\Controllers\Api;

use App\Models\Prescription;
use App\Services\PDF\PrescriptionPDFService;
use Illuminate\Http\Request;

/**
 * Prescription PDF API Controller
 * 
 * Endpoints for prescription PDF generation and delivery
 * - GET /api/v1/prescriptions/{id}/pdf - Download PDF
 * - POST /api/v1/prescriptions/{id}/send-email - Email prescription
 * - GET /api/v1/prescriptions/download-all - Download all as ZIP
 */
class PrescriptionPDFController extends BaseApiController
{
    protected PrescriptionPDFService $service;

    public function __construct(PrescriptionPDFService $service)
    {
        $this->service = $service;
        // Middleware is set in BaseApiController
    }

    /**
     * Download prescription PDF
     * GET /api/v1/prescriptions/{id}/pdf
     */
    public function download(Request $request, Prescription $prescription)
    {
        // Check authorization
        if (!$this->service->canAccessPrescription($prescription, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            // Log the download
            $this->service->logDownload($prescription, $request->user());

            return $this->service->download($prescription);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send prescription via email
     * POST /api/v1/prescriptions/{id}/send-email
     */
    public function sendEmail(Request $request, Prescription $prescription)
    {
        // Check authorization
        if (!$this->service->canAccessPrescription($prescription, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $this->service->email($prescription, $validated['email']);

            return response()->json([
                'success' => true,
                'message' => 'Prescription sent via email successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download all patient prescriptions as ZIP
     * GET /api/v1/prescriptions/download-all
     */
    public function downloadAll(Request $request)
    {
        try {
            // Get all prescriptions for current patient
            $prescriptions = Prescription::where('patient_id', $request->user()->id)
                ->with('consultation.doctor')
                ->get();

            if ($prescriptions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No prescriptions found',
                ], 404);
            }

            $zipPath = $this->service->generateBulkZip($prescriptions);

            return response()->download($zipPath, 'prescriptions.zip')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate ZIP: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get prescription preview
     * GET /api/v1/prescriptions/{id}/preview
     */
    public function preview(Request $request, Prescription $prescription)
    {
        // Check authorization
        if (!$this->service->canAccessPrescription($prescription, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $prescription->id,
                'patient' => $prescription->consultation->patient->only(['id', 'name', 'email']),
                'doctor' => $prescription->consultation->doctor->only(['id', 'name', 'specialization']),
                'medicines' => $prescription->medicines,
                'notes' => $prescription->notes,
                'created_at' => $prescription->created_at,
                'download_count' => $prescription->download_count,
            ],
        ]);
    }
}
