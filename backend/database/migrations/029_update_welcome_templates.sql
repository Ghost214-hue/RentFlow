-- Update "Tenant Welcome" templates for all owners
-- Remove {{password}}/{{national_id}} references, replace with {{setup_link}}
UPDATE templates
SET subject = 'Welcome to {{property}} - Set Up Your Account',
    body = 'Dear {{tenant}},

Welcome to {{property}}! We are excited to have you as our tenant.

Your unit: {{house}}
Your email: {{email}}

To activate your account and set your password, please click the link below:

{{setup_link}}

This link will expire in 48 hours. If you need a new link, please contact your property manager.

Best regards,
RentaFlow Team'
WHERE name = 'Tenant Welcome' AND type = 'email';

-- Update "Caretaker Welcome" templates for all owners
UPDATE templates
SET subject = 'Welcome to RentaFlow - Set Up Your Account',
    body = 'Dear {{tenant}},

Welcome to RentaFlow! You have been registered as a caretaker.

Your email: {{email}}

To activate your account and set your password, please click the link below:

{{setup_link}}

This link will expire in 48 hours. If you need a new link, please contact your property manager.

Best regards,
RentaFlow Team'
WHERE name = 'Caretaker Welcome' AND type = 'email';

-- Also update email_templates table if it exists (legacy table name)
UPDATE email_templates
SET subject = 'Welcome to {{property}} - Set Up Your Account',
    body = 'Dear {{tenant}},

Welcome to {{property}}! We are excited to have you as our tenant.

Your unit: {{house}}
Your email: {{email}}

To activate your account and set your password, please click the link below:

{{setup_link}}

This link will expire in 48 hours. If you need a new link, please contact your property manager.

Best regards,
RentaFlow Team'
WHERE name = 'Tenant Welcome' AND type = 'email';

UPDATE email_templates
SET subject = 'Welcome to RentaFlow - Set Up Your Account',
    body = 'Dear {{tenant}},

Welcome to RentaFlow! You have been registered as a caretaker.

Your email: {{email}}

To activate your account and set your password, please click the link below:

{{setup_link}}

This link will expire in 48 hours. If you need a new link, please contact your property manager.

Best regards,
RentaFlow Team'
WHERE name = 'Caretaker Welcome' AND type = 'email';