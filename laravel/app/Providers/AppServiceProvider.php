<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\EmailLog;
use App\Models\Complaint;
use App\Models\House;
use App\Models\MaintenanceRecord;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\Renter;
use App\Policies\BillPolicy;
use App\Policies\CaretakerPolicy;
use App\Policies\EmailLogPolicy;
use App\Policies\ComplaintPolicy;
use App\Policies\HousePolicy;
use App\Policies\MaintenanceRecordPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PropertyDocumentPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\RenterPolicy;
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
