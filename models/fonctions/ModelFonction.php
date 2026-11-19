<?php

class ModelFonction extends BaseModel
{
    protected string $table = TABLES::FONCTIONS;
    protected string $primaryKey = 'id_fonction';
    protected ?string $statusField = 'statut_fonction';
    protected ?string $createdAtField = 'created_at_fonction';

    public function getUsersByFonction(string $fonctionCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT u.*, r.libelle_role 
                FROM " . TABLES::USERS . " u
                LEFT JOIN " . TABLES::USER_ROLES . " ur ON ur.user_code = u.code_user
                LEFT JOIN " . TABLES::ROLES . " r ON r.code_role = ur.role_code
                WHERE u.fonction_code = ?
                ORDER BY u.nom_user ASC, u.prenom_user ASC
            ");
            $stmt->execute([$fonctionCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelFonction::getUsersByFonction error: " . $e->getMessage());
            return [];
        }
    }
}
