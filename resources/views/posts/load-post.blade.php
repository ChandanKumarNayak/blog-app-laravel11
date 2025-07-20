@forelse ($allPosts as $post)
    @php
        $imageLink = asset('storage/' . ($post->image ?? 'default.jpg'));
    @endphp
    <div id="post-{{ $post->id }}" class="bg-white rounded-lg shadow-md p-5 mb-6 flex flex-col sm:flex-row gap-4">
        <div class="w-full sm:w-48">
            <img src="{{ $imageLink }}" alt="Post Image" class="w-full h-32 object-cover rounded">
        </div>
        <div class="flex-1">
            <div class="flex justify-between items-start gap-3">
                <h2 class="text-xl font-semibold text-blue-600 hover:underline">
                    <a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a>
                </h2>
                <div class="flex gap-2">
                    <a href="{{ route('post.edit', $post->id) }}" class="text-blue-500 hover:text-blue-700 text-lg">
                        <i class="fa fa-edit"></i>
                    </a>

                    {{-- allow only admin to delete the post --}}
                    {{-- @if (auth()->user() && auth()->user()->role === 'admin')
                                <form method="post" action="{{ route('post.delete', $post->id) }}"
                                    onsubmit="return confirm('Are you sure to delete?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 text-lg">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @endif --}}
                    {{-- allow only admin to delete the post --}}

                    {{-- allow only admin to delete the post - using ajax --}}
                    @if (auth()->user() && auth()->user()->role === 'admin')
                        <button class="text-red-500 hover:text-red-700 text-lg dltBtn"
                            data-url="{{ route('post.delete', ['id' => $post->id]) }}">
                            <i class="fa fa-trash"></i>
                        </button>
                    @endif
                    {{-- allow only admin to delete the post - using ajax --}}

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