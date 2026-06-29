<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Http\Resources\Admin\StaffResource;
use App\Services\Admin\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(
        private readonly StaffService $staffService
    ) {}

    // GET /api/admin/staff
    public function index(Request $request): JsonResponse
    {
        $users = $this->staffService->getAllUsers([
            'role' => $request->query('role'),
        ]);

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'data'   => StaffResource::collection($users),
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(StaffResource::collection($users));

        // =============================
    }

    // POST /api/admin/staff
    public function store(StoreStaffRequest $request): JsonResponse
    {
        $user = $this->staffService->createStaff($request->validated());

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'Staff user created successfully.',
            //     'data'    => new StaffResource($user),
            // ], 201);

        // After ApiResponse Integration

            return ApiResponse::created(
                new StaffResource($user),
                'Staff user created successfully.'
            );

        // =============================
    }

    // PATCH /api/admin/staff/{id}
    public function update(UpdateStaffRequest $request, int $id): JsonResponse
    {
        $result = $this->staffService->updateUser($id, $request->validated());

        if (!$result['success']) {
            // Before ApiResponse Integration

                // return response()->json([
                //     'status'  => 'error',
                //     'message' => 'User not found.',
                // ], 404);

            // After ApiResponse Integration

                return ApiResponse::notFound('User not found.');

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'User updated successfully.',
            //     'data'    => new StaffResource($result['user']),
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(
                new StaffResource($result['user']),
                'User updated successfully.'
            );

        // =============================
    }

    // DELETE /api/admin/staff/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        $result = $this->staffService->deleteUser($request->user('api')->id, $id);

        if (!$result['success']) {
            $message = match ($result['reason']) {
                'self_delete'    => 'You cannot delete your own account.',
                'has_orders'     => 'This user has existing orders and cannot be deleted.',
                'has_deliveries' => 'This user has assigned deliveries and cannot be deleted.',
                default          => 'User not found.',
            };

            // Before ApiResponse Integration

                // $status = match ($result['reason']) {
                //     'not_found' => 404,
                //     default     => 422,
                // };
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
            //     'message' => 'User deleted successfully.',
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(message: 'User deleted successfully.');

        // =============================
    }
}
