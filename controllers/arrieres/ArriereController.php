<?php

require_once __DIR__ . '/../../models/arrieres/ModelArriere.php';
require_once __DIR__ . '/../../models/annees/ModelAnnee.php';
require_once __DIR__ . '/../../models/classes/ModelClasse.php';
require_once __DIR__ . '/../../models/niveaux/ModelNiveau.php';
require_once __DIR__ . '/../../models/paiements/ModelPaiement.php';
require_once __DIR__ . '/../../models/sessions_caisse/ModelSessionCaisse.php';

class ArriereController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelArriere();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_ARRIERES', 'VIEW_ARRIERES', 'MANAGE_IMPAYES', 'MANAGE_PAIEMENTS', 'VIEW_PAIEMENTS']);

        $anneeModel = new ModelAnnee();
        $annees = $anneeModel->getAll();

        $niveauModel = new ModelNiveau();
        $niveaux = $niveauModel->getAll();

        $classeModel = new ModelClasse();
        $classes = $classeModel->getAll();

        $selectedAnneeCode = $_SESSION['annee_active_code'] ?? null;
        $stats = $this->model->getStats($selectedAnneeCode);

        $this->loadView('../views/arrieres/list.php', [
            'stats' => $stats,
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_ARRIERES', 'VIEW_ARRIERES', 'MANAGE_IMPAYES', 'MANAGE_PAIEMENTS', 'VIEW_PAIEMENTS']);

        $anneeActiveCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
        $anneeOrigineCode = $_GET['annee_origine_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $statutFilter = $_GET['statut'] ?? null;

        try {
            $items = $this->model->getArrieresList($anneeActiveCode, $anneeOrigineCode, $classeCode, $statutFilter);
            $this->json(['data' => $items]);
        } catch (Exception $e) {
            error_log("ArriereController::apiList error: " . $e->getMessage());
            $this->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    public function apiStats()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_ARRIERES', 'VIEW_ARRIERES', 'MANAGE_IMPAYES', 'MANAGE_PAIEMENTS', 'VIEW_PAIEMENTS']);

        $anneeActiveCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
        $stats = $this->model->getStats($anneeActiveCode);
        $this->json(['status' => 1, 'stats' => $stats]);
    }

    public function apiDetailEtudiant()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_ARRIERES', 'VIEW_ARRIERES', 'MANAGE_IMPAYES', 'MANAGE_PAIEMENTS']);

        $etudiantCode = trim($_GET['etudiant_code'] ?? '');
        if (empty($etudiantCode)) {
            $this->json(['status' => 0, 'message' => 'Code étudiant requis']);
            return;
        }

        $anneeActiveCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
        $details = $this->model->getArrieresEtudiantDetail($etudiantCode, $anneeActiveCode);

        $this->json(['status' => 1, 'data' => $details]);
    }

    public function enregistrerReglement()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['MANAGE_ARRIERES', 'MANAGE_PAIEMENTS', 'RECORD_PAIEMENTS']);

        $inscriptionCode = trim($this->post('inscription_code') ?? '');
        $montant = (float)$this->post('montant_paiement');
        $modePaiement = trim($this->post('mode_paiement') ?? 'Espèces');
        $refPaiement = trim($this->post('reference_paiement') ?? '');
        $observations = trim($this->post('observations') ?? '');

        if (empty($inscriptionCode) || $montant <= 0) {
            $this->json(['status' => 0, 'message' => 'Veuillez sélectionner une inscription et saisir un montant valide (> 0).']);
            return;
        }

        $db = $this->model->getCon();

        try {
            // Vérifier l'inscription d'origine
            $stmtInscr = $db->prepare("SELECT * FROM inscriptions WHERE code_inscription = ? LIMIT 1");
            $stmtInscr->execute([$inscriptionCode]);
            $inscr = $stmtInscr->fetch(PDO::FETCH_ASSOC);

            if (!$inscr) {
                $this->json(['status' => 0, 'message' => 'Inscription introuvable.']);
                return;
            }

            // Calculer le solde restant sur cette inscription
            $stmtP = $db->prepare("SELECT COALESCE(SUM(montant_paiement), 0) as paye FROM paiements WHERE inscription_code = ? AND statut_paiement = 'confirme'");
            $stmtP->execute([$inscriptionCode]);
            $paye = (float)($stmtP->fetchColumn() ?: 0);

            $soldeRestant = (float)$inscr['montant_scolarite_inscription'] - $paye;

            if ($soldeRestant <= 0) {
                $this->json(['status' => 0, 'message' => 'Cette inscription est déjà totalement soldée.']);
                return;
            }

            if ($montant > $soldeRestant + 0.01) {
                $this->json(['status' => 0, 'message' => 'Le montant saisie (' . number_format($montant, 0, ',', ' ') . ' FCFA) dépasse le solde restant dû (' . number_format($soldeRestant, 0, ',', ' ') . ' FCFA).']);
                return;
            }

            // Récupérer la session de caisse active de l'utilisateur
            $userCode = $_SESSION[USERS_AUTH]['user_code'] ?? ($_SESSION['user_code'] ?? null);
            $sessionCaisseCode = null;

            if ($userCode) {
                $stmtSession = $db->prepare("SELECT code_session FROM sessions_caisse WHERE user_code = ? AND statut_session = 'ouverte' ORDER BY id_session DESC LIMIT 1");
                $stmtSession->execute([$userCode]);
                $sessionCaisseCode = $stmtSession->fetchColumn() ?: null;
            }

            // Générer un code paiement unique
            $codePaiement = 'PAI-ARR-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            $anneeActive = $_SESSION['annee_active_code'] ?? $inscr['annee_code'];
            $etablissementCode = $inscr['etablissement_code'] ?? ($_SESSION['etablissement_code'] ?? '5454544456');

            $stmtIns = $db->prepare("
                INSERT INTO paiements 
                (code_paiement, montant_paiement, statut_paiement, reference_paiement, observations, type_paiement, mode_paiement, user_code, annee_code, etablissement_code, inscription_code, tranche_code, session_caisse_code)
                VALUES (?, ?, 'confirme', ?, ?, 'Règlement Arriéré Scolarité', ?, ?, ?, ?, ?, NULL, ?)
            ");

            $stmtIns->execute([
                $codePaiement,
                $montant,
                $refPaiement ?: 'Apurement Arriéré',
                $observations ?: 'Règlement d\'arriéré de scolarité pour l\'année ' . $inscr['annee_code'],
                $modePaiement,
                $userCode,
                $anneeActive,
                $etablissementCode,
                $inscriptionCode,
                $sessionCaisseCode
            ]);

            $this->json([
                'status' => 1,
                'message' => 'Règlement d\'arriéré enregistré avec succès !',
                'code_paiement' => $codePaiement,
                'montant' => $montant,
                'solde_apres' => max(0, $soldeRestant - $montant)
            ]);

        } catch (Exception $e) {
            error_log("ArriereController::enregistrerReglement error: " . $e->getMessage());
            $this->json(['status' => 0, 'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()]);
        }
    }
}
