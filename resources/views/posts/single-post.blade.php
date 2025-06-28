<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Post - {{ $singlePost->title }}</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .post-detail h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-align: left;
        }

        .post-detail .meta {
            font-size: 0.9em;
            color: #888;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .post-detail .post-image {
            text-align: center;
            margin-bottom: 25px;
        }

        .post-detail .post-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .post-detail .description {
            line-height: 1.8;
            color: #333;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
        }

        .back-link a {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="post-detail">
            <h1>{{ $singlePost->title }}</h1>
            <div class="meta">
                Status: {{ $singlePost->status }} | Created: {{ $singlePost->created_at_human }}
            </div>
            <div class="post-image">
                <img src="{{ asset('storage/' . $singlePost->image) }}" alt="Post Image">
            </div>
            <div class="description">
                {{ $singlePost->description }}
            </div>
        </div>

        <div class="back-link">
            <a href="{{ route('home') }}">Back to All Posts</a>
        </div>
    </div>
</body>

</html>
