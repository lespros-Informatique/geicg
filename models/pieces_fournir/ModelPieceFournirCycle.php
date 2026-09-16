<?php

class ModelPieceFournirCycle extends BaseModel
{
    protected string $table = 'piece_fournir_cycle';
    protected string $primaryKey = 'id_piece_cycle';
    protected ?string $statusField = 'statut_piece_cycle';
    protected ?string $createdAtField = 'created_at_piece_cycle';

    public function getAll(?string $cycleCode = null): array
    {
        $sql = "
            SELECT pfc.*, 
                   c.libelle_cycle,
                   pf.libelle_piece,
                   pf.description_piece
            FROM piece_fournir_cycle pfc
            LEFT JOIN cycles c ON c.code_cycle = pfc.cycle_code
            LEFT JOIN pieces_fournir pf ON pf.code_piece_fournir = pfc.piece_code
        ";
        $conditions = [];
        $params = [];
        if (!empty($cycleCode)) {
            $conditions[] = "pfc.cycle_code = ?";
            $params[] = $cycleCode;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY pfc.id_piece_cycle DESC ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all piece_fournir_cycle: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array
    {
        $sql = "
            SELECT pfc.*, 
                   c.libelle_cycle,
                   pf.libelle_piece,
                   pf.description_piece
            FROM piece_fournir_cycle pfc
            LEFT JOIN cycles c ON c.code_cycle = pfc.cycle_code
            LEFT JOIN pieces_fournir pf ON pf.code_piece_fournir = pfc.piece_code
            WHERE pfc.id_piece_cycle = ?
            LIMIT 1
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get by id piece_fournir_cycle: " . $e->getMessage());
            return [];
        }
    }

    public function getByCycle(string $cycleCode): array
    {
        $sql = "
            SELECT pfc.*, 
                   c.libelle_cycle,
                   pf.libelle_piece,
                   pf.description_piece
            FROM piece_fournir_cycle pfc
            LEFT JOIN cycles c ON c.code_cycle = pfc.cycle_code
            LEFT JOIN pieces_fournir pf ON pf.code_piece_fournir = pfc.piece_code
            WHERE pfc.statut_piece_cycle = 'actif'
              AND (pfc.cycle_code = ? OR pfc.cycle_code = '' OR pfc.cycle_code IS NULL)
            ORDER BY pfc.est_obligatoire ASC, pf.libelle_piece ASC
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$cycleCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get piece_fournir_cycle by cycle: " . $e->getMessage());
            return [];
        }
    }

    public function existsForCycle(string $cycleCode, string $pieceCode, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM piece_fournir_cycle WHERE cycle_code = ? AND piece_code = ?";
        $params = [$cycleCode, $pieceCode];
        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND id_piece_cycle != ?";
            $params[] = $excludeId;
        }
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (Exception $e) {
            error_log("existsForCycle error: " . $e->getMessage());
            return false;
        }
    }

    public function getAssignedPieceCodes(string $cycleCode): array
    {
        $sql = "SELECT piece_code FROM piece_fournir_cycle WHERE cycle_code = ?";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$cycleCode]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (Exception $e) {
            error_log("getAssignedPieceCodes error: " . $e->getMessage());
            return [];
        }
    }

    public function getSummaryCounts(?string $cycleCode = null): array
    {
        try {
            $db = $this->getCon();
            $conditions = [];
            $params = [];
            if (!empty($cycleCode)) {
                $conditions[] = "cycle_code = ?";
                $params[] = $cycleCode;
            }

            $whereStr = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

            $stmtTot = $db->prepare("SELECT COUNT(*) FROM piece_fournir_cycle" . $whereStr);
            $stmtTot->execute($params);
            $total = (int)$stmtTot->fetchColumn();

            $condObl = array_merge($conditions, ["est_obligatoire = 'obligatoire'"]);
            $stmtObl = $db->prepare("SELECT COUNT(*) FROM piece_fournir_cycle WHERE " . implode(" AND ", $condObl));
            $stmtObl->execute($params);
            $obligatoires = (int)$stmtObl->fetchColumn();

            $condFac = array_merge($conditions, ["est_obligatoire != 'obligatoire'"]);
            $stmtFac = $db->prepare("SELECT COUNT(*) FROM piece_fournir_cycle WHERE " . implode(" AND ", $condFac));
            $stmtFac->execute($params);
            $facultatifs = (int)$stmtFac->fetchColumn();

            $condCyc = array_merge($conditions, ["statut_piece_cycle = 'actif'"]);
            $stmtCyc = $db->prepare("SELECT COUNT(DISTINCT cycle_code) FROM piece_fournir_cycle WHERE " . implode(" AND ", $condCyc));
            $stmtCyc->execute($params);
            $cyclesConfigures = (int)$stmtCyc->fetchColumn();

            return [
                'total' => $total,
                'obligatoires' => $obligatoires,
                'facultatifs' => $facultatifs,
                'cycles_configures' => $cyclesConfigures
            ];
        } catch (Exception $e) {
            return ['total' => 0, 'obligatoires' => 0, 'facultatifs' => 0, 'cycles_configures' => 0];
        }
    }
}
