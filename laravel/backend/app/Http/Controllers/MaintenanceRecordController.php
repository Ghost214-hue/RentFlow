<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\MaintenanceStatus;
use App\Http\Requests\StoreMaintenanceRecordRequest;
use App\Http\Resources\MaintenanceRecordResource;
use App\Models\Caretaker;
use App\Models\MaintenanceRecord;
use App\Models\Owner;
use App\Models\Renter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Maintenance requests.
 *
 * Renters raise requests against their own unit; staff move them through
 * pending -> in-progress -> completed. Visibility follows the recipient list,
 * mirroring the complaints flow.
 */
class MaintenanceRecordController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MaintenanceRecord::class);

        $perPage = max(5, min((int) $request->integer('per_page', 15), 100));
        $actor = $request->user();

        $query = MaintenanceRecord::query()
            ->with(['renter:id,name', 'house:id,unit', 'property:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->when($request->filled('property_id'), fn ($q) => $q->where('property_id', $request->integer('property_id')));

        if ($actor instanceof Renter) {
            /*
             * MariaDB has no CAST(... AS JSON); JSON_CONTAINS matches a plain
             * string against the integer members of the array, which is the
             * comparison we want. COALESCE supplies '[]' when there is none.
             */
            $actorId = (int) $actor->getKey();
            $query->where(function ($q) use ($actorId): void {
                $q->where('tenant_id', $actorId)->orWhereRaw(
                    "JSON_CONTAINS(COALESCE(recipient_ids, '[]'), ?)",
                    [(string) $actorId],
                );
            });
        }

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();

            // Records in their assigned properties, plus any assigned to them
            // directly by name (assigned_to is a free-text column).
            $query->where(function ($q) use ($ids, $actor): void {
                $q->whereIn('property_id', $ids === [] ? [0] : $ids)
                    ->orWhere('assigned_to', $actor->name);
            });
        }

        $records = $query->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($perPage)->withQueryString();

        return Inertia::render('Maintenance/Index', [
            'records' => [
                'data' => MaintenanceRecordResource::collection($records->items())->resolve(),
                'links' => [
                    'first' => $records->url(1),
                    'last' => $records->url($records->lastPage()),
                    'prev' => $records->previousPageUrl(),
                    'next' => $records->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $records->currentPage(),
                    'from' => $records->firstItem(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'to' => $records->lastItem(),
                    'total' => $records->total(),
                ],
            ],
            'renters' => $this->rentersFor($actor),
            'filters' => [
                'status' => $request->string('status')->toString() ?: null,
                'priority' => $request->string('priority')->toString() ?: null,
            ],
            'canBroadcast' => $actor instanceof Owner,
            'flash' => ['success' => $request->session()->get('success')],
        ]);
    }

    public function store(StoreMaintenanceRecordRequest $request): RedirectResponse
    {
        $attributes = $request->recordAttributes();

        // A broadcast resolves its audience to concrete renter ids.
        if (($attributes['recipient_type'] ?? null) === 'property' && isset($attributes['property_id'])) {
            $attributes['recipient_ids'] = json_encode(
                Renter::query()->where('property_id', $attributes['property_id'])
                    ->pluck('id')->map(fn ($id) => (int) $id)->all()
            );
        }

        if (($attributes['recipient_type'] ?? null) === 'all') {
            $attributes['recipient_ids'] = json_encode(
                Renter::query()->pluck('id')->map(fn ($id) => (int) $id)->all()
            );
        }

        // Completing a record stamps the completion date server-side.
        if (($attributes['status'] ?? null) === 'completed' && empty($attributes['completed_date'])) {
            $attributes['completed_date'] = now()->toDateString();
        }

        $record = MaintenanceRecord::create($attributes);

        return back()->with('success', "Maintenance request \"{$record->title}\" submitted.");
    }

    public function update(StoreMaintenanceRecordRequest $request, MaintenanceRecord $record): RedirectResponse
    {
        $attributes = $request->recordAttributes();

        if (($attributes['status'] ?? null) === 'completed' && empty($attributes['completed_date'])) {
            $attributes['completed_date'] = now()->toDateString();
        }

        $record->update($attributes);

        return back()->with('success', 'Maintenance request updated.');
    }

    /** Move a record along its status lifecycle. Staff only. */
    public function advance(MaintenanceRecord $record): RedirectResponse
    {
        // `advance`, not `update`: a renter may edit their own open request but
        // must never be able to self-complete one.
        $this->authorize('advance', $record);

        $status = $record->status instanceof MaintenanceStatus
            ? $record->status
            : MaintenanceStatus::tryFrom((string) $record->status);

        $next = $status?->next();

        if ($next === null) {
            return back()->with('success', 'This request is already closed.');
        }

        $attributes = ['status' => $next->value];

        if ($next === MaintenanceStatus::Completed) {
            $attributes['completed_date'] = now()->toDateString();
        }

        $record->update($attributes);

        return back()->with('success', "Request marked {$next->value}.");
    }

    public function destroy(MaintenanceRecord $record): RedirectResponse
    {
        $this->authorize('delete', $record);

        $title = $record->title;
        $record->delete();

        return back()->with('success', "\"{$title}\" deleted.");
    }

    /** @return array<int, array{id: int, name: string}> */
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