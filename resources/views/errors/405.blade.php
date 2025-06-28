<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>405 Method Not Allowed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .error-box {
            text-align: center;
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            max-width: 480px;
            width: 100%;
        }
        .error-code {
            font-size: 72px;
            font-weight: 800;
            color: #dc3545;
        }
        .error-message {
            font-size: 20px;
            margin-top: 15px;
            color: #555;
        }
        .btn-home {
            margin-top: 25px;
        }
    </style>
</head>
<body>

    <div class="error-box">
        <div class="error-code">405</div>
        <div class="error-message">Oops! Method Not Allowed.</div>
        <p class="text-muted mt-2">The action you're trying to perform is not allowed on this page.</p>
        <a href="{{ route('home') }}" class="btn btn-primary btn-home">← Back to Posts</a>
    </div>

</body>
</html>
