<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Message\GetMessageResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Chat\GetChatResource;

class ChatWebController extends Controller
{
    protected $chatService;
    protected $chatRepo;

    public function __construct(\App\Services\Interfaces\ChatServiceInterface $chatService, \App\Repositories\Interfaces\ChatRepositoryInterface $chatRepo)
    {
        $this->chatService = $chatService;
        $this->chatRepo = $chatRepo;
    }
    public function startChat(Request $request)
    { // Debugging line to check the request data
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string',
            'message' => 'nullable|string',
        ]);  // Debugging line to check the validated data

        $chat = $this->chatService->startChat($validated);

        if (!$chat) {
            return abort(404, 'No Chat Found');
        }
        $chat = new GetChatResource($chat); // Transform the chat resource
        return redirect()->route('customer.show', ['chatId' => $chat->id])->with(['chat' => $chat]);
    }

    public function sendMessageAsAgent(Request $request)
    {

        $validated = $request->validate([
            'chat_id'   => 'required|exists:chats,id',
            'sender_id' => 'required|exists:users,id',
            'type'      => 'required|in:text,image,file',
            'content'   => 'nullable|string',
            'file_path' => 'nullable|file|max:20480', // max 20MB
        ]);

        $message = $this->chatService->sendMessage($validated);

        if (!$message) {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to send message',
            ], 500);
        }

        return back()->withErrors(['error' => 'Failed to send message']);
    }

    // If it's AJAX (like fetch/axios)
    if ($request->expectsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => new GetMessageResource($message),
        ]);
    }

    // If it's normal Blade form submission
    return redirect()
        ->route('chat.show', ['chatId' => $validated['chat_id']])
        ->with(['message' => new GetMessageResource($message)]);
}

    public function sendMessageAsCustomer(Request $request)
    {
        // dd($request->all());

        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'sender_id' => 'required|exists:users,id',
            'type'    => 'required|in:text,image,file',
            'content' => 'nullable|string',
            'file_path'    => 'nullable|file|max:20480', // max 20MB
        ]);

        $message = $this->chatService->sendMessage($validated);
        if (!$message) {
            return abort(500, 'Failed to send message');

        }
        if ($request->expectsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => new GetMessageResource($message),
        ]);
    }

    // If it's normal Blade form submission
    return redirect()
        ->route('customer.show', ['chatId' => $validated['chat_id']])
        ->with(['message' => new GetMessageResource($message)]);
    }
    public function showAgentChat($chatId)
    {
        $chat = $this->chatRepo->getChatById($chatId);
        $messages = $this->chatService->getChatMessages($chatId);
        // $sender_id = DB::table('messages')
        //     ->where('chat_id', $chatId) // Assuming you want to get the sender_id for the chat
        //     ->value('sender_id');
        $sender_id = Auth::guard('agent')->id(); // Get the authenticated user's ID

        return view('agent.AgentChat', [
            'chat' => $chat,
            'messages' => $messages,
            'sender_id' => $sender_id,
        ]);
    }
    public function showCustomerChat($chatId)
    {
        $chat = $this->chatRepo->getChatById($chatId);
        $messages = $this->chatService->getChatMessages($chatId);
        $sender_id = DB::table('chats')
            ->where('id', $chatId) // Assuming you want to get the sender_id for the chat
            ->value('customer_id'); // Default web guard for customers
        // dd($sender_id);

        return view('CustomerChat', compact('chat', 'messages', 'sender_id'));
    }
    public function closeChat($chatId)
    {
        $chat = $this->chatRepo->deactivateChat($chatId);
        return redirect()->route('agent.dashboard')->with('success', 'Chat closed successfully.');
    }
}
