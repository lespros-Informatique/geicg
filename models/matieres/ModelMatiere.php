<?php

class ModelMatiere extends BaseModel
{
    protected string $table = 'matieres';
    protected string $primaryKey = 'id_matiere';
    protected ?string $statusField = 'statut_matiere';
    protected ?string $createdAtField = 'created_at_matiere';

    public function getAffectationsForMatiere(string $matiereCode): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT em.*, cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                   u.nom_user as nom_prof,
                   u.prenom_user as prenom_prof,
                   e.grade_enseignant
            FROM " . TABLES::ENSEIGNANT_MATIERE . " em
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = em.classe_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
            LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = em.enseignant_code
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = em.enseignant_code
            WHERE em.matiere_code = ?
            ORDER BY cl.libelle_classe ASC
        ");
        $stmt->execute([$matiereCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStatsForMatiere(string $matiereCode, int $defaultTotalClasses = 0): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT 
                COUNT(DISTINCT classe_code) as total_classes,
                COUNT(DISTINCT COALESCE(enseignant_code, user_code)) as total_profs,
                (SELECT COUNT(*) FROM " . TABLES::NOTES . " n JOIN " . TABLES::COMPOSITIONS . " c ON c.code_composition = n.composition_code WHERE c.matiere_code = ? AND n.statut_note = 'actif') as total_notes
            FROM " . TABLES::ENSEIGNANT_MATIERE . "
            WHERE matiere_code = ?
        ");
        $stmt->execute([$matiereCode, $matiereCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_classes' => $defaultTotalClasses, 'total_profs' => 0, 'total_notes' => 0];
    }
}
