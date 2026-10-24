<?php
require_once __DIR__ . '/../config/Database.php';
try {
    $db = (new Database())->getCon();
    echo "CONNECTED TO: " . $db->query("SELECT DATABASE()")->fetchColumn() . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
