<?php

class ModelFiliereCycle
{
    private $con;
    private ?string $lastError = null;

    public function __construct()
    {
        $this->con = (new Database())->getCon();
    }

    public function getCon()
    {
        return $this->con;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function getAll()
    {
        $sql = "
            SELECT fc.*, f.libelle_filiere, c.libelle_cycle, n.libelle_niveau,
                   COALESCE(fc.type_filiere, f.type_filiere) AS type_filiere
            FROM " . TABLES::FILIERE_CYCLES . " fc
            LEFT JOIN " . TABLES::FILIERES . " f ON fc.filiere_code = f.code_filiere
            LEFT JOIN " . TABLES::CYCLES . " c ON fc.cycle_code = c.code_cycle
            LEFT JOIN " . TABLES::NIVEAUX . " n ON fc.niveau_code = n.code_niveau
            ORDER BY c.libelle_cycle ASC, f.libelle_filiere ASC, n.libelle_niveau ASC
        ";
        return $this->con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->con->prepare("
            SELECT fc.*, f.libelle_filiere, c.libelle_cycle, n.libelle_niveau,
                   COALESCE(fc.type_filiere, f.type_filiere) AS type_filiere
            FROM " . TABLES::FILIERE_CYCLES . " fc
            LEFT JOIN " . TABLES::FILIERES . " f ON fc.filiere_code = f.code_filiere
            LEFT JOIN " . TABLES::CYCLES . " c ON fc.cycle_code = c.code_cycle
            LEFT JOIN " . TABLES::NIVEAUX . " n ON fc.niveau_code = n.code_niveau
            WHERE fc.id_filiere_cycle = ? 
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data)
    {
        unset($data['id']);
        unset($data['id_filiere_cycle']);

        $fkErr = ForeignKeyValidator::validate($this->con, TABLES::FILIERE_CYCLES, $data);
        if ($fkErr !== null) {
            $this->lastError = $fkErr;
            error_log('[ModelFiliereCycle::create] FK Error: ' . $fkErr);
            return false;
        }

        $cols = array_keys($data);
        $sql = "INSERT INTO " . TABLES::FILIERE_CYCLES . " (" . implode(', ', array_map(function($c){ return "`$c`"; }, $cols)) . ") 
                VALUES (" . implode(', ', array_fill(0, count($cols), '?')) . ")";
        $stmt = $this->con->prepare($sql);
        try {
            return $stmt->execute(array_values($data));
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log('[ModelFiliereCycle::create] PDO Error: ' . $e->getMessage());
            return false;
        }
    }

    public function update(array $data, $id)
    {
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[] = "`$col` = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $sql = "UPDATE " . TABLES::FILIERE_CYCLES . " SET " . implode(', ', $sets) . " WHERE id_filiere_cycle = ?";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleStatus($id)
    {
        $item = $this->getById($id);
        if (!$item) return false;
        $newStatus = ($item['statut_filiere_cycle'] === 'actif') ? 'inactif' : 'actif';
        $stmt = $this->con->prepare("UPDATE " . TABLES::FILIERE_CYCLES . " SET statut_filiere_cycle = ? WHERE id_filiere_cycle = ?");
        return $stmt->execute([$newStatus, $id]);
    }

    public function getActiveFilieres(): array
    {
        $sql = "SELECT code_filiere, libelle_filiere FROM " . TABLES::FILIERES . " WHERE statut_filiere = 'actif' ORDER BY libelle_filiere ASC";
        return $this->con->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getActiveCycles(): array
    {
        $sql = "SELECT code_cycle, libelle_cycle FROM " . TABLES::CYCLES . " WHERE statut_cycle = 'actif' ORDER BY libelle_cycle ASC";
        return $this->con->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTypeFiliereByFiliereCode(string $filiereCode): ?string
    {
        try {
            $stmt = $this->con->prepare("SELECT type_filiere FROM " . TABLES::FILIERES . " WHERE code_filiere = ? LIMIT 1");
            $stmt->execute([$filiereCode]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['type_filiere'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getTableColumns(): array
    {
        try {
            return $this->con->query("DESCRIBE " . TABLES::FILIERE_CYCLES)->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function filterData(array $data): array
    {
        $cols = $this->getTableColumns();
        return array_intersect_key($data, array_flip($cols));
    }

    public function getDetails(int $id): ?array
    {
        try {
            $stmt = $this->con->prepare("
                SELECT fc.*, f.libelle_filiere, c.libelle_cycle
                FROM " . TABLES::FILIERE_CYCLES . " fc
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = fc.filiere_code
                LEFT JOIN " . TABLES::CYCLES . " c ON c.code_cycle = fc.cycle_code
                WHERE fc.id_filiere_cycle = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            error_log("ModelFiliereCycle::getDetails error: " . $e->getMessage());
            return null;
        }
    }

    public function getClassesForParcours(string $filiereCode, ?string $cycleCode = null, ?string $niveauCode = null): array
    {
        try {
            $cycleVal = $cycleCode ?? '';
            $niveauVal = $niveauCode ?? '';
            $stmt = $this->con->prepare("
                SELECT cl.*, n.libelle_niveau, COUNT(i.id_inscription) as nb_etudiants
                FROM " . TABLES::CLASSES . " cl
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::INSCRIPTIONS . " i ON i.classe_code = cl.code_classe AND i.statut_inscription = 'actif'
                WHERE cl.filiere_code = ?
                  AND (cl.cycle_code = ? OR ? = '' OR cl.cycle_code IS NULL)
                  AND (cl.niveau_code = ? OR ? = '' OR cl.niveau_code IS NULL)
                GROUP BY cl.id_classe
                ORDER BY cl.libelle_classe ASC
            ");
            $stmt->execute([$filiereCode, $cycleVal, $cycleVal, $niveauVal, $niveauVal]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelFiliereCycle::getClassesForParcours error: " . $e->getMessage());
            return [];
        }
    }
}