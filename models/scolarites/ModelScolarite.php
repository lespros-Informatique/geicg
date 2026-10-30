<?php

class ModelScolarite extends BaseModel
{
    protected string $table = TABLES::SCOLARITES;
    protected string $primaryKey = 'id_scolarite';
    protected ?string $statusField = 'statut_scolarite';
    protected ?string $createdAtField = 'created_at_scolarite';

    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null, ?string $filiereCode = null, ?string $affectationEtat = null, ?string $statut = null): array
    {
        $sql = "
            SELECT s.*, f.libelle_filiere, f.slug_filiere, n.libelle_niveau, n.slug_niveau, a.libelle_annee
            FROM " . TABLES::SCOLARITES . " s
            LEFT JOIN " . TABLES::FILIERES . " f ON s.filiere_code = f.code_filiere
            LEFT JOIN " . TABLES::NIVEAUX . " n ON s.niveau_code = n.code_niveau
            LEFT JOIN " . TABLES::ANNEES . " a ON s.annee_code = a.code_annee
        ";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "s.annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "s.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($filiereCode)) {
            $conditions[] = "s.filiere_code = ?";
            $params[] = $filiereCode;
        }
        if (!empty($affectationEtat)) {
            $conditions[] = "s.affectation_etat = ?";
            $params[] = $affectationEtat;
        }
        if (!empty($statut)) {
            $conditions[] = "s.statut_scolarite = ?";
            $params[] = $statut;
        }
        if (!empty($classeCode)) {
            $sql .= " LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ? ";
            $params[] = $classeCode;
            $conditions[] = "(s.niveau_code = cl.niveau_code AND (s.filiere_code = cl.filiere_code OR s.filiere_code IS NULL OR s.filiere_code = ''))";
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY f.libelle_filiere ASC, n.libelle_niveau ASC, s.affectation_etat ASC, s.id_scolarite DESC ";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById($id): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT s.*, f.libelle_filiere, n.libelle_niveau, a.libelle_annee
            FROM " . TABLES::SCOLARITES . " s
            LEFT JOIN " . TABLES::FILIERES . " f ON s.filiere_code = f.code_filiere
            LEFT JOIN " . TABLES::NIVEAUX . " n ON s.niveau_code = n.code_niveau
            LEFT JOIN " . TABLES::ANNEES . " a ON s.annee_code = a.code_annee
            WHERE s.id_scolarite = ? OR s.code_scolarite = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    public function getFormData(array $item = []): array
    {
        require_once __DIR__ . '/../annees/ModelAnnee.php';
        require_once __DIR__ . '/../cycles/ModelCycle.php';
        require_once __DIR__ . '/../filieres/ModelFiliere.php';
        require_once __DIR__ . '/../niveaux/ModelNiveau.php';

        $annees = (new ModelAnnee())->getAll();
        $cycles = (new ModelCycle())->getAll();
        $filieres = (new ModelFiliere())->getAll();
        $niveaux = (new ModelNiveau())->getAll();

        if (!empty($item['filiere_code'])) {
            $hasFil = false;
            foreach ($filieres as $f) {
                if ($f['code_filiere'] === $item['filiere_code']) { $hasFil = true; break; }
            }
            if (!$hasFil) {
                $extraFil = (new ModelFiliere())->getByCode($item['filiere_code']);
                if ($extraFil) $filieres[] = $extraFil;
            }
        }
        if (!empty($item['niveau_code'])) {
            $hasNiv = false;
            foreach ($niveaux as $n) {
                if ($n['code_niveau'] === $item['niveau_code']) { $hasNiv = true; break; }
            }
            if (!$hasNiv) {
                $extraNiv = (new ModelNiveau())->getByCode($item['niveau_code']);
                if ($extraNiv) $niveaux[] = $extraNiv;
            }
        }

        $filiereCyclesMap = $this->getCon()->query("
            SELECT fc.filiere_code, fc.cycle_code, fc.niveau_code, fc.type_filiere, f.type_filiere as f_type 
            FROM " . TABLES::FILIERE_CYCLES . " fc
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = fc.filiere_code
            WHERE (fc.statut_filiere_cycle = 'actif' OR fc.statut_filiere_cycle IS NULL)
        ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'annees' => $annees,
            'cycles' => $cycles,
            'filieres' => $filieres,
            'niveaux' => $niveaux,
            'filiereCyclesMap' => $filiereCyclesMap
        ];
    }

    public function getStats(?string $activeYear): array
    {
        $db = $this->getCon();
        $stmtSco = $db->prepare("
            SELECT 
                COUNT(*) AS total_scolarites,
                COALESCE(SUM(CASE WHEN affectation_etat = 'affecte' THEN 1 ELSE 0 END), 0) AS total_affectes,
                COALESCE(SUM(CASE WHEN affectation_etat = 'non_affecte' OR affectation_etat IS NULL OR affectation_etat = '' THEN 1 ELSE 0 END), 0) AS total_non_affectes
            FROM " . TABLES::SCOLARITES . "
            WHERE (annee_code = ? OR ? = '')
        ");
        $stmtSco->execute([$activeYear, $activeYear]);
        $scoRow = $stmtSco->fetch(PDO::FETCH_ASSOC) ?: [];
        $totalScolarites = (int)($scoRow['total_scolarites'] ?? 0);
        $totalAffectes = (int)($scoRow['total_affectes'] ?? 0);
        $totalNonAffectes = (int)($scoRow['total_non_affectes'] ?? 0);

        $stmtTr = $db->prepare("SELECT COUNT(*) FROM " . TABLES::TRANCHES_SCOLARITE . " WHERE (annee_code = ? OR ? = '')");
        $stmtTr->execute([$activeYear, $activeYear]);
        $totalTranches = (int)$stmtTr->fetchColumn();

        return [
            'totalScolarites' => $totalScolarites,
            'totalAffectes' => $totalAffectes,
            'totalNonAffectes' => $totalNonAffectes,
            'totalTranches' => $totalTranches
        ];
    }

    public function getTranches(string $scolariteCode, ?string $filiereCode = null, ?string $niveauCode = null, ?string $anneeCode = null): array
    {
        $db = $this->getCon();
        if (!empty($anneeCode)) {
            $sql = "SELECT * FROM " . TABLES::TRANCHES_SCOLARITE . " 
                    WHERE scolarite_code = ? OR ((scolarite_code IS NULL OR scolarite_code = '') AND filiere_code = ? AND niveau_code = ? AND annee_code = ?)
                    ORDER BY date_limite ASC, id_tranche ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$scolariteCode, $filiereCode, $niveauCode, $anneeCode]);
        } else {
            $sql = "SELECT * FROM " . TABLES::TRANCHES_SCOLARITE . " 
                    WHERE scolarite_code = ? OR ((scolarite_code IS NULL OR scolarite_code = '') AND filiere_code = ? AND niveau_code = ?)
                    ORDER BY date_limite ASC, id_tranche ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$scolariteCode, $filiereCode, $niveauCode]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getFilieresByCriteria(?string $typeFiliere = null, ?string $cycleCode = null): array
    {
        $sql = "SELECT f.code_filiere FROM " . TABLES::FILIERES . " f WHERE 1=1";
        $params = [];
        if (!empty($typeFiliere)) {
            $sql .= " AND f.type_filiere = ?";
            $params[] = $typeFiliere;
        }
        if (!empty($cycleCode)) {
            $sql .= " AND f.code_filiere IN (SELECT filiere_code FROM " . TABLES::FILIERE_CYCLES . " WHERE cycle_code = ?)";
            $params[] = $cycleCode;
        }
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function checkCombinaisonExists(string $anneeCode, string $filiereCode, string $niveauCode, string $affectationEtat, int $idExclude = 0): ?array
    {
        $sql = "SELECT s.id_scolarite, f.libelle_filiere, n.libelle_niveau 
                FROM " . TABLES::SCOLARITES . " s
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = s.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = s.niveau_code
                WHERE s.annee_code = ? AND s.filiere_code = ? AND s.niveau_code = ? AND s.affectation_etat = ?";
        $params = [$anneeCode, $filiereCode, $niveauCode, $affectationEtat];
        if ($idExclude > 0) {
            $sql .= " AND s.id_scolarite != ?";
            $params[] = $idExclude;
        }
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
