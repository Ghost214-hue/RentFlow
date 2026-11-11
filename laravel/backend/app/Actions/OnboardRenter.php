<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\RenterStatus;
use App\Mail\PortfolioMailer;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\House;
use App\Models\Property;
use App\Models\Renter;
use App\Services\AccountSetupService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Onboard a renter (tenant).
 *
 * Ported from TenantController::store(). The whole operation is one
 * transaction, because a half-onboarded renter (row saved, unit not claimed,
 * or unit claimed but no first bill) is worse than no renter at all.
 *
 * Rules preserved from legacy:
 *   - the unit must be VACANT, and is claimed atomically so two concurrent
 *     onboardings cannot both take the same unit;
 *   - the property's occupied counter is recomputed, not incremented;
 *   - the FIRST bill for the month is rent + deposit (deposit only when > 0),
 *     due on the 5th, and is never created twice.
 */
final class OnboardRenter
{
    private readonly PortfolioMailer $mailer;

    private readonly AccountSetupService $setups;

    public function __construct(
        ?PortfolioMailer $mailer = null,
        ?AccountSetupService $setups = null,
    ) {
        // Constructed by hand (`new OnboardRenter()`), so both collaborators are
        // resolved here rather than demanded at every call site.
        $this->mailer = $mailer ?? app(PortfolioMailer::class);
        $this->setups = $setups ?? app(AccountSetupService::class);
    }

    /** @param array<string, mixed> $attributes */
    public function handle(array $attributes): Renter
    {
        $renter = DB::transaction(function () use ($attributes): Renter {
            $renter = Renter::create([
                'property_id' => $attributes['property_id'],
                'house_id' => $attributes['house_id'],
                'name' => $attributes['name'],
                'email' => $attributes['email'] ?? null,
                'phone' => $attributes['phone'],
                'id_type' => $attributes['id_type'],
                'id_number' => $attributes['id_number'] ?? null,

                // Lease + money. All decimal strings.
                'lease_start' => $attributes['lease_start'] ?? null,
                'lease_end' => $attributes['lease_end'] ?? null,
                'deposit' => $attributes['deposit'] ?? '0.00',
                'balance' => $attributes['balance'] ?? '0.00',
                'credit' => '0.00',
                'status' => RenterStatus::Active,

                // NOTE: the onboarding "rent" is written to houses.rent (via
                // claimUnit), which is the source of truth for bill
                // generation. `tenants` has no rent column.

                'next_of_kin_name' => $attributes['next_of_kin_name'] ?? null,
                'next_of_kin_phone' => $attributes['next_of_kin_phone'] ?? null,
                'next_of_kin_email' => $attributes['next_of_kin_email'] ?? null,
            ]);

            $this->claimUnit((int) $renter->house_id, (int) $renter->id, (string) $attributes['rent']);

            $this->refreshPropertyOccupancy((int) $renter->property_id);

            $this->createFirstBill($renter, (string) $attributes['rent'], (string) ($attributes['deposit'] ?? '0.00'));

            return $renter;
        });

        // After the commit, never inside it: see RecordPayment. A welcome
        // email must not go out for a renter whose onboarding then rolled back.
        //
        // The invitation carries the real setup link, so this supersedes the
        // placeholder the welcome email used before the setup flow existed.
        $this->setups->invite('tenant', (int) $renter->getKey(), (int) $renter->owner_id);

        return $renter;
    }

    /**
     * Move an existing renter into a (vacant) unit.
     *
     * Used when a renter is reassigned. Keeps the unit claim atomic, exactly
     * like onboarding, so two concurrent moves cannot take the same unit.
     */
    public function reassignUnit(Renter $renter, int $houseId, string $rent): void
    {
        DB::transaction(function () use ($renter, $houseId, $rent): void {
            $claimed = House::query()
                ->whereKey($houseId)
                ->where('status', 'vacant')
                ->update([
                    'status' => 'occupied',
                    'tenant_id' => $renter->getKey(),
                    'rent' => $rent,
                ]);

            if ($claimed < 1) {
                throw new RuntimeException('That unit is no longer vacant.');
            }

            $renter->forceFill([
                'house_id' => $houseId,
            ])->save();
        });
    }

    /**
     * Atomically claim a vacant unit.
     *
     * The `status = 'vacant'` predicate is the guard: if a concurrent
     * transaction claimed it first, the UPDATE affects 0 rows and we abort
     * rather than overwriting the other renter.
     */
    private function claimUnit(int $houseId, int $renterId, string $rent): void
    {
        $claimed = House::query()
            ->whereKey($houseId)
            ->where('status', 'vacant')
            ->update([
                'status' => 'occupied',
                'tenant_id' => $renterId,
                'rent' => $rent,
            ]);

        if ($claimed < 1) {
            throw new RuntimeException('That unit is no longer vacant.');
        }
    }

    /**
     * Recompute the property's occupied count from the houses table rather
     * than incrementing a counter, so it cannot drift.
     */
    private function refreshPropertyOccupancy(int $propertyId): void
    {
        $occupied = House::query()
            ->where('property_id', $propertyId)
            ->where('status', 'occupied')
            ->count();

        Property::query()->whereKey($propertyId)->update(['occupied' => $occupied]);
    }

    /**
     * First bill of the tenancy: rent, plus the security deposit when the
     * amount is greater than zero. Deposit is charged ONCE, here — never on
     * recurring bills.
     */
    private function createFirstBill(Renter $renter, string $rent, string $deposit): void
    {
        $month = now()->format('Y-m');

        // Idempotent: never bill a renter twice for the same month.
        $exists = Bill::query()
            ->where('tenant_id', $renter->getKey())
            ->where('month', $month)
            ->exists();

        if ($exists) {
            return;
        }

        $bill = Bill::create([
            'house_id' => $renter->house_id,
            'tenant_id' => $renter->getKey(),
            'month' => $month,
            'type' => 'Rent',
            'due_date' => now()->format('Y-m-05'),
            'total' => '0.00',
            'status' => 'pending',
        ]);

        $items = [
            ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => $rent],
        ];

        if ((float) $deposit > 0.0) {
            $items[] = ['type' => 'Deposit', 'description' => 'Security Deposit', 'amount' => $deposit];
        }

        $total = '0.00';

        foreach ($items as $item) {
            BillItem::create([
                'bill_id' => $bill->getKey(),
                'type' => $item['type'],
                'description' => $item['description'],
                'amount' => $item['amount'],
                'paid' => '0.00',
                'status' => 'pending',
            ]);

            $total = bcadd($total, $item['amount'], 2);
        }

        $bill->update(['total' => $total]);
    }
}
