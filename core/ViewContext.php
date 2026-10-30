<?php

/**
 * Classe centralisée de contexte d'affichage (Vues & Includes)
 * Évite la redondance d'initialisation de variables et de requêtes SQL dans les vues et includes.
 */
class ViewContext
{
    private static array $scripts = [];

    /**
     * Valeurs par défaut injectées dans TOUTES les vues rendues par BaseController::loadView()
     * (évite les initialisations redondantes `$x = $x ?? [];` en tête de chaque vue).
     * Les données transmises par le contrôleur sont toujours prioritaires.
     */
    public static function defaults(): array
    {
        return [
            // Listes de référentiels
            'annees'               => [],
            'classes'              => [],
            'filieres'             => [],
            'cycles'               => [],
            'semestres'            => [],
            'etudiants'            => [],
            'paiements'            => [],
            'salles'               => [],
            'item'                 => [],
            'classeInfo'           => [],
            'pvRows'               => [],
            'statsClasse'          => [],
            'totalPaye'            => 0,

            // Filtres & sélections
            'selectedAnneeCode'    => (string)Session::get('annee_active_code', ''),
            'selectedClasseCode'   => '',
            'selectedMatiereCode'  => '',
            'selectedSemestreCode' => '',

            // Contexte caisse
            'isCaisseOuverte'      => false,
            'activeSession'        => null,
            'encryptedSessionId'   => '',
            'canOpenCaisse'        => false,
            'canCloseCaisse'       => false,
        ];
    }

    /**
     * Enregistre les scripts JS modulaires de la vue courante
     */
    public static function setScripts(array $scripts): void
    {
        self::$scripts = $scripts;
    }

    /**
     * Récupère les scripts JS enregistrés
     */
    public static function getScripts(): array
    {
        return self::$scripts;
    }

    /**
     * Résout les URLs versionnées des scripts JS pour footer-link.php
     */
    public static function resolveScripts($pageScripts = []): array
    {
        $list = !empty(self::$scripts) ? self::$scripts : (array)$pageScripts;
        $urls = [];
        $rootDir = dirname(__DIR__);
        foreach ($list as $script) {
            $scriptClean = ltrim((string)$script, '/');
            $physicalFile = $rootDir . '/' . $scriptClean;
            $v = file_exists($physicalFile) ? filemtime($physicalFile) : time();
            $urls[] = RACINE . htmlspecialchars($scriptClean, ENT_QUOTES, 'UTF-8') . '?v=' . $v;
        }
        self::$scripts = [];
        return $urls;
    }

    /**
     * Contexte global pour public/inc/header.php
     */
    public static function headerData(): array
    {
        $currentUserCode = Auth::code() ?? '';
        $currentRoleCode = Auth::role() ?? '';
        $currentRoles = [];
        $isSuperAdmin = false;
        $userRoles = [];

        if ($currentUserCode !== '') {
            try {
                $userRoles = Auth::roles();
                if (empty($userRoles) && class_exists('ModelUser')) {
                    $userRoles = (new ModelUser())->getUserRoleCodes($currentUserCode);
                }
                $currentRoles = $userRoles;
                $isSuperAdmin = in_array(ROLES::SUPER_ADMIN, $currentRoles, true) || in_array(ROLES::DIR_GENERAL, $currentRoles, true);
            } catch (\Throwable $e) {
                $currentRoles = [];
            }
        }

        $currentUserName  = Auth::user('nom_user') ?? (Auth::user('nom') ?? (Auth::user('login_user') ?? 'Utilisateur'));
        $currentUserEmail = Auth::user('email_user') ?? (Auth::user('email') ?? '');
        $currentUserPhoto = !empty(Auth::user('photo_user'))
            ? RACINE . 'public/assets/images/users/' . Auth::user('photo_user')
            : 'https://ui-avatars.com/api/?name=' . urlencode($currentUserName) . '&background=1E3A5F&color=fff';

        return [
            'currentUserCode'   => $currentUserCode,
            'currentRoleCode'   => $currentRoleCode,
            'currentRoles'      => $currentRoles,
            'userRoles'         => $userRoles,
            'isSuperAdmin'      => $isSuperAdmin,
            'currentUserName'   => $currentUserName,
            'currentUserEmail'  => $currentUserEmail,
            'currentUserPhoto'  => $currentUserPhoto,
            'recentAdminNotifs' => [],
            'unreadNotifsCount' => 0,
        ];
    }

