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
            SELECT fc.*, f.libelle_filiere, c.libelle_cycle 
            FROM filiere_cycles fc
            LEFT JOIN filieres f ON fc.filiere_code = f.code_filiere
            LEFT JOIN cycles c ON fc.cycle_code = c.code_cycle
            ORDER BY c.libelle_cycle ASC, f.libelle_filiere ASC
        ";
        return $this->con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->con->prepare("
            SELECT fc.*, f.libelle_filiere, c.libelle_cycle 
            FROM filiere_cycles fc
            LEFT JOIN filieres f ON fc.filiere_code = f.code_filiere
            LEFT JOIN cycles c ON fc.cycle_code = c.code_cycle
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

        $fkErr = ForeignKeyValidator::validate($this->con, 'filiere_cycles', $data);
        if ($fkErr !== null) {
            $this->lastError = $fkErr;
            error_log('[ModelFiliereCycle::create] FK Error: ' . $fkErr);
            return false;
        }

        $cols = array_keys($data);
        $sql = "INSERT INTO filiere_cycles (" . implode(', ', array_map(function($c){ return "`$c`"; }, $cols)) . ") 
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
        $sql = "UPDATE filiere_cycles SET " . implode(', ', $sets) . " WHERE id_filiere_cycle = ?";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleStatus($id)
    {
        $item = $this->getById($id);
        if (!$item) return false;
        $newStatus = ($item['statut_filiere_cycle'] === 'actif') ? 'inactif' : 'actif';
        $stmt = $this->con->prepare("UPDATE filiere_cycles SET statut_filiere_cycle = ? WHERE id_filiere_cycle = ?");
        return $stmt->execute([$newStatus, $id]);
    }
}