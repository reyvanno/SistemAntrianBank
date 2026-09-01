<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends BaseController
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {
        $this->middleware('permission:dashboard.view');
    }

    /**
     * Menampilkan dashboard admin.
     */
    public function index(Request $request): Response
    {
        return Inertia::render(
            'Admin/Dashboard',
            $this->dashboardService->getDashboardData(
                $request->user()
            )
        );
    }

    /**
     * Mengambil data dashboard terbaru.
     *
     * Endpoint ini digunakan oleh Dashboard.vue
     * untuk melakukan polling tanpa reload halaman.
     */
    public function data(Request $request): JsonResponse
    {
        return response()->json(
            $this->dashboardService->getDashboardData(
                $request->user()
            )
        );
    }
}