    /**
     * Contexte global pour public/inc/sidbar.php
     */
    public static function sidebarData(array $scope = []): array
    {
        $etabCfg = Session::get('etablissement_config') ?? [];
        $rawLogo = $etabCfg['logo_etablissement'] ?? Session::get('etablissement_logo', '');
        $globalEtablissementLogo = $scope['globalEtablissementLogo'] ?? (
            function_exists('getEtablissementLogoUrl')
                ? getEtablissementLogoUrl($rawLogo)
                : ((!empty($rawLogo)) ? ((strpos($rawLogo, 'http') === 0) ? $rawLogo : RACINE . ltrim($rawLogo, '/')) : '')
        );
        $globalEtablissementNom = $scope['globalEtablissementNom'] ?? (
            $etabCfg['libelle_etablissement'] ?? ($etabCfg['nom_etablissement'] ?? Session::get('etablissement_nom', 'GEICG'))
        );

        $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        $isSuperAdmin = Auth::isSuperAdmin();
        $permMap = array_fill_keys(Auth::permissions(), true);

        $canAccess = function ($requiredPerms = []) use ($permMap, $isSuperAdmin): bool {
            if ($isSuperAdmin) return true;
            foreach ((array)$requiredPerms as $perm) {
                if (isset($permMap[$perm])) return true;
            }
            return false;
        };

        $isActiveRoute = function ($patterns, $excludePatterns = []) use ($currentUri): string {
            if (!is_array($patterns)) $patterns = [$patterns];
            if (!is_array($excludePatterns)) $excludePatterns = [$excludePatterns];

            foreach ($excludePatterns as $ex) {
                if (!empty($ex) && strpos($currentUri, $ex) !== false) return '';
            }
            foreach ($patterns as $pattern) {
                if (!empty($pattern) && strpos($currentUri, $pattern) !== false) return 'active';
            }
            return '';
        };

        return [
            'etabCfg'                 => $etabCfg,
            'rawLogo'                 => $rawLogo,
            'globalEtablissementLogo' => $globalEtablissementLogo,
            'globalEtablissementNom'  => $globalEtablissementNom,
            'currentUri'              => $currentUri,
            'isSuperAdmin'            => $isSuperAdmin,
            'permMap'                 => $permMap,
            'canAccess'               => $canAccess,
            'isActiveRoute'           => $isActiveRoute,
        ];
    }

