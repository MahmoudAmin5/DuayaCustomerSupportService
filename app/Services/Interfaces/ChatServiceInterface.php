<?php
namespace App\Services;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

interface ChatServiceInterface
{
    public function StartChat(array $data): Chat ;
}
