<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        // Legacy schema may not exist on fresh installs; nothing to recover.
        if (! Schema::hasTable('payment_allocations')) {
            return;
        }

        // Never clobber payments that were recorded through the UI.
        if (DB::table('payments')->count() > 0) {
            return;
        }

        $groups = DB::table('payment_allocations as pa')
            ->join('bill_items as bi', 'bi.id', '=', 'pa.bill_item_id')
            ->join('bills as b', 'b.id', '=', 'bi.bill_id')
            ->select(
                'pa.payment_id as original_id',
                DB::raw('SUM(pa.amount) as amount'),
                DB::raw('GROUP_CONCAT(DISTINCT pa.category) as categories'),
                DB::raw('GROUP_CONCAT(DISTINCT b.tenant_id) as tenants'),
                DB::raw('GROUP_CONCAT(DISTINCT b.house_id) as houses'),
                DB::raw('GROUP_CONCAT(DISTINCT b.month) as months'),
                DB::raw('MAX(b.owner_id) as owner_id'),
            )
            ->groupBy('pa.payment_id')
            ->orderBy('pa.payment_id')
            ->get();

        $recovered = 0;
        $skipped = 0;

        foreach ($groups as $group) {
            $tenants = array_unique(explode(',', $group->tenants ?? ''));
            $houses = array_unique(explode(',', $group->houses ?? ''));
            $months = array_unique(explode(',', $group->months ?? ''));

            // Only recover payments that resolve to ONE tenant + ONE house.
            // A payment spanning multiple tenants/houses cannot be a single row.
            if (count($tenants) !== 1 || count($houses) !== 1) {
                $skipped++;
                continue;
            }

            $categories = array_unique(explode(',', $group->categories ?? ''));
            $type = count($categories) === 1 ? $categories[0] : 'Mixed';

            DB::table('payments')->insert([
                'id' => (int) $group->original_id, // restore original payment id so allocations join back
                'owner_id' => $group->owner_id,
                'tenant_id' => (int) $tenants[0],
                'house_id' => $houses[0] !== '' ? (int) $houses[0] : null,
                'month' => count($months) === 1 && $months[0] !== '' ? $months[0] : null,
                'amount' => $group->amount,
                'type' => in_array($type, ['Rent', 'Water', 'Electricity', 'Deposit', 'Mixed'], true) ? $type : 'Rent',
                'method' => null,   // not stored in allocations -- left truthful
                'date' => null,     // not stored in allocations -- left truthful
                'status' => 'completed',
                'tenant_confirmed' => false,
                'receipt' => null,
                'reference' => null,
                'description' => 'Recovered from legacy payment_allocations',
                'created_at' => now(),
            ]);

            $recovered++;
        }
    }

    public function down(): void
    {
        // No-op: cannot safely distinguish recovered rows from real ones.
    }
};