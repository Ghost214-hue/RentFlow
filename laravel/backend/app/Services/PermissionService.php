<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Resolves abilities from the permissions matrix plus owner-granted
 * caretaker_permissions overrides.
 *
 * The server is the real gate: every authorization decision funnels through
 * this service (directly or via policies). Frontend guards are UX only.
 */
final class PermissionService
{
    /**
     * Check whether an actor has an ability.
     *
     * @param  object  $actor  Owner | Caretaker | Renter | Admin
     * @param  string  $ability  e.g. 'payments.record', 'bills.generate'
     * @return bool
     */
    public function allows(object $actor, string $ability): bool
    {
        $config = config('permissions', []);

        // Admin with wildcard bypasses everything.
        if ($actor instanceof \App\Models\Admin) {
            return $this->hasAbility($config, 'admin', $ability);
        }

        if ($actor instanceof Owner) {
            return $this->hasAbility($config, 'owner', $ability);
        }

        if ($actor instanceof Caretaker) {
            return $this->caretakerAllows($actor, $ability, $config);
        }

        // Renter / tenant
        return $this->hasAbility($config, 'tenant', $ability);
    }

    /**
     * List all abilities granted to an actor.
     *
     * @return list<string>
     */
    public function abilitiesFor(object $actor): array
    {
        $config = config('permissions', []);

        if ($actor instanceof Owner) {
            return $this->roleAbilities($config, 'owner');
        }

        if ($actor instanceof Caretaker) {
            return $this->caretakerAbilities($actor, $config);
        }

        if ($actor instanceof \App\Models\Admin) {
            return $this->roleAbilities($config, 'admin');
        }

        return $this->roleAbilities($config, 'tenant');
    }

    private function caretakerAllows(Caretaker $caretaker, string $ability, array $config): bool
    {
        // Check owner-granted override first.
        $override = DB::table('caretaker_permissions')
            ->where('caretaker_id', $caretaker->getKey())
            ->where('ability', $ability)
            ->where('granted', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($override !== null) {
            return (bool) $override->granted;
        }

        // Fall back to role default.
        return $this->hasAbility($config, 'caretaker', $ability);
    }

    private function caretakerAbilities(Caretaker $caretaker, array $config): array
    {
        $abilities = $this->roleAbilities($config, 'caretaker');

        $overrides = DB::table('caretaker_permissions')
            ->where('caretaker_id', $caretaker->getKey())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();

        foreach ($overrides as $o) {
            if ((bool) $o->granted && ! in_array($o->ability, $abilities, true)) {
                $abilities[] = $o->ability;
            } elseif (! (bool) $o->granted) {
                $abilities = array_values(array_filter($abilities, fn ($a) => $a !== $o->ability));
            }
        }

        return $abilities;
    }

    private function roleAbilities(array $config, string $role): array
    {
        $defaults = $config['role_defaults'][$role] ?? [];

        // Inherit from lower roles in hierarchy.
        $hierarchy = $config['hierarchy'] ?? [];
        $inherited = [];
        foreach ($hierarchy[$role] ?? [] as $lower) {
            $inherited = array_merge($inherited, $this->roleAbilities($config, $lower));
        }

        return array_unique(array_merge($defaults, $inherited));
    }

    private function hasAbility(array $config, string $role, string $ability): bool
    {
        $abilities = $config['role_defaults'][$role] ?? [];

        // Admin wildcard.
        if ($role === 'admin' && in_array('*', $abilities, true)) {
            return true;
        }

        return in_array($ability, $abilities, true);
    }
}
