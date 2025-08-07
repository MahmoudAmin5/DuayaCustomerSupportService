<?php
namespace App\Services;
use App\Models\Chat;
use App\Models\User;
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
}
