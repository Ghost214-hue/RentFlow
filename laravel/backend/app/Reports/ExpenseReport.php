<?php

declare(strict_types=1);

namespace App\Reports;

use App\Models\Expense;
use App\Support\Amount;
use App\Support\Rate;
use Brick\Money\Money;

/**
 * Money going out: total expenses, split by category and by month.
 *
 * Expenses are owner-scoped via the BelongsToOwner global scope on the
 * Expense model, so a plain query() is already limited to the authenticated
 * actor's owner. ReportScope is accepted for interface consistency with the
 * other report classes.
 */
final class ExpenseReport
{
    public function __construct(private readonly ReportScope $scope) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $total = Amount::sum(Expense::query()->pluck('amount'));

        return [
            'total' => Amount::toString($total),
            'by_category' => $this->byCategory(),
            'by_month' => $this->byMonth(),
        ];
    }

    /**
     * @return array<int, array{label: string, amount: string, count: int, share: string}>
     */
    private function byCategory(): array
    {
        $rows = Expense::query()
            ->selectRaw('category, SUM(amount) AS amount, COUNT(*) AS total')
            ->groupBy('category')
            ->orderByDesc('amount')
            ->get();

        $total = Amount::sum($rows->pluck('amount'));

        return $rows->map(fn (object $row): array => [
            'label' => ucfirst((string) $row->category),
            'amount' => Amount::toString(Amount::of($row->amount)),
            'count' => (int) $row->total,
            'share' => $total->isZero() ? '0.00' : Rate::of(Amount::of($row->amount), $total),
        ])->all();
    }

    /**
     * Last 12 months that have expenses, newest first.
     *
     * @return array<int, array{month: string, amount: string, count: int}>
     */
    private function byMonth(): array
    {
        $rows = Expense::query()
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") AS month, SUM(amount) AS amount, COUNT(*) AS total')
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get();

        return $rows->map(fn (object $row): array => [
            'month' => (string) $row->month,
            'amount' => Amount::toString(Amount::of($row->amount)),
            'count' => (int) $row->total,
        ])->all();
    }
}
