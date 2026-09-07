<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();

echo "=== COMPOSITIONS TABLE COLUMNS ===\n";
$cols = $db->query("DESCRIBE compositions")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo sprintf("%-25s | %-20s | Null: %-3s | Default: %s\n", $c['Field'], $c['Type'], $c['Null'], var_export($c['Default'], true));
}

echo "\n=== NOTES TABLE COLUMNS ===\n";
$ncols = $db->query("DESCRIBE notes")->fetchAll(PDO::FETCH_ASSOC);
foreach ($ncols as $nc) {
    echo sprintf("%-25s | %-20s | Null: %-3s | Default: %s\n", $nc['Field'], $nc['Type'], $nc['Null'], var_export($nc['Default'], true));
}
