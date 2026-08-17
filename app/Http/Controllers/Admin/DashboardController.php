<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\Services\DashboardService;

class DashboardController extends BaseController
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {
        $this->middleware('permission:dashboard.view');
    }

    public function index()
    {
        return inertia(
            'Admin/Dashboard',
            $this->dashboardService->getDashboardData()
        );
    }
}