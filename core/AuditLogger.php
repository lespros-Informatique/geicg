<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/Auth.php';

/**
 * Service de journalisation des actions utilisateurs (Audit Trail)
 * Projet GEICG
 */
class AuditLogger
{
    public const ACTION_LOGIN      = 'CONNEXION';
    public const ACTION_LOGOUT     = 'DECONNEXION';
    public const ACTION_CREATE     = 'AJOUT';
    public const ACTION_UPDATE     = 'MODIFICATION';
    public const ACTION_DELETE     = 'SUPPRESSION';
    public const ACTION_PRINT      = 'IMPRESSION';
    public const ACTION_VIEW       = 'CONSULTATION';

    /**
     * Enregistre une action dans la table de log
     *
     * @param string $action Ex: CONNEXION, AJOUT, MODIFICATION, SUPPRESSION, IMPRESSION
     * @param string $module Ex: user, enseignant, note, bulletin, inscription, scolarite
     * @param string|null $entiteId Clé ou code de l'enregistrement concerné
     * @param string $description Description lisible de l'action
     * @param array|null $details Données contextuelles ou état avant/après (converti en JSON)
     * @param int|null $userId ID utilisateur forcé (si non connecté ou session indisponible)
     * @param string|null $userCode Code utilisateur forcé
     * @param string|null $userNom Nom complet forcé
     * @return bool
     */
    public static function log(
        string $action,
        string $module,
        ?string $entiteId = null,
        string $description = '',
        ?array $details = null,
        ?int $userId = null,
        ?string $userCode = null,
        ?string $userNom = null
    ): bool {
        try {
            $uId = $userId ?? Auth::id();
            $uCode = $userCode ?? Auth::code();
            $uNom = $userNom ?? (Auth::check() ? Auth::fullName() : 'Système');

            $ip = self::getClientIp();
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu', 0, 255);
            $detailsJson = !empty($details) ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) : null;

            $db = (new Database())->getCon();
            $sql = "INSERT INTO " . TABLES::LOGS_ACTIVITES . " 
                    (user_id, user_code, user_nom, action, module, entite_id, description, details_json, ip_address, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $db->prepare($sql);
            return $stmt->execute([
                $uId,
                $uCode,
                $uNom,
                strtoupper(trim($action)),
                strtolower(trim($module)),
                $entiteId,
                $description,
                $detailsJson,
                $ip,
                $userAgent
            ]);
        } catch (Throwable $e) {
            error_log("AuditLogger Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log d'une connexion réussie
     */
    public static function logLogin(int $userId, string $userCode, string $userName, string $email): bool
    {
        return self::log(
            self::ACTION_LOGIN,
            'authentification',
            (string)$userId,
            "Connexion réussie au système ($email)",
            ['email' => $email],
            $userId,
            $userCode,
            $userName
        );
    }

    /**
     * Log d'une déconnexion
     */
    public static function logLogout(): bool
    {
        return self::log(
            self::ACTION_LOGOUT,
            'authentification',
            (string)Auth::id(),
            "Déconnexion du système de " . Auth::fullName()
        );
    }

    /**
     * Log d'une création
     */
    public static function logCreate(string $module, ?string $entiteId, string $description, ?array $details = null): bool
    {
        return self::log(self::ACTION_CREATE, $module, $entiteId, $description, $details);
    }

    /**
     * Log d'une modification
     */
    public static function logUpdate(string $module, ?string $entiteId, string $description, ?array $details = null): bool
    {
        return self::log(self::ACTION_UPDATE, $module, $entiteId, $description, $details);
    }

    /**
     * Log d'une suppression
     */
    public static function logDelete(string $module, ?string $entiteId, string $description, ?array $details = null): bool
    {
        return self::log(self::ACTION_DELETE, $module, $entiteId, $description, $details);
    }

    /**
     * Log d'une impression ou exportation de document
     */
    public static function logPrint(string $module, ?string $entiteId, string $description, ?array $details = null): bool
    {
        return self::log(self::ACTION_PRINT, $module, $entiteId, $description, $details);
    }

    /**
     * Récupère l'adresse IP du client
     */
    private static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}

