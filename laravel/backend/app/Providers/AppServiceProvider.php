<?php

declare(strict_types=1);

namespace App\Providers;

use App\Reports\ReportScope;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(ReportScope::class, function ($app) {
            return ReportScope::for($app['request']->user());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Admin middleware aliases (admin.auth, admin.guest, admin.2fa,
         * admin.throttle) are registered in bootstrap/app.php via
         * $middleware->alias(). They are NOT registered here: doing it in both
         * places is redundant and the bootstrap registration is the one
         * Laravel resolves first.
         */

        // Rate limiters for all throttle:* middleware used in routes/web.php
        // and routes/admin.php.
        RateLimiter::for('login', function () {
            return Limit::perMinute(5)->by(Request::ip());
        });
        RateLimiter::for('password-reset', function () {
            return Limit::perMinute(3)->by(Request::ip());
        });
        RateLimiter::for('register', function () {
            return Limit::perMinute(3)->by(Request::ip());
        });
        RateLimiter::for('setup', function () {
            return Limit::perMinute(3)->by(Request::ip());
        });
        RateLimiter::for('mail', function () {
            return Limit::perMinute(10)->by(Request::ip());
        });
        RateLimiter::for('writes', function () {
            return Limit::perMinute(30)->by(Request::ip());
        });
        RateLimiter::for('reads', function () {
            return Limit::perMinute(60)->by(Request::ip());
        });
        RateLimiter::for('session', function () {
            return Limit::perMinute(120)->by(Request::ip());
        });
        RateLimiter::for('api', function () {
            return Limit::perMinute(60)->by(Request::ip());
        });

        // The admin-login rate limiter used by the admin login route.
        RateLimiter::for('admin-login', function () {
            return Limit::perMinute(5)->by($this->app['request']->email ?: 'admin');
        });
    }
}
