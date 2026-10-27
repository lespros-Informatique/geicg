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
        $this->requirePermission([PERMISSIONS::MANAGE_ARRIERES, PERMISSIONS::VIEW_ARRIERES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::MANAGE_PAIEMENTS, PERMISSIONS::VIEW_PAIEMENTS]);

        $annees = $this->getAccessibleAnnees();

        $niveauModel = new ModelNiveau();
        $niveaux = $niveauModel->getAll();

        $classeModel = new ModelClasse();
        $classes = $classeModel->getAll();

        $selectedAnneeCode = Session::get('annee_active_code');
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
        $this->requirePermission([PERMISSIONS::MANAGE_ARRIERES, PERMISSIONS::VIEW_ARRIERES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::MANAGE_PAIEMENTS, PERMISSIONS::VIEW_PAIEMENTS]);

        $anneeActiveCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
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
        $this->requirePermission([PERMISSIONS::MANAGE_ARRIERES, PERMISSIONS::VIEW_ARRIERES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::MANAGE_PAIEMENTS, PERMISSIONS::VIEW_PAIEMENTS]);

        $anneeActiveCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $stats = $this->model->getStats($anneeActiveCode);
        $this->json(['status' => 1, 'stats' => $stats]);
    }

    public function apiDetailEtudiant()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_ARRIERES, PERMISSIONS::VIEW_ARRIERES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::MANAGE_PAIEMENTS]);

        $etudiantCode = trim($_GET['etudiant_code'] ?? '');
        if (empty($etudiantCode)) {
            $this->json(['status' => 0, 'message' => 'Code étudiant requis']);
            return;
        }

        $anneeActiveCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $details = $this->model->getArrieresEtudiantDetail($etudiantCode, $anneeActiveCode);

        $this->json(['status' => 1, 'data' => $details]);
    }

    public function enregistrerReglement()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_ARRIERES, PERMISSIONS::MANAGE_PAIEMENTS, PERMISSIONS::RECORD_PAIEMENTS]);

        $inscriptionCode = trim($this->post('inscription_code') ?? '');
        $montant = (float)$this->post('montant_paiement');
        $modePaiement = trim($this->post('mode_paiement') ?? 'Espèces');
        $refPaiement = trim($this->post('reference_paiement') ?? '');
        $observations = trim($this->post('observations') ?? '');

        if (empty($inscriptionCode) || $montant <= 0) {
            $this->json(['status' => 0, 'message' => 'Veuillez sélectionner une inscription et saisir un montant valide (> 0).']);
            return;
        }

        try {
            // Vérifier l'inscription d'origine via modèle
            $inscr = $this->model->getInscriptionByCode($inscriptionCode);
            if (!$inscr) {
                $this->json(['status' => 0, 'message' => 'Inscription introuvable.']);
                return;
            }

            // Calculer le solde restant sur cette inscription via modèle
            $paye = $this->model->getTotalPayeByInscription($inscriptionCode);
            $soldeRestant = (float)$inscr['montant_scolarite_inscription'] - $paye;

            if ($soldeRestant <= 0) {
                $this->json(['status' => 0, 'message' => 'Cette inscription est déjà totalement soldée.']);
                return;
            }

            if ($montant > $soldeRestant + 0.01) {
                $this->json(['status' => 0, 'message' => 'Le montant saisie (' . number_format($montant, 0, ',', ' ') . ' FCFA) dépasse le solde restant dû (' . number_format($soldeRestant, 0, ',', ' ') . ' FCFA).']);
                return;
            }

            // Récupérer la session de caisse active de l'utilisateur via modèle
            $userCode = Auth::code();
            $sessionCaisseCode = $userCode ? $this->model->getActiveSessionForUser($userCode) : null;

            // Générer un code paiement unique
            $codePaiement = $this->validator->generateCode(TABLES::PAIEMENTS, 'code_paiement', 'PAI-ARR-', 8);
            $anneeActive = Session::get('annee_active_code') ?: $inscr['annee_code'];
            $etablissementCode = $inscr['etablissement_code'] ?? ($this->getActiveEtablissementCode());

            $this->model->recordArrierePayment([
                'code_paiement' => $codePaiement,
                'montant_paiement' => $montant,
                'reference_paiement' => $refPaiement ?: 'Apurement Arriéré',
                'observations' => $observations ?: 'Règlement d\'arriéré de scolarité pour l\'année ' . $inscr['annee_code'],
                'mode_paiement' => $modePaiement,
                'user_code' => $userCode,
                'annee_code' => $anneeActive,
                'etablissement_code' => $etablissementCode,
                'inscription_code' => $inscriptionCode,
                'session_caisse_code' => $sessionCaisseCode
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

    public function imprimerRecuArriere()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_ARRIERES, PERMISSIONS::VIEW_ARRIERES, PERMISSIONS::VIEW_PAIEMENTS]);

        $codePaiement = trim($_GET['code_paiement'] ?? $_GET['code'] ?? '');
        if (empty($codePaiement)) {
            $this->error("Code paiement manquant.");
            return;
        }

        $paiement = $this->model->getPaiementArriereDetail($codePaiement);
        if (!$paiement) {
            $this->error("Paiement d'arriéré introuvable.");
            return;
        }

        // Calculer les totaux de l'arriéré via modèle
        $totalPaye = $this->model->getTotalPayeByInscription($paiement['inscription_code']);

        $totalDu = (float)$paiement['montant_scolarite_inscription'];
        $ancienRegle = $totalPaye - (float)$paiement['montant_paiement'];
        $restePayer = max(0, $totalDu - $totalPaye);

        require_once __DIR__ . '/../../core/PdfService.php';

        $data = [
          'reference_paiement' => $paiement['code_paiement'],
          'numero_recu' => $paiement['code_paiement'],
          'date_paiement' => date('d/m/Y H:i:s', strtotime($paiement['created_at'] ?? 'now')),
          'matricule_etudiant' => $paiement['matricule_etudiant'],
          'annee_origine' => $paiement['annee_origine'],
          'nom_prenom_etudiant' => mb_strtoupper(($paiement['nom_etudiant'] ?? '') . ' ' . ($paiement['prenom_etudiant'] ?? '')),
          'filiere_niveau' => trim(($paiement['libelle_filiere'] ?? '') . ' ' . ($paiement['libelle_niveau'] ?? '')),
          'statut_affectation' => $paiement['statut_affectation_etudiant'] ?? 'AFFECTE',
          'total_arriere_du' => $totalDu,
          'cumul_ancien_regle' => $ancienRegle,
          'montant_verse' => (float)$paiement['montant_paiement'],
          'reste_a_payer_arriere' => $restePayer,
          'montant_lettres' => PdfService::numberToWordsFrench((float)$paiement['montant_paiement']),
          'mode_paiement' => $paiement['mode_paiement'],
          'reference_transaction' => $paiement['reference_paiement'],
          'caissier_nom' => trim(($paiement['prenom_user'] ?? '') . ' ' . ($paiement['nom_user'] ?? 'Agent Caisse')),
          'session_caisse_id' => $paiement['session_caisse_code'] ?? '-'
        ];

        $html = PdfService::renderTemplate('recu_arriere.php', $data);
        PdfService::generate($html, 'Recu_Arriere_' . $paiement['code_paiement'] . '.pdf', ['orientation' => 'P']);
    }
}
