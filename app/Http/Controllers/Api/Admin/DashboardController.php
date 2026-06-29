<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    // GET /api/admin/dashboard
    public function index(): JsonResponse
    {
        // Before ApiResponse Integration

            // return response()->json([
            //     'status' => 'success',
            //     'data'   => $this->dashboardService->getStatistics(),
            // ]);

        // After ApiResponse Integration

            return ApiResponse::success($this->dashboardService->getStatistics());

        // =============================
    }
}
