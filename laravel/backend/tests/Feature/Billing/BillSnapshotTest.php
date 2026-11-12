<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| BillSnapshot carry-forward
|--------------------------------------------------------------------------
|
| Every figure is derived from the allocation ledger, never from the drifting
| caches (tenants.balance, bill_items.paid, bills.status).
|
| The centrepiece is the multi-bill payment case: one payment split across two
| months used to carry its FULL amount into the later bill's arrears.
|
*/

use App\Billing\BillSnapshotBuilder;
use App\Models\Bill;
use App\Models\House;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Property;
use App\Models\Renter;
use App\Support\Amount;
use App\Support\TenantContext;

beforeEach(function (): void {
    TenantContext::set(null);
    $this->owner = Owner::factory()->create();

    TenantContext::set((int) $this->owner->id);
    $this->property = Property::factory()->create();
    $this->house = House::factory()->forProperty($this->property)->create();
    $this->renter = Renter::factory()->inHouse($this->house)->create();
    TenantContext::clear();

    $this->snapshots = new BillSnapshotBuilder;
});

/** Allocate `amount` of `payment` to `item`. */
function allocate(Payment $payment, \App\Models\BillItem $item, string $amount): void
{
    PaymentAllocation::query()->create([
        'payment_id' => $payment->getKey(),
        'bill_item_id' => $item->getKey(),
        'category' => 'Rent',
        'amount' => $amount,
    ]);
}

it('derives a bill total from its items', function (): void {
    TenantContext::set((int) $this->owner->id);

    [$bill] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '50000.00']);

    $snapshot = $this->snapshots->build($bill->fresh());

    expect(Amount::toString($snapshot->itemsTotal))->toBe('50000.00')
        ->and(Amount::toString($snapshot->total))->toBe('50000.00')
        ->and(Amount::toString($snapshot->paid))->toBe('0.00')
        ->and(Amount::toString($snapshot->balance))->toBe('50000.00');

    TenantContext::clear();
});

it('reports an unpaid bill as pending', function (): void {
    TenantContext::set((int) $this->owner->id);

    [$bill] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '50000.00']);

    expect($this->snapshots->build($bill->fresh())->status->value)->toBe('pending');

    TenantContext::clear();
});

it('reports a fully paid bill as paid', function (): void {
    TenantContext::set((int) $this->owner->id);

    [$bill, $item] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '50000.00']);

    $payment = Payment::factory()->forTenant($this->renter)->ofAmount('50000.00')->create();
    allocate($payment, $item, '50000.00');

    $snapshot = $this->snapshots->build($bill->fresh());

    expect($snapshot->status->value)->toBe('paid')
        ->and(Amount::toString($snapshot->paid))->toBe('50000.00')
        ->and(Amount::toString($snapshot->balance))->toBe('0.00');

    TenantContext::clear();
});

it('reports a part-paid bill as partial', function (): void {
    TenantContext::set((int) $this->owner->id);

    [$bill, $item] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '50000.00']);

    $payment = Payment::factory()->forTenant($this->renter)->ofAmount('20000.00')->create();
    allocate($payment, $item, '20000.00');

    $snapshot = $this->snapshots->build($bill->fresh());

    expect($snapshot->status->value)->toBe('partial')
        ->and(Amount::toString($snapshot->balance))->toBe('30000.00');

    TenantContext::clear();
});

it('ignores a pending payment', function (): void {
    TenantContext::set((int) $this->owner->id);

    [$bill, $item] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '50000.00']);

    // Only SETTLED statuses count. A pending payment is not money received.
    $payment = Payment::factory()->forTenant($this->renter)->ofAmount('50000.00')->pending()->create();
    allocate($payment, $item, '50000.00');

    expect(Amount::toString($this->snapshots->build($bill->fresh())->paid))->toBe('0.00');

    TenantContext::clear();
});

it('carries unpaid arrears from an earlier month', function (): void {
    TenantContext::set((int) $this->owner->id);

    [, $janItem] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '50000.00']);

    [$feb, ] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-02')
        ->createWithItem(['rent' => '50000.00']);

    // January untouched, so February opens with 50,000 of arrears.
    $snapshot = $this->snapshots->build($feb->fresh());

    expect(Amount::toString($snapshot->openingBalance))->toBe('50000.00')
        ->and(Amount::toString($snapshot->total))->toBe('100000.00');

    TenantContext::clear();
});

