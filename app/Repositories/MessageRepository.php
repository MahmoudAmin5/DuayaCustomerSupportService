<?php
namespace App\Repositories;

use App\Models\Message;
use App\Repositories\Interfaces\MessageRepositoryInterface;

class MessageRepository implements MessageRepositoryInterface
{
    public function createMessage(array $data)
    {
        return Message::create($data);
    }
}
