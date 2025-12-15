<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine if user can view the message
     */
    public function view(User $user, Message $message): bool
    {
        // User can view messages from their conversations
        // This assumes message has a conversation relationship
        return $message->conversation->hasUser($user->id);
    }

    /**
     * Determine if user can create a message
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create messages
    }

    /**
     * Determine if user can delete the message
     */
    public function delete(User $user, Message $message): bool
    {
        // Only sender can delete their own messages
        if ($user->id === $message->sender_id) {
            return true;
        }

        // Admin can delete any message
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }
}
