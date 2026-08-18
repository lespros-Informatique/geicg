<?php

class ModelNotification extends BaseModel
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id_notification';
    protected ?string $statusField = 'statut_notification';
    protected ?string $createdAtField = 'created_at_notification';

    /**
     * Récupère toutes les notifications avec les informations du client destinataire
     */
    public function getAllWithClient(?string $pressingCode = null, int $limit = 500): array
    {
        try {
            $sql = "
                SELECT 
                    n.*,
                    c.nom_client,
                    c.telephone_client,
                    c.email_client
                FROM {$this->table} n
                LEFT JOIN " . TABLES::CLIENTS . " c ON n.client_code = c.code_client
                ORDER BY n.id_notification DESC
                LIMIT :limit
            ";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelNotification::getAllWithClient] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Statistiques globales des notifications
     */
    public function getStats(?string $pressingCode = null): array
    {
        try {
            $stats = [
                'total' => 0,
                'non_lues' => 0,
                'lues' => 0,
                'commandes' => 0,
                'alertes' => 0
            ];

            $stmt = $this->getCon()->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN lu_notification = 0 THEN 1 ELSE 0 END) as non_lues,
                    SUM(CASE WHEN lu_notification = 1 THEN 1 ELSE 0 END) as lues,
                    SUM(CASE WHEN type_notification LIKE 'commande%' THEN 1 ELSE 0 END) as commandes,
                    SUM(CASE WHEN type_notification NOT LIKE 'commande%' THEN 1 ELSE 0 END) as alertes
                FROM {$this->table}
            ");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $stats['total'] = (int)($row['total'] ?? 0);
                $stats['non_lues'] = (int)($row['non_lues'] ?? 0);
                $stats['lues'] = (int)($row['lues'] ?? 0);
                $stats['commandes'] = (int)($row['commandes'] ?? 0);
                $stats['alertes'] = (int)($row['alertes'] ?? 0);
            }

            return $stats;
        } catch (Exception $e) {
            error_log('[ModelNotification::getStats] ' . $e->getMessage());
            return ['total' => 0, 'non_lues' => 0, 'lues' => 0, 'commandes' => 0, 'alertes' => 0];
        }
    }

    /**
     * Marque une notification spécifique comme lue
     */
    public function markAsRead(int $id): bool
    {
        try {
            $stmt = $this->getCon()->prepare("UPDATE {$this->table} SET lu_notification = 1 WHERE {$this->primaryKey} = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('[ModelNotification::markAsRead] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bascule le statut lu/non lu d'une notification
     */
    public function toggleRead(int $id): bool
    {
        try {
            $stmt = $this->getCon()->prepare("
                UPDATE {$this->table} 
                SET lu_notification = CASE WHEN lu_notification = 1 THEN 0 ELSE 1 END 
                WHERE {$this->primaryKey} = ?
            ");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('[ModelNotification::toggleRead] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function markAllAsRead(): bool
    {
        try {
            return $this->getCon()->exec("UPDATE {$this->table} SET lu_notification = 1 WHERE lu_notification = 0") !== false;
        } catch (Exception $e) {
            error_log('[ModelNotification::markAllAsRead] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une notification
     */
    public function deleteNotification(int $id): bool
    {
        try {
            $stmt = $this->getCon()->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('[ModelNotification::deleteNotification] ' . $e->getMessage());
            return false;
        }
    }
}
