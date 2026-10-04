<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Renter;
use App\Reports\BillingReport;
use App\Reports\ComplaintsReport;
use App\Reports\MaintenanceReport;
use App\Reports\OccupancyReport;
use App\Reports\PortfolioReport;
use App\Reports\RevenueReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Reports.
 *
 * This page consolidates the five separate report screens the legacy backend
 * exposed -- portfolio, bills, financial, complaints and tenancy/vacancy -- into
 * one, because an owner asking "how is the portfolio doing" was previously
 * forced to cross-reference five pages to answer it.
 *
 *   PortfolioReport    <- ReportsController          monthly revenue, per-property
 *                                                   performance, collection rate
 *   BillingReport      <- BillsReportController      bill status split, arrears aging
 *   RevenueReport      <- FinancialReportController  method and type breakdown
 *   OccupancyReport    <- TenancyVacancyReport...    occupancy, vacancy, terminations
 *   ComplaintsReport   <- ComplaintsReportController status, priority, resolution
 *
 * MaintenanceReport is new: the legacy app had maintenance endpoints but no
 * aggregate, so an owner could see one repair's cost but never the running total.
 *
 * There is no reports table: every figure is computed from the bill and
 * allocation ledger at read time. Money is Brick\Money end to end, so a report
 * can never disagree with what a renter was actually charged.
 *
 * A renter may not reach this page at all: the aggregates would expose another
 * household's finances and disputes. A caretaker sees only their assigned
 * properties, via ReportScope.
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly PortfolioReport $portfolio,
        private readonly BillingReport $billing,
        private readonly RevenueReport $revenue,
        private readonly OccupancyReport $occupancy,
        private readonly ComplaintsReport $complaints,
        private readonly MaintenanceReport $maintenance,
    ) {}

    public function index(Request $request): Response
    {
        $actor = $request->user();

        // Reports are portfolio analytics: staff only.
        abort_if($actor instanceof Renter, 403);

        // One month drives every time-scoped figure, so the tiles, the chart and
        // the per-unit table cannot disagree about which month they describe.
        $month = $request->string('month')->trim()->value();
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }

        return Inertia::render('Reports/Index', [
            'month' => $month,
            'months' => $this->availableMonths(),
            'summary' => $this->portfolio->summary(),
            'monthly' => $this->portfolio->monthly(),
            'topDebtors' => $this->portfolio->topDebtors(),
            'propertyPerformance' => $this->portfolio->propertyPerformance($month),
            'billing' => $this->billing->summary(),
            'composition' => $this->billing->composition($month),
            'byHouse' => $this->billing->byHouse($month),
            'revenue' => $this->revenue->summary(),
            'occupancy' => $this->occupancy->summary(),
            'occupancyByProperty' => $this->occupancy->byProperty(),
            'vacantUnits' => $this->occupancy->vacantUnits(),
            'complaints' => $this->complaints->summary($actor),
            'maintenance' => $this->maintenance->summary(),
        ]);
    }

    /**
     * Months that actually have bills, newest first, so the filter cannot offer
     * an empty month.
     *
     * @return array<int, string>
     */
    private function availableMonths(): array
    {
        return Bill::query()
            ->distinct()
            ->orderByDesc('month')
            ->limit(18)
            ->pluck('month')
            ->map(fn ($m): string => (string) $m)
            ->all();
    }
}
