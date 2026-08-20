<?php

abstract class BaseController
{
    use PressingAware;

    protected $validator;
    protected $model;

    public function __construct()
    {
        $this->validator = new Validator();
        $this->model = $this->resolveModel();
    }

    abstract protected function resolveModel();

    protected function requirePost(bool $checkCsrf = true): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['status' => 0, 'message' => 'Methode non autorisee'], 405);
        }

        if ($checkCsrf && !Validator::validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->json(['status' => 0, 'message' => 'Token de securite invalide ou expire'], 419);
        }
    }

    protected function requireAuth(): void
    {
        if (!isset($_SESSION[USERS_AUTH]['id_user'])) {
            $this->json(['status' => 0, 'message' => 'Authentification requise'], 401);
        }
    }

    protected function unsetSession(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    protected function csrfField(): string
    {
        return Validator::csrfField();
    }

    protected function json(array $data, int $code = 200): void
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data);
        exit;
    }

    protected function success(string $message, array $extra = []): void
    {
        $this->json(array_merge(['status' => 1, 'message' => $message], $extra));
    }

    protected function error(string $message, int $code = 200): void
    {
        $this->json(['status' => 0, 'message' => $message], $code);
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

    protected function generateCode(string $table, string $field, string $prefix, int $len): string
    {
        return $this->validator->generateCode($table, $field, $prefix, $len);
    }

    protected function loadView(string $path, array $data = []): void
    {
        $isSuperAdmin = $data['isSuperAdmin'] ?? $this->isSuperAdmin();
        $isPressing = $data['isPressing'] ?? $this->isPressing();
        $isLivreur = $data['isLivreur'] ?? $this->isLivreur();
        $livreurCode = $data['livreurCode'] ?? ($isLivreur ? $this->getCurrentLivreurCode() : null);
        $pressingCode = $data['pressingCode'] ?? (($isPressing && !$isLivreur) ? $this->getCurrentPressingCode() : null);

        $data['isSuperAdmin'] = $isSuperAdmin;
        $data['isPressing'] = $isPressing;
        $data['isLivreur'] = $isLivreur;
        $data['livreurCode'] = $livreurCode;
        $data['pressingCode'] = $pressingCode;
        $data['currentUserName'] = $data['currentUserName'] ?? ($_SESSION[USERS_AUTH]['nom'] ?? ($_SESSION[USERS_AUTH]['nom_user'] ?? 'Utilisateur'));
        $data['currentUserEmail'] = $data['currentUserEmail'] ?? ($_SESSION[USERS_AUTH]['email'] ?? ($_SESSION[USERS_AUTH]['email_user'] ?? ''));

        // Vérification de l'état d'abonnement pour les pressings
        $isSubscriptionActive = true;
        $subscriptionDetails = null;
        if ($isPressing && !empty($pressingCode)) {
            $isSubscriptionActive = method_exists($this, 'hasActiveAbonnement') 
                ? $this->hasActiveAbonnement($pressingCode) 
                : true;
            $subscriptionDetails = method_exists($this, 'getActiveAbonnementDetails') 
                ? $this->getActiveAbonnementDetails($pressingCode) 
                : null;
        }
        $data['isSubscriptionActive'] = $data['isSubscriptionActive'] ?? $isSubscriptionActive;
        $data['subscriptionDetails'] = $data['subscriptionDetails'] ?? $subscriptionDetails;

        // Calcul dynamique et filtré des notifications pour le badge de la cloche et le dropdown d'aperçu
        try {
            $notifModel = new ModelNotification();
            $notifStats = $notifModel->getStats($pressingCode, $livreurCode);
            $data['unreadNotifsCount'] = $notifStats['non_lues'] ?? 0;
            $data['recentAdminNotifs'] = $notifModel->getAllWithClient($pressingCode, $livreurCode, 5);
        } catch (Exception $e) {
            $data['unreadNotifsCount'] = 0;
            $data['recentAdminNotifs'] = [];
        }

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
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if ($userCode === '') {
            return [];
        }

        $roles = [];
        try {
            $roleCode = $_SESSION[USERS_AUTH]['role_code'] ?? '';
            if ($roleCode === '') {
                $sql = "SELECT role_code FROM " . TABLES::USERS . " WHERE code_user = ? LIMIT 1";
                $pdo = ($this->model && method_exists($this->model, 'getCon')) ? $this->model->getCon() : (new Database())->getCon();
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userCode]);
                $roleCode = $stmt->fetchColumn() ?: '';
            }

            if ($roleCode !== '') {
                $roles[] = $roleCode;
            }
        } catch (Exception $e) {
            // ignore
        }

        return $roles;
    }

    protected function hasRole(string $roleCode): bool
    {
        return in_array($roleCode, $this->getCurrentUserRoles(), true);
    }

    protected function isSuperAdmin(): bool
    {
        return $this->hasRole(ROLES::SUPER_ADMIN) || $this->hasRole('ROLE-SUPER-ADMIN') || $this->hasRole('ROLE-ADMIN') || $this->hasRole('ADMIN');
    }

    protected function isPressing(): bool
    {
        return $this->hasRole(ROLES::PRESSING);
    }

    protected function isLivreur(): bool
    {
        return $this->hasRole(ROLES::LIVREUR);
    }

    protected function getCurrentLivreurCode(): ?string
    {
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if ($userCode === '') {
            return null;
        }

        if (!$this->isLivreur() && !$this->isSuperAdmin()) {
            return null;
        }

        try {
            $pdo = ($this->model && method_exists($this->model, 'getCon')) ? $this->model->getCon() : (new Database())->getCon();
            $sql = "SELECT code_livreur FROM " . TABLES::LIVREURS . " WHERE user_code = ? AND statut_livreur = 'actif' LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userCode]);
            $code = $stmt->fetchColumn();
            if ($code) {
                return $code;
            }

            // Fallback par téléphone ou nom
            $userTel = $_SESSION[USERS_AUTH]['tel'] ?? ($_SESSION[USERS_AUTH]['telephone_user'] ?? '');
            if ($userTel !== '') {
                $stmtTel = $pdo->prepare("SELECT code_livreur FROM " . TABLES::LIVREURS . " WHERE telephone_livreur = ? LIMIT 1");
                $stmtTel->execute([$userTel]);
                $codeTel = $stmtTel->fetchColumn();
                if ($codeTel) {
                    return $codeTel;
                }
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    protected function requireLivreurAccess(string $livreurCode): void
    {
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if ($userCode === '') {
            $this->json(['status' => 0, 'message' => 'Authentification requise'], 401);
        }

        if ($this->isSuperAdmin()) {
            return;
        }

        $current = $this->getCurrentLivreurCode();
        if ($current === null || $current !== $livreurCode) {
            $this->json(['status' => 0, 'message' => 'Accès refusé : vous n\'êtes pas assigné à ce livreur'], 403);
        }
    }

    /**
     * Récupère la liste des permissions attribuées au rôle de l'utilisateur connecté
     */
    protected function getUserPermissions(): array
    {
        if ($this->isSuperAdmin()) {
            return ['*'];
        }

        $roles = $this->getCurrentUserRoles();
        if (empty($roles)) {
            return [];
        }

        try {
            $pdo = ($this->model && method_exists($this->model, 'getCon')) ? $this->model->getCon() : (new Database())->getCon();
            $inClause = implode(',', array_fill(0, count($roles), '?'));
            $sql = "
                SELECT DISTINCT rp.permission_code 
                FROM " . TABLES::ROLES_PERMISSIONS . " rp
                JOIN " . TABLES::PERMISSIONS . " p ON rp.permission_code = p.code_permission
                WHERE rp.role_code IN ($inClause)
                  AND rp.statut_role_permission = 'actif'
                  AND p.statut_permission = 'actif'
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($roles);
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching user permissions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si l'utilisateur possède une permission donnée
     */
    protected function hasPermission(string $permissionCode): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $perms = $this->getUserPermissions();
        return in_array('*', $perms, true) || in_array($permissionCode, $perms, true);
    }

    /**
     * Bloque la requête avec une erreur 403 si l'utilisateur ne possède pas la permission requise
     */
    protected function requirePermission(string $permissionCode, string $customMessage = ''): void
    {
        $this->requireAuth();

        if (!$this->hasPermission($permissionCode)) {
            $msg = !empty($customMessage) ? $customMessage : "Accès refusé : privilège [{$permissionCode}] requis pour cette action.";
            if ($_SERVER['REQUEST_METHOD'] === 'POST' || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
                $this->json(['status' => 0, 'message' => $msg], 403);
            } else {
                header('Location: ' . RACINE . '?error=forbidden');
                exit();
            }
        }
    }
}
