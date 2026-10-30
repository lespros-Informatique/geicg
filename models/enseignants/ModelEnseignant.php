<?php

class ModelEnseignant extends BaseModel
{
    protected string $table = TABLES::ENSEIGNANTS;
    protected string $primaryKey = 'id_enseignant';
    protected ?string $statusField = 'statut_enseignant';
    protected ?string $createdAtField = 'created_at_enseignant';

    public function getAll(): array
    {
        $sql = "
            SELECT e.*,
                   u.nom_user,
                   u.prenom_user,
                   u.email_user,
                   u.telephone_user,
                   u.sexe_user,
                   u.photo_user,
                   u.matricule_user,
                   u.statut_user,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.email_user AS email_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.sexe_user AS sexe_enseignant
            FROM " . TABLES::ENSEIGNANTS . " e
            JOIN " . TABLES::USERS . " u ON u.code_user = e.code_enseignant
            ORDER BY e.id_enseignant DESC
        ";
        try {
            $stmt = $this->getCon()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getAll error: " . $e->getMessage());
            return [];
        }
    }

    public function getActifs(): array
    {
        $sql = "
            SELECT e.*,
                   u.nom_user,
                   u.prenom_user,
                   u.email_user,
                   u.telephone_user,
                   u.sexe_user,
                   u.photo_user,
                   u.matricule_user,
                   u.statut_user,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.email_user AS email_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.sexe_user AS sexe_enseignant
            FROM " . TABLES::ENSEIGNANTS . " e
            JOIN " . TABLES::USERS . " u ON u.code_user = e.code_enseignant
            WHERE e.statut_enseignant = '" . STATUTS::ACTIF . "' AND u.statut_user = '" . STATUTS::ACTIF . "'
            ORDER BY u.nom_user ASC, u.prenom_user ASC
        ";
        try {
            $stmt = $this->getCon()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getActifs error: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id): array
    {
        $sql = "
            SELECT e.*,
                   u.id_user,
                   u.nom_user,
                   u.prenom_user,
                   u.email_user,
                   u.telephone_user,
                   u.sexe_user,
                   u.photo_user,
                   u.matricule_user,
                   u.statut_user,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.email_user AS email_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.sexe_user AS sexe_enseignant
            FROM " . TABLES::ENSEIGNANTS . " e
            JOIN " . TABLES::USERS . " u ON u.code_user = e.code_enseignant
            WHERE e.id_enseignant = ?
            LIMIT 1
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getById error: " . $e->getMessage());
            return [];
        }
    }

