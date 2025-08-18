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
            Chat with Agent
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
        <form id="chat-form" action="{{ route('customer.chat.send', ['chatId' => $chat->id]) }}" method="POST"
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
      @vite(['resources/js/app.js', 'resources/css/app.css'])
   <script>
// قيم جايه من السيرفر (Blade)
const chatId = "{{ $chat->id }}";
const senderId = "{{ $sender_id }}";

// دالة للتحقق من تحميل Echo
function initializeChat() {
    if (typeof window.Echo === 'undefined') {
        console.error("❌ Laravel Echo غير محمل!");
        console.log("تأكد من تحميل Echo و Pusher قبل هذا السكريبت");
        return;
    }

    const messagesContainer = document.getElementById("messages");
    if (!messagesContainer) {
        console.error("❌ عنصر الرسائل غير موجود");
        return;
    }

    // دالة لإضافة رسالة جديدة
    function addMessage(message) {
        const wrapper = document.createElement("div");
        wrapper.className = "flex mb-2 " +
            (message.sender_id == senderId ? "justify-end" : "justify-start");

        const messageDiv = document.createElement("div");
        messageDiv.className = `px-4 py-2 rounded-lg max-w-xs lg:max-w-md ${
            message.sender_id == senderId
                ? 'bg-blue-500 text-white'
                : 'bg-gray-200 text-gray-800'
        }`;

        messageDiv.textContent = message.content;
        wrapper.appendChild(messageDiv);
        messagesContainer.appendChild(wrapper);

        // التمرير لآخر رسالة
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    try {
        // الاستماع للرسائل الجديدة
        window.Echo.channel(`chat.${chatId}`)
            .listen(".MessageSent", (event) => {
                console.log("📩 رسالة وصلت:", event);

                if (event.message && event.message.content) {
                    addMessage(event.message);
                } else {
                    console.warn("⚠️ رسالة غير صحيحة:", event);
                }
            })
            .error((error) => {
                console.error("❌ خطأ في الاتصال:", error);
            });

        console.log("✅ تم تفعيل Chat بنجاح");

    } catch (error) {
        console.error("❌ خطأ في تهيئة Chat:", error);
    }
}

// انتظار تحميل Echo أو تشغيله فوراً إذا كان محملاً
if (typeof window.Echo !== 'undefined') {
    initializeChat();
} else {
    // انتظار تحميل الصفحة والسكريبتات
    document.addEventListener('DOMContentLoaded', function() {
        // محاولة كل 100ms لمدة 5 ثوان
        let attempts = 0;
        const maxAttempts = 50;

        const checkEcho = setInterval(() => {
            attempts++;

            if (typeof window.Echo !== 'undefined') {
                clearInterval(checkEcho);
                initializeChat();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkEcho);
                console.error("❌ فشل في تحميل Laravel Echo خلال 5 ثوان");
                console.log("تأكد من:");
                console.log("1. تحميل pusher-js أو socket.io-client");
                console.log("2. تحميل laravel-echo");
                console.log("3. تهيئة Echo في bootstrap.js");
            }
        }, 100);
    });
}
</script>

</body>

</html>
