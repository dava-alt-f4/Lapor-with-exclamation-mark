<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.inbox', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    if ($user->role === 'admin') {
        return true;
    }

    $conversation = Conversation::find($conversationId);

    return $conversation instanceof Conversation && (int) $conversation->user_id === (int) $user->id;
});
