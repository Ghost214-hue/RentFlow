<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve the owner tenancy context as early as possible.
 *
 * This runs PREPENDED to the web group, ahead of SubstituteBindings, because
 * every owner-scoped model throws when no context is resolved. Route-model
 * binding happens before the route's own middleware, so resolving the actor
 * here is what allows a parameterised route such as
 * POST /complaints/{complaint}/advance to bind at all.
 *
 * It also handles session rehydration for the two actor types the session
 * guard cannot resolve on its own: the guard's provider is Owner, so a
 * Caretaker or Renter session would otherwise come back as null.
 *
 * It never blocks the request — unauthenticated traffic continues and is
 * refused later by the route's authentication middleware.
 */
class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // Already resolved this request (e.g. a test set it explicitly).
        if (TenantContext::isResolved()) {
            return $next($request);
        }

        $user = $request->user();

        // A session-backed Owner resolves through the default guard.
        if ($user instanceof Owner) {
            TenantContext::set((int) $user->getKey());

            return $next($request);
        }

        if ($user instanceof Caretaker || $user instanceof Renter) {
            TenantContext::set(TenantContext::resolveForActor($user));

            return $next($request);
        }

        /*
         * No user yet. The session records which table the actor lives in,
         * because the guard provider is Owner and cannot load a Caretaker or
         * Renter on its own.
         */
        if (! $request->hasSession()) {
            // No session on this route (e.g. a stateless entry point). There is
            // no actor to resolve and nothing downstream should assume one.
            return $next($request);
        }

        $actorType = $request->session()->get('rf_actor_type');
        $actorId = $request->session()->get('rf_actor_id');

        if (is_string($actorType) && is_numeric($actorId)) {
            $model = match ($actorType) {
                'caretaker' => Caretaker::class,
                'renter' => Renter::class,
                default => Owner::class,
            };

            /*
             * The lookup MUST bypass the owner scope. Resolving the context is
             * what this query is for, so applying a scope that depends on the
             * context would throw "no owner context" and deadlock.
             *
             * This is safe: the id comes from the integrity-protected session
             * that AuthenticatedSessionController wrote at login, not from user
             * input. The owner context derived from it then scopes everything
             * downstream, so no subsequent query can escape it.
             */
            $actor = $model::withoutOwnerScope()->find((int) $actorId);

            if ($actor !== null) {
                Auth::setUser($actor);
                $request->setUserResolver(static fn () => $actor);
                TenantContext::set(TenantContext::resolveForActor($actor));
            }
        }

        return $next($request);
    }
}