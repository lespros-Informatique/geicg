<?php

class ModelConfiguration extends BaseModel
{
    protected string $table = TABLES::CONFIGURATIONS;
    protected string $primaryKey = 'id_configuration';
    protected ?string $createdAtField = 'updated_at';

    /**
     * Récupère la configuration institutionnelle active
     */
    public function getConfig(): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} ASC LIMIT 1";
            $stmt = $this->pdo->getCon()->query($sql);
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
            return $row ?: [];
        } catch (Exception $e) {
            error_log("ModelConfiguration::getConfig error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Enregistre ou met à jour la configuration unique
     */
    public function saveConfig(array $data): bool
    {
        try {
            $existing = $this->getConfig();
            $filtered = $this->filterData($data);

            if (!empty($existing) && !empty($existing[$this->primaryKey])) {
                $id = (int)$existing[$this->primaryKey];
                return $this->update($filtered, $id);
            } else {
                return (bool)$this->create($filtered);
            }
        } catch (Exception $e) {
            error_log("ModelConfiguration::saveConfig error: " . $e->getMessage());
            return false;
        }
    }
}

