<?php

class ModelAnnee extends BaseModel
{
    protected string $table = 'annees';
    protected string $primaryKey = 'id_annee';
    protected ?string $statusField = 'statut_annee';
    protected ?string $createdAtField = 'created_at_annee';

    /**
     * Active une année académique en clôturant automatiquement l'ancienne (Exclusivité)
     */
    public function setActiveYear(int $id): bool
    {
        try {
            $db = $this->getCon();
            $db->beginTransaction();
            // Clôturer toute année précédemment active
            $db->exec("UPDATE `annees` SET `statut_annee` = 'cloture' WHERE `statut_annee` = 'actif'");
            // Activer l'année demandée
            $stmt = $db->prepare("UPDATE `annees` SET `statut_annee` = 'actif' WHERE `id_annee` = ?");
            $stmt->execute([$id]);
            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->getCon()->inTransaction()) {
                $this->getCon()->rollBack();
            }
            error_log("ModelAnnee::setActiveYear error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Surcharge de toggleStatus pour garantir qu'il n'y a qu'une seule année active
     */
    public function toggleStatus(int $id): bool
    {
        $current = $this->getById($id);
        if (!$current) return false;

        // Si l'année n'est pas active, on l'active (et clôture l'ancienne)
        if (($current['statut_annee'] ?? '') !== 'actif') {
            return $this->setActiveYear($id);
        } else {
            // Clôturer l'année active
            $stmt = $this->getCon()->prepare("UPDATE `annees` SET `statut_annee` = 'cloture' WHERE `id_annee` = ?");
            return $stmt->execute([$id]);
        }
    }

    /**
     * Vérifie si une année académique peut être activée (date de début arrivée)
     */
    public function canActivate(array $annee, ?string &$errorMsg = null): bool
    {
        $dateDebut = $annee['date_debut_annee'] ?? '';
        if (empty($dateDebut)) {
            $errorMsg = "La date de début de l'année académique n'est pas définie.";
            return false;
        }

        $today = date('Y-m-d');
        if ($dateDebut > $today) {
            $dateDebutFr = date('d/m/Y', strtotime($dateDebut));
            $libelle = $annee['libelle_annee'] ?? '';
            $errorMsg = "Impossible d'activer l'année académique {$libelle} : la date de début ({$dateDebutFr}) n'est pas encore arrivée.";
            return false;
        }

        $dateFin = $annee['date_fin_annee'] ?? '';
        if (!empty($dateFin) && $dateFin < $today) {
            $dateFinFr = date('d/m/Y', strtotime($dateFin));
            $libelle = $annee['libelle_annee'] ?? '';
            $errorMsg = "Impossible d'activer l'année académique {$libelle} : la date de fin ({$dateFinFr}) est déjà passée.";
            return false;
        }

        return true;
    }

    /**
     * Vérifie si une année académique peut être clôturée / désactivée (date de fin arrivée)
     */
    public function canClose(array $annee, ?string &$errorMsg = null): bool
    {
        $dateFin = $annee['date_fin_annee'] ?? '';
        if (empty($dateFin)) {
            $errorMsg = "La date de fin de l'année académique n'est pas définie.";
            return false;
        }

        return true;
    }

    /**
     * Récupère l'unique année académique active en base
     */
    public function getActiveYear(): ?array
    {
        try {
            $stmt = $this->getCon()->query("SELECT * FROM `annees` WHERE `statut_annee` = 'actif' LIMIT 1");
            $res = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            if (!$res) {
                // Fallback sur la plus récente si aucune n'est active
                $stmt2 = $this->getCon()->query("SELECT * FROM `annees` ORDER BY `id_annee` DESC LIMIT 1");
                $res = $stmt2 ? $stmt2->fetch(PDO::FETCH_ASSOC) : null;
            }
            return $res ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getByCode(string $code, ?string $codeField = null): array
    {
        try {
            $field = $codeField ?? 'code_annee';
            $stmt = $this->getCon()->prepare("SELECT * FROM annees WHERE `{$field}` = ?");
            $stmt->execute([$code]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Récupère la liste des années académiques accessibles pour l'utilisateur courant (identique à la navbar)
     */
    public function getAccessibleAnnees(): array
    {
        try {
            $db = $this->getCon();
            if (!$db) return [];

            $allAnnees = $db->query("SELECT * FROM annees ORDER BY id_annee DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (empty($allAnnees)) return [];

            $userCode = Auth::code();
            $userPerms = Auth::permissions();
            $hasGlobalView = in_array('*', $userPerms, true) || in_array(PERMISSIONS::VIEW_ANNEES_ANTERIEURES, $userPerms, true);

            $userAcces = [];
            if (!$hasGlobalView && !empty($userCode)) {
                try {
                    $st = $db->prepare("SELECT annee_code, niveau_acces FROM " . TABLES::USER_ANNEE_ACCES . " WHERE user_code = ?");
                    $st->execute([$userCode]);
                    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                        $userAcces[$r['annee_code']] = $r['niveau_acces'];
                    }
                } catch (Exception $e) {}
            }

            $accessible = [];
            foreach ($allAnnees as $an) {
                $isCurrentActiveDb = (($an['statut_annee'] ?? '') === 'actif');
                $isPlanifie = (($an['statut_annee'] ?? '') === 'planifie');
                $canAccess = $isCurrentActiveDb || $isPlanifie || $hasGlobalView || isset($userAcces[$an['code_annee']]);
                if ($canAccess) {
                    $accessible[] = $an;
                }
            }

            return $accessible;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Statistiques consolidées pour une année académique
     */
    public function getStatsForAnnee(string $anneeCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM " . TABLES::INSCRIPTIONS . " WHERE annee_code = ? AND (statut_inscription != 'annule' OR statut_inscription IS NULL)) as total_etudiants,
                    (SELECT COUNT(*) FROM " . TABLES::CLASSES . " WHERE annee_code = ? AND (statut_classe = 'actif' OR statut_classe IS NULL)) as total_classes,
                    (SELECT COUNT(*) FROM " . TABLES::SEMESTRES . " WHERE annee_code = ? AND (statut_semestre = 'actif' OR statut_semestre IS NULL)) as total_semestres,
                    (SELECT COALESCE(SUM(montant_paiement), 0) FROM " . TABLES::PAIEMENTS . " WHERE annee_code = ? AND (statut_paiement != 'annule' OR statut_paiement IS NULL)) as total_recouvrement
            ");
            $stmt->execute([$anneeCode, $anneeCode, $anneeCode, $anneeCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'total_etudiants' => 0, 'total_classes' => 0, 'total_semestres' => 0, 'total_recouvrement' => 0
            ];
        } catch (Exception $e) {
            error_log("ModelAnnee::getStatsForAnnee error: " . $e->getMessage());
            return [
                'total_etudiants' => 0, 'total_classes' => 0, 'total_semestres' => 0, 'total_recouvrement' => 0
            ];
        }
    }

    /**
     * Classes associées à une année académique avec le nombre d'élèves inscrits
     */
    public function getClassesForAnnee(string $anneeCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT cl.*, f.libelle_filiere, n.libelle_niveau,
                       (SELECT COUNT(*) FROM " . TABLES::INSCRIPTIONS . " ins WHERE ins.classe_code = cl.code_classe AND (ins.statut_inscription != 'annule' OR ins.statut_inscription IS NULL)) as nb_eleves
                FROM " . TABLES::CLASSES . " cl
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                WHERE cl.annee_code = ?
                ORDER BY cl.libelle_classe ASC
            ");
            $stmt->execute([$anneeCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelAnnee::getClassesForAnnee error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Semestres associés à une année académique
     */
    public function getSemestresForAnnee(string $anneeCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT * FROM " . TABLES::SEMESTRES . " WHERE annee_code = ? ORDER BY id_semestre ASC
            ");
            $stmt->execute([$anneeCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelAnnee::getSemestresForAnnee error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recherche une année par son code ou son identifiant numérique
     */
    public function getAnneeByCodeOrId(string $code): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT * FROM " . TABLES::ANNEES . " WHERE code_annee = ? OR id_annee = ? LIMIT 1");
            $stmt->execute([$code, is_numeric($code) ? (int)$code : 0]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelAnnee::getAnneeByCodeOrId error: " . $e->getMessage());
            return null;
        }
    }
}



