<?php

namespace App\Services\Interfaces;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use \App\Models\Message;
use \Illuminate\Database\Eloquent\Collection;

interface ChatServiceInterface
{
    public function startChat(array $data): Chat;
    public function sendMessage(array $data): Message;
    public function getChatMessages(int $chatId): Collection;
}
