<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();
echo "=== SEMESTRES COLUMNS ===\n";
$cols = $db->query("DESCRIBE semestres")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . " | " . $c['Type'] . "\n";
}

echo "\n=== ALL SEMESTRES DATA ===\n";
print_r($db->query("SELECT * FROM semestres")->fetchAll(PDO::FETCH_ASSOC));
