<?php

class ModelEmploi extends BaseModel
{
    protected string $table = 'emplois_temps';
    protected string $primaryKey = 'id_emploi';
    protected ?string $statusField = 'statut_emploi';
    protected ?string $createdAtField = 'created_at_emploi';

    public function getAll(?string $anneeCode = null): array
    {
        $sql = "
            SELECT edt.*, 
                   cl.libelle_classe, 
                   m.libelle_matiere, 
                   s.libelle_salle, 
                   CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS nom_prof
            FROM emplois_temps edt
            LEFT JOIN classes cl ON cl.code_classe = edt.classe_code
            LEFT JOIN matieres m ON m.code_matiere = edt.matiere_code
            LEFT JOIN salles s ON s.code_salle = edt.salle_code
            LEFT JOIN enseignants e ON e.code_enseignant = edt.enseignant_code
            LEFT JOIN users u ON u.code_user = edt.enseignant_code
        ";
        $params = [];
        if (!empty($anneeCode)) {
            $sql .= " WHERE (edt.annee_code = ? OR edt.annee_code IS NULL OR edt.annee_code = '') ";
            $params[] = $anneeCode;
        }
        $sql .= " ORDER BY edt.jour ASC, edt.heure_debut ASC ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all emplois_temps: " . $e->getMessage());
            return [];
        }
    }
}
