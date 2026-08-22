<?php

class FiliereNiveauController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelFiliereNiveau();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/filiere_niveaux/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_filiere_niveau'];
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
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['filiere_code']) && !empty($data['niveau_code'])) {
            if (!$this->checkUniquePair('filiere_niveaux', [
                'filiere_code' => $data['filiere_code'],
                'niveau_code' => $data['niveau_code']
            ], 'Assignation Filière - Niveau')) return;
        }

        if (empty($data['code_filiere_niveau'])) {
            $data['code_filiere_niveau'] = $this->validator->generateCode('filiere_niveaux', 'code_filiere_niveau', 'FNIV-', 8);
        }
        $data['statut_filiere_niveau'] = $data['statut_filiere_niveau'] ?? 'actif';
        $data['created_at_filiere_niveau'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE filiere_niveaux")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success('Assignation Filière - Niveau créée avec succès!');
        } else {
            $this->error('Erreur lors de la création de l\'assignation');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_filiere_niveau');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['filiere_code']) && !empty($data['niveau_code'])) {
            if (!$this->checkUniquePair('filiere_niveaux', [
                'filiere_code' => $data['filiere_code'],
                'niveau_code' => $data['niveau_code']
            ], 'Assignation Filière - Niveau', 'id_filiere_niveau', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE filiere_niveaux")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Assignation Filière - Niveau modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
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
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'filiere_niveau/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'filiere_niveau/list'); exit();
        }
        $this->loadView('../views/filiere_niveaux/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'filiere_niveau/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'filiere_niveau/list'); exit();
        }
        $this->loadView('../views/filiere_niveaux/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/filiere_niveaux/edit.php', ['item' => []]);
    }
}