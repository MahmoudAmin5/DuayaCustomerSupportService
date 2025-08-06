<?php
use App\Models\Chat;
use App\Models\User;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ChatRepository implements ChatRepositoryInterface
{
    public function findOrCreateChat($customerId, $agentId): Chat
    {
        return Chat::firstOrCreate([
            'customer_id' => $customerId,
            'agent_id' => $agentId,
            'is_active' => true
        ]);
    }

    public function findActiveChatByCustomer(int $customerId): ?Chat
    {
        return Chat::where('customer_id', $customerId)
            ->where('is_active', true)
            ->first();
    }
    public function findActiveChatByAgent(int $agentId): ?Chat
    {
        return Chat::where('agent_id', $agentId)
            ->where('is_active', true)
            ->first();
    }
    public function getChatsByAgent(int $agentId): Collection{
        return Chat::where('agent_id', $agentId)
            ->latest()
            ->get();
    }
     public function save(Chat $chat): bool{
        return $chat->save();
     }


}
