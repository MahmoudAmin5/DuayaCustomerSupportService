<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>customer Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}" />
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}" />
    <meta name="user-type" content="agent" />
    <meta name="user-id" content="{{ $sender_id }}" />


    {{-- Laravel CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Include notification manager --}}
    <script src="{{ asset('ChatManager.js') }}"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-lg w-full max-w-3xl flex flex-col h-[80vh]">

        {{-- Header --}}
        <div class="bg-blue-500 text-white p-4 rounded-t-lg text-lg font-semibold flex justify-between items-center">
            <span>
                Chat with {{ $chat->agent ? $chat->agent->name : 'Customer' }}
                <span id="online-status" class="text-sm opacity-75"></span>
            </span>

            <div class="flex space-x-2">
                {{-- Connection Status Indicator --}}
                <div id="connection-status" class="flex items-center">
                    <div id="status-dot" class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                    <span id="status-text" class="text-xs">Connecting...</span>
                </div>
            </div>
        </div>

        {{-- Messages Container --}}
        <div id="messages" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-3">
            @foreach($messages as $message)
                <div class="flex {{ $message->sender_id == $sender_id ? 'justify-end' : 'justify-start' }}">
                    <div
                        class="{{ $message->sender_id == $sender_id ? 'bg-blue-500 text-white' : 'bg-white text-gray-900 border' }} px-4 py-2 rounded-lg max-w-xs shadow">
                        @if($message->type === 'text')
                            <p>{{ $message->content }}</p>

                        @elseif($message->type === 'image')
                            <img src="{{ asset('storage/' . $message->file_path) }}" alt="Image"
                                 class="max-w-[200px] rounded cursor-pointer"
                                 onclick="openImageModal('{{ asset('storage/' . $message->file_path) }}')">

                        @elseif($message->type === 'file')
                            <a href="{{ asset('storage/' . $message->file_path) }}"
                                class="underline {{ $message->sender_id == $sender_id ? 'text-blue-200 hover:text-blue-300' : 'text-blue-500 hover:text-blue-700' }}"
                                target="_blank" download>
                                📄 {{ $message->content ?? 'Download File' }}
                            </a>

                        @elseif($message->type === 'voice')
                            <audio controls class="w-full">
                                <source src="{{ asset('storage/' . $message->file_path) }}" type="audio/mpeg">
                                <source src="{{ asset('storage/' . $message->file_path) }}" type="audio/wav">
                                Your browser does not support audio playback
                            </audio>
                        @endif

                        <div class="flex justify-between items-center mt-1">
                            <div class="text-xs opacity-70">
                                {{ $message->created_at->format('H:i') }}
                            </div>
                            @if($message->sender_id == $sender_id)
                                <div class="text-xs opacity-70">
                                    <span id="message-status-{{ $message->id }}" class="message-status">✓</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Typing Indicator --}}
            <div id="typing-indicator" class="flex justify-start hidden">
                <div class="bg-white text-gray-900 border px-4 py-2 rounded-lg max-w-xs shadow">
                    <div class="flex items-center space-x-1">
                        <div class="typing-dot w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="typing-dot w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="typing-dot w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <span class="text-xs text-gray-500 ml-2">typing...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form id="chat-form" action="{{ route('customer.chat.send', ['chatId' => $chat->id]) }}" method="POST"
            enctype="multipart/form-data" class="border-t bg-white">
            @csrf
            <input type="hidden" name="chat_id" value="{{ $chat->id }}">
            <input type="hidden" name="sender_id" value="{{ $sender_id }}">
            <input type="hidden" name="type" value="text" id="message-type">

            {{-- File Preview Area --}}
            <div id="file-preview-container" class="hidden p-3 border-b bg-gray-50">
                <div class="flex items-center justify-between">
                    <div id="file-preview" class="flex items-center space-x-2"></div>
                    <button type="button" onclick="clearFileSelection()"
                        class="text-gray-500 hover:text-red-500">
                        ✕ Remove
                    </button>
                </div>
            </div>

            {{-- Input Area --}}
            <div class="flex p-3 space-x-2 items-end">
                <!-- Text message -->
                <div class="flex-1">
                    <textarea name="content" id="content-input"
                        class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        placeholder="Type a message..." rows="1" maxlength="1000"></textarea>
                    <div class="text-xs text-gray-500 mt-1">
                        <span id="char-count">0</span>/1000 characters
                    </div>
                </div>

                <!-- File Upload -->
                <input type="file" name="file_path" id="file-input" class="hidden"
                       accept="image/*,audio/*,.pdf,.doc,.docx,.txt,.zip,.rar">

                <!-- Upload Button -->
                <button type="button" onclick="document.getElementById('file-input').click()"
                    class="bg-gray-200 hover:bg-gray-300 p-2 rounded-lg transition-colors"
                    title="Attach file">
                    📎
                </button>

                <!-- Voice Record Button -->
                <button type="button" id="voice-record-btn"
                    class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition-colors"
                    title="Record voice message">
                    🎤
                </button>

                <!-- Send Button -->
                <button type="submit" id="send-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Send
                </button>
            </div>
        </form>
    </div>

    {{-- Image Modal --}}
    <div id="image-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="relative max-w-4xl max-h-4xl p-4">
            <img id="modal-image" src="" alt="Full size image" class="max-w-full max-h-full rounded">
            <button onclick="closeImageModal()"
                class="absolute top-2 right-2 text-white text-2xl hover:text-gray-300">
                ✕
            </button>
        </div>
    </div>

    {{-- Notification Container --}}
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // Enhanced chat functionality
        document.addEventListener("DOMContentLoaded", function () {
            // Auto-scroll to bottom on load
            scrollToBottom();

            // Initialize file handling
            initializeFileHandling();

            // Initialize character counter
            initializeCharacterCounter();

            // Initialize voice recording
            initializeVoiceRecording();

            // Initialize connection status
            initializeConnectionStatus();
        });

        function scrollToBottom() {
            const messagesDiv = document.getElementById("messages");
            if (messagesDiv) {
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }
        }

        function initializeFileHandling() {
            const fileInput = document.getElementById('file-input');
            const preview = document.getElementById('file-preview');
            const previewContainer = document.getElementById('file-preview-container');
            const typeInput = document.getElementById('message-type');
            const contentInput = document.getElementById('content-input');

            fileInput.addEventListener('change', function () {
                preview.innerHTML = '';
                previewContainer.classList.add('hidden');

                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const ext = file.name.split('.').pop().toLowerCase();
                    const fileSize = (file.size / 1024 / 1024).toFixed(2); // Size in MB

                    // Check file size (20MB limit)
                    if (file.size > 20 * 1024 * 1024) {
                        alert('File size must be less than 20MB');
                        this.value = '';
                        return;
                    }

                    // Show preview container
                    previewContainer.classList.remove('hidden');

                    // Handle different file types
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                        typeInput.value = "image";
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.className = "h-16 w-16 object-cover rounded border";
                        preview.appendChild(img);

                        const info = document.createElement('div');
                        info.className = "text-sm text-gray-600";
                        info.innerHTML = `<span class="font-medium">📷 Image:</span> ${file.name} (${fileSize} MB)`;
                        preview.appendChild(info);

                    } else if (['mp3', 'wav', 'ogg', 'm4a'].includes(ext)) {
                        typeInput.value = "voice";
                        const icon = document.createElement('div');
                        icon.className = "w-16 h-16 bg-red-100 rounded border flex items-center justify-center text-2xl";
                        icon.textContent = "🎵";
                        preview.appendChild(icon);

                        const info = document.createElement('div');
                        info.className = "text-sm text-gray-600";
                        info.innerHTML = `<span class="font-medium">🎵 Audio:</span> ${file.name} (${fileSize} MB)`;
                        preview.appendChild(info);

                    } else {
                        typeInput.value = "file";
                        const icon = document.createElement('div');
                        icon.className = "w-16 h-16 bg-gray-100 rounded border flex items-center justify-center text-2xl";
                        icon.textContent = "📄";
                        preview.appendChild(icon);

                        const info = document.createElement('div');
                        info.className = "text-sm text-gray-600";
                        info.innerHTML = `<span class="font-medium">📄 File:</span> ${file.name} (${fileSize} MB)`;
                        preview.appendChild(info);
                    }

                    // Clear text input when file is selected
                    contentInput.value = "";
                }
            });
        }

        function clearFileSelection() {
            const fileInput = document.getElementById('file-input');
            const preview = document.getElementById('file-preview');
            const previewContainer = document.getElementById('file-preview-container');
            const typeInput = document.getElementById('message-type');

            fileInput.value = '';
            preview.innerHTML = '';
            previewContainer.classList.add('hidden');
            typeInput.value = 'text';
        }

        function initializeCharacterCounter() {
            const textarea = document.getElementById('content-input');
            const charCount = document.getElementById('char-count');

            textarea.addEventListener('input', function() {
                const count = this.value.length;
                charCount.textContent = count;

                // Auto-resize textarea
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';

                // Change color when approaching limit
                if (count > 900) {
                    charCount.className = 'text-red-500 font-medium';
                } else if (count > 800) {
                    charCount.className = 'text-yellow-500 font-medium';
                } else {
                    charCount.className = 'text-gray-500';
                }
            });
        }

        function initializeVoiceRecording() {
            const recordBtn = document.getElementById('voice-record-btn');
            let mediaRecorder;
            let audioChunks = [];
            let isRecording = false;

            recordBtn.addEventListener('click', function() {
                if (isRecording) {
                    stopRecording();
                } else {
                    startRecording();
                }
            });

            function startRecording() {
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then(stream => {
                        mediaRecorder = new MediaRecorder(stream);
                        audioChunks = [];

                        mediaRecorder.ondataavailable = event => {
                            audioChunks.push(event.data);
                        };

                        mediaRecorder.onstop = () => {
                            const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                            const audioFile = new File([audioBlob], 'voice_message.wav', { type: 'audio/wav' });

                            // Simulate file input
                            const fileInput = document.getElementById('file-input');
                            const dt = new DataTransfer();
                            dt.items.add(audioFile);
                            fileInput.files = dt.files;

                            // Trigger change event
                            const event = new Event('change', { bubbles: true });
                            fileInput.dispatchEvent(event);

                            stream.getTracks().forEach(track => track.stop());
                        };

                        mediaRecorder.start();
                        isRecording = true;
                        recordBtn.textContent = '⏹️';
                        recordBtn.className = recordBtn.className.replace('bg-red-500 hover:bg-red-600', 'bg-gray-500 hover:bg-gray-600');
                        recordBtn.title = 'Stop recording';
                    })
                    .catch(err => {
                        console.error('Error accessing microphone:', err);
                        alert('Could not access microphone. Please check permissions.');
                    });
            }

            function stopRecording() {
                if (mediaRecorder && isRecording) {
                    mediaRecorder.stop();
                    isRecording = false;
                    recordBtn.textContent = '🎤';
                    recordBtn.className = recordBtn.className.replace('bg-gray-500 hover:bg-gray-600', 'bg-red-500 hover:bg-red-600');
                    recordBtn.title = 'Record voice message';
                }
            }
        }

        function initializeConnectionStatus() {
            const statusDot = document.getElementById('status-dot');
            const statusText = document.getElementById('status-text');

            // Update connection status based on chat manager
            const checkConnection = () => {
                if (window.chatManager && window.chatManager.isInitialized) {
                    statusDot.className = 'w-2 h-2 bg-green-400 rounded-full mr-2';
                    statusText.textContent = 'Connected';
                } else {
                    statusDot.className = 'w-2 h-2 bg-red-400 rounded-full mr-2';
                    statusText.textContent = 'Disconnected';
                }
            };

            // Check every 5 seconds
            setInterval(checkConnection, 5000);
            setTimeout(checkConnection, 1000); // Initial check after 1 second
        }

        // Image modal functions
        function openImageModal(imageSrc) {
            const modal = document.getElementById('image-modal');
            const modalImage = document.getElementById('modal-image');
            modalImage.src = imageSrc;
            modal.classList.remove('hidden');
        }

        function closeImageModal() {
            const modal = document.getElementById('image-modal');
            modal.classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('image-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Enhanced form validation
        document.getElementById('chat-form').addEventListener('submit', function(e) {
            const contentInput = document.getElementById('content-input');
            const fileInput = document.getElementById('file-input');
            const sendBtn = document.getElementById('send-btn');

            // Check if message has content or file
            if (!contentInput.value.trim() && !fileInput.files.length) {
                e.preventDefault();
                alert('Please enter a message or select a file to send.');
                return;
            }

            // Disable send button temporarily
            sendBtn.disabled = true;
            setTimeout(() => {
                sendBtn.disabled = false;
            }, 2000);
        });
    </script>

</body>

</html>
