<?php

namespace App\Http\Controllers\Api\Profile;

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

        return response()->json([
            'status' => 'success',
            'data' => new ProfileResource($profile)
        ]);
    }

    // PATCH /api/profile
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $profile = $this->profileService->updateProfile($user, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Your profile data have been updated',
            'data' => new ProfileResource($profile)
        ]);
    }

    // PATCH /api/profile/password
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $updated = $this->profileService->updatePassword($user, $request->validated());

        if(!$updated){
            return response()->json([
                'status' => 'success',
                'message' => 'The current password is incorrect.'
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'The password has been changed successfully.'
        ]);
    }

    // POST api/profile/phones
    public function storePhone(StoreUserPhoneRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $phone = $this->profileService->storePhone($user, $request->validated()['phone']);

        return response()->json([
            'status' => 'success',
            'message' => 'The phone number has been added successfully.',
            'data' => [
                'id' => $phone->id,
                'phone' => $phone->phone,
                'is_primary' => (bool) $phone->is_primary
            ],
        ], 201);
    }

    // PATCH api/profile/phones/{id}/primary
    public function setPrimary(int $id): JsonResponse
    {
        $user = auth('api')->user();
        $result = $this->profileService->setPrimaryPhone($user, $id);

        if(!$result){
            return response()->json([
                'status' => 'error',
                'message' => 'This phone number not found!',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'The primary phone number has been successfully set.'
        ]);
    }

    // DELETE api/profile/phones/{id}
    public function deletePhone(int $id): JsonResponse
    {
        $user = auth('api')->user();
        $result = $this->profileService->deletePhone($user, $id);

        if($result === 'not_found'){
            return response()->json([
                'status' => 'error',
                'message' => 'This phone number not found!',
            ], 404);
        }

        if($result === 'cannot_delete_only_primary'){
            return response()->json([
                'status' => 'error',
                'message' => 'The sole primary phone number cannot be deleted.',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'The phone number has been deleted successfully.'
        ]);
    }
}
