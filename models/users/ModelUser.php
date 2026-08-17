<?php

class ModelUser extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id_user';
    protected ?string $statusField = 'statut_user';

    public function getUserRole(string $userCode): ?array
    {
        try {
            $sql = "SELECT role_code, libelle_role FROM " . TABLES::ROLES . " r
                    INNER JOIN " . TABLES::USERS . " u ON u.role_code = r.code_role
                    WHERE u.code_user = ? LIMIT 1";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($role) {
                return $role;
            }

            $sql = "SELECT r.code_role, r.libelle_role, up.pressing_code
                    FROM " . TABLES::USERS_PRESSINGS . " up
                    INNER JOIN " . TABLES::ROLES . " r ON up.role_code = r.code_role
                    WHERE up.user_code = ? AND up.statut_user_pressing = 'actif'
                    LIMIT 1";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelUser::getUserRole error: " . $e->getMessage());
            return null;
        }
    }

    public function setUserRole(string $userCode, string $roleCode, string $pressingCode = ''): bool
    {
        try {
            $this->getCon()->beginTransaction();

            $sqlUpdate = "UPDATE " . TABLES::USERS . " SET role_code = ? WHERE code_user = ?";
            $stmtUpdate = $this->getCon()->prepare($sqlUpdate);
            $stmtUpdate->execute([$roleCode, $userCode]);

            if ($pressingCode !== '') {
                $sqlDelete = "DELETE FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ?";
                $stmtDelete = $this->getCon()->prepare($sqlDelete);
                $stmtDelete->execute([$userCode]);

                $sqlInsert = "INSERT INTO " . TABLES::USERS_PRESSINGS . 
                              " (code_user_pressing, user_code, pressing_code, role_code, statut_user_pressing) 
                              VALUES (?, ?, ?, ?, 'actif')";
                $codeUserPressing = 'UP-' . strtoupper(uniqid());
                $stmtInsert = $this->getCon()->prepare($sqlInsert);
                $result = $stmtInsert->execute([$codeUserPressing, $userCode, $pressingCode, $roleCode]);
            } else {
                $sqlDelete = "DELETE FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ?";
                $stmtDelete = $this->getCon()->prepare($sqlDelete);
                $result = $stmtDelete->execute([$userCode]);
            }

            if ($result) {
                $this->getCon()->commit();
                return true;
            }

            $this->getCon()->rollBack();
            return false;
        } catch (Exception $e) {
            error_log("ModelUser::setUserRole error: " . $e->getMessage());
            $this->getCon()->rollBack();
            return false;
        }
    }
}
