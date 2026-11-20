<?php

class ModelMatiere extends BaseModel
{
    protected string $table = TABLES::MATIERES;
    protected string $primaryKey = 'id_matiere';
    protected ?string $statusField = 'statut_matiere';
    protected ?string $createdAtField = 'created_at_matiere';

    public function getAffectationsForMatiere(string $matiereCode, ?string $anneeCode = null): array
    {
        $targetAnnee = $anneeCode ?: (string)Session::get('annee_active_code', '');
        $sql = "
            SELECT DISTINCT 
                   edt.matiere_code,
                   edt.classe_code,
                   cl.libelle_classe, 
                   f.libelle_filiere, 
                   n.libelle_niveau,
                   u.nom_user as nom_prof,
                   u.prenom_user as prenom_prof,
                   e.grade_enseignant,
                   ROUND(SUM(TIME_TO_SEC(TIMEDIFF(edt.heure_fin, edt.heure_debut))) / 3600, 1) AS volume_horaire
            FROM " . TABLES::EMPLOIS_TEMPS . " edt
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
            LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = edt.enseignant_code
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = edt.enseignant_code
            WHERE edt.matiere_code = ?
              AND (edt.statut_emploi = '" . STATUTS::ACTIF . "' OR edt.statut_emploi IS NULL)
        ";
        $params = [$matiereCode];
        if ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL') {
            $sql .= " AND edt.annee_code = ?";
            $params[] = $targetAnnee;
        }
        $sql .= "
            GROUP BY edt.matiere_code, edt.classe_code, cl.libelle_classe, f.libelle_filiere, n.libelle_niveau, u.nom_user, u.prenom_user, e.grade_enseignant
            ORDER BY cl.libelle_classe ASC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStatsForMatiere(string $matiereCode, int $defaultTotalClasses = 0, ?string $anneeCode = null): array
    {
        $targetAnnee = $anneeCode ?: (string)Session::get('annee_active_code', '');
        $params = [$matiereCode];
        $notesYearFilter = '';
        if ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL') {
            $notesYearFilter = " AND c.annee_code = ?";
            $params[] = $targetAnnee;
        }
        $params[] = $matiereCode;
        $edtYearFilter = '';
        if ($targetAnnee !== '' && strtoupper($targetAnnee) !== 'ALL') {
            $edtYearFilter = " AND annee_code = ?";
            $params[] = $targetAnnee;
        }

        $stmt = $this->getCon()->prepare("
            SELECT 
                COUNT(DISTINCT classe_code) as total_classes,
                COUNT(DISTINCT enseignant_code) as total_profs,
                (SELECT COUNT(*)
                 FROM " . TABLES::NOTES . " n
                 INNER JOIN " . TABLES::COMPOSITION_MATIERES . " cm ON cm.id_composition_matiere = n.composition_matiere_id
                 INNER JOIN " . TABLES::COMPOSITIONS . " c ON c.code_composition = n.composition_code
                 WHERE cm.matiere_code = ?
                   AND n.statut_note = '" . STATUTS::ACTIF . "'" . $notesYearFilter . ") as total_notes
            FROM " . TABLES::EMPLOIS_TEMPS . "
            WHERE matiere_code = ?
              AND (statut_emploi = '" . STATUTS::ACTIF . "' OR statut_emploi IS NULL)" . $edtYearFilter . "
        ");
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_classes' => $defaultTotalClasses, 'total_profs' => 0, 'total_notes' => 0];
    }
}
