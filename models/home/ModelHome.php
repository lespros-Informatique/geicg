<?php

class ModelHome extends BaseModel
{
    protected string $table = 'commandes';
    protected string $primaryKey = 'id_commande';
    protected ?string $statusField = 'statut_commande';
    protected ?string $createdAtField = 'created_at_commande';

    public function __construct()
    {
        parent::__construct();
    }

    public function getStats(?string $pressingCode = null): array
    {
        try {
            $stats = [];
            $stats['users'] = (int) $this->pdo->getCon()->query("SELECT COUNT(*) FROM users WHERE statut_user = 'actif'")->fetchColumn();
            $stats['clients'] = (int) $this->pdo->getCon()->query("SELECT COUNT(*) FROM clients WHERE statut_client = 'actif'")->fetchColumn();
            
            if ($pressingCode !== null) {
                $stmtArt = $this->pdo->getCon()->prepare("SELECT COUNT(*) FROM tarifs_articles WHERE pressing_code = ? AND statut_tarif = 'actif'");
                $stmtArt->execute([$pressingCode]);
                $stats['articles'] = (int) $stmtArt->fetchColumn();
            } else {
                $stats['articles'] = (int) $this->pdo->getCon()->query("SELECT COUNT(*) FROM articles_pressings WHERE statut_article = 'actif'")->fetchColumn();
            }

            $sqlCmd = "SELECT COUNT(*) FROM commandes WHERE 1=1";
            $params = [];
            if ($pressingCode !== null) {
                $sqlCmd .= " AND pressing_code = ?";
                $params[] = $pressingCode;
            }
            $stmt = $this->pdo->getCon()->prepare($sqlCmd);
            $stmt->execute($params);
            $stats['commandes'] = (int) $stmt->fetchColumn();

            // Total CA (livré)
            $sqlCa = "SELECT COALESCE(SUM(montant_total_commande), 0) FROM commandes WHERE statut_suivi_commande = 'livree'";
            if ($pressingCode !== null) {
                $sqlCa .= " AND pressing_code = '" . str_replace("'", "''", $pressingCode) . "'";
            }
            $stats['ca_total'] = (float) $this->pdo->getCon()->query($sqlCa)->fetchColumn();

            return $stats;
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return [
                'users' => 0,
                'clients' => 0,
                'articles' => 0,
                'commandes' => 0,
                'ca_total' => 0
            ];
        }
    }

    public function getRecentOrders(int $limit = 10, ?string $pressingCode = null): array
    {
        try {
            $sql = "
                SELECT
                    c.code_commande,
                    COALESCE(cl.nom_client, 'Client') AS nom_client,
                    c.montant_total_commande,
                    c.statut_commande,
                    COALESCE(c.statut_suivi_commande, 'creee') AS statut_suivi_commande,
                    c.created_at_commande,
                    c.id_commande,
                    c.pressing_code,
                    c.type_commande
                FROM commandes c
                LEFT JOIN clients cl ON cl.code_client = c.client_code
            ";

            $params = [];
            if ($pressingCode !== null) {
                $sql .= " WHERE c.pressing_code = ?";
                $params[] = $pressingCode;
            }

            $sql .= " ORDER BY c.id_commande DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->pdo->getCon()->prepare($sql);
            $stmt->bindValue(1, $pressingCode ?? $limit, is_int($limit) && $pressingCode === null ? PDO::PARAM_INT : PDO::PARAM_STR);
            if ($pressingCode !== null) {
                $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $validator = new Validator();
            foreach ($rows as &$row) {
                $row['editId'] = $validator->crypter((int) ($row['id_commande'] ?? 0));
                $row['statutLabel'] = ucfirst(str_replace('_', ' ', $row['statut_suivi_commande'] ?? 'creee'));
            }
            return $rows;
        } catch (Exception $e) {
            error_log("Dashboard recent orders error: " . $e->getMessage());
            return [];
        }
    }
}
