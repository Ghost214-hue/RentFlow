<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\PermissionService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on the first page visit.
     */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props available on every page.
     *
     * Typed per page via generated types; this is only the shared contract.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $ownerId = TenantContext::id();

        return [
            ...parent::share($request),

            'auth' => [
                // null when unauthenticated; never assume a default role.
                'user' => $user === null ? null : [
                    'id' => (int) $user->getAuthIdentifier(),
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role()->value,
                    'roleLabel' => $user->role()->label(),
                ],
                'ownerId' => $ownerId,
            ],

            /*
             * Abilities the actor holds, used by AppLayout to hide nav entries
             * and by pages to hide actions they cannot perform.
             *
             * Resolved lazily so a page that never reads it costs nothing.
             * Returns [] when PermissionService does not expose abilitiesFor()
             * yet, which makes the frontend fall back to the full role nav
             * rather than blanking the sidebar.
             */
            'abilities' => function () use ($user): array {
                if ($user === null) {
                    return [];
                }

                $service = app(PermissionService::class);

                return method_exists($service, 'abilitiesFor')
                    ? $service->abilitiesFor($user)
                    : [];
            },

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
