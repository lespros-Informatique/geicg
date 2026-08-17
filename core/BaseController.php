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

        try {
            $sql = "SELECT role_code FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ? AND statut_user_pressing = 'actif'";
            $stmt = $this->model->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            return [];
        }
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
}
