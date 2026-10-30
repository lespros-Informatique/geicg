<?php

class ModelDepense extends BaseModel
{
    protected string $table = TABLES::DEPENSES;
    protected string $primaryKey = 'id_depense';
    protected ?string $statusField = 'statut_depense';
    protected ?string $createdAtField = 'created_at_depense';

    public function getAll(?string $anneeCode = null, ?string $typeCode = null, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        $where = [];
        $params = [];
        if (!empty($anneeCode)) {
            $where[] = "d.annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($typeCode)) {
            $where[] = "d.type_depense_code = ?";
            $params[] = $typeCode;
        }
        if (!empty($dateDebut)) {
            $where[] = "DATE(d.periode_depense) >= ?";
            $params[] = $dateDebut;
        }
        if (!empty($dateFin)) {
            $where[] = "DATE(d.periode_depense) <= ?";
            $params[] = $dateFin;
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "
            SELECT d.*, 
                   t.libelle_type_depense, 
                   CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) as auteur_nom_complet,
                   CONCAT(COALESCE(uc.nom_user, ''), ' ', COALESCE(uc.prenom_user, '')) as confirmateur_nom_complet
            FROM " . TABLES::DEPENSES . " d
            LEFT JOIN " . TABLES::TYPE_DEPENSES . " t ON t.code_type_depense = d.type_depense_code
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = d.user_code
            LEFT JOIN " . TABLES::USERS . " uc ON uc.code_user = d.user_confirm
            {$whereSql}
            ORDER BY d.id_depense DESC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStats(?string $anneeCode = null, ?string $typeCode = null, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        $where = [];
        $params = [];
        if (!empty($anneeCode)) {
            $where[] = "annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($typeCode)) {
            $where[] = "type_depense_code = ?";
            $params[] = $typeCode;
        }
        if (!empty($dateDebut)) {
            $where[] = "DATE(periode_depense) >= ?";
            $params[] = $dateDebut;
        }
        if (!empty($dateFin)) {
            $where[] = "DATE(periode_depense) <= ?";
            $params[] = $dateFin;
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $andSql = !empty($where) ? "AND " . implode(" AND ", $where) : "";

        $db = $this->getCon();
        
        $stmtTot = $db->prepare("SELECT SUM(montant_depense) FROM " . TABLES::DEPENSES . " WHERE statut_depense != 'annule' " . $andSql);
        $stmtTot->execute($params);
        $totalMontant = (float)($stmtTot->fetchColumn() ?: 0);

        $stmtApp = $db->prepare("SELECT SUM(montant_depense) FROM " . TABLES::DEPENSES . " WHERE statut_depense = 'approuve' " . $andSql);
        $stmtApp->execute($params);
        $montantApprouve = (float)($stmtApp->fetchColumn() ?: 0);

        $stmtAtt = $db->prepare("SELECT SUM(montant_depense) FROM " . TABLES::DEPENSES . " WHERE statut_depense = 'en_attente' " . $andSql);
        $stmtAtt->execute($params);
        $montantEnAttente = (float)($stmtAtt->fetchColumn() ?: 0);

        $stmtCount = $db->prepare("SELECT COUNT(*) FROM " . TABLES::DEPENSES . " " . $whereSql);
        $stmtCount->execute($params);
        $totalCount = (int)($stmtCount->fetchColumn() ?: 0);

        $stmtCntAtt = $db->prepare("SELECT COUNT(*) FROM " . TABLES::DEPENSES . " WHERE statut_depense = 'en_attente' " . $andSql);
        $stmtCntAtt->execute($params);
        $countEnAttente = (int)($stmtCntAtt->fetchColumn() ?: 0);

        $stmtCntApp = $db->prepare("SELECT COUNT(*) FROM " . TABLES::DEPENSES . " WHERE statut_depense = 'approuve' " . $andSql);
        $stmtCntApp->execute($params);
        $countApprouve = (int)($stmtCntApp->fetchColumn() ?: 0);

        $stmtCntAnn = $db->prepare("SELECT COUNT(*) FROM " . TABLES::DEPENSES . " WHERE statut_depense = 'annule' " . $andSql);
        $stmtCntAnn->execute($params);
        $countAnnule = (int)($stmtCntAnn->fetchColumn() ?: 0);

        $stmtAnnSum = $db->prepare("SELECT SUM(montant_depense) FROM " . TABLES::DEPENSES . " WHERE statut_depense = 'annule' " . $andSql);
        $stmtAnnSum->execute($params);
        $montantAnnule = (float)($stmtAnnSum->fetchColumn() ?: 0);

        $moyenne = $totalCount > 0 ? round($totalMontant / $totalCount) : 0;
        $totalTypes = (int)$db->query("SELECT COUNT(*) FROM " . TABLES::TYPE_DEPENSES . "")->fetchColumn();

        return [
            'total_montant' => $totalMontant,
            'montant_approuve' => $montantApprouve,
            'montant_en_attente' => $montantEnAttente,
            'montant_annule' => $montantAnnule,
            'total_count' => $totalCount,
            'count_en_attente' => $countEnAttente,
            'count_approuve' => $countApprouve,
            'count_annule' => $countAnnule,
            'moyenne' => $moyenne,
            'total_types' => $totalTypes
        ];
    }

    public function updateStatusWithConfirm($id, string $statut, ?string $userCode = null): bool
    {
        $allowed = ['en_attente', 'approuve', 'annule'];
        if (!in_array($statut, $allowed, true)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE " . TABLES::DEPENSES . " SET statut_depense = ?, user_confirm = ?, created_at_confirm = ?, updated_at_depense = ? WHERE id_depense = ?";
        $stmt = $this->getCon()->prepare($sql);
        return $stmt->execute([$statut, $userCode, $now, $now, $id]);
    }

    /**
     * Récupère les détails complets d'une dépense
     */
    public function getDetailsById(int $id): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT d.*, 
                       td.libelle_type_depense, 
                       a.libelle_annee, 
                       CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) as auteur_nom_complet,
                       CONCAT(COALESCE(uc.nom_user, ''), ' ', COALESCE(uc.prenom_user, '')) as confirmateur_nom_complet
                FROM " . TABLES::DEPENSES . " d
                LEFT JOIN " . TABLES::TYPE_DEPENSES . " td ON td.code_type_depense = d.type_depense_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = d.annee_code
                LEFT JOIN " . TABLES::USERS . " u ON u.code_user = d.user_code
                LEFT JOIN " . TABLES::USERS . " uc ON uc.code_user = d.user_confirm
                WHERE d.id_depense = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelDepense getDetailsById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère une année par son code
     */
    public function getAnneeByCode(string $code): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT code_annee, libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
            $stmt->execute([$code]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }
}
