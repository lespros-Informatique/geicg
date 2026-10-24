<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();

echo "=== SHOW CREATE VIEW v_dash_synthese_annuelle ===\n";
try {
    $row = $db->query("SHOW CREATE VIEW v_dash_synthese_annuelle")->fetch(PDO::FETCH_ASSOC);
    echo $row['Create View'] . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== V_DASH_SYNTHESE_ANNUELLE DATA ===\n";
try {
    $rows = $db->query("SELECT * FROM v_dash_synthese_annuelle")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== PAIEMENTS BREAKDOWN BY CATEGORIE_PAIEMENT ===\n";
try {
    $rows = $db->query("SELECT categorie_paiement, tranche_code, COUNT(*) as cnt, SUM(montant_paiement) as total FROM paiements GROUP BY categorie_paiement, tranche_code")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
