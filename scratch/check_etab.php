<?php
$db = new PDO("mysql:host=localhost;dbname=db_eicg;charset=utf8", "root", "");
echo "=== ETABLISSEMENTS ===\n";
print_r($db->query("SELECT * FROM etablissements")->fetchAll(PDO::FETCH_ASSOC));
