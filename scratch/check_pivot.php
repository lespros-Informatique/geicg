<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();
echo "=== COMPOSITION_CLASSES TABLE CONTENT ===\n";
print_r($db->query("SELECT * FROM composition_classes")->fetchAll(PDO::FETCH_ASSOC));
