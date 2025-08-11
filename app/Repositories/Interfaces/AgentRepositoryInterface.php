<?php
namespace App\Repositories\Interfaces;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;


interface AgentRepositoryInterface
{
    public function getAvailableAgents();
    public function findById(int $id);
    public function findActiveChatByAgent(int $agentId): ?Chat;
    public function getChatsByAgent(int $agentId): Collection;

}
