<?php

class ModelFiliere extends BaseModel
{
    protected string $table = 'filieres';
    protected string $primaryKey = 'id_filiere';
    protected ?string $statusField = 'statut_filiere';
    protected ?string $createdAtField = 'created_at_filiere';

    /**
     * Récupère uniquement les filières qui sont associées à un cycle actif
     */
    public function getFilieresAssocieesAuxCycles(): array
    {
        $sql = "SELECT DISTINCT f.* 
                FROM filieres f
                INNER JOIN filiere_cycles fc ON fc.filiere_code = f.code_filiere
                INNER JOIN cycles cy ON cy.code_cycle = fc.cycle_code
                WHERE (f.statut_filiere = 'actif' OR f.statut_filiere IS NULL)
                  AND (fc.statut_filiere_cycle = 'actif' OR fc.statut_filiere_cycle IS NULL)
                  AND (cy.statut_cycle = 'actif' OR cy.statut_cycle IS NULL)
                ORDER BY f.libelle_filiere ASC";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelFiliere::getFilieresAssocieesAuxCycles error: " . $e->getMessage());
            return [];
        }
    }
}
