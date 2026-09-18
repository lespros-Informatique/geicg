<?php

class ModelSemestre extends BaseModel
{
    protected string $table = 'semestres';
    protected string $primaryKey = 'id_semestre';
    protected ?string $statusField = 'statut_semestre';
    protected ?string $createdAtField = 'created_at_semestre';

    public function getAll(?string $anneeCode = null): array
    {
        $sql = "SELECT s.*, a.libelle_annee 
                FROM semestres s
                LEFT JOIN annees a ON a.code_annee = s.annee_code";
        $params = [];
        if (!empty($anneeCode)) {
            $sql .= " WHERE s.annee_code = ? ";
            $params[] = $anneeCode;
        }
        $sql .= " ORDER BY s.id_semestre DESC";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all semestres: " . $e->getMessage());
            return [];
        }
    }

    public function getByCode(string $code, ?string $codeField = null): array
    {
        try {
            $field = $codeField ?? 'code_semestre';
            $stmt = $this->getCon()->prepare("SELECT * FROM semestres WHERE `{$field}` = ?");
            $stmt->execute([$code]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Active un semestre en clôturant automatiquement l'ancien pour la même année (Exclusivité)
     */
    public function setActiveSemester(int $id): bool
    {
        try {
            $semestre = $this->getById($id);
            if (!$semestre) return false;
            $anneeCode = $semestre['annee_code'] ?? '';

            $db = $this->getCon();
            $db->beginTransaction();
            // Clôturer tout semestre précédemment active pour cette année
            $stmtClose = $db->prepare("UPDATE `semestres` SET `statut_semestre` = 'cloture' WHERE `annee_code` = ? AND `statut_semestre` = 'actif'");
            $stmtClose->execute([$anneeCode]);
            // Activer le semestre demandé
            $stmtAct = $db->prepare("UPDATE `semestres` SET `statut_semestre` = 'actif' WHERE `id_semestre` = ?");
            $stmtAct->execute([$id]);
            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->getCon()->inTransaction()) {
                $this->getCon()->rollBack();
            }
            error_log("ModelSemestre::setActiveSemester error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bascule le statut d'un semestre (planifie/cloture -> actif, actif -> cloture)
     */
    public function toggleStatus(int $id): bool
    {
        $current = $this->getById($id);
        if (!$current) return false;

        if (($current['statut_semestre'] ?? '') !== 'actif') {
            return $this->setActiveSemester($id);
        } else {
            $stmt = $this->getCon()->prepare("UPDATE `semestres` SET `statut_semestre` = 'cloture' WHERE `id_semestre` = ?");
            return $stmt->execute([$id]);
        }
    }

    /**
     * Vérifie si un semestre peut être activé (date de début définie)
     */
    public function canActivate(array $semestre, ?string &$errorMsg = null): bool
    {
        $dateDebut = $semestre['date_debut_semestre'] ?? '';
        if (empty($dateDebut)) {
            $errorMsg = "La date de début du semestre n'est pas définie.";
            return false;
        }

        $today = date('Y-m-d');
        if ($dateDebut > $today) {
            $dateDebutFr = date('d/m/Y', strtotime($dateDebut));
            $libelle = $semestre['libelle_semestre'] ?? '';
            $errorMsg = "Impossible d'activer le {$libelle} : la date de début ({$dateDebutFr}) n'est pas encore arrivée.";
            return false;
        }

        return true;
    }

    /**
     * Vérifie si un semestre peut être clôturé
     */
    public function canClose(array $semestre, ?string &$errorMsg = null): bool
    {
        $dateFin = $semestre['date_fin_semestre'] ?? '';
        if (empty($dateFin)) {
            $errorMsg = "La date de fin du semestre n'est pas définie.";
            return false;
        }

        return true;
    }
}


