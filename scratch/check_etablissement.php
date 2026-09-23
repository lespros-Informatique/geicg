<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = (new Database())->getCon();
    $rows = $db->query("SELECT * FROM etablissements")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
