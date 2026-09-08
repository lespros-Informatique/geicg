<?php

class ModelComposition extends BaseModel
{
    protected string $table = 'compositions';
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
            FROM compositions comp
            LEFT JOIN semestres s ON s.code_semestre = comp.semestre_code
            LEFT JOIN composition_niveau_filiere cnf ON cnf.composition_code = comp.code_composition
            LEFT JOIN niveaux n ON n.code_niveau = cnf.niveau_code
            LEFT JOIN filieres f ON f.code_filiere = cnf.filiere_code
            LEFT JOIN classes cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
        ";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "(comp.annee_code = ? OR comp.annee_code IS NULL OR comp.annee_code = '')";
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
            $stmtDel = $db->prepare("DELETE FROM composition_niveau_filiere WHERE composition_code = ?");
            $stmtDel->execute([$compositionCode]);

            if (empty($targetItems)) return true;

            $validator = new Validator();
            $stmtIns = $db->prepare("
                INSERT IGNORE INTO composition_niveau_filiere (code_composition_niveau_filiere, composition_code, niveau_code, filiere_code)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($targetItems as $item) {
                if (empty($item['niveau_code']) || empty($item['filiere_code'])) continue;
                $codeCible = $validator->generateCode('composition_niveau_filiere', 'code_composition_niveau_filiere', 'CNF-', 8);
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
                SELECT cnf.*, n.libelle_niveau, f.libelle_filiere
                FROM composition_niveau_filiere cnf
                LEFT JOIN niveaux n ON n.code_niveau = cnf.niveau_code
                LEFT JOIN filieres f ON f.code_filiere = cnf.filiere_code
                WHERE cnf.composition_code = ?
                ORDER BY n.libelle_niveau ASC, f.libelle_filiere ASC
            ");
            $stmt->execute([$compositionCode]);
            $targets = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $stmtClasses = $this->getCon()->prepare("
                SELECT code_classe, libelle_classe 
                FROM classes 
                WHERE niveau_code = ? AND filiere_code = ?
                ORDER BY libelle_classe ASC
            ");

            foreach ($targets as &$t) {
                $stmtClasses->execute([$t['niveau_code'], $t['filiere_code']]);
                $t['classes'] = $stmtClasses->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
            unset($t);

            return $targets;
        } catch (Exception $e) {
            return [];
        }
    }

    public function saveCompositionMatieres(string $compositionCode, string $compositionNiveauFiliereCode, array $matiereCodes): bool
    {
        try {
            $db = $this->getCon();
            $stmtDel = $db->prepare("DELETE FROM composition_matieres WHERE composition_code = ? AND composition_niveau_filiere_code = ?");
            $stmtDel->execute([$compositionCode, $compositionNiveauFiliereCode]);

            if (empty($matiereCodes)) return true;

            $stmtIns = $db->prepare("
                INSERT INTO composition_matieres (composition_code, composition_niveau_filiere_code, matiere_code)
                VALUES (?, ?, ?)
            ");

            foreach ($matiereCodes as $mCode) {
                if (!empty($mCode)) {
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
                FROM composition_matieres cm
                LEFT JOIN matieres m ON m.code_matiere = cm.matiere_code
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
            $stmtCl = $db->prepare("SELECT code_classe, libelle_classe, niveau_code, filiere_code FROM classes WHERE code_classe = ?");
            $stmtCl->execute([$classeCode]);
            $classeRow = $stmtCl->fetch(PDO::FETCH_ASSOC);

            if (!$classeRow) return [];

            $sql = "
                SELECT DISTINCT comp.*, 
                       ? AS libelle_classe,
                       m.libelle_matiere, 
                       s.libelle_semestre
                FROM compositions comp
                INNER JOIN composition_niveau_filiere cnf ON cnf.composition_code = comp.code_composition
                INNER JOIN composition_matieres cm ON (cm.composition_code = comp.code_composition AND cm.composition_niveau_filiere_code = cnf.code_composition_niveau_filiere)
                LEFT JOIN matieres m ON m.code_matiere = cm.matiere_code
                LEFT JOIN semestres s ON s.code_semestre = comp.semestre_code
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

            $sql .= " ORDER BY comp.date_composition DESC ";

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
                    FROM compositions comp
                    INNER JOIN composition_niveau_filiere cnf ON cnf.composition_code = comp.code_composition
                    LEFT JOIN matieres m ON m.code_matiere = ?
                    LEFT JOIN semestres s ON s.code_semestre = comp.semestre_code
                    WHERE cnf.niveau_code = ? AND cnf.filiere_code = ?
                ";
                $paramsFb = [$classeRow['libelle_classe'], $matiereCode, $classeRow['niveau_code'], $classeRow['filiere_code']];
                if (!empty($semestreCode)) {
                    $sqlFallback .= " AND comp.semestre_code = ?";
                    $paramsFb[] = $semestreCode;
                }
                $sqlFallback .= " ORDER BY comp.date_composition DESC ";
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
            $stmt = $this->getCon()->prepare("UPDATE compositions SET statut_composition = ? WHERE id_composition = ?");
            return $stmt->execute([$newStatus, $id]);
        } catch (Exception $e) {
            error_log("Toggle composition status error: " . $e->getMessage());
            return false;
        }
    }
}
