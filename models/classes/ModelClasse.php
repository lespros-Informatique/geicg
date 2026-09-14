<?php

class ModelClasse extends BaseModel
{
    protected string $table = 'classes';
    protected string $primaryKey = 'id_classe';
    protected ?string $statusField = 'statut_classe';
    protected ?string $createdAtField = 'created_at_classe';

    /**
     * Récupère les classes actives ayant leur scolarité déjà enregistrée pour une année académique donnée
     *
     * @param string $anneeCode Code de l'année académique
     * @param string|null $affectationEtat 'affecte' ou 'non_affecte' (optionnel)
     * @return array
     */
    public function getClassesWithScolarite(string $anneeCode, ?string $affectationEtat = null): array
    {
        if (empty($anneeCode)) {
            return [];
        }

        $db = $this->getCon();

        // 1. Essai avec filtre spécifique affectation_etat si fourni
        if (!empty($affectationEtat)) {
            $sqlStrict = "
                SELECT DISTINCT c.id_classe, c.code_classe, c.libelle_classe, c.capacite_max_classe,
                                c.filiere_code, c.niveau_code, c.annee_code, c.etablissement_code,
                                f.libelle_filiere, n.libelle_niveau,
                                s.montant_scolarite, s.code_scolarite, s.affectation_etat
                FROM classes c
                INNER JOIN scolarites s ON s.filiere_code = c.filiere_code
                                       AND (s.niveau_code = c.niveau_code OR s.niveau_code IS NULL OR s.niveau_code = '')
                                       AND s.statut_scolarite = 'actif'
                                       AND s.annee_code = ?
                                       AND (s.affectation_etat = ? OR s.affectation_etat IS NULL OR s.affectation_etat = '')
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                WHERE c.statut_classe = 'actif'
                GROUP BY c.id_classe, c.code_classe, c.libelle_classe, c.capacite_max_classe,
                         c.filiere_code, c.niveau_code, c.annee_code, c.etablissement_code,
                         f.libelle_filiere, n.libelle_niveau
                ORDER BY c.libelle_classe ASC
            ";
            $stmtStrict = $db->prepare($sqlStrict);
            $stmtStrict->execute([$anneeCode, $affectationEtat]);
            $results = $stmtStrict->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (!empty($results)) {
                return $results;
            }
        }

        // 2. Recherche générale : toutes les classes actives ayant au moins une scolarité active pour l'année sélectionnée
        $sql = "
            SELECT DISTINCT c.id_classe, c.code_classe, c.libelle_classe, c.capacite_max_classe,
                            c.filiere_code, c.niveau_code, c.annee_code, c.etablissement_code,
                            f.libelle_filiere, n.libelle_niveau,
                            s.montant_scolarite, s.code_scolarite, s.affectation_etat
            FROM classes c
            INNER JOIN scolarites s ON s.filiere_code = c.filiere_code
                                   AND (s.niveau_code = c.niveau_code OR s.niveau_code IS NULL OR s.niveau_code = '')
                                   AND s.statut_scolarite = 'actif'
                                   AND s.annee_code = ?
            LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
            LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
            WHERE c.statut_classe = 'actif'
            GROUP BY c.id_classe, c.code_classe, c.libelle_classe, c.capacite_max_classe,
                     c.filiere_code, c.niveau_code, c.annee_code, c.etablissement_code,
                     f.libelle_filiere, n.libelle_niveau
            ORDER BY c.libelle_classe ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$anneeCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
