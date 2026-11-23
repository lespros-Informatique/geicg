<?php

class ModelMatiere extends BaseModel
{
    protected string $table = TABLES::MATIERES;
    protected string $primaryKey = 'id_matiere';
    protected ?string $statusField = 'statut_matiere';
    protected ?string $createdAtField = 'created_at_matiere';

    /**
     * Affectations par classe et enseignant dans l'emploi du temps pour une matière donnée.
     */
    public function getAffectationsForMatiere(string $matiereCode, ?string $anneeCode = null): array
    {
        $targetAnnee = $anneeCode !== null ? $anneeCode : (string)Session::get('annee_active_code', '');
        $sql = "
            SELECT
                edt.matiere_code,
                edt.classe_code,
                cl.id_classe,
                cl.libelle_classe,
                f.libelle_filiere,
                f.slug_filiere,
                n.libelle_niveau,
                n.slug_niveau,
                e.id_enseignant,
                edt.enseignant_code,
                u.nom_user AS nom_prof,
                u.prenom_user AS prenom_prof,
                e.grade_enseignant,
                COUNT(edt.id_emploi) AS nb_creneaux,
                GROUP_CONCAT(DISTINCT s.libelle_salle ORDER BY s.libelle_salle SEPARATOR ', ') AS salles_noms,
                GROUP_CONCAT(
                    DISTINCT CONCAT(
                        UPPER(LEFT(edt.jour, 1)),
                        SUBSTRING(edt.jour, 2),
                        ' ',
                        TIME_FORMAT(edt.heure_debut, '%H:%i'),
                        '-',
                        TIME_FORMAT(edt.heure_fin, '%H:%i')
                    )
                    ORDER BY FIELD(edt.jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'), edt.heure_debut
                    SEPARATOR ' · '
                ) AS creneaux_resume,
                ROUND(SUM(TIME_TO_SEC(TIMEDIFF(edt.heure_fin, edt.heure_debut))) / 3600, 1) AS volume_horaire
            FROM " . TABLES::EMPLOIS_TEMPS . " edt
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
            LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = edt.enseignant_code
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = edt.enseignant_code
            LEFT JOIN " . TABLES::SALLES . " s ON s.code_salle = edt.salle_code
            WHERE edt.matiere_code = ?
              AND (edt.statut_emploi = '" . STATUTS::ACTIF . "' OR edt.statut_emploi IS NULL)
        ";
        $params = [$matiereCode];
        if ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL') {
            $sql .= " AND edt.annee_code = ?";
            $params[] = $targetAnnee;
        }
        $sql .= "
            GROUP BY
                edt.matiere_code,
                edt.classe_code,
                cl.id_classe,
                cl.libelle_classe,
                f.libelle_filiere,
                f.slug_filiere,
                n.libelle_niveau,
                n.slug_niveau,
                e.id_enseignant,
                edt.enseignant_code,
                u.nom_user,
                u.prenom_user,
                e.grade_enseignant
            ORDER BY cl.libelle_classe ASC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Maquettes pédagogiques rattachées à cette matière (filières, niveaux, catégories, coefficients).
     */
    public function getMaquettesForMatiere(string $matiereCode, ?string $anneeCode = null): array
    {
        $targetAnnee = $anneeCode !== null ? $anneeCode : (string)Session::get('annee_active_code', '');
        $sql = "
            SELECT
                vm.id_maquette,
                vm.code_maquette,
                vm.annee_code,
                vm.libelle_annee,
                vm.filiere_code,
                vm.libelle_filiere,
                vm.type_filiere,
                vm.niveau_code,
                vm.libelle_niveau,
                vm.categorie_matiere,
                vm.coefficient,
                vm.statut_maquette
            FROM " . VIEWS::MAQUETTES_COMPLETES . " vm
            WHERE vm.matiere_code = ?
        ";
        $params = [$matiereCode];
        if ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL') {
            $sql .= " AND vm.annee_code = ?";
            $params[] = $targetAnnee;
        }
        $sql .= " ORDER BY vm.libelle_filiere ASC, vm.libelle_niveau ASC";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Statistiques dynamiques consolidées pour la fiche matière (emplois du temps, notes, absences, maquettes).
     */
    public function getStatsForMatiere(string $matiereCode, int $defaultTotalClasses = 0, ?string $anneeCode = null): array
    {
        $targetAnnee = $anneeCode !== null ? $anneeCode : (string)Session::get('annee_active_code', '');
        $hasYear = ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL');
        $con = $this->getCon();

        // 1. Emplois du temps
        $sqlEdt = "
            SELECT
                COUNT(DISTINCT classe_code) AS total_classes,
                COUNT(DISTINCT enseignant_code) AS total_profs,
                COUNT(DISTINCT salle_code) AS total_salles,
                COUNT(*) AS total_creneaux,
                COALESCE(ROUND(SUM(TIME_TO_SEC(TIMEDIFF(heure_fin, heure_debut))) / 3600, 1), 0) AS total_volume_hebdo
            FROM " . TABLES::EMPLOIS_TEMPS . "
            WHERE matiere_code = ?
              AND (statut_emploi = '" . STATUTS::ACTIF . "' OR statut_emploi IS NULL)
        ";
        $paramsEdt = [$matiereCode];
        if ($hasYear) {
            $sqlEdt .= " AND annee_code = ?";
            $paramsEdt[] = $targetAnnee;
        }
        $stmtEdt = $con->prepare($sqlEdt);
        $stmtEdt->execute($paramsEdt);
        $edtStats = $stmtEdt->fetch(PDO::FETCH_ASSOC) ?: [];

        // 2. Notes & évaluations
        $sqlNotes = "
            SELECT
                COUNT(*) AS total_notes,
                COUNT(DISTINCT CONCAT(n.composition_code, '|', COALESCE(n.composition_matiere_id, 0))) AS total_evaluations,
                ROUND(AVG(n.valeur_note), 2) AS moyenne_globale
            FROM " . TABLES::NOTES . " n
            INNER JOIN " . TABLES::COMPOSITION_MATIERES . " cm ON cm.id_composition_matiere = n.composition_matiere_id
            INNER JOIN " . TABLES::COMPOSITIONS . " c ON c.code_composition = n.composition_code
            WHERE cm.matiere_code = ?
              AND n.statut_note = '" . STATUTS::ACTIF . "'
        ";
        $paramsNotes = [$matiereCode];
        if ($hasYear) {
            $sqlNotes .= " AND c.annee_code = ?";
            $paramsNotes[] = $targetAnnee;
        }
        $stmtNotes = $con->prepare($sqlNotes);
        $stmtNotes->execute($paramsNotes);
        $notesStats = $stmtNotes->fetch(PDO::FETCH_ASSOC) ?: [];

        // 3. Absences
        $sqlAbs = "
            SELECT
                COUNT(*) AS total_absences_count,
                COALESCE(SUM(duree_heures), 0) AS total_absences_heures,
                COALESCE(SUM(CASE WHEN justifiee = 'non' THEN duree_heures ELSE 0 END), 0) AS total_absences_injustifiees
            FROM " . TABLES::ABSENCES . "
            WHERE matiere_code = ?
        ";
        $paramsAbs = [$matiereCode];
        if ($hasYear) {
            $sqlAbs .= " AND annee_code = ?";
            $paramsAbs[] = $targetAnnee;
        }
        $stmtAbs = $con->prepare($sqlAbs);
        $stmtAbs->execute($paramsAbs);
        $absStats = $stmtAbs->fetch(PDO::FETCH_ASSOC) ?: [];

        // 4. Maquettes pédagogiques
        $sqlMaq = "
            SELECT
                COUNT(*) AS total_maquettes,
                SUM(CASE WHEN categorie_matiere = '" . CATEGORIES_MATIERES::GENERALE . "' THEN 1 ELSE 0 END) AS maquettes_generales,
                SUM(CASE WHEN categorie_matiere = '" . CATEGORIES_MATIERES::PROFESSIONNELLE . "' THEN 1 ELSE 0 END) AS maquettes_professionnelles,
                MIN(coefficient) AS coef_min,
                MAX(coefficient) AS coef_max
            FROM " . TABLES::MAQUETTES_PEDAGOGIQUES . "
            WHERE matiere_code = ?
              AND statut_maquette = '" . STATUTS::ACTIF . "'
        ";
        $paramsMaq = [$matiereCode];
        if ($hasYear) {
            $sqlMaq .= " AND annee_code = ?";
            $paramsMaq[] = $targetAnnee;
        }
        $stmtMaq = $con->prepare($sqlMaq);
        $stmtMaq->execute($paramsMaq);
        $maqStats = $stmtMaq->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_classes'               => (int)($edtStats['total_classes'] ?? $defaultTotalClasses),
            'total_profs'                 => (int)($edtStats['total_profs'] ?? 0),
            'total_salles'                => (int)($edtStats['total_salles'] ?? 0),
            'total_creneaux'              => (int)($edtStats['total_creneaux'] ?? 0),
            'total_volume_hebdo'          => (float)($edtStats['total_volume_hebdo'] ?? 0),
            'total_notes'                 => (int)($notesStats['total_notes'] ?? 0),
            'total_evaluations'           => (int)($notesStats['total_evaluations'] ?? 0),
            'moyenne_globale'             => isset($notesStats['moyenne_globale']) && $notesStats['moyenne_globale'] !== null
                ? (float)$notesStats['moyenne_globale']
                : null,
            'total_absences_count'        => (int)($absStats['total_absences_count'] ?? 0),
            'total_absences_heures'       => (float)($absStats['total_absences_heures'] ?? 0),
            'total_absences_injustifiees' => (float)($absStats['total_absences_injustifiees'] ?? 0),
            'total_maquettes'             => (int)($maqStats['total_maquettes'] ?? 0),
            'maquettes_generales'         => (int)($maqStats['maquettes_generales'] ?? 0),
            'maquettes_professionnelles'  => (int)($maqStats['maquettes_professionnelles'] ?? 0),
            'coef_min'                    => isset($maqStats['coef_min']) && $maqStats['coef_min'] !== null ? (float)$maqStats['coef_min'] : null,
            'coef_max'                    => isset($maqStats['coef_max']) && $maqStats['coef_max'] !== null ? (float)$maqStats['coef_max'] : null,
        ];
    }
}
