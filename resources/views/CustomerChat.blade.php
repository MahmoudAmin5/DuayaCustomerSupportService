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

                {{-- نوع الرسالة --}}
                @if($message->type === 'text')
                    <p>{{ $message->content }}</p>

                @elseif($message->type === 'image')
                    <img src="{{ asset('storage/' . $message->file_path) }}"
                         alt="Image" class="max-w-[200px] rounded">

                @elseif($message->type === 'file')
                    <a href="{{ asset('storage/' . $message->file_path) }}"
                       class="underline text-blue-200 hover:text-blue-300"
                       target="_blank">
                        📄 {{ $message->content ?? 'Download File' }}
                    </a>

                @elseif($message->type === 'voice')
                    <audio controls class="w-full">
                        <source src="{{ asset('storage/' . $message->file_path) }}" type="audio/mpeg">
                        متصفحك لا يدعم تشغيل الصوت
                    </audio>
                @endif

                {{-- الوقت --}}
                <div class="text-xs opacity-70 mt-1">
                    {{ $message->created_at->format('H:i') }}
                </div>
            </div>
        </div>
    @endforeach
</div>


        {{-- Form --}}
        <form id="chat-form" action="{{ route('customer.chat.send', ['chatId' => $chat->id]) }}" method="POST"
            enctype="multipart/form-data" class="flex border-t p-3 space-x-2 items-center">
            @csrf
            <input type="hidden" name="chat_id" value="{{ $chat->id }}">
            <input type="hidden" name="sender_id" value="{{ $sender_id }}">
            <input type="hidden" name="type" value="text" id="message-type">

            <!-- Text message -->
            <textarea name="content" id="content-input"
                class="flex-1 border rounded-l-lg p-2 focus:outline-none resize-none"
                placeholder="Type a message..."></textarea>

            <!-- File Upload -->
            <input type="file" name="file_path" id="file-input" class="hidden">
            <button type="button" onclick="document.getElementById('file-input').click()"
                class="bg-gray-200 px-3 py-2 rounded-lg hover:bg-gray-300">
                📎
            </button>

            <!-- Preview -->
            <div id="file-preview" class="text-sm text-gray-600"></div>

            <!-- Send Button -->
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                Send
            </button>
        </form>
         <script>
    document.addEventListener("DOMContentLoaded", function () {
        let messagesDiv = document.getElementById("messages");
        if (messagesDiv) {
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    });
</script>

        <script>
            const fileInput = document.getElementById('file-input');
            const preview = document.getElementById('file-preview');
            const typeInput = document.getElementById('message-type');
            const contentInput = document.getElementById('content-input');

            fileInput.addEventListener('change', function () {
                preview.innerHTML = ''; // مسح أي معاينة سابقة

                if (this.files && this.files[0]) {
                    let file = this.files[0];
                    let ext = file.name.split('.').pop().toLowerCase();

                    // لو صورة → نعرضها كمعاينة ونغيّر type = image
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                        typeInput.value = "image";

                        let img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.className = "h-16 w-16 object-cover rounded";
                        preview.appendChild(img);
                    }
                    // لو صوت → نغير type = voice
                    else if (['mp3', 'wav', 'ogg'].includes(ext)) {
                        typeInput.value = "voice";

                        let text = document.createElement('span');
                        text.textContent = "🎵 " + file.name;
                        preview.appendChild(text);
                    }
                    // لو ملف عادي → نغير type = file
                    else {
                        typeInput.value = "file";

                        let text = document.createElement('span');
                        text.textContent = "📄 " + file.name;
                        preview.appendChild(text);
                    }


                    contentInput.value = "";
                }
            });
        </script>

        <script>
            // ✅ Listen to Laravel Echo
            window.Echo.channel('chat.{{ $chat->id }}')
                .listen('MessageSent', (e) => {
                    addMessageToUI(e.message);
                });

            // ✅ Send Message (AJAX)
            document.getElementById('sendMessageForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                let formData = new FormData(this);
                if (formData.get('file').name) {
                    formData.set('type', 'file'); // detect file
                } else {
                    formData.set('type', 'text'); // default text
                }

                let response = await fetch(this.action, {
                    method: 'POST',
                    headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: formData
                });

                let data = await response.json();

                if (data.success) {
                    addMessageToUI(data.message); // show my own message instantly
                    this.reset();
                }
            });

            // ✅ Append message to UI
            function addMessageToUI(message) {
                let container = document.getElementById('messages');
                let div = document.createElement('div');

                if (message.type === "text") {
                    div.innerHTML = `<strong>${message.sender_id}:</strong> ${message.content}`;
                } else if (message.type === "image") {
                    div.innerHTML = `<strong>${message.sender_id}:</strong> <img src="/storage/${message.file_path}" width="150">`;
                } else {
                    div.innerHTML = `<strong>${message.sender_id}:</strong> <a href="/storage/${message.file_path}" target="_blank">Download File</a>`;
                }

                container.appendChild(div);
                container.scrollTop = container.scrollHeight; // auto scroll down
            }
        </script>
</body>

</html>
