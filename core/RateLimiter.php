<?php

/**
 * Service Centralisé de Limitation de Débit (Rate Limiting) — Persistant en Base de Données
 * Protège contre les attaques par force brute (connexion, mots de passe, tokens) et le spam (SMTP, API).
 */
class RateLimiter
{
    private static ?PDO $pdo = null;

    private static function getPdo(): ?PDO
    {
        if (self::$pdo === null) {
            try {
                self::$pdo = (new Database())->getCon();
            } catch (Exception $e) {
                error_log("RateLimiter::getPdo error: " . $e->getMessage());
                return null;
            }
        }
        return self::$pdo;
    }

    /**
     * Récupère l'adresse IP réelle du client (sanitisée, max 45 caractères)
     */
    public static function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
            $candidate = trim($parts[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                $ip = $candidate;
            }
        }
        return substr((string)$ip, 0, 45);
    }

    /**
     * Construit une clé unique déterministe <= 191 caractères
     */
    public static function buildKey(string $action, ?string $identifier = null, ?string $ip = null): string
    {
        $cleanAction = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower(trim($action)));
        $clientIp = $ip ?? self::getClientIp();
        $base = $cleanAction . ':' . $clientIp;
        if ($identifier !== null && trim($identifier) !== '') {
            $base .= ':' . sha1(mb_strtolower(trim($identifier)));
        }
        return substr($base, 0, 191);
    }

    /**
     * Formate un délai restant en secondes en texte français lisible
     */
    public static function formatRemainingTime(int $seconds): string
    {
        $seconds = max(1, $seconds);
        if ($seconds >= 60) {
            $minutes = (int)ceil($seconds / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }
        return $seconds . ' seconde' . ($seconds > 1 ? 's' : '');
    }

    /**
     * Vérifie si une action est actuellement bloquée sans incrémenter le compteur
     */
    public static function check(string $action, int $maxAttempts, int $windowSeconds, ?string $identifier = null): array
    {
        $db = self::getPdo();
        if (!$db) {
            return ['allowed' => true, 'attempts' => 0, 'remaining' => $maxAttempts, 'retry_after' => 0, 'locked_until' => null];
        }

        $key = self::buildKey($action, $identifier);
        $now = time();

        try {
            $stmt = $db->prepare("SELECT * FROM `" . TABLES::RATE_LIMITS . "` WHERE `rate_key` = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return ['allowed' => true, 'attempts' => 0, 'remaining' => $maxAttempts, 'retry_after' => 0, 'locked_until' => null];
            }

            // 1. Vérifier un verrou actif
            if (!empty($row['locked_until'])) {
                $lockedTs = strtotime($row['locked_until']);
                if ($lockedTs !== false && $lockedTs > $now) {
                    return [
                        'allowed'      => false,
                        'attempts'     => (int)$row['attempts'],
                        'remaining'    => 0,
                        'retry_after'  => $lockedTs - $now,
                        'locked_until' => $row['locked_until'],
                    ];
                }
            }

            // 2. Vérifier si la fenêtre courante a expiré
            $windowStartTs = strtotime($row['window_start']);
            if ($windowStartTs === false || ($now - $windowStartTs) >= $windowSeconds) {
                return ['allowed' => true, 'attempts' => 0, 'remaining' => $maxAttempts, 'retry_after' => 0, 'locked_until' => null];
            }

            $attempts = (int)$row['attempts'];
            if ($attempts >= $maxAttempts) {
                $retryAfter = max(1, ($windowStartTs + $windowSeconds) - $now);
                return [
                    'allowed'      => false,
                    'attempts'     => $attempts,
                    'remaining'    => 0,
                    'retry_after'  => $retryAfter,
                    'locked_until' => date('Y-m-d H:i:s', $now + $retryAfter),
                ];
            }

            return [
                'allowed'      => true,
                'attempts'     => $attempts,
                'remaining'    => max(0, $maxAttempts - $attempts),
                'retry_after'  => 0,
                'locked_until' => null,
            ];
        } catch (Exception $e) {
            error_log("RateLimiter::check error: " . $e->getMessage());
            return ['allowed' => true, 'attempts' => 0, 'remaining' => $maxAttempts, 'retry_after' => 0, 'locked_until' => null];
        }
    }

    /**
     * Enregistre une tentative (ex: échec de connexion ou appel protégé) et applique le blocage si le seuil est atteint
     */
    public static function hit(string $action, int $maxAttempts, int $windowSeconds, ?string $identifier = null, ?int $lockSeconds = null): array
    {
        $db = self::getPdo();
        if (!$db) {
            return ['allowed' => true, 'attempts' => 1, 'remaining' => max(0, $maxAttempts - 1), 'retry_after' => 0, 'locked_until' => null];
        }

        $ip = self::getClientIp();
        $key = self::buildKey($action, $identifier, $ip);
        $now = time();
        $nowStr = date('Y-m-d H:i:s', $now);
        $lockDuration = ($lockSeconds !== null && $lockSeconds > 0) ? $lockSeconds : $windowSeconds;

        try {
            // Nettoyage probabiliste léger (2%) des enregistrements expirés depuis > 24h
            if (mt_rand(1, 50) === 1) {
                self::purgeExpired(86400);
            }

            $stmt = $db->prepare("SELECT * FROM `" . TABLES::RATE_LIMITS . "` WHERE `rate_key` = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $attempts = 1;
                $lockedUntil = ($attempts >= $maxAttempts) ? date('Y-m-d H:i:s', $now + $lockDuration) : null;
                $stmtIns = $db->prepare("
                    INSERT INTO `" . TABLES::RATE_LIMITS . "` (`rate_key`, `action_name`, `ip_address`, `attempts`, `window_start`, `locked_until`, `updated_at`)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtIns->execute([$key, substr($action, 0, 64), $ip, $attempts, $nowStr, $lockedUntil, $nowStr]);

                return [
                    'allowed'      => true,
                    'attempts'     => $attempts,
                    'remaining'    => max(0, $maxAttempts - $attempts),
                    'retry_after'  => $lockedUntil ? $lockDuration : 0,
                    'locked_until' => $lockedUntil,
                ];
            }

            $windowStartTs = strtotime($row['window_start']);
            $lockedTs = !empty($row['locked_until']) ? strtotime($row['locked_until']) : false;

            // Si un verrou est toujours actif
            if ($lockedTs !== false && $lockedTs > $now) {
                return [
                    'allowed'      => false,
                    'attempts'     => (int)$row['attempts'],
                    'remaining'    => 0,
                    'retry_after'  => $lockedTs - $now,
                    'locked_until' => $row['locked_until'],
                ];
            }

            // Si la fenêtre est expirée (ou verrou expiré), repartir à 1
            if ($windowStartTs === false || ($now - $windowStartTs) >= $windowSeconds || ($lockedTs !== false && $lockedTs <= $now)) {
                $attempts = 1;
                $lockedUntil = ($attempts >= $maxAttempts) ? date('Y-m-d H:i:s', $now + $lockDuration) : null;
                $stmtUpd = $db->prepare("
                    UPDATE `" . TABLES::RATE_LIMITS . "`
                    SET `attempts` = ?, `window_start` = ?, `locked_until` = ?, `updated_at` = ?
                    WHERE `id_rate_limit` = ?
                ");
                $stmtUpd->execute([$attempts, $nowStr, $lockedUntil, $nowStr, (int)$row['id_rate_limit']]);

                return [
                    'allowed'      => true,
                    'attempts'     => $attempts,
                    'remaining'    => max(0, $maxAttempts - $attempts),
                    'retry_after'  => $lockedUntil ? $lockDuration : 0,
                    'locked_until' => $lockedUntil,
                ];
            }

            // Incrément dans la fenêtre active
            $attempts = (int)$row['attempts'] + 1;
            $lockedUntil = ($attempts >= $maxAttempts) ? date('Y-m-d H:i:s', $now + $lockDuration) : null;
            $stmtUpd = $db->prepare("
                UPDATE `" . TABLES::RATE_LIMITS . "`
                SET `attempts` = ?, `locked_until` = ?, `updated_at` = ?
                WHERE `id_rate_limit` = ?
            ");
            $stmtUpd->execute([$attempts, $lockedUntil, $nowStr, (int)$row['id_rate_limit']]);

            return [
                'allowed'      => ($attempts <= $maxAttempts),
                'attempts'     => $attempts,
                'remaining'    => max(0, $maxAttempts - $attempts),
                'retry_after'  => $lockedUntil ? $lockDuration : 0,
                'locked_until' => $lockedUntil,
            ];
        } catch (Exception $e) {
            error_log("RateLimiter::hit error: " . $e->getMessage());
            return ['allowed' => true, 'attempts' => 1, 'remaining' => max(0, $maxAttempts - 1), 'retry_after' => 0, 'locked_until' => null];
        }
    }

    /**
     * Vérifie ET consomme un jeton de tentative en une opération (idéal pour mot de passe oublié / APIs)
     */
    public static function attempt(string $action, int $maxAttempts, int $windowSeconds, ?string $identifier = null, ?int $lockSeconds = null): array
    {
        $status = self::check($action, $maxAttempts, $windowSeconds, $identifier);
        if (!$status['allowed']) {
            return $status;
        }
        return self::hit($action, $maxAttempts, $windowSeconds, $identifier, $lockSeconds);
    }

    /**
     * Réinitialise le compteur pour une action / identifiant donné (ex: après une connexion réussie)
     */
    public static function clear(string $action, ?string $identifier = null): void
    {
        $db = self::getPdo();
        if (!$db) return;

        try {
            $key = self::buildKey($action, $identifier);
            $stmt = $db->prepare("DELETE FROM `" . TABLES::RATE_LIMITS . "` WHERE `rate_key` = ?");
            $stmt->execute([$key]);
        } catch (Exception $e) {
            error_log("RateLimiter::clear error: " . $e->getMessage());
        }
    }

    /**
     * Purge les anciennes entrées expirées pour maintenir la table légère
     */
    public static function purgeExpired(int $olderThanSeconds = 86400): void
    {
        $db = self::getPdo();
        if (!$db) return;

        try {
            $cutoff = date('Y-m-d H:i:s', time() - max(60, $olderThanSeconds));
            $stmt = $db->prepare("
                DELETE FROM `" . TABLES::RATE_LIMITS . "`
                WHERE `updated_at` < ? AND (`locked_until` IS NULL OR `locked_until` < NOW())
            ");
            $stmt->execute([$cutoff]);
        } catch (Exception $e) {
            error_log("RateLimiter::purgeExpired error: " . $e->getMessage());
        }
    }
}
