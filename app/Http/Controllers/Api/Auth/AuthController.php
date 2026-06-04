<?php

namespace App\Http\Controllers\Api\Auth;

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

        return response()->json([
            'status' => 'success',
            'message' => 'Your account has been created!',
            'user' => $result['user'],
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ], 201);
    }

    // ======= Login =======
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result){
            return response()->json([
                'status' => 'error',
                'message' => 'The login information you entered is incorrect.',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'You have been logged in successfully.',
            'user' => $result['user'],
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ], 200);
    }

    // ======= Refresh =======
    public function refresh(): JsonResponse
    {
        $result = $this->authService->refresh();

        return response()->json([
            'status' => 'success',
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ], 200);
    }

    // ======= Me =======
    public function me(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'user' => auth('api')->user(),
        ], 200);
    }
}
