<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();
$db->exec("DELETE FROM compositions WHERE code_composition = 'CMP-G5GUHX7S'");
echo "Test record cleaned up successfully.\n";
