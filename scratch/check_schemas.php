<?php
$db = new PDO("mysql:host=localhost;dbname=db_eicg;charset=utf8", "root", "");

echo "=== ABSENCES ===\n";
print_r($db->query("DESCRIBE absences")->fetchAll(PDO::FETCH_COLUMN));

echo "=== NOTES ===\n";
print_r($db->query("DESCRIBE notes")->fetchAll(PDO::FETCH_COLUMN));

echo "=== ACTUALITES ===\n";
print_r($db->query("DESCRIBE actualites")->fetchAll(PDO::FETCH_COLUMN));

echo "=== EVENEMENTS ===\n";
print_r($db->query("DESCRIBE evenements")->fetchAll(PDO::FETCH_COLUMN));

echo "=== DOCUMENTS ===\n";
print_r($db->query("DESCRIBE documents")->fetchAll(PDO::FETCH_COLUMN));
