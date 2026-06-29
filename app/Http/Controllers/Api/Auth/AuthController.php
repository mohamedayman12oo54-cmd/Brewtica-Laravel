<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ){}

    // ======= Register =======
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'Your account has been created!',
            //     'user' => $result['user'],
            //     'token' => $result['token'],
            //     'token_type' => $result['token_type'],
            //     'expires_in' => $result['expires_in'],
            // ], 201);

        // After ApiResponse Integration

            return ApiResponse::created(
                $result['user'],
                'Your account has been created!',
                [
                    'token' => $result['token'],
                    'token_type' => $result['token_type'],
                    'expires_in' => $result['expires_in'],
                ]
            );

        // =============================
    }

    // ======= Login =======
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result){
            // Before ApiResponse Integration

                // return response()->json([
                //     'status' => 'error',
                //     'message' => 'The login information you entered is incorrect.',
                // ], 401);

            // After ApiResponse Integration
            
                return ApiResponse::unauthorized('The login information you entered is incorrect.');
            
            // =============================
        }

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'You have been logged in successfully.',
            //     'user' => $result['user'],
            //     'token' => $result['token'],
            //     'token_type' => $result['token_type'],
            //     'expires_in' => $result['expires_in'],
            // ], 200);

        // After ApiResponse Integration

            return ApiResponse::success([
                'user' => $result['user'],
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ], 'You have been logged in successfully.');

        // =============================
    }

    // ======= Logout =======
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'You have been logged out successfully.',

            // ], 200);
        
        // After ApiResponse Integration

            return ApiResponse::success(message: 'You have been logged out successfully.');
        
        // =============================
    }

    // ======= Refresh =======
    public function refresh(): JsonResponse
    {
        $result = $this->authService->refresh();

        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'token' => $result['token'],
            //     'token_type' => $result['token_type'],
            //     'expires_in' => $result['expires_in'],
            // ], 200);

        // After ApiResponse Integration

            return ApiResponse::success([
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ]);

        // =============================
    }

    // ======= Me =======
    public function me(): JsonResponse
    {
        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'user' => auth('api')->user(),
            // ], 200);

        // After ApiResponse Integration

            return ApiResponse::success(
                auth('api')->user()
            );

        // =============================
    }
}
