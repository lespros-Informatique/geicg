<?php

class ForeignKeyValidator
{
    /**
     * Registre global des clés étrangères et de leurs correspondances en base de données.
     */
    private static array $fkRegistry = [
        'annee_code' => [
            'table' => 'annees',
            'field' => 'code_annee',
            'label' => 'Année Académique'
        ],
        'etablissement_code' => [
            'table' => 'etablissements',
            'field' => 'code_etablissement',
            'label' => 'Établissement'
        ],
        'user_code' => [
            'table' => 'users',
            'field' => 'code_user',
            'label' => 'Utilisateur'
        ],
        'etudiant_code' => [
            'table' => 'etudiants',
            'field' => 'code_etudiant',
            'label' => 'Étudiant'
        ],
        'inscription_code' => [
            'table' => 'inscriptions',
            'field' => 'code_inscription',
            'label' => 'Dossier d\'Inscription'
        ],
        'classe_code' => [
            'table' => 'classes',
            'field' => 'code_classe',
            'label' => 'Classe'
        ],
        'filiere_code' => [
            'table' => 'filieres',
            'field' => 'code_filiere',
            'label' => 'Filière'
        ],
        'niveau_code' => [
            'table' => 'niveaux',
            'field' => 'code_niveau',
            'label' => 'Niveau d\'Étude'
        ],
        'cycle_code' => [
            'table' => 'cycles',
            'field' => 'code_cycle',
            'label' => 'Cycle Académique'
        ],
        'matiere_code' => [
            'table' => 'matieres',
            'field' => 'code_matiere',
            'label' => 'Matière'
        ],
        'enseignant_code' => [
            'table' => 'enseignants',
            'field' => 'code_enseignant',
            'label' => 'Enseignant'
        ],
        'type_depense_code' => [
            'table' => 'type_depenses',
            'field' => 'code_type_depense',
            'label' => 'Type de Dépense'
        ],
        'scolarite_code' => [
            'table' => 'scolarites',
            'field' => 'code_scolarite',
            'label' => 'Grille Tarifaire / Scolarité'
        ],
        'tranche_code' => [
            'table' => 'tranches_scolarite',
            'field' => 'code_tranche_scolarite',
            'label' => 'Tranche de Scolarité'
        ],
        'role_code' => [
            'table' => 'roles',
            'field' => 'code_role',
            'label' => 'Rôle / Privilège'
        ],
        'permission_code' => [
            'table' => 'permissions',
            'field' => 'code_permission',
            'label' => 'Permission Système'
        ],
        'salle_code' => [
            'table' => 'salles',
            'field' => 'code_salle',
            'label' => 'Salle de Classe'
        ],
        'parent_code' => [
            'table' => 'parents',
            'field' => 'code_parent',
            'label' => 'Parent / Tuteur'
        ],
        'accessoire_code' => [
            'table' => 'accessoires',
            'field' => 'code_accessoire',
            'label' => 'Accessoire / Fourniture'
        ],
        'piece_code' => [
            'table' => 'pieces_fournir',
            'field' => 'code_piece',
            'label' => 'Pièce à Fournir'
        ],
        'session_caisse_code' => [
            'table' => 'ouvertures_caisse',
            'field' => 'code_ouverture',
            'label' => 'Session de Caisse'
        ],
        'ouverture_caisse_code' => [
            'table' => 'ouvertures_caisse',
            'field' => 'code_ouverture',
            'label' => 'Ouverture de Caisse'
        ],
        'cloture_code' => [
            'table' => 'clotures_caisse',
            'field' => 'code_cloture',
            'label' => 'Clôture de Caisse'
        ],
        'composition_code' => [
            'table' => 'compositions',
            'field' => 'code_composition',
            'label' => 'Composition / Évaluation'
        ],
        'dossier_etudiant_code' => [
            'table' => 'dossier_etudiant',
            'field' => 'code_dossier_etudiant',
            'label' => 'Dossier Étudiant'
        ],
        'etudiants_code' => [
            'table' => 'etudiants',
            'field' => 'code_etudiant',
            'label' => 'Étudiant'
        ],
        'galerie_code' => [
            'table' => 'galeries',
            'field' => 'code_galerie',
            'label' => 'Galerie Médias'
        ]
    ];

