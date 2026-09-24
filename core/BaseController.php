<?php

abstract class BaseController
{
    protected $validator;
    protected $model;

    public function __construct()
    {
        $this->validator = new Validator();
        $this->model = $this->resolveModel();
    }

    abstract protected function resolveModel();

    /**
     * Récupère le code de l'année académique active en session (avec initialisation auto)
     */
    protected function getActiveAnneeCode(): string
    {
        $db = (new Database())->getCon();
        $code = $_SESSION['annee_active_code'] ?? '';

        // Vérifier si l'année en session existe toujours en base
        if (!empty($code)) {
            $stmtCheck = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtCheck->execute([$code]);
            $exists = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$exists) {
                unset($_SESSION['annee_active_code']);
                unset($_SESSION['annee_active_libelle']);
                $code = '';
            } else {
                $_SESSION['annee_active_libelle'] = $exists['libelle_annee'];
            }
        }

        if (empty($_SESSION['annee_active_code'])) {
            try {
                $stmt = $db->query("SELECT code_annee, libelle_annee FROM annees WHERE statut_annee = 'actif' ORDER BY id_annee DESC LIMIT 1");
                $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
                if (!$row) {
                    $stmtFallback = $db->query("SELECT code_annee, libelle_annee FROM annees ORDER BY id_annee DESC LIMIT 1");
                    $row = $stmtFallback ? $stmtFallback->fetch(PDO::FETCH_ASSOC) : null;
                }
                if ($row) {
                    $_SESSION['annee_active_code'] = $row['code_annee'];
                    $_SESSION['annee_active_libelle'] = $row['libelle_annee'];
                } else {
                    $_SESSION['annee_active_code'] = '';
                    $_SESSION['annee_active_libelle'] = 'Aucune';
                }
            } catch (Exception $e) {
                $_SESSION['annee_active_code'] = '';
                $_SESSION['annee_active_libelle'] = 'Aucune';
            }
        }
        return $_SESSION['annee_active_code'] ?? '';
    }

    /**
     * Récupère le libellé de l'année académique active en session
     */
    protected function getActiveAnneeLibelle(): string
    {
        $this->getActiveAnneeCode();
        return $_SESSION['annee_active_libelle'] ?? 'Aucune année';
    }

    /**
     * Récupère dynamiquement le code de l'établissement actif en base
     */
    protected function getActiveEtablissementCode(): string
    {
        if (!empty($_SESSION['etablissement_active_code'])) {
            return $_SESSION['etablissement_active_code'];
        }
        try {
            $db = (new Database())->getCon();
            $stmt = $db->query("SELECT code_etablissement FROM etablissements WHERE statut_etablissement = 'actif' ORDER BY id_etablissement DESC LIMIT 1");
            $code = $stmt ? $stmt->fetchColumn() : null;
            if (!$code) {
                $stmtFb = $db->query("SELECT code_etablissement FROM etablissements ORDER BY id_etablissement DESC LIMIT 1");
                $code = $stmtFb ? $stmtFb->fetchColumn() : null;
            }
            if ($code) {
                $_SESSION['etablissement_active_code'] = (string)$code;
                return (string)$code;
            }
        } catch (Exception $e) {}
        return '';
    }

    /**
     * Récupère la ligne complète de l'année active
     */
    protected function getActiveAnnee(): array
    {
        $code = $this->getActiveAnneeCode();
        try {
            $db = (new Database())->getCon();
            $stmt = $db->prepare("SELECT * FROM annees WHERE code_annee = ? LIMIT 1");
            $stmt->execute([$code]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Vérifie si l'utilisateur courant peut consulter l'année spécifiée (ou l'année en session)
     */
    protected function canViewAnnee(?string $anneeCode = null): bool
    {
        $anneeCode = $anneeCode ?: $this->getActiveAnneeCode();
        if (empty($anneeCode)) return true;

        $activeAnnee = $this->getActiveAnnee();
        if (!empty($activeAnnee['code_annee']) && $activeAnnee['code_annee'] === $anneeCode && ($activeAnnee['statut_annee'] ?? '') === 'actif') {
            return true;
        }

        if ($this->hasPermission('VIEW_ANNEES_ANTERIEURES') || $this->hasPermission('*')) {
            return true;
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if (empty($userCode)) return false;

        try {
            $db = (new Database())->getCon();
            $stmt = $db->prepare("SELECT niveau_acces FROM user_annee_acces WHERE user_code = ? AND annee_code = ? LIMIT 1");
            $stmt->execute([$userCode, $anneeCode]);
            $level = $stmt->fetchColumn();
            return !empty($level) && in_array($level, ['lecture', 'ecriture'], true);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Vérifie si l'utilisateur courant peut modifier/saisir sur l'année spécifiée (ou l'année en session)
     */
    protected function canEditAnnee(?string $anneeCode = null): bool
    {
        $anneeCode = $anneeCode ?: $this->getActiveAnneeCode();
        if (empty($anneeCode)) return true;

        $activeAnnee = $this->getActiveAnnee();
        if (!empty($activeAnnee['code_annee']) && $activeAnnee['code_annee'] === $anneeCode && ($activeAnnee['statut_annee'] ?? '') === 'actif') {
            return true;
        }

        if ($this->hasPermission('EDIT_ANNEES_ANTERIEURES') || $this->hasPermission('*')) {
            return true;
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if (empty($userCode)) return false;

        try {
            $db = (new Database())->getCon();
            $stmt = $db->prepare("SELECT niveau_acces FROM user_annee_acces WHERE user_code = ? AND annee_code = ? LIMIT 1");
            $stmt->execute([$userCode, $anneeCode]);
            $level = $stmt->fetchColumn();
            return $level === 'ecriture';
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Détermine si la session actuelle est sur une année antérieure en Mode Lecture Seule
     */
    protected function isAnneeReadOnly(?string $anneeCode = null): bool
    {
        $anneeCode = $anneeCode ?: $this->getActiveAnneeCode();
        $activeAnnee = $this->getActiveAnnee();

        if (!empty($activeAnnee['code_annee']) && $activeAnnee['code_annee'] === $anneeCode && ($activeAnnee['statut_annee'] ?? '') === 'actif') {
            return false;
        }

        return !$this->canEditAnnee($anneeCode);
    }

    /**
     * Récupère les années académiques accessibles par l'utilisateur courant (identique à la navbar)
     */
    protected function getAccessibleAnnees(): array
    {
        return (new ModelAnnee())->getAccessibleAnnees();
    }

    protected function requirePost(bool $checkCsrf = true): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjax()) {
                $this->json(['status' => 0, 'message' => 'Méthode non autorisée (POST requis)'], 405);
            } else {
                $this->renderForbidden('Cette page accepte uniquement les requêtes POST.');
            }
        }

        if ($checkCsrf && !Validator::validateCsrfToken($_POST['csrf_token'] ?? null)) {
            if ($this->isAjax()) {
                $this->json(['status' => 0, 'message' => 'Session ou jeton de sécurité expiré. Veuillez réactualiser la page.'], 419);
            } else {
                $this->renderError('Jeton de sécurité CSRF expiré ou invalide. Veuillez rafraîchir la page et réessayer.', 419);
            }
        }
    }

    protected function requireAuth(): void
    {
        if (!isset($_SESSION[USERS_AUTH]['id_user']) && !isset($_SESSION[USERS_AUTH]['code_user'])) {
            if ($this->isAjax()) {
                $this->json(['status' => 0, 'message' => 'Authentification requise pour cette opération'], 401);
            } else {
                header('Location: ' . RACINE . 'user/connexion');
                exit();
            }
        }
    }

    /**
     * Affiche la page complète d'erreur 403 (Accès Refusé / Privilèges Insuffisants)
     */
    public function renderForbidden(string $message = "Vous ne disposez pas des autorisations nécessaires pour accéder à cette page ou exécuter cette action.", string $permissionCode = ''): void
    {
        if ($this->isAjax()) {
            $this->json(['status' => 0, 'message' => $message, 'required_permission' => $permissionCode], 403);
        } else {
            if (!headers_sent()) {
                http_response_code(403);
            }
            require __DIR__ . '/../views/errors/403.php';
            exit();
        }
    }

    /**
     * Affiche la page complète d'erreur 404 (Ressource Introuvable)
     */
    public function renderNotFound(string $message = "La page, l'enregistrement ou la ressource demandée n'existe pas."): void
    {
        if ($this->isAjax()) {
            $this->json(['status' => 0, 'message' => $message], 404);
        } else {
            if (!headers_sent()) {
                http_response_code(404);
            }
            require __DIR__ . '/../views/errors/404.php';
            exit();
        }
    }

    /**
     * Affiche la page complète d'erreur 500 (Incident Système)
     */
    public function renderError(string $message = "Une anomalie est survenue lors de l'exécution de la requête.", int $code = 500): void
    {
        if ($this->isAjax()) {
            $this->json(['status' => 0, 'message' => $message], $code);
        } else {
            if (!headers_sent()) {
                http_response_code($code);
            }
            require __DIR__ . '/../views/errors/500.php';
            exit();
        }
    }

    protected function unsetSession(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            @session_destroy();
        }
    }

    protected function csrfField(): string
    {
        return Validator::csrfField();
    }

    protected function json(array $data, int $code = 200): void
    {
        if (ob_get_length()) {
            @ob_clean();
        }
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data);
        exit;
    }

    protected function isAjax(): bool
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return true;
        }
        $uri = strtolower($_SERVER['REQUEST_URI'] ?? '');
        if (strpos($uri, '/api') !== false || strpos($uri, 'apilist') !== false || strpos($uri, 'savestatut') !== false || strpos($uri, 'getrecepissedata') !== false || strpos($uri, 'uploadphoto') !== false || strpos($uri, 'prisedevue') !== false || strpos($uri, '/add') !== false || strpos($uri, '/edit') !== false || strpos($uri, '/changer') !== false || strpos($uri, 'getstudentprofilesummary') !== false || strpos($uri, 'gettuitionbyclass') !== false) {
            return true;
        }
        return false;
    }

    protected function success(string $message, $extra = []): void
    {
        $extraData = is_array($extra) ? $extra : ['redirect' => (string)$extra];
        if ($this->isAjax()) {
            $this->json(array_merge(['status' => 1, 'message' => $message], $extraData));
        } else {
            $_SESSION['flash_success'] = $message;
            $redirect = $extraData['redirect'] ?? ($extraData['url'] ?? ($_SERVER['HTTP_REFERER'] ?? RACINE));
            header('Location: ' . $redirect);
            exit;
        }
    }

    protected function error(string $message, $code = 200): void
    {
        $redirect = null;
        $httpCode = 200;
        if (is_string($code)) {
            $redirect = $code;
        } elseif (is_int($code)) {
            $httpCode = $code;
        } elseif (is_array($code)) {
            $redirect = $code['redirect'] ?? ($code['url'] ?? null);
            $httpCode = $code['code'] ?? 200;
        }

        if ($this->isAjax()) {
            $this->json(['status' => 0, 'message' => $message], $httpCode);
        } else {
            $_SESSION['flash_error'] = $message;
            $target = $redirect ?? ($_SERVER['HTTP_REFERER'] ?? RACINE);
            header('Location: ' . $target);
            exit;
        }
    }

    protected function checkUnique(string $table, string $field, $value, string $label, ?string $idField = null, $idVal = null): bool
    {
        $val = trim((string)$value);
        if ($val === '') return true;

        $exists = false;
        if ($idField && !empty($idVal)) {
            $exists = $this->validator->_verif($table, $field, $val, $idField, $idVal);
        } else {
            $exists = $this->validator->verif($table, $field, $val);
        }

        if ($exists) {
            $this->error("Ce $label ($val) est déjà utilisé dans le système !");
            return false;
        }
        return true;
    }

    protected function checkUniquePair(string $table, array $conditions, string $label, ?string $idField = null, $idVal = null): bool
    {
        if (empty($conditions)) return true;

        $pdo = ($this->model && method_exists($this->model, 'getCon')) ? $this->model->getCon() : (new Database())->getCon();
        $where = [];
        $params = [];

        foreach ($conditions as $col => $val) {
            $where[] = "`$col` = ?";
            $params[] = trim((string)$val);
        }

        $sql = "SELECT COUNT(*) FROM `$table` WHERE " . implode(' AND ', $where);
        if ($idField && !empty($idVal)) {
            $sql .= " AND `$idField` != ?";
            $params[] = $idVal;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $count = (int)$stmt->fetchColumn();

        if ($count > 0) {
            $this->error("Ce $label existe déjà pour cette association !");
            return false;
        }
        return true;
    }

    protected function generateCode(string $table, string $field, string $prefix, int $len): string
    {
        return $this->validator->generateCode($table, $field, $prefix, $len);
    }

    /**
     * Récupère la configuration complète de l'établissement (avec mise en cache session)
     */
    protected function getEtablissementConfig(): array
    {
        if (isset($_SESSION['etablissement_config']) && is_array($_SESSION['etablissement_config'])) {
            return $_SESSION['etablissement_config'];
        }
        try {
            $db = (new Database())->getCon();
            $stmt = $db->query("SELECT * FROM etablissements WHERE statut_etablissement = 'actif' ORDER BY id_etablissement DESC LIMIT 1");
            $config = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : [];
            if (!$config) {
                $stmtFb = $db->query("SELECT * FROM etablissements ORDER BY id_etablissement DESC LIMIT 1");
                $config = $stmtFb ? $stmtFb->fetch(PDO::FETCH_ASSOC) : [];
            }
            $_SESSION['etablissement_config'] = $config ?: [];
            return $_SESSION['etablissement_config'];
        } catch (Exception $e) {
            return [];
        }
    }

    protected function loadView(string $path, array $data = []): void
    {
        $etabCfg = $this->getEtablissementConfig();
        $data['etablissementConfig'] = $etabCfg;
        $data['useSlugFiliere'] = !empty($etabCfg['use_slug_filiere']);
        $data['useSlugCycle'] = !empty($etabCfg['use_slug_cycle']);
        $data['useSlugNiveau'] = !empty($etabCfg['use_slug_niveau']);

        $data['isSuperAdmin'] = $this->isSuperAdmin();
        $data['currentUserName'] = $data['currentUserName'] ?? ($_SESSION[USERS_AUTH]['nom'] ?? ($_SESSION[USERS_AUTH]['nom_user'] ?? 'Utilisateur'));
        $data['currentUserEmail'] = $data['currentUserEmail'] ?? ($_SESSION[USERS_AUTH]['email'] ?? ($_SESSION[USERS_AUTH]['email_user'] ?? ''));
        $data['currentUserRole'] = $_SESSION[USERS_AUTH]['role_code'] ?? 'ROLE_USER';

        if (!file_exists($path)) {
            $candidate = __DIR__ . '/../' . ltrim(str_replace('../', '', $path), '/\\');
            if (file_exists($candidate)) {
                $path = $candidate;
            }
        }
        foreach ($data as $key => $value) {
            $$key = $value;
        }
        require $path;
    }

    protected function post(string $key, $default = '')
    {
        return $_POST[$key] ?? $default;
    }

    protected function validateRequired(array $fields): array
    {
        $result = [];
        foreach ($fields as $field => $label) {
            $value = trim($this->post($field));
            if ($value === '') {
                $result[$field] = "$label est requis";
            }
        }
        return $result;
    }

    /**
     * Nettoie automatiquement les champs téléphoniques pour retirer +225 / 225
     */
    protected function cleanPhoneFields(array &$data): void
    {
        $phoneKeys = [
            'telephone', 'telephone_user', 'telephone_etudiant', 'telephone_enseignant',
            'telephone_pere', 'telephone_mere', 'telephone_tuteur', 'telephone_etablissement',
            'telephone_etablissement2', 'telephone_client', 'telephone_livreur', 'contact', 'contact_urgence'
        ];

        foreach ($phoneKeys as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = Validator::cleanPhone($data[$key]);
            }
        }
    }

    protected function redirect(string $url): void
    {
        if (!headers_sent()) {
            header('Location: ' . $url);
        } else {
            echo "<script>window.location.href=" . json_encode($url) . ";</script>";
        }
        exit;
    }

    protected function getCurrentUserCode(): ?string
    {
        return $_SESSION[USERS_AUTH]['code_user'] ?? null;
    }

    protected function getCurrentUserRoles(): array
    {
        $roles = $_SESSION[USERS_AUTH]['roles'] ?? [];
        if (empty($roles)) {
            $roleCode = $_SESSION[USERS_AUTH]['role_code'] ?? ($_SESSION['role_code'] ?? '');
            if ($roleCode !== '') {
                $roles = [$roleCode];
            }
        }
        if (is_string($roles)) {
            $roles = [$roles];
        }
        return array_values(array_unique(array_filter($roles)));
    }

    protected function hasRole(string $roleCode): bool
    {
        return in_array($roleCode, $this->getCurrentUserRoles(), true);
    }

    protected function hasAnyRole(array $roleCodes): bool
    {
        return !empty(array_intersect($this->getCurrentUserRoles(), $roleCodes));
    }

    protected function isSuperAdmin(): bool
    {
        return $this->hasAnyRole(['ROLE_SUPERADMIN', 'ROLE_DIR_GENERAL']);
    }

    /**
     * Exige que l'utilisateur connecté possède le rôle Super Admin ou Direction Générale
     */
    protected function requireSuperAdmin(string $customMessage = ''): void
    {
        $this->requireAuth();
        if (!$this->isSuperAdmin()) {
            $msg = !empty($customMessage) ? $customMessage : "Cette action est réservée exclusivement à la Direction Générale et à l'Administration Système.";
            $this->renderForbidden($msg, 'ROLE_SUPERADMIN');
        }
    }

    /**
     * Récupère la liste des permissions attribuées au rôle de l'utilisateur connecté
     */
    protected function getUserPermissions(): array
    {
        $roles = $this->getCurrentUserRoles();
        if (empty($roles)) {
            return [];
        }

        try {
            $pdo = ($this->model && method_exists($this->model, 'getCon')) ? $this->model->getCon() : (new Database())->getCon();
            $inClause = implode(',', array_fill(0, count($roles), '?'));
            $sql = "
                SELECT DISTINCT rp.permission_code 
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_code = p.code_permission
                WHERE rp.role_code IN ($inClause)
                  AND p.statut_permission = 'actif'
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($roles);
            $perms = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            
            // Si le rôle possède le passe-partout '*'
            if (in_array('*', $perms, true)) {
                return ['*'];
            }
            return $perms;
        } catch (Exception $e) {
            error_log("Error fetching user permissions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si l'utilisateur possède au moins une des permissions métier spécifiées (string ou array)
     */
    protected function hasPermission(string|array $permissionCode): bool
    {
        $perms = $this->getUserPermissions();
        if (in_array('*', $perms, true)) {
            return true;
        }
        if (is_array($permissionCode)) {
            foreach ($permissionCode as $p) {
                if (in_array($p, $perms, true)) {
                    return true;
                }
            }
            return false;
        }
        return in_array($permissionCode, $perms, true);
    }

    /**
     * Vérifie les droits d'action unitaire CRUD (create, edit, show, delete)
     */
    protected function hasActionPermission(string $action): bool
    {
        $perms = $_SESSION[USERS_AUTH]['permissions'] ?? [];
        if (!empty($perms[$action])) {
            return true;
        }
        return false;
    }

    /**
     * Bloque la requête avec une page complète 403 si l'utilisateur ne possède pas au moins l'une des permissions requises
     */
    protected function requirePermission(string|array $permissionCode, string $customMessage = ''): void
    {
        $this->requireAuth();

        if (!$this->hasPermission($permissionCode)) {
            $codeDisplay = is_array($permissionCode) ? implode(' / ', $permissionCode) : $permissionCode;
            $msg = !empty($customMessage) ? $customMessage : "Accès refusé : vous ne possédez pas le privilège [{$codeDisplay}] requis pour accéder à cette section.";
            $this->renderForbidden($msg, $codeDisplay);
        }
    }

    /**
     * Alias de requirePermission pour vérifier plusieurs permissions alternatives
     */
    protected function requireAnyPermission(array $permissions, string $customMessage = ''): void
    {
        $this->requirePermission($permissions, $customMessage);
    }


    /**
     * Bloque la requête avec une page complète 403 si l'action CRUD n'est pas autorisée
     */
    protected function requireAction(string $action, string $customMessage = ''): void
    {
        $this->requireAuth();

        if (!$this->hasActionPermission($action)) {
            $actionLabels = [
                'create' => 'la création de nouveaux enregistrements',
                'edit' => 'la modification d\'enregistrements',
                'show' => 'la consultation de ces données',
                'delete' => 'la suppression d\'enregistrements'
            ];
            $lbl = $actionLabels[$action] ?? $action;
            $msg = !empty($customMessage) ? $customMessage : "Votre compte ne vous autorise pas {$lbl}.";
            $this->renderForbidden($msg, strtoupper($action) . '_PRIVILEGE');
        }
    }

    /**
     * Valide les clés étrangères d'un tableau de données pour une table et déclenche $this->error() si absente ou invalide.
     */
    protected function validateForeignKeys(array $data, ?string $table = null, array $requiredKeys = []): bool
    {
        $db = ($this->model && method_exists($this->model, 'getCon')) ? $this->model->getCon() : (new Database())->getCon();

        // 1. Vérifier les clés supplémentaires explicitement requises
        foreach ($requiredKeys as $key => $label) {
            $colName = is_numeric($key) ? $label : $key;
            $lbl = is_numeric($key) ? $label : $label;
            $val = $data[$colName] ?? '';
            $err = ForeignKeyValidator::validateSingleKey($db, $colName, $val, is_numeric($key) ? null : $lbl);
            if ($err !== null) {
                $this->error($err);
                return false;
            }
        }

        // 2. Si une table est définie, lancer la validation globale
        if (!empty($table)) {
            $err = ForeignKeyValidator::validate($db, $table, $data);
            if ($err !== null) {
                $this->error($err);
                return false;
            }
        }

        return true;
    }
}
