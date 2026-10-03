<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Reports\ComplaintsReport;
use App\Reports\PortfolioReport;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Reports.
 *
 * Ported from ReportsController, BillsReportController and
 * ComplaintsReportController. There is no reports table: every figure is
 * computed from the bill and allocation ledger at read time.
 *
 * A renter may not reach these pages at all: the portfolio aggregates would
 * expose another household's finances and disputes.
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly PortfolioReport $portfolio,
        private readonly ComplaintsReport $complaints,
    ) {}

    public function index(Request $request): Response
    {
        $actor = $request->user();

        // Reports are portfolio analytics: staff only.
        abort_if($actor instanceof Renter, 403);

        return Inertia::render('Reports/Index', [
            'summary' => $this->portfolio->summary(),
            'monthly' => $this->portfolio->monthly(),
            'topDebtors' => $this->portfolio->topDebtors(),
            'complaints' => $this->complaints->summary($actor),
        ]);
    }
}