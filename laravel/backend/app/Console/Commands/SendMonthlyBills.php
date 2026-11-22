<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\GenerateMonthlyBills;
use App\Billing\BillSnapshotBuilder;
use App\Mail\MailTemplate;
use App\Mail\PortfolioMailer;
use App\Models\Bill;
use App\Models\Owner;
use App\Models\RentReminder;
use App\Services\SmsService;
use App\Support\Amount;
use App\Support\CrossTenant;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * Generates this month's bills for every owner, then notifies the tenants
 * whose bill was created in this run.
 *
 * Fixed from the previous version, which could never produce a usable bill:
 *  - it looked for houses with status 'active' (units are 'occupied') and a
 *    `tenancy` relation that does not exist, so it matched nothing;
 *  - it created bills with a total but no bill_items, so they could not be paid;
 *  - it only ran on day 1, so one missed run meant no bills all month;
 *  - Laravel 11 ignores app/Console/Kernel.php, so it was never scheduled.
 *
 * Now it runs DAILY and is idempotent: bills that already exist are untouched
 * and only newly created bills trigger a notification. A server that was down
 * on the 1st simply catches up on the next run.
 *
 * Run: php artisan send:monthly:bills [--no-notify]
 */
class SendMonthlyBills extends Command
{
    protected $signature = 'send:monthly:bills {--no-notify : Create bills but send no email/SMS}';

    protected $description = 'Generate this month\'s bills for all owners and notify tenants (idempotent, safe to run daily)';

    public function handle(): int
    {
        $month = CarbonImmutable::now()->format('Y-m');
        $day = (int) CarbonImmutable::now()->format('j');
        $from = (int) config('billing.generation_day', 1);

        if ($day < $from) {
            $this->components->info("Bills are generated from day {$from}; today is day {$day}.");

            return self::SUCCESS;
        }

        $created = 0;
        $emails = 0;
        $sms = 0;

        $ownerIds = CrossTenant::run('list owners for monthly billing', fn () => Owner::query()->pluck('id')->all());

        foreach ($ownerIds as $ownerId) {
            CrossTenant::runAsOwner((int) $ownerId, 'monthly bill generation', function () use ($ownerId, $month, &$created, &$emails, &$sms): void {
                try {
                    $result = (new GenerateMonthlyBills)->handle(month: $month);
                } catch (\Throwable $e) {
                    $this->components->error("Owner {$ownerId}: {$e->getMessage()}");

                    return;
                }

                $created += $result['generated'];

                if ($this->option('no-notify') || $result['created_ids'] === []) {
                    return;
                }

                foreach (Bill::query()->with(['house', 'renter'])->whereIn('id', $result['created_ids'])->get() as $bill) {
                    [$e, $s] = $this->notify($bill, $month);
                    $emails += $e;
                    $sms += $s;
                }
            });
        }

        $this->components->info("Monthly bills {$month}: created {$created}, emails {$emails}, SMS {$sms}");

        return self::SUCCESS;
    }

    /** @return array{0: int, 1: int} */
    private function notify(Bill $bill, string $month): array
    {
        $renter = $bill->renter;
        $house = $bill->house;

        if ($renter === null || $house === null) {
            return [0, 0];
        }

        // Amount due INCLUDES arrears brought forward and credit applied.
        $snap = (new BillSnapshotBuilder)->build($bill);
        $vars = [
            'tenant' => (string) $renter->name,
            'month' => $month,
            'amount' => Amount::toString($snap->total),
            'balance' => Amount::toString($snap->balance),
            'due_date' => (string) $bill->due_date?->format('Y-m-d'),
            'payment_instructions' => PortfolioMailer::paymentInstructions($house),
        ];

        $emails = 0;
        $sms = 0;

        if (($renter->email ?? '') !== '') {
            try {
                $log = app(\App\Mail\Mailer::class)->trySend(
                    MailTemplate::RentReminder,
                    (int) $house->owner_id,
                    (string) $renter->email,
                    (string) $renter->name,
                    $vars,
                );

                if ($log !== null) {
                    RentReminder::create([
                        'tenant_id' => $renter->getKey(),
                        'house_id' => $house->getKey(),
                        'bill_id' => $bill->getKey(),
                        'month' => $month,
                        'amount' => $vars['amount'],
                        'sent_at' => CarbonImmutable::now(),
                        'status' => 'sent',
                    ]);
                    $emails++;
                }
            } catch (\Throwable $e) {
                $this->components->warn("Email to {$renter->name} failed: {$e->getMessage()}");
            }
        }

        if (($renter->phone ?? '') !== '') {
            try {
                app(SmsService::class)->trySend('bill_generated', (int) $house->owner_id, (string) $renter->phone, (string) $renter->name, $vars);
                $sms++;
            } catch (\Throwable $e) {
                $this->components->warn("SMS to {$renter->name} failed: {$e->getMessage()}");
            }
        }

        return [$emails, $sms];
    }
}
