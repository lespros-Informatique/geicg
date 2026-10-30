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
                                f.libelle_filiere, f.type_filiere, n.libelle_niveau,
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
                         f.libelle_filiere, f.type_filiere, n.libelle_niveau,
                         s.montant_scolarite, s.code_scolarite, s.affectation_etat
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
                            f.libelle_filiere, f.type_filiere, n.libelle_niveau,
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
                     f.libelle_filiere, f.type_filiere, n.libelle_niveau,
                     s.montant_scolarite, s.code_scolarite, s.affectation_etat
            ORDER BY c.libelle_classe ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$anneeCode, $anneeCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Reconduit toutes les classes actives d'une année source vers une année de destination.
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

        $stmtCheck = $db->prepare("SELECT id_classe FROM classes WHERE libelle_classe = ? AND annee_code = ? LIMIT 1");
        $stmtInsert = $db->prepare("
            INSERT INTO classes 
            (code_classe, libelle_classe, capacite_max_classe, cycle_code, filiere_code, etablissement_code, niveau_code, annee_code, statut_classe, created_at_classe, user_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW(), ?)
        ");

        foreach ($classesSource as $cls) {
            // Vérifier si la classe existe déjà dans l'année de destination
            $stmtCheck->execute([$cls['libelle_classe'], $anneeCibleCode]);
            if ($stmtCheck->fetch()) {
                $skippedCount++;
                continue;
            }

            // Générer un nouveau code unique
            $newCode = $validator->generateCode('classes', 'code_classe', 'CLA-', 8);
            
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

    public function getAllWithDetails(?string $anneeCode = null): array
    {
        $sql = "SELECT c.*, 
                       f.libelle_filiere, f.slug_filiere,
                       n.libelle_niveau, n.slug_niveau,
                       cy.libelle_cycle, cy.slug_cycle,
                       a.libelle_annee
                FROM " . TABLES::CLASSES . " c
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
                LEFT JOIN " . TABLES::CYCLES . " cy ON cy.code_cycle = c.cycle_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = c.annee_code";
        $params = [];
        if (!empty($anneeCode)) {
            $sql .= " WHERE c.annee_code = ?";
            $params[] = $anneeCode;
        }
        $sql .= " ORDER BY cy.libelle_cycle ASC, f.libelle_filiere ASC, n.libelle_niveau ASC, c.libelle_classe ASC";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getParcoursPivots(): array
    {
        $sql = "
            SELECT fc.*, 
                   f.libelle_filiere, f.slug_filiere,
                   c.libelle_cycle, c.slug_cycle,
                   n.libelle_niveau, n.slug_niveau
            FROM " . TABLES::FILIERE_CYCLES . " fc
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = fc.filiere_code
            LEFT JOIN " . TABLES::CYCLES . " c ON c.code_cycle = fc.cycle_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = fc.niveau_code
            WHERE fc.statut_filiere_cycle = 'actif'
            ORDER BY c.libelle_cycle ASC, f.libelle_filiere ASC, n.libelle_niveau ASC
        ";
        return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getCycleForFiliereNiveau(string $filiereCode, ?string $niveauCode = null): ?string
    {
        $stmt = $this->getCon()->prepare("
            SELECT cycle_code FROM " . TABLES::FILIERE_CYCLES . " 
            WHERE filiere_code = ? AND (niveau_code = ? OR niveau_code IS NULL OR niveau_code = '') 
            LIMIT 1
        ");
        $stmt->execute([$filiereCode, $niveauCode ?? '']);
        return $stmt->fetchColumn() ?: null;
    }

    public function getFiliereAndNiveauNames(string $filiereCode, string $niveauCode): array
    {
        $stmtF = $this->getCon()->prepare("SELECT libelle_filiere FROM " . TABLES::FILIERES . " WHERE code_filiere = ?");
        $stmtF->execute([$filiereCode]);
        $fName = $stmtF->fetchColumn() ?: '';

        $stmtN = $this->getCon()->prepare("SELECT libelle_niveau FROM " . TABLES::NIVEAUX . " WHERE code_niveau = ?");
        $stmtN->execute([$niveauCode]);
        $nName = $stmtN->fetchColumn() ?: '';

        return ['filiere' => $fName, 'niveau' => $nName];
    }

    public function getClasseWithDetails($id): ?array
    {
        $stmt = $this->getCon()->prepare("
            SELECT c.*, 
                   f.libelle_filiere,
                   n.libelle_niveau,
                   a.libelle_annee
            FROM " . TABLES::CLASSES . " c
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
            LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = c.annee_code
            WHERE c.id_classe = ? OR c.code_classe = ?
            LIMIT 1
        ");
        $stmt->execute([$id, (string)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getEtudiantsForClasse(string $classeCode): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT e.*, ins.created_at_inscription, ins.statut_inscription, ins.code_inscription, ins.affectation_etat
            FROM " . TABLES::ETUDIANTS . " e
            INNER JOIN " . TABLES::INSCRIPTIONS . " ins ON ins.etudiant_code = e.code_etudiant
            WHERE ins.classe_code = ? AND (ins.statut_inscription != 'annule' OR ins.statut_inscription IS NULL)
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
        ");
        $stmt->execute([$classeCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getMatieresForClasse(string $classeCode): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT em.*, m.libelle_matiere, m.code_matiere,
                   u.nom_user as nom_prof,
                   u.prenom_user as prenom_prof,
                   e.grade_enseignant
            FROM " . TABLES::ENSEIGNANT_MATIERE . " em
            LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = em.matiere_code
            LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = em.enseignant_code
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = em.enseignant_code
            WHERE em.classe_code = ?
            ORDER BY m.libelle_matiere ASC
        ");
        $stmt->execute([$classeCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
