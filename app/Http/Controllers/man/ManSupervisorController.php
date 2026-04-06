<?php

namespace App\Http\Controllers\man;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ManSupervisorController extends Controller
{
    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $user = Auth::user();
        $role = $request->role;

        if (! $user->isManufacturingSupervisor()) {
            return back()->with('error', 'Only supervisors can switch roles.');
        }

        if (! in_array($role, $user->getAssignedManufacturingRoles())) {
            return back()->with('error', "Role {$role} is not assigned to you.");
        }

        Session::put('active_manufacturing_role', $role);

        // Redirect to the appropriate dashboard for that role
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
            return redirect()->route($routes[$role]);
        }

        return redirect()->route('man.employee.dashboard');
    }
}
