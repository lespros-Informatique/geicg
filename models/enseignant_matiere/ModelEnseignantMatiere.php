<?php

class ModelEnseignantMatiere extends BaseModel
{
    protected string $table = TABLES::ENSEIGNANT_MATIERE;
    protected string $primaryKey = 'id_enseignant_matiere';
    
    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null): array
    {
        $sql = "SELECT * FROM " . VIEWS::DASH_PEDAGOGIE_AFFECTATIONS . "";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "classe_code = ?";
            $params[] = $classeCode;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY id_enseignant_matiere DESC";
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
                SELECT * FROM " . VIEWS::DASH_PEDAGOGIE_AFFECTATIONS . "
                WHERE id_enseignant_matiere = ?
                LIMIT 1
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
