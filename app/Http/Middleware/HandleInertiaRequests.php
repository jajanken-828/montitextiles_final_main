<?php

namespace App\Http\Middleware;

use App\Models\PagePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy; // Import the PagePermission model

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Get the authenticated user for the default guard (web)
        $user = $request->user();

        // If a user is logged in, fetch their page permissions grouped by module
        $userData = null;
        if ($user) {
            $permissions = PagePermission::where('user_id', $user->id)
                ->get()
                ->groupBy('module')
                ->map(fn ($perms) => $perms->pluck('page'));

            // Merge the original user attributes with the permissions array
            $userData = array_merge($user->toArray(), [
                'permissions' => $permissions,
            ]);
        }

        return [
            ...parent::share($request),
            'auth' => [
                // Enhanced user object containing permissions
                'user' => $userData,

                // B2B Clients - Safely retrieved if guard exists
                'client' => $this->getGuardUser('client'),

                // Supplier Guard - Safely retrieved if guard exists
                'supplier' => $this->getGuardUser('supplier'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            // Flash messages for login/registration alerts
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }

    /**
     * Safely get the user for a specific guard to prevent
     * InvalidArgumentException if the guard is not yet defined.
     */
    protected function getGuardUser(string $guard)
    {
        try {
            return Auth::guard($guard)->user();
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }
}
