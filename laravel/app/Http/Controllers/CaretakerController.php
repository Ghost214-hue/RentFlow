<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaretakerRequest;
use App\Http\Resources\CaretakerResource;
use App\Models\Caretaker;
use App\Models\Owner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Caretakers — staff accounts limited to an assigned set of properties.
 *
 * Ported from CaretakerController. The assigned_properties CSV is parsed into
 * a real list for the UI and re-joined on save, so no client ever handles a
 * comma-separated string.
 */
class CaretakerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Caretaker::class);

        $caretakers = Caretaker::query()->orderBy('name')->get();

        return Inertia::render('Caretakers/Index', [
            'caretakers' => CaretakerResource::collection($caretakers)->resolve(),
            'properties' => $this->propertiesFor(),
            'flash' => ['success' => $request->session()->get('success')],
        ]);
    }

    public function store(StoreCaretakerRequest $request): RedirectResponse
    {
        $attributes = $request->caretakerAttributes();

        // A caretaker without a password cannot sign in yet; the owner sends
        // a setup link (ported from the legacy resend-setup-link flow).
        $attributes['password'] = ($attributes['password'] ?? null) !== null
            ? Hash::make($attributes['password'])
            : Hash::make(bin2hex(random_bytes(16)));

        $caretaker = Caretaker::create($attributes);

        return back()->with('success', "{$caretaker->name} added as a caretaker.");
    }

    public function update(StoreCaretakerRequest $request, Caretaker $caretaker): RedirectResponse
    {
        $attributes = $request->caretakerAttributes();

        // Only rehash when a new password was actually supplied.
        if (($attributes['password'] ?? null) !== null) {
            $attributes['password'] = Hash::make($attributes['password']);
        } else {
            unset($attributes['password']);
        }

        $caretaker->update($attributes);

        return back()->with('success', "{$caretaker->name} updated.");
    }

    public function destroy(Caretaker $caretaker): RedirectResponse
    {
        $this->authorize('delete', $caretaker);

        $name = $caretaker->name;
        $caretaker->delete();

        return back()->with('success', "{$name} removed.");
    }

    /** @return array<int, array{id: int, name: string}> */
    private function propertiesFor(): array
    {
        return \App\Models\Property::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => ['id' => (int) $p->id, 'name' => (string) $p->name])
            ->all();
    }
}