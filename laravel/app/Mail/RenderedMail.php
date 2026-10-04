<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * A message whose subject and body were already rendered.
 *
 * WHY A MAILABLE WHEN THE BODY IS PRE-RENDERED
 * `Mail::html()` looks simpler, but it bypasses Laravel's mail fakes
 * entirely: MailFake has no html() method, so Mail::fake() neither records the
 * send nor stops it going to a real transport. Tests could not assert that an
 * email was sent, and `fake()` gave a false sense of safety.
 *
 * Wrapping the rendered HTML in a Mailable keeps the standard path:
 * MailFake records it, queueing works, and a transport failure is a normal
 * exception that DeliverMail's retry logic can see.
 *
 * The body is handed over already-rendered because an OWNER OVERRIDE is not
 * Blade (see RenderMailTemplate) and must never reach the template compiler.
 */
class RenderedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        // `subject` is already a Mailable method, hence the distinct property
        // names; the message properties stay untouched.
        private readonly string $mailSubject,
        private readonly string $mailBody,
    ) {}

    public function build(): self
    {
        return $this
            ->subject($this->mailSubject)
            ->html($this->mailBody);
    }
}
