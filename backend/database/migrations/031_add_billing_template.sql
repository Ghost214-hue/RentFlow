-- Billing Notification email template (stored in the database, not hardcoded).
-- Idempotent and FK-safe: the template is created/updated for EVERY existing owner by
-- reading owner_id from the `owners` table, so it never references a non-existent owner.
--
-- Supported placeholders (see EmailService::replaceVariables):
--   {{recipient_name}} {{recipient_note}} {{amount}} {{month}} {{property}} {{house}}
--   {{balance}} {{date}} {{invoice_url}} {{owner_name}} {{payment_instructions}}

SET @subject = 'Your {{month}} invoice for {{property}} {{house}} - KES {{amount}}';
SET @body = 'Dear {{recipient_name}},

{{recipient_note}}

Here are the details of your invoice:

    Property:        {{property}}
    Unit:            {{house}}
    Billing period:  {{month}}
    Amount due:      KES {{amount}}
    Balance:         KES {{balance}}
    Invoice date:    {{date}}

{{payment_instructions}}

You can view and download a copy of your invoice here:
{{invoice_url}}

If you have any questions about this invoice, please contact the property office ({{owner_name}}).

Thank you,
RentaFlow Team';

-- 1) Primary table: templates
UPDATE templates
SET subject = @subject, body = @body
WHERE name = 'Billing Notification' AND type = 'email';

INSERT INTO templates (owner_id, name, type, subject, body)
SELECT o.id, 'Billing Notification', 'email', @subject, @body
FROM owners o
WHERE NOT EXISTS (
    SELECT 1 FROM templates t
    WHERE t.owner_id = o.id AND t.name = 'Billing Notification' AND t.type = 'email'
);

-- 2) Legacy table: email_templates (kept in sync)
UPDATE email_templates
SET subject = @subject, body = @body
WHERE name = 'Billing Notification' AND type = 'email';

INSERT INTO email_templates (owner_id, name, type, subject, body)
SELECT o.id, 'Billing Notification', 'email', @subject, @body
FROM owners o
WHERE NOT EXISTS (
    SELECT 1 FROM email_templates et
    WHERE et.owner_id = o.id AND et.name = 'Billing Notification' AND et.type = 'email'
);

