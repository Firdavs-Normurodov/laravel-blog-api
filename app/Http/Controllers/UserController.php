<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        // Validatsiya
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Agar validatsiya muvaffaqiyatsiz bo'lsa
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Rasmni yuklash va foydalanuvchini yaratish
        $imagePath = 'images/default.jpg'; // Default rasm
        if ($request->hasFile('img')) {
            $image = $request->file('img');
            $imagePath = $image->store('images', 'public');
        }

        // Foydalanuvchini yaratish
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'img' => $imagePath,
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        // Validatsiya
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Foydalanuvchini tekshirish
        $user = User::where('email', $validated['email'])->first();

        // Parolni tekshirish
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Token yaratish
        $token = $user->createToken('auth_token')->plainTextToken;

        // Javob qaytarish
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        // Foydalanuvchining tokenini o'chirish
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }
}
