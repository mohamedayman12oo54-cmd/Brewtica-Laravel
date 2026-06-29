<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // هنا ممكن نـ log الـ errors في الـ Production
        });
    }

    public function render($request, Throwable $e)
    {
        // بنتأكد إن الـ Request بيتوقع JSON
        // (كل الـ API Requests بيبعتوا Accept: application/json)
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException(Request $request, Throwable $e)
    {
        // 1. Model مش موجود
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            return ApiResponse::notFound("The {$model} not found");
        }

        // 2. Route مش موجود
        if ($e instanceof NotFoundHttpException) {
            return ApiResponse::notFound('This endpoint dose not exist');
        }

        // 3. مش Authenticated
        if ($e instanceof AuthenticationException) {
            return ApiResponse::unauthorized();
        }

        // 4. مش مسموحله (Forbidden)
        if ($e instanceof AuthorizationException) {
            return ApiResponse::forbidden();
        }

        // 5. Validation Failed
        if ($e instanceof ValidationException) {
            return ApiResponse::validationError($e->errors());
        }

        // 6. Rate Limit Exceeded
        if ($e instanceof TooManyRequestsHttpException) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
            return response()->json([
                'status'      => 'error',
                'message'     => 'Too many attempts. Please try again later.',
                'retry_after' => (int) $retryAfter,
                // ↑ بنقوله كام ثانية يستنى
            ], 429);
        }

        // 7. أي Exception تاني — Server Error
        // في الـ Production: مش بنكشف تفاصيل الـ error
        if (app()->environment('production')) {
            return ApiResponse::serverError();
        }

        // في الـ Development: بنكشف التفاصيل عشان نـ debug
        return response()->json([
            'status'   => 'error',
            'message'  => $e->getMessage(),
            'file'     => $e->getFile(),
            'line'     => $e->getLine(),
        ], 500);
    }
}