<?php

class ModelPieceFournir extends BaseModel
{
    protected string $table = 'pieces_fournir';
    protected string $primaryKey = 'id_piece_fournir';
    protected ?string $statusField = 'statut_piece';
    protected ?string $createdAtField = 'created_at_piece';

    public function getAll(): array
    {
        $sql = "
            SELECT pf.*,
                   (SELECT COUNT(*) FROM piece_fournir_cycle pfc WHERE pfc.piece_code = pf.code_piece_fournir) AS nb_cycles_utilises
            FROM pieces_fournir pf
            ORDER BY pf.id_piece_fournir DESC
        ";
        try {
            $stmt = $this->getCon()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all pieces_fournir: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array
    {
        $sql = "
            SELECT pf.*
            FROM pieces_fournir pf
            WHERE pf.id_piece_fournir = ?
            LIMIT 1
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get by id pieces_fournir: " . $e->getMessage());
            return [];
        }
    }

    public function getActifs(): array
    {
        $sql = "
            SELECT pf.*
            FROM pieces_fournir pf
            WHERE pf.statut_piece = 'actif'
            ORDER BY pf.libelle_piece ASC
        ";
        try {
            $stmt = $this->getCon()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get actifs pieces_fournir: " . $e->getMessage());
            return [];
        }
    }

    public function getSummaryCounts(): array
    {
        try {
            $db = $this->getCon();
            $total = (int)$db->query("SELECT COUNT(*) FROM pieces_fournir")->fetchColumn();
            $actifs = (int)$db->query("SELECT COUNT(*) FROM pieces_fournir WHERE statut_piece = 'actif'")->fetchColumn();
            $inactifs = (int)$db->query("SELECT COUNT(*) FROM pieces_fournir WHERE statut_piece = 'inactif'")->fetchColumn();
            $utilises = (int)$db->query("SELECT COUNT(DISTINCT piece_code) FROM piece_fournir_cycle")->fetchColumn();

            return [
                'total' => $total,
                'actifs' => $actifs,
                'inactifs' => $inactifs,
                'utilises' => $utilises
            ];
        } catch (Exception $e) {
            return ['total' => 0, 'actifs' => 0, 'inactifs' => 0, 'utilises' => 0];
        }
    public function getByLibelle(string $libelle, ?int $excludeId = null): array
    {
        $sql = "
            SELECT pf.*
            FROM pieces_fournir pf
            WHERE LOWER(TRIM(pf.libelle_piece)) = LOWER(TRIM(?))
        ";
        $params = [trim($libelle)];
        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND pf.id_piece_fournir != ?";
            $params[] = $excludeId;
        }
        $sql .= " LIMIT 1";

        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get by libelle pieces_fournir: " . $e->getMessage());
            return [];
        }
    }

    public function existsByLibelle(string $libelle, ?int $excludeId = null): bool
    {
        $item = $this->getByLibelle($libelle, $excludeId);
        return !empty($item);
    }
}

