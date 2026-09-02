<?php

class ModelAnnee extends BaseModel
{
    protected string $table = 'annees';
    protected string $primaryKey = 'id_annee';
    protected ?string $statusField = 'statut_annee';
    protected ?string $createdAtField = 'created_at_annee';

    /**
     * Active une année académique en désactivant automatiquement toutes les autres (Exclusivité)
     */
    public function setActiveYear(int $id): bool
    {
        try {
            $db = $this->getCon();
            $db->beginTransaction();
            // Désactiver toutes les années
            $db->exec("UPDATE `annees` SET `statut_annee` = 'inactif'");
            // Activer l'année demandée
            $stmt = $db->prepare("UPDATE `annees` SET `statut_annee` = 'actif' WHERE `id_annee` = ?");
            $stmt->execute([$id]);
            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->getCon()->inTransaction()) {
                $this->getCon()->rollBack();
            }
            error_log("ModelAnnee::setActiveYear error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Surcharge de toggleStatus pour garantir qu'il n'y a qu'une seule année active
     */
    public function toggleStatus(int $id): bool
    {
        $current = $this->getById($id);
        if (!$current) return false;

        // Si l'année n'est pas active, on l'active (et désactive toutes les autres)
        if (($current['statut_annee'] ?? '') !== 'actif') {
            return $this->setActiveYear($id);
        } else {
            // Si on désactive l'année active
            return parent::updateStatus($id, 'inactif');
        }
    }

    /**
     * Récupère l'unique année académique active en base
     */
    public function getActiveYear(): ?array
    {
        try {
            $stmt = $this->getCon()->query("SELECT * FROM `annees` WHERE `statut_annee` = 'actif' LIMIT 1");
            $res = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            if (!$res) {
                // Fallback sur la plus récente si aucune n'est active
                $stmt2 = $this->getCon()->query("SELECT * FROM `annees` ORDER BY `id_annee` DESC LIMIT 1");
                $res = $stmt2 ? $stmt2->fetch(PDO::FETCH_ASSOC) : null;
            }
            return $res ?: null;
        } catch (Exception $e) {
            return null;
        }
    }
}
