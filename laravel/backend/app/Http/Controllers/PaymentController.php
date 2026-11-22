<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordPayment;
use App\Http\Requests\RecordPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Caretaker;
use App\Models\Payment;
use App\Models\Renter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Payments.
 *
 * All money enters through RecordPayment, which allocates it across the
 * renter's arrears (chosen month first, then oldest) and derives any remainder
 * as credit. No other path may write a payment row.
 */
class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payment::class);

        $perPage = max(5, min((int) $request->integer('per_page', 15), 100));
        $actor = $request->user();

        $query = Payment::query()
            ->with(['renter:id,name', 'house:id,unit,property_id'])
            ->withSum('allocations', 'amount')
            ->when($request->filled('renter_id'), fn ($q) => $q->where('tenant_id', $request->integer('renter_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));

        if ($actor instanceof Renter) {
            $query->where('tenant_id', $actor->getKey());
        }

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereHas('house', fn ($h) => $h->whereIn('property_id', $ids === [] ? [0] : $ids));
        }

        $payments = $query->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($perPage)->withQueryString();

        return Inertia::render('Payments/Index', [
            'payments' => [
                'data' => PaymentResource::collection($payments->items())->resolve(),
                'links' => [
                    'first' => $payments->url(1),
                    'last' => $payments->url($payments->lastPage()),
                    'prev' => $payments->previousPageUrl(),
                    'next' => $payments->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'from' => $payments->firstItem(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'to' => $payments->lastItem(),
                    'total' => $payments->total(),
                ],
            ],
            'renters' => $this->rentersFor($actor),
            'filters' => [
                'renter_id' => $request->integer('renter_id') ?: null,
                'status' => $request->string('status')->toString() ?: null,
            ],
            'flash' => ['success' => $request->session()->get('success')],
        ]);
    }

    public function store(RecordPaymentRequest $request): RedirectResponse
    {
        $payment = (new RecordPayment())->handle($request->paymentAttributes());

        $renter = $payment->renter;

        return back()->with(
            'success',
            sprintf(
                'Payment of KES %s recorded for %s.',
                $payment->amount,
                $renter?->name ?? 'the renter',
            ),
        );
    }

    /**
     * A renter acknowledges they have seen a payment record. This is NOT an
     * edit of the money: it only flips tenant_confirmed.
     */
    public function confirm(Request $request, Payment $payment): RedirectResponse
    {
        if (! $request->user() instanceof Renter) {
            abort(403, 'Only the renter can confirm a payment.');
        }

        $this->authorize('view', $payment);

        if ($payment->tenant_confirmed) {
            return back()->with('error', 'You have already confirmed this payment.');
        }

        $payment->update([
            'tenant_confirmed' => true,
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'Payment confirmed. Thank you.');
    }

    /**
     * Reverse a payment — owner only, or caretaker with payments.reverse grant.
     *
     * Creates a reversal entry (never deletes), removes allocations,
     * restores bill balances in one DB transaction, requires a reason,
     * writes an audit row, and emails/notifies the renter.
     */
    public function reverse(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('reverse', $payment);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($payment, $validated, $request): void {
                // Remove all allocations against this payment's bill items
                foreach ($payment->allocations as $allocation) {
                    $allocation->delete();
                }

                // Mark the payment as reversed
                $payment->status = 'reversed';
                $payment->save();

                // Write audit row
                \App\Models\AuditLog::log(
                    'reverse',
                    'payment_reverse',
                    $payment->tenant?->owner_id ?? 0,
                    'Admin reversed payment ' . $payment->getKey() . ' for renter ' . $payment->tenant_id,
                    $request->ip(),
                    $request->header('User-Agent')
                );
            });

            // Notify the renter AFTER the transaction commits
            $renter = $payment->renter;
            if ($renter) {
                \App\Mail\PortfolioMailer::paymentReversed($renter, $payment, $validated['reason']);
            }

            return back()->with('success', 'Payment reversed and bill balances restored.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to reverse payment: ' . $e->getMessage());
        }
    }

    /** @return array<int, array{id: number, name: string}> */
    private function rentersFor(mixed $actor): array
    {
        $query = Renter::query()
            ->select(['id', 'name'])
            ->where('status', 'active')
            ->orderBy('name');

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();
            $query->whereIn('property_id', $ids === [] ? [0] : $ids);
        }

        return $query->get()
            ->map(fn (Renter $r) => ['id' => (int) $r->getKey(), 'name' => (string) $r->name])
            ->all();
    }
}