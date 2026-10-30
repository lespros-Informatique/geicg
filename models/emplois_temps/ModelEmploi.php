<?php

class ModelEmploi extends BaseModel
{
    protected string $table = TABLES::EMPLOIS_TEMPS;
    protected string $primaryKey = 'id_emploi';
    protected ?string $statusField = 'statut_emploi';
    protected ?string $createdAtField = 'created_at_emploi';

    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null): array
    {
        $sql = "SELECT * FROM v_emplois_temps_complets";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "classe_code = ?";
            $params[] = $classeCode;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY jour ASC, heure_debut ASC ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all emplois_temps: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Résumé des emplois du temps agrégé par classe
     */
    public function getClassesSummary(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null): array
    {
        $sql = "
            SELECT 
                cl.code_classe,
                cl.libelle_classe,
                n.libelle_niveau,
                COUNT(edt.id_emploi) AS nb_creneaux,
                COUNT(DISTINCT edt.jour) AS nb_jours,
                COUNT(DISTINCT edt.matiere_code) AS nb_matieres,
                COUNT(DISTINCT edt.enseignant_code) AS nb_profs,
                GROUP_CONCAT(DISTINCT m.libelle_matiere ORDER BY m.libelle_matiere SEPARATOR ', ') AS matieres_noms,
                MAX(edt.created_at_emploi) AS last_update,
                SUM(TIME_TO_SEC(TIMEDIFF(edt.heure_fin, edt.heure_debut))) / 3600 AS total_heures
            FROM " . TABLES::EMPLOIS_TEMPS . " edt
            INNER JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
            LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
        ";
        $conditions = [];
        $params = [];

        if (!empty($anneeCode)) {
            $conditions[] = "edt.annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "cl.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "edt.classe_code = ?";
            $params[] = $classeCode;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " GROUP BY cl.code_classe, cl.libelle_classe, n.libelle_niveau ORDER BY cl.libelle_classe ASC ";

        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEmploi getClassesSummary error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recherche l'enseignant affecté à une classe et matière
     */
    public function getAssignedTeacher(string $classeCode, string $matiereCode): ?array
    {
        try {
            $db = $this->getCon();
            // 1. Recherche précise par classe_code + matiere_code via la vue v_dash_pedagogie_affectations
            $stmt = $db->prepare("
                SELECT * FROM v_dash_pedagogie_affectations
                WHERE classe_code = ? AND matiere_code = ? AND statut_enseignant_matiere = 'actif'
                LIMIT 1
            ");
            $stmt->execute([$classeCode, $matiereCode]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Recherche de secours par matière
            if (!$row) {
                $stmtGlobal = $db->prepare("
                    SELECT * FROM v_dash_pedagogie_affectations
                    WHERE matiere_code = ? AND statut_enseignant_matiere = 'actif'
                    LIMIT 1
                ");
                $stmtGlobal->execute([$matiereCode]);
                $row = $stmtGlobal->fetch(PDO::FETCH_ASSOC);
            }

            return $row ?: null;
        } catch (Exception $e) {
            error_log("ModelEmploi getAssignedTeacher error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Détecte les conflits de planning (salle, enseignant, classe)
     */
    public function findScheduleConflicts(string $classeCode, string $salleCode, string $enseignantCode, string $jour, string $heureDebut, string $heureFin, $excludeId = null): array
    {
        $conflicts = [];
        $jour = strtolower(trim($jour));
        $heureDebut = trim($heureDebut);
        $heureFin = trim($heureFin);

        if (empty($jour) || empty($heureDebut) || empty($heureFin)) {
            return $conflicts;
        }

        if (strtotime($heureFin) <= strtotime($heureDebut)) {
            $conflicts[] = [
                'type' => 'horaire',
                'title' => 'Incohérence des horaires',
                'message' => "L'heure de fin ($heureFin) doit être strictement postérieure à l'heure de début ($heureDebut)."
            ];
            return $conflicts;
        }

        try {
            $db = $this->getCon();

            $baseSql = "
                SELECT edt.*
                FROM v_emplois_temps_complets edt
                WHERE edt.statut_emploi = 'actif'
                  AND LOWER(edt.jour) = ?
                  AND (edt.heure_debut < ? AND edt.heure_fin > ?)
            ";
            $excludeSql = $excludeId ? " AND edt.id_emploi != " . (int)$excludeId : "";

            // 1. Vérification Conflit Salle
            if (!empty($salleCode)) {
                $stmtS = $db->prepare($baseSql . " AND edt.salle_code = ?" . $excludeSql);
                $stmtS->execute([$jour, $heureFin, $heureDebut, $salleCode]);
                $rowS = $stmtS->fetch(PDO::FETCH_ASSOC);
                if ($rowS) {
                    $salleNom = $rowS['libelle_salle'] ?: 'Cette salle';
                    $debutConf = substr($rowS['heure_debut'], 0, 5);
                    $finConf = substr($rowS['heure_fin'], 0, 5);
                    $conflicts[] = [
                        'type' => 'salle',
                        'title' => 'Salle déjà occupée',
                        'message' => "La salle <strong>" . htmlspecialchars($salleNom) . "</strong> est déjà occupée de <strong>$debutConf à $finConf</strong> par la classe <em>" . htmlspecialchars($rowS['libelle_classe'] ?: 'Autre classe') . "</em> (" . htmlspecialchars($rowS['libelle_matiere'] ?: 'Cours') . ")."
                    ];
                }
            }

            // 2. Vérification Conflit Enseignant
            if (!empty($enseignantCode)) {
                $stmtE = $db->prepare($baseSql . " AND edt.enseignant_code = ?" . $excludeSql);
                $stmtE->execute([$jour, $heureFin, $heureDebut, $enseignantCode]);
                $rowE = $stmtE->fetch(PDO::FETCH_ASSOC);
                if ($rowE) {
                    $profNom = $rowE['nom_prof'] ?: 'Cet enseignant';
                    $debutConf = substr($rowE['heure_debut'], 0, 5);
                    $finConf = substr($rowE['heure_fin'], 0, 5);
                    $conflicts[] = [
                        'type' => 'enseignant',
                        'title' => 'Enseignant non disponible',
                        'message' => "L'enseignant <strong>" . htmlspecialchars($profNom) . "</strong> donne déjà cours de <strong>$debutConf à $finConf</strong> avec la classe <em>" . htmlspecialchars($rowE['libelle_classe'] ?: 'Autre classe') . "</em> en salle " . htmlspecialchars($rowE['libelle_salle'] ?: 'N/A') . "."
                    ];
                }
            }

            // 3. Vérification Conflit Classe
            if (!empty($classeCode)) {
                $stmtC = $db->prepare($baseSql . " AND edt.classe_code = ?" . $excludeSql);
                $stmtC->execute([$jour, $heureFin, $heureDebut, $classeCode]);
                $rowC = $stmtC->fetch(PDO::FETCH_ASSOC);
                if ($rowC) {
                    $classeNom = $rowC['libelle_classe'] ?: 'Cette classe';
                    $debutConf = substr($rowC['heure_debut'], 0, 5);
                    $finConf = substr($rowC['heure_fin'], 0, 5);
                    $conflicts[] = [
                        'type' => 'classe',
                        'title' => 'Classe déjà en cours',
                        'message' => "La classe <strong>" . htmlspecialchars($classeNom) . "</strong> a déjà un cours de <em>" . htmlspecialchars($rowC['libelle_matiere'] ?: 'Matière') . "</em> programmé de <strong>$debutConf à $finConf</strong>."
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("ModelEmploi findScheduleConflicts error: " . $e->getMessage());
        }

        return $conflicts;
    }

    /**
     * Récupère le libellé d'une classe par son code
     */
    public function getClasseLibelle(string $classeCode): ?string
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT libelle_classe FROM " . TABLES::CLASSES . " WHERE code_classe = ? LIMIT 1");
            $stmt->execute([$classeCode]);
            return $stmt->fetchColumn() ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Détails complets d'un créneau d'emploi du temps
     */
    public function getDetailsById(int $id): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT edt.*, 
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       m.libelle_matiere,
                       s.libelle_salle, s.capacite_salle,
                       u.nom_user as nom_prof,
                       u.prenom_user as prenom_prof,
                       e.grade_enseignant
                FROM " . TABLES::EMPLOIS_TEMPS . " edt
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
                LEFT JOIN " . TABLES::SALLES . " s ON s.code_salle = edt.salle_code
                LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = edt.enseignant_code
                LEFT JOIN " . TABLES::USERS . " u ON u.code_user = edt.enseignant_code
                WHERE edt.id_emploi = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelEmploi getDetailsById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Emploi du temps complet d'un enseignant
     */
    public function getTeacherSchedule(string $ensCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT edt.*, 
                       cl.libelle_classe, 
                       m.libelle_matiere, 
                       s.libelle_salle,
                       CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS nom_prof
                FROM " . TABLES::EMPLOIS_TEMPS . " edt
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
                LEFT JOIN " . TABLES::SALLES . " s ON s.code_salle = edt.salle_code
                LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = edt.enseignant_code
                LEFT JOIN " . TABLES::USERS . " u ON u.code_user = edt.enseignant_code
                WHERE edt.enseignant_code = ? AND (edt.statut_emploi = 'actif' OR edt.statut_emploi IS NULL)
                ORDER BY edt.jour ASC, edt.heure_debut ASC
            ");
            $stmt->execute([$ensCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEmploi getTeacherSchedule error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Réinitialise complètement l'emploi du temps d'une classe
     */
    public function resetByClasse(string $classeCode): bool
    {
        try {
            $stmt = $this->getCon()->prepare("DELETE FROM " . TABLES::EMPLOIS_TEMPS . " WHERE classe_code = ?");
            return $stmt->execute([$classeCode]);
        } catch (Exception $e) {
            error_log("ModelEmploi resetByClasse error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Matrice des créneaux pour une classe donnée avec informations de la classe
     */
    public function getClassScheduleMatrix(string $classeCode): array
    {
        try {
            $db = $this->getCon();
            $stmtCls = $db->prepare("
                SELECT cl.libelle_classe, n.libelle_niveau 
                FROM " . TABLES::CLASSES . " cl 
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code 
                WHERE cl.code_classe = ? LIMIT 1
            ");
            $stmtCls->execute([$classeCode]);
            $classeInfo = $stmtCls->fetch(PDO::FETCH_ASSOC) ?: null;

            $stmt = $db->prepare("
                SELECT edt.*, 
                       m.libelle_matiere, 
                       s.libelle_salle, 
                       CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS nom_prof
                FROM " . TABLES::EMPLOIS_TEMPS . " edt
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
                LEFT JOIN " . TABLES::SALLES . " s ON s.code_salle = edt.salle_code
                LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = edt.enseignant_code
                LEFT JOIN " . TABLES::USERS . " u ON u.code_user = edt.enseignant_code
                WHERE edt.classe_code = ?
                ORDER BY edt.jour ASC, edt.heure_debut ASC
            ");
            $stmt->execute([$classeCode]);
            $slots = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return [
                'classe' => $classeInfo,
                'slots' => $slots
            ];
        } catch (Exception $e) {
            error_log("ModelEmploi getClassScheduleMatrix error: " . $e->getMessage());
            return [
                'classe' => null,
                'slots' => []
            ];
        }
    }
}
