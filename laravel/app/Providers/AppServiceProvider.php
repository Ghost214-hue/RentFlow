<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\House;
use App\Models\Property;
use App\Policies\HousePolicy;
use App\Policies\PropertyPolicy;
use App\Support\TenantContext;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Explicit policy mapping. Nothing is inferred, and nothing grants
        // access by default — an unmapped model denies.
        Gate::policy(House::class, HousePolicy::class);
        Gate::policy(Property::class, PropertyPolicy::class);

        // The legacy UI uses Tailwind-style pagination links.
        Paginator::useTailwind();

        // Long-lived workers (queue/scheduler) must never carry one request's
        // owner into the next. Cleared between jobs.
        if ($this->app->runningInConsole()) {
            \Illuminate\Support\Facades\Event::listen(
                \Illuminate\Queue\Events\JobProcessed::class,
                fn () => TenantContext::clear(),
            );

            \Illuminate\Support\Facades\Event::listen(
                \Illuminate\Console\Events\CommandFinished::class,
                fn () => TenantContext::clear(),
            );
        }
    }
}
