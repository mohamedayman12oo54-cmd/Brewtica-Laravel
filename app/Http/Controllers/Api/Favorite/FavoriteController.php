<?php

namespace App\Http\Controllers\Api\Favorite;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct
    (
        private readonly FavoriteService $favoriteService
    ){}

    // GET /api/favorites
    public function show(): JsonResponse
    {
        $user = auth('api')->user();
        $favorites = $this->favoriteService->getFavorites($user);

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'data' => FavoriteResource::collection($favorites),
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(FavoriteResource::collection($favorites));

        // =============================
    }

    // POST /api/favorite/{menuItemId}
    public function toggle(int $menuItemId): JsonResponse
    {
        $user = auth('api')->user();
        $result = $this->favoriteService->toggleFavorite($user, $menuItemId);

        $message = $result['action'] === 'added'
            ? 'The item has been added successfully!'
            : 'The item has been removed successfully!'
        ;

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'action' => $result['action'],
            //     'message' => $message,
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(
                message: $message,
                extra: ['action' => $result['action']]
            );

        // =============================
    }

    // DELETE /api/favorite/{menuItemId}
    public function remove(int $menuItemId): JsonResponse
    {
        $user = auth('api')->user();
        $removed = $this->favoriteService->remove($user, $menuItemId);

        if(!$removed){
            // Before ApiResponse Integration

                // return response()->json([
                //     'status' => 'error',
                //     'message' => 'The item not found in favorites list!'
                // ], 404);

            // After ApiResponse Integration

                return ApiResponse::notFound('The item not found in favorites list!');

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'The item has been removed from favorites list'
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(message: 'The item has been removed from favorites list');

        // =============================
    }
}
