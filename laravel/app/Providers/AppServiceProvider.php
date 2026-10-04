<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\Complaint;
use App\Models\EmailLog;
use App\Models\House;
use App\Models\MaintenanceRecord;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\Renter;
use App\Policies\BillPolicy;
use App\Policies\CaretakerPolicy;
use App\Policies\ComplaintPolicy;
use App\Policies\EmailLogPolicy;
use App\Policies\HousePolicy;
use App\Policies\MaintenanceRecordPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PropertyDocumentPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\RenterPolicy;
use App\Reports\ReportScope;
use App\Support\TenantContext;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         * Reports need to know WHO is asking, not just which owner owns the
         * rows: a caretaker is scoped to their assigned properties everywhere
         * else in the app, and a report that ignored that would widen their
         * reach to the owner's whole portfolio.
         *
         * Bound as scoped(), NOT singleton(). A plain singleton survives across
         * requests in a long-running worker and hands one user's scope to
         * the next -- exactly the cross-tenant leak the owner scope exists
         * to prevent. scoped() is flushed at the request boundary, and each
         * report class keeps its own memoised lookups.
         */
        $this->app->scoped(
            ReportScope::class,
            static fn (): ReportScope => ReportScope::for(request()->user()),
        );
    }

    public function boot(): void
    {
        // Explicit policy mapping. Nothing is inferred, and nothing grants
        // access by default — an unmapped model denies.
        Gate::policy(House::class, HousePolicy::class);
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Renter::class, RenterPolicy::class);
        Gate::policy(Bill::class, BillPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::policy(Caretaker::class, CaretakerPolicy::class);
        Gate::policy(MaintenanceRecord::class, MaintenanceRecordPolicy::class);
        Gate::policy(PropertyDocument::class, PropertyDocumentPolicy::class);
        Gate::policy(EmailLog::class, EmailLogPolicy::class);

        // The legacy UI uses Tailwind-style pagination links.
        Paginator::useTailwind();

        // Long-lived workers (queue/scheduler) must never carry one request's
        // owner into the next. Cleared between jobs.
        if ($this->app->runningInConsole()) {
            Event::listen(
                JobProcessed::class,
                fn () => TenantContext::clear(),
            );

            Event::listen(
                CommandFinished::class,
                fn () => TenantContext::clear(),
            );
        }
    }
}
