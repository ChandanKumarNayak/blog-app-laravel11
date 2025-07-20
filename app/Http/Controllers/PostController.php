<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Events\PostDeleted;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $allPosts = Post::orderBy('created_at', 'desc')->simplePaginate(3);
        return view('posts.index', compact('allPosts'));
    }

    public function fetchAllPost()
    {
        $allPosts = Post::orderBy('created_at', 'desc')->simplePaginate(3);
        $html = view('posts.load-post', compact('allPosts'))->render();

        return response()->json(['status' => 'valid', 'data' => $html]);
    }

    public function create()
    {
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

        $save = Post::create($validated);

        if ($save) {
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

        //save the data
        $save = $post->update($validated);

        if ($save) {
            return redirect()->route('home')->with('success', 'Post updated successfully!');
        } else {
            return redirect()->route('home')->with('error', 'Something went wrong!');
        }
    }

    public function deletePost($id)
    {
        try {
            // Allow only admin to delete
            if (auth()->user()->role !== config('constants.roles.ADMIN')) {
                return response()->json(['status' => 'invalid', 'message' => 'Unauthorized'], 403);
            }

            $post = Post::findOrFail($id);

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

}
