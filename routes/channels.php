<?php

use Illuminate\Support\Facades\Broadcast;

use App\Models\Conversation;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return Conversation::query()
        ->whereKey($conversationId)
        ->whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id)
                ->whereNull('conversation_user.left_at');
        })
        ->exists();
});