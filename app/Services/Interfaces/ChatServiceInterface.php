<?php
namespace App\Services;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;

// class ChatService
// {
//     public function __construct(
//         protected ChatRepositoryInterface $chatRepo,
//         protected MessageRepositoryInterface $messageRepo)
//     {
//         $this->chatRepo = $chatRepo;
//         $this->messageRepo = $messageRepo;
//     }
//     // public function StartChat(int $customerId): Chat
//     // {
//     //     $existingChat = $this->chatRepo->findActiveChatByCustomer($customerId);
//     //     if($existingChat) return $existingChat;
//     //     // $availableAgent = $this->chatRepo->findActiveChatByAgent();
//     // }
// }
