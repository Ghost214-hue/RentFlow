<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreHouseRequest;
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