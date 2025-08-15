<!-- resources/views/agent.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Agent Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-light">

    <div class="container mt-5">
        <h3>Agent Chat</h3>
        <select id="chatSelector" class="form-select mb-3">
            <option value="">Select Chat</option>
            @foreach($chats as $c)
                <option value="{{ $c->id }}">Chat #{{ $c->id }} - {{ $c->customer_name }}</option>
            @endforeach
        </select>

        <div id="chatWindow" style="display:none;">
            <div id="messages" class="border rounded p-3 mb-3" style="height:300px; overflow-y:auto;"></div>
            <form id="sendMessageForm">
                @csrf
                <input type="hidden" name="chat_id" id="chat_id">
                <div class="input-group">
                    <input type="text" name="message" class="form-control" placeholder="Type message..." required>
                    <button class="btn btn-primary">Send</button>
                    <button type="button" id="closeChatBtn" class="btn btn-danger">Close Chat</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentChannel = null;

        $('#chatSelector').change(function () {
            let chatId = $(this).val();
            if (!chatId) return;

            $('#chat_id').val(chatId);
            $('#messages').empty();
            $('#chatWindow').show();

            if (currentChannel) currentChannel.unsubscribe();

            // Pusher listener
            let pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', { cluster: '{{ env("PUSHER_APP_CLUSTER") }}' });
            currentChannel = pusher.subscribe('chat.' + chatId);
            currentChannel.bind('message.sent', function (data) {
                $('#messages').append('<div><strong>' + data.sender + ':</strong> ' + data.message + '</div>');
            });

            // Load old messages
            $.get("/agent/chat/" + chatId + "/messages", function (data) {
                data.forEach(msg => {
                    $('#messages').append('<div><strong>' + msg.sender + ':</strong> ' + msg.message + '</div>');
                });
            });
        });

        $('#sendMessageForm').submit(function (e) {
            e.preventDefault();
            $.post("{{ route('agent.send.message') }}", $(this).serialize(), function () {
                $('input[name="message"]').val('');
            });
        });

        $('#closeChatBtn').click(function () {
            let chatId = $('#chat_id').val();
            $.post("/agent/chat/" + chatId + "/close", { _token: '{{ csrf_token() }}' }, function () {
                alert("Chat closed.");
                location.reload();
            });
        });
    </script>

</body>

</html>
