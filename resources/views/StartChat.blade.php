{{-- resources/views/chat/start.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">💬 Start Chat</h3>

                        <div id="alert-box" class="d-none"></div>

                        <form id="startChatForm" action="{{ route('startChat') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Enter your name">
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="text" name="phone" id="phone" class="form-control"
                                    placeholder="Enter phone number" required>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Message </label>
                                <textarea name="message" id="message" rows="4" class="form-control"
                                    placeholder="Type your message"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <span id="btnText">Start Chat</span>
                                <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
