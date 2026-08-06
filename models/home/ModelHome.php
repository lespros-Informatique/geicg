<?php

class ModelHome
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new Database();
    }

    public function getStats(): array
    {
        try {
            $stats = [];

            $stats['users'] = (int) $this->pdo->getCon()->query("SELECT COUNT(*) FROM users WHERE statut_user = 'actif'")->fetchColumn();
            $stats['clients'] = (int) $this->pdo->getCon()->query("SELECT COUNT(*) FROM clients WHERE statut_client = 'actif'")->fetchColumn();
            $stats['articles'] = (int) $this->pdo->getCon()->query("SELECT COUNT(*) FROM articles_pressings WHERE statut_article = 'actif'")->fetchColumn();
            $stats['commandes'] = (int) $this->pdo->getCon()->query("SELECT COUNT(*) FROM commandes WHERE statut_commande = 'actif'")->fetchColumn();

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

    public function getRecentOrders(int $limit = 10): array
    {
        try {
            $sql = "
                SELECT
                    c.code_commande,
                    COALESCE(cl.nom_client, 'Inconnu') AS nom_client,
                    c.montant_total_commande,
                    c.statut_commande,
                    c.created_at_commande,
                    c.id_commande
                FROM commandes c
                LEFT JOIN clients cl ON cl.code_client = c.client_code
                ORDER BY c.created_at_commande DESC
                LIMIT ?
            ";
            $stmt = $this->pdo->getCon()->prepare($sql);
            $stmt->execute([$limit]);
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
