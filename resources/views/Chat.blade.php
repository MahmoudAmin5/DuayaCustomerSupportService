<!-- resources/views/chat.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <title>Chat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                Chat with Support
                @if($chat->is_active)
                    <span class="badge bg-success float-end">Active</span>
                @else
                    <span class="badge bg-danger float-end">Closed</span>
                @endif
            </div>
            <div class="card-body" id="chat-box" style="height: 400px; overflow-y: scroll;">
                @foreach($messages as $message)
                    <div class="mb-2">
                        <strong>{{ $message->sender_type }}:</strong> {{ $message->content }}
                    </div>
                @endforeach
            </div>

            @if($chat->is_active)
                <div class="card-footer">
                    <form id="send-message">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="content" class="form-control" placeholder="Type your message..."
                                required>
                            <button class="btn btn-primary">Send</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-3 text-center text-muted">
                    This chat is closed.
                </div>
            @endif
        </div>
    </div>

    <script>
        // Enable pusher logging for debugging
        Pusher.logToConsole = true;

        var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
            cluster: "{{ env('PUSHER_APP_CLUSTER') }}"
        });

        var channel = pusher.subscribe("chat.{{ $chat->id }}");
        channel.bind("new-message", function (data) {
            $("#chat-box").append(`<div><strong>${data.sender_type}:</strong> ${data.content}</div>`);
            $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
        });

        $("#send-message").submit(function (e) {
            e.preventDefault();
            $.post("{{ route('chat.send', $chat->id) }}", $(this).serialize());
            $(this).find("input[name=content]").val("");
        });
    </script>

</body>

</html>
