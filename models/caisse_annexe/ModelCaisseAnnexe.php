<?php

class ModelCaisseAnnexe extends BaseModel
{
    protected string $table = 'paiements';
    protected string $primaryKey = 'id_paiement';
    protected ?string $statusField = 'statut_paiement';
    protected ?string $createdAtField = 'date_paiement';

    /**
     * Récupère la liste de tous les paiements de Frais Annexes avec filtres multicritères
     */
    public function getAll(
        ?string $anneeCode = null,
        ?string $niveauCode = null,
        ?string $classeCode = null,
        ?string $typeFiliere = null,
        ?string $modePaiement = null,
        ?string $dateDebut = null,
        ?string $dateFin = null
    ): array {
        $where = "WHERE (p.categorie_paiement = 'FRAIS_ANNEXES' OR p.tranche_code = 'FRAIS_ANNEXES' OR LOWER(p.type_paiement) LIKE '%annexe%') AND p.statut_paiement != 'annule'";
        $params = [];

        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $where .= " AND (p.annee_code = ? OR ins.annee_code = ?)";
            $params[] = $anneeCode;
            $params[] = $anneeCode;
        }

        if (!empty($niveauCode) && $niveauCode !== 'ALL') {
            $where .= " AND cl.niveau_code = ?";
            $params[] = $niveauCode;
        }

        if (!empty($classeCode) && $classeCode !== 'ALL') {
            $where .= " AND ins.classe_code = ?";
            $params[] = $classeCode;
        }

        if (!empty($typeFiliere) && $typeFiliere !== 'ALL') {
            $where .= " AND f.type_filiere = ?";
            $params[] = $typeFiliere;
        }

        if (!empty($modePaiement) && $modePaiement !== 'ALL') {
            $where .= " AND p.mode_paiement = ?";
            $params[] = $modePaiement;
        }

        if (!empty($dateDebut)) {
            $where .= " AND DATE(p.date_paiement) >= ?";
            $params[] = $dateDebut;
        }

        if (!empty($dateFin)) {
            $where .= " AND DATE(p.date_paiement) <= ?";
            $params[] = $dateFin;
        }

