<?php


// Autoload
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

require_once __DIR__ . '/../app/Core/Env.php';
use App\Core\Env;
use App\Core\Database;
use App\Services\EmailService;

Env::load(__DIR__ . '/../../.env');

$db = Database::getInstance();
$emailService = new EmailService();

// Get all owners
try {
    $owners = $db->fetchAll("SELECT id FROM owners WHERE role = 'owner'");
} catch (\Exception $e) {
    echo "ERROR: Database setup incomplete. Run migrations first.\n";
    exit(1);
}

$totalSent = 0;
$errors = [];

$currentMonth = date('Y-m');
$dueDateStr = date('Y-m-05'); // 5th of current month

// Calculate days until due
$today = new DateTime();
$due = new DateTime($dueDateStr);
$daysUntilDue = (int) $today->diff($due)->format('%a');

// Determine if today's date is significant for reminders
$dayOfMonth = (int) date('j');
$isReminderDay = in_array($dayOfMonth, [28, 29, 2, 3, 4, 5, 6, 7]) || $daysUntilDue <= 7;

echo "Date: " . date('Y-m-d') . "\n";
echo "Due date: $dueDateStr\n";
echo "$daysUntilDue days before due\n\n";

if (!$isReminderDay && $daysUntilDue > 7 && $daysUntilDue < 365) {
    echo "Not a reminder day. Skipping.\n";
    echo "Reminders are sent on the 28th-29th (7d), 2nd (3d), 3rd (2d), 4th (1d), 5th-7th (due/past due).\n";
    exit(0);
}

foreach ($owners as $owner) {
    $ownerId = $owner['id'];
    
    // Get tenants with pending bills for this month
    $tenants = $db->fetchAll(
        "SELECT t.*, h.rent, h.unit, p.name as property_name,
                b.total as bill_total, b.paid_amount as bill_paid
         FROM tenants t 
         LEFT JOIN houses h ON t.house_id = h.id 
         LEFT JOIN properties p ON h.property_id = p.id 
         JOIN bills b ON b.house_id = t.house_id AND b.month = ? AND b.status != 'paid'
         WHERE t.owner_id = ? AND t.email IS NOT NULL AND t.email != ''",
        [$currentMonth, $ownerId]
    );
    
    foreach ($tenants as $tenant) {
        $amount = $tenant['bill_total'] - ($tenant['bill_paid'] ?? 0);
        if ($amount <= 0) continue;
        
        // Determine template and message based on days until due
        $templateName = 'Rent Reminder';
        $reminderType = '';
        
        if ($daysUntilDue <= 1) {
            // 0-1 days: final reminder (due today or tomorrow)
            $templateName = 'Rent Reminder Final';
            $reminderType = 'FINAL';
        } elseif ($daysUntilDue == 2) {
            $reminderType = '2 days before';
        } elseif ($daysUntilDue == 3) {
            $reminderType = '3 days before';
        } elseif ($daysUntilDue == 7) {
            $reminderType = '7 days (first reminder)';
        } else {
            $reminderType = $daysUntilDue . ' days before';
        }
        
        try {
            $sent = $emailService->sendRentReminder(
                $ownerId,
                $tenant,
                $currentMonth,
                $amount,
                $daysUntilDue
            );
            
            if ($sent) {
                $totalSent++;
                echo "  ✓ {$tenant['name']} ({$tenant['email']}) - {$reminderType}\n";
                
                // Log the reminder
                try {
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
                } catch (\Exception $logException) {
                    error_log("Rent reminder log error: " . $logException->getMessage());
                }
            } else {
                echo "  ✗ {$tenant['name']} ({$tenant['email']}) - SEND FAILED\n";
                $errors[] = "Failed to send to {$tenant['email']}";
            }
        } catch (\Exception $e) {
            echo "  ! {$tenant['name']} ({$tenant['email']}) - ERROR: " . $e->getMessage() . "\n";
            $errors[] = "Error for {$tenant['email']}: " . $e->getMessage();
            error_log("Rent reminder error for {$tenant['email']}: " . $e->getMessage());
        }
    }
}

// Log summary to file
$logFile = __DIR__ . '/../logs/rent_reminders.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

$logMessage = "[" . date('Y-m-d H:i:s') . "] ";
$logMessage .= "Reminders sent: $totalSent";
if (!empty($errors)) $logMessage .= " | Errors: " . count($errors);
file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);

echo "\n=== Summary ===\n";
echo "Total reminders sent: $totalSent\n";
if (!empty($errors)) {
    echo "Errors encountered: " . count($errors) . "\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

exit($errors ? 1 : 0);