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

    public function getById($id): array
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

    /**
     * Récupère toutes les pièces actives avec leur nombre d'exemplaires requis et nature
     */
    public function getPiecesWithRequirements(?string $cycleCode = null, ?string $niveauCode = null): array
    {
        $sql = "
            SELECT pf.*,
                   COALESCE(pfc.nombre_exemplaires, 1) AS nombre_exemplaires,
                   pfc.nature_document
            FROM pieces_fournir pf
            LEFT JOIN piece_fournir_cycle pfc ON pfc.piece_code = pf.code_piece_fournir 
                                            AND pfc.statut_piece_cycle = 'actif'
        ";
        $params = [];
        $where = ["pf.statut_piece = 'actif'"];
        if (!empty($cycleCode)) {
            $where[] = "(pfc.cycle_code = ? OR pfc.cycle_code IS NULL OR pfc.cycle_code = '')";
            $params[] = $cycleCode;
        }
        if (!empty($niveauCode)) {
            $where[] = "(pfc.niveau_code = ? OR pfc.niveau_code IS NULL OR pfc.niveau_code = '')";
            $params[] = $niveauCode;
        }
        $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " GROUP BY pf.id_piece_fournir, pf.code_piece_fournir, pf.libelle_piece, pf.description_piece, pf.etablissement_code, pf.user_code, pf.statut_piece, pf.created_at_piece, pf.updated_at_piece, pfc.nombre_exemplaires, pfc.nature_document
                  ORDER BY pf.id_piece_fournir ASC";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("getPiecesWithRequirements: " . $e->getMessage());
            return $this->getActifs();
        }
    }
}


