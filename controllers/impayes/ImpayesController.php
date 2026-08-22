<?php

class ImpayesController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelImpayes();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/impayes/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_relance'];
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
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $_SESSION['annee_active_code'] ?? '0GklBk07waYoLB6pHwY';
        $etabCode = '5454544456';
        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['code_relance'])) {
            $data['code_relance'] = $this->validator->generateCode('relances_impayes', 'code_relance', 'REL-', 8);
        }
        $data['statut_relance'] = 'envoye';
        $data['created_at_relance'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['annee_code'] = $anneeCode;
        $data['etablissement_code'] = $etabCode;

        $cols = $this->model->getCon()->query("DESCRIBE relances_impayes")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success('Relance d\'impayé enregistrée et expédiée avec succès!');
        } else {
            $this->error('Erreur lors de l\'enregistrement de la relance');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_inscription');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'impayes/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'impayes/list'); exit();
        }
        $this->loadView('../views/impayes/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'impayes/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'impayes/list'); exit();
        }
        $this->loadView('../views/impayes/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/impayes/edit.php', ['item' => []]);
    }
}
