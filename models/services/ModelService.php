<?php

class ModelService extends BaseModel
{
    protected string $table = 'services';
    protected string $primaryKey = 'id_service';
    protected ?string $statusField = 'statut_service';
    protected ?string $createdAtField = 'created_at_service';

    public function getByPressing(string $pressingCode): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT * FROM " . TABLES::SERVICES . " 
            WHERE pressing_code = ? 
            ORDER BY created_at_service DESC
        ");
        $stmt->execute([$pressingCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
