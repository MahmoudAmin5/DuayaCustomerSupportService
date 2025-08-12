<!-- resources/views/chat.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg w-full max-w-3xl flex flex-col">

        <!-- Chat Header -->
        <div class="bg-indigo-600 text-white p-4 rounded-t-lg flex items-center">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($chat->customer->name ?? 'User') }}"
                class="w-10 h-10 rounded-full border border-white mr-3">
            <h2 class="text-lg font-semibold">{{ $chat->customer->name ?? 'Chat' }}</h2>
        </div>

        <!-- Messages -->
        <div id="messages" class="flex-1 overflow-y-auto p-4 space-y-3 h-[500px]">
            @foreach($messages as $message)
                <div class="flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div
                        class="{{ $message->sender_id == auth()->id() ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-900' }} px-4 py-2 rounded-lg max-w-xs break-words">
                        {{ $message->content }}
                        <div class="text-xs opacity-70 mt-1">{{ $message->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @endforeach

            @if(session('message'))
                @php $msg = session('message'); @endphp
                <div class="flex {{ $msg->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div
                        class="{{ $msg->sender_id == auth()->id() ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-900' }} px-4 py-2 rounded-lg max-w-xs break-words">
                        {{ $msg->content }}
                        <div class="text-xs opacity-70 mt-1">{{ $msg->created_at }}</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Input Area -->
        <form action="{{ route('chat.send', ['chatId' => $chat->id]) }}" method="POST" enctype="multipart/form-data" class="flex border-t p-3">
            @csrf
            <input type="hidden" name="chat_id" value="{{ $chat->id }}">
            <input type="hidden" name="sender_id" value="{{ $sender_id }}">
            <input type="hidden" name="type" value="text">
            <input type="text" name="content" class="flex-1 border rounded-l-lg p-2 focus:outline-none"
                placeholder="Type a message..." required>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700">
                Send
            </button>
        </form>
    </div>
</body>

</html>
