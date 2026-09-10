<?php
$db = new PDO('mysql:host=localhost;dbname=db_eicg;charset=utf8', 'root', '');
$tables = $db->query("SHOW TABLES LIKE '%comp%'")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

foreach ($tables as $t) {
    echo "\n=== Table: $t ===\n";
    $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
    }
}
