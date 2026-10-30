<?php

class MatiereController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelMatiere();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_MATIERES);
        $this->loadView('../views/matieres/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_MATIERES);
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_matiere'];
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
        $this->requirePermission(PERMISSIONS::MANAGE_MATIERES);
        $data = $_POST;
        unset($data['csrf_token']);
        unset($data['id_matiere']);
        unset($data['id']);
        if (!empty($data['libelle_matiere'])) {
            if (!$this->checkUnique('matieres', 'libelle_matiere', $data['libelle_matiere'], 'Nom de la matiere')) return;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        if (empty($data['code_matiere'])) {
            $data['code_matiere'] = $this->validator->generateCode(TABLES::MATIERES, 'code_matiere', 'MAT-', 8);
        }
        $data['statut_matiere'] = $data['statut_matiere'] ?? 'actif';
        $data['created_at_matiere'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        $libelle = trim($data['libelle_matiere'] ?? '');
        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $msg = !empty($libelle) ? "La matière « {$libelle} » a été créée avec succès !" : "Matière créée avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la création de la matière';
            $this->error($msg);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_MATIERES);
        $id = (int)$this->post('id_matiere');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_matiere'])) {
            if (!$this->checkUnique('matieres', 'libelle_matiere', $data['libelle_matiere'], 'Nom de la matiere', 'id_matiere', $id)) return;
        }

        $libelle = trim($data['libelle_matiere'] ?? '');
        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $msg = !empty($libelle) ? "La matière « {$libelle} » a été modifiée avec succès !" : "Matière modifiée avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la modification de la matière';
            $this->error($msg);
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_MATIERES);
        $id = $this->post('id') ?: $this->post('id_matiere');
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
        $this->requirePermission(PERMISSIONS::VIEW_MATIERES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'matiere/list'); exit(); }

            $matiereCode = $item['code_matiere'];
            $affectations = $this->model->getAffectationsForMatiere($matiereCode);
            $stats = $this->model->getStatsForMatiere($matiereCode, count($affectations));
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'matiere/list'); exit();
        }
        $this->loadView('../views/matieres/details.php', [
            'item' => $item, 
            'affectations' => $affectations,
            'stats' => $stats,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_MATIERES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'matiere/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'matiere/list'); exit();
        }
        $this->loadView('../views/matieres/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_MATIERES);
        $this->loadView('../views/matieres/edit.php', ['item' => []]);
    }
}