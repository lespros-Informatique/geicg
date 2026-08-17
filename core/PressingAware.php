<?php

trait PressingAware
{
    protected function getCurrentPressingCode(): ?string
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        if ($this->isLivreur()) {
            return null;
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if ($userCode === '') {
            return null;
        }

        try {
            $sql = "SELECT pressing_code FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ? AND statut_user_pressing = 'actif' LIMIT 1";
            $stmt = (new Database())->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            $pressingCode = $stmt->fetchColumn();
            return $pressingCode ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    protected function requirePressingAccess(string $pressingCode): void
    {
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if ($userCode === '') {
            $this->json(['status' => 0, 'message' => 'Authentification requise'], 401);
        }

        if ($this->isSuperAdmin()) {
            return;
        }

        $current = $this->getCurrentPressingCode();
        if ($current === null || $current !== $pressingCode) {
            $this->json(['status' => 0, 'message' => 'Accès refusé à ce pressing'], 403);
        }
    }

    protected function filterByPressing(array $rows, string $pressingField, ?string $pressingCode): array
    {
        if ($pressingCode === null) {
            return $rows;
        }
        return array_values(array_filter($rows, function ($row) use ($pressingField, $pressingCode) {
            return ($row[$pressingField] ?? '') === $pressingCode;
        }));
    }
}
