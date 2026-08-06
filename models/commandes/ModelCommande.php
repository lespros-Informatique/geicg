<?php

class ModelCommande extends BaseModel
{
    protected string $table = 'commandes';
    protected string $primaryKey = 'id_commande';
    protected ?string $statusField = 'statut_commande';
    protected ?string $createdAtField = 'created_at_commande';

    public function __construct()
    {
        parent::__construct();
    }

    public function getWithDetails(int $id): array
    {
        try {
            $sql = "SELECT cmd.*, cl.nom_client, cl.telephone_client, cl.quartier_client, cl.adresse_client
                    FROM {$this->table} cmd
                    INNER JOIN clients cl ON cl.code_client = cmd.client_code
                    WHERE cmd.{$this->primaryKey} = ?";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelCommande::getWithDetails] ' . $e->getMessage());
            return [];
        }
    }

    public function getByClient(string $clientCode): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE client_code = ? ORDER BY created_at_commande DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$clientCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelCommande::getByClient] ' . $e->getMessage());
            return [];
        }
    }
}
