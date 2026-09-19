<?php

class ModelFraisAnnexe extends BaseModel
{
    protected string $table = 'frais_annexes';
    protected string $primaryKey = 'id_frais_annexe';
    protected ?string $statusField = 'statut_frais_annexe';
    protected ?string $createdAtField = 'created_at_frais_annexe';

    public function getAll(?string $anneeCode = null, ?string $typeFiliere = null): array
    {
        $sql = "
            SELECT fa.*, a.libelle_annee
            FROM frais_annexes fa
            LEFT JOIN annees a ON fa.annee_code = a.code_annee
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
            SELECT fa.*, a.libelle_annee
            FROM frais_annexes fa
            LEFT JOIN annees a ON fa.annee_code = a.code_annee
            WHERE fa.id_frais_annexe = ?
            LIMIT 1
        ");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    public function getMontantByTypeFiliere(string $typeFiliere, string $anneeCode): float
    {
        $stmt = $this->getCon()->prepare("
            SELECT montant_frais_annexe 
            FROM frais_annexes 
            WHERE statut_frais_annexe = 'actif'
              AND (type_filiere = ? OR type_filiere = 'TOUT')
              AND annee_code = ?
            ORDER BY (CASE WHEN type_filiere = ? THEN 1 ELSE 2 END), id_frais_annexe DESC
            LIMIT 1
        ");
        $stmt->execute([$typeFiliere, $anneeCode, $typeFiliere]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['montant_frais_annexe'] : 0.0;
    }
}
