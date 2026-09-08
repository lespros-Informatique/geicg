<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();
$anneeCode = 'ANN-BKFX6BV1';

$stmt = $db->prepare("
    SELECT DISTINCT cl.niveau_code, cl.filiere_code, n.libelle_niveau, f.libelle_filiere
    FROM emplois_temps edt
    INNER JOIN classes cl ON cl.code_classe = edt.classe_code
    INNER JOIN niveaux n ON n.code_niveau = cl.niveau_code
    INNER JOIN filieres f ON f.code_filiere = cl.filiere_code
    WHERE (edt.annee_code = ? OR edt.annee_code IS NULL OR edt.annee_code = '')
");
$stmt->execute([$anneeCode]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Emplois du temps active cibles (count: " . count($rows) . "):\n";
print_r($rows);
