<?php
namespace App\Repositories\Interfaces;
interface ChatRepositoryInterface
{
    public function findOrCreateChat($customerId, $agentId);
}
