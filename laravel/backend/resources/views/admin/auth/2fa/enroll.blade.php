<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set up two-factor authentication</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #e2e8f0; display: flex; min-height: 100vh; align-items: center; justify-content: center; margin: 0; padding: 1rem; }
        .card { background: #1e293b; padding: 2rem; border-radius: 0.75rem; max-width: 30rem; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,.4); }
        h1 { font-size: 1.25rem; margin: 0 0 1rem; }
        code { background: #0f172a; padding: 0.25rem 0.5rem; border-radius: 0.25rem; word-break: break-all; font-size: 0.85rem; }
        input { width: 100%; box-sizing: border-box; padding: 0.65rem; border-radius: 0.4rem; border: 1px solid #334155; background: #0f172a; color: #e2e8f0; margin: 0.75rem 0; font-size: 1rem; letter-spacing: 0.2em; text-align: center; }
        button { background: #2563eb; color: #fff; border: 0; padding: 0.65rem 1.25rem; border-radius: 0.4rem; cursor: pointer; font-size: 1rem; width: 100%; }
        button:hover { background: #1d4ed8; }
        .error { color: #f87171; font-size: 0.875rem; margin: 0.5rem 0; }
        .muted { color: #94a3b8; font-size: 0.875rem; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Set up two-factor authentication</h1>
        <p class="muted">Add this secret to your authenticator app, then enter the six-digit code it shows.</p>
        <p>Secret: <code>{{ $secret }}</code></p>
        <p class="muted">If your app accepts a URI, use: <code>{{ $qrUrl }}</code></p>

        @if ($errors->any())
            <p class="error">{{ $errors->first() }}</p>
        @endif

        <form method="post" action="{{ route('admin.2fa.enroll.store') }}">
            @csrf
            <input name="code" inputmode="numeric" autocomplete="one-time-code" placeholder="123456" required autofocus>
            <button type="submit">Confirm and enable</button>
        </form>
    </div>
</body>
</html>
