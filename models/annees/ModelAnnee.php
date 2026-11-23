<?php

class ModelAnnee extends BaseModel
{
    protected string $table = TABLES::ANNEES;
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
            $db->exec("UPDATE `" . TABLES::ANNEES . "` SET `statut_annee` = '" . STATUTS::CLOTURE . "' WHERE `statut_annee` = '" . STATUTS::ACTIF . "'");
            // Activer l'année demandée
            $stmt = $db->prepare("UPDATE `" . TABLES::ANNEES . "` SET `statut_annee` = '" . STATUTS::ACTIF . "' WHERE `id_annee` = ?");
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
        if (($current['statut_annee'] ?? '') !== STATUTS::ACTIF) {
            return $this->setActiveYear($id);
        } else {
            // Clôturer l'année active
            $stmt = $this->getCon()->prepare("UPDATE `" . TABLES::ANNEES . "` SET `statut_annee` = '" . STATUTS::CLOTURE . "' WHERE `id_annee` = ?");
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
     * Vérifie si une année académique peut être clôturée (date de fin définie et tous les semestres clôturés)
     */
    public function canClose(array $annee, ?string &$errorMsg = null): bool
    {
        $dateFin = $annee['date_fin_annee'] ?? '';
        if (empty($dateFin)) {
            $errorMsg = "La date de fin de l'année académique n'est pas définie.";
            return false;
        }

        $codeAnnee = $annee['code_annee'] ?? '';
        $libelle = $annee['libelle_annee'] ?? $codeAnnee;

        if (!empty($codeAnnee)) {
            try {
                $stmt = $this->getCon()->prepare("
                    SELECT libelle_semestre, statut_semestre
                    FROM `" . TABLES::SEMESTRES . "`
                    WHERE `annee_code` = ?
                    ORDER BY id_semestre ASC
                ");
                $stmt->execute([$codeAnnee]);
                $semestres = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                if (empty($semestres)) {
                    $errorMsg = "Impossible de clôturer l'année académique {$libelle} : aucun semestre n'a été enregistré et clôturé pour cette année.";
                    return false;
                }

                $nonClotures = [];
                foreach ($semestres as $sem) {
                    if (($sem['statut_semestre'] ?? '') !== STATUTS::CLOTURE) {
                        $statutLabel = ($sem['statut_semestre'] ?? '') === STATUTS::ACTIF ? 'Actif' : 'En préparation';
                        $nonClotures[] = ($sem['libelle_semestre'] ?? 'Semestre') . " ({$statutLabel})";
                    }
                }

                if (!empty($nonClotures)) {
                    $errorMsg = "Impossible de clôturer l'année académique {$libelle} : tous les semestres de cette année doivent être clôturés au préalable (" . implode(', ', $nonClotures) . ").";
                    return false;
                }
            } catch (Exception $e) {
                error_log("ModelAnnee::canClose error: " . $e->getMessage());
                $errorMsg = "Erreur lors de la vérification des semestres de l'année académique.";
                return false;
            }
        }

        return true;
    }

    /**
     * Récupère l'unique année académique active en base (sans fallback sur une année clôturée)
     */
    public function getActiveYear(): ?array
    {
        try {
            $stmt = $this->getCon()->query("SELECT * FROM `" . TABLES::ANNEES . "` WHERE `statut_annee` = '" . STATUTS::ACTIF . "' LIMIT 1");
            return ($stmt && ($res = $stmt->fetch(PDO::FETCH_ASSOC))) ? $res : null;
        } catch (Exception $e) {
            error_log("ModelAnnee::getActiveYear error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifie si un utilisateur a le droit d'accéder / consulter une année spécifique
     */
    public function canUserAccessAnnee(string $anneeCode, ?string $userCode = null): bool
    {
        try {
            $annee = $this->getByCode($anneeCode);
            if (empty($annee)) {
                return false;
            }

            // Si l'année est active ou planifiée, accès standard autorisé
            if (($annee['statut_annee'] ?? '') === STATUTS::ACTIF || ($annee['statut_annee'] ?? '') === 'planifie') {
                return true;
            }

            // Si l'année est clôturée ou archivée : vérifier strictement la permission dédiée ou l'accès par la table user_annee_acces
            if (Auth::can(PERMISSIONS::VIEW_ANNEES_ANTERIEURES)) {
                return true;
            }

            $uCode = $userCode ?? Auth::code();
            if (!empty($uCode)) {
                $st = $this->getCon()->prepare("SELECT COUNT(*) FROM `" . TABLES::USER_ANNEE_ACCES . "` WHERE `user_code` = ? AND `annee_code` = ?");
                $st->execute([$uCode, $anneeCode]);
                return ((int)$st->fetchColumn()) > 0;
            }

            return false;
        } catch (Exception $e) {
            error_log("ModelAnnee::canUserAccessAnnee error: " . $e->getMessage());
            return false;
        }
    }

    public function getByCode(string $code, ?string $codeField = null): array
    {
        try {
            $field = $codeField ?? 'code_annee';
            $stmt = $this->getCon()->prepare("SELECT * FROM " . TABLES::ANNEES . " WHERE `{$field}` = ?");
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

            $allAnnees = $db->query("SELECT * FROM " . TABLES::ANNEES . " ORDER BY id_annee DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (empty($allAnnees)) return [];

            $userCode = Auth::code();
            $hasGlobalView = Auth::can(PERMISSIONS::VIEW_ANNEES_ANTERIEURES);

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
                $isCurrentActiveDb = (($an['statut_annee'] ?? '') === STATUTS::ACTIF);
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
     * Statistiques consolidées pour une année académique (scolarité strictement dissociée des frais annexes)
     */
    public function getStatsForAnnee(string $anneeCode): array
    {
        $defaults = [
            'total_etudiants' => 0,
            'total_affectes' => 0,
            'total_non_affectes' => 0,
            'total_hommes' => 0,
            'total_femmes' => 0,
            'total_classes' => 0,
            'total_capacite' => 0,
            'total_semestres' => 0,
            'total_semestres_all' => 0,
            'total_semestres_clotures' => 0,
            'total_evaluations' => 0,
            'total_absences_heures' => 0.0,
            'total_recouvrement' => 0.0
        ];

        try {
            $db = $this->getCon();

            $stmtInsc = $db->prepare("
                SELECT
                    COUNT(*) AS total_etudiants,
                    SUM(CASE WHEN LOWER(COALESCE(i.affectation_etat, '')) IN ('affecte', 'oui') THEN 1 ELSE 0 END) AS total_affectes,
                    SUM(CASE WHEN LOWER(COALESCE(i.affectation_etat, '')) NOT IN ('affecte', 'oui') THEN 1 ELSE 0 END) AS total_non_affectes,
                    SUM(CASE WHEN UPPER(COALESCE(e.sexe_etudiant, '')) LIKE '%F%' THEN 1 ELSE 0 END) AS total_femmes,
                    SUM(CASE WHEN UPPER(COALESCE(e.sexe_etudiant, '')) NOT LIKE '%F%' AND TRIM(COALESCE(e.sexe_etudiant, '')) != '' THEN 1 ELSE 0 END) AS total_hommes
                FROM " . TABLES::INSCRIPTIONS . " i
                LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
                WHERE i.annee_code = ?
                  AND (i.statut_inscription != '" . STATUTS::ANNULE . "' OR i.statut_inscription IS NULL)
            ");
            $stmtInsc->execute([$anneeCode]);
            $inscStats = $stmtInsc->fetch(PDO::FETCH_ASSOC) ?: [];

            $stmtAgg = $db->prepare("
                SELECT
                    (SELECT COUNT(*) FROM " . TABLES::CLASSES . " WHERE annee_code = ? AND (statut_classe = '" . STATUTS::ACTIF . "' OR statut_classe IS NULL)) AS total_classes,
                    (SELECT COALESCE(SUM(capacite_max_classe), 0) FROM " . TABLES::CLASSES . " WHERE annee_code = ? AND (statut_classe = '" . STATUTS::ACTIF . "' OR statut_classe IS NULL)) AS total_capacite,
                    (SELECT COUNT(*) FROM " . TABLES::SEMESTRES . " WHERE annee_code = ? AND (statut_semestre = '" . STATUTS::ACTIF . "' OR statut_semestre IS NULL)) AS total_semestres,
                    (SELECT COUNT(*) FROM " . TABLES::SEMESTRES . " WHERE annee_code = ?) AS total_semestres_all,
                    (SELECT COUNT(*) FROM " . TABLES::SEMESTRES . " WHERE annee_code = ? AND statut_semestre = '" . STATUTS::CLOTURE . "') AS total_semestres_clotures,
                    (SELECT COUNT(*) FROM " . TABLES::COMPOSITIONS . " WHERE annee_code = ? AND (statut_composition != '" . STATUTS::ANNULE . "' OR statut_composition IS NULL)) AS total_evaluations,
                    (SELECT COALESCE(SUM(duree_heures), 0) FROM " . TABLES::ABSENCES . " WHERE annee_code = ?) AS total_absences_heures,
                    (SELECT COALESCE(SUM(montant_paiement), 0) FROM " . TABLES::PAIEMENTS . " WHERE annee_code = ? AND (statut_paiement != '" . STATUTS::ANNULE . "' OR statut_paiement IS NULL) AND (categorie_paiement IS NULL OR categorie_paiement != 'FRAIS_ANNEXES') AND (tranche_code IS NULL OR tranche_code != 'FRAIS_ANNEXES') AND (type_paiement IS NULL OR type_paiement NOT LIKE '%Frais Annexe%')) AS total_recouvrement
            ");
            $stmtAgg->execute([$anneeCode, $anneeCode, $anneeCode, $anneeCode, $anneeCode, $anneeCode, $anneeCode, $anneeCode]);
            $aggStats = $stmtAgg->fetch(PDO::FETCH_ASSOC) ?: [];

            return array_merge($defaults, $inscStats, $aggStats);
        } catch (Exception $e) {
            error_log("ModelAnnee::getStatsForAnnee error: " . $e->getMessage());
            return $defaults;
        }
    }

    /**
     * Classes associées à une année académique avec le nombre d'élèves inscrits (agrégation batch sans sous-requête corrélée)
     */
    public function getClassesForAnnee(string $anneeCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT cl.*, f.libelle_filiere, n.libelle_niveau,
                       COALESCE(eff.nb_eleves, 0) AS nb_eleves
                FROM " . TABLES::CLASSES . " cl
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN (
                    SELECT classe_code, COUNT(*) AS nb_eleves
                    FROM " . TABLES::INSCRIPTIONS . "
                    WHERE annee_code = ?
                      AND (statut_inscription != '" . STATUTS::ANNULE . "' OR statut_inscription IS NULL)
                    GROUP BY classe_code
                ) eff ON eff.classe_code = cl.code_classe
                WHERE cl.annee_code = ?
                ORDER BY cl.libelle_classe ASC
            ");
            $stmt->execute([$anneeCode, $anneeCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelAnnee::getClassesForAnnee error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Semestres associés à une année académique enrichis du nombre d'évaluations et d'heures d'absences
     */
    public function getSemestresForAnnee(string $anneeCode): array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT s.*,
                       COALESCE(cmp.nb_evaluations, 0) AS nb_evaluations,
                       COALESCE(ab.heures_absences, 0) AS heures_absences
                FROM " . TABLES::SEMESTRES . " s
                LEFT JOIN (
                    SELECT semestre_code, COUNT(*) AS nb_evaluations
                    FROM " . TABLES::COMPOSITIONS . "
                    WHERE annee_code = ?
                      AND (statut_composition != '" . STATUTS::ANNULE . "' OR statut_composition IS NULL)
                    GROUP BY semestre_code
                ) cmp ON cmp.semestre_code = s.code_semestre
                LEFT JOIN (
                    SELECT semestre_code, SUM(duree_heures) AS heures_absences
                    FROM " . TABLES::ABSENCES . "
                    WHERE annee_code = ?
                    GROUP BY semestre_code
                ) ab ON ab.semestre_code = s.code_semestre
                WHERE s.annee_code = ?
                ORDER BY s.id_semestre ASC
            ");
            $stmt->execute([$anneeCode, $anneeCode, $anneeCode]);
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



