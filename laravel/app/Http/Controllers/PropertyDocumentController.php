<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\PropertyDocument;
use App\Models\Renter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Rules and documents published to renters.
 *
 * Ported from DocumentController and frontend/pages/documents.php, which the
 * sidebar labels "Rules".
 *
 * A renter may READ the active documents for their own property but may never
 * create, edit or delete one: these are the landlord's terms.
 */
class PropertyDocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $actor = $request->user();

        $query = PropertyDocument::query()
            ->with('property:id,name')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('property_id'), fn ($q) => $q->where('property_id', $request->integer('property_id')));

        if ($actor instanceof Renter) {
            /*
             * A renter sees only ACTIVE documents, and only for their own
             * property. A draft or retired rule is not binding on them, and a
             * rule for someone else's building is none of their business.
             */
            $query->where('is_active', true)->where(function ($q) use ($actor): void {
                $q->whereNull('property_id')
                    ->orWhere('property_id', $actor->property_id);
            });
        }

        $documents = $query->orderByDesc('published_at')->orderByDesc('id')->get();

        return Inertia::render('Documents/Index', [
            'documents' => $documents->map(fn (PropertyDocument $d) => [
                'id' => (int) $d->id,
                'title' => (string) $d->title,
                'content' => $d->content,
                'type' => $d->type instanceof \BackedEnum ? $d->type->value : (string) $d->type,
                'version' => $d->version,
                'is_active' => (bool) $d->is_active,
                'property_id' => $d->property_id !== null ? (int) $d->property_id : null,
                'property_name' => $d->property?->name,
                'published_at' => $d->published_at?->toIso8601String(),
                'updated_at' => $d->updated_at?->toIso8601String(),
            ])->all(),
            'types' => DocumentType::values(),
            'filters' => [
                'type' => $request->string('type')->toString() ?: null,
                'property_id' => $request->integer('property_id') ?: null,
            ],
            'canManage' => $actor instanceof Owner,
            'properties' => $this->propertiesFor($actor),
            'flash' => ['success' => $request->session()->get('success')],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PropertyDocument::class);

        $validated = $request->validate($this->rules());

        // A new document is published now unless the author says otherwise.
        $validated['published_at'] ??= now();

        $document = PropertyDocument::create($validated);

        return back()->with('success', "\"{$document->title}\" published.");
    }

    public function update(Request $request, PropertyDocument $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $document->update($request->validate($this->rules()));

        return back()->with('success', "{$document->title} updated.");
    }

    public function destroy(PropertyDocument $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $title = $document->title;
        $document->delete();

        return back()->with('success', "\"{$title}\" deleted.");
    }

    /** @return array<string, mixed> */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // NOT NULL in the live schema.
            'content' => ['required', 'string'],
            'type' => ['nullable', Rule::in(DocumentType::values())],
            'version' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'property_id' => ['nullable', 'integer', Rule::exists('properties', 'id')],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /** @return array<int, array{id: int, name: string}> */
    private function propertiesFor(mixed $actor): array
    {
        $query = \App\Models\Property::query()->select(['id', 'name'])->orderBy('name');

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereIn('id', $ids === [] ? [0] : $ids);
        }

        return $query->get()
            ->map(fn ($p) => ['id' => (int) $p->id, 'name' => (string) $p->name])
            ->all();
    }
}