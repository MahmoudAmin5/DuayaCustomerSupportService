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
    <meta name="user-type" content="customer" />
    <meta name="user-id" content="{{ $sender_id }}" />


    {{-- Laravel CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Include notification manager --}}
    <script src="{{ asset('ChatManager.js') }}"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .glass-effect {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .message-bubble {
            animation: fadeInUp 0.3s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .typing-animation {
            animation: typing 1.4s infinite;
        }

        @keyframes typing {

            0%,
            60%,
            100% {
                transform: translateY(0);
            }

            30% {
                transform: translateY(-10px);
            }
        }

        .hover-lift {
            transition: all 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .connection-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .file-upload-area {
            transition: all 0.3s ease;
        }

        .file-upload-area:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-color: #3b82f6;
        }
    </style>
</head>

<body class="min-h-screen gradient-bg p-4">
    <div class="flex items-center justify-center min-h-screen">
        <div class="glass-effect shadow-2xl rounded-3xl w-full max-w-4xl flex flex-col h-[85vh] overflow-hidden">

            <!-- Enhanced Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold">{{ $chat->agent ? $chat->agent->name : 'Customer' }}</h2>
                        <div class="flex items-center space-x-2 text-sm opacity-90">
                            <div id="status-dot" class="w-2 h-2 bg-green-400 rounded-full connection-pulse"></div>
                            <span id="status-text">Online</span>
                            <span id="online-status" class="opacity-75"></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Connection Status -->
                    <div id="connection-status" class="flex items-center bg-white bg-opacity-20 rounded-full px-3 py-1">
                        <div id="connection-dot" class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                        <span id="connection-text" class="text-xs font-medium">Connected</span>
                    </div>

                    <!-- Close Chat Button -->
                    <form action="{{ route('agent.chat.close', ['chatId' => $chat->id]) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to close the chat?');">
                        @csrf
                        {{-- <button type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 hover-lift">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Close Chat
                        </button> --}}
                    </form>
                </div>
            </div>

            <!-- Messages Container -->
            <div id="messages"
                class="flex-1 overflow-y-auto p-6 bg-gradient-to-b from-gray-50 to-white scrollbar-hide-">
                <div class="space-y-4">
                    @foreach($messages as $message)
                        <div
                            class="flex {{ $message->sender_id == $sender_id ? 'justify-end' : 'justify-start' }} message-bubble">
                            <div class="max-w-xs lg:max-w-md">
                                <!-- Message Content -->
                                <div
                                    class="{{ $message->sender_id == $sender_id ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-l-2xl rounded-tr-2xl' : 'bg-white text-gray-800 border border-gray-200 rounded-r-2xl rounded-tl-2xl' }} px-5 py-3 shadow-lg">
                                    @if($message->type === 'text')
                                        <p class="text-sm leading-relaxed">{{ $message->content }}</p>

                                    @elseif($message->type === 'image')
                                        <div class="relative group">
                                            <img src="{{ asset('storage/' . $message->file_path) }}" alt="Image"
                                                class="max-w-[250px] rounded-lg cursor-pointer transition-transform group-hover:scale-105"
                                                onclick="openImageModal('{{ asset('storage/' . $message->file_path) }}')">
                                            <div
                                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all duration-200 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>
                                        </div>

                                    @elseif($message->type === 'file')
                                        <a href="{{ asset('storage/' . $message->file_path) }}"
                                            class="flex items-center space-x-3 hover:opacity-80 transition-opacity"
                                            target="_blank" download>
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium">{{ $message->content ?? 'Download File' }}</p>
                                                <p class="text-xs opacity-70">Click to download</p>
                                            </div>
                                        </a>

                                    @elseif($message->type === 'voice' && $message->file_path)
                                        @php
                                            $fileExtension = pathinfo($message->file_path, PATHINFO_EXTENSION);
                                            $mimeTypes = [
                                                'mp3' => 'audio/mpeg',
                                                'wav' => 'audio/wav',
                                                'ogg' => 'audio/ogg',
                                                'm4a' => 'audio/mp4'
                                            ];
                                            $mimeType = $mimeTypes[strtolower($fileExtension)] ?? 'audio/mpeg';
                                        @endphp
                                        <div style="width: 17pc" class="bg-gray-100 rounded-lg ">
                                            <audio controls class="w-full" preload="metadata">
                                                <source src="{{ asset('storage/' . $message->file_path) }}"
                                                    type="{{ $mimeType }}">
                                                <!-- Fallback for different browsers -->
                                                <source src="{{ asset('storage/' . $message->file_path) }}" type="audio/mpeg">
                                                <source src="{{ asset('storage/' . $message->file_path) }}" type="audio/wav">
                                                Your browser does not support audio playback
                                            </audio>

                                            <!-- Debug info (remove in production) -->

                                        </div>
                                    @else
                                        <p class="text-sm leading-relaxed">Unsupported message type.</p>
                                    @endif
                                </div>

                                <!-- Message Info -->
                                <div
                                    class="flex {{ $message->sender_id == $sender_id ? 'justify-end' : 'justify-start' }} mt-1 px-1">
                                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <span>{{ $message->created_at->format('H:i') }}</span>
                                        @if($message->sender_id == $sender_id)
                                            <span id="message-status-{{ $message->id }}" class="message-status text-blue-500">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Enhanced Typing Indicator -->
                    <div id="typing-indicator" class="flex justify-start hidden message-bubble">
                        <div class="bg-white border border-gray-200 rounded-r-2xl rounded-tl-2xl px-5 py-3 shadow-lg">
                            <div class="flex items-center space-x-2">
                                <div class="flex space-x-1">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full typing-animation"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full typing-animation"
                                        style="animation-delay: 0.2s"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full typing-animation"
                                        style="animation-delay: 0.4s"></div>
                                </div>
                                <span class="text-xs text-gray-500 ml-2">typing...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Form -->
            <form id="chat-form" action="{{ route('customer.chat.send', ['chatId' => $chat->id]) }}" method="POST"
                enctype="multipart/form-data" class="bg-white border-t border-gray-100">
                @csrf
                <input type="hidden" name="chat_id" value="{{ $chat->id }}">
                <input type="hidden" name="sender_id" value="{{ $sender_id }}">
                <input type="hidden" name="type" value="text" id="message-type">

                <!-- Enhanced File Preview -->
                <div id="file-preview-container" class="hidden p-4 border-b bg-gray-50">
                    <div
                        class="flex items-center justify-between bg-white rounded-lg p-3 border-2 border-dashed border-blue-200">
                        <div id="file-preview" class="flex items-center space-x-3"></div>
                        <button type="button" onclick="clearFileSelection()"
                            class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Enhanced Input Area -->
                <div class="p-4">
                    <div class="flex items-end space-x-3 bg-gray-50 rounded-2xl p-2">
                        <!-- Text Input -->
                        <div class="flex-1 relative">
                            <textarea name="content" id="content-input"
                                class="w-full bg-transparent border-0 rounded-xl p-3 focus:outline-none resize-none text-gray-800 placeholder-gray-500"
                                placeholder="Type your message..." rows="1" maxlength="1000"></textarea>
                            <div class="absolute bottom-1 right-1 text-xs text-gray-400">
                                <span id="char-count">0</span>/1000
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-2">
                            <!-- File Upload -->
                            <input type="file" name="file_path" id="file-input" class="hidden"
                                accept="image/*,audio/*,.pdf,.doc,.docx,.txt,.zip,.rar">

                            <button type="button" onclick="document.getElementById('file-input').click()"
                                class="w-10 h-10 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover-lift"
                                title="Attach file">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                    </path>
                                </svg>
                            </button>

                            <!-- Voice Record -->
                            <button type="button" id="voice-record-btn"
                                class="w-10 h-10 bg-red-100 hover:bg-red-200 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover-lift"
                                title="Record voice message">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z">
                                    </path>
                                </svg>
                            </button>

                            <!-- Send Button -->
                            <button type="submit" id="send-btn"
                                class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-full flex items-center justify-center transition-all duration-200 hover-lift disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Image Modal -->
    <div id="image-modal" class="hidden fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4">
        <div class="relative max-w-4xl max-h-4xl">
            <img id="modal-image" src="" alt="Full size image" class="max-w-full max-h-full rounded-lg shadow-2xl">
            <button onclick="closeImageModal()"
                class="absolute -top-4 -right-4 w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // Enhanced chat functionality
        document.addEventListener("DOMContentLoaded", function () {
            scrollToBottom();
            initializeFileHandling();
            initializeCharacterCounter();
            initializeVoiceRecording();
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
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);

                    if (file.size > 20 * 1024 * 1024) {
                        alert('File size must be less than 20MB');
                        this.value = '';
                        return;
                    }

                    previewContainer.classList.remove('hidden');

                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                        typeInput.value = "image";
                        const container = document.createElement('div');
                        container.className = "flex items-center space-x-3";

                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.className = "h-16 w-16 object-cover rounded-lg border-2 border-blue-200";

                        const info = document.createElement('div');
                        info.innerHTML = `
                            <p class="font-medium text-gray-800">📷 ${file.name}</p>
                            <p class="text-sm text-gray-500">${fileSize} MB</p>
                        `;

                        container.appendChild(img);
                        container.appendChild(info);
                        preview.appendChild(container);

                    } else if (['mp3', 'wav', 'ogg', 'm4a'].includes(ext)) {
                        typeInput.value = "voice";
                        const container = document.createElement('div');
                        container.className = "flex items-center space-x-3";

                        const icon = document.createElement('div');
                        icon.className = "w-16 h-16 bg-gradient-to-br from-red-100 to-red-200 rounded-lg border-2 border-red-200 flex items-center justify-center";
                        icon.innerHTML = '<svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>';

                        const info = document.createElement('div');
                        info.innerHTML = `
                            <p class="font-medium text-gray-800">🎵 ${file.name}</p>
                            <p class="text-sm text-gray-500">${fileSize} MB</p>
                        `;

                        container.appendChild(icon);
                        container.appendChild(info);
                        preview.appendChild(container);

                    } else {
                        typeInput.value = "file";
                        const container = document.createElement('div');
                        container.className = "flex items-center space-x-3";

                        const icon = document.createElement('div');
                        icon.className = "w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg border-2 border-gray-200 flex items-center justify-center";
                        icon.innerHTML = '<svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>';

                        const info = document.createElement('div');
                        info.innerHTML = `
                            <p class="font-medium text-gray-800">📄 ${file.name}</p>
                            <p class="text-sm text-gray-500">${fileSize} MB</p>
                        `;

                        container.appendChild(icon);
                        container.appendChild(info);
                        preview.appendChild(container);
                    }

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

            textarea.addEventListener('input', function () {
                const count = this.value.length;
                charCount.textContent = count;

                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';

                if (count > 900) {
                    charCount.className = 'text-red-500 font-semibold';
                } else if (count > 800) {
                    charCount.className = 'text-yellow-500 font-medium';
                } else {
                    charCount.className = 'text-gray-400';
                }
            });
        }

        function initializeVoiceRecording() {
            const recordBtn = document.getElementById('voice-record-btn');
            let mediaRecorder;
            let audioChunks = [];
            let isRecording = false;

            recordBtn.addEventListener('click', function () {
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

                            const fileInput = document.getElementById('file-input');
                            const dt = new DataTransfer();
                            dt.items.add(audioFile);
                            fileInput.files = dt.files;

                            const event = new Event('change', { bubbles: true });
                            fileInput.dispatchEvent(event);

                            stream.getTracks().forEach(track => track.stop());
                        };

                        mediaRecorder.start();
                        isRecording = true;
                        recordBtn.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zM12 9a1 1 0 10-2 0v2a1 1 0 102 0V9z" clip-rule="evenodd"></path></svg>';
                        recordBtn.className = recordBtn.className.replace('bg-red-100 hover:bg-red-200 text-red-600', 'bg-gray-800 hover:bg-gray-900 text-white');
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
                    recordBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>';
                    recordBtn.className = recordBtn.className.replace('bg-gray-800 hover:bg-gray-900 text-white', 'bg-red-100 hover:bg-red-200 text-red-600');
                    recordBtn.title = 'Record voice message';
                }
            }
        }

        function initializeConnectionStatus() {
            const statusDot = document.getElementById('connection-dot');
            const statusText = document.getElementById('connection-text');

            const checkConnection = () => {
                if (window.chatManager && window.chatManager.isInitialized) {
                    statusDot.className = 'w-2 h-2 bg-green-400 rounded-full mr-2';
                    statusText.textContent = 'Connected';
                } else {
                    statusDot.className = 'w-2 h-2 bg-red-400 rounded-full mr-2';
                    statusText.textContent = 'Disconnected';
                }
            };

            setInterval(checkConnection, 5000);
            setTimeout(checkConnection, 1000);
        }

        // Image modal functions
        function openImageModal(imageSrc) {
            const modal = document.getElementById('image-modal');
            const modalImage = document.getElementById('modal-image');
            modalImage.src = imageSrc;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('image-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('image-modal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Enhanced form validation and submission
        document.getElementById('chat-form').addEventListener('submit', function (e) {
            const contentInput = document.getElementById('content-input');
            const fileInput = document.getElementById('file-input');
            const sendBtn = document.getElementById('send-btn');

            if (!contentInput.value.trim() && !fileInput.files.length) {
                e.preventDefault();

                // Show elegant error notification
                showNotification('Please enter a message or select a file to send.', 'warning');
                return;
            }

            // Disable send button with loading state
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';

            setTimeout(() => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>';
            }, 2000);
        });

        // Notification system
        function showNotification(message, type = 'info') {
            const container = document.getElementById('notification-container');
            const notification = document.createElement('div');

            const bgColor = {
                'success': 'bg-green-500',
                'warning': 'bg-yellow-500',
                'error': 'bg-red-500',
                'info': 'bg-blue-500'
            }[type];

            notification.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300`;
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 hover:opacity-70">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

            container.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }

        // Enhanced keyboard shortcuts
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                const activeElement = document.activeElement;
                if (activeElement && activeElement.id === 'content-input') {
                    e.preventDefault();
                    document.getElementById('chat-form').dispatchEvent(new Event('submit'));
                }
            }
        });
        function scrollToBottom() {
            const messagesDiv = document.getElementById("messages");
            if (messagesDiv) {
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }
        }


        // Auto-focus on content input
        setTimeout(() => {
            document.getElementById('content-input').focus();
        }, 500);
    </script>
</body>

</html>
