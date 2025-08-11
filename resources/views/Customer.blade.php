<!-- resources/views/customer.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Customer Chat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-light">

    <div class="container mt-5">
        @if(!$hasChat)
            <h3 class="mb-3">Start Chat</h3>
            <form id="startChatForm">
                @csrf
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Message</label>
                    <textarea name="message" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Start</button>
            </form>
        @else
            <h3>Chat</h3>
            <div id="messages" class="border rounded p-3 mb-3" style="height:300px; overflow-y:auto;">
                @foreach($messages as $msg)
                    <div><strong>{{ $msg->sender }}:</strong> {{ $msg->message }}</div>
                @endforeach
            </div>
            <form id="sendMessageForm">
                @csrf
                <input type="hidden" name="chat_id" value="{{ $chat->id }}">
                <div class="input-group">
                    <input type="text" name="message" class="form-control" placeholder="Type message..." required>
                    <button class="btn btn-success">Send</button>
                </div>
            </form>
        @endif
    </div>

    <script>
        @if($hasChat)
            // Pusher listener
            Pusher.logToConsole = false;
            let pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', { cluster: '{{ env("PUSHER_APP_CLUSTER") }}' });
            let channel = pusher.subscribe('chat.{{ $chat->id }}');
            channel.bind('message.sent', function (data) {
                $('#messages').append('<div><strong>' + data.sender + ':</strong> ' + data.message + '</div>');
            });

            $('#sendMessageForm').submit(function (e) {
                e.preventDefault();
                $.post("{{ route('send.message') }}", $(this).serialize(), function (res) {
                    $('input[name="message"]').val('');
                });
            });
        @else
            $('#startChatForm').submit(function (e) {
                e.preventDefault();
                $.post("{{ route('start.chat') }}", $(this).serialize(), function (res) {
                    location.reload();
                });
            });
        @endif
    </script>

</body>

</html>
