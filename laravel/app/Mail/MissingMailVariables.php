<?php

declare(strict_types=1);

namespace App\Mail;

use RuntimeException;

/**
 * A required variable was missing, so the message was NOT sent.
 *
 * This exists so a broken email is a loud, attributable failure in the delivery
 * log rather than a blank greeting that reaches a tenant. It is deliberately a
 * distinct type: the queue job catches everything, but tests and the
 * `/email-logs` page can single this one out, because "we forgot to pass the
 * amount" is a bug in this codebase, not a transport problem.
 */
final class MissingMailVariables extends RuntimeException
{
    /** @param list<string> $missing */
    private function __construct(
        public readonly MailTemplate $template,
        public readonly array $missing,
        string $message,
    ) {
        parent::__construct($message);
    }

    /** @param list<string> $missing */
    public static function for(MailTemplate $template, array $missing): self
    {
        return new self(
            $template,
            $missing,
            sprintf(
                'Refusing to send "%s": missing required variable(s) %s. The message was not sent.',
                $template->value,
                implode(', ', $missing),
            ),
        );
    }
}
