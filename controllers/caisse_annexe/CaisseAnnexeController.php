<?php

class CaisseAnnexeController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelCaisseAnnexe();
    }

    /**
     * Page Principale : Bilan & Journal de la Caisse Annexe
     */
    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_CAISSE_ANNEXE', 'VIEW_PAIEMENTS', 'VIEW_FRAIS_ANNEXES', 'MANAGE_PAIEMENTS']);
        $db = $this->model->getCon();

        // Synchronisation de l'année académique si passée en paramètre GET
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
        $typeFiliere = $_GET['type_filiere'] ?? 'ALL';
        $modePaiement = $_GET['mode_paiement'] ?? 'ALL';
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;

        $annees = $this->getAccessibleAnnees();
        $niveaux = $db->query("SELECT code_niveau, libelle_niveau FROM niveaux WHERE statut_niveau = 'actif' ORDER BY id_niveau ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $classes = $db->query("SELECT code_classe, libelle_classe, cycle_code, filiere_code, niveau_code FROM classes WHERE statut_classe = 'actif' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $filieres = $db->query("SELECT code_filiere, libelle_filiere, type_filiere FROM filieres WHERE statut_filiere = 'actif' ORDER BY libelle_filiere ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Évaluation dynamique des statistiques financières pour les Frais Annexes
        $stats = $this->model->getFinancialStats($activeYear, $niveauCode, $classeCode, $typeFiliere, $dateDebut, $dateFin);

        // Étudiants inscrits pour la sélection dans le modal d'encaissement direct
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
                c.code_classe,
                f.libelle_filiere,
                f.type_filiere
            FROM inscriptions i
            JOIN etudiants e ON i.etudiant_code = e.code_etudiant
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            LEFT JOIN filieres f ON c.filiere_code = f.code_filiere
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

        $canRecord = $this->hasPermission(['MANAGE_CAISSE_ANNEXE', 'RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        $canOpenCaisse = $this->hasPermission(['OUVERTURE_CAISSE', 'MANAGE_CAISSE', 'RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        $canCloseCaisse = $this->hasPermission(['CLOTURE_CAISSE', 'MANAGE_CAISSE', 'RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);

        $this->loadView('../views/caisse_annexe/list.php', [
            'stats' => $stats,
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'filieres' => $filieres,
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

    /**
     * API JSON pour le tableau DataTables du journal de la Caisse Annexe
     */
    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_CAISSE_ANNEXE', 'VIEW_PAIEMENTS', 'VIEW_FRAIS_ANNEXES', 'MANAGE_PAIEMENTS']);

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
        $typeFiliere = $_GET['type_filiere'] ?? null;
        $modePaiement = $_GET['mode_paiement'] ?? null;
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;

        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode, $typeFiliere, $modePaiement, $dateDebut, $dateFin);
        $data = [];

        foreach ($items as $i) {
            $id = $i['id_paiement'];
            $idCrypte = $this->validator->crypter($id);
            $encInscrId = !empty($i['inscription_code']) ? $this->validator->crypter($i['inscription_code']) : '';

            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'encrypted_id' => $idCrypte,
                'encrypted_inscription_id' => $encInscrId,
                'montant_fmt' => number_format((float)$i['montant_paiement'], 0, ',', ' ') . ' FCFA'
            ]);
        }

        $this->json(['data' => $data]);
    }

    /**
     * API JSON de rafraîchissement dynamique des compteurs KPI
     */
    public function apiStats()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_CAISSE_ANNEXE', 'VIEW_PAIEMENTS', 'VIEW_FRAIS_ANNEXES']);

        $anneeCode = $_GET['annee_code'] ?? $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $typeFiliere = $_GET['type_filiere'] ?? null;
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;

        $stats = $this->model->getFinancialStats($anneeCode, $niveauCode, $classeCode, $typeFiliere, $dateDebut, $dateFin);
        $this->json(['status' => 1, 'stats' => $stats]);
    }

    /**
     * API de récupération de la fiche financière de l'étudiant pour les Frais Annexes
     */
    public function getStudentAnnexesSummary()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_CAISSE_ANNEXE', 'VIEW_PAIEMENTS', 'RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);

        $codeInscription = $_GET['inscription_code'] ?? ($_POST['inscription_code'] ?? '');
        if (empty($codeInscription)) {
            $this->json(['status' => 0, 'message' => 'Code inscription requis'], 400);
            return;
        }

        $db = $this->model->getCon();

        // Récupération de l'inscription et de l'étudiant
        $stmt = $db->prepare("
            SELECT 
                i.code_inscription, i.annee_code, i.affectation_etat, i.photo_inscription,
                e.code_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, 
                e.telephone_etudiant, e.email_etudiant, e.photo_etudiant,
                c.code_classe, c.libelle_classe, c.niveau_code,
                f.libelle_filiere, f.type_filiere,
                n.libelle_niveau, a.libelle_annee
            FROM inscriptions i
            JOIN etudiants e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN classes c ON c.code_classe = i.classe_code
            LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
            LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
            LEFT JOIN annees a ON a.code_annee = i.annee_code
            WHERE i.code_inscription = ?
            LIMIT 1
        ");
        $stmt->execute([$codeInscription]);
        $ins = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ins) {
            $this->json(['status' => 0, 'message' => 'Étudiant introuvable pour ce dossier'], 404);
            return;
        }

        $anneeCode = $ins['annee_code'] ?? $this->getActiveAnneeCode();
        $typeFiliere = strtoupper($ins['type_filiere'] ?? 'TOUT');
        $niveauCode = $ins['niveau_code'] ?? '';

        // Récupérer le tarif des frais annexes applicable
        require_once __DIR__ . '/../../models/frais_annexes/ModelFraisAnnexe.php';
        $modelFA = new ModelFraisAnnexe();
        $faDetails = $modelFA->getFraisAnnexeDetails($typeFiliere, $anneeCode, $niveauCode);

        $montantExigible = $faDetails ? (float)$faDetails['montant_frais_annexe'] : 0.0;
        $libelleFA = $faDetails ? ($faDetails['libelle_frais_annexe'] ?? 'Frais Annexes') : 'Frais Annexes Obligatoires';

        // Historique des versements déjà effectués pour les frais annexes
        $stmtPay = $db->prepare("
            SELECT * FROM paiements
            WHERE inscription_code = ?
              AND (categorie_paiement = 'FRAIS_ANNEXES' OR tranche_code = 'FRAIS_ANNEXES' OR LOWER(type_paiement) LIKE '%annexe%')
              AND statut_paiement != 'annule'
            ORDER BY date_paiement ASC
        ");
        $stmtPay->execute([$codeInscription]);
        $allPayments = $stmtPay->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $dejaPaye = 0;
        $historique = [];
        foreach ($allPayments as $p) {
            $mt = (float)$p['montant_paiement'];
            $dejaPaye += $mt;
            $historique[] = [
                'code_paiement' => $p['code_paiement'],
                'date' => !empty($p['date_paiement']) ? date('d/m/Y H:i', strtotime($p['date_paiement'])) : '-',
                'montant' => $mt,
                'montant_fmt' => number_format($mt, 0, ',', ' ') . ' FCFA',
                'mode' => ucfirst($p['mode_paiement'] ?? 'espece'),
                'reference' => $p['reference_paiement'] ?: '-'
            ];
        }

        $resteAPayer = max(0, $montantExigible - $dejaPaye);
        $isSolde = ($montantExigible > 0 && $resteAPayer <= 0);

        $photoPath = !empty($ins['photo_inscription']) ? trim($ins['photo_inscription']) : (!empty($ins['photo_etudiant']) ? trim($ins['photo_etudiant']) : '');
        $photoUrl = !empty($photoPath) ? RACINE . ltrim($photoPath, '/') : '';

        $this->json([
            'status' => 1,
            'data' => [
                'code_inscription' => $ins['code_inscription'],
                'matricule' => $ins['matricule_etudiant'] ?? '-',
                'nom_complet' => trim(($ins['nom_etudiant'] ?? '') . ' ' . ($ins['prenom_etudiant'] ?? '')),
                'photo_url' => $photoUrl,
                'classe' => $ins['libelle_classe'] ?? '-',
                'filiere' => $ins['libelle_filiere'] ?? '-',
                'type_filiere' => $typeFiliere,
                'niveau' => $ins['libelle_niveau'] ?? '-',
                'annee' => $ins['libelle_annee'] ?? '-',
                'libelle_frais_annexe' => $libelleFA,
                'montant_exigible' => $montantExigible,
                'montant_exigible_fmt' => number_format($montantExigible, 0, ',', ' ') . ' FCFA',
                'deja_paye' => $dejaPaye,
                'deja_paye_fmt' => number_format($dejaPaye, 0, ',', ' ') . ' FCFA',
                'reste_a_payer' => $resteAPayer,
                'reste_a_payer_fmt' => number_format($resteAPayer, 0, ',', ' ') . ' FCFA',
                'is_solde' => $isSolde,
                'historique' => $historique
            ]
        ]);
    }

    /**
     * Enregistre un encaissement direct de frais annexe
     */
    public function encaisser()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['MANAGE_CAISSE_ANNEXE', 'RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);

        $db = $this->model->getCon();
        $codeInscription = trim($_POST['inscription_code'] ?? '');
        $montant = (float)($_POST['montant_paiement'] ?? 0);
        $modePaiement = trim($_POST['mode_paiement'] ?? 'espece');
        $reference = trim($_POST['reference_paiement'] ?? '');
        $observations = trim($_POST['observations'] ?? '');

        if (empty($codeInscription)) {
            $this->error("Veuillez sélectionner un étudiant pour enregistrer le règlement.");
            return;
        }

        if ($montant <= 0) {
            $this->error("Le montant du règlement doit être supérieur à 0 FCFA.");
            return;
        }

        // Vérification de l'inscription
        $stmtInscr = $db->prepare("SELECT * FROM inscriptions WHERE code_inscription = ? LIMIT 1");
        $stmtInscr->execute([$codeInscription]);
        $inscription = $stmtInscr->fetch(PDO::FETCH_ASSOC);

        if (!$inscription) {
            $this->error("Dossier d'inscription introuvable.");
            return;
        }

        $activeYear = $inscription['annee_code'] ?? $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $userCode = $this->getCurrentUserCode() ?: 'SYSTEM';

        // Contrôle de session de caisse si paiement en espèces
        $today = date('Y-m-d');
        $sessionCode = null;
        if (strpos(strtolower($modePaiement), 'espece') !== false) {
            $stmtSess = $db->prepare("SELECT code_session FROM sessions_caisse WHERE date_session = ? AND statut_session = 'ouverte' ORDER BY id_session DESC LIMIT 1");
            $stmtSess->execute([$today]);
            $sessionCode = $stmtSess->fetchColumn() ?: null;

            if (!$sessionCode) {
                $this->error("Opération refusée : Aucune session de caisse n'est ouverte pour aujourd'hui. Veuillez ouvrir la caisse du jour pour enregistrer un versement en espèces.");
                return;
            }
        }

        // Génération du code paiement unique
        $codePaiement = $this->generateCode('paiements', 'code_paiement', 'REC-FA-', 6);

        try {
            $sql = "
                INSERT INTO paiements (
                    code_paiement, 
                    inscription_code, 
                    etablissement_code, 
                    annee_code, 
                    tranche_code,
                    montant_paiement, 
                    mode_paiement, 
                    reference_paiement, 
                    type_paiement, 
                    categorie_paiement, 
                    observations,
                    statut_paiement, 
                    date_paiement, 
                    session_caisse_code, 
                    user_code
                ) VALUES (
                    ?, ?, ?, ?, 'FRAIS_ANNEXES',
                    ?, ?, ?, 'Frais Annexes', 'FRAIS_ANNEXES', ?,
                    'confirme', NOW(), ?, ?
                )
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $codePaiement,
                $codeInscription,
                $etabCode,
                $activeYear,
                $montant,
                $modePaiement,
                $reference ?: null,
                $observations ?: 'Règlement Frais Annexes via Caisse Annexe',
                $sessionCode,
                $userCode
            ]);

            $newId = $db->lastInsertId();
            $encryptedId = $this->validator->crypter($newId);
            $encryptedInscrId = $this->validator->crypter($inscription['id_inscription'] ?? $codeInscription);

            $this->success("Encaissement des frais annexes enregistré avec succès !", [
                'code_paiement' => $codePaiement,
                'id_paiement' => $newId,
                'encrypted_id' => $encryptedId,
                'encrypted_inscription_id' => $encryptedInscrId
            ]);

        } catch (Exception $e) {
            $this->error("Erreur lors de l'enregistrement du versement : " . $e->getMessage());
        }
    }

    /**
     * Impression / Exportation du Bilan & Journal de la Caisse Annexe
     */
    public function imprimerRegistre()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_CAISSE_ANNEXE', 'VIEW_PAIEMENTS', 'VIEW_RAPPORTS_FINANCIERS']);

        $anneeCode = $_GET['annee_code'] ?? $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $typeFiliere = $_GET['type_filiere'] ?? null;
        $modePaiement = $_GET['mode_paiement'] ?? null;
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;

        $paiements = $this->model->getAll($anneeCode, $niveauCode, $classeCode, $typeFiliere, $modePaiement, $dateDebut, $dateFin);
        $stats = $this->model->getFinancialStats($anneeCode, $niveauCode, $classeCode, $typeFiliere, $dateDebut, $dateFin);

        $db = $this->model->getCon();
        $anneeLibelle = 'Toutes les années';
        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $stmtA = $db->prepare("SELECT libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$anneeCode]);
            $anneeLibelle = $stmtA->fetchColumn() ?: $anneeCode;
        }

        // Rendu de la vue d'impression épurée
        $this->loadView('../views/caisse_annexe/print.php', [
            'paiements' => $paiements,
            'stats' => $stats,
            'anneeLibelle' => $anneeLibelle,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ]);
    }
}
