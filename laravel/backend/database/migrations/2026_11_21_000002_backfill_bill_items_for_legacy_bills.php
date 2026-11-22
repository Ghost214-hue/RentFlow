<?php

declare(strict_types=1);

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Owner;
use App\Support\Amount;
use App\Support\TenantContext;
use Illuminate\Database\Migrations\Migration;

/**
 * Backfill bill_items for legacy bills.
 *
 * Legacy bills were imported with amounts only in the denormalised
 * `bills.total` / `bills.rent` columns; the `bill_items` rows that
 * BillSnapshot derives every figure from were never created, so every bill
 * rendered as Ksh 0.00 across all months.
 *
 * This migration creates one Rent item per bill that still lacks items,
 * sourced from `bills.total` (the amount that was actually charged). It is
 * idempotent: bills that already have items are left untouched, so re-running
 * the migration creates nothing.
 *
 * Carried-forward arrears are intentionally NOT baked in here: BillSnapshot
 * adds them at read time, exactly once, from the allocation ledger.
 */
return new class extends Migration
{
    public function up(): void
    {
        Owner::query()->orderBy('id')->chunkById(100, function ($owners): void {
            foreach ($owners as $owner) {
                TenantContext::set((int) $owner->id);

                Bill::query()->whereDoesntHave('items')->get()->each(function (Bill $bill): void {
                    BillItem::query()->create([
                        'bill_id' => $bill->getKey(),
                        'type' => 'Rent',
                        'description' => 'Monthly Rent',
                        'amount' => Amount::toString(Amount::of($bill->total)),
                        'paid' => '0.00',
                        'status' => 'pending',
                    ]);
                });
            }
        });

        TenantContext::clear();
    }

    public function down(): void
    {
        // Intentional no-op: this backfill cannot be safely reversed — generated
        // bills create Rent items with the same shape, so deleting by shape
        // would destroy legitimate data. The up() is idempotent and safe to
        // re-run instead.
    }
};