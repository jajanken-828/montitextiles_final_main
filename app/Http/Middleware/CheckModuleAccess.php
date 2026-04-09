<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // CEO has access to everything
        if ($user->role === 'CEO') {
            return $next($request);
        }

        // Secretary or General Manager: check granted_modules
        if (in_array($user->position, ['secretary', 'general_manager'])) {
            $granted = $user->moduleAccess->pluck('module')->toArray();
            if (in_array($module, $granted)) {
                return $next($request);
            }
            abort(403, "You don't have access to the {$module} module.");
        }

        // Manufacturing Supervisor: also check granted_modules
        if ($user->is_manufacturing_supervisor) {
            $granted = $user->moduleAccess->pluck('module')->toArray();
            if (in_array($module, $granted)) {
                return $next($request);
            }
            abort(403, "You don't have access to the {$module} module.");
        }

        // Regular manager: role must match module name
        if ($user->role === $module && $user->position === 'manager') {
            return $next($request);
        }

        abort(403, "You don't have access to the {$module} module.");
    }
}