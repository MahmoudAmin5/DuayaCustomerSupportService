<?php
namespace App\Repositories\Interfaces;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;


interface AgentRepositoryInterface
{
    public function getChatsByAgent(int $agentId): Collection;
     public function getFirstAvailableAgent(): ?User;

}
