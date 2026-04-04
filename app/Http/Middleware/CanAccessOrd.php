<?php

namespace App\Http\Middleware;

use App\Models\OrdAccess;
use Closure;
use Illuminate\Http\Request;

class CanAccessOrd
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user->role === 'CEO') {
            return $next($request);
        }
        $hasAccess = OrdAccess::where('user_id', $user->id)->value('can_access_ord') ?? false;
        if (! $hasAccess) {
            abort(403, 'You do not have permission to access the Order Management module.');
        }

        return $next($request);
    }
}
