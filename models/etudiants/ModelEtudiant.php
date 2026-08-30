<?php

class ModelEtudiant extends BaseModel
{
    protected string $table = 'etudiants';
    protected string $primaryKey = 'id_etudiant';
    protected ?string $statusField = 'statut_etudiant';
    protected ?string $createdAtField = 'created_at_etudiant';

    /**
     * Récupère la liste des étudiants avec leurs inscriptions annuelles et filtres dynamiques
     */
    public function getFilteredRegistry(array $filters = []): array
    {
        $pdo = $this->getCon();
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['annee_code']) && $filters['annee_code'] !== 'ALL') {
            $where[] = "i.annee_code = ?";
            $params[] = $filters['annee_code'];
        }

        if (!empty($filters['niveau_code']) && $filters['niveau_code'] !== 'ALL') {
            $where[] = "cl.niveau_code = ?";
            $params[] = $filters['niveau_code'];
        }

        if (!empty($filters['filiere_code']) && $filters['filiere_code'] !== 'ALL') {
            $where[] = "cl.filiere_code = ?";
            $params[] = $filters['filiere_code'];
        }

        if (!empty($filters['classe_code']) && $filters['classe_code'] !== 'ALL') {
            $where[] = "i.classe_code = ?";
            $params[] = $filters['classe_code'];
        }

        if (!empty($filters['statut_etudiant']) && $filters['statut_etudiant'] !== 'ALL') {
            $where[] = "e.statut_etudiant = ?";
            $params[] = $filters['statut_etudiant'];
        }

        $whereClause = implode(" AND ", $where);

        $sql = "
            SELECT 
                e.id_etudiant,
                e.code_etudiant,
                e.matricule_etudiant,
                e.nom_etudiant,
                e.prenom_etudiant,
                e.sexe_etudiant,
                e.telephone_etudiant,
                e.email_etudiant,
                e.photo_etudiant,
                e.statut_etudiant,
                i.id_inscription,
                i.code_inscription,
                i.statut_inscription,
                i.montant_scolarite_inscription,
                i.created_at_inscription,
                a.code_annee,
                a.libelle_annee,
                cl.code_classe,
                cl.libelle_classe,
                f.code_filiere,
                f.libelle_filiere,
                n.code_niveau,
                n.libelle_niveau
            FROM etudiants e
            JOIN inscriptions i ON i.etudiant_code = e.code_etudiant
            JOIN annees a ON a.code_annee = i.annee_code
            JOIN classes cl ON cl.code_classe = i.classe_code
            JOIN filieres f ON f.code_filiere = cl.filiere_code
            JOIN niveaux n ON n.code_niveau = cl.niveau_code
            WHERE {$whereClause}
            ORDER BY a.libelle_annee DESC, cl.libelle_classe ASC, e.nom_etudiant ASC, e.prenom_etudiant ASC
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getFilteredRegistry error: " . $e->getMessage());
            return [];
        }
    }
}
