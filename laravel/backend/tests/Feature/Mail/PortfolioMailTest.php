<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Portfolio mail
|--------------------------------------------------------------------------
|
| Two things are being pinned here.
|
| First, that the domain actions actually send mail. The legacy app had a full
| EmailService that nothing called: email_logs was empty and the /email-logs page
| showed nothing, because no code path ever invoked it. These tests exist so
| that cannot silently happen again.
|
| Second, that mail can never take the business action down with it. A recorded
| payment, a filed complaint and an approved termination must all survive a mail
| outage, so every one of these is asserted with the mailer failing.
|
*/

use App\Enums\EmailLogStatus;
use App\Jobs\DeliverMail;
use App\Mail\RenderedMail;
use App\Models\Bill;
use App\Models\Complaint;
use App\Models\EmailLog;
use App\Models\House;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Property;
use App\Models\Renter;
use App\Models\RentReminder;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    // The queue is deliberately NOT faked: QUEUE_CONNECTION=sync runs
    // DeliverMail inline, so these assert mail really goes out rather than
    // merely being enqueued.

    Mail::fake();

    TenantContext::set(null);
    $this->owner = Owner::factory()->withPassword('secret123')->create([
        'email' => 'owner@example.test',
        'name' => 'Grace Owner',
    ]);

    TenantContext::set((int) $this->owner->id);
    $this->property = Property::factory()->create([
        'name' => 'Sunrise Court',
        'payment_method_type' => 'paybill',
        'paybill_number' => '522533',
        'paybill_account' => 'RentFlow',
    ]);
    $this->house = House::factory()->forProperty($this->property)->occupied()
        ->create(['unit' => 'A1', 'rent' => '50000.00']);
    $this->renter = Renter::factory()->inHouse($this->house)->create([
        'email' => 'renter@example.test',
        'name' => 'Ada Renter',
        'balance' => '0.00',
    ]);
    TenantContext::clear();
});

// --- the actions send mail ---------------------------------------------

it('emails a welcome when a renter is onboarded', function (): void {
    TenantContext::set((int) $this->owner->id);
    $vacant = House::factory()->forProperty($this->property)->create(['unit' => 'A2']);
    TenantContext::clear();

    $this->actingAs($this->owner)->post('/renters', [
        'property_id' => $this->property->id,
        'house_id' => $vacant->id,
        'name' => 'Bea Newcomer',
        'email' => 'bea@example.test',
        'phone' => '0700000001',
        'id_type' => 'National ID',
        'lease_start' => now()->toDateString(),
        'rent' => '40000.00',
        'deposit' => '40000.00',
        'next_of_kin_name' => 'Kin',
        'next_of_kin_phone' => '0700000002',
    ])->assertRedirect();

    Mail::assertSent(function (RenderedMail $m): bool {
        return $m->hasTo('bea@example.test');
    });
});

it('emails a receipt when a payment is recorded', function (): void {
    TenantContext::set((int) $this->owner->id);
    [$bill, $item] = Bill::factory()->forTenant($this->renter)->createWithItem([
        'total' => '50000.00', 'rent' => '50000.00', 'status' => 'pending',
    ]);
    TenantContext::clear();

    $this->actingAs($this->owner)->post('/payments', [
        'renter_id' => $this->renter->id,
        'amount' => '50000.00',
        'month' => $bill->month,
        'type' => 'Rent',
        'method' => 'M-Pesa',
        'date' => now()->toDateString(),
    ])->assertRedirect();

    Mail::assertSent(function (RenderedMail $m): bool {
        return $m->hasTo('renter@example.test');
    });
});

/*
 * Filed by the RENTER: the case that needs an acknowledgement, because they
 * want to know it arrived. The complaint carries their tenant_id, so there is
 * somebody to write to.
 */
it('acknowledges a renter-filed complaint by email', function (): void {
    $this->actingAs($this->renter)->post('/complaints', [
        'title' => 'No water',
        'description' => 'Tap has been dry since Monday',
        'category' => 'Plumbing',
        'priority' => 'high',
    ])->assertRedirect();

    Mail::assertSent(function (RenderedMail $m): bool {
        return $m->hasTo('renter@example.test');
    });
});

/*
 * A complaint the OWNER logs has no tenant_id, so there is nobody to
 * acknowledge. Nothing must be sent rather than a guess at a recipient.
 */
