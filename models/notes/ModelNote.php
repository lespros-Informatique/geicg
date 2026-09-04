<?php

class ModelNote extends BaseModel
{
    protected string $table = 'notes';
    protected string $primaryKey = 'id_note';
    protected ?string $statusField = 'statut_note';
    protected ?string $createdAtField = 'created_at_note';

    public function getAll(?string $anneeCode = null): array
    {
        $sql = "
            SELECT n.*, 
                   CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) as nom_etudiant,
                   e.matricule_etudiant,
                   m.libelle_matiere,
                   cl.libelle_classe
            FROM notes n
            LEFT JOIN inscriptions ins ON ins.code_inscription = n.inscription_code
            LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
            LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
            LEFT JOIN matieres m ON m.code_matiere = n.matiere_code
        ";
        $params = [];
        if (!empty($anneeCode)) {
            $sql .= " WHERE (n.annee_code = ? OR n.annee_code IS NULL OR n.annee_code = '') ";
            $params[] = $anneeCode;
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
