<?php

namespace App\Http\Controllers\hrm;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Strict Rank Engine
     */
    private function getRank($user)
    {
        if (strtoupper($user->role) === 'CEO') {
            return 60;
        }

        $pos = strtolower($user->position);
        if ($pos === 'secretary') {
            return 50;
        }
        if ($pos === 'general_manager') {
            return 40;
        }
        if ($pos === 'manager') {
            return 30;
        }
        if ($pos === 'supervisor') {
            return 20;
        }
        if ($pos === 'staff') {
            return 10;
        }

        return 0; // Trainees, Onboarding
    }

    private function getPosRank($position)
    {
        $pos = strtolower($position);
        if ($pos === 'ceo') {
            return 60;
        }
        if ($pos === 'secretary') {
            return 50;
        }
        if ($pos === 'general_manager') {
            return 40;
        }
        if ($pos === 'manager') {
            return 30;
        }
        if ($pos === 'supervisor') {
            return 20;
        }
        if ($pos === 'staff') {
            return 10;
        }

        return 0;
    }

    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        // STRICT ACCESS CONTROL: Only Managers (30) and above can access this page
        if ($this->getRank($currentUser) < 30) {
            abort(403, 'Unauthorized access. Only Managers and higher can view Employee Management.');
        }

        $employees = User::with(['auditLogs' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }])->whereIn('position', ['staff', 'supervisor', 'manager', 'general_manager', 'secretary'])
            ->orderBy('role')
            ->orderBy('position')
            ->orderBy('name')
            ->get()
            ->map(function ($emp) {
                return $this->formatEmployee($emp);
            });

        return Inertia::render('Dashboard/HRM/Employee', ['employees' => $employees]);
    }

    public function show($id)
    {
        $employee = User::with('auditLogs')->findOrFail($id);

        return response()->json($this->formatEmployee($employee));
    }

    /**
     * Update employee information.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required|in:HRM,SCM,FIN,MAN,INV,ORD,WAR,CRM,ECO,PRO,PROJ,IT,CEO',
            'position' => 'required|in:staff,supervisor,manager,general_manager,secretary',
            'is_active' => 'required|boolean',
        ]);

        $currentUser = Auth::user();
        $employee = User::findOrFail($id);

        // --- ENFORCE COMPANY HIERARCHY ---
        if ($this->getRank($currentUser) <= $this->getRank($employee)) {
            return back()->with('error', 'You do not have authority to modify an employee of equal or higher rank.');
        }
        if ($this->getRank($currentUser) <= $this->getPosRank($request->position)) {
            return back()->with('error', 'Authority Denied: You cannot promote someone to a rank equal to or higher than your own.');
        }
        if ($request->role === 'CEO' && strtoupper($currentUser->role) !== 'CEO') {
            return back()->with('error', 'Only the CEO can assign the CEO role.');
        }

        $employee->update($request->only('name', 'email', 'role', 'position', 'is_active'));

        return redirect()->back()->with('message', 'Employee updated successfully.');
    }

    /**
     * Toggle employee account status (activate/deactivate).
     */
    public function toggleStatus(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);

        $currentUser = Auth::user();
        $employee = User::findOrFail($id);

        if ($currentUser->id == $employee->id) {
            return back()->with('error', 'Cannot modify own account status.');
        }

        // Hierarchy Check
        if ($this->getRank($currentUser) <= $this->getRank($employee)) {
            return back()->with('error', 'You do not have authority to deactivate an employee of equal or higher rank.');
        }

        $newStatus = ! $employee->is_active;
        $action = $newStatus ? 'reactivate' : 'deactivate';

        $employee->update(['is_active' => $newStatus]);

        AuditLog::create([
            'admin_id' => $currentUser->id,
            'target_id' => $employee->id,
            'target_name' => $employee->name,
            'action' => $action,
            'reason' => $request->reason,
        ]);

        return back()->with('message', "Employee {$employee->name} has been {$action}d.");
    }

    /**
     * Update employee's role and position.
     */
    public function updateRolePosition(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:HRM,SCM,FIN,MAN,INV,ORD,WAR,CRM,ECO,PRO,PROJ,IT,CEO',
            'position' => 'required|in:staff,supervisor,manager,general_manager,secretary',
        ]);

        $currentUser = Auth::user();
        $employee = User::findOrFail($id);

        // --- ENFORCE COMPANY HIERARCHY ---
        if ($this->getRank($currentUser) <= $this->getRank($employee)) {
            return back()->with('error', 'You do not have authority to modify an employee of equal or higher rank.');
        }
        if ($this->getRank($currentUser) <= $this->getPosRank($request->position)) {
            return back()->with('error', 'Authority Denied: You cannot promote someone to a rank equal to or higher than your own.');
        }
        if ($request->role === 'CEO' && strtoupper($currentUser->role) !== 'CEO') {
            return back()->with('error', 'Only the CEO can assign the CEO role.');
        }

        $oldRole = $employee->role;
        $oldPosition = $employee->position;

        $employee->update([
            'role' => $request->role,
            'position' => $request->position,
        ]);

        if ($request->role !== 'MAN' && $employee->manufacturing_role) {
            $employee->update(['manufacturing_role' => null]);
        }

        AuditLog::create([
            'admin_id' => $currentUser->id,
            'target_id' => $employee->id,
            'target_name' => $employee->name,
            'action' => 'role_position_change',
            'reason' => "Role changed from {$oldRole} to {$request->role}, Position from {$oldPosition} to {$request->position}",
        ]);

        return back()->with('message', 'Employee role and position updated successfully.');
    }

    private function formatEmployee($employee)
    {
        return [
            'id' => $employee->id,
            'name' => $employee->name,
            'email' => $employee->email,
            'role' => $employee->role,
            'position' => $employee->position,
            'is_active' => (bool) $employee->is_active,
            'join_date' => $employee->join_date,
            'audit_logs' => $employee->auditLogs,
            'profile_photo_url' => $employee->profile_photo_path ? asset('storage/'.$employee->profile_photo_path) : null,
        ];
    }
}
