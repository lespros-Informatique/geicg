<?php

class ModelDepense extends BaseModel
{
    protected string $table = 'depenses';
    protected string $primaryKey = 'id_depense';
    protected ?string $statusField = 'statut_depense';
    protected ?string $createdAtField = 'created_at_depense';

    public function getAll(?string $anneeCode = null): array
    {
        $where = "";
        $params = [];
        if (!empty($anneeCode)) {
            $where = "WHERE (d.annee_code = ? OR d.annee_code IS NULL OR d.annee_code = '')";
            $params = [$anneeCode];
        }

        $sql = "
            SELECT d.*, t.libelle_type_depense, u.nom_user, u.prenom_user
            FROM depenses d
            LEFT JOIN type_depenses t ON t.code_type_depense = d.type_depense_code
            LEFT JOIN users u ON u.code_user = d.user_code
            {$where}
            ORDER BY d.id_depense DESC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
