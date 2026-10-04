<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Bill;
use App\Models\Complaint;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Renter;
use App\Models\TenancyTermination;
use App\Support\Amount;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;

/**
 * Domain-named email operations, so actions never mention templates.
 *
 * The actions call `paymentReceived($payment)`, not
 * `sendTemplate('Payment Confirmation', ...)`. That keeps three things out of
 * the business logic: the template name, which variables it needs, and the fact
 * that email exists at all. A caller cannot forget a required variable because
 * this class assembles them.
 *
 * Every method is best-effort. A mail problem must never roll back the payment,
 * complaint or termination that triggered it, so trySend() swallows failures and
 * they surface in the delivery log instead.
 */
final class PortfolioMailer
{
    public function __construct(private readonly Mailer $mailer) {}

    /** A renter has been onboarded and needs their activation link. */
    public function renterOnboarded(Renter $renter): void
    {
        $email = (string) ($renter->email ?? '');

        if ($email === '') {
            return;
        }

        $this->mailer->trySend(
            MailTemplate::TenantWelcome,
            $this->ownerId($renter),
            $email,
            (string) $renter->name,
            [
                'tenant' => (string) $renter->name,
                'property' => $this->propertyName($renter),
                'house' => $this->unitName($renter),
                'email' => $email,
                // TODO(activation): wire to password_setup_tokens once the
                // setup-password flow is ported. Until then the link is the
                // reset route, so a renter can still get in.
                'setup_link' => route('password.request').'?email='.urlencode($email),
            ],
        );
    }

    /** A payment was recorded, and the renter expects a receipt. */
    public function paymentReceived(Payment $payment): void
    {
        $renter = Renter::query()->find($payment->tenant_id);

        if ($renter === null || ($renter->email ?? '') === '') {
            return;
        }

        $this->mailer->trySend(
            MailTemplate::PaymentConfirmation,
            $this->ownerId($renter),
            (string) $renter->email,
            (string) $renter->name,
            [
                'tenant' => (string) $renter->name,
                'amount' => Amount::toString(Amount::of($payment->amount)),
                'category' => (string) ($payment->type ?? 'Rent'),
                'date' => (string) ($payment->date ?? CarbonImmutable::now()->toDateString()),
                // read-back after the allocation, so the figure is the settled
                // balance rather than the value before this payment landed.
                'balance' => Amount::toString(Amount::of($renter->fresh()?->balance ?? '0.00')),
                'invoice_section' => $this->invoiceSection($payment),
                'invoice_url' => route('bills.index'),
            ],
        );
    }

    /** Acknowledge a complaint the moment it is filed. */
    public function complaintFiled(Complaint $complaint): void
    {
        $renter = Renter::query()->find($complaint->tenant_id);

        if ($renter === null || ($renter->email ?? '') === '') {
            return;
        }

        $this->mailer->trySend(
            MailTemplate::ComplaintUpdate,
            $this->ownerId($renter),
            (string) $renter->email,
            (string) $renter->name,
            [
                'tenant' => (string) $renter->name,
                'id' => (string) $complaint->getKey(),
                'category' => (string) ($complaint->category ?? 'General'),
                'date' => (string) ($complaint->date ?? CarbonImmutable::now()->toDateString()),
            ],
        );
    }

    /**
     * Tell the OWNER a renter wants to leave. Nothing has changed yet, so this
     * is a request for a decision, not a notice.
     */
    public function terminationRequested(TenancyTermination $termination): void
    {
        $renter = $termination->renter;
        $ownerId = $this->ownerId($renter);
        $owner = Owner::withoutGlobalScopes()->find($ownerId);

        if ($owner === null || ($owner->email ?? '') === '') {
            return;
        }

        $this->mailer->trySend(
            MailTemplate::TerminationRequest,
            $ownerId,
            (string) $owner->email,
            (string) $owner->name,
            [
                'owner_name' => (string) $owner->name,
                'tenant_name' => (string) ($renter?->name ?? 'Tenant'),
                'property' => $this->propertyName($renter),
                'house' => $this->unitName($renter),
                'date' => (string) ($termination->effective_date ?? CarbonImmutable::now()->toDateString()),
                'reason' => (string) ($termination->reason ?? 'Not given'),
            ],
        );
    }

    /** Tell the RENTER their tenancy has actually ended. */
    public function tenancyEnded(TenancyTermination $termination): void
    {
        $renter = $termination->renter;

        if ($renter === null || ($renter->email ?? '') === '') {
            return;
        }

        $owner = Owner::withoutGlobalScopes()->find($this->ownerId($renter));

        $this->mailer->trySend(
            MailTemplate::TerminationNotice,
            $this->ownerId($renter),
            (string) $renter->email,
            (string) $renter->name,
            [
                'tenant_name' => (string) $renter->name,
                'property' => $this->propertyName($renter),
                'house' => $this->unitName($renter),
                'date' => (string) ($termination->effective_date ?? CarbonImmutable::now()->toDateString()),
                'reason' => (string) ($termination->reason ?? 'Not given'),
                'owner_name' => (string) ($owner?->name ?? 'Property management'),
            ],
        );
    }

    // --- helpers --------------------------------------------------------

    private function ownerId(?Renter $renter): int
    {
        if ($renter !== null && $renter->owner_id !== null) {
            return (int) $renter->owner_id;
        }

        return (int) TenantContext::id();
    }

    private function propertyName(?Renter $renter): string
    {
        return (string) ($renter?->property?->name ?? 'your property');
    }

    private function unitName(?Renter $renter): string
    {
        return (string) ($renter?->house?->unit ?? 'your unit');
    }

    /**
     * A plain-text summary of what this payment actually settled.
     *
     * Built from the ledger rather than assumed, so the email cannot claim it
     * cleared a bill it did not touch.
     */
    private function invoiceSection(Payment $payment): string
    {
        $billIds = $payment->allocations()
            ->with('billItem.bill')
            ->get()
            ->map(fn ($a) => $a->billItem?->bill)
            ->filter()
            ->unique('id');

        if ($billIds->isEmpty()) {
            return '';
        }

        $lines = $billIds->map(function (Bill $bill): string {
            $label = (string) ($bill->month ?? '');
            $amount = Amount::toString(Amount::of($bill->total));

            return '• '.trim($label.' — KES '.$amount);
        })->all();

        return implode("\n", $lines);
    }

    /**
     * How to pay, built from the property's own payment details.
     *
     * Left to the template's white-space handling and escaped there, because
     * these strings are owner-controlled.
     */
    public static function paymentInstructions(?Property $property): string
    {
        if ($property === null) {
            return 'Please contact your property manager for payment details.';
        }

        $lines = [];

        if ($property->payment_method_type) {
            $lines[] = $property->payment_method_type;
        }

        if ($property->paybill_number) {
            $lines[] = 'Paybill: '.$property->paybill_number
                .($property->paybill_account ? ' (account '.$property->paybill_account.')' : '');
        }

        if ($property->till_number) {
            $lines[] = 'Till: '.$property->till_number;
        }

        if ($property->bank_name) {
            $lines[] = $property->bank_name
                .($property->bank_account ? ' — '.$property->bank_account : '')
                .($property->bank_branch ? ' ('.$property->bank_branch.')' : '');
        }

        if ($property->mobile_money_number) {
            $lines[] = 'Mobile money: '.$property->mobile_money_number;
        }

        return $lines === [] ? 'Please contact your property manager for payment details.' : implode("\n", $lines);
    }
}
