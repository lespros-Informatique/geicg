<?php

require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/../config/const.php';

/**
 * Classe Auth - Gestionnaire d'authentification et d'identité utilisateur
 * Projet GEICG
 */
class Auth
{
    /**
     * Vérifie si un utilisateur est actuellement connecté
     */
    public static function check(): bool
    {
        return Session::has(USERS_AUTH) && !empty(Session::get(USERS_AUTH . '.id_user'));
    }

    /**
     * Récupère les données de l'utilisateur connecté (ou un champ spécifique)
     */
    public static function user(?string $field = null): mixed
    {
        $userData = Session::get(USERS_AUTH, []);
        if ($field === null) {
            return $userData;
        }
        return $userData[$field] ?? null;
    }

    /**
     * Récupère l'ID de l'utilisateur connecté
     */
    public static function id(): ?int
    {
        $id = self::user('id_user');
        return $id !== null ? (int)$id : null;
    }

    /**
     * Récupère le code unique de l'utilisateur connecté
     */
    public static function code(): ?string
    {
        return self::user('code_user');
    }

    /**
     * Récupère le nom complet (Prénom + Nom) de l'utilisateur connecté
     */
    public static function fullName(): string
    {
        $prenom = self::user('prenom_user') ?? self::user('prenom') ?? '';
        $nom = self::user('nom_user') ?? self::user('nom') ?? '';
        return trim("$prenom $nom");
    }

    /**
     * Récupère le rôle principal de l'utilisateur
     */
    public static function role(): ?string
    {
        return self::user('role_code') ?? (self::roles()[0] ?? null);
    }

    /**
     * Récupère l'ensemble des rôles attribués à l'utilisateur
     */
    public static function roles(): array
    {
        $roles = self::user('roles');
        if (is_array($roles) && !empty($roles)) {
            return $roles;
        }

        $singleRole = self::user('role_code');
        return !empty($singleRole) ? [(string)$singleRole] : [];
    }

    /**
     * Vérifie si l'utilisateur possède un rôle spécifique (ou au moins un rôle parmi la liste)
     */
    public static function hasRole(string|array $roles): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        $userRoles = self::roles();
        if (is_string($roles)) {
            return in_array($roles, $userRoles, true);
        }

        foreach ($roles as $r) {
            if (in_array($r, $userRoles, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifie si l'utilisateur connecté est Super Administrateur
     */
    public static function isSuperAdmin(): bool
    {
        if (!self::check()) {
            return false;
        }

        $perms = self::permissions();
        if (in_array('*', $perms, true)) {
            return true;
        }

        $roles = self::roles();
        return in_array(ROLES::SUPER_ADMIN, $roles, true)
            || in_array('superadmin', $roles, true)
            || in_array(ROLES::ALL_CONTROL, $roles, true);
    }

    /**
     * Récupère la liste des permissions actives de l'utilisateur
     */
    public static function permissions(): array
    {
        $perms = Session::get('permissions');
        if (is_array($perms)) {
            return $perms;
        }

        // Si absent de la session, chargement dynamique
        $roles = self::roles();
        if (empty($roles)) {
            return [];
        }

        try {
            $db = (new Database())->getCon();
            $inClause = implode(',', array_fill(0, count($roles), '?'));
            $stmt = $db->prepare("
                SELECT DISTINCT rp.permission_code 
                FROM " . TABLES::ROLE_PERMISSIONS . " rp
                JOIN " . TABLES::PERMISSIONS . " p ON rp.permission_code = p.code_permission
                WHERE rp.role_code IN ($inClause) AND p.statut_permission = 'actif'
            ");
            $stmt->execute($roles);
            $perms = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            Session::set('permissions', $perms);
            return $perms;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Vérifie si l'utilisateur possède une permission granulaire (ou au moins une parmi la liste)
     */
    public static function can(string|array $permissions): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        $userPerms = self::permissions();
        $permMap = array_fill_keys($userPerms, true);

        if (is_string($permissions)) {
            return isset($permMap[$permissions]);
        }

        foreach ($permissions as $perm) {
            if (isset($permMap[$perm])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Connecte un utilisateur et initialise ses données en session
     */
    public static function login(array $userData, array $roles = [], array $permissions = []): void
    {
        Session::start();
        Session::regenerate(true);

        // Si 2 arguments sont passés et que le 2e argument correspond à des permissions
        if (!empty($roles) && empty($permissions)) {
            if (!empty($userData['roles']) && !str_starts_with((string)($roles[0] ?? ''), 'ROLE_')) {
                $permissions = $roles;
                $roles = (array)$userData['roles'];
            }
        }

        if (empty($roles) && !empty($userData['roles'])) {
            $roles = (array)$userData['roles'];
        }

        if (!empty($roles)) {
            $userData['roles'] = $roles;
            if (empty($userData['role_code'])) {
                $userData['role_code'] = $roles[0];
            }
        }

        Session::set(USERS_AUTH, $userData);
        Session::set('roles', $userData['roles'] ?? []);

        if (!empty($permissions)) {
            Session::set('permissions', $permissions);
        }
    }

    /**
     * Déconnecte l'utilisateur en cours
     */
    public static function logout(): void
    {
        Session::remove(USERS_AUTH);
        Session::remove('permissions');
        Session::destroy();
    }
}
