<?php

class ModelPaiement extends BaseModel
{
    protected string $table = 'paiements';
    protected string $primaryKey = 'id_paiement';
    protected ?string $statusField = 'statut_paiement';
    protected ?string $createdAtField = 'date_paiement';

    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null, ?string $dateDebut = null, ?string $dateFin = null, ?string $canalPaiement = null): array
    {
        $where = "WHERE (p.categorie_paiement IS NULL OR p.categorie_paiement != 'FRAIS_ANNEXES') AND (p.tranche_code IS NULL OR p.tranche_code != 'FRAIS_ANNEXES')";
        $params = [];
        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $where .= " AND (p.annee_code = ? OR p.inscription_annee_code = ?)";
            $params[] = $anneeCode;
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode) && $niveauCode !== 'ALL') {
            $where .= " AND p.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode) && $classeCode !== 'ALL') {
            $where .= " AND p.classe_code = ?";
            $params[] = $classeCode;
        }
        if (!empty($canalPaiement) && $canalPaiement !== 'ALL') {
            $where .= " AND p.canal_paiement = ?";
            $params[] = $canalPaiement;
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
            SELECT p.*
            FROM v_paiements_complets p
            {$where}
            ORDER BY p.id_paiement DESC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById($id): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT p.*
            FROM v_paiements_complets p
            WHERE p.id_paiement = ? OR p.code_paiement = ?
            LIMIT 1
        ");
        $num = is_numeric($id) ? (int)$id : 0;
        $code = (string)$id;
        $stmt->execute([$num, $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    /**
     * Récupère la liste des paiements pour une inscription
     */
    public function getPaiementsByInscription(string $inscriptionCode, bool $excludeAnnule = true): array
    {
        $sql = "SELECT id_paiement, montant_paiement, date_paiement, tranche_code, categorie_paiement 
                FROM " . TABLES::PAIEMENTS . " 
                WHERE inscription_code = ?";
        if ($excludeAnnule) {
            $sql .= " AND statut_paiement != 'annule'";
        }
        $sql .= " ORDER BY date_paiement ASC";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute([$inscriptionCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Récupère les données d'inscription nécessaires pour un paiement
     */
    public function getInscriptionForPaiement(string $inscriptionCode): ?array
    {
        $sql = "SELECT i.*, c.filiere_code, c.niveau_code, c.libelle_classe, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
                FROM " . TABLES::INSCRIPTIONS . " i
                LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
                LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
                WHERE i.code_inscription = ? LIMIT 1";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute([$inscriptionCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Annule un paiement de scolarité
     */
    public function annulerPaiement(int $idPaiement, string $codePaiement, string $motif = ''): array
    {
        $db = $this->getCon();
        try {
            $stmt = $db->prepare("SELECT * FROM " . TABLES::PAIEMENTS . " WHERE id_paiement = ? OR code_paiement = ? LIMIT 1");
            $stmt->execute([$idPaiement, $codePaiement]);
            $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$paiement) {
                return ['success' => false, 'message' => "Paiement introuvable."];
            }

            if ($paiement['statut_paiement'] === 'annule') {
                return ['success' => false, 'message' => "Ce paiement est déjà annulé."];
            }

            $stmtUp = $db->prepare("UPDATE " . TABLES::PAIEMENTS . " SET statut_paiement = 'annule', motif_annulation = ?, updated_at_paiement = NOW() WHERE id_paiement = ?");
            $ok = $stmtUp->execute([$motif, $paiement['id_paiement']]);

            if ($ok) {
                return [
                    'success' => true,
                    'message' => "Le paiement a été annulé avec succès.",
                    'paiement' => $paiement
                ];
            }
            return ['success' => false, 'message' => "Erreur lors de l'annulation du paiement."];
        } catch (Exception $e) {
            error_log("ModelPaiement::annulerPaiement error: " . $e->getMessage());
            return ['success' => false, 'message' => "Erreur lors de l'annulation du paiement."];
        }
    }

    /**
     * Récupère la photo de l'étudiant / inscription rattachée
     */
    public function getPhotoInscription(string $inscriptionCode): ?array
    {
        $stmt = $this->getCon()->prepare("
            SELECT i.photo_inscription, e.photo_etudiant 
            FROM " . TABLES::INSCRIPTIONS . " i 
            LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code 
            WHERE i.code_inscription = ? LIMIT 1
        ");
        $stmt->execute([$inscriptionCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

/**
     * Encodage d'images en Base64
     */
    public function encodeImageBase64($paths): ?string
    {
        foreach ((array)$paths as $p) {
            if (empty($p)) continue;
            $clean = ltrim($p, '/');
            $candidates = [
                $p,
                __DIR__ . '/../../' . $clean,
                __DIR__ . '/../../public/' . $clean,
                'c:/wamp64/www/geicg/' . $clean,
                'c:/wamp64/www/geicg/public/' . $clean
            ];
            foreach ($candidates as $cand) {
                if (!empty($cand) && file_exists($cand) && is_file($cand)) {
                    $ext = strtolower(pathinfo($cand, PATHINFO_EXTENSION));
                    $mime = ($ext === 'png') ? 'png' : (($ext === 'webp') ? 'webp' : (($ext === 'gif') ? 'gif' : 'jpeg'));
                    $content = @file_get_contents($cand);
                    if ($content !== false && !empty($content)) {
                        return 'data:image/' . $mime . ';base64,' . base64_encode($content);
                    }
                }
            }
        }
        return null;
    }

    /**
     * Récupère une année par son code
     */
    public function getAnneeByCode(?string $code): ?array
    {
        if (empty($code)) return null;
        $stmt = $this->getCon()->prepare("SELECT code_annee, libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Dépendances pour la liste des paiements et comptabilité étudiants
     */
    public function getListDependencies(string $activeYear): array
    {
        $db = $this->getCon();
        $niveaux = $db->query("SELECT code_niveau, libelle_niveau FROM " . TABLES::NIVEAUX . " WHERE statut_niveau = 'actif' ORDER BY id_niveau ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $classes = $db->query("SELECT code_classe, libelle_classe, cycle_code, filiere_code, niveau_code FROM " . TABLES::CLASSES . " WHERE statut_classe = 'actif' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmtInscr = $db->prepare("
            SELECT 
                i.code_inscription,
                i.annee_code,
                i.photo_inscription,
                e.code_etudiant,
                e.matricule_etudiant,
                e.nom_etudiant,
                e.prenom_etudiant,
                e.photo_etudiant,
                c.libelle_classe,
                c.code_classe
            FROM " . TABLES::INSCRIPTIONS . " i
            JOIN " . TABLES::ETUDIANTS . " e ON i.etudiant_code = e.code_etudiant
            LEFT JOIN " . TABLES::CLASSES . " c ON i.classe_code = c.code_classe
            WHERE i.statut_inscription != 'annule'
              AND i.annee_code = ?
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
        ");
        $stmtInscr->execute([$activeYear]);
        $inscriptions = $stmtInscr->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $today = date('Y-m-d');
        $stmtSess = $db->prepare("SELECT * FROM " . TABLES::SESSIONS_CAISSE . " WHERE date_session = ? AND statut_session = 'ouverte' ORDER BY id_session DESC LIMIT 1");
        $stmtSess->execute([$today]);
        $activeSession = $stmtSess->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$activeSession) {
            $stmtLast = $db->prepare("SELECT * FROM " . TABLES::SESSIONS_CAISSE . " WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
            $stmtLast->execute([$today]);
            $activeSession = $stmtLast->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $isCaisseOuverte = ($activeSession && ($activeSession['statut_session'] ?? '') === 'ouverte');

        return [
            'niveaux' => $niveaux,
            'classes' => $classes,
            'inscriptions' => $inscriptions,
            'activeSession' => $activeSession,
            'isCaisseOuverte' => $isCaisseOuverte
        ];
    }

    /**
     * Statistiques financières globales dynamiques
     */
    public function computeFinancialStats(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null, ?string $dateDebut = null, ?string $dateFin = null, ?string $sessionAnneeCode = null): array
    {
        $db = $this->getCon();
        $targetAnnee = !empty($anneeCode) ? $anneeCode : ($sessionAnneeCode ?: '');

        $whereIns = ["i.statut_inscription != 'annule'"];
        $paramsIns = [];

        if (!empty($targetAnnee) && $targetAnnee !== 'ALL') {
            $whereIns[] = "i.annee_code = ?";
            $paramsIns[] = $targetAnnee;
        }
        if (!empty($niveauCode) && $niveauCode !== 'ALL') {
            $whereIns[] = "c.niveau_code = ?";
            $paramsIns[] = $niveauCode;
        }
        if (!empty($classeCode) && $classeCode !== 'ALL') {
            $whereIns[] = "i.classe_code = ?";
            $paramsIns[] = $classeCode;
        }

        $strWhereIns = implode(" AND ", $whereIns);

        $sqlIns = "
            SELECT 
                i.id_inscription,
                i.code_inscription,
                i.etudiant_code,
                i.annee_code,
                i.affectation_etat,
                c.filiere_code,
                c.niveau_code,
                COALESCE(f.type_filiere, 'TERTIAIRE') as type_filiere,
                COALESCE(
                    CASE 
                        WHEN i.montant_scolarite_inscription IS NOT NULL AND i.montant_scolarite_inscription > 0 THEN i.montant_scolarite_inscription
                        WHEN s.montant_scolarite IS NOT NULL AND s.montant_scolarite > 0 THEN s.montant_scolarite
                        ELSE 0
                    END, 0
                ) as montant_du
            FROM " . TABLES::INSCRIPTIONS . " i
            LEFT JOIN " . TABLES::CLASSES . " c ON i.classe_code = c.code_classe
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
            LEFT JOIN " . TABLES::SCOLARITES . " s ON (
                s.filiere_code = c.filiere_code 
                AND (s.niveau_code = c.niveau_code OR s.niveau_code IS NULL OR s.niveau_code = '')
                AND (s.annee_code = i.annee_code OR s.annee_code = '')
                AND (s.affectation_etat = i.affectation_etat OR s.affectation_etat IS NULL OR s.affectation_etat = '')
                AND s.statut_scolarite = 'actif'
            )
            WHERE {$strWhereIns}
        ";

        $stmtIns = $db->prepare($sqlIns);
        $stmtIns->execute($paramsIns);
        $inscriptions = $stmtIns->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalInscrits = count($inscriptions);
        $totalScolariteAttendue = 0.0;
        $inscrMap = [];

        foreach ($inscriptions as $ins) {
            $codeInscr = $ins['code_inscription'];
            $du = (float)$ins['montant_du'];
            $totalScolariteAttendue += $du;

            $inscrMap[$codeInscr] = [
                'code_inscription' => $codeInscr,
                'du' => $du,
                'affectation' => strtolower(trim($ins['affectation_etat'] ?? '')),
                'paye' => 0.0
            ];
        }

        $wherePay = [
            "p.statut_paiement != 'annule'",
            "(p.categorie_paiement IS NULL OR p.categorie_paiement != 'FRAIS_ANNEXES')",
            "(p.tranche_code IS NULL OR p.tranche_code != 'FRAIS_ANNEXES')"
        ];
        $paramsPay = [];

        if (!empty($targetAnnee) && $targetAnnee !== 'ALL') {
            $wherePay[] = "(p.annee_code = ? OR ins.annee_code = ?)";
            $paramsPay[] = $targetAnnee;
            $paramsPay[] = $targetAnnee;
        }
        if (!empty($niveauCode) && $niveauCode !== 'ALL') {
            $wherePay[] = "c.niveau_code = ?";
            $paramsPay[] = $niveauCode;
        }
        if (!empty($classeCode) && $classeCode !== 'ALL') {
            $wherePay[] = "ins.classe_code = ?";
            $paramsPay[] = $classeCode;
        }
        if (!empty($dateDebut)) {
            $wherePay[] = "DATE(p.date_paiement) >= ?";
            $paramsPay[] = $dateDebut;
        }
        if (!empty($dateFin)) {
            $wherePay[] = "DATE(p.date_paiement) <= ?";
            $paramsPay[] = $dateFin;
        }

        $strWherePay = implode(" AND ", $wherePay);

        $sqlPay = "
            SELECT 
                p.id_paiement,
                p.montant_paiement,
                p.date_paiement,
                p.mode_paiement,
                p.tranche_code,
                p.categorie_paiement,
                p.inscription_code,
                ins.affectation_etat
            FROM " . TABLES::PAIEMENTS . " p
            LEFT JOIN " . TABLES::INSCRIPTIONS . " ins ON ins.code_inscription = p.inscription_code
            LEFT JOIN " . TABLES::CLASSES . " c ON ins.classe_code = c.code_classe
            WHERE {$strWherePay}
        ";

        $stmtPay = $db->prepare($sqlPay);
        $stmtPay->execute($paramsPay);
        $paiements = $stmtPay->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalEncaisse = 0.0;
        $encaisseAujourdhui = 0.0;
        $encaisseMois = 0.0;
        $encaisseEspeces = 0.0;
        $encaisseMobile = 0.0;
        $encaisseBanque = 0.0;
        $encaisseAffectes = 0.0;
        $encaissePrives = 0.0;

        $today = date('Y-m-d');
        $thisMonth = date('Y-m');

        foreach ($paiements as $p) {
            $m = (float)$p['montant_paiement'];
            $totalEncaisse += $m;

            $dateP = !empty($p['date_paiement']) ? substr($p['date_paiement'], 0, 10) : '';
            $monthP = !empty($p['date_paiement']) ? substr($p['date_paiement'], 0, 7) : '';

            if ($dateP === $today) {
                $encaisseAujourdhui += $m;
            }
            if ($monthP === $thisMonth) {
                $encaisseMois += $m;
            }

            $mode = strtolower(trim($p['mode_paiement'] ?? 'especes'));
            if (in_array($mode, ['especes', 'espece', 'liquide', 'caisse'])) {
                $encaisseEspeces += $m;
            } elseif (in_array($mode, ['wave', 'om', 'orange', 'orange_money', 'mtn', 'moov', 'mobile_money', 'mobile'])) {
                $encaisseMobile += $m;
            } else {
                $encaisseBanque += $m;
            }

            $codeIns = $p['inscription_code'];
            if (isset($inscrMap[$codeIns])) {
                $inscrMap[$codeIns]['paye'] += $m;
            }

            $rawAff = strtolower(trim($p['affectation_etat'] ?? ''));
            if ($rawAff === 'oui' || $rawAff === 'affecte') {
                $encaisseAffectes += $m;
            } else {
                $encaissePrives += $m;
            }
        }

        $elevesSoldes = 0;
        $elevesAcomptes = 0;
        $elevesNonPayeurs = 0;
        $attentePostInscription = 0.0;

        foreach ($inscrMap as $item) {
            $du = $item['du'];
            $paye = $item['paye'];
            $reste = max(0, $du - $paye);

            if ($paye >= $du && $du > 0) {
                $elevesSoldes++;
            } elseif ($paye > 0) {
                $elevesAcomptes++;
            } else {
                $elevesNonPayeurs++;
            }

            $attentePostInscription += $reste;
        }

        $montantEnAttente = max(0, $totalScolariteAttendue - $totalEncaisse);
        $tauxRecouvrement = ($totalScolariteAttendue > 0) ? round(($totalEncaisse / $totalScolariteAttendue) * 100, 1) : 0;

        $targetExerciceAnnee = (!empty($targetAnnee) && $targetAnnee !== 'ALL') ? $targetAnnee : ($sessionAnneeCode ?: '');

        $stmtExercice = $db->prepare("
            SELECT COALESCE(SUM(
                CASE 
                    WHEN i.montant_scolarite_inscription IS NOT NULL AND i.montant_scolarite_inscription > 0 THEN i.montant_scolarite_inscription
                    WHEN s.montant_scolarite IS NOT NULL AND s.montant_scolarite > 0 THEN s.montant_scolarite
                    ELSE 0
                END
            ), 0) as total_exercice
            FROM " . TABLES::INSCRIPTIONS . " i
            LEFT JOIN " . TABLES::CLASSES . " c ON i.classe_code = c.code_classe
            LEFT JOIN " . TABLES::SCOLARITES . " s ON (
                s.filiere_code = c.filiere_code 
                AND (s.niveau_code = c.niveau_code OR s.niveau_code IS NULL OR s.niveau_code = '')
                AND (s.annee_code = i.annee_code OR s.annee_code = '')
                AND (s.affectation_etat = i.affectation_etat OR s.affectation_etat IS NULL OR s.affectation_etat = '')
                AND s.statut_scolarite = 'actif'
            )
            WHERE i.annee_code = ? AND i.statut_inscription != 'annule'
        ");
        $stmtExercice->execute([$targetExerciceAnnee]);
        $totalExerciceSession = (float)$stmtExercice->fetchColumn();

        $stmtLib = $db->prepare("SELECT libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
        $stmtLib->execute([$targetExerciceAnnee]);
        $anneeExerciceLibelle = $stmtLib->fetchColumn() ?: 'En session';

        return [
            'total_inscrits' => $totalInscrits,
            'total_scolarite_attendue' => $totalScolariteAttendue,
            'total_exercice_session' => $totalExerciceSession,
            'annee_exercice_libelle' => $anneeExerciceLibelle,
            'total_encaisse' => $totalEncaisse,
            'montant_en_attente' => $montantEnAttente,
            'taux_recouvrement' => $tauxRecouvrement,
            'encaisse_aujourdhui' => $encaisseAujourdhui,
            'encaisse_mois' => $encaisseMois,
            'total_operations' => count($paiements),
            'encaisse_especes' => $encaisseEspeces,
            'encaisse_mobile' => $encaisseMobile,
            'encaisse_banque' => $encaisseBanque,
            'eleves_soldes' => $elevesSoldes,
            'eleves_acomptes' => $elevesAcomptes,
            'eleves_non_payeurs' => $elevesNonPayeurs,
            'attente_post_inscription' => $attentePostInscription,
            'encaisse_affectes' => $encaisseAffectes,
            'encaisse_prives' => $encaissePrives
        ];
    }

    /**
     * Données de synthèse comptable des étudiants (API JSON)
     */
    public function getComptabiliteEtudiantsData(string $anneeCode, string $niveauCode, string $classeCode, string $regime, string $statutPaiement, ?object $validator = null): array
    {
        $db = $this->getCon();

        $where = "WHERE i.statut_inscription != 'annule' AND i.annee_code = ?";
        $params = [$anneeCode];

        if ($niveauCode !== 'ALL' && !empty($niveauCode)) {
            $where .= " AND i.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if ($classeCode !== 'ALL' && !empty($classeCode)) {
            $where .= " AND i.classe_code = ?";
            $params[] = $classeCode;
        }
        if ($regime !== 'ALL' && !empty($regime)) {
            if ($regime === 'affecte') {
                $where .= " AND (i.affectation_etat = 'affecte' OR i.affectation_etat = 'oui')";
            } else {
                $where .= " AND (i.affectation_etat = 'non_affecte' OR i.affectation_etat = 'non')";
            }
        }

        $sql = "
            SELECT i.*
            FROM v_inscriptions_completes i
            {$where} AND i.code_etudiant IS NOT NULL
            ORDER BY i.nom_etudiant ASC, i.prenom_etudiant ASC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmtPay = $db->query("SELECT inscription_code, total_paye_scolarite AS total_paye FROM v_bilan_paiements_inscriptions");
        $paymentsGrouped = [];
        if ($stmtPay) {
            foreach ($stmtPay->fetchAll(PDO::FETCH_ASSOC) as $rowP) {
                $paymentsGrouped[$rowP['inscription_code']] = (float)$rowP['total_paye'];
            }
        }

        $stmtScoAll = $db->prepare("SELECT filiere_code, niveau_code, affectation_etat, montant_scolarite FROM " . TABLES::SCOLARITES . " WHERE statut_scolarite = 'actif' AND (annee_code = ? OR annee_code IS NULL OR annee_code = '')");
        $stmtScoAll->execute([$anneeCode]);
        $scolariteGrid = $stmtScoAll->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $data = [];
        $kpiCount = 0;
        $kpiScolariteDueTotal = 0;
        $kpiTotalAttendu = 0;
        $kpiTotalEncaisse = 0;
        $kpiResteARecouvrer = 0;

        foreach ($inscriptions as $ins) {
            $codeIns = $ins['code_inscription'];
            $filiereCode = $ins['filiere_code'] ?? '';
            $nCode = $ins['niveau_code'] ?? '';
            $affRaw = $ins['affectation_etat'] ?? 'non';
            $isAffecte = ($affRaw === 'affecte' || $affRaw === 'oui');
            $targetAff = $isAffecte ? 'affecte' : 'non_affecte';

            $scolariteDue = 0;
            foreach ($scolariteGrid as $sg) {
                $sgAff = ($sg['affectation_etat'] === 'affecte' || $sg['affectation_etat'] === 'oui') ? 'affecte' : 'non_affecte';
                if ($sg['filiere_code'] === $filiereCode && $sg['niveau_code'] === $nCode && $sgAff === $targetAff) {
                    $scolariteDue = (float)$sg['montant_scolarite'];
                    break;
                }
            }
            if ($scolariteDue <= 0) {
                $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
            }

            $totalAttendu = $scolariteDue;
            $totalEncaisse = $paymentsGrouped[$codeIns] ?? 0;
            $soldeRestant = max(0, $totalAttendu - $totalEncaisse);
            $taux = ($totalAttendu > 0) ? min(100, round(($totalEncaisse / $totalAttendu) * 100, 1)) : 0;

            $statusCode = 'non_paye';
            if (($totalAttendu > 0 && $totalEncaisse >= $totalAttendu) || ($soldeRestant <= 0 && $totalAttendu > 0)) {
                $statusCode = 'solde';
            } elseif ($totalEncaisse > 0) {
                $statusCode = 'partiel';
            }

            if ($statutPaiement !== 'ALL' && $statutPaiement !== $statusCode) {
                continue;
            }

            $kpiCount++;
            $kpiScolariteDueTotal += $scolariteDue;
            $kpiTotalAttendu += $totalAttendu;
            $kpiTotalEncaisse += $totalEncaisse;
            $kpiResteARecouvrer += $soldeRestant;

            $nomStr = trim($ins['nom_etudiant'] ?? '');
            $prenomStr = trim($ins['prenom_etudiant'] ?? '');
            $iNom = !empty($nomStr) ? mb_substr($nomStr, 0, 1, 'UTF-8') : '';
            $iPrenom = !empty($prenomStr) ? mb_substr($prenomStr, 0, 1, 'UTF-8') : '';
            $initiales = strtoupper($iNom . $iPrenom);
            if (empty($initiales)) {
                $initiales = 'E';
            }

            $photoRel = !empty($ins['photo_etudiant']) ? trim($ins['photo_etudiant']) : (!empty($ins['photo_inscription']) ? trim($ins['photo_inscription']) : '');
            $hasPhoto = false;
            $photoUrl = '';

            if (!empty($photoRel)) {
                $cleanRel = ltrim($photoRel, '/');
                $fullPathDisk = __DIR__ . '/../../' . $cleanRel;
                if (file_exists($fullPathDisk) && is_file($fullPathDisk)) {
                    $hasPhoto = true;
                    $photoUrl = RACINE . $cleanRel;
                } else {
                    $fullPathPublic = __DIR__ . '/../../public/' . $cleanRel;
                    if (file_exists($fullPathPublic) && is_file($fullPathPublic)) {
                        $hasPhoto = true;
                        $photoUrl = RACINE . 'public/' . $cleanRel;
                    }
                }
            }

            $idInscr = (int)($ins['id_inscription'] ?? 0);
            $encInscrId = ($validator && method_exists($validator, 'crypter')) ? ($idInscr > 0 ? $validator->crypter($idInscr) : $validator->crypter($codeIns)) : (string)$idInscr;
            $encInscrCode = ($validator && method_exists($validator, 'crypter')) ? $validator->crypter($codeIns) : $codeIns;
            $encEtuCode = (!empty($ins['code_etudiant']) && $validator && method_exists($validator, 'crypter')) ? $validator->crypter($ins['code_etudiant']) : ($ins['code_etudiant'] ?? '');

            $data[] = [
                'id_inscription' => $idInscr,
                'code_inscription' => $codeIns,
                'encrypted_inscription_id' => $encInscrId,
                'encrypted_inscription_code' => $encInscrCode,
                'code_etudiant' => $ins['code_etudiant'] ?? '',
                'encrypted_etudiant_code' => $encEtuCode,
                'matricule' => $ins['matricule_etudiant'] ?? 'N/A',
                'nom' => strtoupper($ins['nom_etudiant'] ?? ''),
                'prenom' => ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
                'nom_complet' => strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
                'has_photo' => $hasPhoto,
                'initiales' => $initiales,
                'photo' => $photoUrl,
                'telephone' => $ins['telephone_etudiant'] ?? 'N/A',
                'classe' => $ins['libelle_classe'] ?? 'N/A',
                'niveau' => $ins['libelle_niveau'] ?? 'N/A',
                'regime' => $isAffecte ? 'Affecté' : 'Privé',
                'regime_code' => $targetAff,
                'scolarite_due' => $scolariteDue,
                'total_attendu' => $totalAttendu,
                'total_encaisse' => $totalEncaisse,
                'solde_restant' => $soldeRestant,
                'taux_reglement' => $taux,
                'statut_code' => $statusCode,
                'statut_libelle' => ($statusCode === 'solde') ? 'Soldé' : (($statusCode === 'partiel') ? 'Acompte Payé' : 'Non Réglé'),
                'badge_class' => ($statusCode === 'solde') ? 'badge-success' : (($statusCode === 'partiel') ? 'badge-warning' : 'badge-danger')
            ];
        }

        $kpiTauxGlobal = ($kpiTotalAttendu > 0) ? min(100, round(($kpiTotalEncaisse / $kpiTotalAttendu) * 100, 1)) : 0;

        return [
            'status' => 1,
            'success' => true,
            'data' => $data,
            'kpis' => [
                'total_etudiants' => $kpiCount,
                'total_scolarites' => $kpiScolariteDueTotal,
                'total_attendu' => $kpiTotalAttendu,
                'total_encaisse' => $kpiTotalEncaisse,
                'reste_a_recouvrer' => $kpiResteARecouvrer,
                'taux_recouvrement' => $kpiTauxGlobal
            ]
        ];
    }

    /**
     * Fiche financière complète d'un étudiant pour le modal d'encaissement
     */
    public function getStudentFinancialSummaryData(string $lookupKey, string $activeYear, ?object $validator = null): array
    {
        $db = $this->getCon();
        if (empty($lookupKey)) {
            return ['status' => 0, 'message' => 'Code inscription ou matricule manquant'];
        }

        $stmt = $db->prepare("
            SELECT i.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.code_etudiant, e.photo_etudiant, e.telephone_etudiant, e.email_etudiant,
                   c.libelle_classe, c.filiere_code, c.niveau_code,
                   f.libelle_filiere, f.type_filiere, n.libelle_niveau, a.libelle_annee
            FROM " . TABLES::INSCRIPTIONS . " i
            LEFT JOIN " . TABLES::ETUDIANTS . " e ON i.etudiant_code = e.code_etudiant
            LEFT JOIN " . TABLES::CLASSES . " c ON i.classe_code = c.code_classe
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
            LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
            WHERE i.code_inscription = ? OR i.id_inscription = ? OR e.matricule_etudiant = ? OR e.code_etudiant = ?
            ORDER BY (CASE WHEN i.annee_code = ? THEN 1 ELSE 2 END), i.id_inscription DESC
            LIMIT 1
        ");
        $stmt->execute([
            $lookupKey, 
            is_numeric($lookupKey) ? (int)$lookupKey : 0, 
            $lookupKey, 
            $lookupKey, 
            $activeYear
        ]);
        $ins = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ins) {
            $stmtEtu = $db->prepare("
                SELECT i.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.code_etudiant, e.photo_etudiant, e.telephone_etudiant, e.email_etudiant,
                       c.libelle_classe, c.filiere_code, c.niveau_code,
                       f.libelle_filiere, f.type_filiere, n.libelle_niveau, a.libelle_annee
                FROM " . TABLES::ETUDIANTS . " e
                LEFT JOIN " . TABLES::INSCRIPTIONS . " i ON i.etudiant_code = e.code_etudiant AND (i.annee_code = ? OR ? = '') AND (i.statut_inscription != 'annule')
                LEFT JOIN " . TABLES::CLASSES . " c ON i.classe_code = c.code_classe
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
                WHERE e.code_etudiant = ? OR e.matricule_etudiant = ?
                ORDER BY (CASE WHEN i.annee_code = ? THEN 1 ELSE 2 END), i.id_inscription DESC
                LIMIT 1
            ");
            $stmtEtu->execute([$activeYear, $activeYear, $lookupKey, $lookupKey, $activeYear]);
            $ins = $stmtEtu->fetch(PDO::FETCH_ASSOC);
        }

        if (!$ins) {
            return ['status' => 0, 'message' => 'Aucun dossier d\'inscription trouvé pour cet étudiant.'];
        }

        $codeInscription = $ins['code_inscription'] ?? '';
        $filiereCode = $ins['filiere_code'] ?? '';
        $niveauCode = $ins['niveau_code'] ?? '';
        $anneeCode = !empty($ins['annee_code']) ? $ins['annee_code'] : $activeYear;
        
        $rawAff = strtolower(trim($ins['affectation_etat'] ?? ''));
        $isAffecte = ($rawAff === 'oui' || $rawAff === 'affecte' || $rawAff === '1');
        $affEtat = $isAffecte ? 'affecte' : 'non_affecte';

        $stmtSco = $db->prepare("
            SELECT * FROM " . TABLES::SCOLARITES . " 
            WHERE filiere_code = ? 
              AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
              AND (annee_code = ? OR annee_code = ? OR ? = '')
              AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
              AND statut_scolarite = 'actif'
            ORDER BY (CASE WHEN annee_code = ? THEN 1 WHEN annee_code = ? THEN 2 ELSE 3 END),
                     (CASE WHEN affectation_etat = ? THEN 1 ELSE 2 END),
                     id_scolarite DESC
            LIMIT 1
        ");
        $stmtSco->execute([$filiereCode, $niveauCode, $activeYear, $anneeCode, $activeYear, $affEtat, $activeYear, $anneeCode, $affEtat]);
        $scoGrid = $stmtSco->fetch(PDO::FETCH_ASSOC);

        $codeScolarite = $scoGrid['code_scolarite'] ?? '';
        if ($scoGrid && (float)$scoGrid['montant_scolarite'] > 0) {
            $scolariteDue = (float)$scoGrid['montant_scolarite'];
        } else {
            $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
        }

        $stmtPay = $db->prepare("
            SELECT * FROM " . TABLES::PAIEMENTS . " 
            WHERE inscription_code = ? 
              AND statut_paiement != 'annule'
              AND (categorie_paiement IS NULL OR categorie_paiement != 'FRAIS_ANNEXES')
              AND (tranche_code IS NULL OR tranche_code != 'FRAIS_ANNEXES')
            ORDER BY date_paiement ASC, id_paiement ASC
        ");
        $stmtPay->execute([$codeInscription]);
        $allPayments = $stmtPay->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalPaye = 0;
        foreach ($allPayments as $p) {
            $totalPaye += (float)$p['montant_paiement'];
        }

        $hasPaidBefore = (count($allPayments) > 0 || $totalPaye > 0);
        $soldeRestant = max(0, $scolariteDue - $totalPaye);

        $statutReglement = 'Non Réglé';
        $badgeClass = 'badge-danger';
        if ($totalPaye >= $scolariteDue && $scolariteDue > 0) {
            $statutReglement = 'Scolarité Totalement Soldée';
            $badgeClass = 'badge-success';
        } elseif ($totalPaye > 0) {
            $statutReglement = 'Acompte Payé / Solde Débiteur';
            $badgeClass = 'badge-warning';
        }

        if (!empty($codeScolarite)) {
            $stmtTr = $db->prepare("
                SELECT t.*, s.montant_scolarite as scolarite_globale
                FROM " . TABLES::TRANCHES_SCOLARITE . " t
                LEFT JOIN " . TABLES::SCOLARITES . " s ON s.code_scolarite = t.scolarite_code
                WHERE t.statut_tranche = 'actif'
                  AND (
                    t.scolarite_code = ?
                    OR (
                      (t.scolarite_code = '' OR t.scolarite_code IS NULL)
                      AND t.filiere_code = ?
                      AND (t.niveau_code = ? OR t.niveau_code = '' OR t.niveau_code IS NULL)
                      AND (t.annee_code = ? OR ? = '')
                    )
                  )
                ORDER BY t.created_at_tranche ASC, t.id_tranche ASC
            ");
            $stmtTr->execute([$codeScolarite, $filiereCode, $niveauCode, $anneeCode, $anneeCode]);
        } else {
            $stmtTr = $db->prepare("
                SELECT t.*, s.montant_scolarite as scolarite_globale
                FROM " . TABLES::TRANCHES_SCOLARITE . " t
                LEFT JOIN " . TABLES::SCOLARITES . " s ON s.code_scolarite = t.scolarite_code
                WHERE t.statut_tranche = 'actif'
                  AND t.filiere_code = ?
                  AND (t.niveau_code = ? OR t.niveau_code = '' OR t.niveau_code IS NULL)
                  AND (t.annee_code = ? OR ? = '')
                ORDER BY t.created_at_tranche ASC, t.id_tranche ASC
            ");
            $stmtTr->execute([$filiereCode, $niveauCode, $anneeCode, $anneeCode]);
        }
        $dbTranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $tranchesList = [];
        $unassignedPayments = 0;
        $paymentsByTranche = [];

        foreach ($allPayments as $p) {
            $tCode = $p['tranche_code'] ?? '';
            if (!empty($tCode)) {
                $paymentsByTranche[$tCode] = ($paymentsByTranche[$tCode] ?? 0) + (float)$p['montant_paiement'];
            } else {
                $unassignedPayments += (float)$p['montant_paiement'];
            }
        }

        $suggestedTrancheCode = null;

        if (!empty($dbTranches)) {
            foreach ($dbTranches as $tr) {
                $tCode = $tr['code_tranche'];
                $montantTranche = (float)$tr['montant_tranche'];
                $dejaPaye = $paymentsByTranche[$tCode] ?? 0;

                if ($unassignedPayments > 0 && $dejaPaye < $montantTranche) {
                    $needed = $montantTranche - $dejaPaye;
                    $allocated = min($unassignedPayments, $needed);
                    $dejaPaye += $allocated;
                    $unassignedPayments -= $allocated;
                }

                $resteAPayer = max(0, $montantTranche - $dejaPaye);
                $isSoldee = ($resteAPayer <= 0);
                $isPartiel = ($dejaPaye > 0 && $resteAPayer > 0);

                if ($isSoldee) {
                    $statutCode = 'soldee';
                    $statutLibelle = 'Payée (Soldée)';
                    $badgeBg = '#DCFCE7';
                    $badgeColor = '#15803D';
                } elseif ($isPartiel) {
                    $statutCode = 'partiel';
                    $statutLibelle = 'Partielle (Reste : ' . number_format($resteAPayer, 0, ',', ' ') . ' F)';
                    $badgeBg = '#FEF3C7';
                    $badgeColor = '#B45309';
                } else {
                    $statutCode = 'a_payer';
                    $statutLibelle = 'À Payer';
                    $badgeBg = '#EFF6FF';
                    $badgeColor = '#1E3A5F';
                }

                if (!$isSoldee && $suggestedTrancheCode === null) {
                    $suggestedTrancheCode = $tCode;
                }

                $tranchesList[] = [
                    'id_tranche' => $tr['id_tranche'],
                    'code_tranche' => $tCode,
                    'libelle' => $tr['libelle_tranche'],
                    'libelle_tranche' => $tr['libelle_tranche'],
                    'montant' => $montantTranche,
                    'montant_tranche' => $montantTranche,
                    'montant_tranche_fmt' => number_format($montantTranche, 0, ',', ' ') . ' FCFA',
                    'date_limite' => $tr['date_limite'],
                    'date_limite_fmt' => !empty($tr['date_limite']) ? date('d/m/Y', strtotime($tr['date_limite'])) : 'Non définie',
                    'deja_paye' => $dejaPaye,
                    'deja_paye_fmt' => number_format($dejaPaye, 0, ',', ' ') . ' FCFA',
                    'reste' => $resteAPayer,
                    'reste_a_payer' => $resteAPayer,
                    'reste_a_payer_fmt' => number_format($resteAPayer, 0, ',', ' ') . ' FCFA',
                    'is_soldee' => $isSoldee,
                    'statut' => $statutLibelle,
                    'statut_code' => $statutCode,
                    'statut_libelle' => $statutLibelle,
                    'badge' => ($statutCode === 'soldee') ? 'badge-success' : (($statutCode === 'partiel') ? 'badge-warning' : 'badge-info'),
                    'badge_bg' => $badgeBg,
                    'badge_color' => $badgeColor
                ];
            }
        } else {
            $isSoldee = ($soldeRestant <= 0);
            $tranchesList[] = [
                'id_tranche' => 0,
                'code_tranche' => 'SCOLARITE_GLOBALE',
                'libelle' => 'Scolarité Complète',
                'libelle_tranche' => 'Scolarité Complète',
                'montant' => $scolariteDue,
                'montant_tranche' => $scolariteDue,
                'montant_tranche_fmt' => number_format($scolariteDue, 0, ',', ' ') . ' FCFA',
                'date_limite' => '',
                'date_limite_fmt' => 'Annuelle',
                'deja_paye' => $totalPaye,
                'deja_paye_fmt' => number_format($totalPaye, 0, ',', ' ') . ' FCFA',
                'reste' => $soldeRestant,
                'reste_a_payer' => $soldeRestant,
                'reste_a_payer_fmt' => number_format($soldeRestant, 0, ',', ' ') . ' FCFA',
                'is_soldee' => $isSoldee,
                'statut' => $isSoldee ? 'Payée (Soldée)' : 'À Payer',
                'statut_code' => $isSoldee ? 'soldee' : ($totalPaye > 0 ? 'partiel' : 'a_payer'),
                'statut_libelle' => $isSoldee ? 'Payée (Soldée)' : 'À Payer',
                'badge' => $isSoldee ? 'badge-success' : 'badge-info',
                'badge_bg' => $isSoldee ? '#DCFCE7' : '#EFF6FF',
                'badge_color' => $isSoldee ? '#15803D' : '#1E3A5F'
            ];
            $suggestedTrancheCode = 'SCOLARITE_GLOBALE';
        }

        $nomComplet = trim(($ins['nom_etudiant'] ?? '') . ' ' . ($ins['prenom_etudiant'] ?? ''));
        $tauxRecouvrement = ($scolariteDue > 0) ? min(100, round(($totalPaye / $scolariteDue) * 100, 1)) : 0;
        $affLabel = $isAffecte ? 'Étudiant Affecté (État)' : 'Non Affecté (Privé)';

        $historiquePaiements = [];
        foreach ($allPayments as $p) {
            $pId = (int)$p['id_paiement'];
            $pIdCrypte = ($validator && method_exists($validator, 'crypter')) ? $validator->crypter($pId) : (string)$pId;
            $modeP = strtolower($p['mode_paiement'] ?? 'espece');
            $modeLabels = [
                'espece' => 'Espèces',
                'mobile_money' => 'Mobile Money',
                'cheque' => 'Chèque',
                'virement' => 'Virement'
            ];
            $historiquePaiements[] = [
                'id_paiement' => $pId,
                'id_crypte' => $pIdCrypte,
                'encrypted_id' => $pIdCrypte,
                'code_paiement' => $p['code_paiement'] ?? ('RECU-'.$pId),
                'date_paiement' => $p['date_paiement'],
                'date_paiement_fmt' => !empty($p['date_paiement']) ? date('d/m/Y', strtotime($p['date_paiement'])) : '-',
                'montant_paiement' => (float)$p['montant_paiement'],
                'montant_paiement_fmt' => number_format((float)$p['montant_paiement'], 0, ',', ' ') . ' FCFA',
                'mode_paiement' => $modeP,
                'mode_paiement_fmt' => $modeLabels[$modeP] ?? ucfirst($modeP),
                'type_paiement' => $p['type_paiement'] ?? 'Règlement Scolarité',
                'type_transaction' => $p['type_paiement'] ?? 'Versement',
                'reference_paiement' => !empty($p['reference_paiement']) ? $p['reference_paiement'] : '-',
                'tranche_code' => !empty($p['tranche_code']) ? $p['tranche_code'] : '-'
            ];
        }

        $photoPath = !empty($ins['photo_inscription']) ? trim($ins['photo_inscription']) : (!empty($ins['photo_etudiant']) ? trim($ins['photo_etudiant']) : '');
        $photoUrl = !empty($photoPath) ? RACINE . ltrim($photoPath, '/') : '';

        return [
            'status' => 1,
            'success' => true,
            'data' => [
                'code_inscription' => $codeInscription,
                'code_etudiant' => $ins['code_etudiant'] ?? '',
                'matricule' => $ins['matricule_etudiant'] ?? '-',
                'nom_etudiant' => $ins['nom_etudiant'] ?? '',
                'prenom_etudiant' => $ins['prenom_etudiant'] ?? '',
                'etudiant_nom' => $nomComplet,
                'nom_complet' => $nomComplet,
                'photo_etudiant' => $ins['photo_etudiant'] ?? '',
                'photo_inscription' => $ins['photo_inscription'] ?? '',
                'photo' => $photoPath,
                'photo_url' => $photoUrl,
                'telephone_etudiant' => !empty($ins['telephone_etudiant']) ? $ins['telephone_etudiant'] : '-',
                'email_etudiant' => !empty($ins['email_etudiant']) ? $ins['email_etudiant'] : '-',
                'affectation_etat' => $affEtat,
                'affectation_label' => $affLabel,
                'classe' => $ins['libelle_classe'] ?? 'Classe non définie',
                'filiere' => $ins['libelle_filiere'] ?? '-',
                'niveau' => $ins['libelle_niveau'] ?? '-',
                'annee' => $ins['libelle_annee'] ?? '-',
                'scolarite_due' => $scolariteDue,
                'scolarite_due_fmt' => number_format($scolariteDue, 0, ',', ' ') . ' FCFA',
                'total_paye' => $totalPaye,
                'total_paye_fmt' => number_format($totalPaye, 0, ',', ' ') . ' FCFA',
                'solde_restant' => $soldeRestant,
                'solde_restant_fmt' => number_format($soldeRestant, 0, ',', ' ') . ' FCFA',
                'has_paid_before' => $hasPaidBefore,
                'taux_recouvrement' => $tauxRecouvrement,
                'statut_reglement' => $statutReglement,
                'badge_class' => $badgeClass,
                'tranches' => $tranchesList,
                'suggested_tranche_code' => $suggestedTrancheCode,
                'historique_paiements' => $historiquePaiements,
                'all_payments' => $historiquePaiements
            ]
        ];
    }

    /**
     * Prochaine tranche impayée d'un étudiant (contrôle chronologique)
     */
    public function getNextUnpaidTranche(string $inscriptionCode, ?string $defaultAnneeCode = null): ?array
    {
        $db = $this->getCon();
        $ins = $this->getInscriptionForPaiement($inscriptionCode);
        if (!$ins) return null;

        $filiereCode = $ins['filiere_code'] ?? '';
        $niveauCode = $ins['niveau_code'] ?? '';
        $anneeCode = !empty($ins['annee_code']) ? $ins['annee_code'] : ($defaultAnneeCode ?: '');

        $rawAff = strtolower(trim($ins['affectation_etat'] ?? ''));
        $isAffecte = ($rawAff === 'oui' || $rawAff === 'affecte' || $rawAff === '1');
        $affEtat = $isAffecte ? 'affecte' : 'non_affecte';

        $stmtSco = $db->prepare("
            SELECT code_scolarite FROM " . TABLES::SCOLARITES . " 
            WHERE filiere_code = ? 
              AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
              AND (annee_code = ? OR ? = '')
              AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
              AND statut_scolarite = 'actif'
            ORDER BY id_scolarite DESC LIMIT 1
        ");
        $stmtSco->execute([$filiereCode, $niveauCode, $anneeCode, $anneeCode, $affEtat]);
        $scoGrid = $stmtSco->fetch(PDO::FETCH_ASSOC);
        $codeScolarite = $scoGrid['code_scolarite'] ?? '';

        if (!empty($codeScolarite)) {
            $stmtTr = $db->prepare("
                SELECT t.*
                FROM " . TABLES::TRANCHES_SCOLARITE . " t
                LEFT JOIN " . TABLES::SCOLARITES . " s ON s.code_scolarite = t.scolarite_code
                WHERE t.statut_tranche = 'actif'
                  AND (
                    t.scolarite_code = ?
                    OR (
                      (t.scolarite_code = '' OR t.scolarite_code IS NULL)
                      AND t.filiere_code = ?
                      AND (t.niveau_code = ? OR t.niveau_code = '' OR t.niveau_code IS NULL)
                      AND (t.annee_code = ? OR ? = '')
                    )
                  )
                ORDER BY t.created_at_tranche ASC, t.id_tranche ASC
            ");
            $stmtTr->execute([$codeScolarite, $filiereCode, $niveauCode, $anneeCode, $anneeCode]);
        } else {
            $stmtTr = $db->prepare("
                SELECT t.*
                FROM " . TABLES::TRANCHES_SCOLARITE . " t
                LEFT JOIN " . TABLES::SCOLARITES . " s ON s.code_scolarite = t.scolarite_code
                WHERE t.statut_tranche = 'actif'
                  AND t.filiere_code = ?
                  AND (t.niveau_code = ? OR t.niveau_code = '' OR t.niveau_code IS NULL)
                  AND (t.annee_code = ? OR ? = '')
                ORDER BY t.created_at_tranche ASC, t.id_tranche ASC
            ");
            $stmtTr->execute([$filiereCode, $niveauCode, $anneeCode, $anneeCode]);
        }
        $dbTranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($dbTranches)) return null;

        $stmtPay = $db->prepare("SELECT * FROM " . TABLES::PAIEMENTS . " WHERE inscription_code = ? AND statut_paiement != 'annule'");
        $stmtPay->execute([$inscriptionCode]);
        $allPayments = $stmtPay->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $paymentsByTranche = [];
        $unassignedPayments = 0;
        foreach ($allPayments as $p) {
            $tCode = $p['tranche_code'] ?? '';
            if (!empty($tCode)) {
                $paymentsByTranche[$tCode] = ($paymentsByTranche[$tCode] ?? 0) + (float)$p['montant_paiement'];
            } else {
                $unassignedPayments += (float)$p['montant_paiement'];
            }
        }

        foreach ($dbTranches as $tr) {
            $tCode = $tr['code_tranche'];
            $montantTranche = (float)$tr['montant_tranche'];
            $dejaPaye = $paymentsByTranche[$tCode] ?? 0;

            if ($unassignedPayments > 0 && $dejaPaye < $montantTranche) {
                $needed = $montantTranche - $dejaPaye;
                $allocated = min($unassignedPayments, $needed);
                $dejaPaye += $allocated;
                $unassignedPayments -= $allocated;
            }

            $resteAPayer = max(0, $montantTranche - $dejaPaye);
            if ($resteAPayer > 0) {
                return [
                    'code_tranche' => $tCode,
                    'libelle_tranche' => $tr['libelle_tranche'],
                    'montant_tranche' => $montantTranche,
                    'deja_paye' => $dejaPaye,
                    'reste_a_payer' => $resteAPayer
                ];
            }
        }

        return null;
    }

    /**
     * Enregistrement complet et sécurisé d'un encaissement en caisse
     */
    public function processEnregistrementPaiement(array $data, string $userCode, string $defaultAnneeCode, string $defaultEtabCode, ?object $validator = null): array
    {
        $db = $this->getCon();
        $inscriptionCode = trim($data['inscription_code'] ?? '');
        if (empty($inscriptionCode)) {
            return ['success' => false, 'message' => "Veuillez sélectionner un dossier d'inscription valide."];
        }

        $anneeCode = $defaultAnneeCode;
        $etabCode = $defaultEtabCode;

        $stmtInsInfo = $db->prepare("SELECT annee_code, etablissement_code FROM " . TABLES::INSCRIPTIONS . " WHERE code_inscription = ? LIMIT 1");
        $stmtInsInfo->execute([$inscriptionCode]);
        $insData = $stmtInsInfo->fetch(PDO::FETCH_ASSOC);

        if (!empty($insData['annee_code'])) {
            $anneeCode = $insData['annee_code'];
        }
        if (!empty($insData['etablissement_code'])) {
            $etabCode = $insData['etablissement_code'];
        }

        $today = date('Y-m-d');
        $mode = strtolower($data['mode_paiement'] ?? 'espece');

        if ($mode === 'especes' || $mode === 'espece' || $mode === 'cash' || empty($mode)) {
            $stmtSession = $db->prepare("SELECT * FROM " . TABLES::SESSIONS_CAISSE . " WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
            $stmtSession->execute([$today]);
            $sess = $stmtSession->fetch(PDO::FETCH_ASSOC);

            if (!$sess || $sess['statut_session'] !== 'ouverte') {
                if ($sess && in_array($sess['statut_session'], ['cloturee', 'valide'])) {
                    return ['success' => false, 'message' => "Encaissement impossible : La session de caisse du jour a déjà été CLÔTURÉE (Réf: {$sess['code_session']}). Aucun nouvel encaissement en espèces ne peut être enregistré."];
                } else {
                    return ['success' => false, 'message' => "Encaissement impossible : Aucune session de caisse n'est OUVERTE pour aujourd'hui. Veuillez ouvrir la session de caisse avant d'encaisser."];
                }
            }
        }

        $montantPaiement = (float)($data['montant_paiement'] ?? 0);
        if ($montantPaiement <= 0) {
            return ['success' => false, 'message' => "Le montant du versement doit être supérieur à 0 FCFA."];
        }

        $trancheCode = trim($data['tranche_code'] ?? '');
        if (empty($trancheCode)) {
            return ['success' => false, 'message' => "Veuillez obligatoirement sélectionner la tranche correspondante à ce versement."];
        }

        $montantFA = (float)($data['montant_frais_annexes'] ?? 0);
        $montantTranche = (float)($data['montant_tranche'] ?? ($montantPaiement - $montantFA));
        $tranche = null;

        if ($trancheCode !== 'SCOLARITE_GLOBALE') {
            $nextUnpaid = $this->getNextUnpaidTranche($inscriptionCode, $anneeCode);
            if ($nextUnpaid && $nextUnpaid['code_tranche'] !== $trancheCode) {
                return ['success' => false, 'message' => "Encaissement refusé : Vous devez obligatoirement solder la tranche impayée en cours « {$nextUnpaid['libelle_tranche']} » avant de pouvoir enregistrer un versement pour une tranche ultérieure."];
            }

            $stmtTr = $db->prepare("SELECT * FROM " . TABLES::TRANCHES_SCOLARITE . " WHERE code_tranche = ? LIMIT 1");
            $stmtTr->execute([$trancheCode]);
            $tranche = $stmtTr->fetch(PDO::FETCH_ASSOC);
            if (!$tranche) {
                return ['success' => false, 'message' => "La tranche sélectionnée est introuvable."];
            }

            $stmtPayTr = $db->prepare("SELECT SUM(montant_paiement) FROM " . TABLES::PAIEMENTS . " WHERE inscription_code = ? AND tranche_code = ? AND statut_paiement != 'annule'");
            $stmtPayTr->execute([$inscriptionCode, $trancheCode]);
            $dejaPayeTr = (float)($stmtPayTr->fetchColumn() ?: 0);
            $montantMaxTr = (float)$tranche['montant_tranche'];
            $resteAutorise = max(0, $montantMaxTr - $dejaPayeTr);

            if ($resteAutorise <= 0) {
                return ['success' => false, 'message' => "Paiement impossible : La tranche « {$tranche['libelle_tranche']} » a déjà été intégralement soldée (" . number_format($montantMaxTr, 0, ',', ' ') . " FCFA déjà payé). Veuillez sélectionner une autre tranche impayée."];
            }

            if ($montantTranche > $resteAutorise) {
                $resteFmt = number_format($resteAutorise, 0, ',', ' ');
                $montantSaisiFmt = number_format($montantTranche, 0, ',', ' ');
                return ['success' => false, 'message' => "Le montant saisi pour la tranche ($montantSaisiFmt FCFA) dépasse le solde restant dû pour cette tranche ($resteFmt FCFA). Veuillez saisir un montant inférieur ou égal à $resteFmt FCFA."];
            }
        }

        $now = date('Y-m-d H:i:s');
        $ref = $data['reference_paiement'] ?? '';
        $obs = $data['observations'] ?? '';
        $todayDate = date('Y-m-d');

        $stmtActiveSes = $db->prepare("SELECT code_session FROM " . TABLES::SESSIONS_CAISSE . " WHERE date_session = ? AND statut_session = 'ouverte' ORDER BY id_session DESC LIMIT 1");
        $stmtActiveSes->execute([$todayDate]);
        $activeSesCode = $stmtActiveSes->fetchColumn() ?: null;
        if (!$activeSesCode) {
            $stmtAnySes = $db->prepare("SELECT code_session FROM " . TABLES::SESSIONS_CAISSE . " WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
            $stmtAnySes->execute([$todayDate]);
            $activeSesCode = $stmtAnySes->fetchColumn() ?: null;
        }
        $sessionCode = !empty($data['session_caisse_code']) ? $data['session_caisse_code'] : $activeSesCode;

        // Cas split: Frais Annexes + Scolarité
        if ($montantFA > 0) {
            $createdCodes = [];
            $lastPaiementId = 0;

            // Opération 1 : Frais Annexes
            $codeFA = ($validator && method_exists($validator, 'generateCode')) ? $validator->generateCode('paiements', 'code_paiement', 'PAI-', 8) : ('PAI-' . strtoupper(substr(uniqid(), -8)));
            $stmtFA = $db->prepare("
                INSERT INTO " . TABLES::PAIEMENTS . " (
                    code_paiement, inscription_code, etablissement_code, annee_code, tranche_code,
                    montant_paiement, mode_paiement, reference_paiement, type_paiement, categorie_paiement, observations,
                    statut_paiement, date_paiement, session_caisse_code, user_code
                ) VALUES (?, ?, ?, ?, 'FRAIS_ANNEXES', ?, ?, ?, 'Frais Annexes', 'FRAIS_ANNEXES', ?, 'confirme', ?, ?, ?)
            ");
            $obsFA = trim($obs ? ($obs . ' - Frais Annexes') : 'Règlement Frais Annexes');
            $stmtFA->execute([
                $codeFA, $inscriptionCode, $etabCode, $anneeCode,
                $montantFA, $mode, $ref, $obsFA, $now, $sessionCode, $userCode
            ]);
            $lastPaiementId = (int)$db->lastInsertId();
            $createdCodes[] = $codeFA;

            // Opération 2 : Scolarité (Tranche)
            if ($montantTranche > 0) {
                $codeTr = ($validator && method_exists($validator, 'generateCode')) ? $validator->generateCode('paiements', 'code_paiement', 'PAI-', 8) : ('PAI-' . strtoupper(substr(uniqid(), -8)));
                $typeTr = !empty($tranche['libelle_tranche']) ? 'Règlement ' . $tranche['libelle_tranche'] : 'Règlement Scolarité';
                $stmtTrIns = $db->prepare("
                    INSERT INTO " . TABLES::PAIEMENTS . " (
                        code_paiement, inscription_code, etablissement_code, annee_code, tranche_code,
                        montant_paiement, mode_paiement, reference_paiement, type_paiement, categorie_paiement, observations,
                        statut_paiement, date_paiement, session_caisse_code, user_code
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'SCOLARITE', ?, 'confirme', ?, ?, ?)
                ");
                $stmtTrIns->execute([
                    $codeTr, $inscriptionCode, $etabCode, $anneeCode, $trancheCode,
                    $montantTranche, $mode, $ref, $typeTr, $obs, $now, $sessionCode, $userCode
                ]);
                $lastPaiementId = (int)$db->lastInsertId();
                $createdCodes[] = $codeTr;
            }

            $this->updateInscriptionStatusFromPayments($inscriptionCode);

            $stmtCountVersem = $db->prepare("SELECT COUNT(*) FROM " . TABLES::PAIEMENTS . " WHERE inscription_code = ? AND statut_paiement != 'annule'");
            $stmtCountVersem->execute([$inscriptionCode]);
            $countVersements = (int)($stmtCountVersem->fetchColumn() ?: 0);

            $stmtInscrId = $db->prepare("SELECT id_inscription FROM " . TABLES::INSCRIPTIONS . " WHERE code_inscription = ? LIMIT 1");
            $stmtInscrId->execute([$inscriptionCode]);
            $inscrId = (int)($stmtInscrId->fetchColumn() ?: 0);
            $encryptedInscriptionId = ($validator && method_exists($validator, 'crypter')) ? $validator->crypter($inscrId > 0 ? $inscrId : $inscriptionCode) : (string)$inscrId;
            $encryptedId = ($validator && method_exists($validator, 'crypter')) ? $validator->crypter($lastPaiementId) : (string)$lastPaiementId;

            return [
                'success' => true,
                'message' => 'Encaissements enregistrés avec succès ! (' . count($createdCodes) . ' opérations générées)',
                'data' => [
                    'reload' => true,
                    'id_paiement' => $lastPaiementId,
                    'encrypted_id' => $encryptedId,
                    'code_paiement' => implode(' & ', $createdCodes),
                    'montant' => $montantFA + $montantTranche,
                    'count_versements' => $countVersements,
                    'encrypted_inscription_id' => $encryptedInscriptionId
                ]
            ];
        }

        // Cas standard paiement scolarité simple
        if (empty($data['code_paiement'])) {
            $data['code_paiement'] = ($validator && method_exists($validator, 'generateCode')) ? $validator->generateCode('paiements', 'code_paiement', 'PAI-', 8) : ('PAI-' . strtoupper(substr(uniqid(), -8)));
        }

        $data['statut_paiement'] = $data['statut_paiement'] ?? 'confirme';
        $data['date_paiement'] = $now;
        if (empty($data['type_paiement'])) {
            $data['type_paiement'] = !empty($tranche['libelle_tranche']) ? 'Règlement ' . $tranche['libelle_tranche'] : 'Règlement Scolarité';
        }
        $data['session_caisse_code'] = $sessionCode;
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        $data['tranche_code'] = $trancheCode;
        $data['categorie_paiement'] = 'SCOLARITE';

        $filteredData = $this->filterData($data);
        if ($this->create($filteredData)) {
            $lastId = (int)$this->getCon()->lastInsertId();
            if ($lastId <= 0 && !empty($data['code_paiement'])) {
                $stmtFind = $db->prepare("SELECT id_paiement FROM " . TABLES::PAIEMENTS . " WHERE code_paiement = ? LIMIT 1");
                $stmtFind->execute([$data['code_paiement']]);
                $lastId = (int)$stmtFind->fetchColumn();
            }

            $this->updateInscriptionStatusFromPayments($inscriptionCode);

            $stmtCountVersem = $db->prepare("SELECT COUNT(*) FROM " . TABLES::PAIEMENTS . " WHERE inscription_code = ? AND statut_paiement != 'annule'");
            $stmtCountVersem->execute([$inscriptionCode]);
            $countVersements = (int)($stmtCountVersem->fetchColumn() ?: 0);

            $stmtInscrId = $db->prepare("SELECT id_inscription FROM " . TABLES::INSCRIPTIONS . " WHERE code_inscription = ? LIMIT 1");
            $stmtInscrId->execute([$inscriptionCode]);
            $inscrId = (int)($stmtInscrId->fetchColumn() ?: 0);
            $encryptedInscriptionId = ($validator && method_exists($validator, 'crypter')) ? $validator->crypter($inscrId > 0 ? $inscrId : $inscriptionCode) : (string)$inscrId;
            $encryptedId = ($validator && method_exists($validator, 'crypter')) ? $validator->crypter($lastId) : (string)$lastId;

            return [
                'success' => true,
                'message' => 'Règlement de caisse enregistré avec succès!',
                'data' => [
                    'reload' => true,
                    'id_paiement' => $lastId,
                    'encrypted_id' => $encryptedId,
                    'code_paiement' => $data['code_paiement'],
                    'montant' => $montantPaiement,
                    'count_versements' => $countVersements,
                    'encrypted_inscription_id' => $encryptedInscriptionId
                ]
            ];
        }

        return ['success' => false, 'message' => $this->getLastError() ?: "Erreur lors de l'enregistrement du paiement."];
    }

    /**
     * Met à jour le statut de l'inscription selon le cumul des versements de scolarité
     */
    public function updateInscriptionStatusFromPayments(string $inscriptionCode): void
    {
        $db = $this->getCon();
        $stmtIns = $db->prepare("SELECT * FROM " . TABLES::INSCRIPTIONS . " WHERE code_inscription = ? LIMIT 1");
        $stmtIns->execute([$inscriptionCode]);
        $ins = $stmtIns->fetch(PDO::FETCH_ASSOC);
        if ($ins) {
            $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
            $stmtTot = $db->prepare("
                SELECT SUM(montant_paiement) 
                FROM " . TABLES::PAIEMENTS . " 
                WHERE inscription_code = ? 
                  AND statut_paiement != 'annule'
                  AND (categorie_paiement IS NULL OR categorie_paiement != 'FRAIS_ANNEXES')
                  AND (tranche_code IS NULL OR tranche_code != 'FRAIS_ANNEXES')
            ");
            $stmtTot->execute([$inscriptionCode]);
            $totalPayeCumul = (float)($stmtTot->fetchColumn() ?: 0);

            if ($totalPayeCumul >= $scolariteDue && $scolariteDue > 0) {
                $db->prepare("UPDATE " . TABLES::INSCRIPTIONS . " SET statut_inscription = 'solde' WHERE code_inscription = ?")->execute([$inscriptionCode]);
            } else {
                $db->prepare("UPDATE " . TABLES::INSCRIPTIONS . " SET statut_inscription = 'valide' WHERE code_inscription = ? AND statut_inscription = 'solde'")->execute([$inscriptionCode]);
            }
        }
    }

    /**
     * Annule un versement de caisse
     */
    public function annulerVersement(int $id, string $codePaiement, string $motif, string $userCode, string $userName): array
    {
        $db = $this->getCon();
        $paiement = null;
        if ($id > 0) {
            $stmt = $db->prepare("SELECT * FROM " . TABLES::PAIEMENTS . " WHERE id_paiement = ? LIMIT 1");
            $stmt->execute([$id]);
            $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (!empty($codePaiement)) {
            $stmt = $db->prepare("SELECT * FROM " . TABLES::PAIEMENTS . " WHERE code_paiement = ? LIMIT 1");
            $stmt->execute([$codePaiement]);
            $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$paiement) {
            return ['success' => false, 'message' => "Paiement introuvable."];
        }

        if ($paiement['statut_paiement'] === 'annule') {
            return ['success' => false, 'message' => "Ce versement est déjà annulé."];
        }

        $cancellationNote = " [ANNULATION le " . date('d/m/Y H:i') . " par " . (!empty($userName) ? $userName : $userCode) . (!empty($motif) ? " - Motif: " . $motif : "") . "]";
        $newObservations = trim(($paiement['observations'] ?? '') . $cancellationNote);

        $stmtUp = $db->prepare("UPDATE " . TABLES::PAIEMENTS . " SET statut_paiement = 'annule', observations = ? WHERE id_paiement = ?");
        $res = $stmtUp->execute([$newObservations, $paiement['id_paiement']]);

        if ($res) {
            $inscriptionCode = $paiement['inscription_code'] ?? '';
            if (!empty($inscriptionCode)) {
                $this->updateInscriptionStatusFromPayments($inscriptionCode);
            }
            return [
                'success' => true,
                'message' => "Le versement #" . ($paiement['code_paiement'] ?? '') . " a été annulé avec succès.",
                'data' => [
                    'reload' => true,
                    'id_paiement' => $paiement['id_paiement'],
                    'code_paiement' => $paiement['code_paiement']
                ]
            ];
        }

        return ['success' => false, 'message' => "Erreur lors de l'annulation du versement."];
    }

    /**
     * Détails complets d'un paiement avec calculs de soldes et groupes
     */
    public function getDetailsPaiementData(int $actualId, array $item): array
    {
        $inscriptionCode = $item['inscription_code'] ?? '';
        $allPayments = $this->getPaiementsByInscription($inscriptionCode, true);

        $firstPaymentId = !empty($allPayments) ? (int)$allPayments[0]['id_paiement'] : 0;
        $firstDateMinute = !empty($allPayments) ? substr($allPayments[0]['date_paiement'] ?? '', 0, 16) : '';
        
        $firstPaymentGroupIds = [];
        $firstGroupScolarite = 0.0;
        $firstGroupFraisAnnexes = 0.0;

        if (!empty($allPayments)) {
            foreach ($allPayments as $pItem) {
                $pDateMin = substr($pItem['date_paiement'] ?? '', 0, 16);
                $isFA = (($pItem['tranche_code'] ?? '') === 'FRAIS_ANNEXES' || strtolower(trim($pItem['categorie_paiement'] ?? '')) === 'frais_annexes');
                
                if ($pDateMin === $firstDateMinute || (count($firstPaymentGroupIds) < 2 && count($allPayments) <= 2)) {
                    $firstPaymentGroupIds[] = (int)$pItem['id_paiement'];
                    if ($isFA) {
                        $firstGroupFraisAnnexes += (float)$pItem['montant_paiement'];
                    } else {
                        $firstGroupScolarite += (float)$pItem['montant_paiement'];
                    }
                }
            }
        }

        $isFirstPayment = in_array($actualId, $firstPaymentGroupIds, true) || ($actualId === $firstPaymentId) || (count($allPayments) <= 1);

        $totalPayeCumul = 0.0;
        $totalScolariteCumul = 0.0;
        $totalFraisAnnexesCumul = 0.0;

        foreach ($allPayments as $p) {
            $m = (float)$p['montant_paiement'];
            $totalPayeCumul += $m;
            $isFA = (($p['tranche_code'] ?? '') === 'FRAIS_ANNEXES' || strtolower(trim($p['categorie_paiement'] ?? '')) === 'frais_annexes');
            if ($isFA) {
                $totalFraisAnnexesCumul += $m;
            } else {
                $totalScolariteCumul += $m;
            }
            if ((int)$p['id_paiement'] === $actualId) {
                break;
            }
        }

        $scolarite = (float)($item['montant_scolarite_inscription'] ?? 0);
        $soldeRestant = max(0, $scolarite - $totalScolariteCumul);

        if (empty($item['photo_inscription']) && empty($item['photo_etudiant']) && !empty($inscriptionCode)) {
            $pRow = $this->getPhotoInscription($inscriptionCode);
            if ($pRow) {
                $item['photo_inscription'] = $pRow['photo_inscription'] ?? null;
                $item['photo_etudiant'] = $pRow['photo_etudiant'] ?? null;
            }
        }

        return [
            'item' => $item,
            'totalPayeCumul' => $totalPayeCumul,
            'totalScolariteCumul' => $totalScolariteCumul,
            'totalFraisAnnexesCumul' => $totalFraisAnnexesCumul,
            'firstGroupScolarite' => $firstGroupScolarite,
            'firstGroupFraisAnnexes' => $firstGroupFraisAnnexes,
            'isFirstPayment' => $isFirstPayment,
            'soldeRestant' => $soldeRestant,
            'scolarite' => $scolarite
        ];
    }

    /**
     * Données complètes pour l'impression du reçu de versement officiel
     */
    public function getRecuVersementData(string $id): ?array
    {
        $db = $this->getCon();
        $stmtPai = $db->prepare("
            SELECT * FROM v_paiements_complets
            WHERE id_paiement = ? OR code_paiement = ?
            LIMIT 1
        ");
        $stmtPai->execute([$id, $id]);
        $pRow = $stmtPai->fetch(PDO::FETCH_ASSOC);

        if (!$pRow) {
            return null;
        }

        $inscriptionCode = $pRow['code_inscription'] ?? '';

        $stmtAll = $db->prepare("
            SELECT * FROM " . TABLES::PAIEMENTS . " 
            WHERE inscription_code = ? AND statut_paiement != 'annule'
            ORDER BY date_paiement ASC, id_paiement ASC
        ");
        $stmtAll->execute([$inscriptionCode]);
        $allPaiements = $stmtAll->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $scolariteVerse = 0;
        $fraisAnnexesVerse = 0;
        $numVersement = 1;
        $numVersementScolarite = 0;
        $count = 0;
        $countScolarite = 0;

        foreach ($allPaiements as $pItem) {
            $count++;
            $m = (float)$pItem['montant_paiement'];
            $isFA = (($pItem['tranche_code'] ?? '') === 'FRAIS_ANNEXES' || strtolower(trim($pItem['categorie_paiement'] ?? '')) === 'frais_annexes');
            if ($isFA) {
                $fraisAnnexesVerse += $m;
            } else {
                $scolariteVerse += $m;
                $countScolarite++;
            }

            if ((int)$pItem['id_paiement'] === (int)$pRow['id_paiement'] || $pItem['code_paiement'] === $pRow['code_paiement']) {
                $numVersement = $count;
                $numVersementScolarite = $isFA ? 0 : $countScolarite;
            }
        }

        $montantOp = (float)$pRow['montant_paiement'];
        $isFAOp = (($pRow['tranche_code'] ?? '') === 'FRAIS_ANNEXES' || strtolower(trim($pRow['categorie_paiement'] ?? '')) === 'frais_annexes');

        $scolariteTotPayer = (float)($pRow['montant_scolarite_inscription'] ?? 0);
        $scolariteTotVerse = $scolariteVerse;
        $scolariteReste = max(0, $scolariteTotPayer - $scolariteTotVerse);

        $scolariteOpJour = $isFAOp ? 0 : $montantOp;
        $versementOpJour = $montantOp;
        $totalOpJour = $montantOp;
        $totalPayer = $scolariteTotPayer;
        $totalVerse = $scolariteVerse;
        $totalRestePayer = $scolariteReste;

        $nomComplet = trim(strtoupper($pRow['nom_etudiant'] ?? '') . ' ' . ($pRow['prenom_etudiant'] ?? ''));
        $caissierNom = trim(($pRow['prenom_caissier'] ?? '') . ' ' . ($pRow['nom_caissier'] ?? ''));
        if (empty($caissierNom)) {
            $caissierNom = "La Caisse";
        }

        $logoSrc = $this->encodeImageBase64([
            'public/assets/images/logo/logo_eicg.jpg',
            'assets/images/logo/logo_eicg.jpg',
            'public/uploads/logos/logo_1787358264.jpg'
        ]);

        $photoSrc = $this->encodeImageBase64([
            $pRow['photo_etudiant'] ?? '',
            $pRow['photo_inscription'] ?? '',
            'public/assets/images/placeholders/etudiant.png',
            'assets/images/placeholders/etudiant.png'
        ]);

        $numRecuCode = !empty($pRow['recu_numero_paiement']) ? $pRow['recu_numero_paiement'] : (!empty($pRow['code_paiement']) ? $pRow['code_paiement'] : 'GE-' . sprintf('%08d', $pRow['id_paiement']));

        $refSeed = !empty($pRow['code_paiement']) ? $pRow['code_paiement'] : $numRecuCode;
        $refCaissHash1 = sprintf("%05d", abs(crc32($refSeed . '_sco')) % 90000 + 10000);
        $refCaissHash2 = sprintf("%010d", abs(crc32($refSeed . '_cais')) % 9000000000 + 1000000000);
        $refCaiss = !empty($pRow['reference_paiement']) ? $pRow['reference_paiement'] : ($numRecuCode . 'ScoFOF' . $refCaissHash1 . ',' . $refCaissHash2 . 'ScaisKON');

        $filiereNiveau = (!empty($pRow['libelle_filiere']) && !empty($pRow['libelle_niveau'])) 
            ? ($pRow['libelle_filiere'] . ' - ' . $pRow['libelle_niveau']) 
            : ($pRow['libelle_classe'] ?? '-');

        return [
            'annee_libelle' => $pRow['libelle_annee'] ?? '',
            'annee_universitaire' => $pRow['libelle_annee'] ?? '',
            'code_paiement' => $numRecuCode,
            'numero_recu' => $numRecuCode,
            'code_inscription' => $inscriptionCode,
            'date_operation' => !empty($pRow['date_paiement']) ? date('d/m/Y H:i:s', strtotime($pRow['date_paiement'])) : date('d/m/Y H:i:s'),
            'matricule_etudiant' => !empty($pRow['matricule_etudiant']) ? $pRow['matricule_etudiant'] : ($pRow['code_etudiant'] ?? '-'),
            'matricule' => !empty($pRow['matricule_etudiant']) ? $pRow['matricule_etudiant'] : ($pRow['code_etudiant'] ?? '-'),
            'filiere_niveau' => $filiereNiveau,
            'nom_prenom_etudiant' => $nomComplet,
            'nom_prenoms' => $nomComplet,
            'type_operation' => strtoupper($isFAOp ? 'FRAIS ANNEXES' : ($pRow['libelle_tranche'] ?? ($pRow['type_paiement'] ?? 'SCOLARITÉ'))),
            'statut_affectation' => (($pRow['affectation_etat'] ?? '') === 'affecte' || ($pRow['affectation_etat'] ?? '') === 'oui') ? 'AFFECTE' : 'NON AFFECTE',
            'statut_etudiant' => (($pRow['affectation_etat'] ?? '') === 'affecte' || ($pRow['affectation_etat'] ?? '') === 'oui') ? 'AFFECTE' : 'NON AFFECTE',
            'montant_operation' => $montantOp,
            'montant_operation_formatted' => number_format($montantOp, 0, ',', ' ') . ' FCFA',
            'montant_operation_lettres' => class_exists('PdfService') ? PdfService::numberToWordsFrench($montantOp) : '',
            'montant_en_lettres' => class_exists('PdfService') ? PdfService::numberToWordsFrench($montantOp) : '',
            'logo_src' => $logoSrc,
            'photo_etudiant' => $photoSrc,
            'photo_src' => $photoSrc,
            'scolarite_op_du_jour' => $scolariteOpJour,
            'scolarite_total_payer' => $scolariteTotPayer,
            'scolarite_total_verse' => $scolariteTotVerse,
            'scolarite_reste_payer' => $scolariteReste,
            'scolarite_op_jour' => $scolariteOpJour,
            'scolarite_tot_payer' => $scolariteTotPayer,
            'scolarite_tot_verse' => $scolariteTotVerse,
            'scolarite_reste' => $scolariteReste,
            'versement_op_du_jour' => $versementOpJour,
            'total_op_du_jour' => $totalOpJour,
            'total_payer' => $totalPayer,
            'total_verse' => $totalVerse,
            'scolarite_total' => $scolariteTotPayer,
            'total_reste_payer' => $totalRestePayer,
            'reste_a_payer' => $totalRestePayer,
            'droit_op_jour' => 0,
            'droit_tot_payer' => 0,
            'droit_tot_verse' => 0,
            'date_prochain_paiement' => ($scolariteReste <= 0) ? 'SOLDÉ' : date('d/m/Y', strtotime('+30 days', strtotime($pRow['date_paiement'] ?? 'now'))),
            'caissier_nom' => $caissierNom,
            'date_impression' => date('d/m/Y H:i:s'),
            'ref_caiss' => $refCaiss,
            'code_barre_val' => $refCaiss,
            'num_versement' => $numVersement,
            'num_versement_scolarite' => $numVersementScolarite
        ];
    }

    /**
     * Données pour l'impression de l'état de synthèse comptable des étudiants
     */
    public function getPrintComptabiliteEtudiantsData(string $anneeCode, string $niveauCode, string $classeCode, string $regime, string $statutPaiement, string $currentUserName): array
    {
        $db = $this->getCon();

        $where = "WHERE i.statut_inscription != 'annule' AND i.annee_code = ?";
        $params = [$anneeCode];

        if ($niveauCode !== 'ALL' && !empty($niveauCode)) {
            $where .= " AND i.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if ($classeCode !== 'ALL' && !empty($classeCode)) {
            $where .= " AND i.classe_code = ?";
            $params[] = $classeCode;
        }
        if ($regime !== 'ALL' && !empty($regime)) {
            if ($regime === 'affecte') {
                $where .= " AND (i.affectation_etat = 'affecte' OR i.affectation_etat = 'oui')";
            } else {
                $where .= " AND (i.affectation_etat = 'non_affecte' OR i.affectation_etat = 'non')";
            }
        }

        $sql = "
            SELECT i.*
            FROM v_inscriptions_completes i
            {$where} AND i.code_etudiant IS NOT NULL
            ORDER BY i.nom_etudiant ASC, i.prenom_etudiant ASC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmtPay = $db->query("SELECT inscription_code, total_paye_scolarite AS total_paye FROM v_bilan_paiements_inscriptions");
        $paymentsGrouped = [];
        if ($stmtPay) {
            foreach ($stmtPay->fetchAll(PDO::FETCH_ASSOC) as $rowP) {
                $paymentsGrouped[$rowP['inscription_code']] = (float)$rowP['total_paye'];
            }
        }

        $stmtScoAll = $db->prepare("SELECT filiere_code, niveau_code, affectation_etat, montant_scolarite FROM " . TABLES::SCOLARITES . " WHERE statut_scolarite = 'actif' AND (annee_code = ? OR annee_code IS NULL OR annee_code = '')");
        $stmtScoAll->execute([$anneeCode]);
        $scolariteGrid = $stmtScoAll->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $dataEtudiants = [];
        $kpiCount = 0;
        $kpiScolariteDueTotal = 0;
        $kpiTotalAttendu = 0;
        $kpiTotalEncaisse = 0;
        $kpiResteARecouvrer = 0;

        foreach ($inscriptions as $ins) {
            $codeIns = $ins['code_inscription'];
            $filiereCode = $ins['filiere_code'] ?? '';
            $nCode = $ins['niveau_code'] ?? '';
            $affRaw = $ins['affectation_etat'] ?? 'non';
            $isAffecte = ($affRaw === 'affecte' || $affRaw === 'oui');
            $targetAff = $isAffecte ? 'affecte' : 'non_affecte';

            $scolariteDue = 0;
            foreach ($scolariteGrid as $sg) {
                $sgAff = ($sg['affectation_etat'] === 'affecte' || $sg['affectation_etat'] === 'oui') ? 'affecte' : 'non_affecte';
                if ($sg['filiere_code'] === $filiereCode && $sg['niveau_code'] === $nCode && $sgAff === $targetAff) {
                    $scolariteDue = (float)$sg['montant_scolarite'];
                    break;
                }
            }
            if ($scolariteDue <= 0) {
                $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
            }

            $totalAttendu = $scolariteDue;
            $totalEncaisse = $paymentsGrouped[$codeIns] ?? 0;
            $soldeRestant = max(0, $totalAttendu - $totalEncaisse);

            $statusCode = 'non_paye';
            if (($totalAttendu > 0 && $totalEncaisse >= $totalAttendu) || ($soldeRestant <= 0 && $totalAttendu > 0)) {
                $statusCode = 'solde';
            } elseif ($totalEncaisse > 0) {
                $statusCode = 'partiel';
            }

            if ($statutPaiement !== 'ALL' && $statutPaiement !== $statusCode) {
                continue;
            }

            $kpiCount++;
            $kpiScolariteDueTotal += $scolariteDue;
            $kpiTotalAttendu += $totalAttendu;
            $kpiTotalEncaisse += $totalEncaisse;
            $kpiResteARecouvrer += $soldeRestant;

            $dataEtudiants[] = [
                'matricule' => $ins['matricule_etudiant'] ?? 'N/A',
                'nom_complet' => strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
                'classe' => $ins['libelle_classe'] ?? 'N/A',
                'regime' => $isAffecte ? 'Affecté' : 'Privé',
                'scolarite_due' => $scolariteDue,
                'total_attendu' => $totalAttendu,
                'total_encaisse' => $totalEncaisse,
                'solde_restant' => $soldeRestant,
                'statut_code' => $statusCode,
                'statut_libelle' => ($statusCode === 'solde') ? 'Soldé' : (($statusCode === 'partiel') ? 'Acompte Payé' : 'Non Réglé')
            ];
        }

        $logoSrc = $this->encodeImageBase64(['public/assets/images/logo/logo_eicg.jpg']);

        $anneeRow = $db->prepare("SELECT libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
        $anneeRow->execute([$anneeCode]);
        $anneeLib = ($anneeRow->fetchColumn()) ?: $anneeCode;

        $niveauLib = 'Tous les niveaux';
        if ($niveauCode !== 'ALL') {
            $nRow = $db->prepare("SELECT libelle_niveau FROM " . TABLES::NIVEAUX . " WHERE code_niveau = ? LIMIT 1");
            $nRow->execute([$niveauCode]);
            $niveauLib = ($nRow->fetchColumn()) ?: $niveauCode;
        }

        $classeLib = 'Toutes les classes';
        if ($classeCode !== 'ALL') {
            $cRow = $db->prepare("SELECT libelle_classe FROM " . TABLES::CLASSES . " WHERE code_classe = ? LIMIT 1");
            $cRow->execute([$classeCode]);
            $classeLib = ($cRow->fetchColumn()) ?: $classeCode;
        }

        $regimeLib = ($regime === 'affecte') ? 'Affecté de l\'État' : (($regime === 'non_affecte') ? 'Privé / Non-Affecté' : 'Tous les régimes');
        $statutLib = ($statutPaiement === 'solde') ? 'Soldé (Totalement Réglé)' : (($statutPaiement === 'partiel') ? 'Acompte Payé / Partiel' : (($statutPaiement === 'non_paye') ? 'Non Réglé (0 FCFA)' : 'Tous les statuts'));

        return [
            'logo_src' => $logoSrc,
            'annee_libelle' => $anneeLib,
            'niveau_libelle' => $niveauLib,
            'classe_libelle' => $classeLib,
            'regime_libelle' => $regimeLib,
            'statut_libelle' => $statutLib,
            'kpis' => [
                'total_etudiants' => $kpiCount,
                'scolarite_due_total' => $kpiScolariteDueTotal,
                'total_attendu' => $kpiTotalAttendu,
                'total_encaisse' => $kpiTotalEncaisse,
                'reste_a_recouvrer' => $kpiResteARecouvrer
            ],
            'etudiants' => $dataEtudiants,
            'date_impression' => date('d/m/Y H:i:s'),
            'caissier_nom' => !empty($currentUserName) ? $currentUserName : 'Direction Financière'
        ];
    }

    /**
     * Données pour l'impression du Registre Chronologique des Encaissements
     */
    public function getPrintRegistreEncaissementsData(string $anneeCode, string $niveauCode, string $classeCode, string $canalPaiement, string $modePaiement, string $categoriePaiement, string $dateDebut, string $dateFin, string $currentUserName): array
    {
        $db = $this->getCon();
        $where = "WHERE p.statut_paiement != 'annule'";
        $params = [];

        if (!empty($categoriePaiement) && $categoriePaiement !== 'ALL') {
            if ($categoriePaiement === 'FRAIS_ANNEXES') {
                $where .= " AND (p.categorie_paiement = 'FRAIS_ANNEXES' OR p.tranche_code = 'FRAIS_ANNEXES')";
            } else {
                $where .= " AND p.categorie_paiement = ?";
                $params[] = $categoriePaiement;
            }
        } else {
            $where .= " AND (p.categorie_paiement IS NULL OR p.categorie_paiement != 'FRAIS_ANNEXES') AND (p.tranche_code IS NULL OR p.tranche_code != 'FRAIS_ANNEXES')";
        }

        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $where .= " AND (p.annee_code = ? OR p.inscription_annee_code = ?)";
            $params[] = $anneeCode;
            $params[] = $anneeCode;
        }

        if (!empty($niveauCode) && $niveauCode !== 'ALL') {
            $where .= " AND p.niveau_code = ?";
            $params[] = $niveauCode;
        }

        if (!empty($classeCode) && $classeCode !== 'ALL') {
            $where .= " AND p.classe_code = ?";
            $params[] = $classeCode;
        }

        if (!empty($canalPaiement) && $canalPaiement !== 'ALL') {
            $where .= " AND p.canal_paiement = ?";
            $params[] = $canalPaiement;
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
            SELECT p.*
            FROM v_paiements_complets p
            {$where}
            ORDER BY p.date_paiement DESC, p.id_paiement DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalGeneral = 0;
        $totalGuichet = 0;
        $totalEnLigne = 0;
        $totalEspeces = 0;
        $totalMobileMoney = 0;
        $totalBanque = 0;

        foreach ($paiements as $p) {
            $mt = (float)$p['montant_paiement'];
            $totalGeneral += $mt;

            $canal = strtolower($p['canal_paiement'] ?? 'guichet');
            if ($canal === 'en_ligne') {
                $totalEnLigne += $mt;
            } else {
                $totalGuichet += $mt;
            }

            $mode = strtolower($p['mode_paiement'] ?? '');
            if (strpos($mode, 'espece') !== false || strpos($mode, 'espèces') !== false || strpos($mode, 'cash') !== false) {
                $totalEspeces += $mt;
            } elseif (strpos($mode, 'mobile') !== false || strpos($mode, 'wave') !== false || strpos($mode, 'orange') !== false || strpos($mode, 'mtn') !== false || strpos($mode, 'moov') !== false) {
                $totalMobileMoney += $mt;
            } else {
                $totalBanque += $mt;
            }
        }

        $logoSrc = $this->encodeImageBase64(['public/assets/images/logo/logo_eicg.jpg']);

        $anneeLibelle = 'Toutes les années';
        if (!empty($anneeCode)) {
            $stmtA = $db->prepare("SELECT libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$anneeCode]);
            $anneeLibelle = $stmtA->fetchColumn() ?: $anneeCode;
        }

        $niveauLibelle = 'Tous les niveaux';
        if (!empty($niveauCode) && $niveauCode !== 'ALL') {
            $stmtN = $db->prepare("SELECT libelle_niveau FROM " . TABLES::NIVEAUX . " WHERE code_niveau = ? LIMIT 1");
            $stmtN->execute([$niveauCode]);
            $niveauLibelle = $stmtN->fetchColumn() ?: $niveauCode;
        }

        $classeLibelle = 'Toutes les classes';
        if (!empty($classeCode) && $classeCode !== 'ALL') {
            $stmtC = $db->prepare("SELECT libelle_classe FROM " . TABLES::CLASSES . " WHERE code_classe = ? LIMIT 1");
            $stmtC->execute([$classeCode]);
            $classeLibelle = $stmtC->fetchColumn() ?: $classeCode;
        }

        $canalLibelle = 'Tous les canaux';
        if (!empty($canalPaiement) && $canalPaiement !== 'ALL') {
            $canalLibelle = ($canalPaiement === 'en_ligne') ? 'En Ligne' : 'Guichet';
        }

        $modeLibelle = 'Tous les modes';
        if (!empty($modePaiement) && $modePaiement !== 'ALL') {
            $modeLibelle = ucfirst($modePaiement);
        }

        $categorieLibelle = 'Scolarité';
        if (!empty($categoriePaiement) && $categoriePaiement !== 'ALL') {
            $categorieLibelle = ($categoriePaiement === 'FRAIS_ANNEXES') ? 'Frais Annexes' : 'Scolarité';
        }

        $periodeLibelle = 'Toute la période';
        if (!empty($dateDebut) && !empty($dateFin)) {
            $periodeLibelle = 'Du ' . date('d/m/Y', strtotime($dateDebut)) . ' au ' . date('d/m/Y', strtotime($dateFin));
        } elseif (!empty($dateDebut)) {
            $periodeLibelle = 'Du ' . date('d/m/Y', strtotime($dateDebut));
        } elseif (!empty($dateFin)) {
            $periodeLibelle = 'Jusqu\'au ' . date('d/m/Y', strtotime($dateFin));
        }

        return [
            'logo_src' => $logoSrc,
            'annee_libelle' => $anneeLibelle,
            'niveau_libelle' => $niveauLibelle,
            'classe_libelle' => $classeLibelle,
            'canal_libelle' => $canalLibelle,
            'mode_libelle' => $modeLibelle,
            'categorie_libelle' => $categorieLibelle,
            'periode_libelle' => $periodeLibelle,
            'paiements' => $paiements,
            'total_general' => $totalGeneral,
            'total_guichet' => $totalGuichet,
            'total_en_ligne' => $totalEnLigne,
            'total_especes' => $totalEspeces,
            'total_mobile_money' => $totalMobileMoney,
            'total_banque' => $totalBanque,
            'count_paiements' => count($paiements),
            'date_impression' => date('d/m/Y H:i:s'),
            'date_debut' => $dateDebut ? date('d/m/Y', strtotime($dateDebut)) : '-',
            'date_fin' => $dateFin ? date('d/m/Y', strtotime($dateFin)) : '-',
            'agent_nom' => !empty($currentUserName) ? $currentUserName : 'Service Caisse GEICG'
        ];
    }

    /**
     * Données pour l'impression de la fiche bilan financier de l'étudiant
     */
    public function getPrintBilanFinancierData(string $id, string $activeAnneeCode, string $currentUserName): ?array
    {
        $db = $this->getCon();

        $stmtInscr = $db->prepare("
            SELECT 
                i.id_inscription, i.code_inscription, i.statut_inscription, i.montant_scolarite_inscription, i.affectation_etat, i.photo_inscription, i.annee_code, i.created_at_inscription,
                e.code_etudiant, e.id_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, e.telephone_etudiant, e.photo_etudiant,
                cl.code_classe, cl.libelle_classe, cl.filiere_code, cl.niveau_code, f.libelle_filiere, f.type_filiere, n.libelle_niveau, a.libelle_annee
            FROM " . TABLES::INSCRIPTIONS . " i
            JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
            LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
            WHERE i.id_inscription = ? OR i.code_inscription = ? OR e.code_etudiant = ? OR e.id_etudiant = ?
            LIMIT 1
        ");
        $stmtInscr->execute([$id, $id, $id, $id]);
        $ins = $stmtInscr->fetch(PDO::FETCH_ASSOC);

        if (!$ins) {
            return null;
        }

        $inscriptionCode = $ins['code_inscription'] ?? '';
        $anneeCode = $ins['annee_code'] ?? $activeAnneeCode;

        $filiereCode = $ins['filiere_code'] ?? '';
        $nCode = $ins['niveau_code'] ?? '';
        $affRaw = $ins['affectation_etat'] ?? 'non';
        $isAffecte = ($affRaw === 'affecte' || $affRaw === 'oui');
        $targetAff = $isAffecte ? 'affecte' : 'non_affecte';

        $scolariteDue = 0;
        $codeScolarite = '';
        $stmtSco = $db->prepare("
            SELECT code_scolarite, montant_scolarite FROM " . TABLES::SCOLARITES . " 
            WHERE filiere_code = ? 
              AND (annee_code = ? OR annee_code = '' OR annee_code IS NULL)
              AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
              AND (affectation_etat = ? OR affectation_etat = ?)
              AND statut_scolarite = 'actif'
            ORDER BY 
              (CASE WHEN annee_code = ? THEN 1 ELSE 2 END),
              (CASE WHEN niveau_code = ? THEN 1 ELSE 2 END),
              (CASE WHEN affectation_etat = ? THEN 1 ELSE 2 END),
              id_scolarite DESC
            LIMIT 1
        ");
        $stmtSco->execute([$filiereCode, $anneeCode, $nCode, $affRaw, $targetAff, $anneeCode, $nCode, $targetAff]);
        $scoRow = $stmtSco->fetch(PDO::FETCH_ASSOC);
        if ($scoRow) {
            $scolariteDue = (float)$scoRow['montant_scolarite'];
            $codeScolarite = $scoRow['code_scolarite'] ?? '';
        }
        if ($scolariteDue <= 0) {
            $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
        }

        $totalScolariteFixe = $scolariteDue;

        $stmtPaiements = $db->prepare("
            SELECT * FROM " . TABLES::PAIEMENTS . "
            WHERE inscription_code = ? 
              AND statut_paiement != 'annule'
              AND (categorie_paiement IS NULL OR categorie_paiement != 'FRAIS_ANNEXES')
              AND (tranche_code IS NULL OR tranche_code != 'FRAIS_ANNEXES')
            ORDER BY date_paiement ASC, id_paiement ASC
        ");
        $stmtPaiements->execute([$inscriptionCode]);
        $paiements = $stmtPaiements->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $montantPaye = 0;
        foreach ($paiements as $p) {
            $montantPaye += (float)$p['montant_paiement'];
        }

        $restePayer = max(0, $totalScolariteFixe - $montantPaye);

        $logoSrc = $this->encodeImageBase64([
            'public/assets/images/logo/logo_eicg.jpg',
            'assets/images/logo/logo_eicg.jpg'
        ]);

        $photoSrc = $this->encodeImageBase64([
            $ins['photo_etudiant'] ?? '',
            $ins['photo_inscription'] ?? '',
            'public/assets/images/placeholders/etudiant.png'
        ]);

        $nomComplet = trim(strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')));

        $dbTranches = [];
        if (!empty($codeScolarite)) {
            $stmtTr = $db->prepare("
                SELECT code_tranche, libelle_tranche, montant_tranche, date_limite 
                FROM " . TABLES::TRANCHES_SCOLARITE . " 
                WHERE scolarite_code = ? AND statut_tranche = 'actif'
                ORDER BY id_tranche ASC
            ");
            $stmtTr->execute([$codeScolarite]);
            $dbTranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        if (empty($dbTranches)) {
            $stmtTr = $db->prepare("
                SELECT code_tranche, libelle_tranche, montant_tranche, date_limite 
                FROM " . TABLES::TRANCHES_SCOLARITE . " 
                WHERE filiere_code = ? AND niveau_code = ? AND (annee_code = ? OR annee_code = '' OR annee_code IS NULL) AND statut_tranche = 'actif'
                ORDER BY id_tranche ASC
            ");
            $stmtTr->execute([$filiereCode, $nCode, $anneeCode]);
            $dbTranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $echeancier = [];
        if (!empty($dbTranches)) {
            $cumulAttenduTranche = 0;
            $cumulPrecedant = 0;
            foreach ($dbTranches as $tr) {
                $lib = $tr['libelle_tranche'];
                $mt = (float)$tr['montant_tranche'];
                $dt = !empty($tr['date_limite']) ? date('d/m/Y', strtotime($tr['date_limite'])) : 'À l\'échéance';

                $cumulAttenduTranche += $mt;

                $statutHtml = '';
                if ($montantPaye >= $cumulAttenduTranche) {
                    $statutHtml = '<span style="color: #166534; font-weight: bold;">✔ RÉGLÉ (100%)</span>';
                } elseif ($montantPaye > $cumulPrecedant) {
                    $payeSurTranche = $montantPaye - $cumulPrecedant;
                    $pct = ($mt > 0) ? min(100, round(($payeSurTranche / $mt) * 100)) : 0;
                    $resteTranche = max(0, $mt - $payeSurTranche);
                    $statutHtml = '<span style="color: #B45309; font-weight: bold;">⏳ PARTIEL (' . $pct . '%) - Reste: ' . number_format($resteTranche, 0, ',', ' ') . ' FCFA</span>';
                } else {
                    $statutHtml = '<span style="color: #B91C1C; font-weight: bold;">✖ NON RÉGLÉ (0%)</span>';
                }

                $echeancier[] = [
                    'libelle' => $lib,
                    'date_limite' => $dt,
                    'montant' => $mt,
                    'statut_html' => $statutHtml
                ];

                $cumulPrecedant = $cumulAttenduTranche;
            }
        }

        $dateInscription = !empty($ins['created_at_inscription']) ? date('d/m/Y', strtotime($ins['created_at_inscription'])) : '-';

        return [
            'logo_src' => $logoSrc,
            'photo_etudiant' => $photoSrc,
            'annee_libelle' => $ins['libelle_annee'] ?? '-',
            'matricule_etudiant' => $ins['matricule_etudiant'] ?? ($ins['code_etudiant'] ?? '-'),
            'code_inscription' => $inscriptionCode,
            'statut_affectation' => $isAffecte ? 'AFFECTÉ DE L\'ÉTAT' : 'PRIVÉ / NON-AFFECTÉ',
            'nom_prenom_etudiant' => $nomComplet,
            'filiere_libelle' => $ins['libelle_filiere'] ?? '-',
            'niveau_libelle' => $ins['libelle_niveau'] ?? '-',
            'classe_libelle' => $ins['libelle_classe'] ?? '-',
            'contact_etudiant' => $ins['telephone_etudiant'] ?? '-',
            'date_inscription' => $dateInscription,
            'total_scolarite' => $totalScolariteFixe,
            'montant_paye' => $montantPaye,
            'reste_payer' => $restePayer,
            'paiements' => $paiements,
            'echeancier' => $echeancier,
            'agent_caisse_nom' => !empty($currentUserName) ? $currentUserName : 'Caisse Principale GEICG'
        ];
    }

}
