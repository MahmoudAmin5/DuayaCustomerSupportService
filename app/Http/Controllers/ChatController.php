<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\ChatServiceInterface;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
     protected $chatService;

    public function __construct(ChatServiceInterface $chatService)
    {
        $this->chatService = $chatService;
    }

    // Start a new chat or return existing active one
    public function startChat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $chat = $this->chatService->startChat($validated);

        if (!$chat) {
            return response()->json(['message' => 'No available agents'], 400);
        }

        return response()->json(['chat' => $chat], 201);
    }

    // Send a new message in an existing chat
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'user_id' => 'required|exists:users,id',
            'type'    => 'required|in:text,image,file',
            'content' => 'nullable|string',
            'file'    => 'nullable|file|max:20480', // max 20MB
        ]);

        $message = $this->chatService->sendMessage($validated);

        return response()->json(['message' => $message], 201);
    }
}
