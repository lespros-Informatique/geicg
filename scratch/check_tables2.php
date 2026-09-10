<?php
$db = new PDO("mysql:host=localhost;dbname=db_eicg;charset=utf8", "root", "");
echo "=== EVENEMENTS ===\n";
print_r($db->query("DESCRIBE evenements")->fetchAll(PDO::FETCH_COLUMN));

echo "=== DOCUMENTS ===\n";
print_r($db->query("DESCRIBE documents")->fetchAll(PDO::FETCH_COLUMN));
