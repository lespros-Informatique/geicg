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
            $conditions[] = "em.annee_code = ?";
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

    /**
     * Récupère les détails complets d'une affectation
     */
    public function getDetailsById(int $id): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT em.*, 
                       m.libelle_matiere,
                       cl.libelle_classe,
                       f.libelle_filiere,
                       n.libelle_niveau,
                       u.nom_user AS nom_prof,
                       u.prenom_user AS prenom_prof,
                       e.grade_enseignant,
                       u.email_user AS email_enseignant,
                       u.telephone_user AS telephone_enseignant
                FROM " . TABLES::ENSEIGNANT_MATIERE . " em
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = em.matiere_code
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = em.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = em.enseignant_code
                LEFT JOIN " . TABLES::USERS . " u ON u.code_user = em.enseignant_code
                WHERE em.id_enseignant_matiere = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelEnseignantMatiere getDetailsById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Compte les notes saisies pour ce cours dans cette classe
     */
    public function getNbNotesForCourse(string $classeCode, string $matiereCode): int
    {
        try {
            $stmtNotes = $this->getCon()->prepare("
                SELECT COUNT(*) FROM " . TABLES::NOTES . " n
                JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
                JOIN " . TABLES::INSCRIPTIONS . " ins ON ins.code_inscription = n.inscription_code
                WHERE ins.classe_code = ? AND comp.matiere_code = ? AND (n.statut_note = 'actif' OR n.statut_note IS NULL)
            ");
            $stmtNotes->execute([$classeCode, $matiereCode]);
            return (int)$stmtNotes->fetchColumn();
        } catch (Exception $e) {
            error_log("ModelEnseignantMatiere getNbNotesForCourse error: " . $e->getMessage());
            return 0;
        }
    }
}
