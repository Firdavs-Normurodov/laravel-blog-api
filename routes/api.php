<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication route for logged in user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Barcha foydalanuvchilarni ko'rsatish uchun route
Route::get('users', [UserController::class, 'index']); // Barcha foydalanuvchilarni olish

// Bitta foydalanuvchini ko'rsatish uchun route
Route::get('users/{id}', [UserController::class, 'show']); // Berilgan ID ga mos foydalanuvchini olish

// Foydalanuvchi ro'yxatdan o'tishi uchun yo'l
Route::post('register', [UserController::class, 'register']); // Register uchun route

// Foydalanuvchi kirishi uchun yo'l
Route::post('login', [UserController::class, 'login'])->name('login'); // Login uchun route

// Foydalanuvchini tizimdan chiqarish uchun yo'l
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

// Postlar uchun marshrutlar
Route::get('/posts', [PostController::class, 'index']); // Barcha postlarni olish
Route::get('/posts/{id}', [PostController::class, 'show']); // Berilgan ID ga mos postni olish
// Route::get('/user/posts', [PostController::class, 'userPosts'])->middleware('auth:sanctum'); // Foydalanuvchining postlarini olish
Route::post('/posts', [PostController::class, 'store'])->middleware('auth:sanctum'); // Yangi post yaratish
Route::put('/posts/{id}', [PostController::class, 'update'])->middleware('auth:sanctum'); // Postni yangilash
Route::delete('/posts/{id}', [PostController::class, 'destroy'])->middleware('auth:sanctum'); // Postni o'chirish
// ============