    public function getByCode(string $code, ?string $codeField = null): array
    {
        $sql = "
            SELECT e.*,
                   u.id_user,
                   u.nom_user,
                   u.prenom_user,
                   u.email_user,
                   u.telephone_user,
                   u.sexe_user,
                   u.photo_user,
                   u.matricule_user,
                   u.statut_user,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.email_user AS email_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.sexe_user AS sexe_enseignant
            FROM " . TABLES::ENSEIGNANTS . " e
            JOIN " . TABLES::USERS . " u ON u.code_user = e.code_enseignant
            WHERE e.code_enseignant = ?
            LIMIT 1
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$code]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getByCode error: " . $e->getMessage());
            return [];
        }
    }

    public function getUsersAvailableForTeacher(): array
    {
        $sql = "
            SELECT u.id_user, u.code_user, u.nom_user, u.prenom_user, u.email_user, u.telephone_user, u.sexe_user
            FROM " . TABLES::USERS . " u
            WHERE u.statut_user = '" . STATUTS::ACTIF . "'
              AND u.code_user NOT IN (SELECT code_enseignant FROM " . TABLES::ENSEIGNANTS . ")
            ORDER BY u.nom_user ASC, u.prenom_user ASC
        ";
        try {
            $stmt = $this->getCon()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getUsersAvailableForTeacher error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les cours / matières affectés à un enseignant
     */
    public function getCoursForEnseignant(string $codeEnseignant): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT em.*, m.libelle_matiere, cl.libelle_classe
                FROM " . TABLES::ENSEIGNANT_MATIERE . " em
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = em.matiere_code
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = em.classe_code
                WHERE em.enseignant_code = ?
                ORDER BY cl.libelle_classe ASC, m.libelle_matiere ASC
            ");
            $stmt->execute([$codeEnseignant]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getCoursForEnseignant error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si un code utilisateur est déjà enseignant
     */
    public function isEnseignantExists(string $codeUser): bool
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT id_enseignant FROM " . TABLES::ENSEIGNANTS . " WHERE code_enseignant = ?");
            $stmt->execute([$codeUser]);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Récupère un utilisateur par son code
     */
    public function getUserByCode(string $codeUser): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT * FROM " . TABLES::USERS . " WHERE code_user = ?");
            $stmt->execute([$codeUser]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Vérifie si un email est déjà pris
     */
    public function isEmailTaken(string $email, ?string $excludeCodeUser = null): bool
    {
        try {
            $sql = "SELECT id_user FROM " . TABLES::USERS . " WHERE email_user = ?";
            $params = [$email];
            if ($excludeCodeUser) {
                $sql .= " AND code_user != ?";
                $params[] = $excludeCodeUser;
            }
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Rattache un compte employé existant au corps professoral
     */
    public function attachExistingUser(string $codeUser, string $etabCode, string $currentUserCode, array $extraData, ?string &$errorMsg = null): ?array
    {
        $db = $this->getCon();
        $userRow = $this->getUserByCode($codeUser);
        if (!$userRow) {
            $errorMsg = 'Utilisateur introuvable.';
            return null;
        }

        if ($this->isEnseignantExists($codeUser)) {
            $errorMsg = 'Cet utilisateur est déjà enregistré dans le corps enseignant.';
            return null;
        }

        $fkErrRole = ForeignKeyValidator::validate($db, TABLES::USER_ROLES, [
            'user_code' => $codeUser,
            'role_code' => ROLES::ENSEIGNANT
        ]);
        if ($fkErrRole !== null) {
            $errorMsg = $fkErrRole;
            return null;
        }

        $fkErrEns = ForeignKeyValidator::validate($db, TABLES::ENSEIGNANTS, [
            'etablissement_code' => $etabCode
        ]);
        if ($fkErrEns !== null) {
            $errorMsg = $fkErrEns;
            return null;
        }

        $db->beginTransaction();
        try {
            $stmtRole = $db->prepare("
                INSERT IGNORE INTO " . TABLES::USER_ROLES . " (user_code, role_code, create_permission, edit_permission, show_permission, delete_permission)
                VALUES (?, '" . ROLES::ENSEIGNANT . "', 1, 1, 1, 0)
            ");
            $stmtRole->execute([$codeUser]);

            $stmtEns = $db->prepare("
                INSERT INTO " . TABLES::ENSEIGNANTS . " (
                    code_enseignant, grade_enseignant, type_contrat, numero_autorisation,
                    etablissement_code, user_code, statut_enseignant, created_at_enseignant
                ) VALUES (?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtEns->execute([
                $codeUser,
                $extraData['grade_enseignant'] ?? 'Enseignant',
                $extraData['type_contrat'] ?? 'permanent',
                !empty($extraData['numero_autorisation']) ? trim($extraData['numero_autorisation']) : null,
                $etabCode,
                $currentUserCode
            ]);

            $db->commit();
            return $userRow;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("ModelEnseignant attachExistingUser error: " . $e->getMessage());
            $errorMsg = "Erreur lors de la configuration du profil enseignant.";
            return null;
        }
    }

    /**
     * Crée un nouvel enseignant complet (compte utilisateur + profil enseignant)
     */
    public function createNewTeacher(array $userData, array $ensData, string $etabCode, string $currentUserCode, ?string &$errorMsg = null): ?string
    {
        $db = $this->getCon();
        $codeUser = $userData['code_user'];
        $matricule = 'MAT-' . $codeUser;
        $rawPassword = !empty($userData['password_raw']) ? trim($userData['password_raw']) : ('ENS' . rand(100000, 999999));
        $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

        $fkErrUser = ForeignKeyValidator::validate($db, TABLES::USERS, [
            'etablissement_code' => $etabCode
        ]);
        if ($fkErrUser !== null) {
            $errorMsg = $fkErrUser;
            return null;
        }

        $fkErrRole = ForeignKeyValidator::validate($db, TABLES::USER_ROLES, [
            'user_code' => $codeUser,
            'role_code' => ROLES::ENSEIGNANT
        ]);
        if ($fkErrRole !== null) {
            $errorMsg = $fkErrRole;
            return null;
        }

        $fkErrEns = ForeignKeyValidator::validate($db, TABLES::ENSEIGNANTS, [
            'etablissement_code' => $etabCode
        ]);
        if ($fkErrEns !== null) {
            $errorMsg = $fkErrEns;
            return null;
        }

        $db->beginTransaction();
        try {
            $stmtUser = $db->prepare("
                INSERT INTO " . TABLES::USERS . " (
                    code_user, matricule_user, nom_user, prenom_user, email_user, telephone_user,
                    sexe_user, password_user, etablissement_code, statut_user, created_at_user
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtUser->execute([
                $codeUser,
                $matricule,
                $userData['nom'],
                $userData['prenom'],
                $userData['email'] ?: ($codeUser . '@geicg.ci'),
                $userData['telephone'] ?: null,
                $userData['sexe'],
                $hashedPassword,
                $etabCode
            ]);

            $stmtRole = $db->prepare("
                INSERT INTO " . TABLES::USER_ROLES . " (user_code, role_code, create_permission, edit_permission, show_permission, delete_permission)
                VALUES (?, '" . ROLES::ENSEIGNANT . "', 1, 1, 1, 0)
            ");
            $stmtRole->execute([$codeUser]);

            $stmtEns = $db->prepare("
                INSERT INTO " . TABLES::ENSEIGNANTS . " (
                    code_enseignant, grade_enseignant, type_contrat, numero_autorisation,
                    etablissement_code, user_code, statut_enseignant, created_at_enseignant
                ) VALUES (?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtEns->execute([
                $codeUser,
                $ensData['grade_enseignant'] ?? 'Enseignant',
                $ensData['type_contrat'] ?? 'permanent',
                !empty($ensData['numero_autorisation']) ? trim($ensData['numero_autorisation']) : null,
                $etabCode,
                $currentUserCode
            ]);

            $db->commit();
            return $rawPassword;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("ModelEnseignant createNewTeacher error: " . $e->getMessage());
            $errorMsg = "Erreur lors de la création de l'enseignant : " . $e->getMessage();
            return null;
        }
    }

    /**
     * Met à jour le profil d'un enseignant et son compte utilisateur
     */
    public function updateTeacherAndUser(int $idEnseignant, string $codeUser, array $userData, array $ensData, ?string &$errorMsg = null): bool
    {
        $db = $this->getCon();
        $db->beginTransaction();
        try {
            $sqlUser = "
                UPDATE " . TABLES::USERS . " 
                SET nom_user = ?, prenom_user = ?, email_user = ?, telephone_user = ?, sexe_user = ?, updated_at_user = NOW()";
            $paramsUser = [
                $userData['nom'],
                $userData['prenom'],
                $userData['email'] ?: null,
                $userData['telephone'] ?: null,
                $userData['sexe']
            ];

            if (!empty($userData['password_raw'])) {
                $sqlUser .= ", password_user = ?";
                $paramsUser[] = password_hash(trim($userData['password_raw']), PASSWORD_DEFAULT);
            }

            $sqlUser .= " WHERE code_user = ?";
            $paramsUser[] = $codeUser;

            $stmtU = $db->prepare($sqlUser);
            $stmtU->execute($paramsUser);

            $sqlEns = "
                UPDATE " . TABLES::ENSEIGNANTS . " 
                SET grade_enseignant = ?, type_contrat = ?, numero_autorisation = ?, 
                    statut_enseignant = ?, updated_at_enseignant = NOW()
                WHERE id_enseignant = ?
            ";
            $stmtE = $db->prepare($sqlEns);
            $stmtE->execute([
                $ensData['grade_enseignant'] ?? 'Enseignant',
                $ensData['type_contrat'] ?? 'permanent',
                !empty($ensData['numero_autorisation']) ? trim($ensData['numero_autorisation']) : null,
                $ensData['statut_enseignant'] ?? 'actif',
                $idEnseignant
            ]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("ModelEnseignant updateTeacherAndUser error: " . $e->getMessage());
            $errorMsg = 'Erreur lors de la modification : ' . $e->getMessage();
            return false;
        }
    }
}
