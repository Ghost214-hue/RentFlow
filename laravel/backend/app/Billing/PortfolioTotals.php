<?php

declare(strict_types=1);

namespace App\Billing;

use App\Support\Amount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Portfolio money, computed ONE way, from the ledger, in SQL.
 *
 * Every figure is per bill ITEM (charge) against the settled allocations on
 * that item. Because it is per item, carried-forward arrears are never added
 * twice (a bill's "balance" on screen includes prior months; summing those
 * would double count) and a payment can never be counted as both revenue and
 * outstanding.
 *
 *   billed      all charges raised
 *   settled     the part of those charges that has been paid
 *   outstanding charges still unpaid            = billed - settled
 *   overdue     outstanding on bills past their due date
 *   arrears     outstanding on months before the current one
 *   collected   cash received (settled payments), incl. deposits/prepayments
 *   credit      cash received but not yet applied to any charge
 */
final class PortfolioTotals
{
    /**
     * @param  Builder  $bills     an already-scoped Bill query (owner/caretaker scope)
     * @param  Builder  $payments  an already-scoped Payment query
     * @return array<string, string>
     */
    public static function compute(Builder $bills, Builder $payments): array
    {
        $settled = BillSnapshotBuilder::SETTLED;
        $today = CarbonImmutable::today()->toDateString();
        $month = CarbonImmutable::now()->format('Y-m');

        $paid = DB::table('payment_allocations as pa')
            ->join('payments as p', 'p.id', '=', 'pa.payment_id')
            ->whereIn('p.status', $settled)
            ->groupBy('pa.bill_item_id')
            ->selectRaw('pa.bill_item_id, SUM(pa.amount) AS paid');

        $owed = 'GREATEST(bi.amount - COALESCE(x.paid, 0), 0)';
        $cleared = 'LEAST(bi.amount, COALESCE(x.paid, 0))';

        $row = DB::table('bill_items as bi')
            ->join('bills as b', 'b.id', '=', 'bi.bill_id')
            ->leftJoinSub($paid, 'x', 'x.bill_item_id', '=', 'bi.id')
            ->whereIn('b.id', (clone $bills)->select('id'))
            ->selectRaw(
                "COALESCE(SUM(bi.amount),0) AS billed,
                 COALESCE(SUM({$cleared}),0) AS settled,
                 COALESCE(SUM({$owed}),0) AS outstanding,
                 COALESCE(SUM(CASE WHEN b.due_date < ? THEN {$owed} ELSE 0 END),0) AS overdue,
                 COALESCE(SUM(CASE WHEN b.month < ? THEN {$owed} ELSE 0 END),0) AS arrears,
                 COALESCE(SUM(CASE WHEN b.month = ? THEN bi.amount ELSE 0 END),0) AS month_billed,
                 COALESCE(SUM(CASE WHEN b.month = ? THEN {$cleared} ELSE 0 END),0) AS month_settled",
                [$today, $month, $month, $month]
            )
            ->first();

        $collected = Amount::of((clone $payments)->whereIn('status', $settled)->sum('amount'));
        $settledMoney = Amount::of($row->settled ?? '0');
        $monthBilled = Amount::of($row->month_billed ?? '0');
        $monthSettled = Amount::of($row->month_settled ?? '0');

        return [
            'billed' => Amount::toString(Amount::of($row->billed ?? '0')),
            'settled' => Amount::toString($settledMoney),
            'outstanding' => Amount::toString(Amount::of($row->outstanding ?? '0')),
            'overdue' => Amount::toString(Amount::of($row->overdue ?? '0')),
            'arrears' => Amount::toString(Amount::of($row->arrears ?? '0')),
            'collected' => Amount::toString($collected),
            'credit' => Amount::toString(Amount::atLeastZero($collected->minus($settledMoney))),
            'month_billed' => Amount::toString($monthBilled),
            'month_settled' => Amount::toString($monthSettled),
            'month_outstanding' => Amount::toString(Amount::atLeastZero($monthBilled->minus($monthSettled))),
        ];
    }
}
