<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Konsultasi;
use App\Models\Emergency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * ============================================
 * EMERGENCY CONTROLLER
 * ============================================
 * 
 * Handle medical emergency escalation, ambulance calls,
 * hospital referrals, dan emergency contact management
 * 
 * Endpoints:
 * POST /api/v1/emergencies - Create emergency
 * GET /api/v1/emergencies/{id} - Get emergency details
 * POST /api/v1/emergencies/{id}/escalate - Escalate to hospital
 * POST /api/v1/emergencies/{id}/call-ambulance - Call ambulance
 * POST /api/v1/emergencies/{id}/contacts - Add emergency contact
 * POST /api/v1/emergencies/{id}/referral-letter - Generate referral
 * PUT /api/v1/emergencies/{id}/resolve - Mark as resolved
 * GET /api/v1/emergencies/{id}/log - Get escalation log
 */
class EmergencyController extends Controller
{
    /**
     * Create emergency case
     * POST /api/v1/emergencies
     */
    public function create(Request $request)
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'consultation_id' => 'required|exists:konsultasis,id',
                'level' => 'required|in:critical,severe,moderate',
                'reason' => 'required|string|min:10',
                'notes' => 'nullable|string',
            ]);

            // Verify user has access to this consultation
            $consultation = Konsultasi::findOrFail($validated['consultation_id']);
            
            if ($user->id !== $consultation->pasien_id && $user->id !== $consultation->dokter_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $emergency = Emergency::create([
                'consultation_id' => $validated['consultation_id'],
                'created_by_id' => $user->id,
                'level' => $validated['level'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'status' => 'open',
            ]);

            // Log emergency creation
            $emergency->escalationLogs()->create([
                'action' => 'emergency_created',
                'details' => "Emergency {$emergency->level} created: {$validated['reason']}",
                'timestamp' => now(),
            ]);

            // For CRITICAL level - auto-escalate
            if ($emergency->isCritical()) {
                $emergency->update(['status' => 'escalated']);
                Log::warning("CRITICAL EMERGENCY created: Emergency ID {$emergency->id}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Emergency created',
                'data' => $emergency->load('escalationLogs'),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Emergency creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat emergency case',
            ], 500);
        }
    }

    /**
     * Get emergency details
     * GET /api/v1/emergencies/{id}
     */
    public function show($id, Request $request)
    {
        try {
            $emergency = Emergency::with(['consultation', 'createdBy', 'contacts', 'escalationLogs'])
                ->findOrFail($id);

            $user = $request->user();
            
            // Verify access
            if ($user->id !== $emergency->created_by_id &&
                $user->id !== $emergency->consultation->pasien_id &&
                $user->id !== $emergency->consultation->dokter_id &&
                $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $emergency,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Emergency not found',
            ], 404);
        }
    }

    /**
     * Escalate emergency to hospital
     * POST /api/v1/emergencies/{id}/escalate
     */
    public function escalate($id, Request $request)
    {
        try {
            $emergency = Emergency::findOrFail($id);
            
            $validated = $request->validate([
                'hospital_name' => 'required|string',
                'hospital_address' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            $emergency->escalateToHospital([
                'name' => $validated['hospital_name'],
                'address' => $validated['hospital_address'],
            ]);

            if ($validated['notes'] ?? false) {
                $emergency->update(['notes' => $validated['notes']]);
            }

            Log::info("Emergency escalated to hospital: {$validated['hospital_name']}");

            return response()->json([
                'success' => true,
                'message' => 'Emergency escalated to hospital',
                'data' => $emergency->refresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Emergency escalation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal escalate emergency',
            ], 500);
        }
    }

    /**
     * Call ambulance
     * POST /api/v1/emergencies/{id}/call-ambulance
     */
    public function callAmbulance($id, Request $request)
    {
        try {
            $emergency = Emergency::findOrFail($id);
            
            $validated = $request->validate([
                'ambulance_eta' => 'nullable|string',
            ]);

            $emergency->callAmbulance($validated['ambulance_eta'] ?? null);

            // TODO: Integrate dengan real ambulance service
            // For now, just track the call

            Log::warning("Ambulance called for emergency ID: {$id}");

            return response()->json([
                'success' => true,
                'message' => 'Ambulance called successfully',
                'data' => $emergency->refresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Ambulance call failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal panggil ambulance',
            ], 500);
        }
    }

    /**
     * Add emergency contact
     * POST /api/v1/emergencies/{id}/contacts
     */
    public function addContact($id, Request $request)
    {
        try {
            $emergency = Emergency::findOrFail($id);
            
            $validated = $request->validate([
                'type' => 'required|in:hospital,ambulance,police,family,other',
                'name' => 'required|string',
                'phone' => 'required|string',
                'address' => 'nullable|string',
            ]);

            $contact = $emergency->contacts()->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Contact added',
                'data' => $contact,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal tambah contact',
            ], 500);
        }
    }

    /**
     * Mark contact as contacted
     * POST /api/v1/emergencies/{emergencyId}/contacts/{contactId}/confirm
     */
    public function confirmContact($emergencyId, $contactId, Request $request)
    {
        try {
            $emergency = Emergency::findOrFail($emergencyId);
            $contact = $emergency->contacts()->findOrFail($contactId);
            
            $validated = $request->validate([
                'response' => 'nullable|string',
            ]);

            $contact->update([
                'status' => 'confirmed',
                'contacted_at' => now(),
                'response' => $validated['response'] ?? null,
            ]);

            // Log contact confirmation
            $emergency->escalationLogs()->create([
                'action' => 'contact_confirmed',
                'details' => "Contact {$contact->name} ({$contact->type}) confirmed",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact confirmed',
                'data' => $contact,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contact confirmation failed',
            ], 500);
        }
    }

    /**
     * Generate referral letter
     * POST /api/v1/emergencies/{id}/referral-letter
     */
    public function generateReferralLetter($id, Request $request)
    {
        try {
            $emergency = Emergency::findOrFail($id);
            
            $emergency->generateReferralLetter();

            Log::info("Referral letter generated for emergency ID: {$id}");

            return response()->json([
                'success' => true,
                'message' => 'Referral letter generated',
                'data' => [
                    'letter' => $emergency->referral_letter,
                    'generated_at' => $emergency->escalated_at,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Referral letter generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate referral letter',
            ], 500);
        }
    }

    /**
     * Mark emergency as resolved
     * PUT /api/v1/emergencies/{id}/resolve
     */
    public function resolve($id, Request $request)
    {
        try {
            $emergency = Emergency::findOrFail($id);
            
            $validated = $request->validate([
                'resolution_notes' => 'nullable|string',
            ]);

            $emergency->markResolved($validated['resolution_notes'] ?? null);

            $emergency->escalationLogs()->create([
                'action' => 'emergency_resolved',
                'details' => 'Emergency marked as resolved',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Emergency resolved',
                'data' => $emergency->refresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal resolve emergency',
            ], 500);
        }
    }

    /**
     * Get escalation log (audit trail)
     * GET /api/v1/emergencies/{id}/log
     */
    public function getLog($id, Request $request)
    {
        try {
            $emergency = Emergency::findOrFail($id);
            
            $logs = $emergency->escalationLogs()
                ->orderBy('timestamp', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve logs',
            ], 404);
        }
    }

    /**
     * Get active emergencies (admin only)
     * GET /api/v1/admin/emergencies/active
     */
    public function activeEmergencies(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $emergencies = Emergency::active()
                ->with(['consultation', 'createdBy', 'contacts'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $emergencies,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve emergencies',
            ], 500);
        }
    }
}
