<?php
use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    if (! $chat) return false;

    // allow if the logged in user is agent or customer of the chat
    return $user->id === $chat->agent_id || $user->id === $chat->customer_id;
});
