<?php

namespace App\Http\Controllers;
use App\Models\Chat;
use App\Repositories\ChatRepository;

use App\Repositories\Interfaces\AgentRepositoryInterface;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Services\Interfaces\ChatServiceInterface;
use Illuminate\Http\Request;

class AgentController extends Controller
{
     protected $chatRepo;
     protected $agentRepo;
     protected $chatService;

    public function __construct(ChatRepositoryInterface $chatRepo, AgentRepositoryInterface $agentRepo, ChatServiceInterface $chatService)
    {
        $this->chatRepo = $chatRepo;
        $this->agentRepo = $agentRepo;
        $this->chatService = $chatService;
    }
    public function index($agentId)
    {
       $chats = $this->agentRepo->getChatsByAgent($agentId);
       return view('agent.chats', compact('chats'));
    }
     public function show($chatId)
    {
        $chat = $this->chatRepo->getChatById($chatId);
        $messages = $this->chatService->getChatMessages($chatId);

        return view('agent.chat', compact('chat', 'messages'));
    }
     public function closeChat($chatId)
    {
        $this->chatRepo->deactivateChat($chatId);
        return redirect()->route('agent.chats')
            ->with('success', 'Chat closed successfully.');
    }






}
