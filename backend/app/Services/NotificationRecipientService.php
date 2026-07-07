<?php
/**
 * Notification Recipient Service
 * Centralizes recipient management for all payment-related notifications
 * 
 * Supports multiple recipient types:
 * - tenant
 * - next_of_kin
 * - Future: parent, guardian, sponsor, emergency_contact, property_manager
 */
namespace App\Services;

use App\Core\Database;

class NotificationRecipientService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all notification recipients for a tenant
     * 
     * @param int $tenantId
     * @param string $ownerId
     * @return array Array of recipients with type, name, and email
     */
    public function getRecipients(int $tenantId, int $ownerId): array
    {
        $recipients = [];
        $addedEmails = [];

        // Get tenant information
        $tenant = $this->db->fetchOne(
            "SELECT id, name, email FROM tenants WHERE id = ? AND owner_id = ?",
            [$tenantId, $ownerId]
        );

        if (!$tenant) {
            return $recipients;
        }

        // Add tenant as recipient if they have a valid email
        if (!empty($tenant['email']) && filter_var($tenant['email'], FILTER_VALIDATE_EMAIL)) {
            $email = strtolower(trim($tenant['email']));
            if (!in_array($email, $addedEmails)) {
                $recipients[] = [
                    'type' => 'tenant',
                    'name' => $tenant['name'],
                    'email' => $email,
                    'tenant_id' => $tenantId
                ];
                $addedEmails[] = $email;
            }
        }

        // Get Next of Kin information
        $nextOfKin = $this->db->fetchOne(
            "SELECT next_of_kin_name, next_of_kin_phone, next_of_kin_email 
             FROM tenants 
             WHERE id = ? AND owner_id = ? 
             AND next_of_kin_email IS NOT NULL 
             AND TRIM(next_of_kin_email) != ''",
            [$tenantId, $ownerId]
        );

        if ($nextOfKin && filter_var($nextOfKin['next_of_kin_email'], FILTER_VALIDATE_EMAIL)) {
            $email = strtolower(trim($nextOfKin['next_of_kin_email']));
            
            // Only add if not already added (avoid duplicates)
            if (!in_array($email, $addedEmails)) {
                $recipients[] = [
                    'type' => 'next_of_kin',
                    'name' => $nextOfKin['next_of_kin_name'] ?: 'Next of Kin',
                    'email' => $email,
                    'phone' => $nextOfKin['next_of_kin_phone'] ?? null,
                    'tenant_id' => $tenantId
                ];
                $addedEmails[] = $email;
            }
        }

        // Future extension point: Add more recipient types here
        // Example:
        // $recipients = array_merge($recipients, $this->getParentRecipients($tenantId, $ownerId, $addedEmails));
        // $recipients = array_merge($recipients, $this->getGuardianRecipients($tenantId, $ownerId, $addedEmails));

        return $recipients;
    }

    /**
     * Get recipients for a tenant with their details
     * Alias for getRecipients for better readability
     * 
     * @param int $tenantId
     * @param int $ownerId
     * @return array
     */
    public function getNotificationRecipients(int $tenantId, int $ownerId): array
    {
        return $this->getRecipients($tenantId, $ownerId);
    }

    /**
     * Validate a list of recipients
     * Filters out invalid emails and duplicates
     * 
     * @param array $recipients
     * @return array Validated recipients
     */
    public function validateRecipients(array $recipients): array
    {
        $valid = [];
        $addedEmails = [];

        foreach ($recipients as $recipient) {
            // Skip if email is missing
            if (empty($recipient['email'])) {
                continue;
            }

            $email = strtolower(trim($recipient['email']));

            // Skip if invalid email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Skip duplicates
            if (in_array($email, $addedEmails)) {
                continue;
            }

            $valid[] = [
                'type' => $recipient['type'] ?? 'unknown',
                'name' => $recipient['name'] ?? 'Unknown',
                'email' => $email,
                'tenant_id' => $recipient['tenant_id'] ?? null
            ];
            $addedEmails[] = $email;
        }

        return $valid;
    }

    /**
     * Get tenant information by ID
     * 
     * @param int $tenantId
     * @param int $ownerId
     * @return array|null
     */
    public function getTenant(int $tenantId, int $ownerId): ?array
    {
        $tenant = $this->db->fetchOne(
            "SELECT * FROM tenants WHERE id = ? AND owner_id = ?",
            [$tenantId, $ownerId]
        );

        return $tenant ?: null;
    }

    /**
     * Get Next of Kin information for a tenant
     * 
     * @param int $tenantId
     * @param int $ownerId
     * @return array|null
     */
    public function getNextOfKin(int $tenantId, int $ownerId): ?array
    {
        $nextOfKin = $this->db->fetchOne(
            "SELECT next_of_kin_name, next_of_kin_phone, next_of_kin_email 
             FROM tenants 
             WHERE id = ? AND owner_id = ? 
             AND next_of_kin_email IS NOT NULL 
             AND TRIM(next_of_kin_email) != ''",
            [$tenantId, $ownerId]
        );

        if ($nextOfKin) {
            return [
                'name' => $nextOfKin['next_of_kin_name'],
                'phone' => $nextOfKin['next_of_kin_phone'],
                'email' => $nextOfKin['next_of_kin_email']
            ];
        }

        return null;
    }

    /**
     * Log notification to database
     * 
     * @param int $tenantId
     * @param string $tenantName
     * @param string $recipientType
     * @param string $recipientName
     * @param string $recipientEmail
     * @param string $notificationType
     * @param bool $success
     * @param string|null $failureReason
     * @return void
     */
    public function logNotification(
        int $tenantId,
        string $tenantName,
        string $recipientType,
        string $recipientName,
        string $recipientEmail,
        string $notificationType,
        bool $success,
        ?string $failureReason = null
    ): void {
        try {
            $this->db->insert('email_logs', [
                'owner_id' => 0, // System notification
                'to_email' => $recipientEmail,
                'to_name' => $recipientName,
                'subject' => "[$notificationType] Notification for tenant #$tenantId",
                'body' => json_encode([
                    'tenant_id' => $tenantId,
                    'tenant_name' => $tenantName,
                    'recipient_type' => $recipientType,
                    'recipient_name' => $recipientName,
                    'notification_type' => $notificationType,
                    'status' => $success ? 'sent' : 'failed',
                    'failure_reason' => $failureReason
                ]),
                'status' => $success ? 'sent' : 'failed',
                'error' => $failureReason,
                'sent_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // If email_logs table doesn't exist, just log to error log
            error_log("Notification log error: " . $e->getMessage());
        }
    }
}