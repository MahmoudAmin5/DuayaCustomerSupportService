<?php
namespace App\Repositories\Interfaces;

use App\Models\Message;
use Illuminate\Support\Collection;

interface MessageRepositoryInterface
{
    public function createMessage(array $data): message;
    public function findMessageById(int $id): ?Message;
    public function getMessagesByChatId(int $chatId): Collection;

    public function saveMessage(Message $message): bool;

}
