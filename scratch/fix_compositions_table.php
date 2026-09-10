<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();

$columnsToAdd = [
    "classe_code" => "VARCHAR(50) NULL AFTER libelle_composition",
    "matiere_code" => "VARCHAR(50) NULL AFTER classe_code",
    "type_composition" => "ENUM('COMPOSITION','EXAMEN') NOT NULL DEFAULT 'COMPOSITION' AFTER matiere_code",
    "date_composition" => "DATE NULL AFTER semestre_code",
    "heure_debut" => "TIME NULL AFTER date_composition",
    "heure_fin" => "TIME NULL AFTER heure_debut",
    "salle_code" => "VARCHAR(50) NULL AFTER heure_fin",
    "enseignant_code" => "VARCHAR(50) NULL AFTER salle_code"
];

$existingCols = $db->query("DESCRIBE compositions")->fetchAll(PDO::FETCH_COLUMN);

foreach ($columnsToAdd as $colName => $colDef) {
    if (!in_array($colName, $existingCols)) {
        $sql = "ALTER TABLE compositions ADD COLUMN $colName $colDef";
        $db->exec($sql);
        echo "Added column: $colName\n";
    } else {
        echo "Column already exists: $colName\n";
    }
}

echo "\n=== FINAL COMPOSITIONS TABLE STRUCTURE ===\n";
$cols = $db->query("DESCRIBE compositions")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo sprintf("%-25s | %-20s | Null: %-3s | Default: %s\n", $c['Field'], $c['Type'], $c['Null'], var_export($c['Default'], true));
}
