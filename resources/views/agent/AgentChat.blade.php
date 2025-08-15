<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Agent Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Laravel CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Pusher & Laravel Echo --}}
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="{{ asset('js/echo.js') }}"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-lg w-full max-w-3xl flex flex-col h-[80vh]">

        {{-- Header --}}
        <div class="bg-green-500 text-white p-4 rounded-t-lg flex justify-between items-center">
            <span class="text-lg font-semibold">Chat with Customer</span>
            <form method="POST" action="{{ route('agent.chat.close', $chat->id) }}">
                @csrf
                <button type="submit" class="bg-red-500 px-3 py-1 rounded hover:bg-red-600">
                    Close Chat
                </button>
            </form>
        </div>

        {{-- Messages --}}
        <div id="messages" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-3">
            @foreach($messages as $message)
                <div class="flex {{ $message->sender_id == $sender_id ? 'justify-end' : 'justify-start' }}">
                    <div
                        class="{{ $message->sender_id == $sender_id ? 'bg-green-500 text-white' : 'bg-white text-gray-900 border' }} px-4 py-2 rounded-lg max-w-xs shadow">
                        {{ $message->content }}
                        <div class="text-xs opacity-70 mt-1">{{ $message->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Form --}}
        <form action="{{ route('chat.send', ['chatId' => $chat->id]) }}" method="POST" class="flex border-t p-3">
            @csrf
            <input type="hidden" name="chat_id" value="{{ $chat->id }}">
            <input type="hidden" name="sender_id" value="{{ $sender_id }}">
            <input type="hidden" name="type" value="text">
            <textarea name="content" class="flex-1 border rounded-l-lg p-2 focus:outline-none resize-none"
                placeholder="Type a message..." required></textarea>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-lg hover:bg-blue-600">
                Send
            </button>
        </form>
    </div>

    <script>
        const messagesEl = document.getElementById('messages');
        messagesEl.scrollTop = messagesEl.scrollHeight;

        window.Echo.private(`chat.{{ $chat->id }}`)
            .listen('MessageSent', (e) => {
                const isMine = e.message.sender_id == {{ $sender_id }};
                const div = document.createElement('div');
                div.className = `flex ${isMine ? 'justify-end' : 'justify-start'}`;
                div.innerHTML = `
                <div class="${isMine ? 'bg-green-500 text-white' : 'bg-white text-gray-900 border'} px-4 py-2 rounded-lg max-w-xs shadow">
                    ${e.message.content}
                    <div class="text-xs opacity-70 mt-1">${e.message.time}</div>
                </div>`;
                messagesEl.appendChild(div);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            });
    </script>

</body>

</html>
