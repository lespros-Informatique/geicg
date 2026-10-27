<?php

/**
 * Classe Session - Gestionnaire sécurisé et centralisé de sessions PHP
 * Projet GEICG
 */
class Session
{
    /**
     * Démarre la session si elle n'est pas déjà active
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Récupère une valeur de session avec valeur par défaut optionnelle
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();

        // Support de notation par point pour les clés imbriquées (ex: 'users_auth.id_user')
        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $current = $_SESSION;
            foreach ($segments as $segment) {
                if (!is_array($current) || !array_key_exists($segment, $current)) {
                    return $default;
                }
                $current = $current[$segment];
            }
            return $current;
        }

        return $_SESSION[$key] ?? $default;
    }

    /**
     * Enregistre une valeur en session
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();

        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $current = &$_SESSION;
            foreach ($segments as $segment) {
                if (!isset($current[$segment]) || !is_array($current[$segment])) {
                    $current[$segment] = [];
                }
                $current = &$current[$segment];
            }
            $current = $value;
            return;
        }

        $_SESSION[$key] = $value;
    }

    /**
     * Vérifie si une clé existe en session et n'est pas vide/nulle
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Supprime une clé de session
     */
    public static function remove(string $key): void
    {
        self::start();

        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $current = &$_SESSION;
            $last = array_pop($segments);
            foreach ($segments as $segment) {
                if (!isset($current[$segment]) || !is_array($current[$segment])) {
                    return;
                }
                $current = &$current[$segment];
            }
            unset($current[$last]);
            return;
        }

        unset($_SESSION[$key]);
    }

    /**
     * Gestion des messages flash (consommés après lecture)
     */
    public static function flash(string $key, ?string $message = null): mixed
    {
        self::start();

        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        $val = $_SESSION['_flash'][$key] ?? null;
        if ($val !== null) {
            unset($_SESSION['_flash'][$key]);
        }
        return $val;
    }

    /**
     * Régénère l'identifiant de session (sécurité post-authentification)
     */
    public static function regenerate(bool $deleteOldSession = true): bool
    {
        self::start();
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Détruit complètement la session
     */
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }
}
