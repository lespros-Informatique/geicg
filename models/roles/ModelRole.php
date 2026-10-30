<?php

class ModelRole extends BaseModel
{
    protected string $table = TABLES::ROLES;
    protected string $primaryKey = 'id';
    protected ?string $statusField = 'statut_role';

    public function getAllWithPermissions(): array
    {
        try {
            $sql = "SELECT r.*, 
                    GROUP_CONCAT(rp.permission_code SEPARATOR ',') as permission_codes
                    FROM {$this->table} r
                    LEFT JOIN " . TABLES::ROLE_PERMISSIONS . " rp ON r.code_role = rp.role_code
                    GROUP BY r.id
                    ORDER BY r.id ASC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelRole::getAllWithPermissions] ' . $e->getMessage());
            return [];
        }
    }

    public function getPermissions(string $roleCode): array
    {
        try {
            $sql = "SELECT p.code_permission, p.libelle_permission, p.module_permission
                    FROM " . TABLES::PERMISSIONS . " p
                    INNER JOIN " . TABLES::ROLE_PERMISSIONS . " rp ON p.code_permission = rp.permission_code
                    WHERE rp.role_code = ? AND p.statut_permission = 'actif'
                    ORDER BY p.code_permission ASC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$roleCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelRole::getPermissions] ' . $e->getMessage());
            return [];
        }
    }

    public function assignPermission(string $roleCode, string $permissionCode): bool
    {
        try {
            $fkErr = ForeignKeyValidator::validate($this->getCon(), 'role_permissions', [
                'role_code' => $roleCode,
                'permission_code' => $permissionCode
            ]);
            if ($fkErr !== null) {
                error_log('[ModelRole::assignPermission] FK Error: ' . $fkErr);
                return false;
            }

            $sql = "INSERT INTO " . TABLES::ROLE_PERMISSIONS . " (role_code, permission_code)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE permission_code = VALUES(permission_code)";
            $stmt = $this->getCon()->prepare($sql);
            return $stmt->execute([$roleCode, $permissionCode]);
        } catch (Exception $e) {
            error_log('[ModelRole::assignPermission] ' . $e->getMessage());
            return false;
        }
    }

    public function syncPermissions(string $roleCode, array $permissionCodes): bool
    {
        try {
            $this->getCon()->beginTransaction();

            $sql = "DELETE FROM " . TABLES::ROLE_PERMISSIONS . " WHERE role_code = ?";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$roleCode]);

            foreach ($permissionCodes as $permissionCode) {
                $this->assignPermission($roleCode, $permissionCode);
            }

            $this->getCon()->commit();
            return true;
        } catch (Exception $e) {
            error_log('[ModelRole::syncPermissions] ' . $e->getMessage());
            $this->getCon()->rollBack();
            return false;
        }
    }

    public function getRolesWithCounts(): array
    {
        try {
            $sql = "SELECT r.*,
                           COUNT(DISTINCT u.code_user) as nb_users,
                           COUNT(DISTINCT rp.permission_code) as nb_permissions
                    FROM " . TABLES::ROLES . " r
                    LEFT JOIN " . TABLES::USER_ROLES . " ur ON ur.role_code = r.code_role
                    LEFT JOIN " . TABLES::USERS . " u ON u.code_user = ur.user_code AND u.statut_user = 'actif'
                    LEFT JOIN " . TABLES::ROLE_PERMISSIONS . " rp ON rp.role_code = r.code_role
                    GROUP BY r.id
                    ORDER BY r.id ASC";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelRole::getRolesWithCounts] ' . $e->getMessage());
            return [];
        }
    }

    public function existsCode(string $codeRole): bool
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT id FROM " . TABLES::ROLES . " WHERE code_role = ?");
            $stmt->execute([$codeRole]);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    public function createRole(array $data, array $permissions = []): bool
    {
        try {
            $data['statut_role'] = $data['statut_role'] ?? 'actif';
            $sql = "INSERT INTO " . TABLES::ROLES . " (code_role, libelle_role, module, groupe, description, statut_role)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->getCon()->prepare($sql);
            $ok = $stmt->execute([
                $data['code_role'],
                $data['libelle_role'],
                $data['module'] ?? 'ADMINISTRATION',
                $data['groupe'] ?? 'Direction',
                $data['description'] ?? '',
                $data['statut_role']
            ]);
            if ($ok && !empty($permissions)) {
                $this->syncPermissions($data['code_role'], $permissions);
            }
            return $ok;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log('[ModelRole::createRole] ' . $e->getMessage());
            return false;
        }
    }

    public function updateRole(int $id, array $data, array $permissions = []): bool
    {
        try {
            $role = $this->getById($id);
            if (!$role) return false;

            $sql = "UPDATE " . TABLES::ROLES . " 
                    SET libelle_role = ?, module = ?, groupe = ?, description = ?, statut_role = ?
                    WHERE id = ?";
            $stmt = $this->getCon()->prepare($sql);
            $ok = $stmt->execute([
                $data['libelle_role'],
                $data['module'] ?? ($role['module'] ?? 'ADMINISTRATION'),
                $data['groupe'] ?? ($role['groupe'] ?? 'Direction'),
                $data['description'] ?? ($role['description'] ?? ''),
                $data['statut_role'] ?? ($role['statut_role'] ?? 'actif'),
                $id
            ]);
            if ($ok) {
                $this->syncPermissions($role['code_role'], $permissions);
            }
            return $ok;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log('[ModelRole::updateRole] ' . $e->getMessage());
            return false;
        }
    }

    public function getUsersByRole(string $roleCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT u.* FROM " . TABLES::USERS . " u
                INNER JOIN " . TABLES::USER_ROLES . " ur ON ur.user_code = u.code_user
                WHERE ur.role_code = ? AND u.statut_user = 'actif'
                ORDER BY u.nom_user ASC, u.prenom_user ASC
            ");
            $stmt->execute([$roleCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelRole::getUsersByRole] ' . $e->getMessage());
            return [];
        }
    }
}
