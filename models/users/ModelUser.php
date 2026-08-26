<?php

class ModelUser extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id_user';
    protected ?string $statusField = 'statut_user';

    /**
     * Récupère le rôle et les droits d'un utilisateur
     */
    public function getUserRole(string $userCode): ?array
    {
        try {
            $sql = "SELECT ur.*, r.libelle_role, r.module, r.groupe, r.description 
                    FROM user_roles ur
                    INNER JOIN roles r ON r.code_role = ur.role_code
                    WHERE ur.user_code = ? LIMIT 1";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelUser::getUserRole error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Attribue un rôle et des permissions CRUD à un utilisateur
     */
    public function setUserRole(string $userCode, string $roleCode, int $create = 1, int $edit = 1, int $show = 1, int $delete = 0): bool
    {
        try {
            $stmtCheck = $this->getCon()->prepare("SELECT id FROM user_roles WHERE user_code = ? LIMIT 1");
            $stmtCheck->execute([$userCode]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $stmt = $this->getCon()->prepare("
                    UPDATE user_roles 
                    SET role_code = ?, create_permission = ?, edit_permission = ?, show_permission = ?, delete_permission = ? 
                    WHERE user_code = ?
                ");
                return $stmt->execute([$roleCode, $create, $edit, $show, $delete, $userCode]);
            } else {
                $stmt = $this->getCon()->prepare("
                    INSERT INTO user_roles (user_code, role_code, create_permission, edit_permission, show_permission, delete_permission) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                return $stmt->execute([$userCode, $roleCode, $create, $edit, $show, $delete]);
            }
        } catch (Exception $e) {
            error_log("ModelUser::setUserRole error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour le mot de passe d'un utilisateur
     */
    public function updatePassword(string $hashPassword, int $userId): bool
    {
        try {
            $sql = "UPDATE users SET password_user = ?, updated_at_user = ? WHERE id_user = ?";
            $stmt = $this->getCon()->prepare($sql);
            return $stmt->execute([$hashPassword, date('Y-m-d H:i:s'), $userId]);
        } catch (Exception $e) {
            error_log("ModelUser::updatePassword error: " . $e->getMessage());
            return false;
        }
    }
}
