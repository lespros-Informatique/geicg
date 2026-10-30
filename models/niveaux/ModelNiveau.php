<?php

class ModelNiveau extends BaseModel
{
    protected string $table = TABLES::NIVEAUX;
    protected string $primaryKey = 'id_niveau';
    protected ?string $statusField = 'statut_niveau';
    protected ?string $createdAtField = 'created_at_niveau';

    /**
     * Récupère uniquement les niveaux d'études ayant un statut actif
     */
    public function getActifs(): array
    {
        $sql = "SELECT * FROM " . TABLES::NIVEAUX . " WHERE statut_niveau = 'actif' OR statut_niveau IS NULL ORDER BY libelle_niveau ASC";
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
                FROM " . TABLES::NIVEAUX . " n
                JOIN " . TABLES::FILIERE_CYCLES . " fc ON fc.niveau_code = n.code_niveau
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
                    FROM " . TABLES::NIVEAUX . " n
                    JOIN " . TABLES::CLASSES . " cl ON cl.niveau_code = n.code_niveau
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

    public function getClassesForNiveau(string $niveauCode): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT cl.*, f.libelle_filiere,
                   (SELECT COUNT(*) FROM " . TABLES::INSCRIPTIONS . " ins WHERE ins.classe_code = cl.code_classe AND ins.statut_inscription = 'actif') as nb_eleves
            FROM " . TABLES::CLASSES . " cl
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
            WHERE cl.niveau_code = ?
            ORDER BY cl.libelle_classe ASC
        ");
        $stmt->execute([$niveauCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStatsForNiveau(string $niveauCode, int $defaultTotalClasses = 0): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT 
                (SELECT COUNT(*) FROM " . TABLES::CLASSES . " WHERE niveau_code = ? AND statut_classe = 'actif') as total_classes,
                (SELECT COUNT(*) FROM " . TABLES::INSCRIPTIONS . " ins 
                 JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ins.classe_code 
                 WHERE cl.niveau_code = ? AND ins.statut_inscription = 'actif') as total_etudiants
        ");
        $stmt->execute([$niveauCode, $niveauCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_classes' => $defaultTotalClasses, 'total_etudiants' => 0];
    }
}
