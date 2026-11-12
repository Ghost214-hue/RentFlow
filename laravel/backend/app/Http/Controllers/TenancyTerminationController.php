<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\TerminateTenancy;
use App\Enums\TerminationStatus;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use App\Models\TenancyTermination;
use App\Support\Amount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Tenancy termination.
 *
 * Ported from TenantController::terminate(), listTerminations() and the
 * tenant-initiated request in the same controller.
 *
 * The tenancy_terminations table existed in the legacy schema and was written
 * by the old app, but the port never used it: the renter status enum has
 * pending_termination and nothing could put a renter into it.
 */
class TenancyTerminationController extends Controller
{
    public function __construct(private readonly TerminateTenancy $terminate) {}

    public function index(Request $request): Response
    {
        $actor = $request->user();

        $query = TenancyTermination::query()
            ->with(['renter:id,name,email', 'property:id,name', 'house:id,unit'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('created_at');

        $terminations = $query->paginate(25)->withQueryString();

        return Inertia::render('Terminations/Index', [
            'terminations' => [
                'data' => $terminations->map(fn (TenancyTermination $t) => [
                    'id' => (int) $t->getKey(),
                    'renter_name' => $t->renter?->name,
                    'property_name' => $t->property?->name,
                    'house_unit' => $t->house?->unit,
                    'initiated_by' => $t->initiated_by,
                    'reason' => $t->reason,
                    'effective_date' => $t->effective_date?->toDateString(),
                    'status' => $t->status,
                    'created_at' => $t->created_at?->toIso8601String(),
                ])->all(),
                'meta' => [
                    'current_page' => $terminations->currentPage(),
                    'last_page' => $terminations->lastPage(),
                    'total' => $terminations->total(),
                ],
            ],
            // Only an owner may approve or terminate.
            'canAct' => $actor instanceof Owner,
            'filters' => ['status' => $request->string('status')->toString() ?: null],
        ]);
    }

    /**
     * A renter asks to leave. Creates a PENDING request for the owner.
     */
    public function requestTermination(Request $request, Renter $renter): RedirectResponse
    {
        $actor = $request->user();

        /*
         * A renter may ONLY request their own termination.
         *
         * Without this check any signed-in renter could PUT another household
         * into pending_termination and have the landlord notified about it --
         * a renter could weaponise the form against a neighbour.
         */
        if (! $actor instanceof Renter) {
            abort(403);
        }

        abort_unless((int) $actor->getKey() === (int) $renter->getKey(), 403);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            'effective_date' => ['nullable', 'date'],
        ]);

        try {
            $this->terminate->request(
                $renter,
                (string) ($validated['reason'] ?? ''),
                $validated['effective_date'] ?? null,
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Termination requested. Your landlord has been notified.');
    }

    /**
     * The owner (or an assigned caretaker) ends the tenancy immediately.
     */
    public function terminate(Request $request, Renter $renter): RedirectResponse
    {
        $actor = $request->user();

        // RenterPolicy::terminate is owner-only; a caretaker may act on units
        // in their assigned properties, which is checked below.
        if (! $actor instanceof Owner && ! $actor instanceof Caretaker) {
            abort(403);
        }

        if ($actor instanceof Caretaker && $renter->property_id !== null) {
            abort_unless($actor->isAssignedTo((int) $renter->property_id), 403);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            'effective_date' => ['nullable', 'date'],
            'damages' => ['nullable', 'array'],
            'damages.*.title' => ['required', 'string', 'max:255'],
            'damages.*.description' => ['nullable', 'string', 'max:1000'],
            // A decimal string, never a float.
            'damages.*.cost' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'damages.*.vendor_name' => ['nullable', 'string', 'max:255'],
            'damages.*.priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        try {
            $this->terminate->apply(
                $renter,
                $actor,
                (string) ($validated['reason'] ?? ''),
                $validated['effective_date'] ?? null,
                $validated['damages'] ?? [],
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$renter->name}'s tenancy has been terminated and the unit freed.");
    }

    /**
     * Approve a renter's pending request.
     */
    public function approve(Request $request, Renter $renter): RedirectResponse
    {
        $this->authorize('terminate', $renter);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            'effective_date' => ['nullable', 'date'],
        ]);

        try {
            $this->terminate->apply(
                $renter,
                $request->user(),
                (string) ($validated['reason'] ?? ''),
                $validated['effective_date'] ?? null,
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Termination approved.');
    }
}