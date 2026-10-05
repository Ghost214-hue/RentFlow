<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Properties — the landlord's buildings.
 *
 * Ported from PropertyController and frontend/pages/properties.php.
 *
 * Unit counts are always recomputed from the house rows. properties.units and
 * properties.occupied are caches that drift whenever a unit is added or vacated
 * outside this form, so they are written for compatibility but never trusted
 * for display.
 */
class PropertyController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Property::class);

        $perPage = max(5, min((int) $request->integer('per_page', 15), 100));
        $actor = $request->user();

        $query = Property::query()
            ->with(['houses:id,property_id,status', 'caretaker:id,name'])
            ->when($request->filled('search'), fn ($q) => $q
                ->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('payment_method_type'), fn ($q) => $q
                ->where('payment_method_type', $request->string('payment_method_type')));

        // A caretaker only sees the properties they are assigned to.
        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereIn('id', $ids === [] ? [0] : $ids);
        }

        $properties = $query->orderBy('name')
            ->paginate($perPage)->withQueryString();

        return Inertia::render('Properties/Index', [
            'properties' => [
                'data' => PropertyResource::collection($properties->items())->resolve(),
                'links' => [
                    'first' => $properties->url(1),
                    'last' => $properties->url($properties->lastPage()),
                    'prev' => $properties->previousPageUrl(),
                    'next' => $properties->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $properties->currentPage(),
                    'from' => $properties->firstItem(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage(),
                    'to' => $properties->lastItem(),
                    'total' => $properties->total(),
                ],
            ],
            'caretakers' => $this->caretakersFor(),
            'canManage' => $actor instanceof Owner,
            'filters' => [
                'search' => $request->string('search')->toString() ?: null,
                'payment_method_type' => $request->string('payment_method_type')->toString() ?: null,
            ],
            'flash' => ['success' => $request->session()->get('success')],
        ]);
    }

    public function store(StorePropertyRequest $request): RedirectResponse
    {
        $property = Property::create($request->validated());

        return back()->with('success', "{$property->name} added.");
    }

    public function update(StorePropertyRequest $request, Property $property): RedirectResponse
    {
        $property->update($request->validated());

        return back()->with('success', "{$property->name} updated.");
    }

    public function destroy(Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);

        $name = $property->name;

        /*
         * A property cannot be deleted while it still has units: the houses
         * rows would be orphaned and the units would vanish from every report.
         * Vacant or reassign them first.
         */
        if ($property->houses()->exists()) {
            return back()->with('error', "{$name} still has units. Delete or move them first.");
        }

        $property->delete();

        return back()->with('success', "{$name} removed.");
    }

    /** @return array<int, array{id: int, name: string}> */
    private function caretakersFor(): array
    {
        return Caretaker::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn (Caretaker $c) => ['id' => (int) $c->getKey(), 'name' => (string) $c->name])
            ->all();
    }
}