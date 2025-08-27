<?php

namespace App\Repositories;

use App\Models\Message;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    public function createMessage(array $data): Message
    {
        return Message::create($data)->load('sender');
    }
    public function findMessageById(int $id): ?Message
    {
        return Message::find($id);
    }
    public function getMessagesByChatId($chatId): Collection
    {
        return Message::with('sender')->where('chat_id', $chatId)->get();
    }
    public function saveMessage(Message $message): bool
    {
        return $message->save();
    }
}
