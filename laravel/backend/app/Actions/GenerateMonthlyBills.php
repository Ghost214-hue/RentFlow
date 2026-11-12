<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\House;
use App\Support\Amount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Generate the month's bills for occupied units.
 *
 * Ported from BillController::generate(). The rules that matter:
 *
 *  - CURRENT MONTH ONLY. Past and future months are refused.
 *  - Optional day-of-month gate (BILL_GENERATION_DAY) so a landlord is not
 *    invoicing on the 1st.
 *  - IDEMPOTENT. If a bill already exists for renter + month it is NEVER
 *    regenerated, recalculated or altered. That is what stops a second click
 *    on "Generate Bills" from overwriting recorded payments.
 *  - RENT ONLY plus utilities. The security deposit is charged ONCE at
 *    onboarding, never on a recurring monthly bill.
 *  - Carried-forward arrears/credit are NOT baked in here; BillSnapshot adds
 *    them at read time so they appear exactly once.
 *  - Everything commits atomically, or nothing does.
 */
final class GenerateMonthlyBills
{
    /**
     * @param  array<string, string>  $utilityCharges  house id => amount
     * @return array{generated: int, skipped: int, bill_ids: array<int, int>}
     */
    public function handle(
        ?int $propertyId = null,
        ?string $month = null,
        ?string $dueDate = null,
        array $utilityCharges = [],
        ?int $onlyRenterId = null,
    ): array {
        $currentMonth = now()->format('Y-m');
        $month ??= $currentMonth;
        $dueDate ??= now()->format('Y-m-05');

        // Bills can only be generated for the current month.
        if ($month !== $currentMonth) {
            throw ValidationException::withMessages([
                'month' => "Bills can only be generated for the current month ({$currentMonth}).",
            ]);
        }

        // Optional gate: set BILL_GENERATION_DAY to only allow generation
        // from a given day of the month. Unset or 0 allows any day.
        $generationDay = (int) (env('BILL_GENERATION_DAY') ?: 0);

        if ($generationDay > 0 && (int) now()->format('j') < $generationDay) {
            throw ValidationException::withMessages([
                'month' => "Bill generation is only allowed on or after day {$generationDay}.",
            ]);
        }

        $houses = House::query()
            ->with('property:id,name')
            ->where('status', 'occupied')
            ->whereNotNull('tenant_id')
            ->when($propertyId !== null, fn ($q) => $q->where('property_id', $propertyId))
            ->when($onlyRenterId !== null, fn ($q) => $q->where('tenant_id', $onlyRenterId))
            ->orderBy('unit')
            ->get();

        $generated = 0;
        $skipped = 0;
        $billIds = [];

        DB::transaction(function () use ($houses, $month, $dueDate, $utilityCharges, &$generated, &$skipped, &$billIds): void {
            foreach ($houses as $house) {
                // IDEMPOTENT: an existing bill for this renter + month is
                // left completely untouched.
                $existing = Bill::query()
                    ->where('tenant_id', $house->tenant_id)
                    ->where('month', $month)
                    ->orderBy('id')
                    ->first();

                if ($existing !== null) {
                    $skipped++;
                    $billIds[] = (int) $existing->getKey();

                    continue;
                }

                // Monthly bills are RENT ONLY, from houses.rent, plus any
                // utilities. The deposit was charged at onboarding.
                $items = [
                    [
                        'type' => 'Rent',
                        'description' => 'Monthly Rent',
                        'amount' => Amount::of($house->rent),
                    ],
                ];

                $water = Amount::tryFromInput($utilityCharges[(string) $house->getKey()] ?? null);
                if ($water !== null && $water->compareTo(Amount::zero()) > 0) {
                    $items[] = [
                        'type' => 'Water',
                        'description' => 'Water Charges',
                        'amount' => $water,
                    ];
                }

                $electricity = Amount::tryFromInput($utilityCharges['elec_'.(string) $house->getKey()] ?? null);
                if ($electricity !== null && $electricity->compareTo(Amount::zero()) > 0) {
                    $items[] = [
                        'type' => 'Electricity',
                        'description' => 'Electricity Charges',
                        'amount' => $electricity,
                    ];
                }

                $bill = Bill::create([
                    'house_id' => $house->getKey(),
                    'tenant_id' => $house->tenant_id,
                    'month' => $month,
                    'type' => 'Rent',
                    'due_date' => $dueDate,
                    'total' => '0.00',
                    'status' => BillStatus::Pending,
                ]);

                $total = Amount::zero();

                foreach ($items as $item) {
                    BillItem::create([
                        'bill_id' => $bill->getKey(),
                        'type' => $item['type'],
                        'description' => $item['description'],
                        'amount' => Amount::toString($item['amount']),
                        'paid' => '0.00',
                        'status' => 'pending',
                    ]);

                    $total = $total->plus($item['amount']);
                }

                // Keep the denormalised cache in step for legacy readers;
                // BillSnapshot remains authoritative.
                $bill->update(['total' => Amount::toString($total)]);

                $generated++;
                $billIds[] = (int) $bill->getKey();
            }
        });

        return ['generated' => $generated, 'skipped' => $skipped, 'bill_ids' => $billIds];
    }
}