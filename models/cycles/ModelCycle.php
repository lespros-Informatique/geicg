<?php

class ModelCycle extends BaseModel
{
    protected string $table = 'cycles';
    protected string $primaryKey = 'id_cycle';
    protected ?string $statusField = 'statut_cycle';
    protected ?string $createdAtField = 'created_at_cycle';

    public function getFilieresForCycle(string $cycleCode): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT f.*,
                   (SELECT COUNT(*) FROM " . TABLES::CLASSES . " cl WHERE cl.filiere_code = f.code_filiere AND cl.cycle_code = ? AND cl.statut_classe = 'actif') as nb_classes
            FROM " . TABLES::FILIERES . " f
            INNER JOIN " . TABLES::FILIERE_CYCLES . " fc ON fc.filiere_code = f.code_filiere
            WHERE fc.cycle_code = ?
            ORDER BY f.libelle_filiere ASC
        ");
        $stmt->execute([$cycleCode, $cycleCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStatsForCycle(string $cycleCode, int $defaultTotalFilieres = 0): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT 
                COUNT(DISTINCT fc.filiere_code) as total_filieres,
                (SELECT COUNT(*) FROM " . TABLES::CLASSES . " cl 
                 WHERE cl.cycle_code = ? AND cl.statut_classe = 'actif') as total_classes,
                (SELECT COUNT(*) FROM " . TABLES::INSCRIPTIONS . " ins 
                 JOIN " . TABLES::CLASSES . " cl2 ON cl2.code_classe = ins.classe_code
                 WHERE cl2.cycle_code = ? AND ins.statut_inscription = 'actif') as total_etudiants
            FROM " . TABLES::FILIERE_CYCLES . " fc
            WHERE fc.cycle_code = ?
        ");
        $stmt->execute([$cycleCode, $cycleCode, $cycleCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_filieres' => $defaultTotalFilieres, 'total_classes' => 0, 'total_etudiants' => 0
        ];
    }
}
