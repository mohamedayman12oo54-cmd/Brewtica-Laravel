<?php

namespace App\Http\Controllers\Api\Delivery;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\UpdateDeliveryStatusRequest;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;

class DeliveryController extends Controller
{
    public function __construct(
        private readonly DeliveryService $deliveryService
    ) {}

    // GET /api/delivery/deliveries
    public function index(): JsonResponse
    {
        $user       = auth('api')->user();
        $deliveries = $this->deliveryService->getMyDeliveries($user);

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'data'   => $deliveries,
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success($deliveries);

        // =============================
    }

    // PATCH /api/delivery/deliveries/{id}/status
    public function updateStatus(UpdateDeliveryStatusRequest $request, int $id): JsonResponse
    {
        $user   = auth('api')->user();
        $result = $this->deliveryService->updateStatus($user, $id, $request->validated()['status']);

        if (!$result['success']) {
            $message = match($result['reason']) {
                'not_found'          => 'The delivery not found',
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
            //     'message' => 'The delivery status has been updated',
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(message: 'The delivery status has been updated');

        // =============================
    }
}
