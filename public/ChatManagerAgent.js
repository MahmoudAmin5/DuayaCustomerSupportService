class ChatManager {
    constructor() {
        this.pusher = null;
        this.chatChannel = null;
        this.notificationChannel = null;
        this.currentUserId = null;
        this.currentChatId = null;
        this.userType = null;
        this.isInitialized = false;

        this.init();
    }

    init() {
        // Get configuration from meta tags
        this.currentUserId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        this.userType = document.querySelector('meta[name="user-type"]')?.getAttribute('content');
        this.currentChatId = this.getChatIdFromUrl();

        if (!this.currentUserId || !this.userType) {
            console.error('User ID or User Type not found in meta tags');
            return;
        }

        // Initialize Pusher
        this.initPusher();

        // Setup chat channel for real-time messages
        this.setupChatChannel();

        // Setup notification channel
        this.setupNotificationChannel();

        // Setup form submission
        this.setupFormSubmission();

        // Auto-scroll to bottom on load
        this.scrollToBottom();

        this.isInitialized = true;
        console.log('Chat Manager initialized successfully');
    }

    initPusher() {
        const pusherKey = document.querySelector('meta[name="pusher-key"]')?.getAttribute('content');
        const pusherCluster = document.querySelector('meta[name="pusher-cluster"]')?.getAttribute('content') || 'mt1';

        if (!pusherKey) {
            console.error('Pusher key not found');
            return;
        }

        this.pusher = new Pusher(pusherKey, {
            cluster: pusherCluster,
            encrypted: true,
        });

        console.log('Pusher initialized with key:', pusherKey);
    }

    getChatIdFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const pathParts = window.location.pathname.split('/');
        return pathParts[pathParts.length - 1] || urlParams.get('chatId');
    }

    setupChatChannel() {
        if (!this.currentChatId) {
            console.error('Chat ID not found');
            return;
        }

        // Subscribe to private chat channel
        this.chatChannel = this.pusher.subscribe(`agent-channel`);

        // Listen for new messages
        this.chatChannel.bind('message.sent', (data) => {
            console.log('New message received:', data);
            this.handleNewMessage(data);
        });

        console.log(`Subscribed to chat channel: agent-channel.${this.currentChatId}`);
    }

    setupNotificationChannel() {
        let channelName;

        if (this.userType === 'agent') {
            channelName = 'agent-channel';
        } else if (this.userType === 'customer') {
            channelName = `customer-channel.${this.currentUserId}`;
        }

        if (!channelName) {
            console.error('Could not determine notification channel');
            return;
        }

        this.notificationChannel = this.pusher.subscribe(channelName);

        // Listen for notifications
        const eventName = this.userType === 'agent' ? 'agent-notification' : 'customer-notification';
        this.notificationChannel.bind(eventName, (data) => {
            console.log('Notification received:', data);
            this.handleNotification(data);
        });

        console.log(`Subscribed to notification channel: ${channelName}`);
    }

    handleNewMessage(messageData) {
        // Don't show message if it's from current user
        if (messageData.sender_id == this.currentUserId) {
            return;
        }

        // Add message to chat
        this.appendMessageToChat(messageData);

        // Play notification sound if notification manager exists
        if (window.notificationManager) {
            window.notificationManager.playNotificationSound();
        }

        // Auto scroll to bottom
        this.scrollToBottom();
    }

    handleNotification(notificationData) {
        // Only show notification if it's not from current chat or user is not active
        if (notificationData.chat_id != this.currentChatId || !document.hasFocus()) {
            this.showNotificationToast(notificationData);

            // Play sound
            if (window.notificationManager) {
                window.notificationManager.playNotificationSound();
            }
        }
    }

    appendMessageToChat(messageData) {
        const messagesContainer = document.getElementById('messages');
        if (!messagesContainer) return;

        const messageElement = this.createMessageElement(messageData);
        messagesContainer.appendChild(messageElement);
    }

    createMessageElement(messageData) {
        const messageDiv = document.createElement('div');
        const isCurrentUser = messageData.sender_id == this.currentUserId;

        messageDiv.className = `flex ${isCurrentUser ? 'justify-end' : 'justify-start'}`;

        const bubbleClass = isCurrentUser
            ? 'bg-blue-500 text-white'
            : 'bg-white text-gray-900 border';

        let messageContent = '';

        switch (messageData.type) {
            case 'text':
                messageContent = `<p>${this.escapeHtml(messageData.content)}</p>`;
                break;
            case 'image':
                messageContent = `<img src="${messageData.file_path}" alt="Image" class="max-w-[200px] rounded">`;
                break;
            case 'file':
                messageContent = `<a href="${messageData.file_path}" class="underline text-blue-200 hover:text-blue-300" target="_blank">📄 ${this.escapeHtml(messageData.content || 'Download File')}</a>`;
                break;
            case 'voice':
                messageContent = `<audio controls class="w-full"><source src="${messageData.file_path}" type="audio/mpeg">Your browser does not support audio playback</audio>`;
                break;
        }

        messageDiv.innerHTML = `
            <div class="${bubbleClass} px-4 py-2 rounded-lg max-w-xs shadow">
                ${messageContent}
                <div class="text-xs opacity-70 mt-1">
                    ${messageData.created_at}
                </div>
            </div>
        `;

        return messageDiv;
    }

    showNotificationToast(notificationData) {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-white border-l-4 border-blue-500 rounded-lg shadow-lg p-4 max-w-sm z-50';
        toast.style.animation = 'slideInRight 0.3s ease-out';

        toast.innerHTML = `
            <div class="flex justify-between items-start">
                <div>
                    <h6 class="font-semibold text-sm text-gray-800">${this.escapeHtml(notificationData.title)}</h6>
                    <p class="text-sm text-gray-600 mt-1">${this.escapeHtml(notificationData.message)}</p>
                    <p class="text-xs text-gray-500 mt-1">From: ${this.escapeHtml(notificationData.sender_name)}</p>
                </div>
                <button class="text-gray-400 hover:text-gray-600 ml-2" onclick="this.parentElement.parentElement.remove()">
                    ×
                </button>
            </div>
        `;

        // Add click to navigate
        toast.addEventListener('click', () => {
            if (notificationData.url) {
                window.location.href = notificationData.url;
            }
        });

        document.body.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        }, 5000);
    }

    setupFormSubmission() {
        const form = document.getElementById('chat-form');

        if (!form) return;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitMessage(form);
        });

        // Handle Enter key for textarea
        const textarea = document.getElementById('content-input');
        if (textarea) {
            textarea.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });
        }
    }

    async submitMessage(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const textarea = document.getElementById('content-input');

        // Disable submit button
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                // Clear form
                if (textarea) textarea.value = '';
                document.getElementById('file-input').value = '';
                document.getElementById('file-preview').innerHTML = '';
                document.getElementById('message-type').value = 'text';

                // Show success message briefly
                this.showSuccessMessage('Message sent successfully');
            } else {
                throw new Error(result.error || 'Failed to send message');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            this.showErrorMessage('Failed to send message: ' + error.message);
        } finally {
            // Re-enable submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send';
            }
        }
    }

    showSuccessMessage(message) {
        this.showTemporaryMessage(message, 'success');
    }

    showErrorMessage(message) {
        this.showTemporaryMessage(message, 'error');
    }

    showTemporaryMessage(message, type) {
        const messageDiv = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';

        messageDiv.className = `fixed top-4 left-1/2 transform -translate-x-1/2 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50`;
        messageDiv.textContent = message;

        document.body.appendChild(messageDiv);

        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 3000);
    }

    scrollToBottom() {
        const messagesDiv = document.getElementById('messages');
        if (messagesDiv) {
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Cleanup method
    destroy() {
        if (this.chatChannel) {
            this.pusher.unsubscribe(`chat.${this.currentChatId}`);
        }
        if (this.notificationChannel) {
            this.pusher.unsubscribe(this.getNotificationChannelName());
        }
        if (this.pusher) {
            this.pusher.disconnect();
        }
    }

    getNotificationChannelName() {
        return this.userType === 'agent'
            ? 'agent-channel'
            : `customer-channel.${this.currentUserId}`;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chat manager
    window.chatManager = new ChatManager();

    // Add global cleanup function
    window.addEventListener('beforeunload', function() {
        if (window.chatManager) {
            window.chatManager.destroy();
        }
    });
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
