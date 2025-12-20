<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Konsultasi;
use App\Models\ConsultationMessage;
use App\Services\Consultation\ConsultationChatService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/** @noinspection PhpUndefinedMethodInspection */
/**
 * ============================================
 * CONTROLLER: CONSULTATION CHAT
 * ============================================
 * 
 * Endpoints untuk in-call messaging
 */
class ConsultationChatController extends Controller
{
    use ApiResponse;

    protected ConsultationChatService $chatService;

    public function __construct(ConsultationChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Send message dalam konsultasi
     * POST /api/v1/consultations/{id}/messages
     * 
     * @param Request $request
     * @param Konsultasi $consultation
     * @return JsonResponse
     */
    public function sendMessage(Request $request, Konsultasi $consultation): JsonResponse
    {
        $user = Auth::user();

        // Authorization: hanya peserta konsultasi
        if ($user->id !== $consultation->doctor_id && $user->id !== $consultation->patient_id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        // Validation
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:1000',
            'file' => 'nullable|file|max:5120', // 5MB max
        ]);

        $fileData = null;

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = 'consultation_' . $consultation->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('consultations', $file, $fileName);

            $fileData = [
                'url' => Storage::url($path),
                'type' => $this->getFileType($file->getMimeType()),
            ];
        }

        // Send message
        $message = $this->chatService->sendMessage(
            $consultation,
            $user,
            $validated['message'],
            $fileData
        );

        return $this->sendSuccess(
            $message->load('sender:id,name,avatar_url'),
            'Message sent successfully'
        );
    }

    /**
     * Get chat history
     * GET /api/v1/consultations/{id}/messages
     * 
     * @param Request $request
     * @param Konsultasi $consultation
     * @return JsonResponse
     */
    public function getMessages(Request $request, Konsultasi $consultation): JsonResponse
    {
        $user = Auth::user();

        // Authorization
        if ($user->id !== $consultation->doctor_id && $user->id !== $consultation->patient_id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        // Mark unread messages as read
        $this->chatService->markAllAsRead($consultation, $user);

        $limit = $request->query('limit', 50);
        $offset = $request->query('offset', 0);

        $messages = $this->chatService->getChatHistory($consultation, $limit, $offset);
        $unreadCount = $this->chatService->getUnreadCount($consultation, $user);

        return $this->sendSuccess([
            'messages' => $messages,
            'unread_count' => $unreadCount,
            'total' => $consultation->messages()->count(),
        ]);
    }

    /**
     * Mark message as read
     * POST /api/v1/consultation-messages/{id}/read
     * 
     * @param ConsultationMessage $message
     * @return JsonResponse
     */
    public function markAsRead(ConsultationMessage $message): JsonResponse
    {
        $user = Auth::user();

        // Authorization: penerima pesan bisa mark as read
        if (
            $user->id !== $message->consultation->patient_id &&
            $user->id !== $message->consultation->doctor_id
        ) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $this->chatService->markAsRead($message);

        return $this->sendSuccess(null, 'Message marked as read');
    }

    /**
     * Delete message
     * DELETE /api/v1/consultation-messages/{id}
     * 
     * @param ConsultationMessage $message
     * @return JsonResponse
     */
    public function deleteMessage(ConsultationMessage $message): JsonResponse
    {
        $user = Auth::user();

        // Authorization: hanya pengirim bisa delete
        if ($user->id !== $message->sender_id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $this->chatService->deleteMessage($message);

        return $this->sendSuccess(null, 'Message deleted successfully');
    }

    /**
     * Search dalam chat
     * GET /api/v1/consultations/{id}/messages/search
     * 
     * @param Request $request
     * @param Konsultasi $consultation
     * @return JsonResponse
     */
    public function searchMessages(Request $request, Konsultasi $consultation): JsonResponse
    {
        $user = Auth::user();

        // Authorization
        if ($user->id !== $consultation->doctor_id && $user->id !== $consultation->patient_id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $query = $request->query('q');
        if (!$query) {
            return $this->sendError('Search query required', [], 400);
        }

        $messages = $this->chatService->searchMessages($consultation, $query);

        return $this->sendSuccess($messages);
    }

    /**
     * Get unread count
     * GET /api/v1/consultations/{id}/messages/unread-count
     * 
     * @param Konsultasi $consultation
     * @return JsonResponse
     */
    public function getUnreadCount(Konsultasi $consultation): JsonResponse
    {
        $user = Auth::user();

        if ($user->id !== $consultation->doctor_id && $user->id !== $consultation->patient_id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $count = $this->chatService->getUnreadCount($consultation, $user);

        return $this->sendSuccess(['unread_count' => $count]);
    }

    // ========================
    // HELPER METHODS
    // ========================

    private function getFileType(string $mimeType): string
    {
        if (strpos($mimeType, 'image') !== false) {
            return 'image';
        } elseif (strpos($mimeType, 'pdf') !== false || strpos($mimeType, 'document') !== false) {
            return 'document';
        }
        return 'document';
    }
}
