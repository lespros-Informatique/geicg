<?php

class RoleController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelRole();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_ROLES');
        $this->loadView('../views/roles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_ROLES');
        $items = $this->model->getRolesWithCounts();

        $data = [];
        foreach ($items as $i) {
            $id = $i['id'];
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
        $this->requirePermission('MANAGE_ROLES');
        $data = $_POST;
        unset($data['csrf_token']);

        $libelle = trim($data['libelle_role'] ?? '');
        $codeRole = trim($data['code_role'] ?? '');
        $module = trim($data['module'] ?? 'ADMINISTRATION');
        $groupe = trim($data['groupe'] ?? 'Direction');
        $description = trim($data['description'] ?? '');
        $permissions = isset($data['permissions']) && is_array($data['permissions']) ? $data['permissions'] : [];

        if (empty($libelle)) {
            $this->error('Le libellé du rôle est obligatoire.');
            return;
        }

        if (empty($codeRole)) {
            $codeRole = 'ROLE_' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '_', $libelle));
        }

        if ($this->model->existsCode($codeRole)) {
            $this->error('Ce code de rôle existe déjà.');
            return;
        }

        $rolePayload = [
            'code_role' => $codeRole,
            'libelle_role' => $libelle,
            'module' => $module,
            'groupe' => $groupe,
            'description' => $description,
            'statut_role' => 'actif'
        ];
        if ($this->model->createRole($rolePayload, $permissions)) {
            $this->success('Rôle créé avec succès !', RACINE . 'role/list');
        } else {
            $this->error('Erreur lors de la création du rôle.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_ROLES');
        $id = (int)$this->post('id');
        if (!$id) { $this->error('Identifiant invalide', RACINE . 'role/list'); return; }

        $role = $this->model->getById($id);
        if (!$role) { $this->error('Rôle introuvable', RACINE . 'role/list'); return; }

        $data = $_POST;
        unset($data['csrf_token']);

        $libelle = trim($data['libelle_role'] ?? '');
        $module = trim($data['module'] ?? ($role['module'] ?? 'ADMINISTRATION'));
        $groupe = trim($data['groupe'] ?? ($role['groupe'] ?? 'Direction'));
        $description = trim($data['description'] ?? ($role['description'] ?? ''));
        $statut = $data['statut_role'] ?? ($role['statut_role'] ?? 'actif');
        $permissions = isset($data['permissions']) && is_array($data['permissions']) ? $data['permissions'] : [];

        if (empty($libelle)) {
            $this->error('Le libellé du rôle est obligatoire.');
            return;
        }

        $rolePayload = [
            'libelle_role' => $libelle,
            'module' => $module,
            'groupe' => $groupe,
            'description' => $description,
            'statut_role' => $statut
        ];
        if ($this->model->updateRole($id, $rolePayload, $permissions)) {
            $this->success('Rôle et permissions mis à jour avec succès !', RACINE . 'role/list');
        } else {
            $this->error('Erreur lors de la modification du rôle.');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_ROLES');
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
        $this->requirePermission('VIEW_ROLES');
        try {
            $id = $this->validator->decrypter($details);
            $role = $this->model->getById($id);
            if (!$role) { header('Location: ' . RACINE . 'role/list'); exit(); }

            $permissions = $this->model->getPermissions($role['code_role']);
            $stmtUsers = $this->model->getCon()->prepare("
                SELECT u.* FROM users u
                INNER JOIN user_roles ur ON ur.user_code = u.code_user
                WHERE ur.role_code = ? AND u.statut_user = 'actif'
            ");
            $stmtUsers->execute([$role['code_role']]);
            $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'role/list'); exit();
        }
        $this->loadView('../views/roles/details.php', [
            'role' => $role,
            'permissions' => $permissions,
            'users' => $users,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission('MANAGE_ROLES');
        try {
            $id = $this->validator->decrypter($details);
            $role = $this->model->getById($id);
            if (!$role) { header('Location: ' . RACINE . 'role/list'); exit(); }

            $allPermissions = (new ModelPermission())->getGrouped();
            $assignedPerms = $this->model->getPermissions($role['code_role']);
            $assignedCodes = array_column($assignedPerms, 'code_permission');

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'role/list'); exit();
        }
        $this->loadView('../views/roles/edit.php', [
            'role' => $role,
            'allPermissions' => $allPermissions,
            'assignedCodes' => $assignedCodes,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission('MANAGE_ROLES');
        $allPermissions = (new ModelPermission())->getGrouped();
        $this->loadView('../views/roles/edit.php', [
            'role' => [],
            'allPermissions' => $allPermissions,
            'assignedCodes' => []
        ]);
    }
}