<?php
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getCon();

echo "=== TABLES IN DATABASE ===\n";
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

if (in_array('niveaux', $tables)) {
    echo "\n=== NIVEAUX ===\n";
    print_r($db->query("SELECT * FROM niveaux")->fetchAll(PDO::FETCH_ASSOC));
}

if (in_array('filieres', $tables)) {
    echo "\n=== FILIERES ===\n";
    print_r($db->query("SELECT * FROM filieres")->fetchAll(PDO::FETCH_ASSOC));
}

if (in_array('classes', $tables)) {
    echo "\n=== CLASSES ===\n";
    print_r($db->query("SELECT code_classe, libelle_classe, niveau_code, filiere_code FROM classes")->fetchAll(PDO::FETCH_ASSOC));
}

if (in_array('annees_academiques', $tables)) {
    echo "\n=== ANNEES ACADEMIQUES ===\n";
    print_r($db->query("SELECT * FROM annees_academiques")->fetchAll(PDO::FETCH_ASSOC));
} elseif (in_array('annees', $tables)) {
    echo "\n=== ANNEES ===\n";
    print_r($db->query("SELECT * FROM annees")->fetchAll(PDO::FETCH_ASSOC));
}
