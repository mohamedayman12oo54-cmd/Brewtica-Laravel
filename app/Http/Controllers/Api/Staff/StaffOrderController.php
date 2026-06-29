<?php

namespace App\Http\Controllers\Api\Staff;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateOrderStatusRequest;
use App\Http\Resources\Order\OrderResourse;
use App\Services\StaffOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffOrderController extends Controller
{
    public function __construct(
        private readonly StaffOrderService $staffOrderService
    ) {}

    // GET /api/staff/orders
    public function index(Request $request): JsonResponse
    {
        $orders = $this->staffOrderService->getAllOrders([
            'status' => $request->query('status'),
        ]);

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'data'   => OrderResourse::collection($orders),
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(OrderResourse::collection($orders));

        // =============================
    }

    // PATCH /api/staff/orders/{id}/status
    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $result = $this->staffOrderService->updateStatus($id, $request->validated()['status']);

        if (!$result['success']) {
            $message = match($result['reason']) {
                'not_found'          => 'The order not found',
                'invalid_transition' => 'You cannot cancel the order in this status.',
                default              => 'An error occured',
            };

            // Before ApiResponse Integration

                // $httpStatus = $result['reason'] === 'not_found' ? 404 : 422;
                //
                // return response()->json([
                //     'status'  => 'error',
                //     'message' => $message,
                // ], $httpStatus);

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
            //     'message' => 'The status of order has been updated',
            //     'data'    => new OrderResourse($result['order']),
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(
                new OrderResourse($result['order']),
                'The status of order has been updated'
            );

        // =============================
    }
}
