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

    /**
     * Récupère les niveaux d'études associés à un cycle académique
     */
    public function getByCycle(?string $cycleCode): array
    {
        if (empty($cycleCode)) {
            return [];
        }

        try {
            // 1. Recherche via les liaisons du parcours académique (filiere_cycles)
            $sql = "
                SELECT DISTINCT n.code_niveau, n.libelle_niveau, n.slug_niveau
                FROM niveaux n
                JOIN filiere_cycles fc ON fc.niveau_code = n.code_niveau
                WHERE fc.cycle_code = ?
                  AND (n.statut_niveau = 'actif' OR n.statut_niveau IS NULL)
                ORDER BY n.libelle_niveau ASC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$cycleCode]);
            $niveaux = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // 2. Si aucune liaison filiere_cycles, vérifier si des classes sont associées
            if (empty($niveaux)) {
                $sqlClasses = "
                    SELECT DISTINCT n.code_niveau, n.libelle_niveau, n.slug_niveau
                    FROM niveaux n
                    JOIN classes cl ON cl.niveau_code = n.code_niveau
                    WHERE cl.cycle_code = ?
                      AND (n.statut_niveau = 'actif' OR n.statut_niveau IS NULL)
                    ORDER BY n.libelle_niveau ASC
                ";
                $stmtClasses = $this->getCon()->prepare($sqlClasses);
                $stmtClasses->execute([$cycleCode]);
                $niveaux = $stmtClasses->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            // 3. Repli si aucune association spécifique : retourner tous les niveaux actifs
            if (empty($niveaux)) {
                $niveaux = $this->getActifs();
            }

            return $niveaux;
        } catch (Exception $e) {
            error_log("ModelNiveau::getByCycle error: " . $e->getMessage());
            return $this->getActifs();
        }
    }
}
