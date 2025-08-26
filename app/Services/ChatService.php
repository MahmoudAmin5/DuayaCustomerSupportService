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
use App\Http\Resources\Message\GetMessageResource;
use App\Repositories\Interfaces\AgentRepositoryInterface;

class ChatService implements ChatServiceInterface
{
    protected $chatRepo;
    protected $messageRepo;
    protected $userRepo;
    protected $agentRepo;

    public function __construct(
        ChatRepositoryInterface $chatRepo,
        MessageRepositoryInterface $messageRepo,
        UserRepositoryInterface $userRepo,
        AgentRepositoryInterface $agentRepo
    ) {
        $this->chatRepo = $chatRepo;
        $this->messageRepo = $messageRepo;
        $this->userRepo = $userRepo;
        $this->agentRepo = $agentRepo;
    }
    public function startChat(array $data): ?Chat
    {
        return DB::transaction(function () use ($data) {

            $customer = $this->userRepo->firstOrCreateByPhone($data['phone'], $data['name'] ?? 'Unknown Customer');

            $existingChat = $this->chatRepo->findActiveChatByCustomer($customer->id);
            if ($existingChat) {
                return $existingChat->load('messages');
            }

            $agent = $this->agentRepo->getFirstAvailableAgent();
            if (!$agent) {
                return null;
            }


            $newChat = $this->chatRepo->findOrCreateChat($customer->id, $agent->id);

            $newChat->messages()->create([
                'sender_id' => $customer->id,
                'chat_id' => $newChat->id,
                'content' => $data['message'],
            ]);

            return $newChat->load('messages');
        });
    }
    public function sendMessage(array $data): ?Message
    {
        ds($data);
        $chat = $this->chatRepo->getChatById($data['chat_id']);
        if (!$chat) {
            throw new \Exception('Chat not found.');
        }

        if (!in_array($data['sender_id'], [$chat->customer_id, $chat->agent_id])) {
            throw new \Exception('You are not a participant in this chat.');
        }
        // dd($data);

        $messageData = [
            'chat_id'   => $data['chat_id'],
            'sender_id' => $data['sender_id'],
            'type'      => $data['type'] ?? 'text',
        ];

        if ($messageData['type'] === 'text') {
            if (empty($data['content'])) {
                throw new \InvalidArgumentException('Content is required for text messages.');
            }
            $messageData['content'] = $data['content'];
        } elseif (in_array($messageData['type'], ['image', 'file', 'voice'])) {
            if (empty($data['file_path'])) {
                throw new \InvalidArgumentException('File is required for file/image messages.');
            }

            $path = $data['file_path']->storeAs(
                'messages',
                uniqid() . '.' . $data['file_path']->getClientOriginalExtension(),
                'public' // مهم علشان يتخزن جوه storage/app/public
            );
            ds('path file: ' . $path);
            $messageData['file_path'] = $path;
        }


        $message = $this->messageRepo->createMessage($messageData);
        ds($message);

        broadcast(new MessageSent($message))->toOthers();
        return $message;
    }
    public function getChatMessages(int $chatId): Collection
    {
        return $this->messageRepo->getMessagesByChatId($chatId);
    }
}
