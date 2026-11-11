<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Every email the application can send, as a closed set.
 *
 * WHY AN ENUM AND NOT A STRING
 * The legacy EmailService took a magic string: sendTemplate('Paymnt
 * Confirm', ...). A typo matched no template, so it silently sent NOTHING --
 * no exception, no log row, no alert. That is how a broken template goes
 * unnoticed until a renter complains they were never billed.
 *
 * As an enum the typo becomes a fatal error at the call site, and
 * requiredVariables() lets the renderer REFUSE to send a message that is
 * missing data, so "Dear ," can never reach a real person.
 *
 * Bodies live in Blade (resources/views/mail/*.blade.php) so they can branch
 * on data and escape values by default. An owner may override any template
 * with their own copy from `email_templates`; see RenderMailTemplate for how
 * overrides are rendered safely.
 */
enum MailTemplate: string
{
    case TenantWelcome = 'Tenant Welcome';
    case CaretakerWelcome = 'Caretaker Welcome';
    case PasswordReset = 'Password Reset';
    case PaymentConfirmation = 'Payment Confirmation';
    case RentReminder = 'Rent Reminder';
    case RentReminderFinal = 'Rent Reminder Final';
    case LeaseRenewal = 'Lease Renewal';
    case ComplaintUpdate = 'Complaint Update';
    case ComplaintReply = 'Complaint Reply';
    case ManagementNotice = 'Management Notice';
    case TerminationRequest = 'Termination Request';
    case TerminationNotice = 'Termination Notice';
    case TenantVacate = 'Tenant Vacate';

    /**
     * The Blade view holding the shipped body.
     *
     * Kept beside the enum rather than in config so a missing template is a
     * missing file at a predictable path, not a null config lookup.
     */
    public function view(): string
    {
        return 'mail.'.match ($this) {
            self::TenantWelcome => 'tenant-welcome',
            self::CaretakerWelcome => 'caretaker-welcome',
            self::PasswordReset => 'password-reset',
            self::PaymentConfirmation => 'payment-confirmation',
            self::RentReminder => 'rent-reminder',
            self::RentReminderFinal => 'rent-reminder-final',
            self::LeaseRenewal => 'lease-renewal',
            self::ComplaintUpdate => 'complaint-update',
            self::ComplaintReply => 'complaint-reply',
            self::ManagementNotice => 'management-notice',
            self::TerminationRequest => 'termination-request',
            self::TerminationNotice => 'termination-notice',
            self::TenantVacate => 'tenant-vacate',
        };
    }

    /**
     * Subject line, as a placeholder string.
     *
     * Rendered through the same placeholder pass as an owner override so a
     * customised subject can still reference {{amount}} and friends.
     */
    public function defaultSubject(): string
    {
        return match ($this) {
            self::TenantWelcome => 'Welcome to {{property}} - Activate Your Account',
            self::CaretakerWelcome => 'Welcome to RentFlow - Caretaker Account Activation',
            self::PasswordReset => 'Your RentFlow verification code',
            self::PaymentConfirmation => 'Payment received - KES {{amount}}',
            self::RentReminder => 'Friendly rent reminder - {{month}}',
            self::RentReminderFinal => 'Urgent: rent due tomorrow ({{month}})',
            self::LeaseRenewal => 'Your lease is approaching its end - {{house}}',
            self::ComplaintUpdate => 'We received your complaint - reference #{{id}}',
            self::ComplaintReply => 'An update on your complaint #{{id}}',
            self::ManagementNotice => '{{title}} - Important property notice',
            self::TerminationRequest => 'Termination request needing your review - {{property}} {{house}}',
            self::TerminationNotice => 'Tenancy termination notice - {{property}} {{house}}',
            self::TenantVacate => 'Your tenancy has been closed - {{property}}',
        };
    }

    /**
     * Variables that MUST be present, or the send is refused.
     *
     * Anything optional (an invoice link that may not exist yet) is left out on
     * purpose: a template branches on it with @if instead.
     *
     * @return list<string>
     */
    public function requiredVariables(): array
    {
        return match ($this) {
            self::TenantWelcome => ['tenant', 'property', 'house', 'email', 'setup_link'],
            self::CaretakerWelcome => ['name', 'email', 'setup_link'],
            self::PasswordReset => ['name', 'code', 'expires'],
            self::PaymentConfirmation => ['tenant', 'amount', 'category', 'date', 'balance'],
            self::RentReminder, self::RentReminderFinal => [
                'tenant', 'month', 'amount', 'balance', 'payment_instructions',
            ],
            self::LeaseRenewal => ['tenant', 'property', 'house'],
            self::ComplaintUpdate => ['tenant', 'id', 'category', 'date'],
            self::ComplaintReply => ['tenant', 'id', 'category', 'link'],
            self::ManagementNotice => [
                'tenant_name', 'sender_name', 'property', 'house', 'date', 'category', 'title', 'description',
            ],
            self::TerminationRequest => ['owner_name', 'tenant_name', 'property', 'house', 'date', 'reason'],
            self::TerminationNotice => ['tenant_name', 'property', 'house', 'date', 'reason', 'owner_name'],
            self::TenantVacate => ['tenant', 'property', 'house', 'date', 'national_id'],
        };
    }

    /**
     * Password resets are security-critical: a failure must never be silent.
     *
     * @return list<string>
     */
    public function isSecuritySensitive(): bool
    {
        return $this === self::PasswordReset;
    }
}
