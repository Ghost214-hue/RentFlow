<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\BillStatus;
use App\Mail\Mailer;
use App\Mail\MailTemplate;
use App\Mail\PortfolioMailer;
use App\Models\Bill;
use App\Models\Renter;
use App\Models\RentReminder;
use App\Support\Amount;
use App\Support\TenantContext;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Chases unpaid rent before and on the due date.
 *
 * Runs across EVERY owner, so it resolves each tenancy explicitly rather than
 * relying on an ambient context -- a scheduled command has no authenticated
 * actor, and the owner scope fails closed when none is set.
 *
 * IDEMPOTENT BY DESIGN. This command is meant to be run daily (or hourly).
 * `rent_reminders` records (bill_id, days_before_due), so each lead time fires
 * at most once per bill. Without that, "run every day" would send a renter a
 * reminder every single day until they paid.
 *
 *     php artisan rent:remind                 both lead times
 *     php artisan rent:remind --days=1        only the day-before warning
 *     php artisan rent:remind --days=5,1
 */
class SendRentReminders extends Command
{
    protected $signature = 'rent:remind
                            {--days=* : Lead times in days before the due date (default 5,1)}';

    protected $description = 'Email renters whose rent is unpaid and approaching its due date';

    public function handle(): int
    {
        $leadTimes = $this->leadTimes();

        if ($leadTimes === []) {
            $this->components->error('No valid --days values given.');

            return self::INVALID;
        }

        $sent = 0;
        $skipped = 0;

        foreach ($leadTimes as $days) {
            foreach ($this->dueBills($days) as $bill) {
                $outcome = $this->remind($bill, $days);
                $outcome === 'sent' ? $sent++ : $skipped++;
            }
        }

        $this->components->info("Reminders sent: {$sent}; skipped: {$skipped}.");

        return self::SUCCESS;
    }

    /** @return list<int> */
    private function leadTimes(): array
    {
        $raw = (array) $this->option('days');

        if ($raw === [] || $raw === [null]) {
            $raw = [5, 1];
        }

        return collect($raw)
            ->map(fn ($v) => (int) $v)
            ->filter(fn (int $d) => $d >= 0 && $d <= 60)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Bills due in exactly $days days that still owe money.
     *
     * Scoped with withoutOwnerScope() deliberately: this command spans the whole
     * installation, and each bill is handled inside its own owner's context.
     *
     * @return Collection<int, Bill>
     */
    private function dueBills(int $days): Collection
    {
        $target = CarbonImmutable::today()->addDays($days)->toDateString();

        return Bill::withoutOwnerScope()
            ->whereDate('due_date', $target)
            ->whereIn('status', [BillStatus::Pending->value, BillStatus::Partial->value])
            ->whereNotNull('tenant_id')
            ->get();
    }

    private function remind(Bill $bill, int $days): string
    {
        if ($bill->tenant_id === null) {
            return 'skipped';
        }

        // The dedupe check comes BEFORE any work, so a repeat run costs one
        // cheap indexed lookup rather than rendering and queueing an email.
        if (RentReminder::alreadySent((int) $bill->getKey(), $days)) {
            return 'skipped';
        }

        $previous = TenantContext::id();
        TenantContext::set((int) $bill->owner_id);

        try {
            $renter = Renter::query()->find($bill->tenant_id);

            if ($renter === null || ($renter->email ?? '') === '') {
                return 'skipped';
            }

            $amount = $this->outstandingFor($renter, $bill);
            $property = $renter->property ?? $renter->house?->property;

            $log = app(Mailer::class)->trySend(
                // Day-before gets the firmer template; lead time is the only
                // difference between the two.
                $days <= 1 ? MailTemplate::RentReminderFinal : MailTemplate::RentReminder,
                (int) $bill->owner_id,
                (string) $renter->email,
                (string) $renter->name,
                [
                    'tenant' => (string) $renter->name,
                    'month' => $this->humanMonth((string) $bill->month),
                    'amount' => Amount::toString($amount),
                    'balance' => Amount::toString(Amount::of($renter->balance ?? '0.00')),
                    'payment_instructions' => PortfolioMailer::paymentInstructions($property),
                ],
            );

            if ($log === null) {
                // Not recorded, so a later run may try again rather than the
                // reminder being silently lost to a transient mail failure.
                return 'skipped';
            }

            $this->record($bill, $renter, $days, $amount);

            return 'sent';
        } finally {
            TenantContext::set($previous);
        }
    }

    /**
     * What is actually owed on this bill, not the bill's face value.
     *
     * A part-paid bill still owes the remainder, and telling a renter they owe
     * the full amount when they have already paid half is the kind of thing
     * that ends a tenancy.
     */
    private function outstandingFor(Renter $renter, Bill $bill): Money
    {
        $paid = DB::table('payment_allocations')
            ->join('bill_items', 'bill_items.id', '=', 'payment_allocations.bill_item_id')
            ->join('bills', 'bills.id', '=', 'bill_items.bill_id')
            ->where('bills.id', $bill->getKey())
            ->whereIn('payment_allocations.payment_id', DB::table('payments')
                ->where('tenant_id', $renter->getKey())
                ->whereIn('status', ['completed', 'confirmed', 'paid'])
                ->select('id'))
            ->sum('payment_allocations.amount');

        return Amount::owed(Amount::of($bill->total), Amount::of($paid));
    }

    private function record(Bill $bill, Renter $renter, int $days, Money $amount): void
    {
        RentReminder::query()->create([
            'tenant_id' => $renter->getKey(),
            'house_id' => $bill->house_id,
            'bill_id' => $bill->getKey(),
            'month' => (string) $bill->month,
            'amount' => Amount::toString($amount),
            'days_before_due' => $days,
            'sent_at' => CarbonImmutable::now(),
            'created_at' => CarbonImmutable::now(),
            'status' => 'sent',
        ]);
    }

    /** "2026-02" -> "February 2026", so the subject line reads properly. */
    private function humanMonth(string $month): string
    {
        $parsed = CarbonImmutable::createFromFormat('Y-m', $month);

        return $parsed === false ? $month : $parsed->format('F Y');
    }
}
