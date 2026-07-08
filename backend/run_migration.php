<?php
require_once 'backend/app/Core/Env.php';
\App\Core\Env::load();
require_once 'backend/app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$sql = file_get_contents('backend/database/migrations/021_add_bill_type.sql');
$lines = explode("\n", $sql);
$cleanSql = '';
foreach ($lines as $line) {
    $line = trim($line);
    if (strpos($line, '--') === 0) continue;
    $cleanSql .= $line . ' ';
}
$statements = array_filter(array_map('trim', explode(';', $cleanSql)));
foreach ($statements as $statement) {
    if (!empty($statement)) {
        try {
            $db->query($statement);
            echo "Executed: " . substr($statement, 0, 70) . "\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
echo "\nDone\n";