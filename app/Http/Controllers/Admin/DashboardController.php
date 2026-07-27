<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {
    }

    public function index()
    {
        return inertia(
            'Admin/Dashboard',
            $this->dashboardService->getDashboardData()
        );
    }
}