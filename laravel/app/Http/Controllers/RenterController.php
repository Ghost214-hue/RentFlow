<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\OnboardRenter;
use App\Models\Bill;
use App\Models\Complaint;
use App\Models\MaintenanceRecord;
use App\Models\Payment;
use App\Support\Amount;
use App\Http\Requests\StoreRenterRequest;
use App\Http\Resources\RenterResource;
use App\Models\Caretaker;
use App\Models\House;
use App\Models\Property;
use App\Models\Renter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renters (DB table `tenants`).
 *
 * Tenancy comes from the BelongsToOwner global scope; the caretaker
 * restriction (assigned properties only) is applied here and in RenterPolicy.
 */
class RenterController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Renter::class);

        $perPage = max(5, min((int) $request->integer('per_page', 15), 100));
        $actor = $request->user();

        $query = Renter::query()
            ->with(['property:id,name', 'house:id,unit,rent'])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('property_id'), fn ($q) => $q->where('property_id', $request->integer('property_id')));

        // A renter has no list view; they only see their own record.
        if ($actor instanceof Renter) {
            $query->whereKey($actor->getKey());
        }

        // A caretaker only ever sees renters in their assigned properties.
        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereIn('property_id', $ids === [] ? [0] : $ids);
        }

        $renters = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return Inertia::render('Renters/Index', [
            'renters' => RenterResource::collection($renters),
            'properties' => $this->propertiesFor($actor),
            'vacantHouses' => $this->vacantHousesFor($actor),
            'filters' => [
                'search' => $request->string('search')->toString() ?: null,
                'status' => $request->string('status')->toString() ?: null,
                'property_id' => $request->integer('property_id') ?: null,
            ],
        ]);
    }

    public function store(StoreRenterRequest $request): RedirectResponse
    {
        // OnboardRenter runs the whole thing in one transaction: renter row,
        // atomic unit claim, occupied counter, and the first bill.
        $renter = (new OnboardRenter())->handle($request->renterAttributes());
        $unit = $renter->load('house')->house?->unit ?? 'their unit';

        return back()->with('success', "{$renter->name} added to unit {$unit}.");
    }

    public function update(StoreRenterRequest $request, Renter $renter): RedirectResponse
    {
        $attributes = $request->renterAttributes();
        $houseId = (int) $attributes['house_id'];
        $houseChanged = $houseId !== (int) $renter->house_id;

        $renter->update($attributes);

        // Moving a renter to another unit: release the old, claim the new.
        if ($houseChanged) {
            $this->releaseUnit((int) $renter->getRawOriginal('house_id'));
            (new OnboardRenter())->reassignUnit($renter, $houseId, (string) $attributes['rent']);
        }

        return back()->with('success', "{$renter->name} updated.");
    }

    public function show(Request $request, Renter $renter): Response
    {
        $this->authorize('view', $renter);

        $renter->load(['property:id,name', 'house:id,unit,rent']);

        /*
         * History for the detail page. Ported from tenant-details.php, which
         * showed receipts, bills, complaints, maintenance and documents.
         *
         * Every figure is derived from the ledger; tenants.balance is a cache
         * that drifts and is not trusted here.
         */
        $bills = $renter->bills()
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

        $payments = $renter->payments()
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->map(fn (Payment $p) => [
                'id' => (int) $p->getKey(),
                'amount' => Amount::toString(Amount::of($p->amount)),
                'method' => $p->method,
                'status' => $p->status instanceof \BackedEnum ? $p->status->value : (string) $p->status,
                'date' => $p->date?->toDateString(),
            ]);

        $complaints = $renter->complaints()
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn (Complaint $c) => [
                'id' => (int) $c->getKey(),
                'title' => (string) $c->title,
                'status' => $c->status instanceof \BackedEnum ? $c->status->value : (string) $c->status,
                'created_at' => $c->created_at?->toIso8601String(),
            ]);

        $maintenance = $renter->maintenance()
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn (MaintenanceRecord $m) => [
                'id' => (int) $m->getKey(),
                'title' => (string) $m->title,
                'status' => $m->status instanceof \BackedEnum ? $m->status->value : (string) $m->status,
                'priority' => $m->priority instanceof \BackedEnum ? $m->priority->value : (string) $m->priority,
                'created_at' => $m->created_at?->toIso8601String(),
            ]);

        // Outstanding from unpaid bills, not the drifting balance cache.
        $unpaid = Amount::sum(
            $renter->bills()->whereIn('status', ['pending', 'partial', 'overdue'])->pluck('total')
        );

        return Inertia::render('Renters/Show', [
            'renter' => RenterResource::make($renter)->resolve(),
            'history' => [
                'bills' => $bills,
                'payments' => $payments,
                'complaints' => $complaints,
                'maintenance' => $maintenance,
                'unpaid_total' => Amount::toString($unpaid),
            ],
            'flash' => ['success' => $request->session()->get('success')],
        ]);
    }

    public function destroy(Renter $renter): RedirectResponse
    {
        // Policy refuses while the renter still owes money, so their bills
        // and payment history are never orphaned.
        $this->authorize('delete', $renter);

        $name = $renter->name;
        $houseId = (int) $renter->house_id;
        $propertyId = (int) $renter->property_id;

        $renter->delete();
        $this->releaseUnit($houseId, $propertyId);

        return back()->with('success', "{$name} removed.");
    }

    /**
     * Free a unit back to vacant and recompute the property's occupied count
     * from the houses table (never a blind increment, which drifts).
     */
    private function releaseUnit(int $houseId, ?int $propertyId = null): void
    {
        if ($houseId <= 0) {
            return;
        }

        House::query()
            ->whereKey($houseId)
            ->where('status', 'occupied')
            ->update(['status' => 'vacant', 'tenant_id' => null]);

        $propertyId ??= (int) House::withoutOwnerScope()
            ->whereKey($houseId)
            ->value('property_id');

        if ($propertyId > 0) {
            $occupied = House::query()
                ->where('property_id', $propertyId)
                ->where('status', 'occupied')
                ->count();

            Property::query()->whereKey($propertyId)->update(['occupied' => $occupied]);
        }
    }

    /** @return array<int, array{id: int, name: string}> */
    private function propertiesFor(mixed $actor): array
    {
        $query = Property::query()->select(['id', 'name'])->orderBy('name');

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereIn('id', $ids === [] ? [0] : $ids);
        }

        return $query->get()
            ->map(fn (Property $p): array => ['id' => (int) $p->id, 'name' => (string) $p->name])
            ->all();
    }

    /** @return array<int, array{id: int, property_id: int, unit: string, rent: string}> */
    private function vacantHousesFor(mixed $actor): array
    {
        $query = House::query()
            ->select(['id', 'property_id', 'unit', 'rent'])
            ->where('status', 'vacant')
            ->orderBy('unit');

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereIn('property_id', $ids === [] ? [0] : $ids);
        }

        return $query->get()
            ->map(fn (House $h): array => [
                'id' => (int) $h->id,
                'property_id' => (int) $h->property_id,
                'unit' => (string) $h->unit,
                'rent' => (string) $h->rent,
            ])
            ->all();
    }
}