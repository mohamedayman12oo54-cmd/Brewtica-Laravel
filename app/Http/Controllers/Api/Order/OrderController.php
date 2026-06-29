<?php

namespace App\Http\Controllers\Api\Order;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResourse;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    // POST /api/orders
    public function store(): JsonResponse
    {
        $user   = auth('api')->user();
        $result = $this->orderService->createOrder($user);

        if (!$result['success']) {
            $message = match($result['reason']) {
                'cart_empty' => 'Empty cart',
                default      => 'An error occured',
            };

            // Before ApiResponse Integration

                // return response()->json([
                //     'status'  => 'error',
                //     'message' => $message,
                // ], 422);

            // After ApiResponse Integration

                return ApiResponse::error($message, 422);

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'The order has been created successfully.',
            //     'data'    => new OrderResourse($result['order']),
            // ], 201);

        // After ApiResponse Integration

            return ApiResponse::created(
                new OrderResourse($result['order']),
                'The order has been created successfully.'
            );

        // =============================
    }

    // GET /api/orders
    public function index(): JsonResponse
    {
        $user   = auth('api')->user();
        $orders = $this->orderService->getCustomerOrders($user);

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'data'   => OrderResourse::collection($orders),
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(OrderResourse::collection($orders));

        // =============================
    }

    // GET /api/orders/{id}
    public function show(int $id): JsonResponse
    {
        $user  = auth('api')->user();
        $order = $this->orderService->getOrder($user, $id);

        if (!$order) {
            // Before ApiResponse Integration

                // return response()->json([
                //     'status'  => 'error',
                //     'message' => 'The order not found.',
                // ], 404);

            // After ApiResponse Integration

                return ApiResponse::notFound('The order not found.');

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'data'   => new OrderResourse($order),
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(new OrderResourse($order));

        // =============================
    }

    // DELETE /api/orders/{id}
    public function destroy(int $id): JsonResponse
    {
        $user   = auth('api')->user();
        $result = $this->orderService->cancelOrder($user, $id);

        if (!$result['success']) {
            $message = match($result['reason']) {
                'not_found'      => 'The order not found',
                'cannot_cancel'  => 'You cannot cancel the order in this status.',
                default          => 'An error occured.',
            };

            // Before ApiResponse Integration

                // $status = $result['reason'] === 'not_found' ? 404 : 422;
                //
                // return response()->json([
                //     'status'  => 'error',
                //     'message' => $message,
                // ], $status);

            // After ApiResponse Integration

                if ($result['reason'] === 'not_found') {
                    return ApiResponse::notFound($message);
                }

                return ApiResponse::error($message, 422);

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'The order has been cancled successfully',
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(message: 'The order has been cancled successfully');

        // =============================
    }
}
