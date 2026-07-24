<?php
require_once __DIR__ . '/app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/app/Core/Database.php';

$db = \App\Core\Database::getInstance();
$bills = $db->fetchAll("SELECT b.id, b.house_id, h.tenant_id FROM bills b LEFT JOIN houses h ON b.house_id = h.id WHERE b.tenant_id IS NULL");

$fixed = 0;
foreach ($bills as $b) {
    if (!empty($b['tenant_id'])) {
        $db->update('bills', ['tenant_id' => (int) $b['tenant_id']], 'id = ?', [(int) $b['id']]);
        echo "Fixed bill {$b['id']} -> tenant_id {$b['tenant_id']}\n";
        $fixed++;
    }
}
echo "Done. Fixed $fixed bills.\n";