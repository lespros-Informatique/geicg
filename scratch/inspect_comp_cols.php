<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();
$cols = $db->query('DESCRIBE compositions')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | Null: ' . $c['Null'] . ' | Default: ' . var_export($c['Default'], true) . "\n";
}
