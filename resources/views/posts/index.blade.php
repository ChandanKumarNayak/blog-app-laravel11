<!DOCTYPE html>
<html lang="en">

<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Blog Posts</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">

    <div class="max-w-5xl mx-auto px-4 py-10">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-6">All Blog Posts</h1>

        @if (session('success'))
            <div class="mb-4 text-green-700 bg-green-100 border border-green-300 rounded px-4 py-2">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 text-red-700 bg-red-100 border border-red-300 rounded px-4 py-2">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 text-center">
            <a href="{{ route('post.create') }}"
                class="inline-block bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-5 rounded shadow">
                + Create New Post
            </a>

            <form action="{{ route('auth.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="inline-block bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-5 my-1 rounded shadow">
                    <i class="fa fa-sign-out"></i> Logout
                </button>
            </form>
        </div>

        @forelse ($allPosts as $post)
            @php
                $imageLink = asset('storage/' . ($post->image ?? 'default.jpg'));
            @endphp
            <div class="bg-white rounded-lg shadow-md p-5 mb-6 flex flex-col sm:flex-row gap-4">
                <div class="w-full sm:w-48">
                    <img src="{{ $imageLink }}" alt="Post Image" class="w-full h-32 object-cover rounded">
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start gap-3">
                        <h2 class="text-xl font-semibold text-blue-600 hover:underline">
                            <a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a>
                        </h2>
                        <div class="flex gap-2">
                            <a href="{{ route('post.edit', $post->id) }}"
                                class="text-blue-500 hover:text-blue-700 text-lg">
                                <i class="fa fa-edit"></i>
                            </a>

                            {{-- allow only admin to delete the post --}}
                            @if (auth()->user() && auth()->user()->role === 'admin')
                                <form method="post" action="{{ route('post.delete', $post->id) }}"
                                    onsubmit="return confirm('Are you sure to delete?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 text-lg">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                            {{-- allow only admin to delete the post --}}

                        </div>
                    </div>
                    <p class="text-gray-700 mt-2">{{ $post->description }}</p>
                    <div class="text-sm text-gray-600 mt-3 flex items-center gap-2">
                        Status:
                        <span
                            class="inline-block px-3 py-0.5 text-sm font-medium rounded-full
                            {{ $post->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($post->status) }}
                        </span>
                        | Created: {{ $post->created_at_human }}
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded shadow p-6 text-center text-gray-500">
                No posts yet.
            </div>
        @endforelse

        <div class="mt-6">
            {{ $allPosts->links() }}
        </div>
    </div>

</body>

</html>
