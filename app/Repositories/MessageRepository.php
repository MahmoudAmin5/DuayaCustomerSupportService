<?php
namespace App\Repositories;

use App\Models\Message;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    public function createMessage(array $data): Message
    {
        return Message::create($data);
    }
     public function findMessageById(int $id): ?Message {
         return Message::find($id);
     }
     public function getMessagesByChatId(int $chatId): Collection {
         return Message::where('chat_id', $chatId)->latest()->get();
     }
     public function saveMessage(Message $message): bool {
         return $message->save();
     }

}
