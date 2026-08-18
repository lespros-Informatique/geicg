<?php

class ModelNotification extends BaseModel
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id_notification';
    protected ?string $statusField = 'statut_notification';
    protected ?string $createdAtField = 'created_at_notification';

    /**
     * Récupère les notifications filtrées selon le rôle (Livreur, Pressing ou Super Admin)
     */
    public function getAllWithClient(?string $pressingCode = null, ?string $livreurCode = null, int $limit = 500): array
    {
        try {
            if ($livreurCode !== null && $livreurCode !== '') {
                $sql = "
                    SELECT 
                        n.*,
                        c.nom_client,
                        c.telephone_client,
                        c.email_client
                    FROM {$this->table} n
                    LEFT JOIN " . TABLES::CLIENTS . " c ON n.client_code = c.code_client
                    WHERE n.livreur_code = :livreurCode
                    ORDER BY n.id_notification DESC
                    LIMIT :limit
                ";
                $stmt = $this->getCon()->prepare($sql);
                $stmt->bindValue(':livreurCode', $livreurCode);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            if ($pressingCode !== null && $pressingCode !== '') {
                $sql = "
                    SELECT 
                        n.*,
                        c.nom_client,
                        c.telephone_client,
                        c.email_client
                    FROM {$this->table} n
                    LEFT JOIN " . TABLES::CLIENTS . " c ON n.client_code = c.code_client
                    WHERE n.reference_code IN (SELECT code_commande FROM commandes WHERE pressing_code = :pressingCode)
                       OR n.client_code IN (SELECT code_client FROM clients WHERE pressing_code = :pressingCode2)
                    ORDER BY n.id_notification DESC
                    LIMIT :limit
                ";
                $stmt = $this->getCon()->prepare($sql);
                $stmt->bindValue(':pressingCode', $pressingCode);
                $stmt->bindValue(':pressingCode2', $pressingCode);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

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
     * Statistiques des notifications filtrées par rôle
     */
    public function getStats(?string $pressingCode = null, ?string $livreurCode = null): array
    {
        try {
            $stats = [
                'total' => 0,
                'non_lues' => 0,
                'lues' => 0,
                'commandes' => 0,
                'alertes' => 0
            ];

            if ($livreurCode !== null && $livreurCode !== '') {
                $stmt = $this->getCon()->prepare("
                    SELECT 
                        COUNT(*) as total,
                        COALESCE(SUM(CASE WHEN lu_notification = 0 THEN 1 ELSE 0 END), 0) as non_lues,
                        COALESCE(SUM(CASE WHEN lu_notification = 1 THEN 1 ELSE 0 END), 0) as lues,
                        COALESCE(SUM(CASE WHEN type_notification LIKE 'commande%' OR type_notification LIKE 'mission%' THEN 1 ELSE 0 END), 0) as commandes,
                        COALESCE(SUM(CASE WHEN type_notification NOT LIKE 'commande%' AND type_notification NOT LIKE 'mission%' THEN 1 ELSE 0 END), 0) as alertes
                    FROM {$this->table} n
                    WHERE n.livreur_code = :livreurCode
                ");
                $stmt->execute([':livreurCode' => $livreurCode]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif ($pressingCode !== null && $pressingCode !== '') {
                $stmt = $this->getCon()->prepare("
                    SELECT 
                        COUNT(*) as total,
                        COALESCE(SUM(CASE WHEN lu_notification = 0 THEN 1 ELSE 0 END), 0) as non_lues,
                        COALESCE(SUM(CASE WHEN lu_notification = 1 THEN 1 ELSE 0 END), 0) as lues,
                        COALESCE(SUM(CASE WHEN type_notification LIKE 'commande%' THEN 1 ELSE 0 END), 0) as commandes,
                        COALESCE(SUM(CASE WHEN type_notification NOT LIKE 'commande%' THEN 1 ELSE 0 END), 0) as alertes
                    FROM {$this->table} n
                    WHERE n.reference_code IN (SELECT code_commande FROM commandes WHERE pressing_code = :pressingCode)
                       OR n.client_code IN (SELECT code_client FROM clients WHERE pressing_code = :pressingCode2)
                ");
                $stmt->execute([':pressingCode' => $pressingCode, ':pressingCode2' => $pressingCode]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->getCon()->query("
                    SELECT 
                        COUNT(*) as total,
                        COALESCE(SUM(CASE WHEN lu_notification = 0 THEN 1 ELSE 0 END), 0) as non_lues,
                        COALESCE(SUM(CASE WHEN lu_notification = 1 THEN 1 ELSE 0 END), 0) as lues,
                        COALESCE(SUM(CASE WHEN type_notification LIKE 'commande%' THEN 1 ELSE 0 END), 0) as commandes,
                        COALESCE(SUM(CASE WHEN type_notification NOT LIKE 'commande%' THEN 1 ELSE 0 END), 0) as alertes
                    FROM {$this->table}
                ");
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
            }

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
     * Marque toutes les notifications comme lues (filtré pour le livreur si applicable)
     */
    public function markAllAsRead(?string $pressingCode = null, ?string $livreurCode = null): bool
    {
        try {
            if ($livreurCode !== null && $livreurCode !== '') {
                $stmt = $this->getCon()->prepare("
                    UPDATE {$this->table} 
                    SET lu_notification = 1 
                    WHERE lu_notification = 0 
                      AND livreur_code = :livreurCode
                ");
                return $stmt->execute([':livreurCode' => $livreurCode]);
            }

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
