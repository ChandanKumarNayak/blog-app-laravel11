<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>404 | Not Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            text-align: center;
            padding: 60px;
        }

        .error-code {
            font-size: 96px;
            font-weight: bold;
            color: #ef4444;
        }

        .message {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .description {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .button {
            padding: 12px 24px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .button:hover {
            background-color: #2563eb;
        }
    </style>
</head>

<body>

    <div class="error-code">404</div>
    <div class="message">Page Not Found</div>
    <div class="description">Page Not Found</div>
    <a href="{{ route('login') }}" class="button">Go to Login</a>

</body>

</html>