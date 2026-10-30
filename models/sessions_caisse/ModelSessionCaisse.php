<?php

class ModelSessionCaisse extends BaseModel
{
    protected string $table = TABLES::SESSIONS_CAISSE;
    protected string $primaryKey = 'id_session';

    /**
     * Récupère toutes les sessions avec les informations du caissier
     */
    public function getAll(?string $anneeCode = null): array
    {
        $params = [];
        $sql = "
            SELECT s.*, 
                   CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) as caissier_nom,
                   u.email_user as caissier_email,
                   u.telephone_user as caissier_contact,
                   CONCAT(COALESCE(uv.nom_user, ''), ' ', COALESCE(uv.prenom_user, '')) as superviseur_nom
            FROM " . TABLES::SESSIONS_CAISSE . " s
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = s.user_code
            LEFT JOIN " . TABLES::USERS . " uv ON uv.code_user = s.user_validation
        ";
        if (!empty($anneeCode)) {
            $sql .= " WHERE s.annee_code = ? ";
            $params[] = $anneeCode;
        }
        $sql .= " ORDER BY s.date_session DESC, s.heure_ouverture DESC ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all sessions_caisse: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère une session par son ID avec détails
     */
    public function getById($id): array
    {
        $sql = "
            SELECT s.*, 
                   CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) as caissier_nom,
                   u.email_user as caissier_email,
                   u.telephone_user as caissier_contact,
                   CONCAT(COALESCE(uv.nom_user, ''), ' ', COALESCE(uv.prenom_user, '')) as superviseur_nom
            FROM " . TABLES::SESSIONS_CAISSE . " s
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = s.user_code
            LEFT JOIN " . TABLES::USERS . " uv ON uv.code_user = s.user_validation
            WHERE s.id_session = ?
            LIMIT 1
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get by id sessions_caisse: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère la session active (ouverte) pour aujourd'hui ou une date donnée
     */
    public function getActiveSession(?string $date = null)
    {
        if (!$date) $date = date('Y-m-d');
        $stmt = $this->getCon()->prepare("
            SELECT s.*, 
                   CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) as caissier_nom
            FROM " . TABLES::SESSIONS_CAISSE . " s
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = s.user_code
            WHERE s.date_session = ? AND s.statut_session = 'ouverte'
            ORDER BY s.id_session DESC LIMIT 1
        ");
        $stmt->execute([$date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Calcule en temps réel les totaux encaissés par mode de paiement pour une session ou date
     */
    public function getDailyFinancialTotals(string $date, ?string $sessionCode = null)
    {
        if (!empty($sessionCode)) {
            $stmt = $this->getCon()->prepare("
                SELECT mode_paiement, SUM(montant_paiement) as sum_mode, COUNT(*) as count_mode
                FROM " . TABLES::PAIEMENTS . "
                WHERE (session_caisse_code = ? OR (session_caisse_code IS NULL AND DATE(date_paiement) = ?))
                  AND (canal_paiement = 'guichet' OR canal_paiement IS NULL)
                  AND statut_paiement != '" . STATUTS::ANNULE . "'
                GROUP BY mode_paiement
            ");
            $stmt->execute([$sessionCode, $date]);
        } else {
            $stmt = $this->getCon()->prepare("
                SELECT mode_paiement, SUM(montant_paiement) as sum_mode, COUNT(*) as count_mode
                FROM " . TABLES::PAIEMENTS . "
                WHERE DATE(date_paiement) = ? AND (canal_paiement = 'guichet' OR canal_paiement IS NULL) AND statut_paiement != '" . STATUTS::ANNULE . "'
                GROUP BY mode_paiement
            ");
            $stmt->execute([$date]);
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalEspeces = 0;
        $totalMobileMoney = 0;
        $totalChequeVirement = 0;
        $nbEncaissements = 0;

        foreach ($rows as $r) {
            $mode = strtolower($r['mode_paiement'] ?? '');
            $sum = (float)($r['sum_mode'] ?? 0);
            $cnt = (int)($r['count_mode'] ?? 0);

            $nbEncaissements += $cnt;

            if ($mode === 'espece' || $mode === 'especes') {
                $totalEspeces += $sum;
            } elseif ($mode === 'mobile_money' || $mode === 'mobile' || $mode === 'wave' || $mode === 'orange' || $mode === 'mtn' || $mode === 'moov') {
                $totalMobileMoney += $sum;
            } else {
                $totalChequeVirement += $sum;
            }
        }

        $totalGeneral = $totalEspeces + $totalMobileMoney + $totalChequeVirement;

        return [
            'total_especes' => $totalEspeces,
            'total_mobile_money' => $totalMobileMoney,
            'total_cheque_virement' => $totalChequeVirement,
            'total_general' => $totalGeneral,
            'nb_encaissements' => $nbEncaissements
        ];
    }

    /**
     * Récupère les statistiques globales des sessions
     */
    public function getStatistics(?string $anneeCode = null): array
    {
        $db = $this->getCon();
        $whereAnnee = "";
        $params = [];
        if (!empty($anneeCode)) {
            $whereAnnee = " WHERE annee_code = ? ";
            $params[] = $anneeCode;
        }

        $stmtTot = $db->prepare("SELECT COUNT(*) FROM " . TABLES::SESSIONS_CAISSE . $whereAnnee);
        $stmtTot->execute($params);
        $totalSessions = (int)($stmtTot->fetchColumn() ?: 0);

        $whereOuv = empty($whereAnnee) ? " WHERE statut_session = 'ouverte'" : $whereAnnee . " AND statut_session = 'ouverte'";
        $stmtOuv = $db->prepare("SELECT COUNT(*) FROM " . TABLES::SESSIONS_CAISSE . $whereOuv);
        $stmtOuv->execute($params);
        $totalOuvertes = (int)($stmtOuv->fetchColumn() ?: 0);

        $whereClo = empty($whereAnnee) ? " WHERE statut_session IN ('cloturee', 'valide')" : $whereAnnee . " AND statut_session IN ('cloturee', 'valide')";
        $stmtClo = $db->prepare("SELECT COUNT(*) FROM " . TABLES::SESSIONS_CAISSE . $whereClo);
        $stmtClo->execute($params);
        $totalCloturees = (int)($stmtClo->fetchColumn() ?: 0);

        $whereSum = empty($whereAnnee) ? "" : $whereAnnee;
        $stmtSum = $db->prepare("SELECT COALESCE(SUM(total_general), 0) FROM " . TABLES::SESSIONS_CAISSE . $whereSum);
        $stmtSum->execute($params);
        $totalCumulSessions = (float)($stmtSum->fetchColumn() ?: 0);

        return [
            'totalSessions' => $totalSessions,
            'totalOuvertes' => $totalOuvertes,
            'totalCloturees' => $totalCloturees,
            'totalCumulSessions' => $totalCumulSessions
        ];
    }

    /**
     * Récupère la dernière session enregistrée
     */
    public function getLatestSession(): ?array
    {
        $stmt = $this->getCon()->prepare("SELECT * FROM " . TABLES::SESSIONS_CAISSE . " ORDER BY date_session DESC, id_session DESC LIMIT 1");
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Récupère les transactions de paiement associées à une session
     */
    public function getSessionTransactions(string $codeSession, string $dateSession): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT p.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, c.libelle_classe, ts.libelle_tranche
            FROM " . TABLES::PAIEMENTS . " p
            LEFT JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = p.inscription_code
            LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
            LEFT JOIN " . TABLES::TRANCHES_SCOLARITE . " ts ON ts.code_tranche = p.tranche_code
            WHERE (p.session_caisse_code = ? OR (p.session_caisse_code IS NULL AND DATE(p.date_paiement) = ?))
              AND (p.canal_paiement = 'guichet' OR p.canal_paiement IS NULL)
            ORDER BY p.id_paiement DESC
        ");
        $stmt->execute([$codeSession, $dateSession]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
