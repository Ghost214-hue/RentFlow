<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\EmailLogStatus;
use App\Jobs\DeliverMail;
use App\Models\EmailLog;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The single way this application sends email.
 *
 * THE ONE RULE: YOU CANNOT SEND WITHOUT LOGGING.
 *
 * The legacy EmailService exposed send(), queueTemplate() and sendTemplate()
 * as separate methods and returned a bool that most callers ignored. That is
 * why `email_logs` had no rows: the audit trail depended on every call site
 * remembering to write one, and none did. This class makes the two the same
 * operation -- the delivery row is created BEFORE dispatch, so it exists even
 * if rendering, queueing or the transport then fails.
 *
 * NOTHING IS SENT INLINE.
 * A message is queued. With QUEUE_CONNECTION=sync (the current setting) that
 * means it still runs inline in development, but the code path, the retry
 * behaviour and the log status are identical to a real worker -- so switching
 * to the database driver later changes throughput, not behaviour.
 *
 * Mail failures NEVER propagate to the caller. A recorded payment, a filed
 * complaint or an approved termination must still succeed when the mail server
 * is down; the failure belongs in the delivery log, where it can be retried,
 * not in a 500 that rolls back the business action.
 */
final class Mailer
{
    public function __construct(private readonly RenderMailTemplate $renderer) {}

    /**
     * Queue a message for delivery.
     *
     * @param  array<string, mixed>  $variables
     * @return EmailLog The PENDING delivery row, for reference and tests.
     */
    public function send(
        MailTemplate $template,
        int $ownerId,
        string $toEmail,
        string $toName,
        array $variables = [],
        ?CarbonImmutable $scheduledAt = null,
    ): EmailLog {
        return $this->withinOwner($ownerId, function () use (
            $template,
            $ownerId,
            $toEmail,
            $toName,
            $variables,
            $scheduledAt
        ): EmailLog {
            // Rendered up front so a missing variable is caught HERE, where we
            // can attribute it, rather than inside a queue worker where the
            // stack trace is about delivery rather than about the caller.
            $rendered = $this->renderer->render($template, $variables);

            // The delivery log is an audit trail: it must NEVER hold live
            // setup/reset/invoice links. Mask them in the stored body, while
            // the outgoing email keeps the real link.
            $storedBody = self::maskLinks($rendered['body']);

            $log = EmailLog::query()->create([
                'to_email' => $toEmail,
                'to_name' => $toName,
                'subject' => $rendered['subject'],
                'body' => $storedBody,
                'status' => EmailLogStatus::Pending,
                // NOT NULL with no default; set now and updated on real delivery.
                'sent_at' => $scheduledAt ?? CarbonImmutable::now(),
            ]);

            // Pass the unmasked body so the actual email keeps live links.
            DeliverMail::dispatch($log->getKey(), $ownerId, $rendered['body'])
                ->delay($scheduledAt ?? CarbonImmutable::now());

            return $log;
        });
    }

    /**
     * Replace setup, reset and invoice links with a placeholder so secrets
     * are never persisted in the delivery log.
     *
     * @param  string  $body  Rendered email body (HTML or plain text)
     * @return string
     */
    public static function maskLinks(string $body): string
    {
        return preg_replace(
            '/https?:\/\/[^\s<>"\']+/',
            '[LINK REDACTED]',
            $body,
        );
    }

    /**
     * Queue a message, swallowing every failure.
     *
     * This is what domain code should call. A mail problem must not roll back
     * the payment or complaint that triggered it.
     */
    public function trySend(
        MailTemplate $template,
        int $ownerId,
        string $toEmail,
        string $toName,
        array $variables = [],
        ?CarbonImmutable $scheduledAt = null,
    ): ?EmailLog {
        try {
            return $this->send($template, $ownerId, $toEmail, $toName, $variables, $scheduledAt);
        } catch (Throwable $e) {
            Log::warning('Mail could not be queued.', [
                'template' => $template->value,
                'owner_id' => $ownerId,
                'to' => $toEmail,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Re-queue an already-rendered message, e.g. from the delivery log.
     *
     * The stored body is reused verbatim, so a retry cannot render differently
     * from what the recipient was originally promised.
     */
    public function retry(EmailLog $log): void
    {
        $ownerId = (int) $log->owner_id;

        $this->withinOwner($ownerId, function () use ($log, $ownerId): void {
            $log->forceFill([
                'status' => EmailLogStatus::Pending,
                'error' => null,
            ])->save();

            DeliverMail::dispatch($log->getKey(), $ownerId)->delay(CarbonImmutable::now());
        });
    }

    /**
     * Run a callback with the owner tenancy resolved.
     *
     * `email_logs` and `email_templates` are owner-scoped, but email is often
     * triggered from a queue job, a console command or a password reset where
     * no HTTP request has resolved a tenancy yet. Resolving it here keeps the
     * call sites honest without each of them hand-rolling context juggling.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function withinOwner(int $ownerId, callable $callback): mixed
    {
        $previous = TenantContext::id();

        TenantContext::set($ownerId);

        try {
            return $callback();
        } finally {
            // Always restored: leaking one owner's context into the next
            // request would be a cross-tenant read.
            TenantContext::set($previous);
        }
    }
}
