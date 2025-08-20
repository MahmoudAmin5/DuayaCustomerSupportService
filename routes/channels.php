<?php
use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // Check if user is participant in this chat
    $chat = Chat::find($chatId);
    return $chat && ($chat->customer_id == $user->id || $chat->agent_id == $user->id);
});

Broadcast::channel('customer-channel.{userId}', function ($user, $userId) {
    // User can only listen to their own channel
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('agent-channel', function ($user) {
    // Only agents can listen to agent channel
    return $user && $user->role === 'agent';
});
