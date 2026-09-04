<?php
$db = new PDO("mysql:host=localhost;dbname=db_eicg;charset=utf8", "root", "");
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
