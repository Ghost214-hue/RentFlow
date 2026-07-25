<?php

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
    private string $lastError = '';
    
    private EmailQueueService $queueService;
    private bool $queueEnabled;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Load env if not already loaded
        \App\Core\Env::load();
        
        // Direct reads from $_ENV (already set by Env::load())
        $this->fromEmail = $_ENV['MAIL_FROM_EMAIL'] ?? getenv('MAIL_FROM_EMAIL') ?: 'noreply@rentalflow.co.ke';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'RentaFlow';
        
        // SMTP configuration
        $this->useSMTP = filter_var($_ENV['MAIL_USE_SMTP'] ?? getenv('MAIL_USE_SMTP') ?: false, FILTER_VALIDATE_BOOLEAN);
        $this->smtpHost = $_ENV['MAIL_HOST'] ?? getenv('MAIL_HOST') ?: 'smtp.gmail.com';
        $this->smtpPort = (int) ($_ENV['MAIL_PORT'] ?? getenv('MAIL_PORT') ?: 587);
        $this->smtpUsername = $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?: '';
        $this->smtpPassword = $_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD') ?: '';
        $this->smtpEncryption = $_ENV['MAIL_ENCRYPTION'] ?? getenv('MAIL_ENCRYPTION') ?: 'tls';
        
        // Email queue configuration - DISABLED by default for immediate delivery
        $this->queueService = new EmailQueueService();
        $this->queueEnabled = filter_var(
            $_ENV['MAIL_QUEUE_ENABLED'] ?? getenv('MAIL_QUEUE_ENABLED') ?: false,
            FILTER_VALIDATE_BOOLEAN
        );

        if ($this->queueEnabled && !$this->useSMTP) {
            error_log("WARNING: MAIL_QUEUE_ENABLED=true but MAIL_USE_SMTP=false. " .
                "Queued email sending will use PHP mail(), which is unreliable on many Linux hosts. " .
                "Configure SMTP settings in .env or disable the email queue for immediate delivery.");
        }
    }
    
    /**
     * Get email template by name and owner
     */
    public function getTemplate(string $name, int $ownerId): ?array
    {
        // Try both table names for backwards compatibility
        try {
            $template = $this->db->fetchOne(
                "SELECT * FROM templates WHERE owner_id = ? AND name = ? AND type = 'email'",
                [$ownerId, $name]
            );
            if ($template) return $template;
        } catch (\Throwable $e) {
            error_log("getTemplate(templates) failed: " . $e->getMessage());
        }
        
        try {
            $template = $this->db->fetchOne(
                "SELECT * FROM templates WHERE owner_id = 1 AND name = ? AND type = 'email'",
                [$name]
            );
            if ($template) return $template;
        } catch (\Throwable $e) {
            error_log("getTemplate(templates default) failed: " . $e->getMessage());
        }
        
        try {
            $template = $this->db->fetchOne(
                "SELECT * FROM email_templates WHERE owner_id = ? AND name = ? AND type = 'email'",
                [$ownerId, $name]
            );
            if ($template) return $template;
        } catch (\Throwable $e) {
            error_log("getTemplate(email_templates) failed: " . $e->getMessage());
        }
        
        try {
            $template = $this->db->fetchOne(
                "SELECT * FROM email_templates WHERE owner_id = 1 AND name = ? AND type = 'email'",
                [$name]
            );
            if ($template) return $template;
        } catch (\Throwable $e) {
            error_log("getTemplate(email_templates default) failed: " . $e->getMessage());
        }
        
        return null;
    }
    
    public function replaceVariables(string $content, array $data): string
    {
        // Build invoice section if invoice_url is provided
        $invoiceSection = '';
        if (!empty($data['invoice_url'])) {
            $invoiceSection = "\n\nDownload Invoice:\n{$data['invoice_url']}\n\n";
        }
        
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
            '{{next_of_kin_intro}}' => $data['next_of_kin_intro'] ?? '',
            '{{recipient_name}}' => $data['recipient_name'] ?? '',
            '{{payment_instructions}}' => $data['payment_instructions'] ?? '',
            '{{invoice_section}}' => $invoiceSection,
            '{{invoice_url}}' => $data['invoice_url'] ?? '',
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
    
    /**
     * Send email using a template
     */
    public function sendTemplate(string $templateName, int $ownerId, string $toEmail, string $toName, array $variables = []): bool
    {
        try {
            $template = $this->getTemplate($templateName, $ownerId);
            
            if ($template) {
                $subject = $this->replaceVariables($template['subject'], $variables);
                $body = $this->replaceVariables($template['body'], $variables);
                $result = $this->send($toEmail, $toName, $subject, $body);
                if ($result) return true;
                error_log("sendTemplate: send() failed for $templateName to $toEmail");
            } else {
                error_log("Email template not found: '$templateName' for owner $ownerId");
            }
        } catch (\Throwable $e) {
            error_log("sendTemplate exception for $templateName: " . $e->getMessage());
        }
        
        // Fallback: send a direct email with basic info
        $subject = $templateName . ' - ' . ($variables['code'] ?? ($variables['name'] ?? $toName));
        $body = "Dear " . ($variables['name'] ?? $toName) . ",\n\n";
        foreach ($variables as $key => $value) {
            $body .= "$key: $value\n";
        }
        $body .= "\nBest regards,\nRentalFlow";
        
        return $this->send($toEmail, $toName, $subject, $body);
    }

    /**
     * Queue an email template. Falls back to immediate sending if the queue is unavailable.
     */
    public function queueTemplate(string $templateName, int $ownerId, string $toEmail, string $toName, array $variables = [], int $priority = 0, ?\DateTime $scheduledAt = null): bool
    {
        // Queue is disabled - send immediately
        return $this->sendTemplate($templateName, $ownerId, $toEmail, $toName, $variables);
    }
    
    /**
     * Send a simple email via SMTP only
     */
    public function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $logId = null;
        $errorMsg = null;
        
        try {
            // Validate recipient up front rather than discovering it late in RCPT TO
            if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                error_log("EMAIL ERROR: Invalid recipient address: {$toEmail}");
                return false;
            }
            
            // Validate SMTP configuration
            if (empty($this->smtpUsername) || empty($this->smtpPassword)) {
                error_log("EMAIL ERROR: SMTP credentials not configured. Check .env file.");
                return false;
            }
            
            // Try to log email to database (optional - don't fail if table doesn't exist)
            try {
                $userId = 0;
                if (class_exists('\App\Core\Router')) {
                    try { $userId = Router::getAuthUserId() ?? 0; } catch (\Throwable $t) {}
                }
                $logId = $this->db->insert('email_logs', [
                    'owner_id' => $userId,
                    'to_email' => $toEmail,
                    'to_name' => $toName,
                    'subject' => $subject,
                    'body' => $body,
                    'status' => 'pending',
                    'sent_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Throwable $logException) {
                // If email_logs table doesn't exist, continue without logging
                error_log("EMAIL LOGGING DISABLED: " . $logException->getMessage());
            }
            
            error_log("EMAIL: Attempting SMTP send to {$toEmail}");
            $sent = $this->sendViaSMTP($toEmail, $toName, $subject, $body);
            
            if (!$sent) {
                $errorMsg = $this->lastError ?: 'SMTP delivery failed - check credentials and firewall settings';
                error_log("EMAIL FAILED: To: $toEmail, Subject: $subject. $errorMsg");
            } else {
                error_log("EMAIL SENT SUCCESSFULLY: To: $toEmail, Subject: $subject");
            }
            
            // Update log status if logging is available
            if ($logId) {
                try {
                    $this->db->update('email_logs', [
                        'status' => $sent ? 'sent' : 'failed',
                        'error' => $sent ? null : ($errorMsg ?? 'Unknown error')
                    ], 'id = ?', [$logId]);
                } catch (\Throwable $updateException) {
                    error_log("EMAIL LOG UPDATE FAILED: " . $updateException->getMessage());
                }
            }
            
            return $sent;
            
        } catch (\Throwable $e) {
            $errorMsg = "Exception: " . $e->getMessage();
            error_log("EMAIL ERROR: " . $errorMsg . "\nTrace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    private function sendViaSMTP(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $socket = null;
        
        try {
            $this->lastError = '';
            error_log("SMTP: Attempting to connect to {$this->smtpHost}:{$this->smtpPort}");
            
            if (empty($this->smtpUsername) || empty($this->smtpPassword)) {
                $this->lastError = 'SMTP username or password not configured';
                error_log("SMTP ERROR: {$this->lastError}");
                return false;
            }
            
            if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                $this->lastError = "Invalid recipient address: {$toEmail}";
                error_log("SMTP ERROR: {$this->lastError}");
                return false;
            }
            
            // Connect with timeout
            $socket = @fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 30);
            
            if (!$socket) {
                $errorMsg = "Connection failed: $errstr (Error code: $errno)";
                $this->lastError = $errorMsg;
                error_log("SMTP CONNECTION FAILED: {$errorMsg}");
                error_log("SMTP TROUBLESHOOTING:");
                error_log("  1. Check if outbound port {$this->smtpPort} is blocked by firewall");
                error_log("  2. Verify hostname resolution: " . gethostbyname($this->smtpHost));
                error_log("  3. Try using PHP's mail() function instead or a dedicated email service");
                error_log("  4. For Gmail, ensure 'Less secure app access' is enabled or use App Password");
                
                if (function_exists('mail')) {
                    error_log("SMTP: Attempting fallback to PHP mail()");
                    return $this->sendViaPHP($toEmail, $toName, $subject, $body);
                }
                
                return false;
            }
            
            // Read timeout so a slow/loaded server can't leave fgets() hanging
            // or returning a truncated response that gets misread as a failure.
            stream_set_timeout($socket, 30);
            
            error_log("SMTP: Connected successfully");
            $response = $this->readMultiLineResponse($socket);
            error_log("SMTP Initial response: " . trim($response));
            
            if (substr($response, 0, 3) != '220') {
                $this->lastError = 'Unexpected initial response: ' . trim($response);
                error_log("SMTP: {$this->lastError}");
                fclose($socket);
                return false;
            }
            
            // EHLO
            $ehloHost = parse_url(getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? ''), PHP_URL_HOST) ?: 'rentalflow.co.ke';
            fputs($socket, "EHLO {$ehloHost}\r\n");
            $response = $this->readMultiLineResponse($socket);
            error_log("SMTP EHLO response: " . trim($response));
            
            // STARTTLS
            if ($this->smtpEncryption === 'tls' || $this->smtpEncryption === 'ssl') {
                error_log("SMTP: Starting TLS encryption");
                fputs($socket, "STARTTLS\r\n");
                $response = $this->readMultiLineResponse($socket);
                error_log("SMTP STARTTLS response: " . trim($response));
                
                if (substr($response, 0, 3) != '220') {
                    $this->lastError = 'STARTTLS failed: ' . trim($response);
                    error_log("SMTP: {$this->lastError}");
                    fclose($socket);
                    return false;
                }
                
                $cryptoResult = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if ($cryptoResult === false) {
                    $this->lastError = 'Failed to enable TLS encryption';
                    error_log("SMTP: {$this->lastError}");
                    fclose($socket);
                    return false;
                }
                error_log("SMTP: TLS encryption enabled");
                
                fputs($socket, "EHLO {$ehloHost}\r\n");
                $response = $this->readMultiLineResponse($socket);
                error_log("SMTP EHLO (after TLS) response: " . trim($response));
            }
            
            error_log("SMTP: Attempting authentication");
            
            $supportsAuth = stripos($response, 'AUTH') !== false;
            if (!$supportsAuth) {
                error_log("SMTP: Server does not advertise AUTH support - trying anyway");
            }
            
            fputs($socket, "AUTH LOGIN\r\n");
            $response = $this->readMultiLineResponse($socket);
            error_log("SMTP AUTH LOGIN response: " . trim($response));
            
            if (substr($response, 0, 3) !== '334') {
                $this->lastError = 'AUTH LOGIN not accepted: ' . trim($response);
                error_log("SMTP: {$this->lastError}");
                fclose($socket);
                return false;
            }
            
            fputs($socket, base64_encode($this->smtpUsername) . "\r\n");
            $response = $this->readMultiLineResponse($socket);
            error_log("SMTP Username response: " . trim($response));
            
            if (substr($response, 0, 3) !== '334') {
                $this->lastError = 'SMTP username rejected: ' . trim($response);
                error_log("SMTP: {$this->lastError}");
                fclose($socket);
                return false;
            }
            
            fputs($socket, base64_encode($this->smtpPassword) . "\r\n");
            $response = $this->readMultiLineResponse($socket);
            error_log("SMTP Password response: " . trim($response));
            
            if (substr($response, 0, 3) !== '235') {
                $this->lastError = 'SMTP authentication failed: ' . trim($response);
                error_log("SMTP: {$this->lastError}");
                error_log("SMTP: Check if credentials are correct for this server");
                fclose($socket);
                return false;
            }
            
            error_log("SMTP: Authentication successful");
            
            // Envelope sender MUST match the authenticated mailbox on most
            // shared-hosting Exim configs, or the relay is denied even after
            // successful AUTH. The display "From:" header (fromName/fromEmail,
            // set below in the message headers) can still differ from this —
            // only the SMTP envelope (MAIL FROM) is forced to the authenticated
            // account.
            $envelopeFrom = $this->smtpUsername;
            
            fputs($socket, "MAIL FROM:<{$envelopeFrom}>\r\n");
            $response = $this->readMultiLineResponse($socket);
            error_log("SMTP MAIL FROM response: " . trim($response));
            
            if (substr($response, 0, 3) != '250') {
                $this->lastError = 'MAIL FROM rejected: ' . trim($response);
                error_log("SMTP: {$this->lastError}");
                fclose($socket);
                return false;
            }
            
            fputs($socket, "RCPT TO:<{$toEmail}>\r\n");
            $response = $this->readMultiLineResponse($socket);
            error_log("SMTP RCPT TO response: " . trim($response));
            
            if (substr($response, 0, 3) != '250') {
                $this->lastError = 'RCPT TO rejected: ' . trim($response);
                error_log("SMTP: {$this->lastError}");
                fclose($socket);
                return false;
            }
            
            fputs($socket, "DATA\r\n");
            $response = $this->readMultiLineResponse($socket);
            error_log("SMTP DATA response: " . trim($response));
            
            if (substr($response, 0, 3) != '354') {
                $this->lastError = 'DATA command rejected: ' . trim($response);
                error_log("SMTP: {$this->lastError}");
                fclose($socket);
                return false;
            }
            
            // Display "From:" can use the configured MAIL_FROM_EMAIL if it's a
            // valid address; otherwise it falls back to the authenticated mailbox
            // used as the envelope sender above.
            $displayFrom = filter_var($this->fromEmail, FILTER_VALIDATE_EMAIL) ? $this->fromEmail : $envelopeFrom;
            
            $safeFromName = addcslashes(str_replace(["\r", "\n"], '', $this->fromName), '"\\');
            $safeToName = addcslashes(str_replace(["\r", "\n"], '', $toName), '"\\');
            $safeSubject = str_replace(["\r", "\n"], ' ', $subject);
            $headers = [
                "From: \"{$safeFromName}\" <{$displayFrom}>",
                "Reply-To: {$displayFrom}",
                "To: \"{$safeToName}\" <{$toEmail}>",
                "Subject: {$safeSubject}",
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8",
                "X-Mailer: RentaFlow/" . (getenv('APP_VERSION') ?: $_ENV['APP_VERSION'] ?? '1.0')
            ];
            
            $htmlBody = nl2br($body);
            // Dot-stuffing: a line starting with a lone '.' would otherwise be
            // read by the server as the end-of-DATA terminator.
            $htmlBody = preg_replace('/^\./m', '..', $htmlBody);
            $message = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.\r\n";
            fputs($socket, $message);
            
            $response = $this->readMultiLineResponse($socket);
            error_log("SMTP Message sent response: " . trim($response));
            
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            $socket = null;
            
            $success = substr($response, 0, 3) == '250';
            if ($success) {
                error_log("SMTP: Email sent successfully to {$toEmail}");
            } else {
                $this->lastError = 'Message not accepted by server: ' . trim($response);
                error_log("SMTP: {$this->lastError}");
            }
            
            return $success;
            
        } catch (\Throwable $e) {
            $this->lastError = 'SMTP exception: ' . $e->getMessage();
            error_log("SMTP EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        } finally {
            if (is_resource($socket)) {
                fclose($socket);
            }
        }
    }
    
    /**
     * Send email using PHP's mail() function as fallback
     */
    private function sendViaPHP(string $toEmail, string $toName, string $subject, string $body): bool
    {
        try {
            if (!function_exists('mail')) {
                error_log("PHP mail() function not available");
                return false;
            }
            
            $headers = [
                "From: {$this->fromName} <{$this->fromEmail}>",
                "Reply-To: {$this->fromEmail}",
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8",
                "X-Mailer: RentaFlow/" . (getenv('APP_VERSION') ?: $_ENV['APP_VERSION'] ?? '1.0')
            ];
            
            $headerString = implode("\r\n", $headers);
            $sent = mail($toEmail, $subject, nl2br($body), $headerString);
            
            if ($sent) {
                error_log("PHP mail(): Email sent successfully to {$toEmail}");
            } else {
                error_log("PHP mail(): Failed to send email to {$toEmail}");
            }
            
            return $sent;
        } catch (\Throwable $e) {
            error_log("PHP mail() EXCEPTION: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Read multi-line SMTP response (response code followed by lines starting with whitespace).
     * Every response read in sendViaSMTP() goes through this rather than a bare fgets(),
     * so a multi-line reply (common on greeting banners and policy-text rejections) is
     * never misread from just its first line.
     */
    private function readMultiLineResponse($socket): string
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }
        return $response;
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
        
        return $this->queueTemplate('Tenant Welcome', $ownerId, $tenant['email'], $tenant['name'], $variables, 10);
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
        
        // Send immediately for critical onboarding emails
        return $this->sendTemplate('Caretaker Welcome', $ownerId, $caretaker['email'], $caretaker['name'], $variables);
    }
    
    /**
     * Send payment confirmation email to tenant and next of kin - SENDS IMMEDIATELY
     */
    public function sendPaymentConfirmation(int $ownerId, array $tenant, array $payment): bool
    {
        $variables = [
            'tenant' => $tenant['name'],
            'amount' => number_format($payment['amount'], 2),
            'balance' => number_format($payment['balance'] ?? 0, 2),
            'month' => $payment['month'] ?? date('F Y'),
            'category' => $payment['category'] ?? 'Rent',
            'date' => date('Y-m-d', strtotime($payment['date'] ?? 'now')),
            'invoice_url' => $payment['invoice_url'] ?? ''
        ];
        
        // Get all recipients (tenant + next of kin)
        $recipientService = new NotificationRecipientService();
        $recipients = $recipientService->getRecipients((int)$tenant['id'], $ownerId);
        
        if (empty($recipients)) {
            error_log("No valid recipients for payment confirmation - Tenant ID: {$tenant['id']}");
            return false;
        }
        
        $allSent = true;
        
        foreach ($recipients as $recipient) {
            $isNextOfKin = ($recipient['type'] === 'next_of_kin');
            
            $recipientVariables = $variables;
            if ($isNextOfKin) {
                $recipientVariables['next_of_kin_intro'] = "Dear {$recipient['name']},\n\nThis email is to inform you that a payment has been successfully recorded for {$tenant['name']}'s accommodation account. As the registered Next of Kin, you are receiving this notification to keep you informed of important payment activities.\n\n";
                $recipientVariables['recipient_name'] = $recipient['name'];
            }
            
            // Use sendTemplate which has fallback if template not found
            $sent = $this->sendTemplate('Payment Confirmation', $ownerId, $recipient['email'], $recipient['name'], $recipientVariables);
            if (!$sent) {
                $allSent = false;
                error_log("FAILED to send payment confirmation to {$recipient['email']}");
            } else {
                error_log("SUCCESS: Payment confirmation sent to {$recipient['email']}");
            }
        }
        
        return $allSent;
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
     * Send rent reminder email - uses direct send for real-time delivery
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
        
        // Rent reminders are sent directly (not queued) for timely delivery
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
        
        // Send immediately for critical notifications
        return $this->sendTemplate('Tenant Vacate', $ownerId, $tenant['email'], $tenant['name'], $variables);
    }
}