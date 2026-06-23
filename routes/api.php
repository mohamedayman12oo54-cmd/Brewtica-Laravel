<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Menu\MenuController;
use Illuminate\Support\Facades\Route;


// ======= Public Routes =======

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// ======= Protected Routes =======

Route::prefix('auth')->middleware('auth:api')->group(function () {
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

// ======= Menu Routes (Public) =======
Route::prefix('menu')->group(function () {
    Route::get('categories', [MenuController::class, 'categories']);
    Route::get('items', [MenuController::class, 'items']);
    Route::get('items/{id}', [MenuController::class, 'show']);
});