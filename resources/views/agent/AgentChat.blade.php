<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Agent Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f5f6fa;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .chat-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 80vh;
            overflow: hidden;
        }
        .chat-header {
            background: #2ecc71;
            padding: 15px;
            color: white;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .close-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }
        .close-btn:hover {
            background: #c0392b;
        }
        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #ecf0f1;
        }
        .message {
            max-width: 70%;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            clear: both;
        }
        .message.sent {
            background: #2ecc71;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .message.received {
            background: white;
            color: #333;
            margin-right: auto;
            text-align: left;
        }
        .chat-form {
            display: flex;
            padding: 10px;
            background: #fff;
            border-top: 1px solid #ddd;
        }
        .chat-form textarea {
            flex: 1;
            resize: none;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-right: 10px;
        }
        .chat-form button {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
        }
        .chat-form button:hover {
            background: #27ae60;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        Chat with Customer
        <form method="POST" action="{{ route('agent.chat.close', $chat->id) }}" style="margin:0;">
            @csrf
            <button type="submit" class="close-btn">Close Chat</button>
        </form>
    </div>

    <div class="messages">
        @foreach($messages as $message)
            <div class="message {{ $message->sender_id == $sender_id ? 'sent' : 'received' }}">
                {{ $message->content }}
            </div>
        @endforeach
    </div>

    <form action="{{ route('chat.send', ['chatId' => $chat->id]) }}" method="POST" enctype="multipart/form-data" class="flex border-t p-3">
            @csrf
            <input type="hidden" name="chat_id" value="{{ $chat->id }}">
            <input type="hidden" name="sender_id" value="{{ $sender_id }}">
            <input type="hidden" name="type" value="text">
        <textarea name="message" placeholder="Type your message..." required></textarea>
        <button type="submit">Send</button>
    </form>
</div>

</body>
</html>
