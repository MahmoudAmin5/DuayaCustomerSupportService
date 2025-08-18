<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Customer Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Laravel CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">


    {{-- Pusher & Laravel Echo --}}
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-lg w-full max-w-3xl flex flex-col h-[80vh]">

        {{-- Header --}}
        <div class="bg-blue-500 text-white p-4 rounded-t-lg text-lg font-semibold">
            Chat with Customer
        </div>

        {{-- Messages --}}
        <div id="messages" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-3">
            @foreach($messages as $message)
                <div class="flex {{ $message->sender_id == $sender_id ? 'justify-end' : 'justify-start' }}">
                    <div
                        class="{{ $message->sender_id == $sender_id ? 'bg-blue-500 text-white' : 'bg-white text-gray-900 border' }} px-4 py-2 rounded-lg max-w-xs shadow">
                        {{ $message->content }}
                        <div class="text-xs opacity-70 mt-1">{{ $message->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Form --}}
        <form id="chat-form" action="{{ route('chat.send', ['chatId' => $chat->id]) }}" method="POST"
            class="flex border-t p-3">
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
        // قيم جايه من السيرفر (Blade)
        const chatId = "{{ $chat->id }}";
        const senderId = "{{ $sender_id }}";
        const messages = document.getElementById("messages");

        if (window.Echo) {
            window.Echo.channel(`chat.${chatId}`)
                .listen("App\\Events\\MessageSent", (event) => {
                    console.log("📩 New message:", event);

                    // إنشاء الرسالة
                    const wrapper = document.createElement("div");
                    wrapper.className = "flex " +
                        (event.sender_id == senderId ? "justify-end" : "justify-start");

                    wrapper.innerHTML = `
                    <div class="px-4 py-2 rounded-lg max-w-xs shadow ${event.sender_id == senderId
                            ? "bg-blue-500 text-white"
                            : "bg-white text-gray-900 border"
                        }">
                        ${event.content}
                        <div class="text-xs opacity-70 mt-1">${event.time}</div>
                    </div>
                `;

                    // ضيف الرسالة في الـ DOM
                    messages.appendChild(wrapper);

                    // Scroll لآخر رسالة
                    messages.scrollTop = messages.scrollHeight;
                });
        } else {
            console.error("❌ Laravel Echo مش متعرف، تأكد إن app.js متحمل صح.");
        }
    </script>
</body>

</html>
