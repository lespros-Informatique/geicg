<?php

class ModelLivreur extends BaseModel
{
    protected string $table = 'livreurs';
    protected string $primaryKey = 'id_livreur';
    protected ?string $statusField = 'statut_livreur';
    protected ?string $createdAtField = 'created_at_livreur';

    public function getByPressing(string $pressingCode): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE pressing_code = ? OR pressing_code IS NULL OR pressing_code = '' ORDER BY {$this->primaryKey} DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelLivreur::getByPressing] ' . $e->getMessage());
            return [];
        }
    }
}
