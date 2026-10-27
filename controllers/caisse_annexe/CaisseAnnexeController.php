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
    /**
     * Page Principale : Bilan & Journal de la Caisse Annexe
     */
    public function list()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_CAISSE_ANNEXE, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::VIEW_FRAIS_ANNEXES, PERMISSIONS::MANAGE_PAIEMENTS]);

        // Synchronisation de l'année académique si passée en paramètre GET
        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $aRow = $this->model->getAnneeByCode($getAnnee);
            if ($aRow) {
                Session::set('annee_active_code', $aRow['code_annee']);
                Session::set('annee_active_libelle', $aRow['libelle_annee']);
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
        $niveaux = $this->model->getNiveauxActifs();
        $classes = $this->model->getClassesActives();
        $filieres = $this->model->getFilieresActives();

        // Évaluation dynamique des statistiques financières pour les Frais Annexes
        $stats = $this->model->getFinancialStats($activeYear, $niveauCode, $classeCode, $typeFiliere, $dateDebut, $dateFin);

        // Étudiants inscrits pour la sélection dans le modal d'encaissement direct
        $inscriptions = $this->model->getInscriptionsForYear($activeYear);

        // État de la caisse du jour
        $today = date('Y-m-d');
        $activeSession = $this->model->getOpenSessionForDate($today);

        $isCaisseOuverte = ($activeSession && ($activeSession['statut_session'] ?? '') === 'ouverte');
        $encryptedSessionId = ($activeSession && !empty($activeSession['id_session'])) ? $this->validator->crypter($activeSession['id_session']) : '';

        $canRecord = $this->hasPermission([PERMISSIONS::MANAGE_CAISSE_ANNEXE, PERMISSIONS::RECORD_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);
        $canOpenCaisse = $this->hasPermission([PERMISSIONS::OUVERTURE_CAISSE, PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::RECORD_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);
        $canCloseCaisse = $this->hasPermission([PERMISSIONS::CLOTURE_CAISSE, PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::RECORD_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);

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
        $this->requirePermission([PERMISSIONS::VIEW_CAISSE_ANNEXE, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::VIEW_FRAIS_ANNEXES, PERMISSIONS::MANAGE_PAIEMENTS]);

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $aRow = $this->model->getAnneeByCode($getAnnee);
            if ($aRow) {
                Session::set('annee_active_code', $aRow['code_annee']);
                Session::set('annee_active_libelle', $aRow['libelle_annee']);
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
        $this->requirePermission([PERMISSIONS::VIEW_CAISSE_ANNEXE, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::VIEW_FRAIS_ANNEXES]);

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
        $this->requirePermission([PERMISSIONS::VIEW_CAISSE_ANNEXE, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::RECORD_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);

        $codeInscription = $_GET['inscription_code'] ?? ($_POST['inscription_code'] ?? '');
        if (empty($codeInscription)) {
            $this->json(['status' => 0, 'message' => 'Code inscription requis'], 400);
            return;
        }

        // Récupération de l'inscription et de l'étudiant via modèle
        $ins = $this->model->getStudentAnnexeDetails($codeInscription);

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
        $allPayments = $this->model->getStudentPaymentsAnnexe($codeInscription);

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
        $this->requirePermission([PERMISSIONS::MANAGE_CAISSE_ANNEXE, PERMISSIONS::RECORD_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);

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

        // Vérification de l'inscription via modèle
        $inscription = $this->model->getInscriptionByCode($codeInscription);

        if (!$inscription) {
            $this->error("Dossier d'inscription introuvable.");
            return;
        }

        $activeYear = $inscription['annee_code'] ?? $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $userCode = Auth::code() ?: 'SYSTEM';

        // Contrôle de session de caisse si paiement en espèces
        $today = date('Y-m-d');
        $sessionCode = null;
        if (strpos(strtolower($modePaiement), 'espece') !== false) {
            $sess = $this->model->getOpenSessionForDate($today);
            $sessionCode = ($sess && ($sess['statut_session'] ?? '') === 'ouverte') ? ($sess['code_session'] ?? null) : null;

            if (!$sessionCode) {
                $this->error("Opération refusée : Aucune session de caisse n'est ouverte pour aujourd'hui. Veuillez ouvrir la caisse du jour pour enregistrer un versement en espèces.");
                return;
            }
        }

        // Génération du code paiement unique
        $codePaiement = $this->generateCode(TABLES::PAIEMENTS, 'code_paiement', 'REC-FA-', 6);

        try {
            $paymentData = [
                'code_paiement' => $codePaiement,
                'inscription_code' => $codeInscription,
                'etablissement_code' => $etabCode,
                'annee_code' => $activeYear,
                'tranche_code' => 'FRAIS_ANNEXES',
                'montant_paiement' => $montant,
                'mode_paiement' => $modePaiement,
                'reference_paiement' => $reference ?: null,
                'type_paiement' => 'Frais Annexes',
                'categorie_paiement' => 'FRAIS_ANNEXES',
                'observations' => $observations ?: 'Règlement Frais Annexes via Caisse Annexe',
                'statut_paiement' => 'confirme',
                'date_paiement' => date('Y-m-d H:i:s'),
                'session_caisse_code' => $sessionCode,
                'user_code' => $userCode
            ];

            $newId = $this->model->recordPayment($paymentData);
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
        $this->requirePermission([PERMISSIONS::VIEW_CAISSE_ANNEXE, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::VIEW_RAPPORTS_FINANCIERS]);

        $anneeCode = $_GET['annee_code'] ?? $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $typeFiliere = $_GET['type_filiere'] ?? null;
        $modePaiement = $_GET['mode_paiement'] ?? null;
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;

        $paiements = $this->model->getAll($anneeCode, $niveauCode, $classeCode, $typeFiliere, $modePaiement, $dateDebut, $dateFin);
        $stats = $this->model->getFinancialStats($anneeCode, $niveauCode, $classeCode, $typeFiliere, $dateDebut, $dateFin);

        $anneeLibelle = 'Toutes les années';
        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $aRow = $this->model->getAnneeByCode($anneeCode);
            $anneeLibelle = $aRow['libelle_annee'] ?? $anneeCode;
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
