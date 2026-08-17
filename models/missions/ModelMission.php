<?php

class ModelMission extends BaseModel
{
    protected string $table = 'missions';
    protected string $primaryKey = 'id_mission';
    protected ?string $statusField = 'statut_mission';
    protected ?string $createdAtField = 'created_at_mission';

    public function getByLivreur(string $livreurCode): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE livreur_code = ? ORDER BY created_at_mission DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$livreurCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelMission::getByLivreur] ' . $e->getMessage());
            return [];
        }
    }
}
