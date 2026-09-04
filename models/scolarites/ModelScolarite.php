<?php

class ModelScolarite extends BaseModel
{
    protected string $table = 'scolarites';
    protected string $primaryKey = 'id_scolarite';
    protected ?string $statusField = 'statut_scolarite';
    protected ?string $createdAtField = 'created_at_scolarite';

    public function getAll(?string $anneeCode = null): array
    {
        $where = "";
        $params = [];
        if (!empty($anneeCode)) {
            $where = "WHERE (s.annee_code = ? OR s.annee_code IS NULL OR s.annee_code = '')";
            $params = [$anneeCode];
        }

        $sql = "
            SELECT s.*, f.libelle_filiere, n.libelle_niveau, a.libelle_annee
            FROM scolarites s
            LEFT JOIN filieres f ON s.filiere_code = f.code_filiere
            LEFT JOIN niveaux n ON s.niveau_code = n.code_niveau
            LEFT JOIN annees a ON s.annee_code = a.code_annee
            {$where}
            ORDER BY s.id_scolarite DESC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById($id): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT s.*, f.libelle_filiere, n.libelle_niveau, a.libelle_annee
            FROM scolarites s
            LEFT JOIN filieres f ON s.filiere_code = f.code_filiere
            LEFT JOIN niveaux n ON s.niveau_code = n.code_niveau
            LEFT JOIN annees a ON s.annee_code = a.code_annee
            WHERE s.id_scolarite = ?
            LIMIT 1
        ");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }
}
