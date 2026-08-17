<?php

class ModelHome
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new Database();
    }

    public function getStats(?string $pressingCode = null): array
    {
        try {
            $stats = [];
            $stats['users'] = (int) $this->pdo->getCon()->query("SELECT COUNT(*) FROM users WHERE statut_user = 'actif'")->fetchColumn();
            $stats['clients'] = (int) $this->pdo->getCon()->query("SELECT COUNT(*) FROM clients WHERE statut_client = 'actif'")->fetchColumn();
            $stats['articles'] = (int) $this->pdo->getCon()->query("SELECT COUNT(*) FROM articles_pressings WHERE statut_article = 'actif'" . ($pressingCode ? " AND pressing_code = '" . str_replace("'", "''", $pressingCode) . "'" : ""))->fetchColumn();

            $sqlCmd = "SELECT COUNT(*) FROM commandes WHERE statut_commande = 'actif'";
            $params = [];
            if ($pressingCode !== null) {
                $sqlCmd .= " AND pressing_code = ?";
                $params[] = $pressingCode;
            }
            $stmt = $this->pdo->getCon()->prepare($sqlCmd);
            $stmt->execute($params);
            $stats['commandes'] = (int) $stmt->fetchColumn();

            return $stats;
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return [
                'users' => 0,
                'clients' => 0,
                'articles' => 0,
                'commandes' => 0
            ];
        }
    }

    public function getRecentOrders(int $limit = 10, ?string $pressingCode = null): array
    {
        try {
            $sql = "
                SELECT
                    c.code_commande,
                    COALESCE(cl.nom_client, 'Inconnu') AS nom_client,
                    c.montant_total_commande,
                    c.statut_commande,
                    c.created_at_commande,
                    c.id_commande,
                    c.pressing_code
                FROM commandes c
                LEFT JOIN clients cl ON cl.code_client = c.client_code
            ";

            $params = [];
            if ($pressingCode !== null) {
                $sql .= " WHERE c.pressing_code = ?";
                $params[] = $pressingCode;
            }

            $sql .= " ORDER BY c.created_at_commande DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->pdo->getCon()->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $validator = new Validator();
            foreach ($rows as &$row) {
                $row['editId'] = $validator->crypter((int) ($row['id_commande'] ?? 0));
                $row['statutLabel'] = ucfirst($row['statut_commande'] ?? '');
            }
            return $rows;
        } catch (Exception $e) {
            error_log("Dashboard recent orders error: " . $e->getMessage());
            return [];
        }
    }
}
