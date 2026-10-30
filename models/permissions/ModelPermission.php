<?php

class ModelPermission extends BaseModel
{
    protected string $table = TABLES::PERMISSIONS;
    protected string $primaryKey = 'id_permission';
    protected ?string $statusField = 'statut_permission';

    public function getGrouped(): array
    {
        try {
            $sql = "SELECT * FROM " . TABLES::PERMISSIONS . " WHERE statut_permission = 'actif' ORDER BY module_permission ASC, id_permission ASC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $groups = [];
            foreach ($items as $item) {
                $group = !empty($item['module_permission']) ? $item['module_permission'] : 'GÉNÉRAL';
                $groups[$group][] = $item;
            }

            return $groups;
        } catch (Exception $e) {
            error_log('[ModelPermission::getGrouped] ' . $e->getMessage());
            return [];
        }
    }

    public function getDistinctModules(): array
    {
        try {
            $sql = "SELECT DISTINCT module_permission FROM " . TABLES::PERMISSIONS . " WHERE module_permission IS NOT NULL AND module_permission != '' ORDER BY module_permission ASC";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (Exception $e) {
            error_log('[ModelPermission::getDistinctModules] ' . $e->getMessage());
            return [];
        }
    }
}
