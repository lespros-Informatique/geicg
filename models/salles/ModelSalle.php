<?php

class ModelSalle extends BaseModel
{
    protected string $table = TABLES::SALLES;
    protected string $primaryKey = 'id_salle';
    protected ?string $statusField = 'statut_salle';
    protected ?string $createdAtField = 'created_at';

    /**
     * Récupère les emplois du temps associés à une salle
     */
    public function getPlanningForSalle(string $salleCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT edt.*, cl.libelle_classe, m.libelle_matiere,
                       u.nom_user as nom_prof,
                       u.prenom_user as prenom_prof
                FROM " . TABLES::EMPLOIS_TEMPS . " edt
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
                LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = edt.enseignant_code
                LEFT JOIN " . TABLES::USERS . " u ON u.code_user = edt.enseignant_code
                WHERE edt.salle_code = ? AND (edt.statut_emploi = 'actif' OR edt.statut_emploi IS NULL)
                ORDER BY edt.jour ASC, edt.heure_debut ASC
            ");
            $stmt->execute([$salleCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelSalle::getPlanningForSalle error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère la liste des salles ordonnées pour l'impression PDF
     */
    public function getAllForPdf(): array
    {
        try {
            $stmt = $this->getCon()->query("SELECT s.* FROM " . TABLES::SALLES . " s ORDER BY s.libelle_salle ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log("ModelSalle::getAllForPdf error: " . $e->getMessage());
            return [];
        }
    }
}
