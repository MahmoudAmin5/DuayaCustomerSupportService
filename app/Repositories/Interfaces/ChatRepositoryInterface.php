<?php
namespace App\Repositories\Interfaces;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;

interface ChatRepositoryInterface
{
    public function findOrCreateChat($customerId, $agentId): Chat;
    public function findActiveChatByCustomer(int $customerId): ?Chat;
    public function findActiveChatByAgent(int $agentId): ?Chat;
    public function getChatById(int $chatId): ?Chat;
    public function deactivateChat(int $chatId): bool;
     public function save(Chat $chat): bool;

}
