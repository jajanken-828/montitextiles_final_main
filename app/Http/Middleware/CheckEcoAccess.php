<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\EcoAccess;

class CheckEcoAccess
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if ($user->role === 'CEO') {
            return $next($request);
        }
        $access = EcoAccess::where('user_id', $user->id)->where('can_access_eco', true)->first();
        if (!$access) {
            abort(403, 'You are not authorized to access the ECO module.');
        }
        return $next($request);
    }
}