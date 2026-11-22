<?php

declare(strict_types=1);

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminGuest;
use App\Http\Middleware\AdminThrottleLogin;
use App\Http\Middleware\AdminTwoFactor;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveTenantBeforeBindings;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (Application $app): void {
            // Admin routes are loaded here so /admin/* is never 404.
            //
            // The `web` group is applied HERE and not again inside
            // routes/admin.php: Laravel does not de-duplicate gathered
            // middleware, so declaring `web` in both places runs StartSession
            // twice and corrupts the session.
            $app->router->middleware('web')->group(__DIR__ . '/../routes/admin.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(
            replace: [
                // NOTE: this was previously Illuminate\Payment\Middleware\...
                // which does not exist; the correct namespace is Routing.
                SubstituteBindings::class => ResolveTenantBeforeBindings::class,
            ],
            append: [
                HandleInertiaRequests::class,
                // Appended LAST so headers land on whatever the response turned
                // out to be, including a rendered error page.
                SecurityHeaders::class,
                // CORS: allows the Vite dev server (port 5173) and any configured
                // frontend origin to call the Laravel API without being blocked.
                HandleCors::class,
            ],
        );

        // Admin middleware aliases used by routes/admin.php.
        $middleware->alias([
            'admin.auth' => AdminAuth::class,
            'admin.guest' => AdminGuest::class,
            'admin.2fa' => AdminTwoFactor::class,
            'admin.throttle' => AdminThrottleLogin::class,
            'admin.role' => \App\Http\Middleware\AdminRole::class,
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

            if ($request->is('admin') || $request->is('admin/*')) {
                if ($request->is('admin/login')) {
                    return null;
                }

                $request->session()->put('url.intended', $request->fullUrl());

                return redirect()->route('admin.login');
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
