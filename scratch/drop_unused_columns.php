<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();

$colsToDrop = [
    'classe_code',
    'matiere_code',
    'type_composition',
    'date_composition',
    'heure_debut',
    'heure_fin',
    'salle_code',
    'enseignant_code'
];

$existingCols = $db->query("DESCRIBE compositions")->fetchAll(PDO::FETCH_COLUMN);

foreach ($colsToDrop as $col) {
    if (in_array($col, $existingCols)) {
        $db->exec("ALTER TABLE compositions DROP COLUMN $col");
        echo "Dropped column: $col\n";
    }
}

// Create composition_classes pivot table if not exists
$createPivotSql = "
CREATE TABLE IF NOT EXISTS composition_classes (
    id_composition_classe INT AUTO_INCREMENT PRIMARY KEY,
    composition_code VARCHAR(50) NOT NULL,
    niveau_code VARCHAR(50) NULL,
    filiere_code VARCHAR(50) NULL,
    classe_code VARCHAR(50) NOT NULL,
    created_at_composition_classe DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_comp_code (composition_code),
    KEY idx_class_code (classe_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
$db->exec($createPivotSql);
echo "Pivot table composition_classes ready.\n";

echo "\n=== COMPOSITIONS TABLE COLUMNS AFTER DROP ===\n";
$cols = $db->query("DESCRIBE compositions")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo sprintf("%-25s | %-20s | Null: %-3s | Default: %s\n", $c['Field'], $c['Type'], $c['Null'], var_export($c['Default'], true));
}

echo "\n=== COMPOSITION_CLASSES TABLE COLUMNS ===\n";
$pcols = $db->query("DESCRIBE composition_classes")->fetchAll(PDO::FETCH_ASSOC);
foreach ($pcols as $pc) {
    echo sprintf("%-25s | %-20s | Null: %-3s | Default: %s\n", $pc['Field'], $pc['Type'], $pc['Null'], var_export($pc['Default'], true));
}
