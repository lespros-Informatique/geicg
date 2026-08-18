<?php

class ModelRole extends BaseModel
{
    protected string $table = 'roles';
    protected string $primaryKey = 'id_role';
    protected ?string $statusField = 'statut_role';

    public function getAllWithPermissions(): array
    {
        try {
            $sql = "SELECT r.*, 
                    GROUP_CONCAT(rp.permission_code ORDER BY rp.code_role_permission SEPARATOR ',') as permission_codes
                    FROM {$this->table} r
                    LEFT JOIN roles_permissions rp ON r.code_role = rp.role_code AND rp.statut_role_permission = 'actif'
                    GROUP BY r.id_role
                    ORDER BY r.id_role ASC";
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
            $sql = "SELECT p.code_permission, p.libelle_permission, rp.code_role_permission
                    FROM permissions p
                    INNER JOIN roles_permissions rp ON p.code_permission = rp.permission_code
                    WHERE rp.role_code = ? AND rp.statut_role_permission = 'actif'
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
            $code = 'RP-' . strtoupper(uniqid());
            $sql = "INSERT INTO roles_permissions (code_role_permission, role_code, permission_code, statut_role_permission)
                    VALUES (?, ?, ?, 'actif')
                    ON DUPLICATE KEY UPDATE statut_role_permission = 'actif'";
            $stmt = $this->getCon()->prepare($sql);
            return $stmt->execute([$code, $roleCode, $permissionCode]);
        } catch (Exception $e) {
            error_log('[ModelRole::assignPermission] ' . $e->getMessage());
            return false;
        }
    }

    public function removePermission(string $roleCode, string $permissionCode): bool
    {
        try {
            $sql = "UPDATE roles_permissions SET statut_role_permission = 'inactif'
                    WHERE role_code = ? AND permission_code = ?";
            $stmt = $this->getCon()->prepare($sql);
            return $stmt->execute([$roleCode, $permissionCode]);
        } catch (Exception $e) {
            error_log('[ModelRole::removePermission] ' . $e->getMessage());
            return false;
        }
    }

    public function syncPermissions(string $roleCode, array $permissionCodes): bool
    {
        try {
            $this->getCon()->beginTransaction();

            $sql = "UPDATE roles_permissions SET statut_role_permission = 'inactif' WHERE role_code = ?";
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
}
