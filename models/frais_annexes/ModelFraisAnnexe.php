<?php

class ModelFraisAnnexe extends BaseModel
{
    protected string $table = 'frais_annexes';
    protected string $primaryKey = 'id_frais_annexe';
    protected ?string $statusField = 'statut_frais_annexe';
    protected ?string $createdAtField = 'created_at_frais_annexe';

    public function getAll(?string $anneeCode = null, ?string $typeFiliere = null, ?string $niveauCode = null): array
    {
        $sql = "
            SELECT fa.*, a.libelle_annee, n.libelle_niveau
            FROM frais_annexes fa
            LEFT JOIN annees a ON fa.annee_code = a.code_annee
            LEFT JOIN niveaux n ON fa.niveau_code = n.code_niveau
        ";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "fa.annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($typeFiliere)) {
            $conditions[] = "(fa.type_filiere = ? OR fa.type_filiere = 'TOUT')";
            $params[] = $typeFiliere;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "(fa.niveau_code = ? OR fa.niveau_code IS NULL OR fa.niveau_code = '')";
            $params[] = $niveauCode;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY fa.id_frais_annexe DESC ";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById(int $id): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT fa.*, a.libelle_annee, n.libelle_niveau
            FROM frais_annexes fa
            LEFT JOIN annees a ON fa.annee_code = a.code_annee
            LEFT JOIN niveaux n ON fa.niveau_code = n.code_niveau
            WHERE fa.id_frais_annexe = ?
            LIMIT 1
        ");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    public function getMontantByTypeFiliere(string $typeFiliere, string $anneeCode, ?string $niveauCode = null): float
    {
        $sql = "
            SELECT montant_frais_annexe 
            FROM frais_annexes 
            WHERE statut_frais_annexe = 'actif'
              AND (type_filiere = ? OR type_filiere = 'TOUT')
              AND annee_code = ?
        ";
        $params = [$typeFiliere, $anneeCode];

        if (!empty($niveauCode)) {
            $sql .= " AND (niveau_code = ? OR niveau_code IS NULL OR niveau_code = '') ";
            $params[] = $niveauCode;
            $sql .= " ORDER BY 
                (CASE WHEN niveau_code = ? THEN 1 ELSE 2 END), 
                (CASE WHEN type_filiere = ? THEN 1 ELSE 2 END), 
                id_frais_annexe DESC ";
            $params[] = $niveauCode;
            $params[] = $typeFiliere;
        } else {
            $sql .= " ORDER BY (CASE WHEN type_filiere = ? THEN 1 ELSE 2 END), id_frais_annexe DESC ";
            $params[] = $typeFiliere;
        }

        $sql .= " LIMIT 1 ";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['montant_frais_annexe'] : 0.0;
    }

    public function checkDuplicate(string $anneeCode, string $typeFiliere, ?string $niveauCode, ?int $excludeId = null): ?array
    {
        // Contrôle d'unicité sur la combinaison Cible (type_filiere x niveau_code x annee_code)
        $sql = "SELECT * FROM frais_annexes WHERE annee_code = ? AND type_filiere = ?";
        $params = [$anneeCode, $typeFiliere];
        if (empty($niveauCode) || $niveauCode === 'TOUT') {
            $sql .= " AND (niveau_code IS NULL OR niveau_code = '' OR niveau_code = 'TOUT')";
        } else {
            $sql .= " AND niveau_code = ?";
            $params[] = $niveauCode;
        }
        if ($excludeId) {
            $sql .= " AND id_frais_annexe != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->getCon()->prepare($sql . " LIMIT 1");
        $stmt->execute($params);
        $dup = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dup ?: null;
    }
}
