<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
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

        $imagePath = 'images/post_default.jpg'; // Default rasm
        if ($request->hasFile('img')) {
            $image = $request->file('img');
            $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('imgposts', $imageName, 'public');
        } else {
            $defaultImagePath = public_path('images/post_default.jpg');
            $randomName = Str::random(40) . '.jpg';
            $imagePath = 'imgposts/' . $randomName;
            \Storage::disk('public')->put($imagePath, file_get_contents($defaultImagePath));
        }

        $post = Post::create([
            'title' => $request->title,
            'body' => $request->body,
            'img' => $imagePath,
            'user_id' => auth()->id(),
        ]);

        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        // Postni ID orqali qidiramiz
        $post = Post::find($id);

        // Post mavjudligini tekshiramiz
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Faqat o'z postini yangilash uchun tekshirish
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validatsiya
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'img' => 'nullable|image|max:10240'
        ]);

        // Rasmni yangilash
        if ($request->hasFile('img')) {
            // Yangi rasmni yuklaymiz
            $image = $request->file('img');
            $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('imgposts', $imageName, 'public');
            $post->img = $imagePath; // Yangi rasm manzilini saqlaymiz
        }

        // Yangilanishlarni saqlaymiz
        $post->title = $request->title;
        $post->body = $request->body;
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
