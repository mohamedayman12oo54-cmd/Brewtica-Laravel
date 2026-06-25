<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Favorite\FavoriteController;
use App\Http\Controllers\Api\Menu\MenuController;
use App\Http\Controllers\Api\Profile\ProfileController;
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

// ======= Profile Routes (Protected) =======
Route::middleware('auth:api')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::patch('/', [ProfileController::class, 'update']);
    Route::patch('/password', [ProfileController::class, 'updatePassword']);
    Route::post('/phones', [ProfileController::class, 'storePhone']);
    Route::patch('/phones/{id}/primary', [ProfileController::class, 'setPrimary']);
    Route::delete('/phones/{id}', [ProfileController::class, 'deletePhone']);
});

// ======= Favorites Routes (Protected) =======
Route::middleware('auth:api')->group(function () {
    Route::get('favorites', [FavoriteController::class, 'show']);
    Route::post('favorites/{menuItemId}', [FavoriteController::class, 'toggle']);
    Route::delete('favorites/{menuItemId}', [FavoriteController::class, 'remove']);
});