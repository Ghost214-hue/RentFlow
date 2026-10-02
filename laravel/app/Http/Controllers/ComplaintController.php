<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Caretaker;
use App\Models\Complaint;
use App\Models\Owner;
use App\Models\Renter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Complaints — the two-way communication channel.
 *
 * Visibility follows the RECIPIENT list, not the sender: a renter sees the
 * complaints addressed to them, an owner sees everything in their portfolio.
 */
class ComplaintController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Complaint::class);

        $perPage = max(5, min((int) $request->integer('per_page', 15), 100));
        $actor = $request->user();

        $query = Complaint::query()
            ->with(['renter:id,name', 'house:id,unit', 'property:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('property_id'), fn ($q) => $q->where('property_id', $request->integer('property_id')));

        if ($actor instanceof Renter) {
            // Their own complaints, plus any that name them as a recipient.
            $actorId = (int) $actor->getKey();
            $query->where(function ($q) use ($actorId): void {
                $q->where('tenant_id', $actorId)->orWhereRaw(
                    'JSON_CONTAINS(COALESCE(recipient_ids, "[]"), CAST(? AS JSON))',
                    [(string) $actorId],
                );
            });
        }

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->where(function ($q) use ($ids): void {
                $q->whereIn('property_id', $ids === [] ? [0] : $ids)
                    ->orWhere('sender_role', 'caretaker');
            });
        }

        // Owner's optional direction filter: sent by staff vs raised by renters.
        $direction = $request->string('direction')->toString();

        if ($actor instanceof Owner && $direction === 'sent') {
            $query->whereIn('sender_role', ['owner', 'caretaker']);
        } elseif ($actor instanceof Owner && $direction === 'received') {
            $query->where('sender_role', 'tenant');
        }

        $complaints = $query->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($perPage)->withQueryString();

        return Inertia::render('Complaints/Index', [
            'complaints' => [
                'data' => ComplaintResource::collection($complaints->items())->resolve(),
                'links' => [
                    'first' => $complaints->url(1),
                    'last' => $complaints->url($complaints->lastPage()),
                    'prev' => $complaints->previousPageUrl(),
                    'next' => $complaints->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $complaints->currentPage(),
                    'from' => $complaints->firstItem(),
                    'last_page' => $complaints->lastPage(),
                    'per_page' => $complaints->perPage(),
                    'to' => $complaints->lastItem(),
                    'total' => $complaints->total(),
                ],
            ],
            'renters' => $this->rentersFor($actor),
            'filters' => [
                'status' => $request->string('status')->toString() ?: null,
                'direction' => $direction ?: null,
            ],
            'canBroadcast' => $actor instanceof Owner,
            'flash' => ['success' => $request->session()->get('success')],
        ]);
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $attributes = $request->complaintAttributes();

        // A broadcast resolves its audience to concrete renter ids.
        if (($attributes['recipient_type'] ?? null) === 'property'
            && isset($attributes['property_id'])) {
            $attributes['recipient_ids'] = json_encode(
                Renter::query()
                    ->where('property_id', $attributes['property_id'])
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
            );
        }

        if (($attributes['recipient_type'] ?? null) === 'all') {
            $attributes['recipient_ids'] = json_encode(
                Renter::query()->pluck('id')->map(fn ($id) => (int) $id)->all()
            );
        }

        $complaint = Complaint::create($attributes);

        return back()->with('success', "Complaint \"{$complaint->title}\" recorded.");
    }

    public function update(StoreComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $complaint->update($request->complaintAttributes());

        return back()->with('success', 'Complaint updated.');
    }

    /** Move a complaint along open -> in-progress -> resolved. */
    public function advance(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorize('update', $complaint);

        $next = match ($complaint->status instanceof \BackedEnum ? $complaint->status->value : (string) $complaint->status) {
            'open' => 'in-progress',
            'in-progress' => 'resolved',
            default => 'resolved',
        };

        $complaint->update(['status' => $next]);

        return back()->with('success', "Complaint marked {$next}.");
    }

    public function destroy(Complaint $complaint): RedirectResponse
    {
        $this->authorize('delete', $complaint);

        $title = $complaint->title;
        $complaint->delete();

        return back()->with('success', "Complaint \"{$title}\" deleted.");
    }

    /** @return array<int, array{id: number, name: string}> */
    private function rentersFor(mixed $actor): array
    {
        $query = Renter::query()->select(['id', 'name'])->orderBy('name');

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereIn('property_id', $ids === [] ? [0] : $ids);
        }

        return $query->get()
            ->map(fn (Renter $r) => ['id' => (int) $r->getKey(), 'name' => (string) $r->name])
            ->all();
    }
}