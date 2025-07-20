<!DOCTYPE html>
<html lang="en">

<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        <div class="postDiv">
            @include('posts.load-post', ['allPosts' => $allPosts])
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>
    <script>
        $(document).on('click', '.dltBtn', function(e) {
            e.preventDefault();
            if (confirm('Are you sure to delete?')) {
                var url = $(this).data('url');
                //ajax delete
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.status == 'valid') {
                            //load latest posts
                            loadLatestPosts();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Something went wrong');
                    }
                });
            }
        });

        function loadLatestPosts() {
            const fetchPosts = "{{ route('post.fetch') }}";
            $.ajax({
                url: fetchPosts,
                type: 'GET',
                success: function(response) {
                    //render content
                    $('.postDiv').html(response.data);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching posts:", error);
                }
            });
        }
    </script>
    <script type="module">
        window.Echo.channel('posts-channel')
            .listen('.post-deleted', (event) => {
                console.log("Received post-deleted event:", event);
                $('#post-' + event.postId).remove();
            });
    </script>

</body>

</html>
