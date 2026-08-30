<?php

class ModelEnseignant extends BaseModel
{
    protected string $table = 'enseignants';
    protected string $primaryKey = 'id_enseignant';
    protected ?string $statusField = 'statut_enseignant';
    protected ?string $createdAtField = 'created_at_enseignant';

    public function getAll(): array
    {
        $sql = "
            SELECT e.*,
                   u.nom_user,
                   u.prenom_user,
                   u.email_user,
                   u.telephone_user,
                   u.sexe_user,
                   u.photo_user,
                   u.matricule_user,
                   u.statut_user,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.email_user AS email_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.sexe_user AS sexe_enseignant
            FROM enseignants e
            JOIN users u ON u.code_user = e.code_enseignant
            ORDER BY e.id_enseignant DESC
        ";
        try {
            $stmt = $this->getCon()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getAll error: " . $e->getMessage());
            return [];
        }
    }

    public function getActifs(): array
    {
        $sql = "
            SELECT e.*,
                   u.nom_user,
                   u.prenom_user,
                   u.email_user,
                   u.telephone_user,
                   u.sexe_user,
                   u.photo_user,
                   u.matricule_user,
                   u.statut_user,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.email_user AS email_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.sexe_user AS sexe_enseignant
            FROM enseignants e
            JOIN users u ON u.code_user = e.code_enseignant
            WHERE e.statut_enseignant = 'actif' AND u.statut_user = 'actif'
            ORDER BY u.nom_user ASC, u.prenom_user ASC
        ";
        try {
            $stmt = $this->getCon()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getActifs error: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array
    {
        $sql = "
            SELECT e.*,
                   u.id_user,
                   u.nom_user,
                   u.prenom_user,
                   u.email_user,
                   u.telephone_user,
                   u.sexe_user,
                   u.photo_user,
                   u.matricule_user,
                   u.statut_user,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.email_user AS email_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.sexe_user AS sexe_enseignant
            FROM enseignants e
            JOIN users u ON u.code_user = e.code_enseignant
            WHERE e.id_enseignant = ?
            LIMIT 1
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getById error: " . $e->getMessage());
            return [];
        }
    }

    public function getByCode(string $code): array
    {
        $sql = "
            SELECT e.*,
                   u.id_user,
                   u.nom_user,
                   u.prenom_user,
                   u.email_user,
                   u.telephone_user,
                   u.sexe_user,
                   u.photo_user,
                   u.matricule_user,
                   u.statut_user,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.email_user AS email_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.sexe_user AS sexe_enseignant
            FROM enseignants e
            JOIN users u ON u.code_user = e.code_enseignant
            WHERE e.code_enseignant = ?
            LIMIT 1
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$code]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getByCode error: " . $e->getMessage());
            return [];
        }
    }

    public function getUsersAvailableForTeacher(): array
    {
        $sql = "
            SELECT u.id_user, u.code_user, u.nom_user, u.prenom_user, u.email_user, u.telephone_user, u.sexe_user
            FROM users u
            WHERE u.statut_user = 'actif'
              AND u.code_user NOT IN (SELECT code_enseignant FROM enseignants)
            ORDER BY u.nom_user ASC, u.prenom_user ASC
        ";
        try {
            $stmt = $this->getCon()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEnseignant getUsersAvailableForTeacher error: " . $e->getMessage());
            return [];
        }
    }
}
