<?php
$db = new PDO("mysql:host=localhost;dbname=db_eicg;charset=utf8", "root", "");

echo "=== TABLES STRUCTURE ===\n";
echo "1. FILIERES:\n";
print_r($db->query("DESCRIBE filieres")->fetchAll(PDO::FETCH_ASSOC));

echo "\n2. NIVEAUX:\n";
print_r($db->query("DESCRIBE niveaux")->fetchAll(PDO::FETCH_ASSOC));

echo "\n3. CLASSES:\n";
print_r($db->query("DESCRIBE classes")->fetchAll(PDO::FETCH_ASSOC));

echo "\n4. FILIERE_NIVEAU:\n";
try {
    print_r($db->query("DESCRIBE filiere_niveau")->fetchAll(PDO::FETCH_ASSOC));
} catch(Exception $e) {
    echo "No filiere_niveau table\n";
}
