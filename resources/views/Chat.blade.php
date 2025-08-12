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
        </div>

        <!-- Input Area -->
        <form id="chatForm" class="flex border-t p-3">
            <input type="text" id="messageInput" name="message"
                class="flex-1 border rounded-l-lg p-2 focus:outline-none" placeholder="Type a message...">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700">
                Send
            </button>
        </form>
    </div>

    <script>
        const chatId = {{ $chat->id }};
        const authId = {{ auth()->id() }};
        const messagesContainer = document.getElementById('messages');
        const messageInput = document.getElementById('messageInput');
        const chatForm = document.getElementById('chatForm');

        // Auto-scroll to bottom
        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        scrollToBottom();

        // Append single message to chat
        function appendMessage(msg)
        {
            const isMine = msg.sender_id == authId;
            const msgHTML = `
                <div class="flex ${isMine ? 'justify-end' : 'justify-start'}">
                    <div class="${isMine ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-900'} px-4 py-2 rounded-lg max-w-xs break-words">
                        ${msg.content}
                        <div class="text-xs opacity-70 mt-1">${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                    </div>
                </div>
            `;
            messagesContainer.insertAdjacentHTML('beforeend', msgHTML);
            scrollToBottom();
        }

        // Send message via AJAX
        chatForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            let messageText = messageInput.value.trim();
            if (!messageText) return;

            const res = await fetch(`/chat/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ chat_id: chatId, message: messageText })
            });

            if (res.ok) {
                const newMsg = await res.json(); // expects message JSON back from controller
                appendMessage(newMsg);
                messageInput.value = '';
            }
        });

        // Load all messages
        async function loadMessages() {
            const res = await fetch(`/chat/${chatId}/messages`);
            if (!res.ok) return;
            const data = await res.json();
            messagesContainer.innerHTML = '';
            data.forEach(msg => appendMessage(msg));
        }

        // Poll for new messages every 2s
        setInterval(loadMessages, 2000);
    </script>
</body>

</html>
