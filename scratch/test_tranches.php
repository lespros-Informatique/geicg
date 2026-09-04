<?php
$db = new PDO("mysql:host=localhost;dbname=db_eicg;charset=utf8", "root", "");

// Fetch one inscription for BTS IDA
$stmtIns = $db->query("
    SELECT i.code_inscription, i.affectation_etat, e.nom_etudiant, e.prenom_etudiant, c.filiere_code, c.niveau_code, i.annee_code
    FROM inscriptions i
    JOIN etudiants e ON e.code_etudiant = i.etudiant_code
    JOIN classes c ON c.code_classe = i.classe_code
    WHERE c.filiere_code = 'FIL-BP2COHHA'
    LIMIT 2
");
$inscriptions = $stmtIns->fetchAll(PDO::FETCH_ASSOC);

foreach ($inscriptions as $ins) {
    echo "\n------------------------------------------------\n";
    echo "ELEVE: {$ins['nom_etudiant']} {$ins['prenom_etudiant']} (Etat affectation: {$ins['affectation_etat']})\n";
    
    $filiereCode = $ins['filiere_code'];
    $niveauCode = $ins['niveau_code'];
    $anneeCode = $ins['annee_code'];
    $rawAff = strtolower(trim($ins['affectation_etat'] ?? ''));
    $isAffecte = ($rawAff === 'oui' || $rawAff === 'affecte' || $rawAff === '1');
    $affEtat = $isAffecte ? 'affecte' : 'non_affecte';

    // 1. Scolarite Grid
    $stmtSco = $db->prepare("
        SELECT * FROM scolarites 
        WHERE filiere_code = ? 
          AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
          AND (annee_code = ? OR annee_code = '' OR annee_code IS NULL)
          AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
          AND statut_scolarite = 'actif'
        ORDER BY (CASE WHEN annee_code = ? THEN 1 ELSE 2 END),
                 (CASE WHEN affectation_etat = ? THEN 1 ELSE 2 END),
                 id_scolarite DESC
        LIMIT 1
    ");
    $stmtSco->execute([$filiereCode, $niveauCode, $anneeCode, $affEtat, $anneeCode, $affEtat]);
    $scoGrid = $stmtSco->fetch(PDO::FETCH_ASSOC);
    $codeScolarite = $scoGrid['code_scolarite'] ?? '';

    echo "Grille Scolarité Sélectionnée: {$codeScolarite} ({$scoGrid['montant_scolarite']} FCFA, Régime: {$scoGrid['affectation_etat']})\n";

    // 2. Fetch Tranches with NEW QUERY
    if (!empty($codeScolarite)) {
        $stmtTr = $db->prepare("
            SELECT t.*
            FROM tranches_scolarite t
            WHERE t.statut_tranche = 'actif'
              AND (
                t.scolarite_code = ?
                OR (
                  (t.scolarite_code = '' OR t.scolarite_code IS NULL)
                  AND t.filiere_code = ?
                  AND (t.niveau_code = ? OR t.niveau_code = '' OR t.niveau_code IS NULL)
                  AND (t.annee_code = ? OR t.annee_code = '' OR t.annee_code IS NULL)
                )
              )
            ORDER BY t.date_limite ASC, t.id_tranche ASC
        ");
        $stmtTr->execute([$codeScolarite, $filiereCode, $niveauCode, $anneeCode]);
    }
    $tranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC);
    echo "Nombre de tranches retournées: " . count($tranches) . "\n";
    foreach ($tranches as $tr) {
        echo "  -> Tranche: {$tr['libelle_tranche']} | Code: {$tr['code_tranche']} | Montant: {$tr['montant_tranche']} FCFA | Echeance: {$tr['date_limite']}\n";
    }
}
