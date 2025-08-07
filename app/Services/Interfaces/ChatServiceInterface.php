<?php

namespace App\Services\Interfaces;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use \App\Models\Message;

interface ChatServiceInterface
{
    public function StartChat(array $data): Chat;
    public function sendMessage(array $data): Message;
}
