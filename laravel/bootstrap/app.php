<?php

use App\Http\Middleware\AuthenticateLegacyJwt;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveTenantBeforeBindings;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Tenancy context must be resolved BEFORE route-model binding.
         *
         * SubstituteBindings resolves a {model} parameter before the route's
         * own middleware (and therefore before authentication) runs. Every
         * owner-scoped model throws when no context exists, so a route such as
         * POST /complaints/{complaint}/advance would 500 during binding.
         *
         * Laravel's web(replace:) substitutes one middleware for another
         * and expects a single class name, not a list. ResolveTenantContext is
         * therefore paired with SubstituteBindings inside one composite
         * middleware that runs in SubstituteBindings' original slot:
         *
         *     ... StartSession -> ResolveTenantBeforeBindings -> (bindings)
         *
         * The composite sets the tenancy context and then delegates straight
         * to SubstituteBindings, which preserves the required ordering without
         * restating Laravel's default group.
         */
        $middleware->web(
            replace: [
                SubstituteBindings::class => ResolveTenantBeforeBindings::class,
            ],
            append: [
                HandleInertiaRequests::class,
                // Appended LAST so headers land on whatever the response turned
                // out to be, including a rendered error page.
                SecurityHeaders::class,
            ],
        );

        // During coexistence the legacy app authenticates with an HS256 JWT
        // cookie. The bridge guard turns that into a Laravel session so a user
        // logged into either app is logged into both. Removed at cutover.
        $middleware->alias([
            'legacy.jwt' => AuthenticateLegacyJwt::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        /*
         * Send a guest to the login page instead of showing a raw 401.
         *
         * Without this, opening any protected URL directly (a bookmark, a
         * refreshed tab, a pasted link) returns a bare "401 Unauthorized"
         * error page, which looks like a broken application rather than a
         * request to sign in.
         *
         * An EXPIRED session lands here too, which is why the intended URL is
         * flashed: after signing in the user returns to the page they wanted
         * instead of the dashboard.
         *
         * Only browser navigations are redirected. An XHR or JSON request still
         * receives a real 401 so the client can handle it.
         */
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            // Opening the login page while already logged in should go home,
            // not bounce back to a loop.
            if ($request->is('login')) {
                return null;
            }

            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('login');
        });
    })->create();
