<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();
$perms = $db->query("SELECT * FROM role_permissions WHERE permission_code = 'PRINT_FILIERES'")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($perms, JSON_PRETTY_PRINT);