it('does not carry arrears from a later month', function (): void {
    TenantContext::set((int) $this->owner->id);

    [$jan, ] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '50000.00']);

    [$feb, ] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-02')
        ->createWithItem(['rent' => '50000.00']);

    // February must not see January's arrears; the carry is one-directional.
    expect(Amount::toString($this->snapshots->build($jan->fresh())->openingBalance))->toBe('0.00');

    TenantContext::clear();
});

it('carries an overpayment forward as credit', function (): void {
    TenantContext::set((int) $this->owner->id);

    [$jan, $janItem] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '50000.00']);

    // Overpay January by 10,000.
    $payment = Payment::factory()->forTenant($this->renter)->ofAmount('60000.00')->create();
    allocate($payment, $janItem, '60000.00');

    [$feb, ] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-02')
        ->createWithItem(['rent' => '50000.00']);

    $snapshot = $this->snapshots->build($feb->fresh());

    // 10,000 credit reduces February's 50,000 to 40,000.
    expect(Amount::toString($snapshot->creditApplied))->toBe('10000.00')
        ->and(Amount::toString($snapshot->total))->toBe('40000.00');

    TenantContext::clear();
});

/*
 * REGRESSION: the carry-forward used to select Payments that touched an earlier
 * bill and sum their FULL payments.amount. With a payment split across months
 * that inflated the earlier months' paid total, shrinking the arrears carried
 * into the current bill and understating what was owed.
 *
 * Scenario: one 100,000 payment settles 60,000 of January and 40,000 of
 * February. For February the January offset must be 60,000 -- the allocation --
 * not 100,000, the whole payment.
 */
it('carries only the allocated share of a multi-bill payment', function (): void {
    TenantContext::set((int) $this->owner->id);

    [$jan, $janItem] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '100000.00']);

    [$feb, $febItem] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-02')
        ->createWithItem(['rent' => '100000.00']);

    // One payment, split 60,000 / 40,000 across the two bills.
    $payment = Payment::factory()->forTenant($this->renter)->ofAmount('100000.00')->create();
    allocate($payment, $janItem, '60000.00');
    allocate($payment, $febItem, '40000.00');

    $febSnapshot = $this->snapshots->build($feb->fresh());

    // January is 60,000 paid of 100,000, so 40,000 of arrears carry forward.
    expect(Amount::toString($febSnapshot->openingBalance))->toBe('40000.00')
        ->and(Amount::toString($febSnapshot->total))->toBe('140000.00')
        ->and(Amount::toString($febSnapshot->paid))->toBe('40000.00')
        ->and(Amount::toString($febSnapshot->balance))->toBe('100000.00');

    // January's own figures are unaffected by the split.
    $janSnapshot = $this->snapshots->build($jan->fresh());
    expect(Amount::toString($janSnapshot->paid))->toBe('60000.00')
        ->and(Amount::toString($janSnapshot->balance))->toBe('40000.00');

    TenantContext::clear();
});

it('ignores a pending multi-bill payment when carrying forward', function (): void {
    TenantContext::set((int) $this->owner->id);

    [$jan, $janItem] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '100000.00']);

    [$feb, $febItem] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-02')
        ->createWithItem(['rent' => '100000.00']);

    $payment = Payment::factory()->forTenant($this->renter)->ofAmount('100000.00')->pending()->create();
    allocate($payment, $janItem, '60000.00');
    allocate($payment, $febItem, '40000.00');

    // Nothing settled, so January's full 100,000 carries into February.
    $snapshot = $this->snapshots->build($feb->fresh());

    expect(Amount::toString($snapshot->openingBalance))->toBe('100000.00')
        ->and(Amount::toString($snapshot->paid))->toBe('0.00');

    TenantContext::clear();
});

it('keeps one renter arrears out of another bill', function (): void {
    TenantContext::set((int) $this->owner->id);

    $otherRenter = Renter::factory()->inHouse($this->house)->create();

    [, $otherJanItem] = Bill::factory()->forTenant($otherRenter)
        ->forMonth('2026-01')
        ->createWithItem(['rent' => '75000.00']);

    [$feb, ] = Bill::factory()->forTenant($this->renter)
        ->forMonth('2026-02')
        ->createWithItem(['rent' => '50000.00']);

    // The other renter's unpaid January must not appear in this renter's bill.
    expect(Amount::toString($this->snapshots->build($feb->fresh())->openingBalance))->toBe('0.00');

    TenantContext::clear();
});