    /**
     * Contexte global pour public/inc/nav.php
     */
    public static function navData(): array
    {
        $activeAnneeCode = (string)Session::get('annee_active_code', '');
        $activeAnneeLibelle = (string)Session::get('annee_active_libelle', 'Aucune année');
        $dbNav = null;
        $allAnneesNav = [];

        try {
            $dbNav = (new Database())->getCon();
            $allAnneesNav = $dbNav ? $dbNav->query("SELECT * FROM " . TABLES::ANNEES . " ORDER BY id_annee DESC")->fetchAll(PDO::FETCH_ASSOC) : [];
            if (!empty($allAnneesNav)) {
                $foundNav = false;
                foreach ($allAnneesNav as $anNav) {
                    if ($anNav['code_annee'] === $activeAnneeCode) {
                        $foundNav = true;
                        $activeAnneeLibelle = $anNav['libelle_annee'];
                        break;
                    }
                }
                if (!$foundNav) {
                    foreach ($allAnneesNav as $anNav) {
                        if (($anNav['statut_annee'] ?? '') === STATUTS::ACTIF) {
                            $activeAnneeCode = $anNav['code_annee'];
                            $activeAnneeLibelle = $anNav['libelle_annee'];
                            Session::set('annee_active_code', $activeAnneeCode);
                            Session::set('annee_active_libelle', $activeAnneeLibelle);
                            $foundNav = true;
                            break;
                        }
                    }
                    if (!$foundNav) {
                        $activeAnneeCode = '';
                        $activeAnneeLibelle = 'Aucune';
                        Session::set('annee_active_code', '');
                        Session::set('annee_active_libelle', 'Aucune');
                    }
                }
            } else {
                $activeAnneeCode = '';
                $activeAnneeLibelle = 'Aucune année';
                Session::set('annee_active_code', '');
                Session::set('annee_active_libelle', 'Aucune année');
            }
        } catch (Exception $e) {
            $allAnneesNav = [];
        }

        $userCodeNav = Auth::code() ?? '';
        $userPermsNav = Auth::user('permissions') ?? [];
        $hasGlobalViewNav = in_array('*', $userPermsNav, true) || in_array(PERMISSIONS::VIEW_ANNEES_ANTERIEURES, $userPermsNav, true);

        $userAccesNav = [];
        if (!$hasGlobalViewNav && !empty($userCodeNav) && $dbNav) {
            try {
                $stNav = $dbNav->prepare("SELECT annee_code, niveau_acces FROM " . TABLES::USER_ANNEE_ACCES . " WHERE user_code = ?");
                $stNav->execute([$userCodeNav]);
                foreach ($stNav->fetchAll(PDO::FETCH_ASSOC) as $rNav) {
                    $userAccesNav[$rNav['annee_code']] = $rNav['niveau_acces'];
                }
            } catch (Exception $e) {}
        }

        foreach ($allAnneesNav as &$anItem) {
            $isCurrentActiveDb = (($anItem['statut_annee'] ?? '') === STATUTS::ACTIF);
            $isSelected = ($anItem['code_annee'] === $activeAnneeCode || $anItem['libelle_annee'] === $activeAnneeLibelle);
            $canAccessThisYear = $isCurrentActiveDb || $hasGlobalViewNav || isset($userAccesNav[$anItem['code_annee']]);
            $accessLevelThisYear = $isCurrentActiveDb ? 'actif' : ($userAccesNav[$anItem['code_annee']] ?? ($hasGlobalViewNav ? 'global' : 'none'));

            $anItem['_isCurrentActiveDb']   = $isCurrentActiveDb;
            $anItem['_isSelected']          = $isSelected;
            $anItem['_canAccessThisYear']   = $canAccessThisYear;
            $anItem['_accessLevelThisYear'] = $accessLevelThisYear;
        }
        unset($anItem);

        $navRoles = Auth::roles();
        if (empty($navRoles) && !empty(Auth::role())) {
            $navRoles = [Auth::role()];
        }

        $isPastAnneeNav = false;
        $isReadOnlyAnneeNav = false;

        if (!empty($allAnneesNav)) {
            foreach ($allAnneesNav as $aObjNav) {
                if ($aObjNav['code_annee'] === $activeAnneeCode) {
                    if (($aObjNav['statut_annee'] ?? '') !== STATUTS::ACTIF) {
                        $isPastAnneeNav = true;
                    }
                    break;
                }
            }
        }

        if ($isPastAnneeNav) {
            $hasEditPermBanner = in_array('*', $userPermsNav, true) || in_array(PERMISSIONS::EDIT_ANNEES_ANTERIEURES, $userPermsNav, true);
            if (!$hasEditPermBanner && !empty($userCodeNav) && $dbNav) {
                try {
                    $stChk = $dbNav->prepare("SELECT niveau_acces FROM " . TABLES::USER_ANNEE_ACCES . " WHERE user_code = ? AND annee_code = ? LIMIT 1");
                    $stChk->execute([$userCodeNav, $activeAnneeCode]);
                    $lvlNav = $stChk->fetchColumn();
                    if ($lvlNav !== 'ecriture') {
                        $isReadOnlyAnneeNav = true;
                    }
                } catch (Exception $e) {
                    $isReadOnlyAnneeNav = true;
                }
            } elseif (!$hasEditPermBanner) {
                $isReadOnlyAnneeNav = true;
            }
        }

        return [
            'activeAnneeCode'    => $activeAnneeCode,
            'activeAnneeLibelle' => $activeAnneeLibelle,
            'allAnneesNav'       => $allAnneesNav,
            'hasGlobalViewNav'   => $hasGlobalViewNav,
            'userAccesNav'       => $userAccesNav,
            'navRoles'           => $navRoles,
            'isPastAnneeNav'     => $isPastAnneeNav,
            'isReadOnlyAnneeNav' => $isReadOnlyAnneeNav,
        ];
    }
}

