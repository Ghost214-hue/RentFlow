<?php

declare(strict_types=1);

namespace App\Reports;

use App\Support\Amount;
use App\Support\Rate;
use Brick\Money\Money;

/**
 * Money in: what was collected, by what route, and what is still in flight.
 *
 * Ported from the legacy FinancialReportController, which broke revenue down by
 * method and by type. Both matter to an owner: the method split decides where to
 * put collection effort (M-Pesa reconciles, cash does not), and rent versus
 * deposit versus maintenance income is not the same kind of money.
 *
 * Failed and pending payments are reported separately from settled. Folding them
 * into revenue is how a portfolio looks solvent while the rent is unpaid.
 */
final class RevenueReport
{
    /** Payment statuses that represent settled money. */
    private const SETTLED = ['completed', 'confirmed', 'paid'];

    public function __construct(private readonly ReportScope $scope) {}

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $collected = $this->sumWhere(['completed', 'confirmed', 'paid']);
        $pending = $this->sumWhere(['pending']);
        $failed = $this->sumWhere(['failed']);

        return [
            'collected' => Amount::toString($collected),
            'pending' => Amount::toString($pending),
            'failed' => Amount::toString($failed),
            'in_flight' => Amount::toString($pending->plus($failed)),
            'counts' => [
                'settled' => $this->countWhere(['completed', 'confirmed', 'paid']),
                'pending' => $this->countWhere(['pending']),
                'failed' => $this->countWhere(['failed']),
            ],
            // Share of SETTLED money, so pending and failed cannot inflate it.
            'method_share' => $this->breakdown('method', $collected),
            'type_breakdown' => $this->breakdown('type', $collected),
        ];
    }

    /**
     * Settled money grouped by a payment column, with each group's share.
     *
     * @return array<int, array{label: string, amount: string, count: int, share: string}>
     */
    private function breakdown(string $column, Money $total): array
    {
        $rows = $this->scope->payments()
            ->whereIn('status', self::SETTLED)
            ->whereNotNull($column)
            ->selectRaw($column.' AS label, SUM(amount) AS amount, COUNT(*) AS total')
            ->groupBy($column)
            ->orderByDesc('amount')
            ->get();

        return $rows->map(fn (object $row): array => [
            'label' => (string) $row->label,
            'amount' => Amount::toString(Amount::of($row->amount)),
            'count' => (int) $row->total,
            'share' => Rate::of(Amount::of($row->amount), $total),
        ])->all();
    }

    private function sumWhere(array $statuses): Money
    {
        return Amount::sum(
            $this->scope->payments()->whereIn('status', $statuses)->pluck('amount')
        );
    }

    private function countWhere(array $statuses): int
    {
        return (int) $this->scope->payments()->whereIn('status', $statuses)->count();
    }
}
