<?php

namespace App\Http\Controllers\man;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class ManDashboardController extends Controller
{
    public function staffDashboard()
    {
        $user = auth()->user();

        // Check if user is a manufacturing supervisor
        if ($user->is_manufacturing_supervisor) {
            $assignedRoles = $user->getAssignedManufacturingRoles();
            $activeRole = Session::get('active_manufacturing_role');

            // If no active role is set, show role selection page
            if (!$activeRole || !in_array($activeRole, $assignedRoles)) {
                return Inertia::render('Dashboard/MAN/Supervisor/RoleSelection', [
                    'assignedRoles' => $assignedRoles,
                    'activeRole' => $activeRole,
                ]);
            }

            // Active role is set, redirect to that role's dashboard
            $routes = [
                'knitting_yarn' => 'man.staff.knitting-yarn.dashboard',
                'dyeing_color' => 'man.staff.dyeing-color.dashboard',
                'dyeing_fabric_softener' => 'man.staff.dyeing-fabric-softener.dashboard',
                'dyeing_squeezer' => 'man.staff.dyeing-squeezer.dashboard',
                'dyeing_ironing' => 'man.staff.dyeing-ironing.dashboard',
                'dyeing_forming' => 'man.staff.dyeing-forming.dashboard',
                'dyeing_packaging' => 'man.staff.dyeing-packaging.dashboard',
                'maintenance_checker' => 'man.staff.maintenance-checker.dashboard',
                'checker_quality' => 'man.staff.checker-quality.dashboard',
            ];

            if (isset($routes[$activeRole])) {
                return Redirect::route($routes[$activeRole]);
            }
        }

        // Normal staff (single role)
        $role = $user->manufacturing_role;

        $routes = [
            'knitting_yarn' => 'man.staff.knitting-yarn.dashboard',
            'dyeing_color' => 'man.staff.dyeing-color.dashboard',
            'dyeing_fabric_softener' => 'man.staff.dyeing-fabric-softener.dashboard',
            'dyeing_squeezer' => 'man.staff.dyeing-squeezer.dashboard',
            'dyeing_ironing' => 'man.staff.dyeing-ironing.dashboard',
            'dyeing_forming' => 'man.staff.dyeing-forming.dashboard',
            'dyeing_packaging' => 'man.staff.dyeing-packaging.dashboard',
            'maintenance_checker' => 'man.staff.maintenance-checker.dashboard',
            'checker_quality' => 'man.staff.checker-quality.dashboard',
        ];

        if (isset($routes[$role])) {
            return Redirect::route($routes[$role]);
        }

        return Inertia::render('Dashboard/MAN/Employee/Index');
    }
}