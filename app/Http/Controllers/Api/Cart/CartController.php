<?php

namespace App\Http\Controllers\Api\Cart;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    // GET /api/cart
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        $cart = $this->cartService->getCart($user);

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'data'   => $cart,
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success($cart);

        // =============================
    }

    // Post /api/cart
    public function store(AddToCartRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $result = $this->cartService->addToCart($user, $request->validated());

        if (!$result['success']) {
            // Before ApiResponse Integration

                // return response()->json([
                //     'status'  => 'error',
                //     'message' => 'This size is not available for this item.',
                // ], 422);

            // After ApiResponse Integration

                return ApiResponse::error('This size is not available for this item.', 422);

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'Item added to cart successfully.',
            // ], 201);

        // After ApiResponse Integration

            return ApiResponse::created(message: 'Item added to cart successfully.');

        // =============================
    }

    // PATCH /api/cart/{menuItemId}/{size}
    public function update(UpdateCartRequest $request, int $menuItemId, string $size): JsonResponse
    {
        $user     = auth('api')->user();
        $quantity = $request->validated()['quantity'];

        $result   = $this->cartService->updateCartItem($user, $menuItemId, $size, $quantity);

        if (!$result['success']) {
            // Before ApiResponse Integration

                // return response()->json([
                //     'status'  => 'error',
                //     'message' => 'Item not found in the cart.',
                // ], 404);

            // After ApiResponse Integration

                return ApiResponse::notFound('Item not found in the cart.');

            // =============================
        }

        $message = $result['action'] === 'removed'
            ? 'Item removed from cart successfully.'
            : 'Quantity updated successfully.';

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'action'  => $result['action'],
            //     'message' => $message,
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(
                message: $message,
                extra: ['action' => $result['action']]
            );

        // =============================
    }

    // DELETE /api/cart/{menuItemId}/{size}
    public function destroy(int $menuItemId, string $size): JsonResponse
    {
        $user    = auth('api')->user();
        $removed = $this->cartService->removeCartItem($user, $menuItemId, $size);

        if (!$removed) {
            // Before ApiResponse Integration

                // return response()->json([
                //     'status'  => 'error',
                //     'message' => 'Item not found in the cart.',
                // ], 404);

            // After ApiResponse Integration

                return ApiResponse::notFound('Item not found in the cart.');

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'Item removed from cart successfully.',
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(message: 'Item removed from cart successfully.');

        // =============================
    }

    // DELETE /api/cart
    public function clear(): JsonResponse
    {
        $user = auth('api')->user();
        $this->cartService->clearCart($user);

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'The cart has been removed successfully.',
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(message: 'The cart has been removed successfully.');

        // =============================
    }
}
