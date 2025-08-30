<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Post;
use App\Events\PostCreated;
use App\Events\PostDeleted;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $page = request('page', 1); // Get the current page, default to 1
        $cacheKey = 'posts.all.page.' . $page;

        $allPosts = Cache::remember($cacheKey, now()->addMinutes(2), function () {
            return Post::orderBy('created_at', 'desc')->simplePaginate(5);
        });

        return view('posts.index', compact('allPosts'));
    }

    public function fetchAllPost()
    {
        $allPosts = Post::orderBy('created_at', 'desc')->simplePaginate(5);
        $html = view('posts.load-post', compact('allPosts'))->render();

        return response()->json(['status' => 'valid', 'data' => $html]);
    }

    public function create()
    {
        $this->authorize('create', Post::class);

        return view('posts.create');
    }

    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|min:3|max:100',
            'description' => 'required|min:10|max:1000',
            'image' => 'nullable|image|mimes:png,jpg|max:2048',
            'status' => 'required|in:active,inactive'
        ]);

        //store image if coming but 1st unlink previous one
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('post', 'public');
        }

        //create slug
        $title = $request->post('title');
        $validated['slug'] = Str::slug($title);

        //store user id
        $validated['user_id'] = Auth::id();

        $save = Post::create($validated);

        if ($save) {
            //broadcast 
            event(new PostCreated());
            //clear cache
            Cache::forget('posts.all');

            return redirect()->back()->with('success', 'Post created successfully!');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function singlePost($slug)
    {
        $singlePost = Post::where('slug', $slug)->firstOrFail();

        return view('posts.single-post', compact('singlePost'));
    }

    public function editPost($id)
    {
        $singleRow = Post::findOrFail($id);

        Gate::authorize('edit-post', $singleRow);

        return view('posts.edit', compact('singleRow'));
    }

    public function updatePost(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|min:3|max:100',
            'description' => 'required|min:3,max:1000',
            'image' => 'nullable|image|mimes:png,jpg|max:2048',
            'status' => 'required|in:active,inactive'
        ]);

        $post = Post::findOrFail($id);

        //check image is coming or not
        if ($request->hasFile('image')) {
            //check exists
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                $delete = Storage::disk('public')->delete($post->image);
            }
            $validated['image'] = $request->file('image')->store('post', 'public');
        }
        //create slug 
        $title = $request->post('title');
        $validated['slug'] = Str::slug($title);

        //store user id
        $validated['user_id'] = Auth::id();

        //save the data
        $save = $post->update($validated);

        if ($save) {
            //broadcast 
            event(new PostCreated());
            //clear cache
            Cache::forget('posts.all');

            return redirect()->route('home')->with('success', 'Post updated successfully!');
        } else {
            return redirect()->route('home')->with('error', 'Something went wrong!');
        }
    }

    public function deletePost($id)
    {
        try {
            // Allow only admin to delete
            $post = Post::findOrFail($id);

            Gate::authorize('delete-post');

            // Delete image if exists and is not the default one
            if ($post->image && $post->image !== 'default.jpg' && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }

            $post->delete();

            //broadcast 
            event(new PostDeleted($id));

            return response()->json(['status' => 'valid', 'message' => 'Post deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Post Deletion Error: ' . $e->getMessage());

            return response()->json(['status' => 'invalid', 'message' => 'An error occurred while deleting the post'], 500);
        }
    }

    public function fetchDescFromAI(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|min:3|max:100'
            ]);

            //get description from AI

            $response = $this->getDescriptionFromAI($validated['title']);

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getDescriptionFromAI(string $title)
    {
        $prompt = "Write a concise, factual, SEO-friendly blog description (120-180 words) for the post titled: \"{$title}\". Avoid fluff and include relevant search terms.";

        $apiKey = config('services.gemini.key'); // GEMINI_API_KEY
        $model = config('services.gemini.model', 'gemini-2.5-flash-lite'); // avoids thinking by default

        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 220,
                'temperature' => 0.7,
            ],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::asJson()
            ->timeout(20)
            ->retry(2, 500, throw: false)
            ->post($url, $payload);

        if (!$response->ok()) {
            return response()->json([
                'success' => false,
                'message' => $response->json(),
            ], 422);
        }

        $json = $response->json();

        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return response()->json([
            'success' => true,
            'data' => $text,
            'usage' => $json['usageMetadata'] ?? null, // check thoughtsTokenCount here
        ]);
    }

}
