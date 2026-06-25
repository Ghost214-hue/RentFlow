<?php
/**
 * Cron Job: Send Rent Reminders
 * 
 * This script should be run daily via cron/scheduled task
 * It sends rent reminders at 7 days, 3 days, 2 days, and 1 day before the 5th of each month
 * 
 * Setup (Linux):
 * crontab -e
 * 0 8 * * * /usr/bin/php /path/to/RentFlow/backend/cron/rent_reminders.php
 * 
 * Setup (Windows):
 * Use Task Scheduler to run daily at 8 AM
 */

require_once __DIR__ . '/../app/Core/Env.php';
use App\Core\Env;
use App\Core\Database;
use App\Services\EmailService;

Env::load();

$db = Database::getInstance();
$emailService = new EmailService();

// Get all owners
$owners = $db->fetchAll("SELECT id FROM users WHERE role = 'owner'");

$totalSent = 0;
$errors = [];

foreach ($owners as $owner) {
    $ownerId = $owner['id'];
    
    // Get all tenants for this owner
    $tenants = $db->fetchAll(
        "SELECT t.*, h.rent, h.unit, p.name as property_name 
         FROM tenants t 
         LEFT JOIN houses h ON t.house_id = h.id 
         LEFT JOIN properties p ON h.property_id = p.id 
         WHERE t.owner_id = ? AND t.email IS NOT NULL AND t.email != ''",
        [$ownerId]
    );
    
    $currentMonth = date('Y-m');
    $dueDate = date('Y-m-05'); // 5th of current month
    
    foreach ($tenants as $tenant) {
        // Check if tenant has a pending bill for current month
        $bill = $db->fetchOne(
            "SELECT * FROM bills WHERE house_id = ? AND month = ? AND status != 'paid'",
            [$tenant['house_id'], $currentMonth]
        );
        
        if (!$bill) {
            continue; // No pending bill, skip reminder
        }
        
        // Calculate days until due date
        $today = new DateTime();
        $due = new DateTime($dueDate);
        $daysUntilDue = (int) $today->diff($due)->format('%a');
        
        // Send reminder if due date is within 7 days or past due
        if ($daysUntilDue <= 7 && $daysUntilDue >= -7) {
            try {
                $amount = $bill['total'] - ($bill['paid_amount'] ?? 0);
                
                if ($amount > 0) {
                    $emailService->sendRentReminder(
                        $ownerId,
                        $tenant,
                        $currentMonth,
                        $amount,
                        $daysUntilDue
                    );
                    $totalSent++;
                    
                    // Log the reminder
                    $db->insert('rent_reminders', [
                        'owner_id' => $ownerId,
                        'tenant_id' => $tenant['id'],
                        'house_id' => $tenant['house_id'],
                        'month' => $currentMonth,
                        'amount' => $amount,
                        'days_before_due' => $daysUntilDue,
                        'sent_at' => date('Y-m-d H:i:s'),
                        'status' => 'sent'
                    ]);
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to send reminder to {$tenant['email']}: " . $e->getMessage();
                error_log("Rent reminder error: " . $e->getMessage());
            }
        }
    }
}

// Log summary
$logFile = __DIR__ . '/../logs/rent_reminders.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$timestamp = date('Y-m-d H:i:s');
$logMessage = "[$timestamp] Rent reminders sent: $totalSent";
if (!empty($errors)) {
    $logMessage .= "\nErrors:\n" . implode("\n", $errors);
}
$logMessage .= "\n---\n";

file_put_contents($logFile, $logMessage, FILE_APPEND);

echo "Rent reminder cron completed.\n";
echo "Total reminders sent: $totalSent\n";
if (!empty($errors)) {
    echo "Errors encountered:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

exit(0);