<?php

namespace App\Http\Controllers\pro;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ProDashboardController extends Controller
{
    public function managerDashboard()
    {
        return Redirect::route('pro.manager.material-requests');
    }

    public function staffDashboard()
    {
        return Inertia::render('Dashboard/PRO/Employee/index', [
            'user' => auth()->user(),
            'stats' => [
                'activeBids' => 0,
                'pendingContracts' => 0,
                'supplierIssues' => 0,
            ],
        ]);
    }
}
