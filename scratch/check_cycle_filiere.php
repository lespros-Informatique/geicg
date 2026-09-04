<?php
$db = new PDO("mysql:host=localhost;dbname=db_eicg;charset=utf8", "root", "");

echo "=== FILIERE_CYCLES COLS ===\n";
print_r($db->query("DESCRIBE filiere_cycles")->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== SAMPLE FILIERE_CYCLES ===\n";
print_r($db->query("
    SELECT fc.*, c.libelle_cycle, f.libelle_filiere 
    FROM filiere_cycles fc
    LEFT JOIN cycles c ON c.code_cycle = fc.cycle_code
    LEFT JOIN filieres f ON f.code_filiere = fc.filiere_code
")->fetchAll(PDO::FETCH_ASSOC));
