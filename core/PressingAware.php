<?php

trait PressingAware
{
    protected function getCurrentPressingCode(): ?string
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        if ($this->isLivreur()) {
            return null;
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if ($userCode === '') {
            return null;
        }

        try {
            $sql = "SELECT pressing_code FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ? AND statut_user_pressing = 'actif' LIMIT 1";
            $stmt = (new Database())->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            $pressingCode = $stmt->fetchColumn();
            return $pressingCode ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    protected function requirePressingAccess(string $pressingCode): void
    {
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if ($userCode === '') {
            $this->json(['status' => 0, 'message' => 'Authentification requise'], 401);
        }

        if ($this->isSuperAdmin()) {
            return;
        }

        $current = $this->getCurrentPressingCode();
        if ($current === null || $current !== $pressingCode) {
            $this->json(['status' => 0, 'message' => 'Accès refusé à ce pressing'], 403);
        }
    }

    /**
     * Vérifie si un pressing dispose d'un abonnement B2B actif et non expiré
     */
    protected function hasActiveAbonnement(?string $pressingCode = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($pressingCode === null) {
            $pressingCode = $this->getCurrentPressingCode();
        }

        if (empty($pressingCode)) {
            return false;
        }

        try {
            $pdo = (new Database())->getCon();
            // Mettre à jour automatiquement les abonnements expirés
            $pdo->prepare("
                UPDATE " . TABLES::ABONNEMENTS_PRESSINGS . " 
                SET statut_abonnement_pressing = 'expire' 
                WHERE pressing_code = ? 
                  AND statut_abonnement_pressing = 'actif' 
                  AND date_fin_abonnement < CURDATE()
            ")->execute([$pressingCode]);

            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM " . TABLES::ABONNEMENTS_PRESSINGS . " 
                WHERE pressing_code = ? 
                  AND statut_abonnement_pressing = 'actif' 
                  AND date_debut_abonnement <= CURDATE() 
                  AND date_fin_abonnement >= CURDATE()
            ");
            $stmt->execute([$pressingCode]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (Exception $e) {
            error_log('[PressingAware::hasActiveAbonnement] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les détails de l'abonnement du pressing
     */
    protected function getActiveAbonnementDetails(?string $pressingCode = null): ?array
    {
        if ($pressingCode === null) {
            $pressingCode = $this->getCurrentPressingCode();
        }

        if (empty($pressingCode)) {
            return null;
        }

        try {
            $pdo = (new Database())->getCon();
            $stmt = $pdo->prepare("
                SELECT 
                    ap.*, 
                    COALESCE(f.libelle_forfait, 'Forfait Standard') as libelle_forfait, 
                    COALESCE(ap.montant_abonnement, f.montant_forfait, 0) as montant_reel,
                    DATEDIFF(ap.date_fin_abonnement, CURDATE()) as jours_restants
                FROM " . TABLES::ABONNEMENTS_PRESSINGS . " ap
                LEFT JOIN " . TABLES::FORFAITS . " f ON ap.forfait_code = f.code_forfait
                WHERE ap.pressing_code = ?
                ORDER BY ap.id_abonnement_pressing DESC
                LIMIT 1
            ");
            $stmt->execute([$pressingCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Bloque l'exécution si le pressing n'a pas d'abonnement actif
     */
    protected function requireActiveAbonnement(?string $pressingCode = null, string $actionLabel = ''): void
    {
        if ($this->isSuperAdmin()) {
            return;
        }

        if (!$this->hasActiveAbonnement($pressingCode)) {
            $label = !empty($actionLabel) ? " pour {$actionLabel}" : "";
            $message = "Action bloquée : votre pressing n'a pas d'abonnement actif ou votre abonnement a expiré. Veuillez souscrire à un forfait{$label}.";

            if ($_SERVER['REQUEST_METHOD'] === 'POST' || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
                $this->json(['status' => 0, 'message' => $message, 'require_subscription' => true], 403);
            } else {
                header('Location: ' . RACINE . 'abonnement/list?error=subscription_required');
                exit();
            }
        }
    }

    protected function filterByPressing(array $rows, string $pressingField, ?string $pressingCode): array
    {
        if ($pressingCode === null) {
            return $rows;
        }
        return array_values(array_filter($rows, function ($row) use ($pressingField, $pressingCode) {
            return ($row[$pressingField] ?? '') === $pressingCode;
        }));
    }
}
