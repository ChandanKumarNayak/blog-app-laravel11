<!DOCTYPE html>
<html lang="en">

<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Simple Blog</title>
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

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

        .post {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
        }

        .post:last-child {
            border-bottom: none;
        }

        .post-image {
            flex: 0 0 150px;
            margin-right: 20px;
        }

        .post-image img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }

        .post-content {
            flex: 1;
        }

        .post-content h2 {
            color: #007bff;
            margin-top: 0;
            margin-bottom: 5px;
        }

        .post-content p {
            color: #555;
            line-height: 1.6;
        }

        .post-content .meta {
            font-size: 0.9em;
            color: #888;
            margin-top: 10px;
        }

        .no-posts {
            text-align: center;
            color: #666;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination nav {
            display: inline-block;
        }

        .pagination nav svg {
            height: 20px;
            width: 20px;
        }

        .pagination nav a,
        .pagination nav span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            margin: 0 2px;
            text-decoration: none;
            color: #007bff;
            border-radius: 4px;
        }

        .pagination nav span.current {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination nav span.disabled {
            color: #ccc;
        }

        .create-link {
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }

        .create-link a {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>All Blog Posts</h1>

        @if (session('success'))
            <p style="color: green">{{ session('success') }}</p>
        @endif

        @if (session('error'))
            <p style="color: red">{{ session('error') }}</p>
        @endif

        <div class="create-link">
            <a href="{{ route('post.create') }}">Create New Post</a>
        </div>

        @if ($allPosts->isNotEmpty())
            @foreach ($allPosts as $post)
                @php
                    $imageLink = asset('storage/' . ($post->image ?? 'default.jpg'));
                @endphp
                <div class="post-item-template">
                    <div class="post-image">
                        <img src="{{ $imageLink }}" alt="Post Image">
                    </div>
                    <div class="post-content">
                        <h2>
                            <a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a>
                            <a href="{{ route('post.edit', $post->id) }}" class="btn btn-primary"><i
                                    class="fa fa-edit"></i></a>
                            <form method="post" action="{{ route('post.delete', $post->id) }}"
                                onsubmit="return confirm('Are you sure to delete?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </h2>
                        <p>{{ $post->description }}</p>
                        <div class="meta">
                            Status: {{ $post->status }} | Created: {{ $post->created_at_human }}
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="no-posts-placeholder">
                <p class="no-posts">No posts yet.</p>
            </div>
        @endif

        <div class="pagination-placeholder">
            {{ $allPosts->links() }}
        </div>
    </div>
</body>

</html>
