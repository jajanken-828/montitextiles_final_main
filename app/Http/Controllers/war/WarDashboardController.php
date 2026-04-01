<?php

namespace App\Http\Controllers\war;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class WarDashboardController extends Controller
{
    public function managerDashboard()
    {
        return Inertia::render('Dashboard/WAR/Manager/index', [
            'user' => auth()->user(),
            'bins' => [],
            'stats' => [
                'totalBins' => 0,
                'occupiedBins' => 0,
                'availableBins' => 0,
            ],
        ]);
    }

    public function staffDashboard()
    {
        return Inertia::render('Dashboard/WAR/Employee/index', [
            'user' => auth()->user(),
            'bins' => [],
            'stats' => [
                'totalBins' => 0,
                'occupiedBins' => 0,
                'availableBins' => 0,
            ],
        ]);
    }
}
