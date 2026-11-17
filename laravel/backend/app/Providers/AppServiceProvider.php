<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the admin middleware aliases (admin.auth, admin.guest,
        // admin.2fa, admin.throttle) that routes/admin.php depends on.
        $this->app['router']->aliasMiddleware('admin.auth', Middleware\AdminAuth::class);
        $this->app['router']->aliasMiddleware('admin.guest', Middleware\AdminGuest::class);
        $this->app['router']->aliasMiddleware('admin.2fa', Middleware\AdminTwoFactor::class);
        $this->app['router']->aliasMiddleware('admin.throttle', Middleware\AdminThrottleLogin::class);

        // The admin-login rate limiter used by the admin login route.
        RateLimiter::for('admin-login', function () {
            return Limit::perMinute(5)->by($this->app['request']->email ?: 'admin');
        });
    }
}
