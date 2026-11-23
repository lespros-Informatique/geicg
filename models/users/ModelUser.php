<?php

class ModelUser extends BaseModel
{
    protected string $table = TABLES::USERS;
    protected string $primaryKey = 'id_user';
    protected ?string $statusField = 'statut_user';

    /**
     * Récupère tous les rôles et privilèges d'un utilisateur
     */
    public function getUserRoles(string $userCode): array
    {
        try {
            $sql = "SELECT ur.*, r.id AS role_id, r.libelle_role, r.module, r.groupe, r.description 
                    FROM " . TABLES::USER_ROLES . " ur
                    INNER JOIN " . TABLES::ROLES . " r ON r.code_role = ur.role_code
                    WHERE ur.user_code = ?
                    ORDER BY CASE 
                        WHEN ur.role_code = '" . ROLES::SUPER_ADMIN . "' THEN 1 
                        WHEN ur.role_code = '" . ROLES::DIR_GENERAL . "' THEN 2
                        WHEN ur.role_code = '" . ROLES::DIR_ETUDES . "' THEN 3
                        WHEN ur.role_code = '" . ROLES::CHEF_DEP . "' THEN 4
                        WHEN ur.role_code = '" . ROLES::COMPTABLE . "' THEN 5
                        WHEN ur.role_code = '" . ROLES::SCOLARITE . "' THEN 6
                        WHEN ur.role_code = '" . ROLES::CAISSIER . "' THEN 7
                        WHEN ur.role_code = '" . ROLES::ENSEIGNANT . "' THEN 8
                        ELSE 9 END, ur.id ASC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelUser::getUserRoles error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère la liste simple des codes de rôles d'un utilisateur
     */
    public function getUserRoleCodes(string $userCode): array
    {
        $roles = $this->getUserRoles($userCode);
        return array_values(array_unique(array_filter(array_column($roles, 'role_code'))));
    }

    /**
     * Récupère le rôle principal (le plus haut dans la hiérarchie) d'un utilisateur
     */
    public function getUserRole(string $userCode): ?array
    {
        $roles = $this->getUserRoles($userCode);
        return !empty($roles) ? $roles[0] : null;
    }

    /**
     * Synchronise une liste de rôles multiples avec permissions CRUD spécifiques par rôle
     * @param string $userCode Code de l'utilisateur
     * @param array $rolesData [ 'ROLE_CODE' => ['create' => 1, 'edit' => 1, ...], ... ] ou ['ROLE_1', 'ROLE_2']
     * @param array $defaultCrud Permissions par défaut si non fournies
     */
    public function syncUserRoles(string $userCode, array $rolesData, array $defaultCrud = []): bool
    {
        try {
            $pdo = $this->getCon();
            $pdo->beginTransaction();

            // Supprimer les rôles actuels
            $stmtDel = $pdo->prepare("DELETE FROM " . TABLES::USER_ROLES . " WHERE user_code = ?");
            $stmtDel->execute([$userCode]);

            if (empty($rolesData)) {
                $stmtDef = $pdo->query("SELECT code_role FROM " . TABLES::ROLES . " WHERE statut_role = '" . STATUTS::ACTIF . "' ORDER BY id ASC LIMIT 1");
                $defRole = ($stmtDef && $stmtDef->fetchColumn()) ?: ROLES::USER;
                $rolesData = [$defRole => ['create' => 1, 'edit' => 1, 'show' => 1, 'delete' => 0]];
            }

            $stmtIns = $pdo->prepare("
                INSERT INTO " . TABLES::USER_ROLES . " (user_code, role_code, create_permission, edit_permission, show_permission, delete_permission) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($rolesData as $key => $val) {
                if (is_array($val)) {
                    $roleCode = trim($key);
                    $createP = !empty($val['create']) ? 1 : 0;
                    $editP   = !empty($val['edit']) ? 1 : 0;
                    $showP   = isset($val['show']) ? (!empty($val['show']) ? 1 : 0) : 1;
                    $deleteP = !empty($val['delete']) ? 1 : 0;
                } else {
                    $roleCode = trim($val);
                    $createP = isset($defaultCrud['create']) ? (int)$defaultCrud['create'] : 1;
                    $editP   = isset($defaultCrud['edit']) ? (int)$defaultCrud['edit'] : 1;
                    $showP   = isset($defaultCrud['show']) ? (int)$defaultCrud['show'] : 1;
                    $deleteP = isset($defaultCrud['delete']) ? (int)$defaultCrud['delete'] : 0;
                }

                if (!empty($roleCode)) {
                    $fkErr = ForeignKeyValidator::validate($pdo, TABLES::USER_ROLES, [
                        'user_code' => $userCode,
                        'role_code' => $roleCode
                    ]);
                    if ($fkErr !== null) {
                        error_log('[ModelUser::syncRoles] FK Error: ' . $fkErr);
                        continue;
                    }

                    $stmtIns->execute([$userCode, $roleCode, $createP, $editP, $showP, $deleteP]);
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->getCon()->inTransaction()) {
                $this->getCon()->rollBack();
            }
            error_log("ModelUser::syncUserRoles error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Attribue un rôle unique ou ajoute un rôle (rétrocompatibilité)
     */
    public function setUserRole(string $userCode, string $roleCode, int $create = 1, int $edit = 1, int $show = 1, int $delete = 0): bool
    {
        return $this->syncUserRoles($userCode, [$roleCode], [
            'create' => $create,
            'edit' => $edit,
            'show' => $show,
            'delete' => $delete
        ]);
    }

    /**
     * Met à jour le mot de passe d'un utilisateur
     */
    public function updatePassword(string $hashPassword, int $userId): bool
    {
        try {
            $sql = "UPDATE " . TABLES::USERS . " SET password_user = ?, updated_at_user = ? WHERE id_user = ?";
            $stmt = $this->getCon()->prepare($sql);
            return $stmt->execute([$hashPassword, date('Y-m-d H:i:s'), $userId]);
        } catch (Exception $e) {
            error_log("ModelUser::updatePassword error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les privilèges d'accès aux années antérieures pour un utilisateur
     * Retourne un tableau associatif : ['ANN-CODE-1' => 'lecture', 'ANN-CODE-2' => 'ecriture']
     */
    public function getUserAnneeAcces(string $userCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT annee_code, niveau_acces FROM " . TABLES::USER_ANNEE_ACCES . " WHERE user_code = ?");
            $stmt->execute([$userCode]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $res = [];
            foreach ($rows as $r) {
                $res[$r['annee_code']] = $r['niveau_acces'];
            }
            return $res;
        } catch (Exception $e) {
            error_log("ModelUser::getUserAnneeAcces error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Enregistre ou met à jour les privilèges d'accès aux années antérieures pour un utilisateur
     * @param string $userCode Code de l'utilisateur
     * @param array $accesList Tableau associatif ['annee_code' => 'lecture'|'ecriture']
     */
    public function saveUserAnneeAcces(string $userCode, array $accesList): bool
    {
        try {
            $pdo = $this->getCon();
            $stmtDel = $pdo->prepare("DELETE FROM " . TABLES::USER_ANNEE_ACCES . " WHERE user_code = ?");
            $stmtDel->execute([$userCode]);

            if (empty($accesList)) {
                return true;
            }

            $stmtIns = $pdo->prepare("INSERT INTO " . TABLES::USER_ANNEE_ACCES . " (user_code, annee_code, niveau_acces) VALUES (?, ?, ?)");
            foreach ($accesList as $anneeCode => $niveau) {
                $anneeCode = trim($anneeCode);
                $niveau = strtolower(trim($niveau));
                if (!empty($anneeCode) && in_array($niveau, ['lecture', 'ecriture'], true)) {
                    $stmtIns->execute([$userCode, $anneeCode, $niveau]);
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("ModelUser::saveUserAnneeAcces error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère la liste complète des utilisateurs avec leurs rôles concaténés et libellé de fonction
     */
    public function getUsersWithRolesAndFonctions(): array
    {
        try {
            $sql = "SELECT u.*, 
                           GROUP_CONCAT(DISTINCT r.libelle_role ORDER BY r.id SEPARATOR '||') as roles_libelles,
                           GROUP_CONCAT(DISTINCT r.code_role ORDER BY r.id SEPARATOR ',') as roles_codes,
                           f.libelle_fonction
                    FROM " . TABLES::USERS . " u
                    LEFT JOIN " . TABLES::USER_ROLES . " ur ON ur.user_code = u.code_user
                    LEFT JOIN " . TABLES::ROLES . " r ON r.code_role = ur.role_code
                    LEFT JOIN " . TABLES::FONCTIONS . " f ON f.code_fonction = u.fonction_code
                    GROUP BY u.id_user
                    ORDER BY u.id_user DESC";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelUser::getUsersWithRolesAndFonctions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si un e-mail est déjà attribué à un compte utilisateur
     */
    public function existsEmail(string $email, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT id_user FROM " . TABLES::USERS . " WHERE email_user = ?";
            $params = [$email];
            if ($excludeId !== null) {
                $sql .= " AND id_user != ?";
                $params[] = $excludeId;
            }
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            error_log("ModelUser::existsEmail error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un numéro de téléphone est déjà attribué à un compte utilisateur
     */
    public function existsTelephone(string $telephone, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT id_user FROM " . TABLES::USERS . " WHERE telephone_user = ?";
            $params = [$telephone];
            if ($excludeId !== null) {
                $sql .= " AND id_user != ?";
                $params[] = $excludeId;
            }
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            error_log("ModelUser::existsTelephone error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère le code du rôle actif par défaut
     */
    public function getDefaultRoleCode(): string
    {
        try {
            $stmt = $this->getCon()->query("SELECT code_role FROM " . TABLES::ROLES . " WHERE statut_role = '" . STATUTS::ACTIF . "' ORDER BY id ASC LIMIT 1");
            return ($stmt && ($code = $stmt->fetchColumn())) ? $code : ROLES::USER;
        } catch (Exception $e) {
            error_log("ModelUser::getDefaultRoleCode error: " . $e->getMessage());
            return ROLES::USER;
        }
    }

    /**
     * Récupère le libellé d'une fonction à partir de son code
     */
    public function getFonctionLibelle(string $fonctionCode): ?string
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT libelle_fonction FROM " . TABLES::FONCTIONS . " WHERE code_fonction = ? LIMIT 1");
            $stmt->execute([$fonctionCode]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['libelle_fonction'] ?? null;
        } catch (Exception $e) {
            error_log("ModelUser::getFonctionLibelle error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère la fiche enseignant active liée à un utilisateur
     */
    public function getEnseignantProfile(string $userCode): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT * FROM " . TABLES::ENSEIGNANTS . " WHERE code_enseignant = ? AND statut_enseignant = '" . STATUTS::ACTIF . "' LIMIT 1");
            $stmt->execute([$userCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelUser::getEnseignantProfile error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère toutes les permissions uniques accordées à un ensemble de rôles
     */
    public function getPermissionsByRoles(array $roleCodes): array
    {
        if (empty($roleCodes)) {
            return [];
        }
        try {
            $inClause = implode(',', array_fill(0, count($roleCodes), '?'));
            $sql = "SELECT DISTINCT rp.permission_code 
                    FROM " . TABLES::ROLE_PERMISSIONS . " rp
                    JOIN " . TABLES::PERMISSIONS . " p ON rp.permission_code = p.code_permission
                    WHERE rp.role_code IN ($inClause) AND p.statut_permission = '" . STATUTS::ACTIF . "'";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($roleCodes);
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (Exception $e) {
            error_log("ModelUser::getPermissionsByRoles error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère l'année académique active courante (sans fallback sur une année clôturée)
     */
    public function getActiveAnneeAcademique(): ?array
    {
        try {
            $stmt = $this->getCon()->query("
                SELECT code_annee, libelle_annee 
                FROM `" . TABLES::ANNEES . "` 
                WHERE `statut_annee` = '" . STATUTS::ACTIF . "' 
                LIMIT 1
            ");
            return ($stmt && ($active = $stmt->fetch(PDO::FETCH_ASSOC))) ? $active : null;
        } catch (Exception $e) {
            error_log("ModelUser::getActiveAnneeAcademique error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Recherche un utilisateur par l'un de ses identifiants uniques (téléphone, e-mail, matricule, code)
     */
    public function findUserByLogin(string $login): ?array
    {
        try {
            $sql = "SELECT * FROM " . TABLES::USERS . " 
                    WHERE telephone_user = :login 
                       OR email_user = :login 
                       OR matricule_user = :login 
                       OR code_user = :login 
                    LIMIT 1";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([':login' => $login]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelUser::findUserByLogin error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crée un jeton sécurisé de réinitialisation de mot de passe (hash SHA-256 en base, validité 60 min)
     */
    public function createPasswordResetToken(string $userCode, string $email, int $ttlMinutes = 60, ?string $ipAddress = null): ?string
    {
        try {
            $db = $this->getCon();
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $expiresAt = date('Y-m-d H:i:s', time() + ($ttlMinutes * 60));

            // Invalider les anciens jetons non utilisés de cet utilisateur
            $stmtInv = $db->prepare("
                UPDATE `" . TABLES::PASSWORD_RESETS . "`
                SET `used_at` = NOW()
                WHERE `user_code` = ? AND `used_at` IS NULL
            ");
            $stmtInv->execute([$userCode]);

            $stmtIns = $db->prepare("
                INSERT INTO `" . TABLES::PASSWORD_RESETS . "` (`user_code`, `email_reset`, `token_hash`, `expires_at`, `ip_address`, `created_at`)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmtIns->execute([$userCode, $email, $tokenHash, $expiresAt, $ipAddress]);

            return $rawToken;
        } catch (Exception $e) {
            error_log("ModelUser::createPasswordResetToken error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifie la validité d'un jeton de réinitialisation (non utilisé, non expiré, utilisateur actif)
     */
    public function findValidPasswordReset(string $rawToken): ?array
    {
        $cleanToken = trim($rawToken);
        if ($cleanToken === '' || !ctype_xdigit($cleanToken)) {
            return null;
        }

        try {
            $tokenHash = hash('sha256', $cleanToken);
            $stmt = $this->getCon()->prepare("
                SELECT pr.*, u.id_user, u.nom_user, u.prenom_user, u.email_user, u.statut_user
                FROM `" . TABLES::PASSWORD_RESETS . "` pr
                INNER JOIN `" . TABLES::USERS . "` u ON u.code_user = pr.user_code
                WHERE pr.token_hash = ?
                  AND pr.used_at IS NULL
                  AND pr.expires_at > NOW()
                  AND u.statut_user = '" . STATUTS::ACTIF . "'
                LIMIT 1
            ");
            $stmt->execute([$tokenHash]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelUser::findValidPasswordReset error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Marque un jeton de réinitialisation comme utilisé (usage unique)
     */
    public function markPasswordResetUsed(int $idReset, string $userCode): bool
    {
        try {
            $stmt = $this->getCon()->prepare("
                UPDATE `" . TABLES::PASSWORD_RESETS . "`
                SET `used_at` = NOW()
                WHERE (`id_reset` = ? OR `user_code` = ?) AND `used_at` IS NULL
            ");
            return $stmt->execute([$idReset, $userCode]);
        } catch (Exception $e) {
            error_log("ModelUser::markPasswordResetUsed error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère la fiche détaillée d'un utilisateur avec le libellé de sa fonction.
     */
    public function getUserDetailsById(int $userId): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT u.*, f.libelle_fonction
                FROM " . TABLES::USERS . " u
                LEFT JOIN " . TABLES::FONCTIONS . " f ON f.code_fonction = u.fonction_code
                WHERE u.id_user = ?
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            error_log("ModelUser::getUserDetailsById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère le profil enseignant lié à un compte utilisateur (actif ou inactif).
     */
    public function getEnseignantLink(string $userCode): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT id_enseignant, code_enseignant, grade_enseignant, type_contrat, numero_autorisation, statut_enseignant
                FROM " . TABLES::ENSEIGNANTS . "
                WHERE code_enseignant = ?
                LIMIT 1
            ");
            $stmt->execute([$userCode]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            error_log("ModelUser::getEnseignantLink error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère la liste détaillée des permissions RBAC effectives de l'utilisateur (avec libellés et modules).
     */
    public function getUserPermissionsDetailed(string $userCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT DISTINCT
                    p.code_permission,
                    p.libelle_permission,
                    p.module_permission
                FROM " . TABLES::USER_ROLES . " ur
                INNER JOIN " . TABLES::ROLE_PERMISSIONS . " rp ON rp.role_code = ur.role_code
                INNER JOIN " . TABLES::PERMISSIONS . " p ON p.code_permission = rp.permission_code
                WHERE ur.user_code = ?
                  AND p.statut_permission = '" . STATUTS::ACTIF . "'
                ORDER BY p.module_permission ASC, p.libelle_permission ASC
            ");
            $stmt->execute([$userCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelUser::getUserPermissionsDetailed error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les accès aux années académiques avec leurs libellés et statuts.
     */
    public function getUserAnneeAccesDetails(string $userCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT
                    uaa.annee_code,
                    uaa.niveau_acces,
                    uaa.created_at_acces,
                    a.id_annee,
                    a.libelle_annee,
                    a.statut_annee
                FROM " . TABLES::USER_ANNEE_ACCES . " uaa
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = uaa.annee_code
                WHERE uaa.user_code = ?
                ORDER BY a.id_annee DESC
            ");
            $stmt->execute([$userCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelUser::getUserAnneeAccesDetails error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les dernières actions journalisées de l'utilisateur dans logs_activites.
     */
    public function getUserRecentLogs(string $userCode, int $limit = 15): array
    {
        try {
            $lim = max(1, min(50, $limit));
            $stmt = $this->getCon()->prepare("
                SELECT id_log, action, module, entite_id, description, ip_address, created_at
                FROM " . TABLES::LOGS_ACTIVITES . "
                WHERE user_code = ?
                ORDER BY created_at DESC, id_log DESC
                LIMIT {$lim}
            ");
            $stmt->execute([$userCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelUser::getUserRecentLogs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre total d'actions journalisées pour cet utilisateur.
     */
    public function getUserLogsCount(string $userCode): int
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT COUNT(*) FROM " . TABLES::LOGS_ACTIVITES . " WHERE user_code = ?
            ");
            $stmt->execute([$userCode]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("ModelUser::getUserLogsCount error: " . $e->getMessage());
            return 0;
        }
    }
}

