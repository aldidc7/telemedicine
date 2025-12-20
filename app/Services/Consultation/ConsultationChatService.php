<?php

namespace App\Services\Consultation;

use App\Events\ConsultationMessageSent;
use App\Models\ConsultationMessage;
use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Support\Collection;

/** @noinspection PhpUndefinedMethodInspection */
/**
 * ============================================
 * SERVICE: CONSULTATION CHAT
 * ============================================
 * 
 * Business logic untuk in-call chat
 * - Mengirim pesan
 * - Fetch history
 * - Mark as read
 * - Handle attachments
 */
class ConsultationChatService
{
    /**
     * Send message dalam konsultasi
     * 
     * @param Konsultasi $consultation
     * @param User $sender
     * @param string $message
     * @param array $fileData Optional: ['url' => 'path', 'type' => 'image|document']
     * @return ConsultationMessage
     */
    public function sendMessage(Konsultasi $consultation, User $sender, string $message, ?array $fileData = null): ConsultationMessage
    {
        $messageData = [
            'consultation_id' => $consultation->id,
            'sender_id' => $sender->id,
            'message' => $message,
        ];

        if ($fileData) {
            $messageData['file_url'] = $fileData['url'] ?? null;
            $messageData['file_type'] = $fileData['type'] ?? null;
        }

        $consultationMessage = ConsultationMessage::create($messageData);

        // Broadcast message to participants
        broadcast(new ConsultationMessageSent($consultationMessage))->toOthers();

        return $consultationMessage;
    }

    /**
     * Get chat history untuk konsultasi
     * 
     * @param Konsultasi $consultation
     * @param int $limit
     * @param int $offset
     * @return Collection
     */
    public function getChatHistory(Konsultasi $consultation, int $limit = 50, int $offset = 0): Collection
    {
        return $consultation->messages()
            ->with('sender:id,name,avatar_url')
            ->orderBy('created_at', 'asc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Mark pesan sebagai sudah dibaca
     * Biasanya dijalankan saat user menerima message
     * 
     * @param ConsultationMessage $message
     * @return void
     */
    public function markAsRead(ConsultationMessage $message): void
    {
        if (!$message->is_read) {
            $message->markAsRead();
        }
    }

    /**
     * Mark semua unread messages sebagai read
     * Saat user membuka chat
     * 
     * @param Konsultasi $consultation
     * @param User $user
     * @return int Jumlah messages yang diupdate
     */
    public function markAllAsRead(Konsultasi $consultation, User $user): int
    {
        $messages = $consultation->messages()
            ->unread()
            ->where('sender_id', '!=', $user->id)
            ->get();

        $count = 0;
        foreach ($messages as $message) {
            $message->markAsRead();
            $count++;
        }

        return $count;
    }

    /**
     * Get unread message count
     * 
     * @param Konsultasi $consultation
     * @param User $user
     * @return int
     */
    public function getUnreadCount(Konsultasi $consultation, User $user): int
    {
        return $consultation->messages()
            ->unread()
            ->where('sender_id', '!=', $user->id)
            ->count();
    }

    /**
     * Delete message (soft delete)
     * 
     * @param ConsultationMessage $message
     * @return bool
     */
    public function deleteMessage(ConsultationMessage $message): bool
    {
        return $message->delete();
    }

    /**
     * Search dalam chat history
     * 
     * @param Konsultasi $consultation
     * @param string $query
     * @return Collection
     */
    public function searchMessages(Konsultasi $consultation, string $query): Collection
    {
        return $consultation->messages()
            ->with('sender:id,name,avatar_url')
            ->where('message', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get last message dalam konsultasi
     * Untuk preview di consultation list
     * 
     * @param Konsultasi $consultation
     * @return ConsultationMessage|null
     */
    public function getLastMessage(Konsultasi $consultation): ?ConsultationMessage
    {
        return $consultation->messages()
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
