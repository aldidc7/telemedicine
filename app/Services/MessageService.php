<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MessageService
{
    /**
     * Get atau create conversation antara 2 user
     */
    public function getOrCreateConversation($user1Id, $user2Id)
    {
        // Pastikan user1Id lebih kecil dari user2Id untuk konsistensi
        if ($user1Id > $user2Id) {
            [$user1Id, $user2Id] = [$user2Id, $user1Id];
        }

        return Conversation::firstOrCreate(
            [
                'user_1_id' => $user1Id,
                'user_2_id' => $user2Id,
            ]
        );
    }

    /**
     * Send message
     */
    public function sendMessage($conversationId, $senderId, $content, $attachmentPath = null, $attachmentType = null)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Verify sender is part of conversation
        if (!$conversation->hasUser($senderId)) {
            throw new \Exception('User tidak termasuk dalam conversation ini');
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'content' => $content,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
        ]);

        // Update conversation's last message
        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => substr($content, 0, 50),
        ]);

        // Send notification to recipient
        try {
            $sender = \App\Models\User::findOrFail($senderId);
            $recipientId = $sender->id === $conversation->user_1_id 
                ? $conversation->user_2_id 
                : $conversation->user_1_id;
            
            $notificationService = app(NotificationService::class);
            $notificationService->notifyNewMessage(
                $recipientId,
                $sender->name,
                substr($content, 0, 50),
                $conversationId
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to create message notification: ' . $e->getMessage());
        }

        return $message->load('sender');
    }

    /**
     * Get messages dalam conversation dengan pagination
     */
    public function getMessages($conversationId, $userId, $page = 1, $perPage = 20)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Verify user is part of conversation
        if (!$conversation->hasUser($userId)) {
            throw new \Exception('User tidak termasuk dalam conversation ini');
        }

        // Mark as read
        $conversation->markAsRead($userId);

        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $messages->items(),
            'pagination' => [
                'total' => $messages->total(),
                'per_page' => $messages->perPage(),
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
            ],
        ];
    }

    /**
     * Get all conversations untuk user
     */
    public function getUserConversations($userId, $search = null, $page = 1, $perPage = 20)
    {
        $query = Conversation::where(function ($q) use ($userId) {
            $q->where('user_1_id', $userId)
                ->orWhere('user_2_id', $userId);
        })
        ->with(['user1', 'user2'])
        ->orderBy('last_message_at', 'desc')
        ->orderBy('created_at', 'desc');

        // Filter by search (cari di nama user)
        if ($search) {
            $query->whereHas('user1', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('user2', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $conversations = $query->paginate($perPage, ['*'], 'page', $page);

        // Format response dengan info other user
        $data = $conversations->map(function ($conv) use ($userId) {
            $otherUser = $conv->getOtherUser($userId);
            return [
                'id' => $conv->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'role' => $otherUser->role,
                    'avatar' => $otherUser->avatar ?? null,
                ],
                'last_message_at' => $conv->last_message_at,
                'last_message_preview' => $conv->last_message_preview,
                'unread_count' => $conv->getUnreadCount($userId),
            ];
        });

        return [
            'data' => $data,
            'pagination' => [
                'total' => $conversations->total(),
                'per_page' => $conversations->perPage(),
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
            ],
        ];
    }

    /**
     * Get conversation detail
     */
    public function getConversationDetail($conversationId, $userId)
    {
        $conversation = Conversation::with(['user1', 'user2'])
            ->findOrFail($conversationId);

        if (!$conversation->hasUser($userId)) {
            throw new \Exception('User tidak termasuk dalam conversation ini');
        }

        $otherUser = $conversation->getOtherUser($userId);

        return [
            'id' => $conversation->id,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'role' => $otherUser->role,
                'avatar' => $otherUser->avatar ?? null,
            ],
            'last_message_at' => $conversation->last_message_at,
            'created_at' => $conversation->created_at,
        ];
    }

    /**
     * Mark conversation as read
     */
    public function markConversationAsRead($conversationId, $userId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->hasUser($userId)) {
            throw new \Exception('User tidak termasuk dalam conversation ini');
        }

        $conversation->markAsRead($userId);

        return [
            'success' => true,
            'unread_count' => $conversation->getUnreadCount($userId),
        ];
    }

    /**
     * Get unread count across all conversations
     */
    public function getTotalUnreadCount($userId)
    {
        $conversations = Conversation::where(function ($q) use ($userId) {
            $q->where('user_1_id', $userId)
                ->orWhere('user_2_id', $userId);
        })->get();

        $totalUnread = 0;
        foreach ($conversations as $conv) {
            $totalUnread += $conv->getUnreadCount($userId);
        }

        return $totalUnread;
    }

    /**
     * Delete conversation (soft delete semua messages)
     */
    public function deleteConversation($conversationId, $userId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->hasUser($userId)) {
            throw new \Exception('User tidak termasuk dalam conversation ini');
        }

        // Delete conversation and related messages
        $conversation->delete();

        return ['success' => true];
    }
}
