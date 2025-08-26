<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Start Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Laravel CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

        .hover-lift {
            transition: all 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body class="min-h-screen gradient-bg p-4">
    <div class="flex items-center justify-center min-h-screen">
        <div class="glass-effect shadow-2xl rounded-3xl w-full max-w-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 flex items-center space-x-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-semibold">Start Chat</h1>
                    <p class="text-sm opacity-90">Connect with our support team</p>
                </div>
            </div>

            <!-- Form -->
            <form id="startChatForm" action="{{ route('startChat') }}" method="POST" class="p-6 space-y-4 bg-white">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your name"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required inputmode="tel"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <div class="relative">
                        <textarea id="message" name="message" rows="4" maxlength="1000" placeholder="Type your message"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-y"></textarea>
                        <div class="absolute bottom-2 right-3 text-xs text-gray-400">
                            <span id="char-count">0</span>/1000
                        </div>
                    </div>
                </div>

                <button type="submit" id="submit-btn"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl px-4 py-3 font-medium transition-all duration-200 hover-lift disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2">
                    <span id="btnText">Start Chat</span>
                    <svg id="btnSpinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // Character counter like chat pages
        (function initializeCharacterCounter() {
            const textarea = document.getElementById('message');
            const charCount = document.getElementById('char-count');
            if (!textarea) return;

            textarea.addEventListener('input', function () {
                const count = this.value.length;
                charCount.textContent = count;

                if (count > 900) {
                    charCount.className = 'text-red-500 font-semibold';
                } else if (count > 800) {
                    charCount.className = 'text-yellow-500 font-medium';
                } else {
                    charCount.className = 'text-gray-400';
                }
            });
        })();

        // Simple notification system (aligned with chat pages)
        function showNotification(message, type = 'info') {
            const container = document.getElementById('notification-container');
            const notification = document.createElement('div');

            const bgColor = {
                success: 'bg-green-500',
                warning: 'bg-yellow-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
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
            setTimeout(() => notification.classList.remove('translate-x-full'), 100);
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }

        // Submit handling with loading state
        (function initializeSubmit() {
            const form = document.getElementById('startChatForm');
            const btn = document.getElementById('submit-btn');
            const spinner = document.getElementById('btnSpinner');

            form.addEventListener('submit', function (e) {
                // Leverage HTML5 validation; show a nice toast if invalid
                if (!form.checkValidity()) {
                    e.preventDefault();
                    showNotification('Please fill required fields correctly.', 'warning');
                    return;
                }

                btn.disabled = true;
                spinner.classList.remove('hidden');
            });
        })();

        // Auto-focus
        setTimeout(() => {
            const phone = document.getElementById('phone');
            if (phone) phone.focus();
        }, 300);
    </script>
</body>

</html>
