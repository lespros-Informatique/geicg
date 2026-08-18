<?php

class RoleController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelRole();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/roles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $roles = $this->model->getAllWithPermissions();
        $data = [];

        foreach ($roles as $r) {
            $idCrypte = $this->validator->crypter($r['id_role']);
            $permissions = $this->model->getPermissions($r['code_role']);
            $permissionLabels = array_map(function($p) {
                return $p['libelle_permission'] ?? $p['code_permission'];
            }, $permissions);

            $data[] = [
                'code' => $r['code_role'],
                'libelle' => $r['libelle_role'],
                'description' => $r['description_role'] ?? '',
                'statut' => $r['statut_role'],
                'permissions_count' => count($permissions),
                'permissions' => implode(', ', $permissionLabels),
                'id' => $r['id_role'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['libelle_role' => $_POST['libelle_role'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $code = $this->post('code_role') ?: $this->validator->generateCode(TABLES::ROLES, 'code_role', 'ROLE-', 6);
        if ($this->validator->getByElement(TABLES::ROLES, 'code_role', $code)) {
            $this->error('Ce code rôle existe déjà!');
            return;
        }

        $data = [
            'code_role' => $code,
            'libelle_role' => $this->post('libelle_role'),
            'description_role' => $this->post('description_role') ?? '',
            'statut_role' => 'actif',
            'created_at_role' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Rôle créé avec succès!', ['role_code' => $code]);
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['libelle_role' => $_POST['libelle_role'] ?? '', 'id_role' => $_POST['id_role'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $statut = in_array($this->post('statut_role'), ['actif', 'inactif']) ? $this->post('statut_role') : 'actif';
        $id = (int) $this->post('id_role');

        $data = [
            'id_role' => $id,
            'libelle_role' => $this->post('libelle_role'),
            'description_role' => $this->post('description_role') ?? '',
            'statut_role' => $statut,
            'updated_at_role' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data, $id)) {
            $this->success('Rôle modifié avec succès!');
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
            $this->error('Rôle introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'role/list');
                exit();
            }

            $permissions = $this->model->getPermissions($item['code_role']);
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'role/list');
            exit();
        }

        $this->loadView('../views/roles/details.php', [
            'role' => $item,
            'encryptedId' => $encryptedId,
            'permissions' => $permissions
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'role/list');
                exit();
            }

            $permissionModel = new ModelPermission();
            $allPermissions = $permissionModel->getGrouped();
            $assignedPermissions = $this->model->getPermissions($item['code_role']);
            $assignedCodes = array_column($assignedPermissions, 'code_permission');
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'role/list');
            exit();
        }

        $this->loadView('../views/roles/edit.php', [
            'role' => $item,
            'encryptedId' => $encryptedId,
            'allPermissions' => $allPermissions,
            'assignedCodes' => $assignedCodes
        ]);
    }

    public function updatePermissions()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $id = (int) $this->post('id_role');
        $role = $this->model->getById($id);
        if (!$role) {
            $this->error('Rôle introuvable');
            return;
        }

        $permissions = $this->post('permissions', []);
        if (!is_array($permissions)) {
            $permissions = [];
        }

        if ($this->model->syncPermissions($role['code_role'], $permissions)) {
            $this->success('Permissions mises à jour avec succès!');
        } else {
            $this->error('Erreur lors de la mise à jour des permissions');
        }
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/roles/edit.php', ['role' => []]);
    }
}
