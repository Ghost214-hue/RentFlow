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
        
        // Helper to read from $_ENV with getenv() fallback (web vs CLI compatibility)
        $env = function(string $key, mixed $default = null): mixed {
            return $_ENV[$key] ?? getenv($key) ?: $default;
        };
        
        $this->fromEmail = $env('MAIL_FROM_EMAIL', 'noreply@rentflow.com');
        $this->fromName = $env('MAIL_FROM_NAME', 'RentFlow');
        
        // SMTP configuration
        $this->useSMTP = filter_var($env('MAIL_USE_SMTP', false), FILTER_VALIDATE_BOOLEAN);
        $this->smtpHost = $env('MAIL_HOST', 'smtp.gmail.com');
        $this->smtpPort = (int) $env('MAIL_PORT', 587);
        $this->smtpUsername = $env('MAIL_USERNAME', '');
        $this->smtpPassword = $env('MAIL_PASSWORD', '');
        $this->smtpEncryption = $env('MAIL_ENCRYPTION', 'tls');
        
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
            '{{name}}' => $data['name'] ?? '',
            '{{code}}' => $data['code'] ?? '',
            '{{expires}}' => $data['expires'] ?? '',
            '{{tenant}}' => $data['tenant'] ?? '',
            '{{tenant_name}}' => $data['tenant_name'] ?? $data['tenant'] ?? '',
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
            '{{link}}' => $data['link'] ?? getenv('APP_URL') ?: $_ENV['APP_URL'] ?? 'http://localhost',
            '{{owner_name}}' => $data['owner_name'] ?? '',
            '{{days_until_due}}' => $data['days_until_due'] ?? '',
            '{{reply_text}}' => $data['reply_text'] ?? '',
            '{{reason}}' => $data['reason'] ?? '',
            '{{title}}' => $data['title'] ?? '',
            '{{sender_name}}' => $data['sender_name'] ?? '',
            '{{description}}' => $data['description'] ?? '',
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
            // Try to log email to database (optional - don't fail if table doesn't exist)
            try {
                $logId = $this->db->insert('email_logs', [
                    'owner_id' => Router::getAuthUserId() ?? 0,
                    'to_email' => $toEmail,
                    'to_name' => $toName,
                    'subject' => $subject,
                    'body' => $body,
                    'status' => 'pending',
                    'sent_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $logException) {
                // If email_logs table doesn't exist, continue without logging
                error_log("EMAIL LOGGING DISABLED: " . $logException->getMessage());
                error_log("TIP: Run 'php backend/database/migrate.php' to create email_logs table");
            }
            
            $sent = false;
            $errorMsg = '';
            
            if ($this->useSMTP && !empty($this->smtpUsername) && !empty($this->smtpPassword)) {
                error_log("EMAIL: Attempting SMTP send to {$toEmail}");
                $sent = $this->sendViaSMTP($toEmail, $toName, $subject, $body);
                if (!$sent) {
                    $errorMsg = 'SMTP delivery failed - check /var/log/apache2/error.log or run: tail -f /var/log/apache2/error.log';
                }
            } else {
                // Fallback to PHP mail() function
                error_log("EMAIL: Attempting PHP mail() send to {$toEmail}");
                $sent = $this->sendViaMail($toEmail, $toName, $subject, $body);
                if (!$sent) {
                    $errorMsg = 'PHP mail() function failed - check server mail configuration';
                }
            }
            
            // Update log status if logging is available
            if ($logId) {
                try {
                    $this->db->update('email_logs', [
                        'status' => $sent ? 'sent' : 'failed',
                        'error' => $sent ? null : $errorMsg
                    ], 'id = ?', [$logId]);
                } catch (\Exception $updateException) {
                    error_log("EMAIL LOG UPDATE FAILED: " . $updateException->getMessage());
                }
            }
            
            if ($sent) {
                error_log("EMAIL SENT SUCCESSFULLY: To: $toEmail, Subject: $subject");
            } else {
                error_log("EMAIL FAILED: To: $toEmail, Subject: $subject. $errorMsg");
            }
            
            return $sent;
            
        } catch (\Exception $e) {
            $errorMsg = "Exception: " . $e->getMessage();
            error_log("EMAIL ERROR: " . $errorMsg . "\nTrace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Send email via SMTP (PHPMailer-style implementation using fsockopen)
     */
    private function sendViaSMTP(string $toEmail, string $toName, string $subject, string $body): bool
    {
        try {
            error_log("SMTP: Attempting to connect to {$this->smtpHost}:{$this->smtpPort}");
            
            $socket = fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 30);
            
            if (!$socket) {
                error_log("SMTP CONNECTION FAILED: $errstr (Error code: $errno)");
                error_log("SMTP: Check if port {$this->smtpPort} is open and firewall allows outbound connections");
                return false;
            }
            
            error_log("SMTP: Connected successfully");
            $response = fgets($socket, 515);
            error_log("SMTP Initial response: " . trim($response));
            
            if (substr($response, 0, 3) != '220') {
               error_log("SMTP: Unexpected initial response code: " . substr($response, 0, 3));
                fclose($socket);
                return false;
            }
            
            // EHLO
            fputs($socket, "EHLO " . gethostname() . "\r\n");
            $response = $this->readMultiLineResponse($socket);
            error_log("SMTP EHLO response: " . trim($response));
            
            // AUTH LOGIN
            if ($this->smtpEncryption === 'tls' || $this->smtpEncryption === 'ssl') {
                error_log("SMTP: Starting TLS encryption");
                fputs($socket, "STARTTLS\r\n");
                $response = fgets($socket, 515);
                error_log("SMTP STARTTLS response: " . trim($response));
                
                if (substr($response, 0, 3) != '220') {
                    error_log("SMTP: STARTTLS failed");
                    fclose($socket);
                    return false;
                }
                
                $cryptoResult = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if ($cryptoResult === false) {
                    error_log("SMTP: Failed to enable TLS encryption");
                    fclose($socket);
                    return false;
                }
                error_log("SMTP: TLS encryption enabled");
                
                fputs($socket, "EHLO " . gethostname() . "\r\n");
                $response = $this->readMultiLineResponse($socket);
                error_log("SMTP EHLO (after TLS) response: " . trim($response));
            }
            
            error_log("SMTP: Attempting authentication");
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            error_log("SMTP AUTH LOGIN response: " . trim($response));
            
            if (substr($response, 0, 3) != '334') {
                error_log("SMTP: AUTH LOGIN not accepted");
                fclose($socket);
                return false;
            }
            
            fputs($socket, base64_encode($this->smtpUsername) . "\r\n");
            $response = fgets($socket, 515);
            error_log("SMTP Username response: " . trim($response));
            
            if (substr($response, 0, 3) != '334') {
                error_log("SMTP: Username rejected");
                fclose($socket);
                return false;
            }
            
            fputs($socket, base64_encode($this->smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            error_log("SMTP Password response: " . trim($response));
            
            if (substr($response, 0, 3) != '235') {
                error_log("SMTP: Authentication FAILED - Check username/password");
                error_log("SMTP: For Gmail, use an App Password if 2FA is enabled");
                fclose($socket);
                return false;
            }
            
            error_log("SMTP: Authentication successful");
            
            // From
            fputs($socket, "MAIL FROM: <{$this->fromEmail}>\r\n");
            $response = fgets($socket, 515);
            error_log("SMTP MAIL FROM response: " . trim($response));
            
            if (substr($response, 0, 3) != '250') {
                error_log("SMTP: MAIL FROM rejected");
                fclose($socket);
                return false;
            }
            
            // To
            fputs($socket, "RCPT TO: <{$toEmail}>\r\n");
            $response = fgets($socket, 515);
            error_log("SMTP RCPT TO response: " . trim($response));
            
            if (substr($response, 0, 3) != '250') {
                error_log("SMTP: RCPT TO rejected - Recipient may not exist");
                fclose($socket);
                return false;
            }
            
            // Data
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            error_log("SMTP DATA response: " . trim($response));
            
            if (substr($response, 0, 3) != '354') {
                error_log("SMTP: DATA command not accepted");
                fclose($socket);
                return false;
            }
            
            // Headers and body
            $headers = [
                "From: {$this->fromName} <{$this->fromEmail}>",
                "Reply-To: {$this->fromEmail}",
                "To: {$toName} <{$toEmail}>",
                "Subject: {$subject}",
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8",
                "X-Mailer: RentFlow/" . (getenv('APP_VERSION') ?: $_ENV['APP_VERSION'] ?? '1.0')
            ];
            
            $message = implode("\r\n", $headers) . "\r\n\r\n" . nl2br($body) . "\r\n.\r\n";
            fputs($socket, $message);
            
            $response = fgets($socket, 515);
            error_log("SMTP Message sent response: " . trim($response));
            
            // Quit
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            $success = substr($response, 0, 3) == '250';
            if ($success) {
                error_log("SMTP: Email sent successfully to {$toEmail}");
            } else {
                error_log("SMTP: Email not accepted by server");
            }
            
            return $success;
            
        } catch (\Exception $e) {
            error_log("SMTP EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Read multi-line SMTP response (response code followed by lines starting with whitespace)
     */
    private function readMultiLineResponse($socket): string
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            // SMTP multi-line responses end with code followed by space (not hyphen)
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }
        return $response;
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
            "X-Mailer: RentFlow/" . (getenv('APP_VERSION') ?: $_ENV['APP_VERSION'] ?? '1.0')
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
            'link' => getenv('APP_URL') ?: $_ENV['APP_URL'] ?? 'http://localhost'
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
            'link' => getenv('APP_URL') ?: $_ENV['APP_URL'] ?? 'http://localhost'
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
    
    /**
     * Send vacate confirmation email to tenant
     */
    public function sendTenantVacate(int $ownerId, int $tenantId, string $propertyName, string $houseUnit): bool
    {
        $tenant = $this->db->fetchOne(
            "SELECT name, email, id_number FROM tenants WHERE id = ? AND owner_id = ?",
            [$tenantId, $ownerId]
        );
        
        if (!$tenant || empty($tenant['email'])) {
            return false;
        }
        
        $variables = [
            'tenant' => $tenant['name'],
            'property' => $propertyName,
            'house' => $houseUnit,
            'date' => date('Y-m-d'),
            'national_id' => $tenant['id_number'] ?? ''
        ];
        
        return $this->sendTemplate('Tenant Vacate', $ownerId, $tenant['email'], $tenant['name'], $variables);
    }
}
