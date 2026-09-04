<?php

class ModelEnseignantMatiere extends BaseModel
{
    protected string $table = 'enseignant_matiere';
    protected string $primaryKey = 'id_enseignant_matiere';
    
    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null): array
    {
        $sql = "SELECT em.*, 
                       m.libelle_matiere,
                       cl.libelle_classe,
                       n.libelle_niveau,
                       CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS enseignant_nom,
                       e.code_enseignant
                FROM enseignant_matiere em
                LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
                LEFT JOIN classes cl ON cl.code_classe = em.classe_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN enseignants e ON e.code_enseignant = em.enseignant_code
                LEFT JOIN users u ON u.code_user = em.enseignant_code";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "(em.annee_code = ? OR em.annee_code IS NULL OR em.annee_code = '')";
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "cl.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "em.classe_code = ?";
            $params[] = $classeCode;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY em.id_enseignant_matiere DESC";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all enseignant_matiere: " . $e->getMessage());
            return [];
        }
    }
}
