<?php

class ModelFiliere extends BaseModel
{
    protected string $table = TABLES::FILIERES;
    protected string $primaryKey = 'id_filiere';
    protected ?string $statusField = 'statut_filiere';
    protected ?string $createdAtField = 'created_at_filiere';

    /**
     * Récupère uniquement les filières qui sont associées à un cycle actif
     */
    public function getFilieresAssocieesAuxCycles(): array
    {
        $sql = "SELECT DISTINCT f.* 
                FROM " . TABLES::FILIERES . " f
                INNER JOIN " . TABLES::FILIERE_CYCLES . " fc ON fc.filiere_code = f.code_filiere
                INNER JOIN " . TABLES::CYCLES . " cy ON cy.code_cycle = fc.cycle_code
                WHERE (f.statut_filiere = '" . STATUTS::ACTIF . "' OR f.statut_filiere IS NULL)
                  AND (fc.statut_filiere_cycle = '" . STATUTS::ACTIF . "' OR fc.statut_filiere_cycle IS NULL)
                  AND (cy.statut_cycle = '" . STATUTS::ACTIF . "' OR cy.statut_cycle IS NULL)
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

    public function getCyclesForFiliere(string $filiereCode): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT c.* FROM " . TABLES::CYCLES . " c
            INNER JOIN " . TABLES::FILIERE_CYCLES . " fc ON fc.cycle_code = c.code_cycle
            WHERE fc.filiere_code = ?
        ");
        $stmt->execute([$filiereCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getClassesForFiliere(string $filiereCode): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT cl.*, n.libelle_niveau,
                   (SELECT COUNT(*) FROM " . TABLES::INSCRIPTIONS . " ins WHERE ins.classe_code = cl.code_classe AND ins.statut_inscription = '" . STATUTS::ACTIF . "') as nb_eleves
            FROM " . TABLES::CLASSES . " cl
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
            WHERE cl.filiere_code = ?
            ORDER BY cl.libelle_classe ASC
        ");
        $stmt->execute([$filiereCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStatsForFiliere(string $filiereCode, int $defaultTotalClasses = 0): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT 
                (SELECT COUNT(*) FROM " . TABLES::CLASSES . " WHERE filiere_code = ? AND statut_classe = '" . STATUTS::ACTIF . "') as total_classes,
                (SELECT COUNT(*) FROM " . TABLES::INSCRIPTIONS . " ins 
                 JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ins.classe_code 
                 WHERE cl.filiere_code = ? AND ins.statut_inscription = '" . STATUTS::ACTIF . "') as total_etudiants
        ");
        $stmt->execute([$filiereCode, $filiereCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_classes' => $defaultTotalClasses, 'total_etudiants' => 0];
    }

    public function getCatalogueForPdf(): array
    {
        $sql = "SELECT f.*,
                       GROUP_CONCAT(DISTINCT c.code_cycle ORDER BY c.code_cycle SEPARATOR ', ') as cycles_codes,
                       GROUP_CONCAT(DISTINCT c.libelle_cycle ORDER BY c.libelle_cycle SEPARATOR ' | ') as cycles_libelles
                FROM " . TABLES::FILIERES . " f
                LEFT JOIN " . TABLES::FILIERE_CYCLES . " fc ON fc.filiere_code = f.code_filiere AND (fc.statut_filiere_cycle = '" . STATUTS::ACTIF . "' OR fc.statut_filiere_cycle IS NULL)
                LEFT JOIN " . TABLES::CYCLES . " c ON c.code_cycle = fc.cycle_code AND (c.statut_cycle = '" . STATUTS::ACTIF . "' OR c.statut_cycle IS NULL)
                WHERE f.statut_filiere = '" . STATUTS::ACTIF . "' OR f.statut_filiere IS NULL
                GROUP BY f.id_filiere
                ORDER BY f.type_filiere ASC, f.libelle_filiere ASC";
        return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
