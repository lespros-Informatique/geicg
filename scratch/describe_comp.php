<?php
$db = new PDO('mysql:host=localhost;dbname=db_eicg;charset=utf8', 'root', '');
$cols = $db->query('DESCRIBE compositions')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . " (" . $c['Type'] . ")\n";
}
