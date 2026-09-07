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
                   GROUP_CONCAT(DISTINCT cl.libelle_classe ORDER BY cl.libelle_classe SEPARATOR ', ') AS classes_cible_names,
                   GROUP_CONCAT(DISTINCT n.libelle_niveau ORDER BY n.libelle_niveau SEPARATOR ', ') AS niveaux_cible_names
            FROM compositions comp
            LEFT JOIN semestres s ON s.code_semestre = comp.semestre_code
            LEFT JOIN composition_classes cc ON cc.composition_code = comp.code_composition
            LEFT JOIN classes cl ON cl.code_classe = cc.classe_code
            LEFT JOIN niveaux n ON n.code_niveau = cc.niveau_code
        ";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "(comp.annee_code = ? OR comp.annee_code IS NULL OR comp.annee_code = '')";
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "cc.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "cc.classe_code = ?";
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
            $stmtDel = $db->prepare("DELETE FROM composition_classes WHERE composition_code = ?");
            $stmtDel->execute([$compositionCode]);

            if (empty($targetItems)) return true;

            $stmtIns = $db->prepare("
                INSERT INTO composition_classes (composition_code, niveau_code, filiere_code, classe_code)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($targetItems as $item) {
                $stmtIns->execute([
                    $compositionCode,
                    $item['niveau_code'] ?? null,
                    $item['filiere_code'] ?? null,
                    $item['classe_code']
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
                SELECT cc.*, cl.libelle_classe, n.libelle_niveau, f.libelle_filiere
                FROM composition_classes cc
                LEFT JOIN classes cl ON cl.code_classe = cc.classe_code
                LEFT JOIN niveaux n ON n.code_niveau = cc.niveau_code
                LEFT JOIN filieres f ON f.code_filiere = cc.filiere_code
                WHERE cc.composition_code = ?
            ");
            $stmt->execute([$compositionCode]);
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

        $sql = "
            SELECT comp.*, 
                   cl.libelle_classe, 
                   m.libelle_matiere, 
                   s.libelle_semestre
            FROM compositions comp
            LEFT JOIN classes cl ON cl.code_classe = comp.classe_code
            LEFT JOIN matieres m ON m.code_matiere = comp.matiere_code
            LEFT JOIN semestres s ON s.code_semestre = comp.semestre_code
            WHERE comp.classe_code = ? AND comp.matiere_code = ?
        ";
        $params = [$classeCode, $matiereCode];

        if (!empty($semestreCode)) {
            $sql .= " AND comp.semestre_code = ?";
            $params[] = $semestreCode;
        }
        if (!empty($typeComposition)) {
            $sql .= " AND comp.type_composition = ?";
            $params[] = $typeComposition;
        }

        $sql .= " ORDER BY comp.date_composition DESC ";

        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
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

