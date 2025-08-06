<?php
use App\Models\Chat;
use App\Models\User;
use App\Repositories\Interfaces\ChatRepositoryInterface;

class ChatRepository implements ChatRepositoryInterface
{
    public function findOrCreateChat($customerId, $agentId)
    {
        return Chat::firstOrCreate([
            'customer_id' => $customerId,
            'agent_id' => $agentId
        ]);
    }
}
