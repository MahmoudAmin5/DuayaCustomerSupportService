<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\ChatServiceInterface;
use \Illuminate\Database\Eloquent\Collection;
use App\Events\MessageSent;

class ChatService implements ChatServiceInterface
{
    protected $chatRepo;
    protected $messageRepo;
    protected $userRepo;

    public function __construct(
        ChatRepositoryInterface $chatRepo,
        MessageRepositoryInterface $messageRepo,
        UserRepositoryInterface $userRepo
    ) {
        $this->chatRepo = $chatRepo;
        $this->messageRepo = $messageRepo;
        $this->userRepo = $userRepo;
    }
    public function startChat(array $data): Chat
    {
        return DB::transaction(function () use ($data) {
            $customer = $this->userRepo->firstOrCreateByPhone($data['phone'], $data['name'] ?? 'Unknown Customer');

            // Check if customer already has an active chat
            $existingChat = $this->chatRepo->findActiveChatByCustomer($customer->id);
            if ($existingChat) {
                return $existingChat->load('messages'); // return existing chat with messages
            }

            // Get an available agent
            $agent = $this->userRepo->getFirstAvailableAgent();
            if (!$agent) {
                return null; // No available agents
            }

            // Create chat
            $newChat = $this->chatRepo->findOrCreateChat($customer->id, $agent->id);

            // Store message via relationship (safe, clean)
            $newChat->messages()->create([
                'sender_id' => $customer->id,
                'chat_id' => $newChat->id,
                'content' => $data['message'], // can be string, image, etc.
            ]);

            return $newChat->load('messages'); // optional: eager load messages
        });
    }
    public function sendMessage(array $data): Message
    {
        $messageData = [
            'chat_id' => $data['chat_id'],
            'sender_id' => $data['sender_id'],
            'type'    => $data['type'] ?? 'text',
        ];

        if ($messageData['type'] === 'text') {
            $messageData['content'] = $data['content'];
        } elseif (in_array($messageData['type'], ['image', 'file'])) {
            // Upload the file
            $path = $data['file']->store('messages'); // or 'chat_uploads'
            $messageData['file_path'] = $path;
        }

        $message = $this->messageRepo->createMessage($messageData);
       broadcast(new MessageSent($message))->toOthers();
       return $message; // return the created message
    }
    public function getChatMessages(int $chatId): Collection
    {
        return $this->messageRepo->getMessagesByChatId($chatId);
    }
}
