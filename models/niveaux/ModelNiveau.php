<?php

class ModelNiveau extends BaseModel
{
    protected string $table = 'niveaux';
    protected string $primaryKey = 'id_niveau';
    protected ?string $statusField = 'statut_niveau';
    protected ?string $createdAtField = 'created_at_niveau';

    /**
     * Récupère uniquement les niveaux d'études ayant un statut actif
     */
    public function getActifs(): array
    {
        $sql = "SELECT * FROM niveaux WHERE statut_niveau = 'actif' OR statut_niveau IS NULL ORDER BY libelle_niveau ASC";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelNiveau::getActifs error: " . $e->getMessage());
            return [];
        }
    }
}
