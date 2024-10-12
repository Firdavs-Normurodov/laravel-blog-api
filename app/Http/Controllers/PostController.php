<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        return Post::with('user')->get();
    }

    public function userPosts()
    {
        $userId = auth()->id(); // Tizimga kirgan foydalanuvchining ID sini olish
        $posts = Post::where('user_id', $userId)->get(); // Foydalanuvchining postlarini olish

        return response()->json($posts, 200);
    }
    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        return response()->json($post, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $post = Post::create([
            'title' => $request->title,
            'body' => $request->body,
            'img' => $request->file('img') ? $request->file('img')->store('imgposts', 'public') : 'default.jpg',
            'user_id' => auth()->id(), // Foydalanuvchi ID ni saqlash
        ]);

        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Validatsiya
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        // Postni yangilash
        $post->title = $request->input('title');
        $post->body = $request->input('body');

        // Rasmni yangilash
        if ($request->hasFile('img')) {
            $path = $request->file('img')->store('imgposts', 'public');
            $post->img = $path;
        }

        $post->save();

        return response()->json($post, 200);
    }


    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to delete this post'], 403);
        }

        $post->delete();
        return response()->json(['message' => 'Post deleted successfully'], 200);
    }
}
