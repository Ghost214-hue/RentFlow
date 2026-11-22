<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Billing\BillSnapshotBuilder;
use App\Billing\PortfolioTotals;
use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\Expense;
use App\Models\House;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Renter;
use App\Support\Amount;
use App\Support\Rate;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The root landing page. Dispatched by role.
 *
 * Previously this rendered the owner dashboard for everyone who could pass
 * PropertyPolicy::viewAny -- which includes caretakers. That handed a caretaker
 * the owner's whole portfolio: every property, every bill total, every payment.
 *
 * Each role now gets its own landing page:
 *   owner     -> the portfolio dashboard
 *   caretaker -> CaretakerDashboardController, scoped to assigned properties
 *   renter    -> the renter's own dashboard
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $actor = $request->user();

        // Staff landing pages are separate controllers with their own scoping.
        if ($actor instanceof Caretaker) {
            return redirect()->route('caretaker.dashboard');
        }

        if ($actor instanceof Renter) {
            return redirect()->route('renter.dashboard');
        }

        $this->authorize('viewAny', Property::class);

        return Inertia::render('Dashboard/Index', [
            'stats' => $this->ownerStats(),
        ]);
    }

    /** @return array<string, mixed> */
    private function ownerStats(): array
    {
        // Money is aggregated in SQL (never in the browser) and emitted as
        // strings. Amount::of() normalises the SUM() result, which MySQL may
        // return as a float for a DECIMAL column.
        // One ledger-based calculation feeds every card, so billed, collected,
        // outstanding, overdue and arrears always agree with each other and
        // with the bills page.
        $totals = PortfolioTotals::compute(Bill::query(), Payment::query());
        $expenses = Amount::sum([Expense::query()->sum('amount')]);

        $units = House::query()->selectRaw(
            "COUNT(*) AS total,
             SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) AS occupied"
        )->first();

        $total = (int) ($units->total ?? 0);
        $occupied = (int) ($units->occupied ?? 0);

        return [
            'properties' => Property::query()->count(),
            'units' => $total,
            'occupiedUnits' => $occupied,
            'vacantUnits' => max(0, $total - $occupied),
            'expected' => $totals['billed'],
            'collected' => $totals['collected'],
            'expenses' => Amount::toString($expenses),
            'outstanding' => $totals['outstanding'],
            'overdue' => $totals['overdue'],
            'arrears' => $totals['arrears'],
            'credit' => $totals['credit'],
            'monthly' => $this->monthlyStats($totals),
        ];
    }

    /**
     * @return array{
     *     months: array<int, array{label: string, billed: string, collected: string, billedHeight: string, collectedHeight: string}>,
     *     current: array{label: string, billed: string, collected: string, outstanding: string, arrears: string, overdue: string, collectionRate: string}
     * }
     */
    private function monthlyStats(array $totals): array
    {
        $currentMonth = CarbonImmutable::now()->startOfMonth();
        $months = collect(range(5, 0))
            ->map(fn (int $offset): CarbonImmutable => $currentMonth->subMonths($offset));
        $keys = $months->map(fn (CarbonImmutable $month): string => $month->format('Y-m'));
        $firstMonth = $keys->first();
        $lastMonth = $keys->last();

        $billedByMonth = Bill::query()
            ->whereBetween('month', [$firstMonth, $lastMonth])
            ->selectRaw('month, SUM(total) AS amount')
            ->groupBy('month')
            ->pluck('amount', 'month');

        $collectedByMonth = Payment::query()
            ->whereIn('status', BillSnapshotBuilder::SETTLED)
            ->whereBetween('date', [
                $currentMonth->subMonths(5)->toDateString(),
                $currentMonth->endOfMonth()->toDateString(),
            ])
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') AS period, SUM(amount) AS amount")
            ->groupBy('period')
            ->pluck('amount', 'period');

        $monthlyAmounts = $months->map(function (CarbonImmutable $month) use ($billedByMonth, $collectedByMonth): array {
            $key = $month->format('Y-m');

            return [
                'month' => $month,
                'billed' => Amount::of($billedByMonth->get($key, '0.00')),
                'collected' => Amount::of($collectedByMonth->get($key, '0.00')),
            ];
        });

        $maxBilled = Amount::zero();
        $maxCollected = Amount::zero();
        foreach ($monthlyAmounts as $amounts) {
            if ($amounts['billed']->compareTo($maxBilled) > 0) {
                $maxBilled = $amounts['billed'];
            }
            if ($amounts['collected']->compareTo($maxCollected) > 0) {
                $maxCollected = $amounts['collected'];
            }
        }

        $trend = $monthlyAmounts->map(fn (array $amounts): array => [
            'label' => $amounts['month']->format('M'),
            'billed' => Amount::toString($amounts['billed']),
            'collected' => Amount::toString($amounts['collected']),
            'billedHeight' => Rate::of($amounts['billed'], $maxBilled),
            'collectedHeight' => Rate::of($amounts['collected'], $maxCollected),
        ])->all();

        $current = $monthlyAmounts->last();

        return [
            'months' => $trend,
            'current' => [
                'label' => $currentMonth->format('F Y'),
                // This month's charges vs the money allocated to them. Arrears
                // from earlier months are reported separately, never mixed in.
                'billed' => $totals['month_billed'],
                'collected' => $totals['month_settled'],
                'outstanding' => $totals['month_outstanding'],
                'arrears' => $totals['arrears'],
                'overdue' => $totals['overdue'],
                'collectionRate' => Rate::of(Amount::of($totals['month_settled']), Amount::of($totals['month_billed'])),
            ],
        ];
    }
}