<?php

class ModelAbsence extends BaseModel
{
    protected string $table = 'absences';
    protected string $primaryKey = 'id_absence';
    
    protected ?string $createdAtField = 'created_at_absence';

    public function getAll(?string $anneeCode = null): array
    {
        $sql = "
            SELECT abs.*, 
                   CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) as nom_etudiant,
                   e.matricule_etudiant,
                   cl.libelle_classe,
                   m.libelle_matiere
            FROM absences abs
            LEFT JOIN etudiants e ON e.code_etudiant = abs.etudiant_code
            LEFT JOIN classes cl ON cl.code_classe = abs.classe_code
            LEFT JOIN matieres m ON m.code_matiere = abs.matiere_code
        ";
        $params = [];
        if (!empty($anneeCode)) {
            $sql .= " WHERE (abs.annee_code = ? OR abs.annee_code IS NULL OR abs.annee_code = '') ";
            $params[] = $anneeCode;
        }
        $sql .= " ORDER BY abs.date_absence DESC, abs.id_absence DESC ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all absences: " . $e->getMessage());
            return [];
        }
    }
}
