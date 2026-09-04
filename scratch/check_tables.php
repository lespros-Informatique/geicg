<?php
$db = new PDO("mysql:host=localhost;dbname=db_eicg;charset=utf8", "root", "");
$cols = $db->query("DESCRIBE type_depenses")->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
