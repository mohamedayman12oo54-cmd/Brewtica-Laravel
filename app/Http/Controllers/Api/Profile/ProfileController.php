<?php

namespace App\Http\Controllers\Api\Profile;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\StoreUserPhoneRequest;
use App\Http\Resources\Profile\ProfileResource;
use App\services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ){}

    // Get /api/profile
    public function show(): JsonResponse
    {
        $user = auth('api')->user();
        $profile = $this->profileService->getProfile($user);

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'data' => new ProfileResource($profile)
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(new ProfileResource($profile));

        // =============================
    }

    // PATCH /api/profile
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $profile = $this->profileService->updateProfile($user, $request->validated());

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'Your profile data have been updated',
            //     'data' => new ProfileResource($profile)
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(
                new ProfileResource($profile),
                'Your profile data have been updated'
            );

        // =============================
    }

    // PATCH /api/profile/password
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $updated = $this->profileService->updatePassword($user, $request->validated());

        if(!$updated){
            // Before ApiResponse Integration

                // return response()->json([
                //     'status' => 'success',
                //     'message' => 'The current password is incorrect.'
                // ], 422);

            // After ApiResponse Integration

                return ApiResponse::error('The current password is incorrect.', 422);

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'The password has been changed successfully.'
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(message: 'The password has been changed successfully.');

        // =============================
    }

    // POST api/profile/phones
    public function storePhone(StoreUserPhoneRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $phone = $this->profileService->storePhone($user, $request->validated()['phone']);

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'The phone number has been added successfully.',
            //     'data' => [
            //         'id' => $phone->id,
            //         'phone' => $phone->phone,
            //         'is_primary' => (bool) $phone->is_primary
            //     ],
            // ], 201);

        // After ApiResponse Integration

            return ApiResponse::created(
                [
                    'id' => $phone->id,
                    'phone' => $phone->phone,
                    'is_primary' => (bool) $phone->is_primary
                ],
                'The phone number has been added successfully.'
            );

        // =============================
    }

    // PATCH api/profile/phones/{id}/primary
    public function setPrimary(int $id): JsonResponse
    {
        $user = auth('api')->user();
        $result = $this->profileService->setPrimaryPhone($user, $id);

        if(!$result){
            // Before ApiResponse Integration

                // return response()->json([
                //     'status' => 'error',
                //     'message' => 'This phone number not found!',
                // ], 404);

            // After ApiResponse Integration

                return ApiResponse::notFound('This phone number not found!');

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'The primary phone number has been successfully set.'
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(message: 'The primary phone number has been successfully set.');

        // =============================
    }

    // DELETE api/profile/phones/{id}
    public function deletePhone(int $id): JsonResponse
    {
        $user = auth('api')->user();
        $result = $this->profileService->deletePhone($user, $id);

        if($result === 'not_found'){
            // Before ApiResponse Integration

                // return response()->json([
                //     'status' => 'error',
                //     'message' => 'This phone number not found!',
                // ], 404);

            // After ApiResponse Integration

                return ApiResponse::notFound('This phone number not found!');

            // =============================
        }

        if($result === 'cannot_delete_only_primary'){
            // Before ApiResponse Integration

                // return response()->json([
                //     'status' => 'error',
                //     'message' => 'The sole primary phone number cannot be deleted.',
                // ], 422);

            // After ApiResponse Integration

                return ApiResponse::error('The sole primary phone number cannot be deleted.', 422);

            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'The phone number has been deleted successfully.'
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success(message: 'The phone number has been deleted successfully.');

        // =============================
    }
}
