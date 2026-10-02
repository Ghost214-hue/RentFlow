<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Owner;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Multitenancy for RentFlow: shared database, shared schema, `owner_id` on
 * almost every table. Each owner is one tenant of the SaaS.
 *
 * This trait installs a global scope that restricts every query to the
 * authenticated owner's rows, and stamps `owner_id` on create. Two owners can
 * therefore never read, update or delete each other's data through normal
 * Eloquent usage — a cross-tenant lookup returns 404, not a row.
 *
 * It FAILS CLOSED: with no resolved owner context, queries throw rather than
 * silently returning every tenant's rows. (The legacy Router::getAuthRole()
 * defaulted to 'owner', which is fail-open and is why this is deliberate.)
 *
 * For CLI jobs that legitimately span owners, call withoutOwnerScope() and say
 * why in a comment — it is never the default.
 *
 * @phpstan-require-extends Model
 */
trait BelongsToOwner
{
    public static function bootBelongsToOwner(): void
    {
        static::addGlobalScope(static function (Builder $query): void {
            $ownerId = TenantContext::id();

            if ($ownerId === null) {
                throw new RuntimeException(sprintf(
                    'No owner context resolved for %s. Refusing to run an unscoped query. '
                    .'Use withoutOwnerScope() only where cross-owner access is genuinely intended.',
                    static::class,
                ));
            }

            $query->where($query->getModel()->getTable().'.owner_id', $ownerId);
        });

        static::creating(static function (Model $model): void {
            $ownerId = TenantContext::id();

            if ($ownerId === null) {
                throw new RuntimeException(sprintf(
                    'No owner context resolved; refusing to create %s without an owner_id.',
                    static::class,
                ));
            }

            // Never let a request-supplied owner_id win over the session's.
            $model->setAttribute('owner_id', $ownerId);
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    /**
     * Run a query across every owner.
     *
     * Usage: Property::withoutOwnerScope()->where(...)->get();
     *
     * Intended for CLI jobs, seeders and aggregate reports only — always leave a
     * comment justifying the scope bypass. Never use it to serve a request.
     */
    public static function withoutOwnerScope(): Builder
    {
        return static::query()->withoutGlobalScope(static::class);
    }
}