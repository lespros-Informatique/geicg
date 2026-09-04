<?php
$db = new PDO("mysql:host=localhost;dbname=db_eicg;charset=utf8", "root", "");

// Make sure ANN-0EDLMW66 is set to actif
$db->exec("UPDATE annees SET statut_annee = 'actif' WHERE code_annee = 'ANN-0EDLMW66'");

echo "=== V_DASH_SYNTHESE_ANNUELLE FOR ANN-0EDLMW66 ===\n";
$stmt = $db->prepare("SELECT * FROM v_dash_synthese_annuelle WHERE code_annee = ?");
$stmt->execute(['ANN-0EDLMW66']);
print_r($stmt->fetch(PDO::FETCH_ASSOC));

echo "=== INSCRIPTIONS FOR ANN-0EDLMW66 ===\n";
$stmtI = $db->prepare("SELECT * FROM v_dash_inscriptions_details WHERE annee_code = ?");
$stmtI->execute(['ANN-0EDLMW66']);
print_r($stmtI->fetchAll(PDO::FETCH_ASSOC));

echo "=== PAIEMENTS FOR ANN-0EDLMW66 ===\n";
$stmtP = $db->prepare("SELECT * FROM paiements WHERE inscription_code IN (SELECT code_inscription FROM inscriptions WHERE annee_code = ?)");
$stmtP->execute(['ANN-0EDLMW66']);
print_r($stmtP->fetchAll(PDO::FETCH_ASSOC));
