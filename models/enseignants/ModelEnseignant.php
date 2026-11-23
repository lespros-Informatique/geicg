<?php

class ModelEnseignant extends BaseModel
{
    protected string $table = TABLES::ENSEIGNANTS;
    protected string $primaryKey = 'id_enseignant';
    protected ?string $statusField = 'statut_enseignant';
    protected ?string $createdAtField = 'created_at_enseignant';

    public function getStats(): array
    {
        $db = $this->getCon();
        try {
            $stmt = $db->query("
                SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN LOWER(TRIM(e.statut_enseignant)) = '" . STATUTS::ACTIF . "' THEN 1 ELSE 0 END) AS actifs,
                    SUM(CASE WHEN LOWER(TRIM(e.type_contrat)) = 'permanent' THEN 1 ELSE 0 END) AS permanents,
                    SUM(CASE WHEN LOWER(TRIM(e.type_contrat)) IN ('vacataire', 'prestataire') THEN 1 ELSE 0 END) AS vacataires,
                    SUM(CASE WHEN UPPER(TRIM(COALESCE(u.sexe_user, ''))) IN ('M', 'MASCULIN') THEN 1 ELSE 0 END) AS hommes,
                    SUM(CASE WHEN UPPER(TRIM(COALESCE(u.sexe_user, ''))) IN ('F', 'FEMININ') THEN 1 ELSE 0 END) AS femmes
                FROM " . TABLES::ENSEIGNANTS . " e
                LEFT JOIN " . TABLES::USERS . " u ON u.code_user = e.code_enseignant
            ");
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total' => (int)($res['total'] ?? 0),
                'actifs' => (int)($res['actifs'] ?? 0),
                'permanents' => (int)($res['permanents'] ?? 0),
                'vacataires' => (int)($res['vacataires'] ?? 0),
                'hommes' => (int)($res['hommes'] ?? 0),
                'femmes' => (int)($res['femmes'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log("ModelEnseignant::getStats error: " . $e->getMessage());
            return [
                'total' => 0,
                'actifs' => 0,
                'permanents' => 0,
                'vacataires' => 0,
                'hommes' => 0,
                'femmes' => 0
            ];
        }
    }

    public function getDistinctGrades(): array
    {
        try {
            $stmt = $this->getCon()->query("
                SELECT DISTINCT grade_enseignant 
                FROM " . TABLES::ENSEIGNANTS . " 
                WHERE grade_enseignant IS NOT NULL AND TRIM(grade_enseignant) != ''
                ORDER BY grade_enseignant ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAll(?string $anneeCode = null): array
    {
        $targetAnnee = $anneeCode ?? (string)Session::get('annee_active_code', '');
        $edtYearCond = '';
        $params = [];
        if ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL') {
            $edtYearCond = " AND edt.annee_code = ?";
            $params[] = $targetAnnee;
        }
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
                   u.last_connexion,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.email_user AS email_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.sexe_user AS sexe_enseignant,
                   ROUND(COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(edt.heure_fin, edt.heure_debut))) / 3600, 0), 1) AS volume_horaire,
                   GROUP_CONCAT(DISTINCT edt.annee_code SEPARATOR ',') AS annees_codes,
                   GROUP_CONCAT(DISTINCT edt.matiere_code SEPARATOR ',') AS matieres_codes,
                   GROUP_CONCAT(DISTINCT m.libelle_matiere SEPARATOR ', ') AS matieres_libelles
            FROM " . TABLES::ENSEIGNANTS . " e
            JOIN " . TABLES::USERS . " u ON u.code_user = e.code_enseignant
            LEFT JOIN " . TABLES::EMPLOIS_TEMPS . " edt ON (
                edt.enseignant_code = e.code_enseignant
                AND (edt.statut_emploi = '" . STATUTS::ACTIF . "' OR edt.statut_emploi IS NULL)" . $edtYearCond . "
            )
            LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
            GROUP BY e.id_enseignant
            ORDER BY e.id_enseignant DESC
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
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
                   u.last_connexion,
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
                   u.last_connexion,
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
                   u.last_connexion,
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
     * Récupère les cours / matières planifiés regroupés par classe et matière pour un enseignant
     */
    public function getCoursForEnseignant(string $codeEnseignant, ?string $anneeCode = null): array
    {
        try {
            $targetAnnee = $anneeCode !== null ? $anneeCode : (string)Session::get('annee_active_code', '');
            $sql = "
                SELECT
                    edt.matiere_code,
                    edt.classe_code,
                    m.id_matiere,
                    m.libelle_matiere,
                    cl.id_classe,
                    cl.libelle_classe,
                    f.libelle_filiere,
                    n.libelle_niveau,
                    COUNT(edt.id_emploi) AS nb_creneaux,
                    GROUP_CONCAT(DISTINCT s.libelle_salle ORDER BY s.libelle_salle SEPARATOR ', ') AS salles_noms,
                    GROUP_CONCAT(
                        DISTINCT CONCAT(
                            UPPER(LEFT(edt.jour, 1)),
                            SUBSTRING(edt.jour, 2),
                            ' ',
                            TIME_FORMAT(edt.heure_debut, '%H:%i'),
                            '-',
                            TIME_FORMAT(edt.heure_fin, '%H:%i')
                        )
                        ORDER BY FIELD(edt.jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'), edt.heure_debut
                        SEPARATOR ' · '
                    ) AS creneaux_resume,
                    ROUND(SUM(TIME_TO_SEC(TIMEDIFF(edt.heure_fin, edt.heure_debut))) / 3600, 1) AS volume_horaire
                FROM " . TABLES::EMPLOIS_TEMPS . " edt
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::SALLES . " s ON s.code_salle = edt.salle_code
                WHERE edt.enseignant_code = ?
                  AND (edt.statut_emploi = '" . STATUTS::ACTIF . "' OR edt.statut_emploi IS NULL)
            ";
            $params = [$codeEnseignant];
            if ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL') {
                $sql .= " AND edt.annee_code = ?";
                $params[] = $targetAnnee;
            }
            $sql .= "
                GROUP BY
                    edt.matiere_code,
                    edt.classe_code,
                    m.id_matiere,
                    m.libelle_matiere,
                    cl.id_classe,
                    cl.libelle_classe,
                    f.libelle_filiere,
                    n.libelle_niveau
                ORDER BY cl.libelle_classe ASC, m.libelle_matiere ASC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getCoursForEnseignant error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère le planning hebdomadaire détaillé (créneau par créneau) d'un enseignant.
     */
    public function getPlanningForEnseignant(string $codeEnseignant, ?string $anneeCode = null): array
    {
        try {
            $targetAnnee = $anneeCode !== null ? $anneeCode : (string)Session::get('annee_active_code', '');
            $sql = "
                SELECT
                    edt.id_emploi,
                    edt.jour,
                    edt.heure_debut,
                    edt.heure_fin,
                    edt.classe_code,
                    edt.matiere_code,
                    edt.salle_code,
                    cl.id_classe,
                    cl.libelle_classe,
                    f.libelle_filiere,
                    n.libelle_niveau,
                    m.id_matiere,
                    m.libelle_matiere,
                    s.id_salle,
                    s.libelle_salle,
                    ROUND(TIME_TO_SEC(TIMEDIFF(edt.heure_fin, edt.heure_debut)) / 3600, 1) AS duree_heures
                FROM " . TABLES::EMPLOIS_TEMPS . " edt
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
                LEFT JOIN " . TABLES::SALLES . " s ON s.code_salle = edt.salle_code
                WHERE edt.enseignant_code = ?
                  AND (edt.statut_emploi = '" . STATUTS::ACTIF . "' OR edt.statut_emploi IS NULL)
            ";
            $params = [$codeEnseignant];
            if ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL') {
                $sql .= " AND edt.annee_code = ?";
                $params[] = $targetAnnee;
            }
            $sql .= "
                ORDER BY
                    FIELD(edt.jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'),
                    edt.heure_debut ASC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getPlanningForEnseignant error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Statistiques consolidées de charge pédagogique pour un enseignant sur la session ciblée.
     */
    public function getStatsForEnseignant(string $codeEnseignant, ?string $anneeCode = null): array
    {
        $targetAnnee = $anneeCode !== null ? $anneeCode : (string)Session::get('annee_active_code', '');
        $hasYear = ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL');
        $con = $this->getCon();

        $sqlEdt = "
            SELECT
                COUNT(*) AS total_creneaux,
                COALESCE(ROUND(SUM(TIME_TO_SEC(TIMEDIFF(heure_fin, heure_debut))) / 3600, 1), 0) AS total_heures_hebdo,
                COUNT(DISTINCT jour) AS total_jours_actifs,
                COUNT(DISTINCT classe_code) AS total_classes,
                COUNT(DISTINCT matiere_code) AS total_matieres,
                COUNT(DISTINCT salle_code) AS total_salles
            FROM " . TABLES::EMPLOIS_TEMPS . "
            WHERE enseignant_code = ?
              AND (statut_emploi = '" . STATUTS::ACTIF . "' OR statut_emploi IS NULL)
        ";
        $paramsEdt = [$codeEnseignant];
        if ($hasYear) {
            $sqlEdt .= " AND annee_code = ?";
            $paramsEdt[] = $targetAnnee;
        }
        $stmtEdt = $con->prepare($sqlEdt);
        $stmtEdt->execute($paramsEdt);
        $edt = $stmtEdt->fetch(PDO::FETCH_ASSOC) ?: [];

        $sqlEtu = "
            SELECT COUNT(DISTINCT i.etudiant_code) AS total_etudiants
            FROM " . TABLES::INSCRIPTIONS . " i
            INNER JOIN (
                SELECT DISTINCT classe_code
                FROM " . TABLES::EMPLOIS_TEMPS . "
                WHERE enseignant_code = ?
                  AND (statut_emploi = '" . STATUTS::ACTIF . "' OR statut_emploi IS NULL)"
                  . ($hasYear ? " AND annee_code = ?" : "") . "
            ) ecls ON ecls.classe_code = i.classe_code
            WHERE i.statut_inscription != '" . STATUTS::ANNULE . "'"
              . ($hasYear ? " AND i.annee_code = ?" : "") . "
        ";
        $paramsEtu = [$codeEnseignant];
        if ($hasYear) {
            $paramsEtu[] = $targetAnnee;
            $paramsEtu[] = $targetAnnee;
        }
        $stmtEtu = $con->prepare($sqlEtu);
        $stmtEtu->execute($paramsEtu);
        $totalEtudiants = (int)$stmtEtu->fetchColumn();

        return [
            'total_creneaux'     => (int)($edt['total_creneaux'] ?? 0),
            'total_heures_hebdo' => (float)($edt['total_heures_hebdo'] ?? 0),
            'total_jours_actifs' => (int)($edt['total_jours_actifs'] ?? 0),
            'total_classes'      => (int)($edt['total_classes'] ?? 0),
            'total_matieres'     => (int)($edt['total_matieres'] ?? 0),
            'total_salles'       => (int)($edt['total_salles'] ?? 0),
            'total_etudiants'    => $totalEtudiants,
        ];
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

            $fkErrRole = ForeignKeyValidator::validate($db, TABLES::USER_ROLES, [
                'user_code' => $codeUser,
                'role_code' => ROLES::ENSEIGNANT
            ]);
            if ($fkErrRole !== null) {
                $db->rollBack();
                $errorMsg = $fkErrRole;
                return null;
            }

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

    /**
     * Récupère le programme des cours et séances d'une journée donnée (avec filtres optionnels enseignant/année)
     */
    public function getProgrammeJourData(?string $date = null, ?string $ensCode = null, ?string $anneeCode = null): array
    {
        $date = trim($date ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $jours = [
            1 => 'lundi', 2 => 'mardi', 3 => 'mercredi',
            4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'
        ];
        $joursFr = [
            1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi',
            4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'
        ];
        $moisFr = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        $time = strtotime($date);
        $numJour = (int)date('N', $time);
        $jourDb = $jours[$numJour] ?? 'lundi';
        $jourFr = $joursFr[$numJour] ?? 'Lundi';
        $moisNum = (int)date('n', $time);
        $dateFormatee = $jourFr . ' ' . date('d', $time) . ' ' . ($moisFr[$moisNum] ?? '') . ' ' . date('Y', $time);
        $isToday = ($date === date('Y-m-d'));

        $sql = "
            SELECT edt.id_emploi,
                   edt.code_emploi,
                   edt.classe_code,
                   edt.matiere_code,
                   edt.enseignant_code,
                   edt.salle_code,
                   edt.annee_code,
                   edt.jour,
                   edt.heure_debut,
                   edt.heure_fin,
                   ROUND(TIME_TO_SEC(TIMEDIFF(edt.heure_fin, edt.heure_debut)) / 3600, 1) AS duree,
                   m.libelle_matiere,
                   cl.libelle_classe,
                   s.libelle_salle,
                   u.code_user,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.email_user AS email_enseignant,
                   u.photo_user,
                   e.type_contrat,
                   e.grade_enseignant
            FROM " . TABLES::EMPLOIS_TEMPS . " edt
            LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
            LEFT JOIN " . TABLES::SALLES . " s ON s.code_salle = edt.salle_code
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = edt.enseignant_code
            LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = edt.enseignant_code
            WHERE LOWER(edt.jour) = ?
              AND (edt.statut_emploi = '" . STATUTS::ACTIF . "' OR edt.statut_emploi IS NULL)
        ";
        $params = [$jourDb];

        if (!empty($anneeCode)) {
            $sql .= " AND edt.annee_code = ? ";
            $params[] = $anneeCode;
        }

        if (!empty($ensCode)) {
            $sql .= " AND edt.enseignant_code = ? ";
            $params[] = $ensCode;
        }

        $sql .= " ORDER BY edt.heure_debut ASC, cl.libelle_classe ASC ";

        $items = [];
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $ex) {
            error_log("ModelEnseignant::getProgrammeJourData error: " . $ex->getMessage());
            $items = [];
        }

        $totalHeures = 0;
        $profsSet = [];
        $classesSet = [];
        $sallesSet = [];
        foreach ($items as $item) {
            $totalHeures += (float)($item['duree'] ?? 0);
            if (!empty($item['enseignant_code'])) $profsSet[$item['enseignant_code']] = true;
            if (!empty($item['classe_code'])) $classesSet[$item['classe_code']] = true;
            if (!empty($item['salle_code'])) $sallesSet[$item['salle_code']] = true;
        }

        $ensInfo = null;
        if (!empty($ensCode)) {
            try {
                $stmtEns = $this->getCon()->prepare("
                    SELECT e.*, u.nom_user, u.prenom_user, u.telephone_user, u.email_user, u.photo_user 
                    FROM " . TABLES::ENSEIGNANTS . " e 
                    LEFT JOIN " . TABLES::USERS . " u ON u.code_user = e.code_enseignant 
                    WHERE e.code_enseignant = ? OR u.code_user = ? 
                    LIMIT 1
                ");
                $stmtEns->execute([$ensCode, $ensCode]);
                $ensInfo = $stmtEns->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (Exception $e) {}
        }

        return [
            'date' => $date,
            'jour' => $jourDb,
            'jour_libelle' => $jourFr,
            'date_formatee' => $dateFormatee,
            'is_today' => $isToday,
            'stats' => [
                'total_cours' => count($items),
                'total_heures' => round($totalHeures, 1),
                'nb_profs' => count($profsSet),
                'nb_classes' => count($classesSet),
                'nb_salles' => count($sallesSet)
            ],
            'items' => $items,
            'enseignant' => $ensInfo
        ];
    }
}
