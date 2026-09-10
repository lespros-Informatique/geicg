<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Validator.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../models/home/ModelHome.php';

$db = (new Database())->getCon();
$anneeCode = 'ANN-0EDLMW66';

try {
    echo "1. v_dash_synthese_annuelle...\n";
    $stmtSynthese = $db->prepare("SELECT * FROM v_dash_synthese_annuelle WHERE code_annee = ?");
    $stmtSynthese->execute([$anneeCode ?: '']);
    $synthese = $stmtSynthese->fetch(PDO::FETCH_ASSOC) ?: [];
    print_r($synthese);

    echo "2. Compteurs Pédagogiques...\n";
    $totalEnseignants = (int)$db->query("SELECT COUNT(*) FROM enseignants WHERE statut_enseignant = 'actif'")->fetchColumn();
    $totalMatieres = (int)$db->query("SELECT COUNT(*) FROM matieres WHERE statut_matiere = 'actif'")->fetchColumn();
    $totalSalles = (int)$db->query("SELECT COUNT(*) FROM salles WHERE statut_salle = 'actif'")->fetchColumn();
    echo "Enseignants: $totalEnseignants, Matieres: $totalMatieres, Salles: $totalSalles\n";

    echo "3. Notes & Absences...\n";
    $stmtNotes = $db->prepare("
        SELECT COUNT(*) FROM notes n 
        INNER JOIN inscriptions i ON i.code_inscription = n.inscription_code 
        WHERE i.annee_code = ? AND n.statut_note = 'actif'
    ");
    $stmtNotes->execute([$anneeCode ?: '']);
    $totalNotes = (int)$stmtNotes->fetchColumn();
    echo "Notes: $totalNotes\n";

    $stmtAbs = $db->prepare("
        SELECT COUNT(*) FROM absences a 
        INNER JOIN inscriptions i ON i.code_inscription = a.inscription_code 
        WHERE i.annee_code = ? AND a.statut_absence = 'actif'
    ");
    $stmtAbs->execute([$anneeCode ?: '']);
    $totalAbsences = (int)$stmtAbs->fetchColumn();
    echo "Absences: $totalAbsences\n";

    echo "4. Communication...\n";
    $totalActualites = (int)$db->query("SELECT COUNT(*) FROM actualites WHERE statut_actualite = 'actif'")->fetchColumn();
    echo "Actualites: $totalActualites\n";

    $totalEvenements = (int)$db->query("SELECT COUNT(*) FROM evenements WHERE statut_evenement = 'actif'")->fetchColumn();
    echo "Evenements: $totalEvenements\n";

    $totalDocuments = (int)$db->query("SELECT COUNT(*) FROM documents WHERE statut_document = 'actif'")->fetchColumn();
    echo "Documents: $totalDocuments\n";

} catch (Exception $e) {
    echo "EXCEPTION THROWN: " . $e->getMessage() . "\n";
}
