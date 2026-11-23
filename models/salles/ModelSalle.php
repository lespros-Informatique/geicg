<?php

class ModelSalle extends BaseModel
{
    protected string $table = TABLES::SALLES;
    protected string $primaryKey = 'id_salle';
    protected ?string $statusField = 'statut_salle';
    protected ?string $createdAtField = 'created_at';

    /**
     * Récupère les créneaux d'emploi du temps associés à une salle pour une année donnée.
     */
    public function getPlanningForSalle(string $salleCode, ?string $anneeCode = null): array
    {
        try {
            $targetAnnee = $anneeCode !== null ? $anneeCode : (string)Session::get('annee_active_code', '');
            $sql = "
                SELECT
                    edt.*,
                    cl.id_classe,
                    cl.libelle_classe,
                    f.libelle_filiere,
                    n.libelle_niveau,
                    m.id_matiere,
                    m.libelle_matiere,
                    e.id_enseignant,
                    e.grade_enseignant,
                    u.nom_user AS nom_prof,
                    u.prenom_user AS prenom_prof,
                    ROUND(TIME_TO_SEC(TIMEDIFF(edt.heure_fin, edt.heure_debut)) / 3600, 1) AS duree_heures
                FROM " . TABLES::EMPLOIS_TEMPS . " edt
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
                LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = edt.enseignant_code
                LEFT JOIN " . TABLES::USERS . " u ON u.code_user = edt.enseignant_code
                WHERE edt.salle_code = ?
                  AND (edt.statut_emploi = '" . STATUTS::ACTIF . "' OR edt.statut_emploi IS NULL)
            ";
            $params = [$salleCode];
            if ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL') {
                $sql .= " AND edt.annee_code = ?";
                $params[] = $targetAnnee;
            }
            $sql .= "
                ORDER BY
                    FIELD(edt.jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'),
                    edt.heure_debut ASC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelSalle::getPlanningForSalle error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Statistiques dynamiques d'occupation et de charge pour une salle.
     */
    public function getStatsForSalle(string $salleCode, ?string $anneeCode = null): array
    {
        $targetAnnee = $anneeCode !== null ? $anneeCode : (string)Session::get('annee_active_code', '');
        $hasYear = ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL');
        $con = $this->getCon();

        // 1. Statistiques sur l'emploi du temps
        $sqlEdt = "
            SELECT
                COUNT(*) AS total_creneaux,
                COALESCE(ROUND(SUM(TIME_TO_SEC(TIMEDIFF(heure_fin, heure_debut))) / 3600, 1), 0) AS total_heures_hebdo,
                COUNT(DISTINCT jour) AS total_jours_actifs,
                COUNT(DISTINCT classe_code) AS total_classes,
                COUNT(DISTINCT matiere_code) AS total_matieres,
                COUNT(DISTINCT enseignant_code) AS total_enseignants
            FROM " . TABLES::EMPLOIS_TEMPS . "
            WHERE salle_code = ?
              AND (statut_emploi = '" . STATUTS::ACTIF . "' OR statut_emploi IS NULL)
        ";
        $paramsEdt = [$salleCode];
        if ($hasYear) {
            $sqlEdt .= " AND annee_code = ?";
            $paramsEdt[] = $targetAnnee;
        }
        $stmtEdt = $con->prepare($sqlEdt);
        $stmtEdt->execute($paramsEdt);
        $edt = $stmtEdt->fetch(PDO::FETCH_ASSOC) ?: [];

        // 2. Effectif maximal parmi les classes accueillies dans cette salle
        $sqlMax = "
            SELECT COALESCE(MAX(eff.nb_etudiants), 0) AS max_effectif_classe
            FROM (
                SELECT i.classe_code, COUNT(DISTINCT i.etudiant_code) AS nb_etudiants
                FROM " . TABLES::INSCRIPTIONS . " i
                INNER JOIN (
                    SELECT DISTINCT classe_code
                    FROM " . TABLES::EMPLOIS_TEMPS . "
                    WHERE salle_code = ?
                      AND (statut_emploi = '" . STATUTS::ACTIF . "' OR statut_emploi IS NULL)"
                      . ($hasYear ? " AND annee_code = ?" : "") . "
                ) scls ON scls.classe_code = i.classe_code
                WHERE i.statut_inscription != 'annule'"
                  . ($hasYear ? " AND i.annee_code = ?" : "") . "
                GROUP BY i.classe_code
            ) eff
        ";
        $paramsMax = [$salleCode];
        if ($hasYear) {
            $paramsMax[] = $targetAnnee;
            $paramsMax[] = $targetAnnee;
        }
        $stmtMax = $con->prepare($sqlMax);
        $stmtMax->execute($paramsMax);
        $maxRow = $stmtMax->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_creneaux'      => (int)($edt['total_creneaux'] ?? 0),
            'total_heures_hebdo'  => (float)($edt['total_heures_hebdo'] ?? 0),
            'total_jours_actifs'  => (int)($edt['total_jours_actifs'] ?? 0),
            'total_classes'       => (int)($edt['total_classes'] ?? 0),
            'total_matieres'      => (int)($edt['total_matieres'] ?? 0),
            'total_enseignants'   => (int)($edt['total_enseignants'] ?? 0),
            'max_effectif_classe' => (int)($maxRow['max_effectif_classe'] ?? 0),
        ];
    }

    /**
     * Récupère la liste des salles ordonnées pour l'impression PDF
     */
    public function getAllForPdf(): array
    {
        try {
            $stmt = $this->getCon()->query("SELECT s.* FROM " . TABLES::SALLES . " s ORDER BY s.libelle_salle ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log("ModelSalle::getAllForPdf error: " . $e->getMessage());
            return [];
        }
    }
}
