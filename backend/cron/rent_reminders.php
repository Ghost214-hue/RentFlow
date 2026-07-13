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

Env::load(__DIR__ . '/../../.env');

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
$errors = [];

echo "Date: " . date('Y-m-d') . "\n\n";

// Fetch unpaid overdue rent bills with occupied tenant house and property payment details
$overdueBills = $db->fetchAll("
    SELECT b.id AS bill_id, b.house_id, b.total AS bill_amount, b.due_date, b.month,
           h.unit AS house_number, h.rent, h.property_id,
           t.id AS tenant_id, t.name AS tenant_name, t.email AS tenant_email,
           p.payment_method_type, p.paybill_number, p.paybill_account,
           p.till_number, p.bank_name, p.bank_account, p.bank_branch, p.mobile_money_number
    FROM bills b
    JOIN houses h ON h.id = b.house_id
    JOIN tenants t ON t.id = h.tenant_id
    JOIN properties p ON p.id = h.property_id
    WHERE b.status != 'paid'
      AND b.due_date <= CURDATE()
      AND h.status = 'occupied'
      AND t.email IS NOT NULL
      AND TRIM(t.email) != ''
    ORDER BY t.email, b.due_date ASC
");

if (empty($overdueBills)) {
    echo "No overdue payments found.\n";
    exit(0);
}

// Ensure template exists
$templateRow = $emailService->getTemplate('Rent Reminder', 1);
if (!$templateRow) {
    echo "ERROR: Template 'Rent Reminder' not found.\n";
    exit(1);
}
$templateBody = (string)($templateRow['body'] ?? '');

// Send one reminder per bill (one email per outstanding bill per tenant)
foreach ($overdueBills as $row) {
    $tenantName = $row['tenant_name'];
    $tenantEmail = $row['tenant_email'];
    $amount = number_format((float)$row['bill_amount'], 2);
    $month = $row['month'] ?: date('Y-m', strtotime($row['due_date']));

    // Build payment instructions for the property
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

    // Get all recipients (tenant + next of kin)
    $recipients = $recipientService->getRecipients((int)$row['tenant_id'], 1);
    
    if (empty($recipients)) {
        echo "  ! {$tenantName} - No valid recipients found\n";
        continue;
    }
    
    // Send to each recipient
    $billSent = false;
    foreach ($recipients as $recipient) {
        $isNextOfKin = ($recipient['type'] === 'next_of_kin');
        
        // For next of kin, add introduction to the body
        $recipientBody = $body;
        if ($isNextOfKin) {
            $intro = "Dear {$recipient['name']},\n\nThis is a friendly reminder that a payment of KES {$amount} is due for {$tenantName}'s accommodation for the month of {$month}. As the registered Next of Kin, you are receiving this notification to help ensure timely payment.\n\n";
            $recipientBody = $intro . $body;
        }
        
        try {
            $sent = $emailService->send(
                $recipient['email'],
                $recipient['name'],
                'Rent Reminder - ' . $month,
                $recipientBody
            );
            
            // Log the notification
            $recipientService->logNotification(
                (int)$row['tenant_id'],
                $tenantName,
                $recipient['type'],
                $recipient['name'],
                $recipient['email'],
                'Payment Reminder',
                $sent
            );

            if ($sent) {
                if (!$billSent) {
                    $totalSent++;
                    $billSent = true;
                }
                echo "  ✓ Sent to {$recipient['name']} ({$recipient['email']}) - {$month} - KES {$amount}\n";
            } else {
                echo "  ✗ {$recipient['name']} ({$recipient['email']}) - SEND FAILED\n";
                $errors[] = "Failed to send to {$recipient['email']}";
            }
        } catch (\Exception $e) {
            echo "  ! {$recipient['name']} ({$recipient['email']}) - ERROR: " . $e->getMessage() . "\n";
            $errors[] = "Error for {$recipient['email']}: " . $e->getMessage();
            error_log("Rent reminder error for {$recipient['email']}: " . $e->getMessage());
        }
    }
    
    // Log reminder record if at least one email was sent
    if ($billSent) {
        try {
            $db->insert('rent_reminders', [
                'owner_id' => 1,
                'tenant_id' => $row['tenant_id'],
                'house_id' => $row['house_id'],
                'bill_id' => $row['bill_id'],
                'month' => $month,
                'amount' => $row['bill_amount'],
                'days_before_due' => (new DateTime())->diff(new DateTime($row['due_date']))->days,
                'sent_at' => date('Y-m-d H:i:s'),
                'status' => 'sent'
            ]);
        } catch (\Exception $logException) {
            error_log("Rent reminder log error: " . $logException->getMessage());
        }
    }
}

// Log summary
$logFile = __DIR__ . '/../logs/rent_reminders.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

$logMessage = "[" . date('Y-m-d H:i:s') . "] ";
$logMessage .= "Tenant reminders sent: $totalSent";
if (!empty($errors)) $logMessage .= " | Errors: " . count($errors);
file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);

echo "\n=== Summary ===\n";
echo "Total tenant reminders sent: $totalSent\n";
if (!empty($errors)) {
    echo "Errors encountered: " . count($errors) . "\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

exit($errors ? 1 : 0);