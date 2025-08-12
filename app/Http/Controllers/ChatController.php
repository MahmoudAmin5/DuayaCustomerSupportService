<?php

namespace App\Http\Controllers;

use App\Http\Resources\Message\GetMessageResource;
use App\Http\Resources\Chat\GetChatResource;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use Illuminate\Http\Request;
use App\Services\Interfaces\ChatServiceInterface;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    protected $chatService;
    protected $chatRepo;

    public function __construct(ChatServiceInterface $chatService, ChatRepositoryInterface $chatRepo)
    {
        $this->chatService = $chatService;
        $this->chatRepo = $chatRepo;
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
        return response()->json([
            'message' => new GetMessageResource($message)
        ], 201);
    }


    public function getChatMessages(Request $request): JsonResponse
    {
        $chatId = $request->route('chatId');
        $messages = $this->chatService->getChatMessages($chatId);

        if ($messages->isEmpty()) {
            return response()->json(['message' => 'No messages found'], 404);
        }
        $messages = GetMessageResource::collection($messages);
        return response()->json(['messages' => $messages], 200);
    }
    public function showChat($chatId)
    {
        $chat = $this->chatRepo->getChatById($chatId);
        $messages = $this->chatService->getChatMessages($chatId);

        return view('chat', [
            'chat' => $chat,
            'messages' => $messages
        ]);
    }

    public function startChatWeb(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        // call existing service (it returns Chat or null)
        $chat = $this->chatService->startChat($validated);

        if (!$chat) {
            return back()->withErrors(['phone' => 'No agents available right now. Please try later.'])->withInput();
        }

        // log the customer in to create session so private channel auth works
        // chat has customer_id (user model). If you prefer to avoid login, use token-based approach instead.
        \Illuminate\Support\Facades\Auth::loginUsingId($chat->customer_id);

        // redirect to web chat page
        return redirect()->route('chat.view', ['chatId' => $chat->id]);
    }
}
