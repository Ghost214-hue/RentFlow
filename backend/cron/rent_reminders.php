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
use App\Services\NotificationRecipientService;

Env::load();

$db = Database::getInstance();
$emailService = new EmailService();
$recipientService = new NotificationRecipientService();

// Check database setup
try {
    $db->fetchAll("SELECT id FROM owners LIMIT 1");
} catch (\Exception $e) {
    echo "ERROR: Database setup incomplete. Run migrations first.\n";
    exit(1);
}

$totalSent = 0;
$totalOverdue = 0;
$totalAdvance7 = 0;
$totalAdvance5 = 0;
$totalAdvance3 = 0;
$totalAdvance2 = 0;
$totalAdvance1 = 0;
$errors = [];

echo "=== RentaFlow Rent Reminders ===\n";
echo "Date: " . date('Y-m-d') . "\n\n";

// ============================================
// HELPER FUNCTION: Send reminders for a set of bills
// ============================================
function sendReminders($db, $emailService, $recipientService, $bills, $reminderType, &$totalSent, &$totalCount, &$errors) {
    if (empty($bills)) {
        echo "    No bills found for this reminder type.\n";
        return;
    }
    
    echo "    Found " . count($bills) . " bill(s)\n";
    
    foreach ($bills as $row) {
        $ownerId = (int) $row['owner_id'];
        $templateRow = $emailService->getTemplate('Rent Reminder', $ownerId);
        if (!$templateRow) {
            echo "    ERROR: Template 'Rent Reminder' not found for owner {$ownerId}.\n";
            $errors[] = "Template missing for owner {$ownerId}";
            continue;
        }
        $templateBody = (string)($templateRow['body'] ?? '');
        $tenantName = $row['tenant_name'];
        $tenantEmail = $row['tenant_email'];
        $amount = number_format((float)$row['bill_amount'], 2);
        $month = $row['month'] ?: date('Y-m', strtotime($row['due_date']));
        $dueDate = date('F d, Y', strtotime($row['due_date']));

        // Build payment instructions
        $instructions = '';
        switch ($row['payment_method_type']) {
            case 'paybill':
                if (!empty($row['paybill_number'])) {
                    $instructions .= "Paybill Number: " . $row['paybill_number'] . "\n";
                }
                if (!empty($row['paybill_account'])) {
                    $instructions .= "Account Number: " . $row['paybill_account'] . "\n";
                }
                break;
            case 'till':
                if (!empty($row['till_number'])) {
                    $instructions .= "Till Number: " . $row['till_number'] . "\n";
                }
                break;
            case 'bank':
                if (!empty($row['bank_name']) || !empty($row['bank_account']) || !empty($row['bank_branch'])) {
                    $instructions .= "Bank: " . trim(($row['bank_name'] ?? '') . ' ' . ($row['bank_branch'] ?? '')) . "\n";
                    if (!empty($row['bank_account'])) {
                        $instructions .= "Account Number: " . $row['bank_account'] . "\n";
                    }
                }
                break;
            case 'mobile_money':
                if (!empty($row['mobile_money_number'])) {
                    $instructions .= "M-Pesa Number: " . $row['mobile_money_number'] . "\n";
                }
                break;
        }
        $instructions = trim($instructions) ?: 'Contact property management for payment options.';

        $body = str_replace(
            ['{{tenant}}', '{{month}}', '{{amount}}', '{{balance}}', '{{payment_instructions}}'],
            [$tenantName, $month, $amount, $amount, $instructions],
            $templateBody
        );

        // Customize message based on reminder type
        $subjectPrefix = '';
        $introMessage = '';
        
        switch ($reminderType) {
            case 'overdue':
                $subjectPrefix = 'OVERDUE - ';
                $daysOverdue = (new DateTime())->diff(new DateTime($row['due_date']))->days;
                $introMessage = "Dear {$tenantName},\n\nThis is a reminder that your rent payment of KES {$amount} for {$month} was due on {$dueDate} ({$daysOverdue} days overdue). Please make your payment immediately to avoid further penalties.\n\n";
                $daysBeforeDue = -$daysOverdue;
                break;
                
            case '7days':
                $subjectPrefix = 'Due in 7 Days - ';
                $introMessage = "Dear {$tenantName},\n\nThis is a friendly reminder that your rent payment of KES {$amount} for {$month} is due on {$dueDate} (in 7 days). Please plan your payment accordingly.\n\n";
                $daysBeforeDue = 7;
                break;
                
            case '3days':
                $subjectPrefix = 'Due in 3 Days - ';
                $introMessage = "Dear {$tenantName},\n\nThis is a reminder that your rent payment of KES {$amount} for {$month} is due on {$dueDate} (in 3 days). Please ensure your payment is made before the due date.\n\n";
                $daysBeforeDue = 3;
                break;

            case '5days':
                $subjectPrefix = 'Due in 5 Days - ';
                $introMessage = "Dear {$tenantName},\n\nThis is a friendly reminder that your rent payment of KES {$amount} for {$month} is due on {$dueDate} (in 5 days). Please plan your payment accordingly.\n\n";
                $daysBeforeDue = 5;
                break;
                
            case '2days':
                $subjectPrefix = 'Due in 2 Days - ';
                $introMessage = "Dear {$tenantName},\n\nURGENT: Your rent payment of KES {$amount} for {$month} is due on {$dueDate} (in 2 days). Please make your payment as soon as possible.\n\n";
                $daysBeforeDue = 2;
                break;
                
            case '1day':
                $subjectPrefix = 'Due Tomorrow - ';
                $introMessage = "Dear {$tenantName},\n\nFINAL REMINDER: Your rent payment of KES {$amount} for {$month} is due TOMORROW, {$dueDate}. Please make your payment today to avoid late fees.\n\n";
                $daysBeforeDue = 1;
                break;
        }
        
        $body = $introMessage . $body;

        // Get all recipients (tenant + next of kin)
        $recipients = $recipientService->getRecipients((int)$row['tenant_id'], $ownerId);
        
        if (empty($recipients)) {
            echo "    ! {$tenantName} - No valid recipients found\n";
            continue;
        }
        
        // Send to each recipient
        $billSent = false;
        foreach ($recipients as $recipient) {
            $isNextOfKin = ($recipient['type'] === 'next_of_kin');
            
            // For next of kin, add introduction to the body
            $recipientBody = $body;
            if ($isNextOfKin) {
                $kinIntro = "Dear {$recipient['name']},\n\nThis is a friendly reminder that a payment of KES {$amount} is due for {$tenantName}'s accommodation for the month of {$month}. The due date is {$dueDate}.\n\nAs the registered Next of Kin, you are receiving this notification to help ensure timely payment.\n\n";
                $recipientBody = $kinIntro . $body;
            }
            
            try {
                $sent = $emailService->send(
                    $recipient['email'],
                    $recipient['name'],
                    'Rent Reminder - ' . $subjectPrefix . $month,
                    $recipientBody
                );
                
                // Log the notification
                $recipientService->logNotification(
                    (int)$row['tenant_id'],
                    $tenantName,
                    $recipient['type'],
                    $recipient['name'],
                    $recipient['email'],
                    'Payment Reminder - ' . ucfirst(str_replace('days', ' Days', $reminderType)),
                    $sent
                );

                if ($sent) {
                    if (!$billSent) {
                        $totalSent++;
                        $totalCount++;
                        $billSent = true;
                    }
                    echo "    ✓ Sent to {$recipient['name']} ({$recipient['email']}) - {$month} - KES {$amount}\n";
                } else {
                    echo "    ✗ {$recipient['name']} ({$recipient['email']}) - SEND FAILED\n";
                    $errors[] = "Failed to send to {$recipient['email']}";
                }
            } catch (\Exception $e) {
                echo "    ! {$recipient['name']} ({$recipient['email']}) - ERROR: " . $e->getMessage() . "\n";
                $errors[] = "Error for {$recipient['email']}: " . $e->getMessage();
                error_log("Rent reminder error for {$recipient['email']}: " . $e->getMessage());
            }
        }
        
        // Log reminder record if at least one email was sent
        if ($billSent) {
            try {
                $db->insert('rent_reminders', [
                    'owner_id' => $ownerId,
                    'tenant_id' => $row['tenant_id'],
                    'house_id' => $row['house_id'],
                    'bill_id' => $row['bill_id'],
                    'month' => $month,
                    'amount' => $row['bill_amount'],
                    'days_before_due' => $daysBeforeDue,
                    'sent_at' => date('Y-m-d H:i:s'),
                    'status' => 'sent'
                ]);
            } catch (\Exception $logException) {
                error_log("Rent reminder log error: " . $logException->getMessage());
            }
        }
    }
}

