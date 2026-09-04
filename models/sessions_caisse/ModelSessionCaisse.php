<?php

class ModelSessionCaisse extends BaseModel
{
    protected string $table = 'sessions_caisse';
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
            FROM sessions_caisse s
            LEFT JOIN users u ON u.code_user = s.user_code
            LEFT JOIN users uv ON uv.code_user = s.user_validation
        ";
        if (!empty($anneeCode)) {
            $sql .= " WHERE (s.annee_code = ? OR s.annee_code IS NULL OR s.annee_code = '') ";
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
    public function getById(int $id): array
    {
        $sql = "
            SELECT s.*, 
                   CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) as caissier_nom,
                   u.email_user as caissier_email,
                   u.telephone_user as caissier_contact,
                   CONCAT(COALESCE(uv.nom_user, ''), ' ', COALESCE(uv.prenom_user, '')) as superviseur_nom
            FROM sessions_caisse s
            LEFT JOIN users u ON u.code_user = s.user_code
            LEFT JOIN users uv ON uv.code_user = s.user_validation
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
    public function getActiveSession(string $date = null)
    {
        if (!$date) $date = date('Y-m-d');
        $stmt = $this->getCon()->prepare("
            SELECT s.*, 
                   CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) as caissier_nom
            FROM sessions_caisse s
            LEFT JOIN users u ON u.code_user = s.user_code
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
                FROM paiements
                WHERE (session_caisse_code = ? OR (session_caisse_code IS NULL AND DATE(date_paiement) = ?))
                  AND statut_paiement != 'annule'
                GROUP BY mode_paiement
            ");
            $stmt->execute([$sessionCode, $date]);
        } else {
            $stmt = $this->getCon()->prepare("
                SELECT mode_paiement, SUM(montant_paiement) as sum_mode, COUNT(*) as count_mode
                FROM paiements
                WHERE DATE(date_paiement) = ? AND statut_paiement != 'annule'
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
}
