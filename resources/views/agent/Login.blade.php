<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Agent Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(120deg, #eef2ff, #f8fafc); }
        .card { border: none; border-radius: 1rem; }
    </style>
</head>
<body class="d-flex align-items-center" style="min-height: 100vh;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg p-4">
                <div class="text-center mb-3">
                    <i class="bi bi-shield-lock" style="font-size:2.5rem;"></i>
                    <h4 class="mt-2">Agent Sign In</h4>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="m-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li class="small">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('agent.login.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>

                    <div class="d-grid mt-3">
                        <button class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3 text-muted small">
                    Use an account with role <code>agent</code>.
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
