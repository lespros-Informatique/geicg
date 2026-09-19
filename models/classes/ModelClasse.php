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
                                       AND s.affectation_etat = ?
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                WHERE c.statut_classe = 'actif' AND c.annee_code = ?
                GROUP BY c.id_classe, c.code_classe, c.libelle_classe, c.capacite_max_classe,
                         c.filiere_code, c.niveau_code, c.annee_code, c.etablissement_code,
                         f.libelle_filiere, n.libelle_niveau
                ORDER BY c.libelle_classe ASC
            ";
            $stmtStrict = $db->prepare($sqlStrict);
            $stmtStrict->execute([$anneeCode, $affectationEtat, $anneeCode]);
            return $stmtStrict->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
            WHERE c.statut_classe = 'actif' AND c.annee_code = ?
            GROUP BY c.id_classe, c.code_classe, c.libelle_classe, c.capacite_max_classe,
                     c.filiere_code, c.niveau_code, c.annee_code, c.etablissement_code,
                     f.libelle_filiere, n.libelle_niveau
            ORDER BY c.libelle_classe ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$anneeCode, $anneeCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Reconduit toutes les classes actives d'une année source vers une année cible.
     */
    public function reconduireClassesAnnee(string $anneeSourceCode, string $anneeCibleCode, ?string $etabCode = null, ?string $userCode = null): array
    {
        $db = $this->getCon();
        $validator = new Validator();

        // 1. Récupérer toutes les classes actives de l'année source
        $stmt = $db->prepare("SELECT * FROM classes WHERE annee_code = ? AND statut_classe = 'actif'");
        $stmt->execute([$anneeSourceCode]);
        $classesSource = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($classesSource)) {
            return [
                'success' => false,
                'message' => "Aucune classe active trouvée pour l'année source sélectionnée.",
                'count' => 0
            ];
        }

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($classesSource as $cls) {
            // Vérifier si la classe existe déjà dans l'année cible
            $stmtCheck = $db->prepare("SELECT id_classe FROM classes WHERE libelle_classe = ? AND annee_code = ? LIMIT 1");
            $stmtCheck->execute([$cls['libelle_classe'], $anneeCibleCode]);
            if ($stmtCheck->fetch()) {
                $skippedCount++;
                continue;
            }

            // Générer un nouveau code unique
            $newCode = $validator->generateCode('classes', 'code_classe', 'CLA-', 8);
            
            $stmtInsert = $db->prepare("
                INSERT INTO classes 
                (code_classe, libelle_classe, capacite_max_classe, cycle_code, filiere_code, etablissement_code, niveau_code, annee_code, statut_classe, created_at_classe, user_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW(), ?)
            ");
            
            $inserted = $stmtInsert->execute([
                $newCode,
                $cls['libelle_classe'],
                $cls['capacite_max_classe'],
                $cls['cycle_code'] ?? null,
                $cls['filiere_code'],
                $etabCode ?: $cls['etablissement_code'],
                $cls['niveau_code'],
                $anneeCibleCode,
                $userCode ?: $cls['user_code']
            ]);

            if ($inserted) {
                $createdCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Reconduction terminée : {$createdCount} classe(s) créée(s), {$skippedCount} ignorée(s) (déjà existantes).",
            'count' => $createdCount,
            'skipped' => $skippedCount
        ];
    }
}
