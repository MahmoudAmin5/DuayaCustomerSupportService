<?php
namespace App\Services;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

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
        return DB::transaction(function () use ($data)
        {
             $customer = $this->userRepo->firstOrCreateByPhone($data['phone']);
             $existingChat = $this->chatRepo->findActiveChatByCustomer($customer->id);

             if ($existingChat) {
                 return $existingChat;
             }

             $agent = $this->userRepo->getFirstAvailableAgent();

             if (!$agent) return null ; // No available agent
             $newChat = $this->chatRepo->findOrCreateChat($customer->id, $agent->id);

             $message = $this->messageRepo->createMessage([
                 'chat_id' => $newChat->id,
                 'user_id' => $customer->id,
                 'content' => $data['message'],
             ]);

               return $newChat;

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

    return $this->messageRepo->createMessage($messageData);
}
}