// ============================================
// PART 1: OVERDUE REMINDERS (Past Due Date)
// ============================================
echo "[1] Checking for Overdue Bills...\n";

$overdueBills = $db->fetchAll("
    SELECT b.id AS bill_id, b.owner_id, b.house_id, b.total AS bill_amount, b.due_date, b.month,
           h.unit AS house_number, h.rent, h.property_id,
           t.id AS tenant_id, t.name AS tenant_name, t.email AS tenant_email,
           p.payment_method_type, p.paybill_number, p.paybill_account,
           p.till_number, p.bank_name, p.bank_account, p.bank_branch, p.mobile_money_number
    FROM bills b
    JOIN houses h ON h.id = b.house_id
    JOIN tenants t ON t.id = COALESCE(b.tenant_id, h.tenant_id)
    JOIN properties p ON p.id = h.property_id
    WHERE b.status != 'paid'
      AND b.due_date < CURDATE()
      AND h.status = 'occupied'
      AND t.email IS NOT NULL
      AND TRIM(t.email) != ''
    ORDER BY t.email, b.due_date ASC
");

sendReminders($db, $emailService, $recipientService, $overdueBills, 'overdue', $totalSent, $totalOverdue, $errors);

// ============================================
// PART 2: 7 DAYS BEFORE DUE DATE
// ============================================
echo "\n[2] Checking for Bills Due in 7 Days...\n";

$advance7Bills = $db->fetchAll("
    SELECT b.id AS bill_id, b.owner_id, b.house_id, b.total AS bill_amount, b.due_date, b.month,
           h.unit AS house_number, h.rent, h.property_id,
           t.id AS tenant_id, t.name AS tenant_name, t.email AS tenant_email,
           p.payment_method_type, p.paybill_number, p.paybill_account,
           p.till_number, p.bank_name, p.bank_account, p.bank_branch, p.mobile_money_number
    FROM bills b
    JOIN houses h ON h.id = b.house_id
    JOIN tenants t ON t.id = COALESCE(b.tenant_id, h.tenant_id)
    JOIN properties p ON p.id = h.property_id
    WHERE b.status != 'paid'
      AND b.due_date = DATE_ADD(CURDATE(), INTERVAL 7 DAY)
      AND h.status = 'occupied'
      AND t.email IS NOT NULL
      AND TRIM(t.email) != ''
      AND NOT EXISTS (
          SELECT 1 FROM rent_reminders rr
          WHERE rr.bill_id = b.id AND rr.days_before_due = 7 AND DATE(rr.sent_at) = CURDATE()
      )
    ORDER BY t.email, b.due_date ASC
");

sendReminders($db, $emailService, $recipientService, $advance7Bills, '7days', $totalSent, $totalAdvance7, $errors);

// ============================================
// PART 3: 5 DAYS BEFORE DUE DATE
// ============================================
echo "\n[3] Checking for Bills Due in 5 Days...\n";

$advance5Bills = $db->fetchAll("
    SELECT b.id AS bill_id, b.owner_id, b.house_id, b.total AS bill_amount, b.due_date, b.month,
           h.unit AS house_number, h.rent, h.property_id,
           t.id AS tenant_id, t.name AS tenant_name, t.email AS tenant_email,
           p.payment_method_type, p.paybill_number, p.paybill_account,
           p.till_number, p.bank_name, p.bank_account, p.bank_branch, p.mobile_money_number
    FROM bills b
    JOIN houses h ON h.id = b.house_id
    JOIN tenants t ON t.id = COALESCE(b.tenant_id, h.tenant_id)
    JOIN properties p ON p.id = h.property_id
    WHERE b.status != 'paid'
      AND b.due_date = DATE_ADD(CURDATE(), INTERVAL 5 DAY)
      AND h.status = 'occupied'
      AND t.email IS NOT NULL
      AND TRIM(t.email) != ''
      AND NOT EXISTS (
          SELECT 1 FROM rent_reminders rr
          WHERE rr.bill_id = b.id AND rr.days_before_due = 5 AND DATE(rr.sent_at) = CURDATE()
      )
    ORDER BY t.email, b.due_date ASC
");

sendReminders($db, $emailService, $recipientService, $advance5Bills, '5days', $totalSent, $totalAdvance5, $errors);

// ============================================
// PART 4: 3 DAYS BEFORE DUE DATE
// ============================================
echo "\n[4] Checking for Bills Due in 3 Days...\n";

$advance3Bills = $db->fetchAll("
    SELECT b.id AS bill_id, b.owner_id, b.house_id, b.total AS bill_amount, b.due_date, b.month,
           h.unit AS house_number, h.rent, h.property_id,
           t.id AS tenant_id, t.name AS tenant_name, t.email AS tenant_email,
           p.payment_method_type, p.paybill_number, p.paybill_account,
           p.till_number, p.bank_name, p.bank_account, p.bank_branch, p.mobile_money_number
    FROM bills b
    JOIN houses h ON h.id = b.house_id
    JOIN tenants t ON t.id = COALESCE(b.tenant_id, h.tenant_id)
    JOIN properties p ON p.id = h.property_id
    WHERE b.status != 'paid'
      AND b.due_date = DATE_ADD(CURDATE(), INTERVAL 3 DAY)
      AND h.status = 'occupied'
      AND t.email IS NOT NULL
      AND TRIM(t.email) != ''
      AND NOT EXISTS (
          SELECT 1 FROM rent_reminders rr
          WHERE rr.bill_id = b.id AND rr.days_before_due = 3 AND DATE(rr.sent_at) = CURDATE()
      )
    ORDER BY t.email, b.due_date ASC
");

sendReminders($db, $emailService, $recipientService, $advance3Bills, '3days', $totalSent, $totalAdvance3, $errors);

// ============================================
// PART 5: 2 DAYS BEFORE DUE DATE
// ============================================
echo "\n[5] Checking for Bills Due in 2 Days...\n";

$advance2Bills = $db->fetchAll("
    SELECT b.id AS bill_id, b.owner_id, b.house_id, b.total AS bill_amount, b.due_date, b.month,
           h.unit AS house_number, h.rent, h.property_id,
           t.id AS tenant_id, t.name AS tenant_name, t.email AS tenant_email,
           p.payment_method_type, p.paybill_number, p.paybill_account,
           p.till_number, p.bank_name, p.bank_account, p.bank_branch, p.mobile_money_number
    FROM bills b
    JOIN houses h ON h.id = b.house_id
    JOIN tenants t ON t.id = COALESCE(b.tenant_id, h.tenant_id)
    JOIN properties p ON p.id = h.property_id
    WHERE b.status != 'paid'
      AND b.due_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY)
      AND h.status = 'occupied'
      AND t.email IS NOT NULL
      AND TRIM(t.email) != ''
      AND NOT EXISTS (
          SELECT 1 FROM rent_reminders rr
          WHERE rr.bill_id = b.id AND rr.days_before_due = 2 AND DATE(rr.sent_at) = CURDATE()
      )
    ORDER BY t.email, b.due_date ASC
");

sendReminders($db, $emailService, $recipientService, $advance2Bills, '2days', $totalSent, $totalAdvance2, $errors);

// ============================================
// PART 6: 1 DAY BEFORE DUE DATE
// ============================================
echo "\n[6] Checking for Bills Due Tomorrow...\n";

$advance1Bills = $db->fetchAll("
    SELECT b.id AS bill_id, b.owner_id, b.house_id, b.total AS bill_amount, b.due_date, b.month,
           h.unit AS house_number, h.rent, h.property_id,
           t.id AS tenant_id, t.name AS tenant_name, t.email AS tenant_email,
           p.payment_method_type, p.paybill_number, p.paybill_account,
           p.till_number, p.bank_name, p.bank_account, p.bank_branch, p.mobile_money_number
    FROM bills b
    JOIN houses h ON h.id = b.house_id
    JOIN tenants t ON t.id = COALESCE(b.tenant_id, h.tenant_id)
    JOIN properties p ON p.id = h.property_id
    WHERE b.status != 'paid'
      AND b.due_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
      AND h.status = 'occupied'
      AND t.email IS NOT NULL
      AND TRIM(t.email) != ''
      AND NOT EXISTS (
          SELECT 1 FROM rent_reminders rr
          WHERE rr.bill_id = b.id AND rr.days_before_due = 1 AND DATE(rr.sent_at) = CURDATE()
      )
    ORDER BY t.email, b.due_date ASC
");

sendReminders($db, $emailService, $recipientService, $advance1Bills, '1day', $totalSent, $totalAdvance1, $errors);

// ============================================
// SUMMARY
// ============================================

// Log summary
$logFile = __DIR__ . '/../logs/rent_reminders.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

$logMessage = "[" . date('Y-m-d H:i:s') . "] ";
$logMessage .= "Reminders sent: {$totalSent} (Overdue: {$totalOverdue}, 7d: {$totalAdvance7}, 5d: {$totalAdvance5}, 3d: {$totalAdvance3}, 2d: {$totalAdvance2}, 1d: {$totalAdvance1})";
if (!empty($errors)) $logMessage .= " | Errors: " . count($errors);
file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);

echo "\n=== Summary ===\n";
echo "Total reminders sent: {$totalSent}\n";
echo "  - Overdue reminders: {$totalOverdue}\n";
echo "  - Advance reminders (7 days): {$totalAdvance7}\n";
echo "  - Advance reminders (5 days): {$totalAdvance5}\n";
echo "  - Advance reminders (3 days): {$totalAdvance3}\n";
echo "  - Advance reminders (2 days): {$totalAdvance2}\n";
echo "  - Advance reminders (1 day): {$totalAdvance1}\n";
if (!empty($errors)) {
    echo "Errors encountered: " . count($errors) . "\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

echo "\n=== Next Steps ===\n";
echo "1. Check tenant email inboxes for reminders\n";
echo "2. View log: tail -f backend/logs/rent_reminders.log\n";
echo "3. Check database: SELECT * FROM rent_reminders ORDER BY sent_at DESC LIMIT 10;\n";
echo "4. Automate: Add to crontab: 0 8 * * * cd " . dirname(__DIR__) . " && php backend/cron/rent_reminders.php\n";

exit($errors ? 1 : 0);
