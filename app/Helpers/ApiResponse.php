<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    // ======= Success =======
    public static function success(
        mixed $data = null,
        string $message = '',
        int $status = 200,
        array $extra = []
    ): JsonResponse {
        $response = ['status' => 'success'];

        if ($message) $response['message'] = $message;
        if ($data !== null) $response['data'] = $data;

        return response()->json([...$response, ...$extra], $status);
    }

    // ======= Error =======
    public static function error(
        string $message,
        int $status = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if ($errors !== null) $response['errors'] = $errors;

        return response()->json($response, $status);
    }

   // ======= Created =======
    public static function created(
        mixed $data = null,
        string $message = 'Successfully created.',
        array $extra = []
    ): JsonResponse {
        return self::success($data, $message, 201, $extra);
    }

    // ======= Not Found =======
    public static function notFound(
        string $message = 'The item does not exist.'
    ): JsonResponse {
        return self::error($message, 404);
    }

    // ======= Unauthorized =======
    public static function unauthorized(
        string $message = 'You are not authorized.'
    ): JsonResponse {
        return self::error($message, 401);
    }

    // ======= Forbidden =======
    public static function forbidden(
        string $message = 'You are not allowed to do this.'
    ): JsonResponse {
        return self::error($message, 403);
    }

    // ======= Validation Error =======
    public static function validationError(
        mixed $errors,
        string $message = 'Invalid data'
    ): JsonResponse {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], 422);
    }

    // ======= Too Many Requests =======
    public static function tooManyRequests(
        string $message = 'too much — wait a bit and try again.'
    ): JsonResponse {
        return self::error($message, 429);
    }

    // ======= Server Error =======
    public static function serverError(
        string $message = 'There is a system error.'
    ): JsonResponse {
        return self::error($message, 500);
    }
}
