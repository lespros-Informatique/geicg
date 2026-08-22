<?php

class EtudiantController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEtudiant();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/etudiants/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_etudiant'];
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
        if (!empty($data['matricule_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'matricule_etudiant', $data['matricule_etudiant'], 'Matricule etudiant')) return;
        }
        if (!empty($data['email_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'email_etudiant', $data['email_etudiant'], 'Email etudiant')) return;
        }
        if (!empty($data['telephone_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'telephone_etudiant', $data['telephone_etudiant'], 'Telephone etudiant')) return;
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $_SESSION['annee_active_code'] ?? '0GklBk07waYoLB6pHwY';
        $etabCode = '5454544456';
        if (empty($data['code_etudiant'])) {
            $data['code_etudiant'] = $this->validator->generateCode('etudiants', 'code_etudiant', 'ETU-', 8);
        }
        $data['statut_etudiant'] = $data['statut_etudiant'] ?? 'actif';
        $data['created_at_etudiant'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE etudiants")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Item créé avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_etudiant');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['matricule_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'matricule_etudiant', $data['matricule_etudiant'], 'Matricule etudiant', 'id_etudiant', $id)) return;
        }
        if (!empty($data['email_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'email_etudiant', $data['email_etudiant'], 'Email etudiant', 'id_etudiant', $id)) return;
        }
        if (!empty($data['telephone_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'telephone_etudiant', $data['telephone_etudiant'], 'Telephone etudiant', 'id_etudiant', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE etudiants")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
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
            if (!$item) { header('Location: ' . RACINE . 'etudiant/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'etudiant/list'); exit();
        }
        $this->loadView('../views/etudiants/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'etudiant/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'etudiant/list'); exit();
        }
        $this->loadView('../views/etudiants/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/etudiants/edit.php', ['item' => []]);
    }
}