<?php

class ModelComposition extends BaseModel
{
    protected string $table = TABLES::COMPOSITIONS;
    protected string $primaryKey = 'id_composition';
    protected ?string $statusField = 'statut_composition';
    protected ?string $createdAtField = 'created_at_composition';

    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null, ?string $typeComposition = null): array
    {
        $sql = "
            SELECT comp.*, 
                   s.libelle_semestre,
                   GROUP_CONCAT(DISTINCT CONCAT(f.libelle_filiere, ' (', n.libelle_niveau, ')') ORDER BY f.libelle_filiere SEPARATOR ', ') AS filieres_cible_names,
                   GROUP_CONCAT(DISTINCT cl.libelle_classe ORDER BY cl.libelle_classe SEPARATOR ', ') AS classes_cible_names
            FROM " . TABLES::COMPOSITIONS . " comp
            LEFT JOIN " . TABLES::SEMESTRES . " s ON s.code_semestre = comp.semestre_code
            LEFT JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON cnf.composition_code = comp.code_composition
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cnf.niveau_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cnf.filiere_code
            LEFT JOIN " . TABLES::CLASSES . " cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
        ";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "comp.annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "cnf.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "cl.code_classe = ?";
            $params[] = $classeCode;
        }
        if (!empty($typeComposition)) {
            $conditions[] = "comp.type_composition = ?";
            $params[] = $typeComposition;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " GROUP BY comp.id_composition ORDER BY comp.id_composition DESC ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all compositions: " . $e->getMessage());
            return [];
        }
    }

    public function saveTargetClasses(string $compositionCode, array $targetItems): bool
    {
        try {
            $db = $this->getCon();
            $stmtDel = $db->prepare("DELETE FROM " . TABLES::COMPOSITION_NIVEAU_FILIERE . " WHERE composition_code = ?");
            $stmtDel->execute([$compositionCode]);

            if (empty($targetItems)) return true;

            $validator = new Validator();
            $stmtIns = $db->prepare("
                INSERT IGNORE INTO " . TABLES::COMPOSITION_NIVEAU_FILIERE . " (code_composition_niveau_filiere, composition_code, niveau_code, filiere_code)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($targetItems as $item) {
                if (empty($item['niveau_code']) || empty($item['filiere_code'])) continue;
                $codeCible = $validator->generateCode(TABLES::COMPOSITION_NIVEAU_FILIERE, 'code_composition_niveau_filiere', 'CNF-', 8);
                $stmtIns->execute([
                    $codeCible,
                    $compositionCode,
                    $item['niveau_code'],
                    $item['filiere_code']
                ]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Save target classes error: " . $e->getMessage());
            return false;
        }
    }

    public function getTargetClasses(string $compositionCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT cnf.*, n.libelle_niveau, f.libelle_filiere, cl.code_classe, cl.libelle_classe,
                       (SELECT COUNT(*) 
                        FROM " . TABLES::COMPOSITION_MATIERES . " cm 
                        WHERE cm.composition_code = cnf.composition_code 
                          AND cm.composition_niveau_filiere_code = cnf.code_composition_niveau_filiere) AS nb_matieres
                FROM " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cnf.niveau_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cnf.filiere_code
                LEFT JOIN " . TABLES::CLASSES . " cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
                WHERE cnf.composition_code = ?
                GROUP BY cnf.code_composition_niveau_filiere
                ORDER BY n.libelle_niveau ASC, f.libelle_filiere ASC
            ");
            $stmt->execute([$compositionCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get target classes error: " . $e->getMessage());
            return [];
        }
    }

    public function getTargetClassesByCompositions(array $compositionCodes): array
    {
        if (empty($compositionCodes)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($compositionCodes), '?'));
        try {
            $stmt = $this->getCon()->prepare("
                SELECT cnf.*, n.libelle_niveau, f.libelle_filiere, cl.code_classe, cl.libelle_classe
                FROM " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cnf.niveau_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cnf.filiere_code
                LEFT JOIN " . TABLES::CLASSES . " cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
                WHERE cnf.composition_code IN ({$placeholders})
                GROUP BY cnf.code_composition_niveau_filiere, cl.code_classe
                ORDER BY n.libelle_niveau ASC, f.libelle_filiere ASC
            ");
            $stmt->execute(array_values($compositionCodes));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $grouped = [];
            foreach ($rows as $r) {
                $grouped[$r['composition_code']][] = $r;
            }
            return $grouped;
        } catch (Exception $e) {
            error_log("Get target classes batch error: " . $e->getMessage());
            return [];
        }
    }

    public function saveCompositionMatieres(string $compositionCode, string $compositionNiveauFiliereCode, array $matiereCodes): bool
    {
        try {
            $db = $this->getCon();
            $stmtDel = $db->prepare("DELETE FROM " . TABLES::COMPOSITION_MATIERES . " WHERE composition_code = ? AND composition_niveau_filiere_code = ?");
            $stmtDel->execute([$compositionCode, $compositionNiveauFiliereCode]);

            if (empty($matiereCodes)) return true;

            $stmtIns = $db->prepare("
                INSERT INTO " . TABLES::COMPOSITION_MATIERES . " (composition_code, composition_niveau_filiere_code, matiere_code)
                VALUES (?, ?, ?)
            ");

            foreach ($matiereCodes as $mCode) {
                if (!empty($mCode)) {
                    $fkErr = ForeignKeyValidator::validate($db, TABLES::COMPOSITION_MATIERES, [
                        'composition_code' => $compositionCode,
                        'matiere_code' => $mCode
                    ]);
                    if ($fkErr !== null) {
                        error_log("Save composition matieres FK Error: " . $fkErr);
                        continue;
                    }

                    $stmtIns->execute([$compositionCode, $compositionNiveauFiliereCode, $mCode]);
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Save composition matieres error: " . $e->getMessage());
            return false;
        }
    }

    public function getCompositionMatieres(string $compositionCode, string $compositionNiveauFiliereCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT cm.*, m.libelle_matiere
                FROM " . TABLES::COMPOSITION_MATIERES . " cm
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = cm.matiere_code
                WHERE cm.composition_code = ? AND cm.composition_niveau_filiere_code = ?
            ");
            $stmt->execute([$compositionCode, $compositionNiveauFiliereCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getByClasseMatiere(?string $classeCode, ?string $matiereCode, ?string $semestreCode = null, ?string $typeComposition = null): array
    {
        if (empty($classeCode) || empty($matiereCode)) {
            return [];
        }

        try {
            $db = $this->getCon();
            // 1. Fetch target class (niveau and filiere)
            $stmtCl = $db->prepare("SELECT code_classe, libelle_classe, niveau_code, filiere_code FROM " . TABLES::CLASSES . " WHERE code_classe = ?");
            $stmtCl->execute([$classeCode]);
            $classeRow = $stmtCl->fetch(PDO::FETCH_ASSOC);

            if (!$classeRow) return [];

            $sql = "
                SELECT DISTINCT comp.*, 
                       ? AS libelle_classe,
                       m.libelle_matiere, 
                       s.libelle_semestre
                FROM " . TABLES::COMPOSITIONS . " comp
                INNER JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON cnf.composition_code = comp.code_composition
                INNER JOIN " . TABLES::COMPOSITION_MATIERES . " cm ON (cm.composition_code = comp.code_composition AND cm.composition_niveau_filiere_code = cnf.code_composition_niveau_filiere)
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = cm.matiere_code
                LEFT JOIN " . TABLES::SEMESTRES . " s ON s.code_semestre = comp.semestre_code
                WHERE cnf.niveau_code = ? AND cnf.filiere_code = ? AND cm.matiere_code = ?
            ";
            $params = [$classeRow['libelle_classe'], $classeRow['niveau_code'], $classeRow['filiere_code'], $matiereCode];

            if (!empty($semestreCode)) {
                $sql .= " AND comp.semestre_code = ?";
                $params[] = $semestreCode;
            }
            if (!empty($typeComposition)) {
                $sql .= " AND comp.type_composition = ?";
                $params[] = $typeComposition;
            }

            $sql .= " ORDER BY comp.id_composition DESC ";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Fallback: Check target compositions matching class (Niveau + Filière) if subjects matrix not yet saved
            if (empty($results)) {
                $sqlFallback = "
                    SELECT DISTINCT comp.*, 
                           ? AS libelle_classe,
                           m.libelle_matiere, 
                           s.libelle_semestre
                    FROM " . TABLES::COMPOSITIONS . " comp
                    INNER JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON cnf.composition_code = comp.code_composition
                    LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = ?
                    LEFT JOIN " . TABLES::SEMESTRES . " s ON s.code_semestre = comp.semestre_code
                    WHERE cnf.niveau_code = ? AND cnf.filiere_code = ?
                ";
                $paramsFb = [$classeRow['libelle_classe'], $matiereCode, $classeRow['niveau_code'], $classeRow['filiere_code']];
                if (!empty($semestreCode)) {
                    $sqlFallback .= " AND comp.semestre_code = ?";
                    $paramsFb[] = $semestreCode;
                }
                if (!empty($typeComposition)) {
                    $sqlFallback .= " AND comp.type_composition = ?";
                    $paramsFb[] = $typeComposition;
                }
                $sqlFallback .= " ORDER BY comp.id_composition DESC ";
                $stmtFb = $db->prepare($sqlFallback);
                $stmtFb->execute($paramsFb);
                $results = $stmtFb->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            return $results;
        } catch (Exception $e) {
            error_log("Get compositions by classe/matiere: " . $e->getMessage());
            return [];
        }
    }

    public function toggleStatus(int $id): bool
    {
        try {
            $item = $this->getById($id);
            if (!$item) return false;
            $current = $item['statut_composition'] ?? 'programme';
            $newStatus = ($current === 'programme' || $current === 'actif') ? 'termine' : 'programme';
            $stmt = $this->getCon()->prepare("UPDATE " . TABLES::COMPOSITIONS . " SET statut_composition = ? WHERE id_composition = ?");
            return $stmt->execute([$newStatus, $id]);
        } catch (Exception $e) {
            error_log("Toggle composition status error: " . $e->getMessage());
            return false;
        }
    }

    public function getEdtActiveTargets(?string $anneeCode = null): array
    {
        try {
            $sql = "
                SELECT DISTINCT cl.niveau_code, cl.filiere_code, n.libelle_niveau, f.libelle_filiere
                FROM " . TABLES::EMPLOIS_TEMPS . " edt
                INNER JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
                INNER JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                INNER JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
            ";
            $params = [];
            if (!empty($anneeCode)) {
                $sql .= " WHERE edt.annee_code = ? ";
                $params[] = $anneeCode;
            }
            $sql .= " ORDER BY n.libelle_niveau ASC, f.libelle_filiere ASC ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get EDT active targets error: " . $e->getMessage());
            return [];
        }
    }

    public function getTeachersWithUsers(): array
    {
        try {
            $sql = "
                SELECT e.code_enseignant, CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS nom_complet
                FROM " . TABLES::ENSEIGNANTS . " e
                JOIN " . TABLES::USERS . " u ON u.code_user = e.code_enseignant
                ORDER BY u.nom_user ASC
            ";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get teachers with users error: " . $e->getMessage());
            return [];
        }
    }

    public function getMatieresForClasse(string $classeCode, ?string $anneeCode = null): array
    {
        $db = $this->getCon();
        $source = 'emploi_temps';

        // 1. Emploi du temps
        $stmtEdt = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ') AS prof_nom
            FROM " . TABLES::EMPLOIS_TEMPS . " edt
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
            LEFT JOIN " . TABLES::ENSEIGNANT_MATIERE . " em ON (em.classe_code = edt.classe_code AND em.matiere_code = edt.matiere_code)
            LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = em.enseignant_code
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = e.code_enseignant
            WHERE edt.classe_code = ? AND (edt.annee_code = ? OR ? = '')
            GROUP BY m.code_matiere, m.libelle_matiere
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtEdt->execute([$classeCode, $anneeCode, $anneeCode]);
        $items = $stmtEdt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fallback enseignant_matiere
        if (empty($items)) {
            $stmtEm = $db->prepare("
                SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                       GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ') AS prof_nom
                FROM " . TABLES::ENSEIGNANT_MATIERE . " em
                INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = em.matiere_code
                LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = em.enseignant_code
                LEFT JOIN " . TABLES::USERS . " u ON u.code_user = e.code_enseignant
                WHERE em.classe_code = ?
                GROUP BY m.code_matiere, m.libelle_matiere
                ORDER BY m.libelle_matiere ASC
            ");
            $stmtEm->execute([$classeCode]);
            $items = $stmtEm->fetchAll(PDO::FETCH_ASSOC);
            $source = 'enseignant_matiere';
        }

        // 3. Fallback all matieres
        if (empty($items)) {
            $stmtAll = $db->query("SELECT code_matiere, libelle_matiere FROM " . TABLES::MATIERES . " ORDER BY libelle_matiere ASC");
            $items = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
            $source = 'all';
        }

        return ['items' => $items, 'source' => $source];
    }

    public function findClasseForTargetNiveauFiliere(string $compositionNiveauFiliereCode): ?string
    {
        $db = $this->getCon();
        $stmtCible = $db->prepare("SELECT niveau_code, filiere_code FROM " . TABLES::COMPOSITION_NIVEAU_FILIERE . " WHERE code_composition_niveau_filiere = ?");
        $stmtCible->execute([$compositionNiveauFiliereCode]);
        $targetRow = $stmtCible->fetch(PDO::FETCH_ASSOC);
        if ($targetRow) {
            $stmtCl = $db->prepare("SELECT code_classe FROM " . TABLES::CLASSES . " WHERE niveau_code = ? AND filiere_code = ? LIMIT 1");
            $stmtCl->execute([$targetRow['niveau_code'], $targetRow['filiere_code']]);
            $cRow = $stmtCl->fetch(PDO::FETCH_ASSOC);
            return $cRow['code_classe'] ?? null;
        }
        return null;
    }

    public function findExistingComposition(string $classeCode, string $matiereCode, string $semestreCode, string $typeComposition, string $anneeCode): ?array
    {
        $stmt = $this->getCon()->prepare("
            SELECT * FROM " . TABLES::COMPOSITIONS . " 
            WHERE classe_code = ? AND matiere_code = ? AND semestre_code = ? AND type_composition = ? AND (annee_code = ? OR ? IS NULL OR ? = '')
            ORDER BY id_composition DESC LIMIT 1
        ");
        $stmt->execute([$classeCode, $matiereCode, $semestreCode, $typeComposition, $anneeCode, $anneeCode, $anneeCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
