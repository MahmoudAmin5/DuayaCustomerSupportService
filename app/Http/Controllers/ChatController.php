<?php

namespace App\Http\Controllers;

use App\Http\Resources\Message\GetMessageResource;
use App\Http\Resources\Chat\GetChatResource;
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
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $chat = $this->chatService->startChat($validated);

        if (!$chat) {
            return response()->json(['message' => 'No available agents'], 400);
        }
        $chat = new GetChatResource($chat); // Transform the chat resource
        return response()->json(['chat' => $chat], 201);
    }

    // Send a new message in an existing chat
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'sender_id' => 'required|exists:users,id',
            'type'    => 'required|in:text,image,file',
            'content' => 'nullable|string',
            'file'    => 'nullable|file|max:20480', // max 20MB
        ]);

        $message = $this->chatService->sendMessage($validated);
        if (!$message) {
            return response()->json(['message' => 'Failed to send message'], 500);
        }
        $messages = new GetMessageResource($message); // Transform the message resource
        return response()->json(['message' => $messages], 201);
    }
    public function getChatMessages(Request $request): JsonResponse
    {
         $chatId = $request->query('chatId');
        $messages = $this->chatService->getChatMessages($chatId);

        if ($messages->isEmpty()) {
            return response()->json(['message' => 'No messages found'], 404);
        }
        $messages = GetMessageResource::collection($messages);
        return response()->json(['messages' => $messages], 200);
    }
    public function showChat($chatId)
{
    return view('chat', [
        'chatId' => $chatId
    ]);
}
}
