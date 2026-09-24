<?php

class ModelTranche extends BaseModel
{
    protected string $table = 'tranches_scolarite';
    protected string $primaryKey = 'id_tranche';
    protected ?string $statusField = 'statut_tranche';
    protected ?string $createdAtField = 'created_at_tranche';

    public function getAll(?string $anneeCode = null, ?string $filiereCode = null, ?string $niveauCode = null, ?string $statut = null): array
    {
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "(t.annee_code = ? OR s.annee_code = ?)";
            $params[] = $anneeCode;
            $params[] = $anneeCode;
        }
        if (!empty($filiereCode)) {
            $conditions[] = "(s.filiere_code = ? OR t.filiere_code = ?)";
            $params[] = $filiereCode;
            $params[] = $filiereCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "(s.niveau_code = ? OR t.niveau_code = ?)";
            $params[] = $niveauCode;
            $params[] = $niveauCode;
        }
        if (!empty($statut)) {
            $conditions[] = "t.statut_tranche = ?";
            $params[] = $statut;
        }

        $where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "
            SELECT t.*, 
                   s.montant_scolarite, 
                   s.affectation_etat,
                   f.libelle_filiere, 
                   n.libelle_niveau, 
                   a.libelle_annee
            FROM tranches_scolarite t
            LEFT JOIN scolarites s ON t.scolarite_code = s.code_scolarite
            LEFT JOIN filieres f ON (s.filiere_code = f.code_filiere OR t.filiere_code = f.code_filiere)
            LEFT JOIN niveaux n ON (s.niveau_code = n.code_niveau OR t.niveau_code = n.code_niveau)
            LEFT JOIN annees a ON (s.annee_code = a.code_annee OR t.annee_code = a.code_annee)
            {$where}
            ORDER BY f.libelle_filiere ASC, n.libelle_niveau ASC, t.date_limite ASC, t.id_tranche ASC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById($id): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT t.*, 
                   s.montant_scolarite, 
                   s.affectation_etat,
                   f.libelle_filiere, 
                   n.libelle_niveau, 
                   a.libelle_annee
            FROM tranches_scolarite t
            LEFT JOIN scolarites s ON t.scolarite_code = s.code_scolarite
            LEFT JOIN filieres f ON (s.filiere_code = f.code_filiere OR t.filiere_code = f.code_filiere)
            LEFT JOIN niveaux n ON (s.niveau_code = n.code_niveau OR t.niveau_code = n.code_niveau)
            LEFT JOIN annees a ON (s.annee_code = a.code_annee OR t.annee_code = a.code_annee)
            WHERE t.id_tranche = ? OR t.code_tranche = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }
}
