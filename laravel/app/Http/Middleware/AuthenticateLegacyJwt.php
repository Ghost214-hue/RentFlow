<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\LegacyJwt;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentication bridge for the coexistence period.
 *
 * The legacy app issues an HS256 JWT in the `rf_token` cookie (with
 * localStorage as a client-side fallback). During migration both apps share
 * one JWT_SECRET, so this middleware validates that cookie and establishes an
 * equivalent Laravel session — a user signed in to either app is signed in to
 * both.
 *
 * REMOVE AT CUTOVER. This is scaffolding, not a permanent auth strategy.
 *
 * Fail-closed by design: a missing, malformed or expired token leaves the
 * request unauthenticated. It never falls back to a default role.
 */
class AuthenticateLegacyJwt
{
    public function handle(Request $request, Closure $next): Response
    {
        // Already authenticated through normal Laravel session auth, or the
        // actor was rehydrated by ResolveTenantContext which runs earlier.
        if ($request->user() !== null) {
            TenantContext::set($this->ownerIdFor($request->user()));

            return $next($request);
        }

        $token = $request->cookie('rf_token');

        if (! is_string($token) || $token === '') {
            return $this->unauthenticated($request);
        }

        $payload = LegacyJwt::decode($token);

        if ($payload === null) {
            return $this->unauthenticated($request);
        }

        $actor = LegacyJwt::resolveActor($payload);

        if ($actor === null) {
            return $this->unauthenticated($request);
        }

        // Establish the Laravel session so subsequent requests are cookie-auth.
        Auth::setUser($actor);
        $request->setUserResolver(static fn () => $actor);

        // The whole tenancy axis, for every role, is the owner.
        TenantContext::set(TenantContext::resolveForActor($actor));

        return $next($request);
    }

    private function ownerIdFor(mixed $actor): ?int
    {
        if ($actor instanceof \App\Models\Owner
            || $actor instanceof \App\Models\Caretaker
            || $actor instanceof \App\Models\Renter) {
            return TenantContext::resolveForActor($actor);
        }

        return null;
    }

    /**
     * Refuse an unauthenticated request.
     *
     * A BROWSER navigation is sent to the login page rather than shown a bare
     * "401 Unauthorized" error page: opening a bookmarked URL, refreshing a tab
     * or pasting a link should look like a request to sign in, not a broken
     * application. The wanted URL is remembered so sign-in returns the user
     * there instead of dumping them on the dashboard.
     *
     * A JSON or API request keeps a real 401 so the client can handle it.
     */
    private function unauthenticated(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(401, 'Unauthenticated.');
        }

        // Avoid a redirect loop if /login ever sits behind this guard.
        if ($request->is('login')) {
            abort(401, 'Unauthenticated.');
        }

        $request->session()->put('url.intended', $request->fullUrl());

        return redirect()->route('login');
    }
}