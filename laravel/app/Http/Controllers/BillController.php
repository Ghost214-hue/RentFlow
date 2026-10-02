<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GenerateMonthlyBills;
use App\Billing\BillSnapshotBuilder;
use App\Http\Requests\GenerateBillsRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\House;
use App\Models\Renter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Bills.
 *
 * Every displayed figure comes from BillSnapshot, so this list, the invoice,
 * the billing email, the renter portal and the reports always agree.
 */
class BillController extends Controller
{
    public function __construct(
        private readonly BillSnapshotBuilder $snapshots,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Bill::class);

        $perPage = max(5, min((int) $request->integer('per_page', 15), 100));
        $actor = $request->user();
        $month = $request->string('month')->toString() ?: now()->format('Y-m');

        $query = Bill::query()
            ->with(['items', 'house:id,unit,property_id,rent', 'house.property:id,name', 'renter:id,name'])
            ->where('month', $month)
            ->when($request->filled('property_id'), fn ($q) => $q->whereHas(
                'house',
                fn ($h) => $h->where('property_id', $request->integer('property_id')),
            ))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));

        if ($actor instanceof Renter) {
            $query->where('tenant_id', $actor->getKey());
        }

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereHas('house', fn ($h) => $h->whereIn('property_id', $ids === [] ? [0] : $ids));
        }

        $bills = $query->orderBy('month')->orderBy('id')->paginate($perPage)->withQueryString();

        // Keep the paginator envelope and the resource shape separate: mixing
        // them via ->additional() collapses the paginator into a Collection.
        return Inertia::render('Bills/Index', [
            'bills' => [
                'data' => BillResource::collection($bills->items())->resolve(),
                'links' => [
                    'first' => $bills->url(1),
                    'last' => $bills->url($bills->lastPage()),
                    'prev' => $bills->previousPageUrl(),
                    'next' => $bills->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $bills->currentPage(),
                    'from' => $bills->firstItem(),
                    'last_page' => $bills->lastPage(),
                    'per_page' => $bills->perPage(),
                    'to' => $bills->lastItem(),
                    'total' => $bills->total(),
                ],
            ],
            'months' => $this->monthsFor($actor),
            'properties' => $this->propertiesFor($actor),
            'filters' => [
                'month' => $month,
                'property_id' => $request->integer('property_id') ?: null,
                'status' => $request->string('status')->toString() ?: null,
            ],
            'canGenerate' => $actor instanceof \App\Models\Owner || $actor instanceof Caretaker,
            'flash' => ['success' => $request->session()->get('success')],
        ]);
    }

    public function generate(GenerateBillsRequest $request): RedirectResponse
    {
        $actor = $request->user();

        // A renter can only ever generate their OWN bill; owners and
        // caretakers cover their whole (assigned) portfolio.
        $onlyRenter = $actor instanceof Renter ? (int) $actor->getKey() : null;

        $result = (new GenerateMonthlyBills())->handle(
            propertyId: $request->integer('property_id') ?: null,
            month: $request->string('month')->toString() ?: null,
            dueDate: $request->string('due_date')->toString() ?: null,
            utilityCharges: $request->utilityCharges(),
            onlyRenterId: $onlyRenter,
        );

        return back()->with(
            'success',
            $result['generated'] === 0
                ? 'No new bills needed — everything for this month already exists.'
                : sprintf(
                    '%d bill%s generated, %d already existed and were left untouched.',
                    $result['generated'],
                    $result['generated'] === 1 ? '' : 's',
                    $result['skipped'],
                ),
        );
    }

    /** @return array<int, string> */
    private function monthsFor(mixed $actor): array
    {
        $query = Bill::query()->select('month')->distinct()->orderByDesc('month');

        if ($actor instanceof Renter) {
            $query->where('tenant_id', $actor->getKey());
        }

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereHas('house', fn ($h) => $h->whereIn('property_id', $ids === [] ? [0] : $ids));
        }

        return $query->pluck('month')->all();
    }

    /** @return array<int, array{id: int, name: string}> */
    private function propertiesFor(mixed $actor): array
    {
        $query = \App\Models\Property::query()->select(['id', 'name'])->orderBy('name');

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereIn('id', $ids === [] ? [0] : $ids);
        }

        return $query->get()->map(fn ($p) => ['id' => (int) $p->id, 'name' => (string) $p->name])->all();
    }
}