<?php

class ModelPermission extends BaseModel
{
    protected string $table = 'permissions';
    protected string $primaryKey = 'id_permission';
    protected ?string $statusField = 'statut_permission';

    public function getGrouped(): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE statut_permission = 'actif' ORDER BY code_permission ASC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $groups = [];
            foreach ($items as $item) {
                $code = $item['code_permission'] ?? '';
                $prefix = explode('_', $code)[0] ?? 'AUTRE';
                $groups[$prefix][] = $item;
            }

            return $groups;
        } catch (Exception $e) {
            error_log('[ModelPermission::getGrouped] ' . $e->getMessage());
            return [];
        }
    }
}
