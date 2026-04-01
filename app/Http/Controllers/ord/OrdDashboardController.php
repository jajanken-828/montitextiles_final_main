<?php

namespace App\Http\Controllers\ord;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class OrdDashboardController extends Controller
{
    public function managerDashboard()
    {
        return Inertia::render('Dashboard/ORD/Manager/index', [
            'user' => auth()->user(),
            'recentOrders' => [],
            'stats' => [
                'pendingOrders' => 0,
                'completedToday' => 0,
                'totalRevenue' => 0,
            ],
        ]);
    }

    public function staffDashboard()
    {
        return Inertia::render('Dashboard/ORD/Employee/index', [
            'user' => auth()->user(),
            'recentOrders' => [],
            'stats' => [
                'pendingOrders' => 0,
                'completedToday' => 0,
                'totalRevenue' => 0,
            ],
        ]);
    }
}
