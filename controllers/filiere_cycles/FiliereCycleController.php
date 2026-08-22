<?php

class FiliereCycleController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelFiliereCycle();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/filiere_cycles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_filiere_cycle'];
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

        if (!empty($data['filiere_code']) && !empty($data['cycle_code'])) {
            if (!$this->checkUniquePair('filiere_cycles', [
                'filiere_code' => $data['filiere_code'],
                'cycle_code' => $data['cycle_code']
            ], 'Assignation Filière - Cycle')) return;
        }

        if (empty($data['code_filiere_cycle'])) {
            $data['code_filiere_cycle'] = $this->validator->generateCode('filiere_cycles', 'code_filiere_cycle', 'FCYC-', 8);
        }
        $data['statut_filiere_cycle'] = $data['statut_filiere_cycle'] ?? 'actif';
        $data['created_at_filiere_cycle'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE filiere_cycles")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success('Assignation Filière - Cycle créée avec succès!');
        } else {
            $this->error('Erreur lors de la création de l\'assignation');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_filiere_cycle');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['filiere_code']) && !empty($data['cycle_code'])) {
            if (!$this->checkUniquePair('filiere_cycles', [
                'filiere_code' => $data['filiere_code'],
                'cycle_code' => $data['cycle_code']
            ], 'Assignation Filière - Cycle', 'id_filiere_cycle', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE filiere_cycles")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Assignation Filière - Cycle modifiée avec succès!');
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
            if (!$item) { header('Location: ' . RACINE . 'filiere_cycle/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'filiere_cycle/list'); exit();
        }
        $this->loadView('../views/filiere_cycles/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'filiere_cycle/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'filiere_cycle/list'); exit();
        }
        $this->loadView('../views/filiere_cycles/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/filiere_cycles/edit.php', ['item' => []]);
    }
}