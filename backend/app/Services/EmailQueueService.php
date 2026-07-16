<?php
/**
 * Email Queue Service
 * Handles asynchronous email sending via database queue
 */
namespace App\Services;

use App\Core\Database;

class EmailQueueService
{
    private Database $db;
    private bool $enabled;

    public function __construct()
    {
        $this->db = Database::getInstance();
        try {
            $this->enabled = $this->getSetting('enabled', '1') === '1';
        } catch (\Throwable $e) {
            $this->enabled = false;
            error_log('EmailQueueService init error: ' . $e->getMessage());
        }
    }

    /**
     * Queue an email for later sending
     */
    public function queue(
        int $ownerId,
        string $templateName,
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        string $body,
        int $priority = 0,
        ?\DateTime $scheduledAt = null
    ): bool {
        if (!$this->enabled) {
            return false;
        }

        try {
            $this->db->insert('email_queue', [
                'owner_id' => $ownerId,
                'template_name' => $templateName,
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName,
                'subject' => $subject,
                'body' => $body,
                'priority' => $priority,
                'status' => 'pending',
                'scheduled_at' => $scheduledAt ? $scheduledAt->format('Y-m-d H:i:s') : null,
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to queue email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process pending emails from the queue
     */
    public function process(int $batchSize = 10): array
    {
        if (!$this->enabled) {
            return ['processed' => 0, 'failed' => 0, 'message' => 'Queue is disabled'];
        }

        $batchSize = max(1, min($batchSize, (int) $this->getSetting('batch_size', '10')));

        // Get pending emails ordered by priority and creation time
        $emails = $this->db->fetchAll(
            "SELECT * FROM email_queue 
             WHERE status = 'pending' 
             AND (scheduled_at IS NULL OR scheduled_at <= NOW())
             AND attempts < max_attempts
             ORDER BY priority DESC, created_at ASC
             LIMIT ?",
            [$batchSize]
        );

        $processed = 0;
        $failed = 0;

        $emailService = new EmailService();

        foreach ($emails as $email) {
            // Mark as processing
            $this->db->update('email_queue', [
                'status' => 'processing',
                'attempts' => $email['attempts'] + 1
            ], 'id = ?', [$email['id']]);

            // Send the email
            $sent = $emailService->send(
                $email['recipient_email'],
                $email['recipient_name'] ?? '',
                $email['subject'],
                $email['body']
            );

            if ($sent) {
                // Mark as sent
                $this->db->update('email_queue', [
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$email['id']]);
                $processed++;
            } else {
                // Mark as failed or pending for retry
                $newStatus = ($email['attempts'] + 1) >= $email['max_attempts'] ? 'failed' : 'pending';
                $this->db->update('email_queue', [
                    'status' => $newStatus,
                    'error_message' => 'SMTP delivery failed'
                ], 'id = ?', [$email['id']]);
                $failed++;
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'total' => count($emails)
        ];
    }

    /**
     * Retry failed emails
     */
    public function retryFailed(int $batchSize = 10): array
    {
        if (!$this->enabled) {
            return ['retried' => 0, 'failed' => 0];
        }

        // Reset failed emails back to pending for retry
        $stmt = $this->db->query(
            "UPDATE email_queue 
             SET status = 'pending', attempts = 0, error_message = NULL 
             WHERE status = 'failed' 
             AND attempts < max_attempts
             AND (scheduled_at IS NULL OR scheduled_at <= NOW())
             LIMIT ?",
            [$batchSize]
        );

        $retried = $stmt ? $stmt->affected_rows : 0;

        $result = $this->process($batchSize);

        return [
            'retried' => $retried,
            'failed' => $result['failed'] ?? 0,
            'processed' => $result['processed'] ?? 0,
            'total' => $result['total'] ?? 0,
        ];
    }

    /**
     * Get queue statistics
     */
    public function getStats(): array
    {
        $stats = $this->db->fetchOne(
            "SELECT 
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing,
                COUNT(CASE WHEN status = 'sent' THEN 1 END) as sent,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                COUNT(*) as total
             FROM email_queue
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );

        return $stats ?: [
            'pending' => 0,
            'processing' => 0,
            'sent' => 0,
            'failed' => 0,
            'total' => 0
        ];
    }

    /**
     * Clean up old sent emails (older than specified days)
     */
    public function cleanup(int $daysOld = 30): int
    {
        $result = $this->db->query(
            "DELETE FROM email_queue 
             WHERE status = 'sent' 
             AND sent_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$daysOld]
        );

        return $result ?: 0;
    }

    /**
     * Get a setting value from the database
     */
    public function getSetting(string $key, string $default = ''): string
    {
        try {
            $setting = $this->db->fetchOne(
                "SELECT setting_value FROM email_queue_settings WHERE setting_key = ?",
                [$key]
            );
            return $setting ? (string) $setting['setting_value'] : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Update a setting value
     */
    public function updateSetting(string $key, string $value): bool
    {
        try {
            $this->db->query(
                "INSERT INTO email_queue_settings (setting_key, setting_value) 
                 VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
                [$key, $value, $value]
            );
            return true;
        } catch (\Exception $e) {
            error_log("Failed to update email queue setting: " . $e->getMessage());
            return false;
        }
    }
}