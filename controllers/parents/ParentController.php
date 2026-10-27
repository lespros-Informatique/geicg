<?php

class ParentController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelParent();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_PARENTS, PERMISSIONS::MANAGE_PARENTS]);
        $this->loadView('../views/parents/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_PARENTS, PERMISSIONS::MANAGE_PARENTS]);
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_parent'];
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
        $this->requirePermission(PERMISSIONS::MANAGE_PARENTS);
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['telephone_parent'])) {
            if (!$this->checkUnique('parents', 'telephone_parent', $data['telephone_parent'], 'Telephone du parent')) return;
        }
        if (!empty($data['email_parent'])) {
            if (!$this->checkUnique('parents', 'email_parent', $data['email_parent'], 'Email du parent')) return;
        }

        $userCode = Auth::code() ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        if (empty($data['code_parent'])) {
            $data['code_parent'] = $this->validator->generateCode('parents', 'code_parent', 'PAR-', 8);
        }
        $data['statut_parent'] = $data['statut_parent'] ?? 'actif';
        $data['created_at_parent'] = date('Y-m-d H:i:s');
        $cols = $this->model->getTableColumns();
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = $this->model->filterData($data);
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
        $this->requirePermission(PERMISSIONS::MANAGE_PARENTS);
        $id = (int)$this->post('id_parent');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['telephone_parent'])) {
            if (!$this->checkUnique('parents', 'telephone_parent', $data['telephone_parent'], 'Telephone du parent', 'id_parent', $id)) return;
        }
        if (!empty($data['email_parent'])) {
            if (!$this->checkUnique('parents', 'email_parent', $data['email_parent'], 'Email du parent', 'id_parent', $id)) return;
        }

        $filteredData = $this->model->filterData($data);
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
        $this->requirePermission(PERMISSIONS::MANAGE_PARENTS);
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
        $this->requirePermission([PERMISSIONS::VIEW_PARENTS, PERMISSIONS::MANAGE_PARENTS]);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getParentDetails((int)$id);
            if (!$item) { header('Location: ' . RACINE . 'parent/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'parent/list'); exit();
        }
        $this->loadView('../views/parents/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_PARENTS);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'parent/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'parent/list'); exit();
        }
        $this->loadView('../views/parents/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_PARENTS);
        $this->loadView('../views/parents/edit.php', ['item' => []]);
    }
}