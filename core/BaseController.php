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
        if (isset($_SESSION[USERS_AUTH]['id_user'])) {
            session_destroy();
        }
    }

    protected function csrfField(): string
    {
        return Validator::csrfField();
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function success(string $message, array $extra = []): void
    {
        $this->json(array_merge(['status' => 1, 'message' => $message], $extra));
    }

    protected function error(string $message, int $code = 400): void
    {
        $this->json(['status' => 0, 'message' => $message], $code);
    }

    protected function generateCode(string $table, string $field, string $prefix, int $len): string
    {
        return $this->validator->generateCode($table, $field, $prefix, $len);
    }

    protected function loadView(string $path, array $data = []): void
    {
        foreach ($data as $key => $value) {
            $$key = $value;
        }
        require_once $path;
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
        header('Location: ' . $url);
        exit;
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
        return $this->hasRole(ROLES::SUPER_ADMIN);
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

        if (!$this->isLivreur()) {
            return null;
        }

        try {
            $sql = "SELECT code_livreur FROM " . TABLES::LIVREURS . " WHERE user_code = ? AND statut_livreur = 'actif' LIMIT 1";
            $stmt = $this->model->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            $code = $stmt->fetchColumn();
            return $code ?: null;
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
