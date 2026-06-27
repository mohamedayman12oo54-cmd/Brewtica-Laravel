<?php

namespace App\Http\Controllers\Api\Delivery;

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

        return response()->json([
            'status' => 'success',
            'data'   => $deliveries,
        ]);
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

            $httpStatus = $result['reason'] === 'not_found' ? 404 : 422;

            return response()->json([
                'status'  => 'error',
                'message' => $message,
            ], $httpStatus);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'The delivery status has been updated',
        ]);
    }
}