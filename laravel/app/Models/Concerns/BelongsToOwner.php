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
    /**
     * Named key for the owner scope.
     *
     * The scope MUST be registered under an explicit name. An anonymous
     * closure cannot be removed later: withoutGlobalScope(static::class) looks
     * up by identifier, finds nothing, and silently leaves the scope in place,
     * so withoutOwnerScope() would throw instead of bypassing.
     */
    private const OWNER_SCOPE = 'rentflow_owner_scope';

    public static function bootBelongsToOwner(): void
    {
        static::addGlobalScope(self::OWNER_SCOPE, static function (Builder $query): void {
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
     * Intended for the auth/tenancy bootstrap, CLI jobs, seeders and aggregate
     * reports. Two callers legitimately need it:
     *
     *  - ResolveTenantContext, which reads the actor OUT of the session in
     *    order to derive the owner context. It cannot use the scope, because
     *    the scope is what depends on that context. Safe because the id comes
     *    from the integrity-protected session, never from user input, and the
     *    context derived from it scopes everything after.
     *  - Authenticator::findByEmail, which matches an email before any owner is
     *    known. The password check and the resulting session are what bind the
     *    login to one owner.
     *
     * Never use it to serve a request.
     */
    public static function withoutOwnerScope(): Builder
    {
        return static::query()->withoutGlobalScope(self::OWNER_SCOPE);
    }
}