<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return Chat::where('id', $chatId)
    ->where(function($query) use ($user){
           $query->where('customer_id', $user->id)
                  ->orWhere('agent_id', $user->id);
    })
    ->exists();
});