        $sql = "
            SELECT 
                p.*,
                e.nom_etudiant, 
                e.prenom_etudiant, 
                e.matricule_etudiant,
                e.telephone_etudiant,
                e.email_etudiant,
                e.photo_etudiant,
                ins.photo_inscription,
                ins.affectation_etat,
                TRIM(CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, ''))) as etudiant_nom,
                cl.libelle_classe, 
                cl.code_classe,
                cl.niveau_code,
                f.libelle_filiere,
                f.code_filiere,
                f.type_filiere,
                n.libelle_niveau,
                a.libelle_annee,
                u.nom_user as nom_caissier, 
                u.prenom_user as prenom_caissier,
                u.code_user as caissier_code
            FROM paiements p
            LEFT JOIN inscriptions ins ON ins.code_inscription = p.inscription_code
            LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
            LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
            LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
            LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
            LEFT JOIN annees a ON (a.code_annee = p.annee_code OR a.code_annee = ins.annee_code)
            LEFT JOIN users u ON u.code_user = p.user_code
            {$where}
            ORDER BY p.date_paiement DESC, p.id_paiement DESC
        ";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Calcule l'intégralité des statistiques financières et indicateurs KPI de la Caisse Annexe
     */
    public function getFinancialStats(
        ?string $anneeCode = null,
        ?string $niveauCode = null,
        ?string $classeCode = null,
        ?string $typeFiliere = null,
        ?string $dateDebut = null,
        ?string $dateFin = null
    ): array {
        $db = $this->getCon();
        $today = date('Y-m-d');
        $firstDayOfMonth = date('Y-m-01');

        // 1. Récupération des paiements de frais annexes filtrés
        $paiements = $this->getAll($anneeCode, $niveauCode, $classeCode, $typeFiliere, null, $dateDebut, $dateFin);

        $totalEncaisse = 0;
        $encaisseAujourdhui = 0;
        $encaisseMois = 0;
        $encaisseEspeces = 0;
        $encaisseMobileMoney = 0;
        $encaisseBanque = 0;
        $encaisseIndustrielle = 0;
        $encaisseTertiaire = 0;
        $totalRecus = count($paiements);

        foreach ($paiements as $p) {
            $mt = (float)($p['montant_paiement'] ?? 0);
            $totalEncaisse += $mt;

            $dateP = !empty($p['date_paiement']) ? substr($p['date_paiement'], 0, 10) : '';
            if ($dateP === $today) {
                $encaisseAujourdhui += $mt;
            }
            if ($dateP >= $firstDayOfMonth && $dateP <= $today) {
                $encaisseMois += $mt;
            }

            $mode = strtolower($p['mode_paiement'] ?? '');
            if (strpos($mode, 'espece') !== false || strpos($mode, 'cash') !== false) {
                $encaisseEspeces += $mt;
            } elseif (strpos($mode, 'mobile') !== false || strpos($mode, 'wave') !== false || strpos($mode, 'orange') !== false || strpos($mode, 'mtn') !== false || strpos($mode, 'moov') !== false) {
                $encaisseMobileMoney += $mt;
            } else {
                $encaisseBanque += $mt;
            }

            $tf = strtoupper($p['type_filiere'] ?? '');
            if ($tf === 'INDUSTRIELLE') {
                $encaisseIndustrielle += $mt;
            } elseif ($tf === 'TERTIAIRE') {
                $encaisseTertiaire += $mt;
            }
        }

        // 2. Évaluation dynamique du Budget Exigible (Frais Annexes Attendus) par rapport aux inscrits réels
        $whereInscr = "WHERE i.statut_inscription != 'annule'";
        $paramsInscr = [];

        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $whereInscr .= " AND i.annee_code = ?";
            $paramsInscr[] = $anneeCode;
        }
        if (!empty($niveauCode) && $niveauCode !== 'ALL') {
            $whereInscr .= " AND cl.niveau_code = ?";
            $paramsInscr[] = $niveauCode;
        }
        if (!empty($classeCode) && $classeCode !== 'ALL') {
            $whereInscr .= " AND i.classe_code = ?";
            $paramsInscr[] = $classeCode;
        }
        if (!empty($typeFiliere) && $typeFiliere !== 'ALL') {
            $whereInscr .= " AND f.type_filiere = ?";
            $paramsInscr[] = $typeFiliere;
        }

        $sqlInscr = "
            SELECT 
                i.code_inscription,
                i.annee_code,
                cl.code_classe,
                cl.niveau_code,
                f.type_filiere
            FROM inscriptions i
            LEFT JOIN classes cl ON cl.code_classe = i.classe_code
            LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
            {$whereInscr}
        ";
        $stmtInscr = $db->prepare($sqlInscr);
        $stmtInscr->execute($paramsInscr);
        $inscriptions = $stmtInscr->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalInscrits = count($inscriptions);

        // Récupérer la grille complète des tarifs frais annexes pour l'évaluation dynamique
        $sqlFA = "SELECT * FROM frais_annexes WHERE statut_frais_annexe = 'actif'";
        $paramsFA = [];
        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $sqlFA .= " AND annee_code = ?";
            $paramsFA[] = $anneeCode;
        }
        $stmtFA = $db->prepare($sqlFA);
        $stmtFA->execute($paramsFA);
        $tarifsFA = $stmtFA->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Récupérer les paiements groupés par étudiant pour calculer le statut soldé / non soldé
        $sqlGroupedPay = "
            SELECT 
                inscription_code, 
                SUM(montant_paiement) as total_paye_fa
            FROM paiements
            WHERE (categorie_paiement = 'FRAIS_ANNEXES' OR tranche_code = 'FRAIS_ANNEXES' OR LOWER(type_paiement) LIKE '%annexe%')
              AND statut_paiement != 'annule'
            GROUP BY inscription_code
        ";
        $groupedPay = $db->query($sqlGroupedPay)->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        $totalFraisAnnexesAttendus = 0;
        $elevesSoldes = 0;
        $elevesNonSoldes = 0;
        $elevesAcomptes = 0;

        foreach ($inscriptions as $ins) {
            $tFiliere = strtoupper($ins['type_filiere'] ?? 'TOUT');
            $nivCode = $ins['niveau_code'] ?? '';
            $insAnnee = $ins['annee_code'] ?? '';

            // Trouver le montant du frais annexe pour cette inscription
            $matchedTarif = 0;
            foreach ($tarifsFA as $tar) {
                $matchAnnee = (empty($tar['annee_code']) || $tar['annee_code'] === $insAnnee);
                $matchFiliere = ($tar['type_filiere'] === 'TOUT' || $tar['type_filiere'] === $tFiliere);
                $matchNiveau = (empty($tar['niveau_code']) || $tar['niveau_code'] === 'TOUT' || $tar['niveau_code'] === $nivCode);

                if ($matchAnnee && $matchFiliere && $matchNiveau) {
                    $matchedTarif = (float)$tar['montant_frais_annexe'];
                    break;
                }
            }

            $totalFraisAnnexesAttendus += $matchedTarif;

            $paid = (float)($groupedPay[$ins['code_inscription']] ?? 0);
            if ($matchedTarif > 0 && $paid >= $matchedTarif) {
                $elevesSoldes++;
            } elseif ($paid > 0) {
                $elevesAcomptes++;
                $elevesNonSoldes++;
            } else {
                $elevesNonSoldes++;
            }
        }

        $attenteFraisAnnexes = max(0, $totalFraisAnnexesAttendus - $totalEncaisse);
        $tauxRecouvrementFA = ($totalFraisAnnexesAttendus > 0) 
            ? min(100, round(($totalEncaisse / $totalFraisAnnexesAttendus) * 100, 1)) 
            : 0;

        // Libellé de l'exercice
        $anneeExerciceLibelle = 'Session Active';
        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $stmtLib = $db->prepare("SELECT libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtLib->execute([$anneeCode]);
            $anneeExerciceLibelle = $stmtLib->fetchColumn() ?: $anneeCode;
        }

        return [
            'total_encaisse' => $totalEncaisse,
            'encaisse_aujourdhui' => $encaisseAujourdhui,
            'encaisse_mois' => $encaisseMois,
            'encaisse_especes' => $encaisseEspeces,
            'encaisse_mobile_money' => $encaisseMobileMoney,
            'encaisse_banque' => $encaisseBanque,
            'encaisse_industrielle' => $encaisseIndustrielle,
            'encaisse_tertiaire' => $encaisseTertiaire,
            'total_frais_annexes_attendus' => $totalFraisAnnexesAttendus,
            'attente_frais_annexes' => $attenteFraisAnnexes,
            'taux_recouvrement_fa' => $tauxRecouvrementFA,
            'eleves_soldes' => $elevesSoldes,
            'eleves_non_soldes' => $elevesNonSoldes,
            'eleves_acomptes' => $elevesAcomptes,
            'total_inscrits' => $totalInscrits,
            'total_recus' => $totalRecus,
            'annee_exercice_libelle' => $anneeExerciceLibelle
        ];
    }
}
