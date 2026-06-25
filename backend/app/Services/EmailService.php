<?php
/**
 * Email Service - Handles sending emails using templates from database
 */
namespace App\Services;

use App\Core\Database;
use App\Core\Router;

class EmailService
{
    private Database $db;
    private string $fromEmail;
    private string $fromName;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->fromEmail = $_ENV['MAIL_FROM_EMAIL'] ?? 'noreply@rentflow.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'RentFlow';
    }
    
    /**
     * Get email template by name and owner
     */
    public function getTemplate(string $name, int $ownerId): ?array
    {
        $template = $this->db->fetchOne(
            "SELECT * FROM email_templates WHERE owner_id = ? AND name = ? AND type = 'email'",
            [$ownerId, $name]
        );
        
        if (!$template) {
            // Try to get default template (owner_id = 1 or system template)
            $template = $this->db->fetchOne(
                "SELECT * FROM email_templates WHERE owner_id = 1 AND name = ? AND type = 'email'",
                [$name]
            );
        }
        
        return $template ?: null;
    }
    
    /**
     * Replace template variables with actual values
     * Supports: {{tenant}}, {{amount}}, {{month}}, {{property}}, {{house}}, {{id}}, {{balance}}, {{category}}, {{date}}
     */
    public function replaceVariables(string $content, array $data): string
    {
        $replacements = [
            '{{tenant}}' => $data['tenant'] ?? '',
            '{{amount}}' => $data['amount'] ?? '',
            '{{month}}' => $data['month'] ?? '',
            '{{property}}' => $data['property'] ?? '',
            '{{house}}' => $data['house'] ?? '',
            '{{id}}' => $data['id'] ?? '',
            '{{balance}}' => $data['balance'] ?? '',
            '{{category}}' => $data['category'] ?? '',
            '{{date}}' => $data['date'] ?? date('Y-m-d'),
            '{{email}}' => $data['email'] ?? '',
            '{{password}}' => $data['password'] ?? '',
            '{{national_id}}' => $data['national_id'] ?? '',
            '{{phone}}' => $data['phone'] ?? '',
            '{{link}}' => $data['link'] ?? $_ENV['APP_URL'] ?? 'http://localhost',
            '{{owner_name}}' => $data['owner_name'] ?? '',
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
    
    /**
     * Send email using a template
     */
    public function sendTemplate(string $templateName, int $ownerId, string $toEmail, string $toName, array $variables = []): bool
    {
        $template = $this->getTemplate($templateName, $ownerId);
        
        if (!$template) {
            error_log("Email template not found: $templateName for owner $ownerId");
            return false;
        }
        
        $subject = $this->replaceVariables($template['subject'], $variables);
        $body = $this->replaceVariables($template['body'], $variables);
        
        return $this->send($toEmail, $toName, $subject, $body);
    }
    
    /**
     * Send a simple email
     */
    public function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        // For now, we'll log emails. In production, integrate with PHPMailer, SendGrid, Mailgun, etc.
        $emailLog = [
            'to' => $toEmail,
            'to_name' => $toName,
            'from' => $this->fromEmail,
            'subject' => $subject,
            'body' => $body,
            'sent_at' => date('Y-m-d H:i:s')
        ];
        
        // Log email to database for tracking
        $this->db->insert('email_logs', [
            'owner_id' => Router::getAuthUserId() ?? 0,
            'to_email' => $toEmail,
            'to_name' => $toName,
            'subject' => $subject,
            'body' => $body,
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
        
        // TODO: Integrate with actual email provider (PHPMailer, SendGrid, Mailgun, etc.)
        // For development, we'll just log it
        error_log("EMAIL SENT: To: $toEmail, Subject: $subject");
        
        return true;
    }
    
    /**
     * Send welcome email to new tenant
     */
    public function sendTenantWelcome(int $ownerId, array $tenant, string $propertyName, string $houseUnit): bool
    {
        $variables = [
            'tenant' => $tenant['name'],
            'property' => $propertyName,
            'house' => $houseUnit,
            'email' => $tenant['email'],
            'password' => $tenant['temp_password'] ?? $tenant['id_number'], // Temporary password
            'national_id' => $tenant['id_number'] ?? '',
            'link' => $_ENV['APP_URL'] ?? 'http://localhost'
        ];
        
        return $this->sendTemplate('Tenant Welcome', $ownerId, $tenant['email'], $tenant['name'], $variables);
    }
    
    /**
     * Send welcome email to new caretaker
     */
    public function sendCaretakerWelcome(int $ownerId, array $caretaker): bool
    {
        $variables = [
            'tenant' => $caretaker['name'], // Reuse tenant variable for name
            'email' => $caretaker['email'],
            'password' => $caretaker['temp_password'] ?? $caretaker['id_number'],
            'national_id' => $caretaker['id_number'] ?? '',
            'link' => $_ENV['APP_URL'] ?? 'http://localhost'
        ];
        
        return $this->sendTemplate('Caretaker Welcome', $ownerId, $caretaker['email'], $caretaker['name'], $variables);
    }
    
    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation(int $ownerId, array $tenant, array $payment): bool
    {
        $variables = [
            'tenant' => $tenant['name'],
            'amount' => number_format($payment['amount'], 2),
            'balance' => number_format($payment['balance'] ?? 0, 2),
            'month' => $payment['month'] ?? date('F Y'),
            'category' => $payment['category'] ?? 'Rent',
            'date' => date('Y-m-d', strtotime($payment['date'] ?? 'now'))
        ];
        
        return $this->sendTemplate('Payment Confirmation', $ownerId, $tenant['email'], $tenant['name'], $variables);
    }
    
    /**
     * Send complaint update email
     */
    public function sendComplaintUpdate(int $ownerId, array $tenant, array $complaint, ?string $replyText = null): bool
    {
        $variables = [
            'tenant' => $tenant['name'],
            'id' => $complaint['id'],
            'category' => $complaint['category'],
            'date' => date('Y-m-d', strtotime($complaint['date'] ?? 'now'))
        ];
        
        $templateName = $replyText ? 'Complaint Reply' : 'Complaint Update';
        
        return $this->sendTemplate($templateName, $ownerId, $tenant['email'], $tenant['name'], $variables);
    }
    
    /**
     * Send rent reminder email
     */
    public function sendRentReminder(int $ownerId, array $tenant, string $month, float $amount, int $daysUntilDue): bool
    {
        $variables = [
            'tenant' => $tenant['name'],
            'amount' => number_format($amount, 2),
            'month' => date('F Y', strtotime($month . '-01')),
            'balance' => number_format($tenant['balance'] ?? 0, 2),
            'date' => date('Y-m-d')
        ];
        
        $templateName = $daysUntilDue <= 1 ? 'Rent Reminder Final' : 'Rent Reminder';
        
        return $this->sendTemplate($templateName, $ownerId, $tenant['email'], $tenant['name'], $variables);
    }
}