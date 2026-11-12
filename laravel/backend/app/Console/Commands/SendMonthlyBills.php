<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\BillStatus;
use App\Mail\MailTemplate;
use App\Mail\PortfolioMailer;
use App\Models\Bill;
use App\Models\House;
use App\Models\Renter;
use App\Models\RentReminder;
use App\Services\SmsService;
use App\Services\SmsTemplateRenderer;
use App\Support\Amount;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Sends monthly bills to all tenants on the 1st day of each month.
 *
 * This command:
 * - Creates bills for the current month for all active tenancies
 * - Sends email notifications to renters with their bill details
 * - Is idempotent - safe to run multiple times per month
 *
 * Run: php artisan send:monthly:bills
 */
class SendMonthlyBills extends Command
{
    protected $signature = 'send:monthly:bills';
    protected $description = 'Send monthly bills to all tenants on the 1st day of the month';

    public function handle(): int
    {
        $currentMonth = CarbonImmutable::now()->format('Y-m');
        $currentDay = CarbonImmutable::now()->day;

        // Only run on the 1st day of the month
        if ($currentDay !== 1) {
            $this->components->info("This command should only run on the 1st day of the month. Today is day {$currentDay}.");
            return self::SUCCESS;
        }

        $this->components->info("Generating bills for {$currentMonth}...");

        // Get all active houses/tenancies
        $houses = House::where('status', 'active')->get();

        $billsCreated = 0;
        $sentEmails = 0;
        $sentSms = 0;

        $smsService = app(SmsService::class);

        foreach ($houses as $house) {
            $tenancy = $house->tenancy;

            if (!$tenancy || !$tenancy->renter_id) {
                continue;
            }

            $renterId = $tenancy->renter_id;
            $renter = Renter::find($renterId);

            if (!$renter || !$renter->email) {
                continue;
            }

            // Check if bill already exists for this month
            $existingBill = Bill::where('house_id', $house->id)
                ->where('month', $currentMonth)
                ->where('tenant_id', $renterId)
                ->first();

            if ($existingBill) {
                $this->components->info("Bill already exists for {$renter->name} - {$currentMonth}");
                continue;
            }

            // Create the bill
            $dueDate = CarbonImmutable::now()->endOfMonth()->format('Y-m-d');
            $rentAmount = $tenancy->rent ?? 0;

            $bill = Bill::create([
                'house_id' => $house->id,
                'tenant_id' => $renterId,
                'month' => $currentMonth,
                'due_date' => $dueDate,
                'status' => BillStatus::Pending->value,
                'total' => Amount::of($rentAmount),
                'description' => "Monthly rent for {$currentMonth}",
            ]);

            $billsCreated++;

            // Send email notification using existing Mailer class
            try {
                $log = app(\App\Mail\Mailer::class)->trySend(
                    MailTemplate::RentReminder,
                    (int) $house->owner_id,
                    (string) $renter->email,
                    (string) $renter->name,
                    [
                        'tenant' => (string) $renter->name,
                        'month' => $currentMonth,
                        'amount' => Amount::toString(Amount::of($rentAmount)),
                        'balance' => Amount::toString(Amount::of($renter->balance ?? '0.00')),
                        'payment_instructions' => PortfolioMailer::paymentInstructions($house),
                    ],
                );

                if ($log === null) {
                    continue;
                }

                // Record rent reminder
                RentReminder::create([
                    'tenant_id' => $renterId,
                    'house_id' => $house->id,
                    'bill_id' => $bill->id,
                    'month' => $currentMonth,
                    'amount' => Amount::toString(Amount::of($rentAmount)),
                    'sent_at' => CarbonImmutable::now(),
                    'status' => 'sent',
                ]);

                $sentEmails++;

                // Send SMS notification if renter has a phone number
                if (($renter->phone ?? '') !== '') {
                    try {
                        $smsService->trySend(
                            'bill_generated',
                            (int) $house->owner_id,
                            (string) $renter->phone,
                            (string) $renter->name,
                            [
                                'tenant' => (string) $renter->name,
                                'month' => $currentMonth,
                                'amount' => Amount::toString(Amount::of($rentAmount)),
                                'due_date' => $dueDate,
                                'balance' => Amount::toString(Amount::of($renter->balance ?? '0.00')),
                                'payment_instructions' => PortfolioMailer::paymentInstructions($house),
                            ],
                        );
                        $sentSms++;
                    } catch (\Exception $e) {
                        $this->warning("Failed to send bill SMS to {$renter->name}: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                $this->warning("Failed to send bill email to {$renter->name}: " . $e->getMessage());
            }
        }

        $this->components->info(
            "Monthly bills: Created {$billsCreated}, Emails sent {$sentEmails}, SMS sent {$sentSms}"
        );

        return self::SUCCESS;
    }
}
