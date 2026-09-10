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
            SELECT d.*, 
                   t.libelle_type_depense, 
                   CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) as auteur_nom_complet
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

    public function getStats(?string $anneeCode = null): array
    {
        $where = "";
        $params = [];
        if (!empty($anneeCode)) {
            $where = "WHERE (annee_code = ? OR annee_code IS NULL OR annee_code = '')";
            $params = [$anneeCode];
        }

        $db = $this->getCon();
        $stmtTot = $db->prepare("SELECT SUM(montant_depense) FROM depenses " . $where);
        $stmtTot->execute($params);
        $totalMontant = (float)($stmtTot->fetchColumn() ?: 0);

        $stmtCount = $db->prepare("SELECT COUNT(*) FROM depenses " . $where);
        $stmtCount->execute($params);
        $totalCount = (int)($stmtCount->fetchColumn() ?: 0);

        $moyenne = $totalCount > 0 ? round($totalMontant / $totalCount) : 0;

        $totalTypes = (int)$db->query("SELECT COUNT(*) FROM type_depenses")->fetchColumn();

        return [
            'total_montant' => $totalMontant,
            'total_count' => $totalCount,
            'moyenne' => $moyenne,
            'total_types' => $totalTypes
        ];
    }
}
