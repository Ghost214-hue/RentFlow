<?php

declare(strict_types=1);

namespace App\Http\Middleware;

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

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}