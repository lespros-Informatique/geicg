<?php

require_once __DIR__ . '/Session.php';

/**
 * Fonctions d'Aide Globales (Helpers) - DRY & Formatage Centralisé
 * Projet GEICG
 */

if (!function_exists('formatMoney')) {
    /**
     * Formate un montant en monnaie (par défaut FCFA)
     */
    function formatMoney(mixed $amount, string $currency = 'FCFA'): string
    {
        $numeric = is_numeric($amount) ? (float)$amount : 0.0;
        return number_format($numeric, 0, ',', ' ') . ' ' . $currency;
    }
}

if (!function_exists('formatDate')) {
    /**
     * Formate une date en format français (d/m/Y par défaut)
     */
    function formatDate(?string $date, string $format = 'd/m/Y'): string
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : '-';
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Formate une date et heure en format français
     */
    function formatDateTime(?string $dateTime, string $format = 'd/m/Y H:i'): string
    {
        return formatDate($dateTime, $format);
    }
}

if (!function_exists('statusBadge')) {
    /**
     * Génère un badge HTML standardisé selon le statut
     * Respecte rigoureusement la charte graphique et les classes Bootstrap existantes du projet.
     */
    function statusBadge(string $status, string $label = ''): string
    {
        $cleanStatus = strtolower(trim($status));
        $displayLabel = !empty($label) ? htmlspecialchars($label) : htmlspecialchars(ucfirst(str_replace('_', ' ', $cleanStatus)));

        $badgeConfig = match ($cleanStatus) {
            'valide', 'actif', 'solde', 'ouvert', 'programme', 'justifie' => [
                'bg' => '#ECFDF5',
                'color' => '#065F46',
                'border' => '#A7F3D0'
            ],
            'en_attente', 'en_cours', 'partiel' => [
                'bg' => '#FFFBEB',
                'color' => '#92400E',
                'border' => '#FDE68A'
            ],
            'rejete', 'inactif', 'ferme', 'archive', 'cloture' => [
                'bg' => '#F1F5F9',
                'color' => '#475569',
                'border' => '#CBD5E1'
            ],
            'annule', 'non_justifie', 'bloque' => [
                'bg' => '#FEF2F2',
                'color' => '#991B1B',
                'border' => '#FECACA'
            ],
            default => [
                'bg' => '#F8FAFC',
                'color' => '#334155',
                'border' => '#E2E8F0'
            ]
        };

        return sprintf(
            '<span style="background: %s; color: %s; border: 1px solid %s; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">%s</span>',
            $badgeConfig['bg'],
            $badgeConfig['color'],
            $badgeConfig['border'],
            $displayLabel
        );
    }
}

if (!function_exists('csrfToken')) {
    /**
     * Récupère ou génère le jeton CSRF de session
     */
    function csrfToken(): string
    {
        $token = Session::get('csrf_token');
        if (empty($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }
}

if (!function_exists('csrfField')) {
    /**
     * Génère le champ input caché CSRF pour les formulaires POST
     */
    function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('sanitize')) {
    /**
     * Échappe une chaîne de caractères pour un affichage HTML sécurisé
     */
    function sanitize(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('getEtablissementLogoUrl')) {
    /**
     * Résout proprement l'URL du logo d'établissement avec fallbacks sécurisés
     */
    function getEtablissementLogoUrl(?string $rawLogo = null): string
    {
        $baseDir = dirname(__DIR__);
        if (!empty($rawLogo)) {
            if (str_starts_with($rawLogo, 'http')) {
                return $rawLogo;
            }
            $cleanPath = ltrim($rawLogo, '/');
            if (file_exists($baseDir . '/' . $cleanPath)) {
                return RACINE . $cleanPath;
            }
            if (file_exists($baseDir . '/public/' . $cleanPath)) {
                return RACINE . 'public/' . $cleanPath;
            }
        }
        if (file_exists($baseDir . '/public/assets/images/logo/logo_eicg.jpg')) {
            return RACINE . 'public/assets/images/logo/logo_eicg.jpg';
        }
        if (file_exists($baseDir . '/public/uploads/logos/logo_1787358264.jpg')) {
            return RACINE . 'public/uploads/logos/logo_1787358264.jpg';
        }
        return '';
    }
}
