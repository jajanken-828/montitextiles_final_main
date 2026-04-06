<?php

namespace App\Http\Controllers\man;

use App\Http\Controllers\Controller;
use App\Models\ManufacturingSupervisorRole;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManAccessController extends Controller
{
    public function index()
    {
        $staff = User::where('role', 'MAN')
            ->where('position', 'staff')
            ->with('supervisorRoles')
            ->get(['id', 'name', 'email', 'manufacturing_role', 'is_manufacturing_supervisor'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'manufacturing_role' => $user->manufacturing_role,
                    'is_manufacturing_supervisor' => $user->is_manufacturing_supervisor,
                    'supervisor_roles' => $user->supervisorRoles,
                ];
            });

        $allRoles = [
            'knitting_yarn', 'dyeing_color', 'dyeing_fabric_softener',
            'dyeing_squeezer', 'dyeing_ironing', 'dyeing_forming',
            'dyeing_packaging', 'maintenance_checker', 'checker_quality',
        ];

        return Inertia::render('Dashboard/MAN/Manager/Access', [
            'staff' => $staff,
            'allRoles' => $allRoles,
        ]);
    }

    public function assignSupervisor(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_supervisor' => 'required|boolean',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->is_manufacturing_supervisor = $request->is_supervisor;
        $user->save();

        if (! $request->is_supervisor) {
            ManufacturingSupervisorRole::where('user_id', $user->id)->delete();
            session()->forget('active_manufacturing_role');
        }

        return back()->with('message', 'Supervisor status updated.');
    }

    public function updateSupervisorRoles(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles' => 'array',
            'roles.*' => 'string|in:knitting_yarn,dyeing_color,dyeing_fabric_softener,dyeing_squeezer,dyeing_ironing,dyeing_forming,dyeing_packaging,maintenance_checker,checker_quality',
        ]);

        $user = User::findOrFail($request->user_id);

        if (! $user->is_manufacturing_supervisor) {
            return back()->with('error', 'User is not a supervisor. Promote first.');
        }

        ManufacturingSupervisorRole::where('user_id', $user->id)->delete();
        foreach ($request->roles as $role) {
            ManufacturingSupervisorRole::create([
                'user_id' => $user->id,
                'manufacturing_role' => $role,
            ]);
        }

        return back()->with('message', 'Supervisor roles updated.');
    }
}