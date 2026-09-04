<?php

class ModelNote extends BaseModel
{
    protected string $table = 'notes';
    protected string $primaryKey = 'id_note';
    protected ?string $statusField = 'statut_note';
    protected ?string $createdAtField = 'created_at_note';

    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null): array
    {
        $sql = "
            SELECT n.*, 
                   CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) as nom_etudiant,
                   e.matricule_etudiant,
                   m.libelle_matiere,
                   cl.libelle_classe,
                   niv.libelle_niveau
            FROM notes n
            LEFT JOIN inscriptions ins ON ins.code_inscription = n.inscription_code
            LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
            LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
            LEFT JOIN niveaux niv ON niv.code_niveau = cl.niveau_code
            LEFT JOIN matieres m ON m.code_matiere = n.matiere_code
        ";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "(n.annee_code = ? OR n.annee_code IS NULL OR n.annee_code = '')";
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "cl.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "ins.classe_code = ?";
            $params[] = $classeCode;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY n.id_note DESC ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all notes: " . $e->getMessage());
            return [];
        }
    }
}
