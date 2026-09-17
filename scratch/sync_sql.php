<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();

// Dump permissions
$perms = $db->query("SELECT * FROM permissions ORDER BY id_permission ASC")->fetchAll(PDO::FETCH_ASSOC);
$permInserts = [];
foreach ($perms as $p) {
    $created = $db->quote($p['created_at_permission']);
    $permInserts[] = "({$p['id_permission']}," . $db->quote($p['code_permission']) . "," . $db->quote($p['libelle_permission']) . "," . $db->quote($p['module_permission']) . ",'actif',{$created})";
}
$permSql = "INSERT INTO `permissions` VALUES\n" . implode(",\n", $permInserts) . ";";

// Dump role_permissions
$rPerms = $db->query("SELECT * FROM role_permissions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$rPermInserts = [];
foreach ($rPerms as $rp) {
    $rPermInserts[] = "({$rp['id']}," . $db->quote($rp['role_code']) . "," . $db->quote($rp['permission_code']) . ")";
}
$rPermSql = "INSERT INTO `role_permissions` VALUES\n" . implode(",\n", $rPermInserts) . ";";

$sqlPath = __DIR__ . '/../database/db_eicg.sql';
$sqlFile = file_get_contents($sqlPath);

// Replace permissions insert block
$sqlFile = preg_replace("/INSERT INTO [`\"]permissions[`\"].*?;/s", $permSql, $sqlFile);

// Replace role_permissions insert block
$sqlFile = preg_replace("/INSERT INTO [`\"]role_permissions[`\"].*?;/s", $rPermSql, $sqlFile);

file_put_contents($sqlPath, $sqlFile);
echo "database/db_eicg.sql updated successfully!\n";
