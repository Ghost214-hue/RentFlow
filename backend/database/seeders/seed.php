<?php
/**
 * RentaFlow Database Seeder
 * Run: php backend/database/seeders/seed.php
 */

require_once __DIR__ . '/../../app/Core/Database.php';

use App\Core\Database;

echo "=== RentaFlow Database Seeder ===\n\n";

try {
    $db = Database::getInstance();

    // 1. Create demo owner
    echo "Creating demo owner... ";
    $existing = $db->fetchOne("SELECT id FROM owners WHERE email = 'owner@rentaflow.co'");
    if ($existing) {
        echo "Already exists (ID: {$existing['id']})\n";
        $ownerId = $existing['id'];
    } else {
        $ownerId = $db->insert('owners', [
            'name'       => 'James Mwangi',
            'email'      => 'owner@rentaflow.co',
            'password'   => password_hash('admin123', PASSWORD_BCRYPT),
            'phone'      => '+254 712 345 678',
            'avatar'     => 'JM',
            'last_login' => '2024-01-15 08:30:00',
        ]);
        echo "Created (ID: {$ownerId})\n";
    }

    // 2. Create properties
    $properties = [
        ['name' => 'Sunrise Apartments', 'address' => '123 Kilimani Road, Nairobi', 'type' => 'Apartment Block', 'units' => 4, 'image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=400', 'rent' => 45000],
        ['name' => 'Green Valley Estate', 'address' => '456 Ngong Road, Nairobi', 'type' => 'Townhouses', 'units' => 2, 'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400', 'rent' => 65000],
        ['name' => 'Palm Heights', 'address' => '789 Mombasa Road, Nairobi', 'type' => 'Studio Apartments', 'units' => 3, 'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400', 'rent' => 28000],
    ];

    $propIds = [];
    echo "\nCreating properties...\n";
    foreach ($properties as $p) {
        $existing = $db->fetchOne("SELECT id FROM properties WHERE name = ? AND owner_id = ?", [$p['name'], $ownerId]);
        if ($existing) {
            echo "  - {$p['name']}: Already exists (ID: {$existing['id']})\n";
            $propIds[$p['name']] = $existing['id'];
        } else {
            $id = $db->insert('properties', array_merge($p, ['owner_id' => $ownerId, 'occupied' => 0]));
            echo "  - {$p['name']}: Created (ID: {$id})\n";
            $propIds[$p['name']] = $id;
        }
    }

    // 3. Create houses
    $houses = [
        ['property' => 'Sunrise Apartments', 'unit' => 'A1', 'type' => '2 Bedroom', 'rent' => 45000, 'water_meter' => 'WM-2024-001', 'elec_meter' => 'EM-2024-001'],
        ['property' => 'Sunrise Apartments', 'unit' => 'A2', 'type' => '2 Bedroom', 'rent' => 45000, 'water_meter' => 'WM-2024-002', 'elec_meter' => 'EM-2024-002'],
        ['property' => 'Sunrise Apartments', 'unit' => 'A3', 'type' => '1 Bedroom', 'rent' => 35000, 'water_meter' => 'WM-2024-003', 'elec_meter' => 'EM-2024-003'],
        ['property' => 'Sunrise Apartments', 'unit' => 'A4', 'type' => '2 Bedroom', 'rent' => 45000, 'water_meter' => 'WM-2024-004', 'elec_meter' => 'EM-2024-004'],
        ['property' => 'Green Valley Estate', 'unit' => 'T1', 'type' => '3 Bedroom', 'rent' => 65000, 'water_meter' => 'WM-2024-005', 'elec_meter' => 'EM-2024-005'],
        ['property' => 'Green Valley Estate', 'unit' => 'T2', 'type' => '3 Bedroom', 'rent' => 65000, 'water_meter' => 'WM-2024-006', 'elec_meter' => 'EM-2024-006'],
        ['property' => 'Palm Heights', 'unit' => 'S1', 'type' => 'Studio', 'rent' => 28000, 'water_meter' => 'WM-2024-008', 'elec_meter' => 'EM-2024-008'],
        ['property' => 'Palm Heights', 'unit' => 'S2', 'type' => 'Studio', 'rent' => 28000, 'water_meter' => 'WM-2024-009', 'elec_meter' => 'EM-2024-009'],
        ['property' => 'Palm Heights', 'unit' => 'S3', 'type' => 'Studio', 'rent' => 28000, 'water_meter' => 'WM-2024-010', 'elec_meter' => 'EM-2024-010'],
    ];

    $houseIds = [];
    echo "\nCreating houses...\n";
    foreach ($houses as $h) {
        $existing = $db->fetchOne("SELECT id FROM houses WHERE unit = ? AND property_id = ?", [$h['unit'], $propIds[$h['property']]]);
        if ($existing) {
            echo "  - {$h['property']} {$h['unit']}: Already exists (ID: {$existing['id']})\n";
            $houseIds[$h['property']][$h['unit']] = $existing['id'];
        } else {
            $id = $db->insert('houses', [
                'owner_id'    => $ownerId,
                'property_id' => $propIds[$h['property']],
                'unit'        => $h['unit'],
                'type'        => $h['type'],
                'status'      => 'occupied',
                'rent'        => $h['rent'],
                'water_meter' => $h['water_meter'],
                'elec_meter'  => $h['elec_meter'],
            ]);
            echo "  - {$h['property']} {$h['unit']}: Created (ID: {$id})\n";
            $houseIds[$h['property']][$h['unit']] = $id;
        }
    }

    // 4. Create tenants
    $tenants = [
        ['name' => 'David Kimani', 'email' => 'david.k@email.com', 'phone' => '+254 734 567 890', 'id_number' => '29876543', 'house' => 'Sunrise Apartments', 'unit' => 'A1', 'lease_start' => '2023-06-01', 'lease_end' => '2024-05-31', 'deposit' => 90000, 'balance' => 0],
        ['name' => 'Grace Wanjiku', 'email' => 'grace.w@email.com', 'phone' => '+254 722 333 444', 'id_number' => '30123456', 'house' => 'Sunrise Apartments', 'unit' => 'A2', 'lease_start' => '2023-08-15', 'lease_end' => '2024-08-14', 'deposit' => 90000, 'balance' => 45000],
        ['name' => 'Peter Omondi', 'email' => 'peter.o@email.com', 'phone' => '+254 711 444 555', 'id_number' => '31234567', 'house' => 'Sunrise Apartments', 'unit' => 'A3', 'lease_start' => '2023-09-01', 'lease_end' => '2024-08-31', 'deposit' => 70000, 'balance' => 0],
        ['name' => 'Lucy Njeri', 'email' => 'lucy.n@email.com', 'phone' => '+254 733 555 666', 'id_number' => '32345678', 'house' => 'Green Valley Estate', 'unit' => 'T1', 'lease_start' => '2023-07-01', 'lease_end' => '2024-06-30', 'deposit' => 130000, 'balance' => 65000],
        ['name' => 'Michael Otieno', 'email' => 'michael.o@email.com', 'phone' => '+254 744 666 777', 'id_number' => '33456789', 'house' => 'Green Valley Estate', 'unit' => 'T2', 'lease_start' => '2023-10-01', 'lease_end' => '2024-09-30', 'deposit' => 130000, 'balance' => 0],
        ['name' => 'Anne Muthoni', 'email' => 'anne.m@email.com', 'phone' => '+254 755 777 888', 'id_number' => '34567890', 'house' => 'Palm Heights', 'unit' => 'S1', 'lease_start' => '2023-11-01', 'lease_end' => '2024-10-31', 'deposit' => 56000, 'balance' => 28000],
        ['name' => 'Joseph Kamau', 'email' => 'joseph.k@email.com', 'phone' => '+254 766 888 999', 'id_number' => '35678901', 'house' => 'Palm Heights', 'unit' => 'S2', 'lease_start' => '2023-12-01', 'lease_end' => '2024-11-30', 'deposit' => 56000, 'balance' => 0],
    ];

    $tenantIds = [];
    echo "\nCreating tenants...\n";
    foreach ($tenants as $t) {
        $existing = $db->fetchOne("SELECT id FROM tenants WHERE email = ? AND owner_id = ?", [$t['email'], $ownerId]);
        if ($existing) {
            echo "  - {$t['name']}: Already exists (ID: {$existing['id']})\n";
            $tenantIds[$t['email']] = $existing['id'];
        } else {
            $houseId = $houseIds[$t['house']][$t['unit']];
            $propId = $propIds[$t['house']];
            $id = $db->insert('tenants', [
                'owner_id'    => $ownerId,
                'property_id' => $propId,
                'house_id'    => $houseId,
                'name'        => $t['name'],
                'email'       => $t['email'],
                'phone'       => $t['phone'],
                'id_number'   => $t['id_number'],
                'lease_start' => $t['lease_start'],
                'lease_end'   => $t['lease_end'],
                'deposit'     => $t['deposit'],
                'balance'     => $t['balance'],
                'password'    => password_hash($t['id_number'], PASSWORD_BCRYPT),
            ]);
            // Link tenant to house
            $db->update('houses', ['tenant_id' => $id, 'status' => 'occupied'], 'id = ?', [$houseId]);
            echo "  - {$t['name']}: Created (ID: {$id})\n";
            $tenantIds[$t['email']] = $id;
        }
    }

    // 5. Create payments
    echo "\nCreating payments...\n";
    $payments = [
        ['tenant' => 'david.k@email.com', 'house' => 'Sunrise Apartments', 'unit' => 'A1', 'amount' => 45000, 'type' => 'Rent', 'date' => '2024-01-05'],
        ['tenant' => 'grace.w@email.com', 'house' => 'Sunrise Apartments', 'unit' => 'A2', 'amount' => 20000, 'type' => 'Rent', 'date' => '2024-01-10'],
        ['tenant' => 'peter.o@email.com', 'house' => 'Sunrise Apartments', 'unit' => 'A3', 'amount' => 35000, 'type' => 'Rent', 'date' => '2024-01-03'],
        ['tenant' => 'lucy.n@email.com', 'house' => 'Green Valley Estate', 'unit' => 'T1', 'amount' => 30000, 'type' => 'Rent', 'date' => '2024-01-08'],
        ['tenant' => 'michael.o@email.com', 'house' => 'Green Valley Estate', 'unit' => 'T2', 'amount' => 65000, 'type' => 'Rent', 'date' => '2024-01-02'],
        ['tenant' => 'david.k@email.com', 'house' => 'Sunrise Apartments', 'unit' => 'A1', 'amount' => 1200, 'type' => 'Electricity', 'date' => '2024-01-12'],
        ['tenant' => 'anne.m@email.com', 'house' => 'Palm Heights', 'unit' => 'S1', 'amount' => 15000, 'type' => 'Rent', 'date' => '2024-01-15'],
    ];

    foreach ($payments as $p) {
        $receipt = 'RCP-2024-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $tenantId = $tenantIds[$p['tenant']];
        $houseId = $houseIds[$p['house']][$p['unit']];

        $existing = $db->fetchOne(
            "SELECT id FROM payments WHERE tenant_id = ? AND amount = ? AND type = ? AND DATE(date) = ?",
            [$tenantId, $p['amount'], $p['type'], $p['date']]
        );
        if ($existing) continue;

        $db->insert('payments', [
            'owner_id'    => $ownerId,
            'tenant_id'   => $tenantId,
            'house_id'    => $houseId,
            'amount'      => $p['amount'],
            'type'        => $p['type'],
            'method'      => 'M-Pesa',
            'date'        => $p['date'],
            'status'      => ($p['amount'] < 30000 && $p['type'] === 'Rent') ? 'pending' : 'completed',
            'receipt'     => $receipt,
            'description' => $p['type'] === 'Rent' ? "January 2024 Rent" : "Electricity Bill",
        ]);
        echo "  - Receipt {$receipt}: {$p['tenant']} - KES {$p['amount']}\n";
    }

    // 6. Create bills
    echo "\nCreating bills...\n";
    $bills = [
        ['house' => 'Sunrise Apartments', 'unit' => 'A1', 'rent' => 45000, 'water' => 450, 'electricity' => 1200, 'status' => 'paid'],
        ['house' => 'Sunrise Apartments', 'unit' => 'A2', 'rent' => 45000, 'water' => 850, 'electricity' => 0, 'status' => 'partial'],
        ['house' => 'Sunrise Apartments', 'unit' => 'A3', 'rent' => 35000, 'water' => 0, 'electricity' => 0, 'status' => 'paid'],
        ['house' => 'Green Valley Estate', 'unit' => 'T1', 'rent' => 65000, 'water' => 1200, 'electricity' => 2300, 'status' => 'partial'],
        ['house' => 'Green Valley Estate', 'unit' => 'T2', 'rent' => 65000, 'water' => 0, 'electricity' => 0, 'status' => 'paid'],
        ['house' => 'Palm Heights', 'unit' => 'S1', 'rent' => 28000, 'water' => 450, 'electricity' => 0, 'status' => 'partial'],
        ['house' => 'Palm Heights', 'unit' => 'S2', 'rent' => 28000, 'water' => 0, 'electricity' => 890, 'status' => 'paid'],
    ];

    foreach ($bills as $b) {
        $houseId = $houseIds[$b['house']][$b['unit']];
        $total = $b['rent'] + $b['water'] + $b['electricity'];
        $existing = $db->fetchOne("SELECT id FROM bills WHERE house_id = ? AND month = '2024-01'", [$houseId]);
        if (!$existing) {
            $db->insert('bills', [
                'owner_id'    => $ownerId,
                'house_id'    => $houseId,
                'month'       => '2024-01',
                'rent'        => $b['rent'],
                'water'       => $b['water'],
                'electricity' => $b['electricity'],
                'total'       => $total,
                'status'      => $b['status'],
                'due_date'    => '2024-01-05',
            ]);
            echo "  - {$b['house']} {$b['unit']}: KES {$total} ({$b['status']})\n";
        }
    }

    // 7. Create complaints
    echo "\nCreating complaints...\n";
    $complaints = [
        ['tenant' => 'david.k@email.com', 'title' => 'Leaking Kitchen Faucet', 'category' => 'Plumbing', 'priority' => 'medium', 'status' => 'in-progress', 'date' => '2024-01-10', 'description' => 'The kitchen faucet has been dripping continuously for 3 days.'],
        ['tenant' => 'grace.w@email.com', 'title' => 'Broken Window Lock', 'category' => 'Security', 'priority' => 'high', 'status' => 'resolved', 'date' => '2024-01-08', 'description' => 'The bedroom window lock is broken, cannot secure the window.'],
        ['tenant' => 'lucy.n@email.com', 'title' => 'Power Outage in Bedroom', 'category' => 'Electrical', 'priority' => 'high', 'status' => 'open', 'date' => '2024-01-14', 'description' => 'No electricity in the master bedroom since yesterday evening.'],
        ['tenant' => 'anne.m@email.com', 'title' => 'Noisy Neighbors', 'category' => 'Noise', 'priority' => 'low', 'status' => 'open', 'date' => '2024-01-13', 'description' => 'Frequent loud music from nearby unit after 10 PM.'],
    ];

    foreach ($complaints as $c) {
        $tenantId = $tenantIds[$c['tenant']];
        $timeline = [];
        if ($c['status'] !== 'open') {
            $timeline[] = ['date' => $c['date'], 'status' => 'Submitted', 'note' => 'Complaint logged'];
            if ($c['status'] === 'in-progress') {
                $timeline[] = ['date' => date('Y-m-d', strtotime($c['date'] . '+1 day')), 'status' => 'Acknowledged', 'note' => 'Caretaker assigned'];
                $timeline[] = ['date' => date('Y-m-d', strtotime($c['date'] . '+2 day')), 'status' => 'In Progress', 'note' => 'Repair scheduled'];
            }
            if ($c['status'] === 'resolved') {
                $timeline[] = ['date' => $c['date'], 'status' => 'Submitted', 'note' => 'Complaint logged'];
                $timeline[] = ['date' => date('Y-m-d', strtotime($c['date'])), 'status' => 'Acknowledged', 'note' => 'Urgent repair assigned'];
                $timeline[] = ['date' => date('Y-m-d', strtotime($c['date'] . '+1 day')), 'status' => 'Resolved', 'note' => 'Issue fixed'];
            }
        } else {
            $timeline[] = ['date' => $c['date'], 'status' => 'Submitted', 'note' => 'Complaint logged'];
        }

        $tenant = $db->fetchOne("SELECT house_id FROM tenants WHERE id = ?", [$tenantId]);

        $db->insert('complaints', [
            'owner_id'    => $ownerId,
            'tenant_id'   => $tenantId,
            'house_id'    => $tenant['house_id'],
            'title'       => $c['title'],
            'category'    => $c['category'],
            'priority'    => $c['priority'],
            'status'      => $c['status'],
            'date'        => $c['date'],
            'description' => $c['description'],
            'timeline'    => json_encode($timeline),
            'comments'    => '[]',
        ]);
        echo "  - {$c['title']} ({$c['status']})\n";
    }

    // 8. Update property occupied counts
    echo "\nUpdating property stats...\n";
    foreach ($propIds as $name => $id) {
        $occupied = $db->fetchOne("SELECT COUNT(*) as total FROM houses WHERE property_id = ? AND status = 'occupied'", [$id]);
        $total = $db->fetchOne("SELECT COUNT(*) as total FROM houses WHERE property_id = ?", [$id]);
        $db->update('properties', ['occupied' => $occupied['total'], 'units' => $total['total']], 'id = ?', [$id]);
        echo "  - {$name}: {$occupied['total']}/{$total['total']} occupied\n";
    }

    // 9. Create default templates
    echo "\nCreating communication templates...\n";
    $templates = [
        ['name' => 'Rent Reminder', 'type' => 'email', 'subject' => 'Rent Reminder - {{month}}', 'body' => 'Dear {{tenant}},\n\nThis is a friendly reminder that your rent of KES {{amount}} for {{month}} is due on {{dueDate}}.\n\nThank you,\n{{owner}}'],
        ['name' => 'Rent Confirmation', 'type' => 'whatsapp', 'subject' => '', 'body' => 'Hi {{tenant}},\n\nYour payment of KES {{amount}} for {{month}} has been received. Receipt: {{receipt}}'],
        ['name' => 'Maintenance Notice', 'type' => 'email', 'subject' => 'Scheduled Maintenance - {{property}}', 'body' => 'Dear Residents,\n\nPlease be informed that maintenance will be conducted on {{date}} from {{time}}: {{details}}'],
        ['name' => 'Complaint Update', 'type' => 'email', 'subject' => 'Update on Your Complaint #{{id}}', 'body' => 'Dear {{tenant}},\n\nYour complaint regarding {{issue}} has been updated to status: {{status}}.\n\n{{message}}'],
        ['name' => 'Lease Renewal', 'type' => 'email', 'subject' => 'Lease Renewal Notice', 'body' => 'Dear {{tenant}},\n\nYour lease for {{house}} expires on {{date}}. Please contact us to discuss renewal options.'],
        ['name' => 'Management Notice', 'type' => 'email', 'subject' => '{{title}} - RentaFlow Management Notice', 'body' => "Dear {{tenant_name}},\n\nYou have received an important notice from {{sender_name}}.\n\n--- MESSAGE ---\n{{description}}\n\n--- NEXT STEPS ---\nPlease log in to your RentaFlow account to view full details, track updates, and respond if needed.\n\nIf you have any questions, contact the property owner or caretaker directly.\n\nBest regards,\nRentaFlow Team"],
    ];

    foreach ($templates as $t) {
        $existing = $db->fetchOne("SELECT id FROM templates WHERE name = ? AND owner_id = ?", [$t['name'], $ownerId]);
        if (!$existing) {
            $db->insert('templates', array_merge($t, ['owner_id' => $ownerId]));
            echo "  - {$t['name']}: Created\n";
        }
    }

    // 10. Seed email_templates table (used by EmailService for password reset, etc.)
    echo "\nCreating email templates...\n";
    $emailTemplates = [
        [
            'owner_id' => $ownerId,
            'name' => 'Password Reset',
            'type' => 'email',
            'subject' => 'Password Reset Code - RentaFlow',
            'body' => "Dear {{name}},\n\nWe received a request to reset your password for your RentaFlow account.\n\nYour verification code is: {{code}}\n\nThis code will expire in {{expires}}.\n\nIf you did not request a password reset, please ignore this email and your password will remain unchanged.\n\nTo reset your password:\n1. Enter the verification code above\n2. Create a new secure password\n\nBest regards,\nRentaFlow Team",
        ],
        [
            'owner_id' => $ownerId,
            'name' => 'Tenant Welcome',
            'type' => 'email',
            'subject' => 'Welcome to {{property}} - Your New Home',
            'body' => "Dear {{tenant}},\n\nWelcome to {{property}}! We're excited to have you as our new tenant.\n\nHere are your details:\n- Property: {{property}}\n- Unit/House: {{house}}\n- Email: {{email}}\n- Password: {{password}}\n\nYou can login to your tenant portal at: {{link}}\n\nPlease keep your login credentials secure.\n\nWelcome home!\n\nBest regards,\nProperty Management",
        ],
        [
            'owner_id' => $ownerId,
            'name' => 'Payment Confirmation',
            'type' => 'email',
            'subject' => 'Payment Confirmation - KES {{amount}}',
            'body' => "Hi {{tenant}},\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES {{amount}}\n- Category: {{category}}\n- Date: {{date}}\n- Current Balance: KES {{balance}}\n\nThank you for your payment!\n\nBest regards,\nProperty Management",
        ],
    ];

    foreach ($emailTemplates as $t) {
        $existing = $db->fetchOne("SELECT id FROM email_templates WHERE name = ? AND owner_id = ?", [$t['name'], $ownerId]);
        if (!$existing) {
            $db->insert('email_templates', $t);
            echo "  - {$t['name']}: Created\n";
        } else {
            echo "  - {$t['name']}: Already exists\n";
        }
    }

    echo "\n=== Seeding Complete! ===\n";
    echo "Login with: owner@rentaflow.co / admin123\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}