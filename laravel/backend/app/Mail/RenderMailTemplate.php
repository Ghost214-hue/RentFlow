<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Support\TenantContext;
use RuntimeException;

/**
 * Turns a MailTemplate plus variables into a subject and body.
 *
 * Resolution order, and WHY it is this way:
 *
 *   1. the OWNER'S OWN row in `email_templates`   (they customised it)
 *   2. the shipped Blade view in resources/views/mail
 *
 * The legacy install only ever seeded owner 1, so simply reading that owner's
 * rows as a fallback for everyone else would be a CROSS-TENANT READ -- one
 * landlord seeing (and another editing) another's templates. Each owner gets
 * the shipped default until they write their own.
 *
 * OWNER OVERRIDES ARE NOT BLADE, AND THAT IS DELIBERATE.
 * An override body is stored as text and substituted with a plain
 * {{placeholder}} pass. It is never handed to the Blade compiler: `@php`,
 * `@include` and friends would turn "customise your welcome email" into
 * arbitrary server-side code execution. Shipped templates are Blade because
 * they are ours; overrides are not because they are theirs.
 */
final class RenderMailTemplate
{
    /**
     * @return array{subject: string, body: string}
     *
     * @throws MissingMailVariables when a required variable is absent, so the
     *                              message fails loudly instead of sending
     *                              "Dear ," to a real person.
     */
    public function render(MailTemplate $template, array $variables): array
    {
        $this->assertVariablesPresent($template, $variables);

        $override = $this->ownerOverride($template);

        $subject = $override?->subject !== null && trim($override->subject) !== ''
            ? $this->substitute($override->subject, $variables)
            : $this->substitute($template->defaultSubject(), $variables);

        $body = $override !== null
            ? $this->substitute($override->body ?? '', $variables)
            : $this->renderBlade($template, $variables);

        return ['subject' => $subject, 'body' => $body];
    }

    /**
     * @throws MissingMailVariables
     */
    private function assertVariablesPresent(MailTemplate $template, array $variables): void
    {
        // Null and '' both count as missing: a template branching on @if would
        // render the empty branch, which is not what the caller intended.
        $missing = array_values(array_filter(
            $template->requiredVariables(),
            static fn (string $key): bool => ! array_key_exists($key, $variables)
                || $variables[$key] === null
                || $variables[$key] === '',
        ));

        if ($missing !== []) {
            throw MissingMailVariables::for($template, $missing);
        }
    }

    /**
     * The signed-in owner's own override row, if they have written one.
     */
    private function ownerOverride(MailTemplate $template): ?EmailTemplate
    {
        $ownerId = TenantContext::id();

        if ($ownerId === null) {
            return null;
        }

        return EmailTemplate::query()
            ->where('name', $template->value)
            ->first();
    }

    /**
     * Substitute {{name}} with an HTML-escaped value.
     *
     * Escaped because the result lands in an HTML email: an unescaped
     * {{tenant}} carrying a name like `<script>` would otherwise execute in
     * the reader's mail client.
     */
    private function substitute(string $text, array $variables): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*(\w+)\s*\}\}/',
            function (array $m) use ($variables): string {
                $value = $variables[$m[1]] ?? '';

                return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            },
            $text,
        );
    }

    /**
     * Render the shipped Blade view.
     *
     * Blade's own {{ }} escaping applies inside the view, so values are
     * escaped once here and NOT pre-escaped by substitute().
     */
    private function renderBlade(MailTemplate $template, array $variables): string
    {
        $view = $template->view();

        if (! view()->exists($view)) {
            throw new RuntimeException(sprintf(
                'Mail view "%s" for %s is missing. Every MailTemplate case needs its Blade template.',
                $view,
                $template->value,
            ));
        }

        return (string) view($view, $variables)->render();
    }
}
