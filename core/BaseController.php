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

    private function isSuperAdmin(string $userCode): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM " . TABLES::USERS_PRESSINGS . " ur
                    INNER JOIN " . TABLES::ROLES . " r ON ur.role_code = r.code_role
                    WHERE ur.user_code = ? AND r.code_role = 'ROLE-ADMIN'";
            $stmt = $this->model->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function getCurrentPressingCode(): ?string
    {
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if ($userCode === '') {
            return null;
        }

        if ($this->isSuperAdmin($userCode)) {
            return null;
        }

        try {
            $sql = "SELECT pressing_code FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ? AND statut_user_pressing = 'actif' LIMIT 1";
            $stmt = $this->model->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            $pressingCode = $stmt->fetchColumn();
            return $pressingCode ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    protected function requirePermission(string $module, string $action): void
    {
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';

        if ($userCode === '') {
            $this->json(['status' => 0, 'message' => 'Authentification requise'], 401);
        }

        if ($this->isSuperAdmin($userCode)) {
            return;
        }

        try {
            $sqlRoles = "SELECT role_code FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ? AND statut_user_pressing = 'actif'";
            $stmtRoles = $this->model->getCon()->prepare($sqlRoles);
            $stmtRoles->execute([$userCode]);
            $roleCodes = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);

            if (empty($roleCodes)) {
                $this->json(['status' => 0, 'message' => 'Accès refusé : aucun rôle assigné'], 403);
            }

            $placeholders = implode(',', array_fill(0, count($roleCodes), '?'));
            $sqlPerms = "SELECT p.libelle_permission
                         FROM " . TABLES::PERMISSIONS . " p
                         INNER JOIN " . TABLES::ROLES_PERMISSIONS . " rp ON p.code_permission = rp.permission_code
                         INNER JOIN " . TABLES::ROLES . " r ON rp.role_code = r.code_role
                         WHERE p.statut_permission = 'actif'
                         AND r.statut_role = 'actif'
                         AND r.code_role IN ($placeholders)";
            $stmtPerms = $this->model->getCon()->prepare($sqlPerms);
            $stmtPerms->execute($roleCodes);
            $perms = $stmtPerms->fetchAll(PDO::FETCH_COLUMN);

            $hasAccess = false;
            foreach ($perms as $perm) {
                if (strpos($perm, $module . '_' . $action) !== false || strpos($perm, $module . '_' . $action) === 0) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                $this->json([
                    'status' => 0,
                    'message' => "Accès refusé : permission '$module/$action' manquante"
                ], 403);
            }
        } catch (Exception $e) {
            $this->json(['status' => 0, 'message' => 'Erreur de vérification des permissions'], 500);
        }
    }
}
