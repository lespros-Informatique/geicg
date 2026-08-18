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
            $db = $this->pdo->getCon();
            $stats = [
                'is_pressing' => ($pressingCode !== null),
                'pressing_code' => $pressingCode
            ];

            if ($pressingCode !== null) {
                // Requête directe ultra-rapide sur la vue SQL v_pressing_dashboard_stats
                $stmt = $db->prepare("SELECT * FROM v_pressing_dashboard_stats WHERE code_pressing = ? LIMIT 1");
                $stmt->execute([$pressingCode]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $stats['pressing'] = [
                    'code_pressing' => $row['code_pressing'] ?? $pressingCode,
                    'libelle_pressing' => $row['libelle_pressing'] ?? $pressingCode,
                    'telephone_pressing' => $row['telephone_pressing'] ?? '',
                    'email_pressing' => $row['email_pressing'] ?? '',
                    'adresse_pressing' => $row['adresse_pressing'] ?? '',
                    'logo_pressing' => $row['logo_pressing'] ?? ''
                ];

                $stats['commandes']     = (int)($row['total_commandes'] ?? 0);
                $stats['a_traiter']     = (int)($row['a_traiter'] ?? 0);
                $stats['en_traitement'] = (int)($row['en_traitement'] ?? 0);
                $stats['pretes']        = (int)($row['pretes'] ?? 0);
                $stats['en_livraison']  = (int)($row['en_livraison'] ?? 0);
                $stats['livrees']       = (int)($row['livrees'] ?? 0);
                $stats['ca_total']      = (float)($row['ca_total'] ?? 0);
                $stats['ca_encaisse']   = (float)($row['ca_encaisse'] ?? 0);
                $stats['clients']       = (int)($row['total_clients'] ?? 0);
                $stats['livreurs']      = (int)($row['total_livreurs'] ?? 0);
                $stats['articles']      = (int)($row['total_tarifs'] ?? 0);
                $stats['tarifs']        = $stats['articles'];
                $stats['users']         = 1;

            } else {
                // Vue Super Admin : Agrégation globale depuis la vue SQL
                $stmt = $db->query("
                    SELECT 
                        COUNT(code_pressing) AS pressings,
                        COALESCE(SUM(total_commandes), 0) AS commandes,
                        COALESCE(SUM(a_traiter), 0) AS a_traiter,
                        COALESCE(SUM(en_traitement), 0) AS en_traitement,
                        COALESCE(SUM(pretes), 0) AS pretes,
                        COALESCE(SUM(en_livraison), 0) AS en_livraison,
                        COALESCE(SUM(livrees), 0) AS livrees,
                        COALESCE(SUM(ca_total), 0) AS ca_total,
                        COALESCE(SUM(ca_encaisse), 0) AS ca_encaisse
                    FROM v_pressing_dashboard_stats
                ");
                $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $stats['pressings']     = (int)($row['pressings'] ?? 0);
                $stats['commandes']     = (int)($row['commandes'] ?? 0);
                $stats['a_traiter']     = (int)($row['a_traiter'] ?? 0);
                $stats['en_traitement'] = (int)($row['en_traitement'] ?? 0);
                $stats['pretes']        = (int)($row['pretes'] ?? 0);
                $stats['en_livraison']  = (int)($row['en_livraison'] ?? 0);
                $stats['livrees']       = (int)($row['livrees'] ?? 0);
                $stats['ca_total']      = (float)($row['ca_total'] ?? 0);
                $stats['ca_encaisse']   = (float)($row['ca_encaisse'] ?? 0);

                $stats['clients']       = (int) $db->query("SELECT COUNT(*) FROM " . TABLES::CLIENTS . " WHERE statut_client = 'actif'")->fetchColumn();
                $stats['articles']      = (int) $db->query("SELECT COUNT(*) FROM " . TABLES::ARTICLES_PRESSINGS . " WHERE statut_article = 'actif'")->fetchColumn();
                $stats['users']         = (int) $db->query("SELECT COUNT(*) FROM " . TABLES::USERS . " WHERE statut_user = 'actif'")->fetchColumn();
            }

            return $stats;
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return [
                'is_pressing' => false,
                'users' => 0,
                'clients' => 0,
                'articles' => 0,
                'commandes' => 0,
                'ca_total' => 0,
                'ca_encaisse' => 0,
                'a_traiter' => 0,
                'en_traitement' => 0,
                'pretes' => 0,
                'livrees' => 0
            ];
        }
    }

    public function getRecentOrders(int $limit = 10, ?string $pressingCode = null): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT * FROM v_commandes_dashboard";

            if ($pressingCode !== null) {
                $sql .= " WHERE pressing_code = ? ORDER BY id_commande DESC LIMIT " . (int)$limit;
                $stmt = $db->prepare($sql);
                $stmt->execute([$pressingCode]);
            } else {
                $sql .= " ORDER BY id_commande DESC LIMIT " . (int)$limit;
                $stmt = $db->query($sql);
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $validator = new Validator();
            $stepLabels = [
                'creee' => 'Créée',
                'en_attente_collecte' => 'En attente collecte',
                'collectee' => 'Collectée',
                'inventaire_fait' => 'Inventaire fait',
                'devis_emis' => 'Devis émis',
                'devis_valide' => 'Devis validé',
                'en_traitement' => 'En traitement',
                'prete' => 'Prête',
                'en_livraison' => 'En livraison',
                'livree' => 'Livrée',
                'annulee' => 'Annulée'
            ];

            foreach ($rows as &$row) {
                $row['editId'] = $validator->crypter((int) ($row['id_commande'] ?? 0));
                $st = $row['statut_suivi_commande'] ?? 'creee';
                $row['statutLabel'] = $stepLabels[$st] ?? ucfirst(str_replace('_', ' ', $st));
                $row['date_formatted'] = !empty($row['created_at_commande']) ? date('d/m/Y H:i', strtotime($row['created_at_commande'])) : '-';
                $row['type_label'] = ($row['type_commande'] === 'colis') ? '📦 Sac sans détail' : '👕 Détaillée';
            }
            return $rows;
        } catch (Exception $e) {
            error_log("Dashboard recent orders error: " . $e->getMessage());
            return [];
        }
    }
}
