<?php

namespace App\Http\Controllers\Api\Admin;

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
        return response()->json([
            'status' => 'success',
            'data'   => $this->dashboardService->getStatistics(),
        ]);
    }
}
