<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureManufacturingRole
{
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'MAN') {
            abort(403, 'Not a manufacturing staff.');
        }

        // Staff with single role
        if (! $user->isManufacturingSupervisor()) {
            if ($user->manufacturing_role !== $requiredRole) {
                abort(403, "You don't have permission for {$requiredRole}.");
            }

            return $next($request);
        }

        // Supervisor: check active session role
        $activeRole = Session::get('active_manufacturing_role');
        if (! $activeRole || $activeRole !== $requiredRole) {
            abort(403, 'You are not acting as '.str_replace('_', ' ', $requiredRole).'. Please switch role.');
        }

        // Also verify the role is actually assigned to this supervisor
        if (! in_array($requiredRole, $user->getAssignedManufacturingRoles())) {
            abort(403, "Role {$requiredRole} is not assigned to you by the manager.");
        }

        return $next($request);
    }
}
