<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../core/BaseModel.php';
require_once __DIR__ . '/../../config/const.php';

class ModelAuditLog extends BaseModel
{
    protected string $table = TABLES::LOGS_ACTIVITES;
    protected string $primaryKey = 'id_log';

    /**
     * Récupère les logs avec filtres, recherche et pagination
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            if (is_numeric($filters['user_id'])) {
                $sql .= " AND user_id = ?";
                $params[] = (int)$filters['user_id'];
            } else {
                $sql .= " AND user_code = ?";
                $params[] = trim($filters['user_id']);
            }
        }

        if (!empty($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = strtoupper(trim($filters['action']));
        }

        if (!empty($filters['module'])) {
            $sql .= " AND module = ?";
            $params[] = strtolower(trim($filters['module']));
        }

        if (!empty($filters['date_debut'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['date_debut'] . ' 00:00:00';
        }

        if (!empty($filters['date_fin'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['date_fin'] . ' 23:59:59';
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (description LIKE ? OR user_nom LIKE ? OR user_code LIKE ? OR entite_id LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY id_log DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Compte le nombre total de logs correspondant aux filtres
     */
    public function countLogs(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            if (is_numeric($filters['user_id'])) {
                $sql .= " AND user_id = ?";
                $params[] = (int)$filters['user_id'];
            } else {
                $sql .= " AND user_code = ?";
                $params[] = trim($filters['user_id']);
            }
        }

        if (!empty($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = strtoupper(trim($filters['action']));
        }

        if (!empty($filters['module'])) {
            $sql .= " AND module = ?";
            $params[] = strtolower(trim($filters['module']));
        }

        if (!empty($filters['date_debut'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['date_debut'] . ' 00:00:00';
        }

        if (!empty($filters['date_fin'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['date_fin'] . ' 23:59:59';
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (description LIKE ? OR user_nom LIKE ? OR user_code LIKE ? OR entite_id LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère la liste des modules distincts enregistrés
     */
    public function getDistinctModules(): array
    {
        $stmt = $this->getCon()->query("SELECT DISTINCT module FROM {$this->table} ORDER BY module ASC");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    /**
     * Récupère la liste des actions distinctes enregistrées
     */
    public function getDistinctActions(): array
    {
        $stmt = $this->getCon()->query("SELECT DISTINCT action FROM {$this->table} ORDER BY action ASC");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    /**
     * Récupère les logs récents d'un utilisateur spécifique
     */
    public function getRecentByUser(int $userId, int $limit = 10): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = ? 
            ORDER BY id_log DESC 
            LIMIT " . (int)$limit
        );
        $stmt->execute([(int)$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
