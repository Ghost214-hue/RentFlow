<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreHouseRequest;
use App\Models\Bill;
use App\Models\MaintenanceRecord;
use App\Support\Amount;
use App\Http\Resources\HouseResource;
use App\Models\Caretaker;
use App\Models\House;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Houses & Units.
 *
 * The tenant scope comes from the BelongsToOwner global scope; the caretaker
 * restriction (assigned properties only) is applied HERE and in HousePolicy.
 */
class HouseController extends Controller
{
    /**
     * Unit detail.
     *
     * Ported from frontend/pages/house-details.php: unit facts, the current
     * tenant, meter numbers, bill history, and open maintenance. Renters never
     * reach this page -- it is an owner/caretaker view of someone else's home.
     */
    public function show(Request $request, House $house): Response
    {
        $actor = $request->user();
        $this->authorize('view', $house);

        $house->load(['property:id,name,address', 'renter:id,name,email,phone,status,profile_picture']);

        /*
         * HousePolicy lets a renter view the unit they occupy. The bill and
         * maintenance history must therefore be scoped to THEM, not to the
         * unit: a re-let unit still holds the previous tenant's bills, and
         * showing those would expose another household's charges.
         *
         * Staff see the whole unit history.
         */
        $viewerIsStaff = ! $actor instanceof \App\Models\Renter;

        $billQuery = $house->bills()
            ->when(! $viewerIsStaff, fn ($q) => $q->where('tenant_id', $actor->getKey()));

        $maintenanceQuery = $house->maintenance()
            ->when(! $viewerIsStaff, fn ($q) => $q->where('tenant_id', $actor->getKey()));

        $bills = $billQuery
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->map(fn (Bill $b) => [
                'id' => (int) $b->getKey(),
                'month' => $b->month,
                'total' => Amount::toString(Amount::of($b->total)),
                'status' => $b->status instanceof \BackedEnum ? $b->status->value : (string) $b->status,
                'due_date' => $b->due_date?->toDateString(),
            ]);

        $maintenance = $maintenanceQuery
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn (MaintenanceRecord $m) => [
                'id' => (int) $m->getKey(),
                'title' => (string) $m->title,
                'status' => $m->status instanceof \BackedEnum ? $m->status->value : (string) $m->status,
                'priority' => $m->priority instanceof \BackedEnum ? $m->priority->value : (string) $m->priority,
                'cost' => Amount::toString(Amount::of($m->cost)),
                'created_at' => $m->created_at?->toIso8601String(),
            ]);

        // Outstanding is derived from the ledger, not the cached house/renter columns.
        $unpaidTotal = Amount::sum(
            $billQuery->whereIn('status', ['pending', 'partial', 'overdue'])->pluck('total')
        );

        return Inertia::render('Houses/Show', [
            'house' => [
                'id' => (int) $house->getKey(),
                'unit' => (string) $house->unit,
                'type' => $house->type,
                'status' => $house->status instanceof \BackedEnum ? $house->status->value : (string) $house->status,
                'rent' => Amount::toString(Amount::of($house->rent)),
                'water_meter' => $house->water_meter,
                'elec_meter' => $house->elec_meter,
                'last_reading' => Amount::toString(Amount::of($house->last_reading)),
                'property_id' => (int) $house->property_id,
                'property_name' => $house->property?->name,
                'property_address' => $house->property?->address,
            ],
            'currentTenant' => $house->renter === null ? null : [
                'id' => (int) $house->renter->getKey(),
                'name' => (string) $house->renter->name,
                'email' => $house->renter->email,
                'phone' => $house->renter->phone,
                'status' => $house->renter->status instanceof \BackedEnum
                    ? $house->renter->status->value
                    : (string) $house->renter->status,
                'profile_picture' => $house->renter->profile_picture,
            ],
            'financials' => [
                'unpaid_total' => Amount::toString($unpaidTotal),
            ],
            'bills' => $bills,
            'maintenance' => $maintenance,
        ]);
    }

    public function index(Request $request): Response
    {
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = max(5, min($perPage, 100));

        $query = House::query()
            // preventLazyLoading() is on outside production; be explicit.
            ->with(['property:id,name', 'renter:id,name'])
            ->when(
                $request->filled('property_id'),
                fn ($q) => $q->where('property_id', $request->integer('property_id')),
            )
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where('unit', 'like', '%'.$request->string('search').'%'),
            );

        // A caretaker only ever sees their assigned properties.
        $actor = $request->user();

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();

            $query->whereIn('property_id', $ids === [] ? [0] : $ids);
        }

        $houses = $query->orderBy('unit')->paginate($perPage)->withQueryString();

        return Inertia::render('Houses/Index', [
            'houses' => HouseResource::collection($houses),
            'properties' => $this->availablePropertiesFor($actor),
            'filters' => [
                'property_id' => $request->integer('property_id') ?: null,
                'search' => $request->string('search')->toString() ?: null,
            ],
        ]);
    }

    public function store(StoreHouseRequest $request): RedirectResponse
    {
        $house = House::create($request->validated());

        return back()->with('success', "Unit {$house->unit} added.");
    }

    public function update(StoreHouseRequest $request, House $house): RedirectResponse
    {
        $house->update($request->validated());

        return back()->with('success', "Unit {$house->unit} updated.");
    }

    public function destroy(Request $request, House $house): RedirectResponse
    {
        $this->authorize('delete', $house);

        $unit = $house->unit;
        $house->delete();

        return back()->with('success', "Unit {$unit} deleted.");
    }

    /**
     * Properties the actor is allowed to assign a unit to.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function availablePropertiesFor(mixed $actor): array
    {
        $query = Property::query()->select(['id', 'name'])->orderBy('name');

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereIn('id', $ids === [] ? [0] : $ids);
        }

        return $query->get()
            ->map(fn (Property $p): array => [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
            ])
            ->all();
    }
}