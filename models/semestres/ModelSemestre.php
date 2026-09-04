<?php

class ModelSemestre extends BaseModel
{
    protected string $table = 'semestres';
    protected string $primaryKey = 'id_semestre';
    protected ?string $statusField = 'statut_semestre';
    protected ?string $createdAtField = 'created_at_semestre';

    public function getAll(?string $anneeCode = null): array
    {
        $sql = "SELECT s.*, a.libelle_annee 
                FROM semestres s
                LEFT JOIN annees a ON a.code_annee = s.annee_code";
        $params = [];
        if (!empty($anneeCode)) {
            $sql .= " WHERE (s.annee_code = ? OR s.annee_code IS NULL OR s.annee_code = '') ";
            $params[] = $anneeCode;
        }
        $sql .= " ORDER BY s.id_semestre DESC";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all semestres: " . $e->getMessage());
            return [];
        }
    }
}
