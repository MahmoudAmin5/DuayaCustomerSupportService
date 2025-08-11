<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\AgentRepositoryInterface;

class AgentRepository implements AgentRepositoryInterface
{
    

    public function getChatsByAgent(int $agentId): Collection
    {
        return Chat::where('agent_id', $agentId)->get();
    }
    public function getFirstAvailableAgent(): ?User
    {
        return User::where('role', 'agent')
            ->whereDoesntHave('chatsAsAgent', function ($query) {
                $query->where('is_active', true);
            })
            ->first();
    }

}
