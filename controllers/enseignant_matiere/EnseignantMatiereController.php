<?php

class EnseignantMatiereController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEnseignantMatiere();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_AFFECTATIONS');
        $annees = $this->getAccessibleAnnees();
        $niveaux = (new ModelNiveau())->getAll();
        $classes = (new ModelClasse())->getAll();
        
        if (isset($_GET['annee_code']) && !empty($_GET['annee_code'])) {
            $selectedAnneeCode = trim($_GET['annee_code']);
            foreach ($annees as $a) {
                if ($a['code_annee'] === $selectedAnneeCode) {
                    Session::set('annee_active_code', $a['code_annee']);
                    Session::set('annee_active_libelle', $a['libelle_annee']);
                    break;
                }
            }
        } else {
            $selectedAnneeCode = Session::get('annee_active_code');
        }

        $this->loadView('../views/enseignant_matiere/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_AFFECTATIONS');
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_enseignant_matiere'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_AFFECTATIONS');
        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['enseignant_code']) || empty($data['matiere_code']) || empty($data['classe_code'])) {
            $this->error('Veuillez sélectionner un enseignant, une matière et une classe.');
            return;
        }

        $data['coefficient'] = !empty($data['coefficient']) ? (float)$data['coefficient'] : 1.0;
        $data['statut_enseignant_matiere'] = $data['statut_enseignant_matiere'] ?? 'actif';
        $data['created_at_enseignant_matiere'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $this->success('Affectation de composition créée avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_AFFECTATIONS');
        $id = (int)$this->post('id_enseignant_matiere');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['coefficient'])) {
            $data['coefficient'] = (float)$data['coefficient'];
        }

        $data['updated_at_enseignant_matiere'] = date('Y-m-d H:i:s');
        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $this->success('Affectation modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_AFFECTATIONS');
        $id = $this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Item introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_AFFECTATIONS');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getDetailsById((int)$id);
            if (!$item) { header('Location: ' . RACINE . 'enseignant_matiere/list'); exit(); }

            $nbNotes = $this->model->getNbNotesForCourse($item['classe_code'], $item['matiere_code']);
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("EnseignantMatiereController::details error: " . $e->getMessage());
            $this->renderNotFound("L'affectation de composition demandée est introuvable.");
            return;
        }
        $this->loadView('../views/enseignant_matiere/details.php', [
            'item' => $item, 
            'nbNotes' => $nbNotes,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'enseignant_matiere/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'enseignant_matiere/list'); exit();
        }
        $this->loadView('../views/enseignant_matiere/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/enseignant_matiere/edit.php', ['item' => []]);
    }
}