    /**
     * Définition des clés étrangères obligatoires par table d'insertion
     */
    private static array $requiredFksByTable = [
        'inscriptions' => ['etudiant_code', 'classe_code', 'annee_code', 'etablissement_code'],
        'paiements' => ['inscription_code', 'annee_code', 'etablissement_code'],
        'depenses' => ['type_depense_code', 'annee_code', 'etablissement_code'],
        'absences' => ['etudiant_code', 'classe_code', 'annee_code', 'etablissement_code'],
        'notes' => ['etudiant_code', 'matiere_code', 'classe_code', 'annee_code', 'etablissement_code'],
        'classes' => ['filiere_code', 'niveau_code', 'annee_code', 'etablissement_code'],
        'annees' => ['etablissement_code'],
        'emplois_temps' => ['classe_code', 'matiere_code', 'enseignant_code', 'annee_code', 'etablissement_code'],
        'enseignant_matiere' => ['enseignant_code', 'matiere_code', 'classe_code', 'annee_code', 'etablissement_code'],
        'filiere_cycles' => ['filiere_code', 'cycle_code', 'etablissement_code', 'annee_code'],
        'filiere_niveaux' => ['filiere_code', 'niveau_code', 'etablissement_code'],
        'scolarites' => ['filiere_code', 'niveau_code', 'annee_code', 'etablissement_code'],
        'tranches_scolarite' => ['scolarite_code', 'annee_code', 'etablissement_code'],
        'accessoire_inscription' => ['inscription_code', 'accessoire_code', 'annee_code', 'etablissement_code'],
        'relances_impayes' => ['inscription_code', 'annee_code', 'etablissement_code'],
        'clotures_caisse' => ['annee_code', 'etablissement_code'],
        'ouvertures_caisse' => ['annee_code', 'etablissement_code'],
        'sessions_caisse' => ['user_code', 'annee_code', 'etablissement_code'],
        'user_roles' => ['user_code', 'role_code'],
        'role_permissions' => ['role_code', 'permission_code'],
        'user_permissions' => ['user_code', 'permission_code'],
        'etudiants' => ['etablissement_code'],
        'enseignants' => ['etablissement_code'],
        'filieres' => ['etablissement_code'],
        'cycles' => ['etablissement_code'],
        'matieres' => ['filiere_code', 'niveau_code', 'etablissement_code'],
        'compositions' => ['annee_code', 'etablissement_code'],
        'composition_matieres' => ['composition_code', 'matiere_code'],
        'composition_niveau_filiere' => ['composition_code', 'filiere_code', 'niveau_code'],
        'accessoires' => ['etablissement_code'],
        'dossier_etudiant' => ['inscription_code', 'etudiant_code', 'piece_code', 'etablissement_code'],
        'parents' => ['etablissement_code'],
        'piece_fournir_cycle' => ['piece_code', 'cycle_code', 'etablissement_code'],
        'pieces_fournir' => ['etablissement_code'],
        'semestres' => ['annee_code', 'etablissement_code'],
        'documents' => ['etablissement_code'],
        'fonctions' => ['etablissement_code'],
        'galeries' => ['etablissement_code'],
        'evenements' => ['etablissement_code'],
        'salles' => ['etablissement_code'],
        'type_depenses' => ['etablissement_code']
    ];

    /**
     * Valide l'ensemble des clés étrangères d'un payload d'insertion pour une table donnée.
     * Retourne une chaîne d'erreur si la validation échoue, ou null si l'insertion peut procéder.
     */
    public static function validate(PDO $pdo, string $table, array $data): ?string
    {
        $tableKey = strtolower(trim($table));
        $requiredKeys = self::$requiredFksByTable[$tableKey] ?? [];

        // 1. Vérification des clés obligatoires pour cette table
        foreach ($requiredKeys as $reqKey) {
            $val = isset($data[$reqKey]) ? trim((string)$data[$reqKey]) : '';
            if ($val === '') {
                $info = self::$fkRegistry[$reqKey] ?? null;
                $label = $info ? $info['label'] : $reqKey;
                return "Opération interrompue : La clé étrangère '{$label}' (champ '{$reqKey}') est obligatoire mais absente de la requête pour la table '{$table}'.";
            }
        }

        // 2. Vérification de l'existence des clés étrangères transmises dans le payload
        foreach ($data as $col => $value) {
            $colKey = strtolower(trim($col));

            // Ne pas traiter comme FK si le champ correspond à la clé primaire métier de la table elle-même
            if (self::isSelfPrimaryKey($tableKey, $colKey)) {
                continue;
            }

            if (isset(self::$fkRegistry[$colKey])) {
                $val = trim((string)$value);
                // Si une clé existe dans le registre et qu'une valeur est transmise (non vide)
                if ($val !== '') {
                    $err = self::checkKeyExistence($pdo, $colKey, $val);
                    if ($err !== null) {
                        return $err;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Valide une seule clé étrangère individuelle.
     */
    public static function validateSingleKey(PDO $pdo, string $fkCol, $val, ?string $customLabel = null): ?string
    {
        $value = trim((string)$val);
        $info = self::$fkRegistry[$fkCol] ?? null;
        $label = $customLabel ?? ($info['label'] ?? $fkCol);

        if ($value === '') {
            return "Opération interrompue : La clé étrangère '{$label}' (champ '{$fkCol}') est obligatoire mais absente.";
        }

        if ($info) {
            return self::checkKeyExistence($pdo, $fkCol, $value, $label);
        }

        return null;
    }

    /**
     * Vérifie si la clé passée est la clé primaire propre à la table (et non une FK)
     */
    private static function isSelfPrimaryKey(string $table, string $col): bool
    {
        // Exemple: table 'classes' a sa clé 'code_classe', table 'annees' a 'code_annee'
        $singularTable = rtrim($table, 's');
        if ($table === 'annees') $singularTable = 'annee';
        if ($table === 'filieres') $singularTable = 'filiere';

        return ($col === "code_{$singularTable}" || $col === "id_{$singularTable}");
    }

    /**
     * Vérifie l'existence d'une valeur dans la table parente référencée par la FK
     */
    private static function checkKeyExistence(PDO $pdo, string $fkCol, string $val, ?string $customLabel = null): ?string
    {
        $info = self::$fkRegistry[$fkCol] ?? null;
        if (!$info) {
            return null;
        }

        $targetTable = $info['table'];
        $targetField = $info['field'];
        $label = $customLabel ?? $info['label'];

        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$targetTable}` WHERE `{$targetField}` = ?");
            $stmt->execute([$val]);
            $count = (int)$stmt->fetchColumn();

            if ($count === 0) {
                return "Opération interrompue : La référence '{$label}' ('{$val}') est introuvable ou n'existe pas dans le système (table {$targetTable}).";
            }
        } catch (Exception $e) {
            error_log("ForeignKeyValidator error checking {$fkCol}='{$val}': " . $e->getMessage());
            return "Opération interrompue : Erreur lors de la vérification de la clé étrangère '{$label}'.";
        }

        return null;
    }
}
