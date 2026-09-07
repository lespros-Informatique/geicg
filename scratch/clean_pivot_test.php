<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();
$db->exec("DELETE FROM compositions WHERE code_composition = 'CMP-TESTPIVOT'");
$db->exec("DELETE FROM composition_classes WHERE composition_code = 'CMP-TESTPIVOT'");
echo "Test records cleaned up.\n";
