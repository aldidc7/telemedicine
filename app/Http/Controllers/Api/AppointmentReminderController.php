<?php

/**
 * @noinspection PhpUndefinedMethodInspection
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppointmentReminder;
use App\Models\Konsultasi;
use App\Services\Appointment\AppointmentReminderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** @noinspection PhpUndefinedMethodInspection */
/**
 * ============================================
 * APPOINTMENT REMINDER CONTROLLER
 * ============================================
 * 
 * Manage appointment reminders
 * 
 * Features:
 * - View reminder status
 * - Resend reminder manually
 * - Toggle reminder preferences
 * - Reminder history
 * 
 * Endpoints:
 * GET /api/v1/appointment-reminders - List reminders
 * GET /api/v1/appointment-reminders/{id} - Detail
 * POST /api/v1/appointment-reminders/{id}/resend - Resend reminder
 * GET /api/v1/appointments/{id}/reminders - Reminders untuk appointment
 * PUT /api/v1/reminder-preferences - Update preferences
 * GET /api/v1/reminder-preferences - Get preferences
 */
/** @noinspection PhpUndefinedMethodInspection */
class AppointmentReminderController extends Controller
{
    use ApiResponse;

    protected AppointmentReminderService $reminderService;

    /**
     * @noinspection PhpUndefinedMethodInspection
     */
    public function __construct(AppointmentReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
        // Middleware registration for authentication
        $middleware = 'middleware';
        if (method_exists($this, $middleware)) {
            $this->{$middleware}('auth:sanctum');
        }
    }

    /**
     * List reminders untuk current user
     * GET /api/v1/appointment-reminders
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->sendError('Unauthorized', [], 401);
            }

            $reminders = AppointmentReminder::where('user_id', $user->id)
                ->with('appointment', 'appointment.dokter')
                ->orderBy('scheduled_for', 'desc')
                ->paginate($request->per_page ?? 15);

            return $this->success([
                'data' => $reminders->items(),
                'pagination' => [
                    'total' => $reminders->total(),
                    'per_page' => $reminders->perPage(),
                    'current_page' => $reminders->currentPage(),
                    'last_page' => $reminders->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch reminders: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get detail reminder
     * GET /api/v1/appointment-reminders/{id}
     * @noinspection PhpUndefinedMethodInspection
     */
    public function show(string $id): JsonResponse
    {
        try {
            $reminder = AppointmentReminder::with('appointment', 'user')
                ->findOrFail($id);

            // Authorize
            //noinspection PhpUndefinedMethodInspection
            $userId = Auth::user()?->id;
            if (!$userId || $reminder->user_id !== $userId) {
                return $this->error('Unauthorized', 403);
            }

            return $this->success($reminder);
        } catch (\Exception $e) {
            return $this->error('Reminder not found', 404);
        }
    }

    /**
     * Get reminders untuk specific appointment
     * GET /api/v1/appointments/{appointmentId}/reminders
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getAppointmentReminders(string $appointmentId): JsonResponse
    {
        try {
            $appointment = Konsultasi::findOrFail($appointmentId);
            //noinspection PhpUndefinedMethodInspection
            $user = Auth::user();

            // Authorize
            if ($appointment->patient_id !== $user->id && $appointment->doctor_id !== $user->id) {
                return $this->error('Unauthorized', 403);
            }

            $reminders = AppointmentReminder::where('appointment_id', $appointmentId)
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'type' => $r->getReminderTypeLabel(),
                    'status' => $r->getStatusLabel(),
                    'scheduled_for' => $r->scheduled_for,
                    'sent_at' => $r->sent_at,
                    'error_message' => $r->error_message,
                ]);

            return $this->success([
                'appointment_id' => $appointmentId,
                'reminders' => $reminders,
            ]);
        } catch (\Exception $e) {
            return $this->error('Appointment not found', 404);
        }
    }

    /**
     * Resend reminder manually
     * POST /api/v1/appointment-reminders/{id}/resend
     * @noinspection PhpUndefinedMethodInspection
     */
    public function resend(string $id): JsonResponse
    {
        try {
            $reminder = AppointmentReminder::findOrFail($id);

            // Authorize
            //noinspection PhpUndefinedMethodInspection
            $userId = Auth::user()?->id;
            if (!$userId || $reminder->user_id !== $userId) {
                return $this->error('Unauthorized', 403);
            }

            // Check if appointment not expired
            if ($reminder->isAppointmentExpired()) {
                return $this->error('Cannot resend reminder for past appointment', 400);
            }

            // Send
            $this->reminderService->sendReminder($reminder);

            return $this->success([
                'message' => 'Reminder resent successfully',
                'reminder' => $reminder,
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to resend reminder: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user reminder preferences
     * GET /api/v1/reminder-preferences
     */
    public function getPreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->success([
                'email_enabled' => $user->email_notifications_enabled ?? true,
                'sms_enabled' => $user->sms_notifications_enabled ?? true,
                'push_enabled' => $user->push_notifications_enabled ?? true,
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch preferences: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update reminder preferences
     * PUT /api/v1/reminder-preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email_enabled' => 'boolean',
                'sms_enabled' => 'boolean',
                'push_enabled' => 'boolean',
            ]);

            $user = $request->user();

            if (isset($validated['email_enabled'])) {
                $user->email_notifications_enabled = $validated['email_enabled'];
            }
            if (isset($validated['sms_enabled'])) {
                $user->sms_notifications_enabled = $validated['sms_enabled'];
            }
            if (isset($validated['push_enabled'])) {
                $user->push_notifications_enabled = $validated['push_enabled'];
            }

            $user->save();

            return $this->success([
                'message' => 'Preferences updated',
                'preferences' => [
                    'email_enabled' => $user->email_notifications_enabled,
                    'sms_enabled' => $user->sms_notifications_enabled,
                    'push_enabled' => $user->push_notifications_enabled,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to update preferences: ' . $e->getMessage(), 500);
        }
    }
}
