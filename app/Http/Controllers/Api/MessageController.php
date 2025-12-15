<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use App\Services\MessageService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * ============================================
 * MESSAGE/CHAT CONTROLLER
 * ============================================
 * 
 * Menangani messaging antara Pasien dan Dokter
 * 
 * Endpoint:
 * GET /api/v1/messages/conversations - List conversations
 * GET /api/v1/messages/conversations/{id} - Get conversation detail
 * POST /api/v1/messages/conversations - Create/get conversation
 * POST /api/v1/messages/conversations/{id}/send - Send message
 * GET /api/v1/messages/conversations/{id}/messages - Get messages in conversation
 * POST /api/v1/messages/conversations/{id}/read - Mark as read
 * DELETE /api/v1/messages/conversations/{id} - Delete conversation
 * GET /api/v1/messages/unread-count - Get total unread count
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class MessageController extends Controller
{
    use ApiResponse;

    public function __construct(
        private MessageService $messageService
    ) {}

    /**
     * Get all conversations untuk user yang login
     * 
     * Query params:
     * - search: string (cari di nama user lain)
     * - page: integer (default 1)
     * - per_page: integer (default 20, max 50)
     * 
     * GET /api/v1/messages/conversations
     */
    public function getConversations(Request $request)
    {
        $user = $request->user();
        $search = $request->query('search');
        $page = $request->query('page', 1);
        $perPage = min($request->query('per_page', 20), 50);

        $result = $this->messageService->getUserConversations($user->id, $search, $page, $perPage);

        return $this->successResponse($result['data'], 'Conversations berhasil diambil', [
            'pagination' => $result['pagination'],
        ]);
    }

    /**
     * Get conversation detail
     * 
     * GET /api/v1/messages/conversations/{id}
     */
    public function getConversationDetail(Request $request, $conversationId)
    {
        $user = $request->user();

        try {
            $conversation = $this->messageService->getConversationDetail($conversationId, $user->id);
            return $this->successResponse($conversation, 'Conversation detail berhasil diambil');
        } catch (\Exception $e) {
            return $this->forbiddenResponse($e->getMessage());
        }
    }

    /**
     * Create atau get existing conversation dengan user lain
     * 
     * Request body:
     * {
     *   "with_user_id": 5
     * }
     * 
     * POST /api/v1/messages/conversations
     */
    public function createConversation(Request $request)
    {
        $request->validate([
            'with_user_id' => 'required|integer|exists:users,id',
        ]);

        $user = $request->user();
        $otherUserId = $request->with_user_id;

        if ($user->id === $otherUserId) {
            return $this->validationErrorResponse('Tidak bisa membuat conversation dengan diri sendiri');
        }

        // Check if other user exists
        $otherUser = User::findOrFail($otherUserId);

        // Get or create conversation
        $conversation = $this->messageService->getOrCreateConversation($user->id, $otherUserId);

        return $this->successResponse([
            'id' => $conversation->id,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'role' => $otherUser->role,
            ],
        ], 'Conversation berhasil dibuat');
    }

    /**
     * Send message dalam conversation
     * 
     * Request body:
     * {
     *   "content": "Halo dokter, apa kabar?",
     *   "attachment_path": null (optional),
     *   "attachment_type": "image" (optional - image/file/document)
     * }
     * 
     * POST /api/v1/messages/conversations/{id}/send
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'content' => 'required|string|min:1|max:5000',
            'attachment_path' => 'nullable|string',
            'attachment_type' => 'nullable|in:image,file,document',
        ]);

        $user = $request->user();

        try {
            $message = $this->messageService->sendMessage(
                $conversationId,
                $user->id,
                $request->content,
                $request->attachment_path,
                $request->attachment_type
            );

            return $this->createdResponse($message, 'Message berhasil dikirim');
        } catch (\Exception $e) {
            return $this->forbiddenResponse($e->getMessage());
        }
    }

    /**
     * Get messages dalam conversation dengan pagination
     * 
     * Query params:
     * - page: integer (default 1)
     * - per_page: integer (default 20, max 100)
     * 
     * GET /api/v1/messages/conversations/{id}/messages
     */
    public function getMessages(Request $request, $conversationId)
    {
        $user = $request->user();
        $page = $request->query('page', 1);
        $perPage = min($request->query('per_page', 20), 100);

        try {
            $result = $this->messageService->getMessages($conversationId, $user->id, $page, $perPage);
            
            // Reverse untuk tampilan normal (terbaru di bawah)
            $messages = array_reverse($result['data']);

            return $this->successResponse($messages, 'Messages berhasil diambil', [
                'pagination' => $result['pagination'],
            ]);
        } catch (\Exception $e) {
            return $this->forbiddenResponse($e->getMessage());
        }
    }

    /**
     * Mark conversation as read
     * 
     * POST /api/v1/messages/conversations/{id}/read
     */
    public function markAsRead(Request $request, $conversationId)
    {
        $user = $request->user();

        try {
            $result = $this->messageService->markConversationAsRead($conversationId, $user->id);
            return $this->successResponse(null, 'Conversation ditandai sebagai dibaca');
        } catch (\Exception $e) {
            return $this->forbiddenResponse($e->getMessage());
        }
    }

    /**
     * Get total unread count untuk user
     * 
     * GET /api/v1/messages/unread-count
     */
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();
        $unreadCount = $this->messageService->getTotalUnreadCount($user->id);

        return $this->successResponse([
            'total_unread' => $unreadCount,
        ], 'Unread count berhasil diambil');
    }

    /**
     * Delete conversation
     * 
     * DELETE /api/v1/messages/conversations/{id}
     */
    public function deleteConversation(Request $request, $conversationId)
    {
        $user = $request->user();

        try {
            $this->messageService->deleteConversation($conversationId, $user->id);
            return $this->successResponse(null, 'Conversation berhasil dihapus');
        } catch (\Exception $e) {
            return $this->forbiddenResponse($e->getMessage());
        }
    }
}
