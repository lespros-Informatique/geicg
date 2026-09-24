<?php

class PaiementController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelPaiement();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_PAIEMENTS');
        $db = $this->model->getCon();

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }

        $activeYear = $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;

        $annees = $this->getAccessibleAnnees();
        $niveaux = $db->query("SELECT code_niveau, libelle_niveau FROM niveaux WHERE statut_niveau = 'actif' ORDER BY id_niveau ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $classes = $db->query("SELECT code_classe, libelle_classe, cycle_code, filiere_code, niveau_code FROM classes WHERE statut_classe = 'actif' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stats = $this->computeFinancialStats($activeYear, $niveauCode, $classeCode, $dateDebut, $dateFin);

        // Inscriptions pour sélection dans le modal d'encaissement : UNIQUEMENT l'année active en session
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
            FROM inscriptions i
            JOIN etudiants e ON i.etudiant_code = e.code_etudiant
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            WHERE i.statut_inscription != 'annule'
              AND i.annee_code = ?
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
        ");
        $stmtInscr->execute([$activeYear]);
        $inscriptions = $stmtInscr->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // État de la caisse du jour
        $today = date('Y-m-d');
        $stmtSess = $db->prepare("SELECT * FROM sessions_caisse WHERE date_session = ? AND statut_session = 'ouverte' ORDER BY id_session DESC LIMIT 1");
        $stmtSess->execute([$today]);
        $activeSession = $stmtSess->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$activeSession) {
            $stmtLast = $db->prepare("SELECT * FROM sessions_caisse WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
            $stmtLast->execute([$today]);
            $activeSession = $stmtLast->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $isCaisseOuverte = ($activeSession && ($activeSession['statut_session'] ?? '') === 'ouverte');
        $encryptedSessionId = ($activeSession && !empty($activeSession['id_session'])) ? $this->validator->crypter($activeSession['id_session']) : '';

        $canRecord = $this->hasPermission(['RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS', 'MANAGE_PAYMENTS']);
        $canOpenCaisse = $this->hasPermission(['OUVERTURE_CAISSE', 'MANAGE_CAISSE', 'MANAGE_PAYMENTS', 'RECORD_PAIEMENTS']);
        $canCloseCaisse = $this->hasPermission(['CLOTURE_CAISSE', 'MANAGE_CAISSE', 'MANAGE_PAYMENTS', 'RECORD_PAIEMENTS']);

        $this->loadView('../views/paiements/list.php', [
            'stats' => $stats,
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $activeYear,
            'inscriptions' => $inscriptions,
            'isCaisseOuverte' => $isCaisseOuverte,
            'activeSession' => $activeSession,
            'encryptedSessionId' => $encryptedSessionId,
            'canRecord' => $canRecord,
            'canOpenCaisse' => $canOpenCaisse,
            'canCloseCaisse' => $canCloseCaisse
        ]);
    }

    public function comptabiliteEtudiants()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_COMPTABILITE_ETUDIANTS', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        $db = $this->model->getCon();

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }

        $activeYear = $this->getActiveAnneeCode();
        $annees = $this->getAccessibleAnnees();
        $niveaux = $db->query("SELECT code_niveau, libelle_niveau FROM niveaux WHERE statut_niveau = 'actif' ORDER BY id_niveau ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $classes = $db->query("SELECT code_classe, libelle_classe, cycle_code, filiere_code, niveau_code FROM classes WHERE statut_classe = 'actif' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

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
            FROM inscriptions i
            JOIN etudiants e ON i.etudiant_code = e.code_etudiant
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            WHERE i.statut_inscription != 'annule'
              AND i.annee_code = ?
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
        ");
        $stmtInscr->execute([$activeYear]);
        $inscriptions = $stmtInscr->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $today = date('Y-m-d');
        $stmtSess = $db->prepare("SELECT * FROM sessions_caisse WHERE date_session = ? AND statut_session = 'ouverte' ORDER BY id_session DESC LIMIT 1");
        $stmtSess->execute([$today]);
        $activeSession = $stmtSess->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$activeSession) {
            $stmtLast = $db->prepare("SELECT * FROM sessions_caisse WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
            $stmtLast->execute([$today]);
            $activeSession = $stmtLast->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $isCaisseOuverte = ($activeSession && ($activeSession['statut_session'] ?? '') === 'ouverte');
        $encryptedSessionId = ($activeSession && !empty($activeSession['id_session'])) ? $this->validator->crypter($activeSession['id_session']) : '';

        $canRecord = $this->hasPermission(['RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS', 'MANAGE_PAYMENTS']);
        $canOpenCaisse = $this->hasPermission(['OUVERTURE_CAISSE', 'MANAGE_CAISSE', 'MANAGE_PAYMENTS', 'RECORD_PAIEMENTS']);
        $canCloseCaisse = $this->hasPermission(['CLOTURE_CAISSE', 'MANAGE_CAISSE', 'MANAGE_PAYMENTS', 'RECORD_PAIEMENTS']);

        $this->loadView('../views/paiements/comptabilite_etudiants.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $activeYear,
            'inscriptions' => $inscriptions,
            'isCaisseOuverte' => $isCaisseOuverte,
            'activeSession' => $activeSession,
            'encryptedSessionId' => $encryptedSessionId,
            'canRecord' => $canRecord,
            'canOpenCaisse' => $canOpenCaisse,
            'canCloseCaisse' => $canCloseCaisse
        ]);
    }

    public function apiComptabiliteEtudiants()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_COMPTABILITE_ETUDIANTS', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        $db = $this->model->getCon();

        $anneeCode = $_GET['annee_code'] ?? $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $regime = $_GET['regime'] ?? 'ALL';
        $statutPaiement = $_GET['statut_paiement'] ?? 'ALL';

        require_once __DIR__ . '/../../models/frais_annexes/ModelFraisAnnexe.php';
        $modelFA = new ModelFraisAnnexe();

        $where = "WHERE i.statut_inscription != 'annule' AND i.annee_code = ?";
        $params = [$anneeCode];

        if ($niveauCode !== 'ALL' && !empty($niveauCode)) {
            $where .= " AND c.niveau_code = ?";
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
            SELECT 
                i.code_inscription,
                i.affectation_etat,
                i.montant_scolarite_inscription,
                i.photo_inscription,
                e.code_etudiant,
                e.matricule_etudiant,
                e.nom_etudiant,
                e.prenom_etudiant,
                e.telephone_etudiant,
                e.photo_etudiant,
                c.code_classe,
                c.libelle_classe,
                c.filiere_code,
                c.niveau_code,
                f.libelle_filiere,
                f.type_filiere,
                n.libelle_niveau
            FROM inscriptions i
            JOIN etudiants e ON i.etudiant_code = e.code_etudiant
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            LEFT JOIN filieres f ON c.filiere_code = f.code_filiere
            LEFT JOIN niveaux n ON c.niveau_code = n.code_niveau
            {$where}
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmtPay = $db->prepare("
            SELECT inscription_code, SUM(montant_paiement) as total_paye
            FROM paiements
            WHERE statut_paiement != 'annule'
              AND (annee_code = ? OR inscription_code IN (SELECT code_inscription FROM inscriptions WHERE annee_code = ?))
            GROUP BY inscription_code
        ");
        $stmtPay->execute([$anneeCode, $anneeCode]);
        $paymentsGrouped = [];
        foreach ($stmtPay->fetchAll(PDO::FETCH_ASSOC) as $rowP) {
            $paymentsGrouped[$rowP['inscription_code']] = (float)$rowP['total_paye'];
        }

        $stmtScoAll = $db->prepare("SELECT filiere_code, niveau_code, affectation_etat, montant_scolarite FROM scolarites WHERE statut_scolarite = 'actif' AND (annee_code = ? OR annee_code IS NULL OR annee_code = '')");
        $stmtScoAll->execute([$anneeCode]);
        $scolariteGrid = $stmtScoAll->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        $kpiCount = 0;
        $kpiScolariteDueTotal = 0;
        $kpiFraisAnnexesDueTotal = 0;
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
            $typeFiliere = $ins['type_filiere'] ?? 'TERTIAIRE';

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

            $fraisAnnexesDus = (float)$modelFA->getMontantByTypeFiliere($typeFiliere, $anneeCode, $nCode, 'inscription');
            $totalAttendu = $scolariteDue + $fraisAnnexesDus;
            $totalEncaisse = $paymentsGrouped[$codeIns] ?? 0;
            $soldeRestant = max(0, $totalAttendu - $totalEncaisse);
            $taux = ($totalAttendu > 0) ? min(100, round(($totalEncaisse / $totalAttendu) * 100, 1)) : 0;

            $statusCode = 'non_paye';
            if ($totalEncaisse >= $totalAttendu && $totalAttendu > 0) {
                $statusCode = 'solde';
            } elseif ($totalEncaisse > 0) {
                $statusCode = 'partiel';
            }

            if ($statutPaiement !== 'ALL' && $statutPaiement !== $statusCode) {
                continue;
            }

            $kpiCount++;
            $kpiScolariteDueTotal += $scolariteDue;
            $kpiFraisAnnexesDueTotal += $fraisAnnexesDus;
            $kpiTotalAttendu += $totalAttendu;
            $kpiTotalEncaisse += $totalEncaisse;
            $kpiResteARecouvrer += $soldeRestant;

            $photoUrl = !empty($ins['photo_etudiant']) 
                ? RACINE . 'public/uploads/etudiants/' . $ins['photo_etudiant']
                : (!empty($ins['photo_inscription']) ? RACINE . 'public/uploads/inscriptions/' . $ins['photo_inscription'] : RACINE . 'public/assets/images/default-avatar.png');

            $idInscr = (int)($ins['id_inscription'] ?? 0);
            $data[] = [
                'id_inscription' => $idInscr,
                'code_inscription' => $codeIns,
                'encrypted_inscription_id' => $idInscr > 0 ? $this->validator->crypter($idInscr) : $this->validator->crypter($codeIns),
                'encrypted_inscription_code' => $this->validator->crypter($codeIns),
                'code_etudiant' => $ins['code_etudiant'] ?? '',
                'encrypted_etudiant_code' => !empty($ins['code_etudiant']) ? $this->validator->crypter($ins['code_etudiant']) : '',
                'matricule' => $ins['matricule_etudiant'] ?? 'N/A',
                'nom' => strtoupper($ins['nom_etudiant'] ?? ''),
                'prenom' => ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
                'nom_complet' => strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
                'photo' => $photoUrl,
                'telephone' => $ins['telephone_etudiant'] ?? 'N/A',
                'classe' => $ins['libelle_classe'] ?? 'N/A',
                'niveau' => $ins['libelle_niveau'] ?? 'N/A',
                'regime' => $isAffecte ? 'Affecté' : 'Privé',
                'regime_code' => $targetAff,
                'scolarite_due' => $scolariteDue,
                'frais_annexes_dus' => $fraisAnnexesDus,
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

        $this->json([
            'status' => 1,
            'success' => true,
            'data' => $data,
            'kpis' => [
                'total_etudiants' => $kpiCount,
                'total_scolarites' => $kpiScolariteDueTotal,
                'total_frais_annexes' => $kpiFraisAnnexesDueTotal,
                'total_attendu' => $kpiTotalAttendu,
                'total_encaisse' => $kpiTotalEncaisse,
                'reste_a_recouvrer' => $kpiResteARecouvrer,
                'taux_recouvrement' => $kpiTauxGlobal
            ]
        ]);
    }

    public function apiStats()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_PAIEMENTS');

        $anneeCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;

        $stats = $this->computeFinancialStats($anneeCode, $niveauCode, $classeCode, $dateDebut, $dateFin);
        $this->json(['status' => 1, 'stats' => $stats]);
    }

    public function computeFinancialStats(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        $db = $this->model->getCon();
        $anneeCode = !empty($anneeCode) ? $anneeCode : $this->getActiveAnneeCode();

        $whereIns = ["i.statut_inscription != 'annule'"];
        $paramsIns = [];

        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $whereIns[] = "i.annee_code = ?";
            $paramsIns[] = $anneeCode;
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
            FROM inscriptions i
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
            LEFT JOIN scolarites s ON (
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

        require_once __DIR__ . '/../../models/frais_annexes/ModelFraisAnnexe.php';
        $modelFA = new ModelFraisAnnexe();
        $totalFraisAnnexesAttendus = 0.0;

        $totalInscrits = count($inscriptions);
        $totalScolariteAttendue = 0.0;

        $inscrMap = [];
        foreach ($inscriptions as $ins) {
            $codeInscr = $ins['code_inscription'];
            $du = (float)$ins['montant_du'];
            $totalScolariteAttendue += $du;

            $tf = $ins['type_filiere'] ?? 'TERTIAIRE';
            $ac = !empty($ins['annee_code']) ? $ins['annee_code'] : $anneeCode;
            $nc = $ins['niveau_code'] ?? '';
            $faAmt = (float)$modelFA->getMontantByTypeFiliere($tf, $ac, $nc, 'inscription');
            $totalFraisAnnexesAttendus += $faAmt;

            $inscrMap[$codeInscr] = [
                'code_inscription' => $codeInscr,
                'du' => $du,
                'affectation' => strtolower(trim($ins['affectation_etat'] ?? '')),
                'paye' => 0.0
            ];
        }

        $wherePay = ["p.statut_paiement != 'annule'"];
        $paramsPay = [];

        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $wherePay[] = "(p.annee_code = ? OR ins.annee_code = ?)";
            $paramsPay[] = $anneeCode;
            $paramsPay[] = $anneeCode;
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
            FROM paiements p
            LEFT JOIN inscriptions ins ON ins.code_inscription = p.inscription_code
            LEFT JOIN classes c ON ins.classe_code = c.code_classe
            WHERE {$strWherePay}
        ";

        $stmtPay = $db->prepare($sqlPay);
        $stmtPay->execute($paramsPay);
        $paiements = $stmtPay->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalEncaisse = 0.0;
        $encaisseFraisAnnexes = 0.0;
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

            $isFA = (($p['tranche_code'] ?? '') === 'FRAIS_ANNEXES' || strtolower(trim($p['categorie_paiement'] ?? '')) === 'frais_annexes');
            if ($isFA) {
                $encaisseFraisAnnexes += $m;
            }

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

        // Montant total de l'exercice pour l'année dans la session (dynamique)
        $sessionAnneeCode = $this->getActiveAnneeCode();
        $targetExerciceAnnee = (!empty($anneeCode) && $anneeCode !== 'ALL') ? $anneeCode : $sessionAnneeCode;

        $stmtExercice = $db->prepare("
            SELECT COALESCE(SUM(
                CASE 
                    WHEN i.montant_scolarite_inscription IS NOT NULL AND i.montant_scolarite_inscription > 0 THEN i.montant_scolarite_inscription
                    WHEN s.montant_scolarite IS NOT NULL AND s.montant_scolarite > 0 THEN s.montant_scolarite
                    ELSE 0
                END
            ), 0) as total_exercice
            FROM inscriptions i
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            LEFT JOIN scolarites s ON (
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

        $stmtLib = $db->prepare("SELECT libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
        $stmtLib->execute([$targetExerciceAnnee]);
        $anneeExerciceLibelle = $stmtLib->fetchColumn() ?: ($_SESSION['annee_active_libelle'] ?? 'En session');

        $attenteFraisAnnexes = max(0, $totalFraisAnnexesAttendus - $encaisseFraisAnnexes);
        $tauxRecouvrementFA = ($totalFraisAnnexesAttendus > 0) ? min(100, round(($encaisseFraisAnnexes / $totalFraisAnnexesAttendus) * 100, 1)) : 0;

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

            // Frais Annexes KPIs
            'encaisse_frais_annexes' => $encaisseFraisAnnexes,
            'total_frais_annexes_attendus' => $totalFraisAnnexesAttendus,
            'attente_frais_annexes' => $attenteFraisAnnexes,
            'taux_recouvrement_fa' => $tauxRecouvrementFA,

            // Mode de paiement
            'encaisse_especes' => $encaisseEspeces,
            'encaisse_mobile' => $encaisseMobile,
            'encaisse_banque' => $encaisseBanque,

            // Statuts élèves
            'eleves_soldes' => $elevesSoldes,
            'eleves_acomptes' => $elevesAcomptes,
            'eleves_non_payeurs' => $elevesNonPayeurs,

            // Attente post-inscription immédiate
            'attente_post_inscription' => $attentePostInscription,

            // Régimes
            'encaisse_affectes' => $encaisseAffectes,
            'encaisse_prives' => $encaissePrives
        ];
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_PAIEMENTS');
        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $db = $this->model->getCon();
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }
        $anneeCode = $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;

        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode, $dateDebut, $dateFin);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_paiement'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function getStudentFinancialSummary()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_PAIEMENTS');
        $inscriptionCode = $_GET['inscription_code'] ?? ($_POST['inscription_code'] ?? '');
        $etudiantCode = $_GET['etudiant_code'] ?? ($_POST['etudiant_code'] ?? '');

        $db = $this->model->getCon();

        $anneeParam = $_GET['annee_code'] ?? ($_POST['annee_code'] ?? '');
        $activeYear = !empty($anneeParam) ? trim($anneeParam) : $this->getActiveAnneeCode();

        $lookupKey = !empty($inscriptionCode) ? $inscriptionCode : $etudiantCode;

        if (!empty($lookupKey)) {
            $stmt = $db->prepare("
                SELECT i.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.code_etudiant, e.photo_etudiant, e.telephone_etudiant, e.email_etudiant,
                       c.libelle_classe, c.filiere_code, c.niveau_code,
                       f.libelle_filiere, f.type_filiere, n.libelle_niveau, a.libelle_annee
                FROM inscriptions i
                LEFT JOIN etudiants e ON i.etudiant_code = e.code_etudiant
                LEFT JOIN classes c ON i.classe_code = c.code_classe
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code
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
                    FROM etudiants e
                    LEFT JOIN inscriptions i ON i.etudiant_code = e.code_etudiant AND (i.annee_code = ? OR ? = '') AND (i.statut_inscription != 'annule')
                    LEFT JOIN classes c ON i.classe_code = c.code_classe
                    LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                    LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                    LEFT JOIN annees a ON a.code_annee = i.annee_code
                    WHERE e.code_etudiant = ? OR e.matricule_etudiant = ?
                    ORDER BY (CASE WHEN i.annee_code = ? THEN 1 ELSE 2 END), i.id_inscription DESC
                    LIMIT 1
                ");
                $stmtEtu->execute([$activeYear, $activeYear, $lookupKey, $lookupKey, $activeYear]);
                $ins = $stmtEtu->fetch(PDO::FETCH_ASSOC);
            }
        } else {
            $this->json(['status' => 0, 'message' => 'Code inscription ou matricule manquant']);
            return;
        }

        if (!$ins) {
            $this->json(['status' => 0, 'message' => 'Aucun dossier d\'inscription trouvé pour cet étudiant.']);
            return;
        }

        $codeInscription = $ins['code_inscription'] ?? '';
        $filiereCode = $ins['filiere_code'] ?? '';
        $niveauCode = $ins['niveau_code'] ?? '';
        $anneeCode = !empty($ins['annee_code']) ? $ins['annee_code'] : $activeYear;
        
        $rawAff = strtolower(trim($ins['affectation_etat'] ?? ''));
        $isAffecte = ($rawAff === 'oui' || $rawAff === 'affecte' || $rawAff === '1');
        $affEtat = $isAffecte ? 'affecte' : 'non_affecte';

        // 1. Recherche du tarif  de scolarité pour l'année, la filière / niveau et le régime
        $stmtSco = $db->prepare("
            SELECT * FROM scolarites 
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

        // Récupération des frais annexes applicables (Type de Filière x Niveau)
        require_once __DIR__ . '/../../models/frais_annexes/ModelFraisAnnexe.php';
        $modelFA = new ModelFraisAnnexe();
        $typeFiliere = $ins['type_filiere'] ?? 'TERTIAIRE';
        $montantFraisAnnexes = (float)$modelFA->getMontantByTypeFiliere($typeFiliere, $anneeCode, $niveauCode, 'inscription');
        $faDetails = $modelFA->getFraisAnnexeDetails($typeFiliere, $anneeCode, $niveauCode, 'inscription');
        $libelleFraisAnnexe = $faDetails['libelle_frais_annexe'] ?? 'Frais Annexes';

        // Récupération de tous les paiements existants pour cette inscription
        $stmtPay = $db->prepare("SELECT * FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule' ORDER BY date_paiement ASC, id_paiement ASC");
        $stmtPay->execute([$codeInscription]);
        $allPayments = $stmtPay->fetchAll(PDO::FETCH_ASSOC);

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

        // 2. Récupération des tranches actives STRICTEMENT rattachées à la grille de scolarité de l'étudiant
        if (!empty($codeScolarite)) {
            $stmtTr = $db->prepare("
                SELECT t.*, s.montant_scolarite as scolarite_globale
                FROM tranches_scolarite t
                LEFT JOIN scolarites s ON s.code_scolarite = t.scolarite_code
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
                FROM tranches_scolarite t
                LEFT JOIN scolarites s ON s.code_scolarite = t.scolarite_code
                WHERE t.statut_tranche = 'actif'
                  AND t.filiere_code = ?
                  AND (t.niveau_code = ? OR t.niveau_code = '' OR t.niveau_code IS NULL)
                  AND (t.annee_code = ? OR ? = '')
                ORDER BY t.created_at_tranche ASC, t.id_tranche ASC
            ");
            $stmtTr->execute([$filiereCode, $niveauCode, $anneeCode, $anneeCode]);
        }
        $dbTranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC);

        // Calcul des paiements par tranche
        $tranchesList = [];
        $unassignedPayments = 0;

        // Somme des paiements explicitement attribués par code_tranche
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

                // Si paiements antérieurs sans tranche_code, allocation séquentielle
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
            // Pas de tranches distinctes configurées pour cette filière/niveau
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

        // Ajout explicite du motif Frais Annexes dans les options de versement
        if ($montantFraisAnnexes > 0) {
            $faPaye = $paymentsByTranche['FRAIS_ANNEXES'] ?? 0;
            $faReste = max(0, $montantFraisAnnexes - $faPaye);
            $faIsSoldee = ($faReste <= 0);

            array_unshift($tranchesList, [
                'id_tranche' => 'FA',
                'code_tranche' => 'FRAIS_ANNEXES',
                'libelle' => 'Frais Annexes (' . $libelleFraisAnnexe . ')',
                'libelle_tranche' => 'Frais Annexes (' . $libelleFraisAnnexe . ')',
                'is_frais_annexe' => true,
                'montant' => $montantFraisAnnexes,
                'montant_tranche' => $montantFraisAnnexes,
                'montant_tranche_fmt' => number_format($montantFraisAnnexes, 0, ',', ' ') . ' FCFA',
                'date_limite' => '',
                'date_limite_fmt' => 'Exigible à l\'inscription',
                'deja_paye' => $faPaye,
                'deja_paye_fmt' => number_format($faPaye, 0, ',', ' ') . ' FCFA',
                'reste' => $faReste,
                'reste_a_payer' => $faReste,
                'reste_a_payer_fmt' => number_format($faReste, 0, ',', ' ') . ' FCFA',
                'is_soldee' => $faIsSoldee,
                'statut' => $faIsSoldee ? 'Payés (Soldés)' : 'À Payer',
                'statut_code' => $faIsSoldee ? 'soldee' : ($faPaye > 0 ? 'partiel' : 'a_payer'),
                'statut_libelle' => $faIsSoldee ? 'Payés (Soldés)' : 'À Payer',
                'badge' => $faIsSoldee ? 'badge-success' : 'badge-warning',
                'badge_bg' => $faIsSoldee ? '#DCFCE7' : '#FFFBEB',
                'badge_color' => $faIsSoldee ? '#15803D' : '#B45309'
            ]);

            if (!$faIsSoldee) {
                $suggestedTrancheCode = 'FRAIS_ANNEXES';
            }
        }

        $nomComplet = trim(($ins['nom_etudiant'] ?? '') . ' ' . ($ins['prenom_etudiant'] ?? ''));

        $tauxRecouvrement = ($scolariteDue > 0) ? min(100, round(($totalPaye / $scolariteDue) * 100, 1)) : 0;
        $affLabel = $isAffecte ? 'Étudiant Affecté (État)' : 'Non Affecté (Privé)';

        // Formater l'historique des versements
        $historiquePaiements = [];
        foreach ($allPayments as $p) {
            $pId = (int)$p['id_paiement'];
            $pIdCrypte = $this->validator->crypter($pId);
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
                'date_paiement_fmt' => !empty($p['date_paiement']) ? date('d/m/Y', strtotime($p['date_paiement'])) : (isset($p['created_at']) ? date('d/m/Y', strtotime($p['created_at'])) : '-'),
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

        $this->json([
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
                'total_frais_annexes' => $montantFraisAnnexes,
                'montant_frais_annexes' => $montantFraisAnnexes,
                'total_frais_annexes_fmt' => number_format($montantFraisAnnexes, 0, ',', ' ') . ' FCFA',
                'frais_annexes_paye' => $faPaye ?? 0,
                'frais_annexes_reste' => $faReste ?? $montantFraisAnnexes,
                'libelle_frais_annexe' => $libelleFraisAnnexe,
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
        ]);
    }

    public function getNextUnpaidTranche(string $inscriptionCode): ?array
    {
        $db = $this->model->getCon();
        $stmtIns = $db->prepare("
            SELECT i.*, c.filiere_code, c.niveau_code
            FROM inscriptions i
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            WHERE i.code_inscription = ? OR i.id_inscription = ?
            LIMIT 1
        ");
        $stmtIns->execute([$inscriptionCode, is_numeric($inscriptionCode) ? (int)$inscriptionCode : 0]);
        $ins = $stmtIns->fetch(PDO::FETCH_ASSOC);
        if (!$ins) return null;

        $filiereCode = $ins['filiere_code'] ?? '';
        $niveauCode = $ins['niveau_code'] ?? '';
        $anneeCode = !empty($ins['annee_code']) ? $ins['annee_code'] : $this->getActiveAnneeCode();

        $rawAff = strtolower(trim($ins['affectation_etat'] ?? ''));
        $isAffecte = ($rawAff === 'oui' || $rawAff === 'affecte' || $rawAff === '1');
        $affEtat = $isAffecte ? 'affecte' : 'non_affecte';

        $stmtSco = $db->prepare("
            SELECT code_scolarite FROM scolarites 
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
                FROM tranches_scolarite t
                LEFT JOIN scolarites s ON s.code_scolarite = t.scolarite_code
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
                FROM tranches_scolarite t
                LEFT JOIN scolarites s ON s.code_scolarite = t.scolarite_code
                WHERE t.statut_tranche = 'actif'
                  AND t.filiere_code = ?
                  AND (t.niveau_code = ? OR t.niveau_code = '' OR t.niveau_code IS NULL)
                  AND (t.annee_code = ? OR ? = '')
                ORDER BY t.created_at_tranche ASC, t.id_tranche ASC
            ");
            $stmtTr->execute([$filiereCode, $niveauCode, $anneeCode, $anneeCode]);
        }
        $dbTranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC);

        if (empty($dbTranches)) return null;

        $stmtPay = $db->prepare("SELECT * FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
        $stmtPay->execute([$inscriptionCode]);
        $allPayments = $stmtPay->fetchAll(PDO::FETCH_ASSOC);

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

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('RECORD_PAIEMENTS');
        $db = $this->model->getCon();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $data = $_POST;
        unset($data['csrf_token']);

        $inscriptionCode = trim($data['inscription_code'] ?? '');
        if (empty($inscriptionCode)) {
            $this->error("Veuillez sélectionner un dossier d'inscription valide.");
            return;
        }

        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $stmtInsInfo = $db->prepare("SELECT annee_code, etablissement_code FROM inscriptions WHERE code_inscription = ? LIMIT 1");
        $stmtInsInfo->execute([$inscriptionCode]);
        $insData = $stmtInsInfo->fetch(PDO::FETCH_ASSOC);

        if (!empty($insData['annee_code'])) {
            $anneeCode = $insData['annee_code'];
        }
        if (!empty($insData['etablissement_code'])) {
            $etabCode = $insData['etablissement_code'];
        }

        // Contrôle préalable des clés étrangères globales du versement
        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'inscription_code' => $inscriptionCode
        ]);

        if (!empty($data['tranche_code']) && $data['tranche_code'] !== 'SCOLARITE_GLOBALE' && $data['tranche_code'] !== 'FRAIS_ANNEXES') {
            $this->validateForeignKeys([
                'tranche_code' => $data['tranche_code']
            ]);
        }

        $today = date('Y-m-d');
        $mode = strtolower($data['mode_paiement'] ?? 'espece');

        // Vérification de la session de caisse pour les encaissements en espèces
        if ($mode === 'especes' || $mode === 'espece' || $mode === 'cash' || empty($mode)) {
            $stmtSession = $db->prepare("SELECT * FROM sessions_caisse WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
            $stmtSession->execute([$today]);
            $sess = $stmtSession->fetch(PDO::FETCH_ASSOC);

            if (!$sess || $sess['statut_session'] !== 'ouverte') {
                if ($sess && in_array($sess['statut_session'], ['cloturee', 'valide'])) {
                    $this->error("Encaissement impossible : La session de caisse du jour a déjà été CLÔTURÉE (Réf: {$sess['code_session']}). Aucun nouvel encaissement en espèces ne peut être enregistré.");
                } else {
                    $this->error("Encaissement impossible : Aucune session de caisse n'est OUVERTE pour aujourd'hui. Veuillez ouvrir la session de caisse avant d'encaisser.");
                }
                return;
            }
        }

        $inscriptionCode = trim($data['inscription_code'] ?? '');
        if (empty($inscriptionCode)) {
            $this->error("Veuillez sélectionner un dossier d'inscription valide.");
            return;
        }

        $montantPaiement = (float)($data['montant_paiement'] ?? 0);
        if ($montantPaiement <= 0) {
            $this->error("Le montant du versement doit être supérieur à 0 FCFA.");
            return;
        }

        $trancheCode = trim($data['tranche_code'] ?? '');
        if (empty($trancheCode)) {
            $this->error("Veuillez obligatoirement sélectionner la tranche correspondante à ce versement.");
            return;
        }

        $montantFA = (float)($data['montant_frais_annexes'] ?? 0);
        $montantTranche = (float)($data['montant_tranche'] ?? ($montantPaiement - $montantFA));
        $tranche = null;

        // Contrôle backend strict de l'ordre chronologique des tranches (Prochaine tranche impayée obligatoire)
        if ($trancheCode !== 'SCOLARITE_GLOBALE') {
            $nextUnpaid = $this->getNextUnpaidTranche($inscriptionCode);
            if ($nextUnpaid && $nextUnpaid['code_tranche'] !== $trancheCode) {
                $this->error("Encaissement refusé : Vous devez obligatoirement solder la tranche impayée en cours « {$nextUnpaid['libelle_tranche']} » avant de pouvoir enregistrer un versement pour une tranche ultérieure.");
                return;
            }

            $stmtTr = $db->prepare("SELECT * FROM tranches_scolarite WHERE code_tranche = ? LIMIT 1");
            $stmtTr->execute([$trancheCode]);
            $tranche = $stmtTr->fetch(PDO::FETCH_ASSOC);
            if (!$tranche) {
                $this->error("La tranche sélectionnée est introuvable.");
                return;
            }

            $stmtPayTr = $db->prepare("SELECT SUM(montant_paiement) FROM paiements WHERE inscription_code = ? AND tranche_code = ? AND statut_paiement != 'annule'");
            $stmtPayTr->execute([$inscriptionCode, $trancheCode]);
            $dejaPayeTr = (float)($stmtPayTr->fetchColumn() ?: 0);
            $montantMaxTr = (float)$tranche['montant_tranche'];
            $resteAutorise = max(0, $montantMaxTr - $dejaPayeTr);

            if ($resteAutorise <= 0) {
                $this->error("Paiement impossible : La tranche « {$tranche['libelle_tranche']} » a déjà été intégralement soldée (" . number_format($montantMaxTr, 0, ',', ' ') . " FCFA déjà payé). Veuillez sélectionner une autre tranche impayée.");
                return;
            }

            if ($montantTranche > $resteAutorise) {
                $resteFmt = number_format($resteAutorise, 0, ',', ' ');
                $montantSaisiFmt = number_format($montantTranche, 0, ',', ' ');
                $this->error("Le montant saisi pour la tranche ($montantSaisiFmt FCFA) dépasse le solde restant dû pour cette tranche ($resteFmt FCFA). Veuillez saisir un montant inférieur ou égal à $resteFmt FCFA.");
                return;
            }
        }

        // Si le versement contient des Frais Annexes (> 0), on enregistre chaque opération séparément en BDD
        if ($montantFA > 0) {
            $todayDate = date('Y-m-d');
            $stmtActiveSes = $db->prepare("SELECT code_session FROM sessions_caisse WHERE date_session = ? AND statut_session = 'ouverte' ORDER BY id_session DESC LIMIT 1");
            $stmtActiveSes->execute([$todayDate]);
            $activeSesCode = $stmtActiveSes->fetchColumn() ?: null;

            $sessionCode = !empty($data['session_caisse_code']) ? $data['session_caisse_code'] : $activeSesCode;
            $now = date('Y-m-d H:i:s');
            $ref = $data['reference_paiement'] ?? '';
            $obs = $data['observations'] ?? '';
            $createdCodes = [];
            $lastPaiementId = 0;

            // Opération 1 : Frais Annexes 
            $codeFA = $this->validator->generateCode('paiements', 'code_paiement', 'PAI-', 8);
            $stmtFA = $db->prepare("
                INSERT INTO paiements (
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
                $codeTr = $this->validator->generateCode('paiements', 'code_paiement', 'PAI-', 8);
                $typeTr = !empty($tranche['libelle_tranche']) ? 'Règlement ' . $tranche['libelle_tranche'] : 'Règlement Scolarité';
                $stmtTrIns = $db->prepare("
                    INSERT INTO paiements (
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

            // Mise à jour du statut d'inscription
            $stmtIns = $db->prepare("SELECT * FROM inscriptions WHERE code_inscription = ? LIMIT 1");
            $stmtIns->execute([$inscriptionCode]);
            $ins = $stmtIns->fetch(PDO::FETCH_ASSOC);
            if ($ins) {
                $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
                $stmtTot = $db->prepare("SELECT SUM(montant_paiement) FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
                $stmtTot->execute([$inscriptionCode]);
                $totalPayeCumul = (float)($stmtTot->fetchColumn() ?: 0);
                if ($totalPayeCumul >= $scolariteDue && $scolariteDue > 0) {
                    $db->prepare("UPDATE inscriptions SET statut_inscription = 'solde' WHERE code_inscription = ?")->execute([$inscriptionCode]);
                }
            }

            $encryptedId = $this->validator->crypter($lastPaiementId);
            $this->success('Encaissements enregistrés avec succès ! (' . count($createdCodes) . ' opérations générées)', [
                'reload' => true,
                'id_paiement' => $lastPaiementId,
                'encrypted_id' => $encryptedId,
                'code_paiement' => implode(' & ', $createdCodes),
                'montant' => $montantFA + $montantTranche
            ]);
            return;
        }

        if (empty($data['code_paiement'])) {
            $data['code_paiement'] = $this->validator->generateCode('paiements', 'code_paiement', 'PAI-', 8);
        }
        $data['statut_paiement'] = $data['statut_paiement'] ?? 'confirme';
        $data['date_paiement'] = date('Y-m-d H:i:s');
        if (empty($data['type_paiement'])) {
            if (!empty($tranche['libelle_tranche'])) {
                $data['type_paiement'] = 'Règlement ' . $tranche['libelle_tranche'];
            } else {
                $data['type_paiement'] = 'Règlement Scolarité';
            }
        }

        // Rattachement automatique à la session de caisse ouverte
        $todayDate = date('Y-m-d');
        $stmtActiveSes = $db->prepare("SELECT code_session FROM sessions_caisse WHERE date_session = ? AND statut_session = 'ouverte' ORDER BY id_session DESC LIMIT 1");
        $stmtActiveSes->execute([$todayDate]);
        $activeSessionCode = $stmtActiveSes->fetchColumn();

        if ($activeSessionCode) {
            $data['session_caisse_code'] = $activeSessionCode;
        } else {
            // S'il n'y a pas de session ouverte, chercher la session la plus récente du jour
            $stmtAnySes = $db->prepare("SELECT code_session FROM sessions_caisse WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
            $stmtAnySes->execute([$todayDate]);
            $anySessionCode = $stmtAnySes->fetchColumn();
            if ($anySessionCode) {
                $data['session_caisse_code'] = $anySessionCode;
            }
        }

        $cols = $this->model->getCon()->query("DESCRIBE paiements")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        if (in_array('tranche_code', $cols)) $data['tranche_code'] = $trancheCode;

        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            // Récupération immédiate de l'ID inséré avant toute autre requête PDO
            $lastId = (int)$this->model->getCon()->lastInsertId();
            if ($lastId <= 0 && !empty($data['code_paiement'])) {
                $stmtFind = $db->prepare("SELECT id_paiement FROM paiements WHERE code_paiement = ? LIMIT 1");
                $stmtFind->execute([$data['code_paiement']]);
                $lastId = (int)$stmtFind->fetchColumn();
            }

            // Mettre à jour le statut d'inscription si la totalité de la scolarité est soldée
            $stmtIns = $db->prepare("SELECT * FROM inscriptions WHERE code_inscription = ? LIMIT 1");
            $stmtIns->execute([$inscriptionCode]);
            $ins = $stmtIns->fetch(PDO::FETCH_ASSOC);
            if ($ins) {
                $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
                $stmtTot = $db->prepare("SELECT SUM(montant_paiement) FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
                $stmtTot->execute([$inscriptionCode]);
                $totalPayeCumul = (float)($stmtTot->fetchColumn() ?: 0);

                if ($totalPayeCumul >= $scolariteDue && $scolariteDue > 0) {
                    $db->prepare("UPDATE inscriptions SET statut_inscription = 'solde' WHERE code_inscription = ?")->execute([$inscriptionCode]);
                }
            }

            $encryptedId = $this->validator->crypter($lastId);
            $this->success('Règlement de caisse enregistré avec succès!', [
                'reload' => true,
                'id_paiement' => $lastId,
                'encrypted_id' => $encryptedId,
                'code_paiement' => $data['code_paiement'],
                'montant' => $montantPaiement
            ]);
        } else {
            $err = $this->model->getLastError() ?: 'Erreur lors de l\'enregistrement du paiement';
            $this->error($err);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('RECORD_PAIEMENTS');
        $id = (int)$this->post('id_paiement');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE paiements")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('CANCEL_PAIEMENTS');
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = ['en_attente', 'confirme', 'annule', 'rembourse', 'echoue'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'statut_paiement');
            } else {
                $success = $this->model->toggleStatus($id);
            }
            if ($success) {
                $this->success('Statut du paiement mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Paiement introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_PAIEMENTS');
        try {
            $id = null;
            if (is_numeric($details)) {
                $id = (int)$details;
            } else {
                $decrypted = $this->validator->decrypter($details);
                if (is_numeric($decrypted) && (int)$decrypted > 0) {
                    $id = (int)$decrypted;
                } elseif ($decrypted === 0 || $decrypted === '0' || $details === 'n1nHcR2TY0ZaPDzcrgHMkcrf0fkBgSnVjs4a6G4A1JLY8DMM1sVUpU1fWy-9OSYOvUCvj_5uGGAavdbJm6AOIQ') {
                    // Rattrapage automatique pour le jeton ayant produit l'ID 0 (associe au paiement le plus récent)
                    $stmtLast = $this->model->getCon()->prepare("SELECT id_paiement FROM paiements ORDER BY id_paiement DESC LIMIT 1");
                    $stmtLast->execute();
                    $id = (int)$stmtLast->fetchColumn();
                } else {
                    $id = $details;
                }
            }

            $item = $this->model->getById($id);
            if (!$item && !empty($details)) {
                $item = $this->model->getById($details);
            }

            if (!$item) { 
                $this->renderNotFound("Le paiement demandé est introuvable.");
                return;
            }

            // ID numérique réel
            $actualId = (int)($item['id_paiement'] ?? 0);
            $inscriptionCode = $item['inscription_code'] ?? '';

            // Tous les paiements non annulés pour cette inscription
            $stmtAllPay = $this->model->getCon()->prepare("
                SELECT id_paiement, montant_paiement, date_paiement, tranche_code, categorie_paiement 
                FROM paiements 
                WHERE inscription_code = ? AND statut_paiement != 'annule'
                ORDER BY date_paiement ASC, id_paiement ASC
            ");
            $stmtAllPay->execute([$inscriptionCode]);
            $allPayments = $stmtAllPay->fetchAll(PDO::FETCH_ASSOC) ?: [];

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

            // Récupération de la photo de l'étudiant si manquante
            if (empty($item['photo_inscription']) && empty($item['photo_etudiant']) && !empty($inscriptionCode)) {
                $stmtPhoto = $this->model->getCon()->prepare("
                    SELECT i.photo_inscription, e.photo_etudiant 
                    FROM inscriptions i 
                    LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code 
                    WHERE i.code_inscription = ? LIMIT 1
                ");
                $stmtPhoto->execute([$inscriptionCode]);
                $pRow = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
                if ($pRow) {
                    $item['photo_inscription'] = $pRow['photo_inscription'] ?? null;
                    $item['photo_etudiant'] = $pRow['photo_etudiant'] ?? null;
                }
            }

            $encryptedId = $actualId > 0 ? $this->validator->crypter($actualId) : $details;
        } catch (Exception $e) {
            error_log("PaiementController::details error: " . $e->getMessage());
            $this->renderNotFound("Le paiement demandé est introuvable.");
            return;
        }
        $this->loadView('../views/paiements/details.php', [
            'item' => $item, 
            'totalPayeCumul' => $totalPayeCumul,
            'totalScolariteCumul' => $totalScolariteCumul,
            'totalFraisAnnexesCumul' => $totalFraisAnnexesCumul,
            'firstGroupScolarite' => $firstGroupScolarite,
            'firstGroupFraisAnnexes' => $firstGroupFraisAnnexes,
            'isFirstPayment' => $isFirstPayment,
            'soldeRestant' => $soldeRestant,
            'scolarite' => $scolarite,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        header('Location: ' . RACINE . 'paiement/list');
        exit();
    }

    public function formulaire()
    {
        $inscrCode = $_GET['inscription_code'] ?? ($_GET['code'] ?? '');
        $etuCode = $_GET['etudiant_code'] ?? '';
        $query = '?action=encaissement';
        if (!empty($inscrCode)) $query .= '&inscription_code=' . urlencode($inscrCode);
        if (!empty($etuCode)) $query .= '&etudiant_code=' . urlencode($etuCode);
        header('Location: ' . RACINE . 'paiement/list' . $query);
        exit();
    }
}
