<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
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

// Auth routes for guest user
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

// Fetch all posts
Route::get('posts', [PostController::class, 'index'])->name('posts.index');
Route::get('posts/{id}', [PostController::class, 'show'])->name('show');
Route::get('fetch-categories', [PostController::class, 'fetchCategories'])->name('fetch-categories');

// Fetch all categories
Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('categories/{id}', [CategoryController::class, 'show'])->name('categories.show');

// If user is authenticated
Route::middleware('auth:sanctum')->group(function () {

    // Route group for posts
    Route::group(['prefix' => 'posts', 'as' => 'posts.'], function () {
        Route::post('store', [PostController::class, 'store'])->name('store');
        Route::post('update/{id}', [PostController::class, 'update'])->name('update');
        Route::delete('destroy/{id}', [PostController::class, 'destroy'])->name('destroy');
    });

    // Auth routes for authenticated user
    Route::get('me', [AuthController::class, 'me'])->name('me');
    Route::delete('logout', [AuthController::class, 'logout'])->name('logout');
});