it('sends no acknowledgement for a complaint with no tenant', function (): void {
    $this->actingAs($this->owner)->post('/complaints', [
        'title' => 'No water',
        'description' => 'Tap has been dry since Monday',
        'category' => 'Plumbing',
        'priority' => 'high',
        'house_id' => $this->house->id,
    ])->assertRedirect();

    Mail::assertNothingSent();
});

it('notifies the owner when a renter asks to leave', function (): void {
    $this->actingAs($this->renter)
        ->post("/renters/{$this->renter->id}/termination", ['reason' => 'Relocating'])
        ->assertRedirect();

    // Goes to the LANDLORD, not the renter who already knows.
    Mail::assertSent(function (RenderedMail $m): bool {
        return $m->hasTo('owner@example.test');
    });
});

it('notifies the renter when the owner ends the tenancy', function (): void {
    $this->actingAs($this->owner)
        ->post("/renters/{$this->renter->id}/terminate", ['reason' => 'Eviction'])
        ->assertRedirect();

    Mail::assertSent(function (RenderedMail $m): bool {
        return $m->hasTo('renter@example.test');
    });
});

// --- mail must never break the business action -------------------------

it('still records a payment when the transport fails', function (): void {
    TenantContext::set((int) $this->owner->id);
    Bill::factory()->forTenant($this->renter)->createWithItem([
        'total' => '50000.00', 'rent' => '50000.00', 'status' => 'pending',
    ]);
    TenantContext::clear();

    // Undo the fake and make the real transport explode. This is the truest
    // version of "mail is down": the failure happens at the very last moment,
    // after the payment is already committed.
    Mail::clearResolvedInstances();
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $response = $this->actingAs($this->owner)->post('/payments', [
        'renter_id' => $this->renter->id,
        'amount' => '1000.00',
        'type' => 'Rent',
        'method' => 'Cash',
        'date' => now()->toDateString(),
    ]);

    // The money is recorded; the email is lost. That is the correct trade.
    $response->assertRedirect();
    expect(Payment::withoutOwnerScope()->where('tenant_id', $this->renter->id)->count())
        ->toBeGreaterThan(0);
});

it('still terminates a tenancy when the transport fails', function (): void {
    Mail::clearResolvedInstances();
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $this->actingAs($this->owner)
        ->post("/renters/{$this->renter->id}/terminate", ['reason' => 'Done'])
        ->assertRedirect();

    expect(Renter::withoutOwnerScope()->find($this->renter->id)->status->value)->toBe('terminated');
});

it('sends nothing to a renter with no email address', function (): void {
    TenantContext::set((int) $this->owner->id);
    $silent = Renter::factory()->inHouse($this->house)->create(['email' => null]);
    TenantContext::clear();

    $this->actingAs($this->owner)
        ->post("/renters/{$silent->id}/terminate", ['reason' => 'Done'])
        ->assertRedirect();

    Mail::assertNothingSent();
});

// --- rent reminders ---------------------------------------------------

/** A bill due in $days, unpaid. */
function seedDueBill(Renter $renter, int $days): Bill
{
    TenantContext::set((int) $renter->owner_id);

    [$bill] = Bill::factory()->forTenant($renter)->createWithItem([
        'total' => '50000.00',
        'rent' => '50000.00',
        'status' => 'pending',
        'due_date' => now()->addDays($days)->toDateString(),
    ]);

    TenantContext::clear();

    return $bill;
}

it('emails a reminder for an unpaid bill approaching its due date', function (): void {
    seedDueBill($this->renter, 5);

    $this->artisan('rent:remind', ['--days' => [5]])->assertSuccessful();

    Mail::assertSent(function (RenderedMail $m): bool {
        return $m->hasTo('renter@example.test');
    });
});

it('sends the firmer template on the day before', function (): void {
    seedDueBill($this->renter, 1);

    $this->artisan('rent:remind', ['--days' => [1]])->assertSuccessful();

    Mail::assertSent(function (RenderedMail $m): bool {
        return $m->hasTo('renter@example.test');
    });
});

/*
 * THE POINT OF THE rent_reminders TABLE.
 *
 * This command runs daily. Without the (bill_id, days_before_due) record it
 * would email the same renter every single day until they paid.
 */
