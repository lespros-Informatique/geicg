<?php

class ModelPressing extends BaseModel
{
    protected string $table = 'pressings';
    protected string $primaryKey = 'id_pressing';
    protected ?string $statusField = 'statut_pressing';
    protected ?string $createdAtField = 'created_at_pressing';

    public function getByCode(string $code): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT * FROM {$this->table} WHERE code_pressing = ? LIMIT 1");
            $stmt->execute([$code]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            error_log('[ModelPressing::getByCode] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Statistiques globales du pressing pour le tableau de bord 360°
     */
    public function getPressingStats(string $pressingCode): array
    {
        try {
            // Total commandes & Total CA
            $sql = "
                SELECT 
                    COUNT(*) as total_commandes,
                    COALESCE(SUM(CASE WHEN statut_suivi_commande = 'livree' THEN montant_total_commande ELSE 0 END), 0) as ca_total,
                    COUNT(DISTINCT client_code) as total_clients,
                    COALESCE(SUM(CASE WHEN statut_suivi_commande NOT IN ('livree', 'refusee', 'annulee') THEN 1 ELSE 0 END), 0) as commandes_en_cours,
                    COALESCE(SUM(CASE WHEN statut_suivi_commande = 'creee' THEN 1 ELSE 0 END), 0) as commandes_nouvelles
                FROM " . TABLES::COMMANDES . "
                WHERE pressing_code = ?
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            // Total articles tarifés
            $stmtT = $this->getCon()->prepare("SELECT COUNT(*) FROM " . TABLES::TARIFS_ARTICLES . " WHERE pressing_code = ? AND statut_tarif = 'actif'");
            $stmtT->execute([$pressingCode]);
            $stats['total_tarifs'] = (int)$stmtT->fetchColumn();

            // Total livreurs rattachés
            $stmtL = $this->getCon()->prepare("SELECT COUNT(DISTINCT livreur_code) FROM " . TABLES::MISSIONS . " m JOIN " . TABLES::COMMANDES . " c ON m.commande_code = c.code_commande WHERE c.pressing_code = ?");
            $stmtL->execute([$pressingCode]);
            $stats['total_livreurs'] = (int)$stmtL->fetchColumn();

            return $stats;
        } catch (Exception $e) {
            error_log("Erreur getPressingStats: " . $e->getMessage());
            return [
                'total_commandes' => 0,
                'ca_total' => 0,
                'total_clients' => 0,
                'commandes_en_cours' => 0,
                'commandes_nouvelles' => 0,
                'total_tarifs' => 0,
                'total_livreurs' => 0
            ];
        }
    }

    /**
     * Liste des commandes du pressing
     */
    public function getPressingOrders(string $pressingCode, int $limit = 100): array
    {
        try {
            $sql = "
                SELECT 
                    c.*, 
                    COALESCE(cl.nom_client, 'Client') as nom_client, 
                    COALESCE(cl.telephone_client, '-') as telephone_client,
                    COALESCE(cl.email_client, '') as email_client
                FROM " . TABLES::COMMANDES . " c
                LEFT JOIN " . TABLES::CLIENTS . " cl ON c.client_code = cl.code_client
                WHERE c.pressing_code = ?
                ORDER BY c.id_commande DESC
                LIMIT ?
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->bindValue(1, $pressingCode, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getPressingOrders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Liste des articles et tarifs du pressing
     */
    public function getPressingTarifs(string $pressingCode): array
    {
        try {
            $sql = "
                SELECT 
                    t.*, 
                    COALESCE(a.libelle_article, 'Article') as libelle_article,
                    COALESCE(s.libelle_service, 'Service') as libelle_service,
                    COALESCE(cat.libelle_categorie_article, 'Catégorie') as libelle_categorie
                FROM " . TABLES::TARIFS_ARTICLES . " t
                LEFT JOIN " . TABLES::ARTICLES_PRESSINGS . " a ON t.article_code = a.code_article
                LEFT JOIN " . TABLES::SERVICES . " s ON t.service_code = s.code_service
                LEFT JOIN " . TABLES::CATEGORIES_ARTICLES . " cat ON a.categorie_article_code = cat.code_categorie_article
                WHERE t.pressing_code = ?
                ORDER BY cat.libelle_categorie_article ASC, a.libelle_article ASC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getPressingTarifs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Horaires d'ouverture du pressing
     */
    public function getPressingHoraires(string $pressingCode): array
    {
        try {
            $sql = "SELECT * FROM " . TABLES::HORAIRES_PRESSINGS . " WHERE pressing_code = ? ORDER BY FIELD(jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getPressingHoraires: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clients fidélisés ayant commandé dans ce pressing
     */
    public function getPressingClients(string $pressingCode): array
    {
        try {
            $sql = "
                SELECT 
                    cl.code_client,
                    cl.nom_client,
                    cl.telephone_client,
                    cl.email_client,
                    cl.adresse_client,
                    COUNT(c.id_commande) as nb_commandes,
                    COALESCE(SUM(c.montant_total_commande), 0) as total_depense,
                    MAX(c.created_at_commande) as derniere_commande
                FROM " . TABLES::COMMANDES . " c
                JOIN " . TABLES::CLIENTS . " cl ON c.client_code = cl.code_client
                WHERE c.pressing_code = ?
                GROUP BY cl.code_client, cl.nom_client, cl.telephone_client, cl.email_client, cl.adresse_client
                ORDER BY total_depense DESC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getPressingClients: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Missions de livraison/collecte liées à ce pressing
     */
    public function getPressingMissions(string $pressingCode, int $limit = 50): array
    {
        try {
            $sql = "
                SELECT 
                    m.*,
                    c.code_commande,
                    c.montant_total_commande,
                    c.statut_suivi_commande,
                    COALESCE(l.nom_livreur, 'Non assigné') as nom_livreur,
                    COALESCE(l.telephone_livreur, '-') as telephone_livreur,
                    COALESCE(cl.nom_client, 'Client') as nom_client,
                    COALESCE(cl.telephone_client, '-') as telephone_client
                FROM " . TABLES::MISSIONS . " m
                JOIN " . TABLES::COMMANDES . " c ON m.commande_code = c.code_commande
                LEFT JOIN " . TABLES::LIVREURS . " l ON m.livreur_code = l.code_livreur
                LEFT JOIN " . TABLES::CLIENTS . " cl ON c.client_code = cl.code_client
                WHERE c.pressing_code = ?
                ORDER BY m.id_mission DESC
                LIMIT ?
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->bindValue(1, $pressingCode, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getPressingMissions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Abonnement B2B et forfait actif du pressing
     */
    public function getPressingAbonnement(string $pressingCode): ?array
    {
        try {
            $sql = "
                SELECT 
                    ap.*, 
                    COALESCE(f.libelle_forfait, 'Forfait Standard') as libelle_forfait, 
                    COALESCE(ap.montant_abonnement, f.montant_forfait, 0) as montant_abonnement,
                    COALESCE(f.montant_forfait, 0) as montant_forfait,
                    COALESCE(f.description_forfait, '') as description_forfait,
                    DATEDIFF(ap.date_fin_abonnement, CURDATE()) as jours_restants
                FROM " . TABLES::ABONNEMENTS_PRESSINGS . " ap
                LEFT JOIN " . TABLES::FORFAITS . " f ON ap.forfait_code = f.code_forfait
                WHERE ap.pressing_code = ?
                ORDER BY ap.id_abonnement_pressing DESC
                LIMIT 1
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            error_log("Erreur getPressingAbonnement: " . $e->getMessage());
            return null;
        }
    }
}
