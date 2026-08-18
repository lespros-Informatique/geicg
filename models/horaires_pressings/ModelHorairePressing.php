<?php

class ModelHorairePressing extends BaseModel
{
    protected string $table = 'horaires_pressings';
    protected string $primaryKey = 'id_horaire';
    protected ?string $createdAtField = 'created_at';

    public function getByPressing(string $pressingCode): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE pressing_code = ? ORDER BY FIELD(jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelHorairePressing::getByPressing] ' . $e->getMessage());
            return [];
        }
    }
}
