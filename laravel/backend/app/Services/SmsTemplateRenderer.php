<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Renders SMS message bodies from template name + variables.
 *
 * SMS bodies live as plain-text strings keyed by template name. The renderer
 * substitutes {{variable}} placeholders and returns a short SMS body (under
 * 160 chars where possible) plus a longer body for concatenated SMS.
 */
final class SmsTemplateRenderer
{
    /** @var array<string, string> */
    private const TEMPLATES = [
        'bill_generated' => "RentFlow: Your {{month}} rent bill of KES {{amount}} is due on {{due_date}}. Pay via {{payment_instructions}}. Reply STOP to opt out.",
        'rent_reminder' => "RentFlow reminder: {{month}} rent of KES {{amount}} is due {{due_date}}. Balance: KES {{balance}}. {{payment_instructions}} Reply STOP to opt out.",
        'rent_reminder_final' => "RENTFLOW URGENT: {{month}} rent of KES {{amount}} is {{days_overdue}} day(s) overdue. Pay now: {{payment_instructions}}. Reply STOP to opt out.",
        'payment_confirmation' => "RentFlow: Payment of KES {{amount}} received for {{category}} on {{date}}. New balance: KES {{balance}}. Reply STOP to opt out.",
    ];

    /** @param  array<string, mixed>  $variables */
    public function render(string $template, array $variables): array
    {
        if (! isset(self::TEMPLATES[$template])) {
            throw new \InvalidArgumentException("Unknown SMS template: {$template}");
        }

        $body = self::TEMPLATES[$template];

        foreach ($variables as $key => $value) {
            $body = str_replace('{{'.$key.'}}', (string) $value, $body);
        }

        return [
            'body' => $body,
        ];
    }

    /** @return array<string, string> */
    public function templates(): array
    {
        return self::TEMPLATES;
    }
}