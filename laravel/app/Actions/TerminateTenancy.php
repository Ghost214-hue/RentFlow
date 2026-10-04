<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\RenterStatus;
use App\Enums\TerminationStatus;
use App\Models\Caretaker;
use App\Models\House;
use App\Models\MaintenanceRecord;
use App\Models\Owner;
use App\Models\Renter;
use App\Models\TenancyTermination;
use App\Support\Amount;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Tenancy termination, ported from TenantController::terminate() and the
 * tenant-initiated request in the same controller.
 *
 * Two flows:
 *   request()  -- the RENTER asks to leave. Sets status pending_termination and
 *                 writes a PENDING row for the owner to approve.
 *   apply()    -- the OWNER ends the tenancy. Records any damages as completed
 *                 maintenance rows, frees the unit, scrubs the renter's
 *                 personal data, and writes a COMPLETED row.
 *
 * Differs from the legacy implementation deliberately:
 *   - damage costs go through Amount/Brick, never (float) as the legacy did;
 *   - everything runs in ONE transaction, so a failure cannot leave the unit
 *     released while the renter row still says "active";
 *   - the renter row is locked, so two concurrent terminations cannot both
 *     pass the "already terminating" check.
 */
final class TerminateTenancy
{
    /**
     * A renter asks to leave their tenancy.
     */
    public function request(Renter $renter, string $reason, ?string $effectiveDate = null): TenancyTermination
    {
        if ($renter->house_id === null) {
            throw new RuntimeException('You are not currently occupying a unit.');
        }

        if ($renter->status === RenterStatus::Terminated) {
            throw new RuntimeException('This tenancy has already ended.');
        }

        $effectiveDate ??= now()->addDays(30)->toDateString();

        return DB::transaction(function () use ($renter, $reason, $effectiveDate): TenancyTermination {
            // Lock the renter so two requests cannot both pass the check.
            $locked = Renter::query()->lockForUpdate()->findOrFail($renter->getKey());

            $existing = TenancyTermination::query()
                ->where('tenant_id', $locked->getKey())
                ->where('status', TerminationStatus::Pending->value)
                ->exists();

            if ($existing) {
                throw new RuntimeException('You already have a pending termination request.');
            }

            $locked->update(['status' => RenterStatus::PendingTermination]);

            return TenancyTermination::create([
                'tenant_id' => $locked->getKey(),
                'property_id' => $locked->property_id,
                'house_id' => $locked->house_id,
                'initiated_by' => 'tenant',
                'initiated_by_user_id' => $locked->getKey(),
                'reason' => $reason,
                'effective_date' => $effectiveDate,
                'status' => TerminationStatus::Pending->value,
            ]);
        });
    }

    /**
     * The owner (or an assigned caretaker) ends the tenancy immediately.
     *
     * @param  array<int, array{title: string, description?: string, cost?: string, vendor_name?: string, priority?: string}>  $damages
     */
    public function apply(
        Renter $renter,
        Owner|Caretaker $actor,
        string $reason,
        ?string $effectiveDate = null,
        array $damages = [],
    ): TenancyTermination {
        $effectiveDate ??= now()->toDateString();

        return DB::transaction(function () use ($renter, $actor, $reason, $effectiveDate, $damages): TenancyTermination {
            $locked = Renter::query()->lockForUpdate()->findOrFail($renter->getKey());

            $houseId = $locked->house_id;
            $propertyId = $locked->property_id;

            // Damages become completed maintenance rows so the cost stays
            // visible in the unit's repair history.
            foreach ($damages as $damage) {
                if (trim((string) ($damage['title'] ?? '')) === '') {
                    continue;
                }

                MaintenanceRecord::create([
                    'property_id' => $propertyId,
                    'house_id' => $houseId,
                    'tenant_id' => $locked->getKey(),
                    'title' => $damage['title'],
                    'description' => $damage['description'] ?? '',
                    'category' => 'Damage',
                    'priority' => $damage['priority'] ?? 'medium',
                    'status' => 'completed',
                    // Money as a decimal string, never a float.
                    'cost' => Amount::toString(Amount::of($damage['cost'] ?? '0')),
                    'vendor_name' => $damage['vendor_name'] ?? '',
                    'notes' => 'Recorded during tenancy termination',
                    'completed_date' => now()->toDateString(),
                    'recipient_type' => 'individual',
                    'recipient_ids' => json_encode([(int) $locked->getKey()]),
                ]);
            }

            // Free the unit.
            if ($houseId !== null) {
                House::query()->whereKey($houseId)->update([
                    'status' => 'vacant',
                    'tenant_id' => null,
                ]);
            }

            /*
             * Scrub the renter's personal data but KEEP house_id/property_id so
             * past-tenancy history stays traceable per unit. The password is
             * cleared so a terminated renter cannot sign in again.
             */
            $locked->update([
                'lease_end' => $effectiveDate,
                'status' => RenterStatus::Terminated,
                'documents' => null,
                'profile_picture' => null,
                'password' => null,
                'next_of_kin_name' => null,
                'next_of_kin_phone' => null,
                'next_of_kin_email' => null,
            ]);

            // Recount occupancy from the house rows rather than trusting the
            // cached properties.occupied column.
            if ($propertyId !== null) {
                $occupied = House::query()
                    ->where('property_id', $propertyId)
                    ->where('status', 'occupied')
                    ->count();

                \App\Models\Property::query()
                    ->whereKey($propertyId)
                    ->update(['occupied' => $occupied]);
            }

            // Complete any pending request, or record this one.
            $pending = TenancyTermination::query()
                ->where('tenant_id', $locked->getKey())
                ->where('status', TerminationStatus::Pending->value)
                ->first();

            if ($pending !== null) {
                $pending->update([
                    'status' => TerminationStatus::Completed->value,
                    'reason' => $reason,
                    'effective_date' => $effectiveDate,
                ]);

                return $pending;
            }

            return TenancyTermination::create([
                'tenant_id' => $locked->getKey(),
                'property_id' => $propertyId,
                'house_id' => $houseId,
                'initiated_by' => $actor instanceof Caretaker ? 'caretaker' : 'owner',
                'initiated_by_user_id' => (int) $actor->getKey(),
                'reason' => $reason,
                'effective_date' => $effectiveDate,
                'status' => TerminationStatus::Completed->value,
            ]);
        });
    }
}