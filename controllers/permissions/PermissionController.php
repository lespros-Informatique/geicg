<?php

class PermissionController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelPermission();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/permissions/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $groups = $this->model->getGrouped();
        $data = [];

        foreach ($groups as $group => $items) {
            foreach ($items as $i) {
                $idCrypte = $this->validator->crypter($i['id_permission']);
                $data[] = [
                    'code' => $i['code_permission'],
                    'libelle' => $i['libelle_permission'],
                    'description' => $i['description_permission'] ?? '',
                    'groupe' => $group,
                    'statut' => $i['statut_permission'],
                    'id' => $i['id_permission'],
                    'editId' => $idCrypte
                ];
            }
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['code_permission' => $_POST['code_permission'] ?? '', 'libelle_permission' => $_POST['libelle_permission'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        if ($this->validator->getByElement(TABLES::PERMISSIONS, 'code_permission', $this->post('code_permission'))) {
            $this->error('Ce code permission existe déjà!');
            return;
        }

        $data = [
            'code_permission' => $this->post('code_permission'),
            'libelle_permission' => $this->post('libelle_permission'),
            'description_permission' => $this->post('description_permission') ?? '',
            'statut_permission' => 'actif',
            'created_at_permission' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Permission créée avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['code_permission' => $_POST['code_permission'] ?? '', 'libelle_permission' => $_POST['libelle_permission'] ?? '', 'id_permission' => $_POST['id_permission'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $statut = in_array($this->post('statut_permission'), ['actif', 'inactif']) ? $this->post('statut_permission') : 'actif';
        $id = (int) $this->post('id_permission');

        $data = [
            'code_permission' => $this->post('code_permission'),
            'libelle_permission' => $this->post('libelle_permission'),
            'description_permission' => $this->post('description_permission') ?? '',
            'statut_permission' => $statut,
            'updated_at_permission' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Permission modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Permission introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'permission/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'permission/list');
            exit();
        }

        $this->loadView('../views/permissions/details.php', [
            'permission' => $item,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'permission/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'permission/list');
            exit();
        }

        $this->loadView('../views/permissions/edit.php', ['permission' => $item]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/permissions/edit.php', ['permission' => []]);
    }
}
