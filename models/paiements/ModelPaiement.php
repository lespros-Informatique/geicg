<?php

class ModelPaiement extends BaseModel
{
    protected string $table = 'paiements';
    protected string $primaryKey = 'id_paiement';
    protected ?string $statusField = 'statut_paiement';
    protected ?string $createdAtField = 'created_at_paiement';

    public function __construct()
    {
        parent::__construct();
    }

    public function getByCommande(string $commandeCode): array
    {
        try {
            $sql = "SELECT p.* FROM {$this->table} p
                    WHERE p.commande_code = ?
                    ORDER BY p.created_at_paiement DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$commandeCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelPaiement::getByCommande] ' . $e->getMessage());
            return [];
        }
    }

    public function getByLigneCode(string $ligneCode): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE commande_code = ? ORDER BY created_at_paiement DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$ligneCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelPaiement::getByLigneCode] ' . $e->getMessage());
            return [];
        }
    }

    public function existsByLigneAndDate(string $ligneCode, string $date): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE commande_code = ? AND DATE(created_at_paiement) = ?";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$ligneCode, $date]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log('[ModelPaiement::existsByLigneAndDate] ' . $e->getMessage());
            return false;
        }
    }
}
