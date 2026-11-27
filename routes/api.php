<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('/login', [TokenController::class, 'store'])->middleware('throttle:10,1')->name('login');
    Route::post('/logout', [TokenController::class, 'destroy'])->middleware('auth:sanctum')->name('logout');

    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
        Route::match(['put', 'patch'], '/posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    });
});
