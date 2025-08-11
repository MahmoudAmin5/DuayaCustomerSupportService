<<?php
namespace App\Services\interfaces;

use Illuminate\Support\Collection;

interface AgentServiceInterface
{
    public function getActiveChats(): Collection;
    
}
