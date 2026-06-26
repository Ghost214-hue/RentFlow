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
    private bool $useSMTP;
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;
    private string $smtpEncryption;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->fromEmail = $_ENV['MAIL_FROM_EMAIL'] ?? 'noreply@rentflow.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'RentFlow';
        
        // SMTP configuration
        $this->useSMTP = filter_var($_ENV['MAIL_USE_SMTP'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->smtpHost = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $this->smtpPort = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $this->smtpUsername = $_ENV['MAIL_USERNAME'] ?? '';
        $this->smtpPassword = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->smtpEncryption = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
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
            '{{days_until_due}}' => $data['days_until_due'] ?? '',
            '{{reply_text}}' => $data['reply_text'] ?? '',
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
        $logId = null;
        
        try {
            // Log email to database first
            $logId = $this->db->insert('email_logs', [
                'owner_id' => Router::getAuthUserId() ?? 0,
                'to_email' => $toEmail,
                'to_name' => $toName,
                'subject' => $subject,
                'body' => $body,
                'status' => 'pending',
                'sent_at' => date('Y-m-d H:i:s')
            ]);
            
            $sent = false;
            
            if ($this->useSMTP && !empty($this->smtpUsername) && !empty($this->smtpPassword)) {
                $sent = $this->sendViaSMTP($toEmail, $toName, $subject, $body);
            } else {
                // Fallback to PHP mail() function
                $sent = $this->sendViaMail($toEmail, $toName, $subject, $body);
            }
            
            // Update log status
            if ($logId) {
                $this->db->update('email_logs', [
                    'status' => $sent ? 'sent' : 'failed'
                ], 'id = ?', [$logId]);
            }
            
            if ($sent) {
                error_log("EMAIL SENT: To: $toEmail, Subject: $subject");
            } else {
                error_log("EMAIL FAILED: To: $toEmail, Subject: $subject");
            }
            
            return $sent;
            
        } catch (\Exception $e) {
            error_log("EMAIL ERROR: " . $e->getMessage());
            
            // Update log with error
            if ($logId) {
                $this->db->update('email_logs', [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ], 'id = ?', [$logId]);
            }
            
            return false;
        }
    }
    
    /**
     * Send email via SMTP (PHPMailer-style implementation using fsockopen)
     */
    private function sendViaSMTP(string $toEmail, string $toName, string $subject, string $body): bool
    {
        try {
            $socket = fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 30);
            
            if (!$socket) {
                error_log("SMTP connection failed: $errstr ($errno)");
                return false;
            }
            
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                fclose($socket);
                return false;
            }
            
            // EHLO
            fputs($socket, "EHLO " . gethostname() . "\r\n");
            $response = fgets($socket, 515);
            
            // AUTH LOGIN
            if ($this->smtpEncryption === 'tls') {
                fputs($socket, "STARTTLS\r\n");
                $response = fgets($socket, 515);
                if (substr($response, 0, 3) != '220') {
                    fclose($socket);
                    return false;
                }
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }
            
            fputs($socket, "EHLO " . gethostname() . "\r\n");
            fgets($socket, 515);
            
            fputs($socket, "AUTH LOGIN\r\n");
            fgets($socket, 515);
            
            fputs($socket, base64_encode($this->smtpUsername) . "\r\n");
            fgets($socket, 515);
            
            fputs($socket, base64_encode($this->smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            
            if (substr($response, 0, 3) != '235') {
                fclose($socket);
                return false;
            }
            
            // From
            fputs($socket, "MAIL FROM: <{$this->fromEmail}>\r\n");
            fgets($socket, 515);
            
            // To
            fputs($socket, "RCPT TO: <{$toEmail}>\r\n");
            fgets($socket, 515);
            
            // Data
            fputs($socket, "DATA\r\n");
            fgets($socket, 515);
            
            // Headers and body
            $headers = [
                "From: {$this->fromName} <{$this->fromEmail}>",
                "Reply-To: {$this->fromEmail}",
                "To: {$toName} <{$toEmail}>",
                "Subject: {$subject}",
                "MIME-Version: 1.0",
                "Content-Type: text/plain; charset=UTF-8",
                "X-Mailer: RentFlow/" . ($_ENV['APP_VERSION'] ?? '1.0')
            ];
            
            $message = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n";
            fputs($socket, $message);
            
            $response = fgets($socket, 515);
            
            // Quit
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            return substr($response, 0, 3) == '250';
            
        } catch (\Exception $e) {
            error_log("SMTP error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using PHP mail() function
     */
    private function sendViaMail(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $headers = [
            "From: {$this->fromName} <{$this->fromEmail}>",
            "Reply-To: {$this->fromEmail}",
            "MIME-Version: 1.0",
            "Content-Type: text/plain; charset=UTF-8",
            "X-Mailer: RentFlow/" . ($_ENV['APP_VERSION'] ?? '1.0')
        ];
        
        $headerString = implode("\r\n", $headers);
        
        return mail($toEmail, $subject, $body, $headerString);
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
            'password' => $tenant['temp_password'] ?? $tenant['id_number'],
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
            'tenant' => $caretaker['name'],
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
            'date' => date('Y-m-d', strtotime($complaint['date'] ?? 'now')),
            'reply_text' => $replyText ?? ''
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
            'date' => date('Y-m-d'),
            'days_until_due' => $daysUntilDue
        ];
        
        $templateName = $daysUntilDue <= 1 ? 'Rent Reminder Final' : 'Rent Reminder';
        
        return $this->sendTemplate($templateName, $ownerId, $tenant['email'], $tenant['name'], $variables);
    }
}