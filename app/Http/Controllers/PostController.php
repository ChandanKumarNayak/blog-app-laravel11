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
use Symfony\Component\Mime\MimeTypes;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $page = request('page', 1); // Get the current page, default to 1
        //$cacheKey = 'posts.all.page.' . $page;

        // $allPosts = Cache::remember($cacheKey, now()->addMinutes(2), function () {
        //     return Post::orderBy('created_at', 'desc')->simplePaginate(5);
        // });

        $allPosts = Post::orderBy('created_at', 'desc')->simplePaginate(5);

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

        //create slug
        $title = $request->post('title');
        $validated['slug'] = Str::slug($title);

        //store image if coming otherwise generate from AI
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('post', 'public');
        } else {
            $prompt = "Create a blog header image for the post titled '{$title}'.Photorealistic editorial style; include clean negative space at the top for title overlay.";
            //$this->generateImage($prompt);
            $validated['image'] = $this->huggingFaceImageGeneration($prompt);
        }

        //store user id
        $validated['user_id'] = Auth::id();

        $save = Post::create($validated);

        if ($save) {
            //broadcast 
            event(new PostCreated());
            //clear cache
            //Cache::forget('posts.all');

            return redirect()->route('home')->with('success', 'Post created successfully!');
        } else {
            return redirect()->route('home')->with('error', 'Something went wrong!');
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
            'description' => 'required|min:3,max:1o00',
            'image' => 'nullable|image|mimes:png,jpg|max:2048',
            'status' => 'required|in:active,inactive'
        ]);

        $post = Post::findOrFail($id);

        //check image is coming or not - if not coming generate using AI
        if ($request->hasFile('image')) {
            //check exists
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                $delete = Storage::disk('public')->delete($post->image);
            }
            $validated['image'] = $request->file('image')->store('post', 'public');
        } else {
            $title = $request->post('title');
            $prompt = "Create a blog header image for the post titled '{$validated['title']}'.Photorealistic editorial style; include clean negative space at the top for title overlay.";
            //$this->generateImage($prompt);
            $validated['image'] = $this->huggingFaceImageGeneration($prompt);
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
            //Cache::forget('posts.all');

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
        $prompt = "Write a concise, factual, description (maximum upto 1000 characters) for the topic titled: \"{$title}\".";

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

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::asJson()
            ->withHeaders(['x-goog-api-key' => $apiKey]) // prefer header for API key
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

    public function generateImage(string $prompt)
    {
        $apiKey = config('services.gemini.key'); // GEMINI_API_KEY
        $model = config('services.gemini.image_model', 'gemini-2.5-flash-image-preview'); // image-capable model

        // Build parts: always text, optionally an inline_data image for editing/composition
        $parts = [
            ['text' => $prompt],
        ];

        // Explicitly ask for image (and optionally text) in the response
        $payload = [
            'contents' => [
                ['parts' => $parts],
            ],
            'generation_config' => [
                'response_modalities' => ['IMAGE', 'TEXT'], // ensure image parts are returned
            ],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::asJson()
            ->withHeaders(['x-goog-api-key' => $apiKey]) // prefer header for API key
            ->timeout(120)
            ->retry(2, 500)
            ->post($url, $payload);

        if (!$response->ok()) {
            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'message' => $response->json(),
            ], $response->status());
        }

        $json = $response->json();
        dd($json);
        $imageUrls = [];
        $candidates = $json['candidates'] ?? [];

        foreach ($candidates as $cand) {
            $candParts = $cand['content']['parts'] ?? [];
            foreach ($candParts as $part) {
                // Handle both inline_data (REST) and inlineData (SDK-style)
                $inline = $part['inline_data'] ?? ($part['inlineData'] ?? null);
                if (is_array($inline) && !empty($inline['data'])) {
                    $mime = $inline['mime_type'] ?? ($inline['mimeType'] ?? 'image/png');
                    $ext = MimeTypes::getDefault()->getExtensions($mime) ?? 'png';
                    // Optionally normalize preferred 'jpeg' to 'jpg'
                    $ext = $ext === 'jpeg' ? 'jpg' : $ext;

                    $filename = 'ai_images/' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $ext;
                    Storage::disk('public')->put($filename, base64_decode($inline['data']));

                    // Prefer Storage::url when using the public disk + storage:link
                    $imageUrls[] = Storage::url($filename);
                }
            }
        }

        // Fallback: collect any text parts from all candidates
        $textOut = '';
        if (empty($imageUrls)) {
            $texts = [];
            foreach ($candidates as $cand) {
                foreach (($cand['content']['parts'] ?? []) as $part) {
                    if (!empty($part['text'])) {
                        $texts[] = $part['text'];
                    }
                }
            }
            $textOut = trim(implode("\n", $texts));
        }

        return response()->json([
            'success' => true,
            'images' => $imageUrls,
            'text' => $textOut,
            'usage' => $json['usageMetadata'] ?? null,
        ]);
    }

    public function huggingFaceImageGeneration(string $prompt)
    {
        $token = config('services.hf.token');
        $model = config('services.hf.image_model'); // e.g. Qwen/Qwen-Image

        // Use pixel dimensions, not 16 and 9.
        $parameters = array_filter([
            'width' => 1024,                 // 16:9 ~ 1024x576
            'height' => 576,
            'num_inference_steps' => 24,     // quality/speed trade-off
            'guidance_scale' => 7.5,         // prompt adherence
            'negative_prompt' => 'text, watermark, logo',
            'seed' => null,                  // set an int for reproducibility
        ], fn($v) => $v !== null); // [12]

        $payload = [
            'inputs' => $prompt,
            'parameters' => $parameters,
        ]; // Serverless infers text-to-image from the model card task. [11][12]

        $url = 'https://api-inference.huggingface.co/models/' . $model; // [11]

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])
            ->timeout(180)
            ->retry(2, 1500)
            ->post($url, $payload); // [11]

        // Handle cold start (503) by waiting briefly and retrying. [11]
        $attempts = 0;
        while (in_array($response->status(), [503, 524], true) && $attempts < 2) {
            $attempts++;
            sleep(5);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])
                ->timeout(180)
                ->post($url, $payload); // [11]
        }

        if (!$response->ok()) {
            return null;
            // return response()->json([
            //     'success' => false,
            //     'message' => $response->json(),
            //     'status' => $response->status(),
            // ], 422); // Errors come back as JSON on non-2xx. [11]
        }

        // Serverless returns raw image bytes for text-to-image. [11]
        $bytes = $response->body();
        $contentType = $response->header('Content-Type');

        // Save image (unreachable while dd is active). [11]
        $ext = str_contains($contentType, 'jpeg') ? 'jpg'
            : (str_contains($contentType, 'webp') ? 'webp' : 'png');
        $path = 'post/' . now()->format('Ymd_His') . '_' . Str::random(6) . '.' . $ext;
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }

}
