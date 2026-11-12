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
use App\Services\SmsService;
use App\Support\Amount;
use App\Support\TenantContext;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Chases unpaid rent before, on, and after the due date.
 *
 * Runs across EVERY owner, so it resolves each tenancy explicitly rather than
 * relying on an ambient context -- a scheduled command has no authenticated
 * actor, and the owner scope fails closed when none is set.
 *
 * IDEMPOTENT BY DESIGN. This command is meant to be run daily (or hourly).
 * `rent_reminders` records (bill_id, days_before_due), so each lead time fires
 * at most once per bill per day. For overdue bills, days_before_due is stored
 * as a negative number (e.g. -1, -2, -3 = days overdue) so each day a new
 * reminder can be sent until payment is recorded.
 *
 *     php artisan rent:remind                 both lead times + overdue
 *     php artisan rent:remind --days=1        only the day-before warning + overdue
 *     php artisan rent:remind --days=5,1
 *     php artisan rent:remind --overdue-only  only overdue bill reminders
 */
class SendRentReminders extends Command
{
    protected $signature = 'rent:remind
                            {--days=* : Lead times in days before the due date (default 5,1)}
                            {--overdue-only : Only send reminders for overdue bills}';

    protected $description = 'Email renters whose rent is unpaid - upcoming, due, and overdue';

    public function handle(): int
    {
        $leadTimes = $this->leadTimes();
        $overdueOnly = (bool) $this->option('overdue-only');

        if ($leadTimes === [] && !$overdueOnly) {
            $this->components->error('No valid --days values given.');

            return self::INVALID;
        }

        $sent = 0;
        $skipped = 0;
        $sentSms = 0;

        $smsService = app(SmsService::class);

        // Send reminders for upcoming bills (before due date)
        if (!$overdueOnly) {
            foreach ($leadTimes as $days) {
                foreach ($this->dueBills($days) as $bill) {
                    $outcome = $this->remind($bill, $days, $smsService);
                    $outcome === 'sent' ? $sent++ : $skipped++;
                }
            }
        }

        // Send reminders for overdue bills (past due date, still unpaid)
        foreach ($this->overdueBills() as $bill) {
            $daysOverdue = (int) CarbonImmutable::today()->diffInDays($bill->due_date);
            $outcome = $this->remind($bill, -$daysOverdue, $smsService);
            $outcome === 'sent' ? $sent++ : $skipped++;
        }

        $this->components->info("Reminders sent: {$sent}; skipped: {$skipped}; SMS sent: {$sentSms}.");

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

    /**
     * Overdue bills that still owe money.
     *
     * Finds bills whose due_date has passed but still have Pending or Partial
     * status. These get a daily reminder until the bill is paid.
     *
     * @return Collection<int, Bill>
     */
    private function overdueBills(): Collection
    {
        $today = CarbonImmutable::today()->toDateString();

        return Bill::withoutOwnerScope()
            ->whereDate('due_date', '<', $today)
            ->whereIn('status', [BillStatus::Pending->value, BillStatus::Partial->value])
            ->whereNotNull('tenant_id')
            ->get();
    }

    private function remind(Bill $bill, int $days, SmsService $smsService): string
    {
        if ($bill->tenant_id === null) {
            return 'skipped';
        }

        $previous = TenantContext::id();
        TenantContext::set((int) $bill->owner_id);

        try {
            $renter = Renter::query()->find($bill->tenant_id);

            if ($renter === null || ($renter->email ?? '') === '') {
                return 'skipped';
            }

            // Skip if already reminded today for this bill (idempotency check)
            if ($this->alreadyRemindedToday($bill, $days)) {
                return 'skipped';
            }

            $amount = $this->outstandingFor($renter, $bill);
            $property = $renter->property ?? $renter->house?->property;

            // Use RentReminderFinal template for overdue bills (firmer tone)
            $template = $days < 0 ? MailTemplate::RentReminderFinal : MailTemplate::RentReminder;

            $log = app(Mailer::class)->trySend(
                $template,
                (int) $bill->owner_id,
                (string) $renter->email,
                (string) $renter->name,
                [
                    'tenant' => (string) $renter->name,
                    'month' => $this->humanMonth((string) $bill->month),
                    'amount' => Amount::toString($amount),
                    'balance' => Amount::toString(Amount::of($renter->balance ?? '0.00')),
                    'payment_instructions' => PortfolioMailer::paymentInstructions($property),
                    'is_overdue' => $days < 0,
                    'days_overdue' => $days < 0 ? abs($days) : null,
                ],
            );

            if ($log === null) {
                // Not recorded, so a later run may try again rather than the
                // reminder being silently lost to a transient mail failure.
                return 'skipped';
            }

            // Send SMS if renter has a phone number
            if (($renter->phone ?? '') !== '') {
                $smsTemplate = $days < 0 ? 'rent_reminder_final' : 'rent_reminder';
                try {
                    $smsService->trySend(
                        $smsTemplate,
                        (int) $bill->owner_id,
                        (string) $renter->phone,
                        (string) $renter->name,
                        [
                            'tenant' => (string) $renter->name,
                            'month' => $this->humanMonth((string) $bill->month),
                            'amount' => Amount::toString($amount),
                            'balance' => Amount::toString(Amount::of($renter->balance ?? '0.00')),
                            'due_date' => (string) $bill->due_date,
                            'days_overdue' => $days < 0 ? abs($days) : null,
                            'payment_instructions' => PortfolioMailer::paymentInstructions($property),
                        ],
                    );
                } catch (\Exception $e) {
                    $this->warning("Failed to send SMS reminder to {$renter->name}: " . $e->getMessage());
                }
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

    /**
     * Check if this bill was already reminded today for the same lead time.
     * Prevents duplicate emails when the command runs multiple times per day.
     */
    private function alreadyRemindedToday(Bill $bill, int $days): bool
    {
        $today = CarbonImmutable::today()->toDateString();

        return RentReminder::withoutOwnerScope()
            ->where('bill_id', $bill->getKey())
            ->where('days_before_due', $days)
            ->whereDate('sent_at', $today)
            ->exists();
    }

    /** "2026-02" -> "February 2026", so the subject line reads properly. */
    private function humanMonth(string $month): string
    {
        $parsed = CarbonImmutable::createFromFormat('Y-m', $month);

        return $parsed === false ? $month : $parsed->format('F Y');
    }
}
