<?php

class AnneeController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelAnnee();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_ANNEES');
        $this->loadView('../views/annees/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_ANNEES');
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_annee'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function validateDatesAnnee(string $dateDebut, string $dateFin): ?string
    {
        if (empty($dateDebut) || empty($dateFin)) {
            return "Les dates de début et de fin de l'année académique sont obligatoires.";
        }

        $timeDebut = strtotime($dateDebut);
        $timeFin = strtotime($dateFin);

        if (!$timeDebut || !$timeFin) {
            return "Les dates saisies ne sont pas valide (AAAA-MM-JJ).";
        }

        if ($timeFin <= $timeDebut) {
            $debutFormatted = date('d/m/Y', $timeDebut);
            $finFormatted = date('d/m/Y', $timeFin);
            return "Incohérence des dates : La date de fin ($finFormatted) doit être strictement postérieure à la date de début ($debutFormatted).";
        }

        if (($timeFin - $timeDebut) < (30 * 86400)) {
            return "Une année académique doit couvrir une durée minimale d'au moins 30 jours.";
        }

        return null;
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_ANNEES');
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_annee'])) {
            if (!$this->checkUnique('annees', 'libelle_annee', $data['libelle_annee'], 'Annee academique')) return;
        }

        // Validation stricte des dates
        $dateErr = $this->validateDatesAnnee($data['date_debut_annee'] ?? '', $data['date_fin_annee'] ?? '');
        if ($dateErr) {
            $this->error($dateErr);
            return;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        if (empty($data['code_annee'])) {
            $data['code_annee'] = $this->validator->generateCode('annees', 'code_annee', 'ANN-', 8);
        }
        
        // Toute nouvelle année académique est créée avec le statut 'planifie' par défaut (En préparation)
        $data['statut_annee'] = 'planifie';
        $data['created_at_annee'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        
        $filteredData = $this->model->filterData($data);
        $libelle = $data['libelle_annee'] ?? '';
        if ($this->model->create($filteredData)) {
            $this->success("L'année académique « {$libelle} » a été créée avec succès (statut initial : en préparation).");
        } else {
            $this->error($this->model->getLastError() ?: 'Erreur lors de la création de l\'année académique');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_ANNEES');
        $id = (int)$this->post('id_annee');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        
        $currentItem = $id ? $this->model->getById($id) : null;
        if (!$currentItem) {
            $this->error('Année académique introuvable');
            return;
        }

        if (($currentItem['statut_annee'] ?? '') !== 'planifie') {
            $this->error('Seule une année académique en préparation (planifiée) peut être modifiée.');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_annee'])) {
            if (!$this->checkUnique('annees', 'libelle_annee', $data['libelle_annee'], 'Annee academique', 'id_annee', $id)) return;
        }

        // Validation stricte des dates
        $dateDebut = $data['date_debut_annee'] ?? ($currentItem['date_debut_annee'] ?? '');
        $dateFin = $data['date_fin_annee'] ?? ($currentItem['date_fin_annee'] ?? '');
        $dateErr = $this->validateDatesAnnee($dateDebut, $dateFin);
        if ($dateErr) {
            $this->error($dateErr);
            return;
        }

        // Vérification des règles de dates si changement de statut
        $newStatus = $data['statut_annee'] ?? ($currentItem['statut_annee'] ?? 'planifie');
        $oldStatus = $currentItem['statut_annee'] ?? 'planifie';

        $evalItem = array_merge($currentItem, $data);

        if ($newStatus === 'actif' && $oldStatus !== 'actif') {
            $errorMsg = '';
            if (!$this->model->canActivate($evalItem, $errorMsg)) {
                $this->error($errorMsg);
                return;
            }
        } elseif ($newStatus === 'cloture' && $oldStatus === 'actif') {
            $errorMsg = '';
            if (!$this->model->canClose($evalItem, $errorMsg)) {
                $this->error($errorMsg);
                return;
            }
        }

        $data['updated_at_annee'] = date('Y-m-d H:i:s');
        $filteredData = $this->model->filterData($data);

        $libelle = $data['libelle_annee'] ?? ($currentItem['libelle_annee'] ?? '');

        if ($this->model->update($filteredData, $id)) {
            if ($newStatus === 'actif') {
                $this->model->setActiveYear($id);
                $updatedRow = $this->model->getById($id);
                if ($updatedRow) {
                    Session::set('annee_active_code', $updatedRow['code_annee']);
                    Session::set('annee_active_libelle', $updatedRow['libelle_annee']);
                }
            }
            $this->success("L'année académique « {$libelle} » a été modifiée avec succès !");
        } else {
            $this->error($this->model->getLastError() ?: 'Erreur lors de la modification de l\'année académique');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_ANNEES');
        $id = (int)$this->post('id');
        $item = $id ? $this->model->getById($id) : null;
        if ($item) {
            $currentStatus = $item['statut_annee'] ?? 'inactif';
            $errorMsg = '';

            // 1. Tentative d'activation : la date de début doit être arrivée
            if ($currentStatus !== 'actif') {
                if (!$this->model->canActivate($item, $errorMsg)) {
                    $this->error($errorMsg);
                    return;
                }
            } 
            // 2. Tentative de clôture / désactivation : la date de fin doit être arrivée
            else {
                if (!$this->model->canClose($item, $errorMsg)) {
                    $this->error($errorMsg);
                    return;
                }
            }

            if ($this->model->toggleStatus($id)) {
                $activeYear = $this->model->getActiveYear();
                if ($activeYear) {
                    Session::set('annee_active_code', $activeYear['code_annee']);
                    Session::set('annee_active_libelle', $activeYear['libelle_annee']);
                }
                $this->success('Statut mis à jour avec succès (Une seule année active à la fois) !', [
                    'reload' => true,
                    'activeYear' => $activeYear
                ]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Année académique introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_ANNEES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { 
                $this->renderNotFound("L'année académique demandée est introuvable.");
                return;
            }
            
            $anneeCode = $item['code_annee'];
            $stats = $this->model->getStatsForAnnee($anneeCode);
            $classes = $this->model->getClassesForAnnee($anneeCode);
            $semestres = $this->model->getSemestresForAnnee($anneeCode);
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("AnneeController::details error: " . $e->getMessage());
            $this->renderNotFound("L'année académique demandée est introuvable.");
            return;
        }
        $this->loadView('../views/annees/details.php', [
            'item' => $item, 
            'stats' => $stats,
            'classes' => $classes,
            'semestres' => $semestres,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        header('Location: ' . RACINE . 'annee/list');
        exit();
    }

    public function formulaire()
    {
        header('Location: ' . RACINE . 'annee/list');
        exit();
    }

    /**
     * Active une année académique pour la session PHP de l'utilisateur connecté
     */
    public function setSession()
    {
        $this->requireAuth();
        $code = trim($_POST['code_annee'] ?? ($_GET['code_annee'] ?? ''));

        if (empty($code)) {
            $this->json(['status' => 0, 'message' => 'Code année académique manquant']);
            return;
        }

        $annee = $this->model->getAnneeByCodeOrId($code);

        if ($annee) {
            Session::set('annee_active_code', $annee['code_annee']);
            Session::set('annee_active_libelle', $annee['libelle_annee']);
            
            $this->json([
                'status' => 1,
                'message' => "Année académique {$annee['libelle_annee']} activée avec succès pour votre session.",
                'annee' => [
                    'code' => $annee['code_annee'],
                    'libelle' => $annee['libelle_annee']
                ]
            ]);
        } else {
            $this->json(['status' => 0, 'message' => 'Année académique sélectionnée introuvable']);
        }
    }
}