{{-- resources/views/chat/chat.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { background: #f8f9fa; }
        .chat-box { height: 70vh; overflow-y: auto; background: #fff; border-radius: 10px; padding: 15px; }
        .message { max-width: 70%; padding: 10px; border-radius: 15px; margin-bottom: 8px; }
        .sent { background: #0d6efd; color: white; margin-left: auto; }
        .received { background: #e9ecef; color: black; margin-right: auto; }
        .message-time { font-size: 0.75rem; opacity: 0.7; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">💬 Chat Room</h5>
        </div>
        <div class="card-body d-flex flex-column">
            <div id="chatBox" class="chat-box mb-3"></div>

            <form id="sendMessageForm" class="d-flex">
                <input type="text" id="messageInput" name="content" class="form-control me-2" placeholder="Type a message..." required>
                <button class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const chatId = "{{ $chat_id ?? '' }}"; // Pass this from controller after starting chat
    const senderId = "{{ auth()->id() ?? '' }}"; // Current user ID

    function fetchMessages() {
        $.ajax({
            url: `/api/chat/messages?chat_id=${chatId}`,
            type: 'GET',
            success: function (data) {
                $('#chatBox').empty();
                data.messages.forEach(msg => {
                    const alignment = msg.sender_id == senderId ? 'sent' : 'received';
                    $('#chatBox').append(`
                        <div class="d-flex flex-column ${alignment === 'sent' ? 'align-items-end' : 'align-items-start'}">
                            <div class="message ${alignment}">
                                ${msg.content || '<em>File Uploaded</em>'}
                                <div class="message-time">${msg.created_at}</div>
                            </div>
                        </div>
                    `);
                });
                $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
            }
        });
    }

    $('#sendMessageForm').on('submit', function (e) {
        e.preventDefault();
        const message = $('#messageInput').val();

        $.ajax({
            url: '/api/chat/send',
            type: 'POST',
            data: {
                chat_id: chatId,
                sender_id: senderId,
                type: 'text',
                content: message
            },
            success: function () {
                $('#messageInput').val('');
                fetchMessages();
            }
        });
    });

    // Load messages every 0.5 seconds
    setInterval(fetchMessages, 500);
    fetchMessages();
</script>
</body>
</html>