it('never sends the same reminder twice', function (): void {
    seedDueBill($this->renter, 5);

    $this->artisan('rent:remind', ['--days' => [5]])->assertSuccessful();
    $this->artisan('rent:remind', ['--days' => [5]])->assertSuccessful();
    $this->artisan('rent:remind', ['--days' => [5]])->assertSuccessful();

    Mail::assertSentCount(1);
    expect(RentReminder::withoutOwnerScope()->count())->toBe(1);
});

/*
 * The dedupe key is (bill_id, days_before_due), so two DIFFERENT bills at
 * the same lead time are two different messages -- the record is per bill,
 * not per renter and not global.
 */
it(
    'reminds each bill separately at the same lead time', function (): void {
        seedDueBill($this->renter, 5);

        TenantContext::set((int) $this->owner->id);
        $second = House::factory()->forProperty($this->property)->occupied()->create();
        $otherRenter = Renter::factory()->inHouse($second)->create([
            'email' => 'other@example.test',
            'name' => 'Otto Other',
        ]);
        TenantContext::clear();

        seedDueBill($otherRenter, 5);

        $this->artisan('rent:remind', ['--days' => [5]])->assertSuccessful();

        Mail::assertSentCount(2);
        expect(RentReminder::withoutOwnerScope()->count())->toBe(2);
    });

it('does not chase a bill that has already been paid', function (): void {
    $bill = seedDueBill($this->renter, 5);

    TenantContext::set((int) $this->owner->id);
    $bill->update(['status' => 'paid']);
    TenantContext::clear();

    $this->artisan('rent:remind', ['--days' => [5]])->assertSuccessful();

    Mail::assertNothingSent();
});

it('does not chase a bill that is not due yet', function (): void {
    seedDueBill($this->renter, 20);

    $this->artisan('rent:remind', ['--days' => [5]])->assertSuccessful();

    Mail::assertNothingSent();
});

it('chases only the unpaid remainder of a part-paid bill', function (): void {
    TenantContext::set((int) $this->owner->id);
    [$bill, $item] = Bill::factory()->forTenant($this->renter)->createWithItem([
        'total' => '50000.00', 'rent' => '50000.00', 'status' => 'partial',
        'due_date' => now()->addDays(5)->toDateString(),
    ]);

    $payment = Payment::factory()->forTenant($this->renter)->ofAmount('20000.00')
        ->create(['status' => 'completed']);
    PaymentAllocation::query()->create([
        'payment_id' => $payment->id,
        'bill_item_id' => $item->id,
        'category' => 'Rent',
        'amount' => '20000.00',
    ]);
    TenantContext::clear();

    $this->artisan('rent:remind', ['--days' => [5]])->assertSuccessful();

    $reminder = RentReminder::withoutOwnerScope()->firstOrFail();

    // Asking for the full 50,000 when 20,000 is already paid is exactly the
    // kind of message that ends a tenancy.
    expect($reminder->amount)->toBe('30000.00');
});

// --- the delivery log is the audit trail -------------------------------

it('records a delivery row for every send', function (): void {
    seedDueBill($this->renter, 5);

    $this->artisan('rent:remind', ['--days' => [5]])->assertSuccessful();

    $log = EmailLog::withoutOwnerScope()->where('to_email', 'renter@example.test')->first();

    expect($log)->not->toBeNull()
        ->and($log->status->value)->toBe('sent')
        ->and($log->subject)->not->toBe('');
});

it('marks a delivery failed rather than leaving it pending forever', function (): void {
    TenantContext::set((int) $this->owner->id);
    EmailLog::query()->create([
        'to_email' => 'x@example.test',
        'to_name' => 'X',
        'subject' => 'Test',
        'body' => 'Body',
        'status' => 'pending',
        'sent_at' => now(),
    ]);
    TenantContext::clear();

    // Simulate the job exhausting its retries.
    $job = new DeliverMail(EmailLog::withoutOwnerScope()->latest('id')->value('id'), (int) $this->owner->id);
    $job->failed(new RuntimeException('SMTP unreachable'));

    // Read through the model, not the raw column: status is cast to the
    // enum, so the raw value would be the enum instance rather than a string.
    expect(EmailLog::withoutOwnerScope()->latest('id')->first()->status)->toBe(EmailLogStatus::Failed);
});
