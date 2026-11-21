<?php
/**
 * GEICG - Classe Seeder Paramétrable (Multi-Années Académiques)
 *
 * Remplit la base de données avec des données métier 100% cohérentes sans jamais
 * modifier la structure de la base de données ni du projet.
 *
 * Utilisation CLI :
 *   php database/DatabaseSeeder.php
 *   php database/DatabaseSeeder.php --years=2023-2024,2024-2025,2025-2026 --students=10 --fresh
 *   php database/DatabaseSeeder.php --years=2023-2024,2024-2025,2025-2026 --students=15 --teachers=10 --depenses=12 --seed=2026 --fresh
 *   php database/DatabaseSeeder.php --clear                  # Vide les données métier/seed (sans ré-injecter)
 *   php database/DatabaseSeeder.php --clear --clear-years    # Vide aussi la table annees
 *   php database/DatabaseSeeder.php --help                   # Affiche l'aide des options
 *
 * Utilisation programmatique :
 *   require_once __DIR__ . '/DatabaseSeeder.php';
 *   $seeder = new DatabaseSeeder($pdo, [...]);
 *   $summary = $seeder->run();     // Remplir
 *   $deleted = $seeder->clear();   // Vider uniquement
 */

if (!class_exists('Database')) {
    require_once __DIR__ . '/../config/Database.php';
}

class DatabaseSeeder
{
    private PDO $pdo;
    private array $config;
    private string $etabCode = '';
    private string $adminCode = '';
    private string $caissierCode = '';
    private string $scolariteCode = '';
    private string $comptableCode = '';
    private array $stats = [];
    private int $seq = 1;

    private const NOMS_FAMILLE = [
        'KOUASSI', 'KONE', 'TRAORE', 'YAO', 'N\'GUESSAN', 'DIALLO', 'BAMBA', 'KOUAME',
        'OUATTARA', 'TOURE', 'SORO', 'DIABATE', 'COULIBALY', 'AKA', 'KOFFI', 'ASSI',
        'GNAGNE', 'DOUMBIA', 'CISSE', 'SANGARE', 'KOUADIO', 'TANOH', 'BROU', 'ZADI',
        'GNAHOUE', 'FOTIE', 'BAKAYOKO', 'SYLLA', 'KEITA', 'CAMARA', 'MEITE', 'AKE'
    ];

    private const PRENOMS_M = [
        'Jean-Marc', 'Aboubacar', 'Franck', 'Ibrahim', 'Kévin', 'Moussa', 'Cédric',
        'Youssouf', 'Fabrice', 'Ismaël', 'Junior', 'Boris', 'Souleymane', 'Hermann',
        'Landry', 'Didier', 'Seydou', 'Armel', 'Romaric', 'Stéphane', 'Wilfried', 'Adama'
    ];

    private const PRENOMS_F = [
        'Awa', 'Mariam', 'Christelle', 'Fatoumata', 'Grace', 'Aminata', 'Prisca',
        'Aïcha', 'Nadège', 'Estelle', 'Salimata', 'Clarisse', 'Mireille', 'Rokia',
        'Inès', 'Bintou', 'Sandrine', 'Raïssa', 'Kadiatou', 'Solange', 'Vanessa', 'Carine'
    ];

    private const VILLES = [
        'Bouaké', 'Abidjan', 'Yamoussoukro', 'Korhogo', 'Daloa', 'San-Pédro', 'Man', 'Gagnoa', 'Divo', 'Abengourou'
    ];

    private const QUARTIERS = [
        'Bouaké - Kennedy', 'Bouaké - Commerce', 'Bouaké - Air France', 'Bouaké - Nimbo',
        'Bouaké - Koko', 'Bouaké - Broukro', 'Bouaké - Dar-es-Salam', 'Bouaké - Ahougnanssou',
        'Bouaké - Zone Industrielle', 'Bouaké - Sokoura'
    ];

    private const PROFESSIONS = [
        'Commerçant(e)', 'Enseignant(e)', 'Fonctionnaire', 'Comptable', 'Technicien Supérieur',
        'Transporteur', 'Entrepreneur', 'Infirmier(ère)', 'Chauffeur', 'Agriculteur', 'Mécanicien', 'Couturière'
    ];

    private const LYCEES_ORIGINE = [
        'Lycée Classique de Bouaké', 'Lycée Moderne Jeunes Filles Bouaké', 'Lycée Technique de Bouaké',
        'Lycée Municipal Djibo Sounkalo', 'Lycée Moderne de Yamoussoukro', 'Collège Saint-Viateur Bouaké',
        'Lycée Moderne de Korhogo', 'Lycée Moderne 1 de Daloa', 'Groupe Scolaire Avicenne'
    ];

    private const MODES_GUICHET = ['Espèces', 'Espèces', 'Espèces', 'Wave', 'Orange Money', 'MTN Money', 'Chèque'];
    private const MODES_EN_LIGNE = ['Wave', 'Orange Money', 'MTN Money', 'Moov Money', 'Virement Bancaire'];

    public function __construct(?PDO $pdo = null, array $options = [])
    {
        $this->pdo = $pdo ?? (new Database())->getCon();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $defaults = [
            'years'              => ['2023-2024', '2024-2025', '2025-2026'],
            'students_per_class' => 10,
            'teachers'           => 8,
            'depenses_per_year'  => 10,
            'sessions_per_year'  => 4,
            'fresh'              => false,
            'seed'               => 2026,
            'verbose'            => true,
        ];

        $this->config = array_merge($defaults, $options);
        $this->config['years'] = array_values(array_unique(array_filter(array_map('trim', (array)$this->config['years']))));
        sort($this->config['years']);
        $this->config['students_per_class'] = max(2, (int)$this->config['students_per_class']);
        $this->config['teachers']           = max(4, (int)$this->config['teachers']);
        $this->config['depenses_per_year']  = max(2, (int)$this->config['depenses_per_year']);
        $this->config['sessions_per_year']  = max(2, (int)$this->config['sessions_per_year']);
    }

    /**
     * Point d'entrée principal
     */
    public function run(): array
    {
        mt_srand((int)$this->config['seed']);
        $this->stats = [];
        $this->seq = 1;

        $this->log("================================================================");
        $this->log("  GEICG DatabaseSeeder — Années : " . implode(', ', $this->config['years']));
        $this->log("  Options : {$this->config['students_per_class']} étudiants/classe, {$this->config['teachers']} enseignants, fresh=" . ($this->config['fresh'] ? 'oui' : 'non'));
        $this->log("================================================================");

        $this->resolveInstitutionalContext();

        if ($this->config['fresh']) {
            $this->purgeTransactionalData();
        }

        $this->pdo->beginTransaction();
        try {
            $this->ensureBaremesBulletin();
            $this->ensureStaffUsers();
            $teachers = $this->seedTeachers();
            $catalog  = $this->ensureAcademicCatalog();
            $pieces   = $this->seedPiecesFournir($catalog['bts_cycle_code']);
            $kits     = $this->seedAccessoires();

            // Registre longitudinal d'étudiants (suivi 1A -> 2A sur plusieurs années)
            // [filiere_code => [array of ['code_etudiant', 'affectation_etat', 'sexe']]]
            $cohort1AByYear = [];
            $studentCounter = 1;
            $inscriptionsWithArrears = []; // pour générer quelques règlements d'arriérés sur l'année suivante

            $totalYears = count($this->config['years']);
            foreach ($this->config['years'] as $yearIndex => $yearLabel) {
                $isActiveYear = ($yearIndex === $totalYears - 1);
                $yearStatus   = $isActiveYear ? 'actif' : 'cloture';

                $this->log("\n>>> Traitement de l'année académique {$yearLabel} ({$yearStatus})...");

                $yearRow = $this->ensureAnnee($yearLabel, $yearStatus);
                $anneeCode = $yearRow['code_annee'];
                $startYear = (int)substr($yearLabel, 0, 4);

                $this->ensureUserAnneeAccess($anneeCode);
                $semestres = $this->seedSemestres($anneeCode, $startYear, $isActiveYear);
                $classes   = $this->seedClasses($anneeCode, $catalog);
                $maquettes = $this->seedMaquettes($anneeCode, $catalog);
                $tarifs    = $this->seedScolaritesAndTranches($anneeCode, $startYear, $catalog);
                $annexes   = $this->seedFraisAnnexes($anneeCode, $startYear);
                $this->seedAccessoireCibles($anneeCode, $kits, $catalog);
                $emploisByClasse = $this->seedEmploisDuTemps($anneeCode, $classes, $maquettes, $teachers, $catalog['salles']);

                // Inscriptions (progression 1A -> 2A + nouveaux 1A)
                $prevYearLabel = $yearIndex > 0 ? $this->config['years'][$yearIndex - 1] : null;
                $inscriptionsByClasse = $this->seedStudentsAndInscriptions(
                    $anneeCode,
                    $yearLabel,
                    $startYear,
                    $classes,
                    $tarifs,
                    $cohort1AByYear,
                    $prevYearLabel,
                    $studentCounter
                );

                // Dossiers administratifs & Retraits de kits
                $this->seedDossiersEtudiants($inscriptionsByClasse, $pieces, $startYear, $isActiveYear);
                $this->seedAccessoireInscriptions($anneeCode, $inscriptionsByClasse, $kits, $startYear, $isActiveYear);

                // Sessions de caisse & Paiements (Scolarité + Frais Annexes + Arriérés)
                $sessions = $this->seedSessionsCaisse($anneeCode, $startYear, $isActiveYear);
                $newArrears = $this->seedPaiementsAndRelances(
                    $anneeCode,
                    $startYear,
                    $isActiveYear,
                    $inscriptionsByClasse,
                    $tarifs,
                    $annexes,
                    $sessions,
                    $inscriptionsWithArrears
                );
                $inscriptionsWithArrears = array_merge($inscriptionsWithArrears, $newArrears);

                // Dépenses de l'année
                $this->seedDepenses($anneeCode, $startYear, $isActiveYear, $catalog['type_depenses']);

                // Évaluations, Compositions, Notes & Absences
                $this->seedCompositionsAndNotes($anneeCode, $startYear, $isActiveYear, $semestres, $classes, $maquettes, $inscriptionsByClasse);
                $this->seedAbsences($anneeCode, $startYear, $inscriptionsByClasse, $emploisByClasse);

                // Documents pédagogiques de l'année
                $this->seedDocuments($anneeCode, $startYear, $catalog);
            }

            // Événements institutionnels
            $this->seedEvenements();

            // S'assurer que toute année future hors liste (ex: 2026-2027) reste en 'planifie'
            $lastYear = end($this->config['years']);
            $stmtPlan = $this->pdo->prepare("UPDATE annees SET statut_annee = 'planifie' WHERE libelle_annee > ? AND statut_annee = 'actif'");
            $stmtPlan->execute([$lastYear]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        $this->log("\n================================================================");
        $this->log("  SEED TERMINÉ AVEC SUCCÈS (0 modification de schéma)");
        $this->log("================================================================");
        foreach ($this->stats as $tbl => $cnt) {
            $this->log(sprintf("  + %-30s : %d ligne(s)", $tbl, $cnt));
        }

        return $this->stats;
    }

    private function inc(string $table, int $n = 1): void
    {
        $this->stats[$table] = ($this->stats[$table] ?? 0) + $n;
    }

    private function log(string $msg): void
    {
        if (!empty($this->config['verbose'])) {
            echo $msg . PHP_EOL;
        }
    }

    private function genCode(string $prefix): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $rand = '';
        for ($i = 0; $i < 6; $i++) {
            $rand .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $prefix . $rand . str_pad((string)($this->seq++ % 100), 2, '0', STR_PAD_LEFT);
    }

    private function pick(array $items)
    {
        return $items[mt_rand(0, count($items) - 1)];
    }

    private function resolveInstitutionalContext(): void
    {
        $etab = $this->pdo->query("SELECT code_etablissement FROM etablissements ORDER BY id_etablissement ASC LIMIT 1")->fetchColumn();
        if (!$etab) {
            throw new RuntimeException("Aucun établissement trouvé dans la table `etablissements`.");
        }
        $this->etabCode = (string)$etab;

        $admin = $this->pdo->query("
            SELECT u.code_user
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_code = u.code_user
            WHERE ur.role_code IN ('ROLE_SUPERADMIN', 'all_control')
            ORDER BY u.id_user ASC
            LIMIT 1
        ")->fetchColumn();
        if (!$admin) {
            $admin = $this->pdo->query("SELECT code_user FROM users ORDER BY id_user ASC LIMIT 1")->fetchColumn();
        }
        if (!$admin) {
            throw new RuntimeException("Aucun utilisateur administrateur trouvé dans la table `users`.");
        }
        $this->adminCode = (string)$admin;
    }

    /**
     * Vide uniquement les données métier/transactionnelles sans ré-injecter.
     * Préserve intactes : structure DB, etablissements, configurations, RBAC (roles/permissions/role_permissions),
     * comptes utilisateurs existants (hors SEED-*), cycles, filieres, niveaux, salles, type_depenses.
     */
    public function clear(bool $includeYears = false): array
    {
        $this->log("================================================================");
        $this->log("  GEICG DatabaseSeeder — VIDAGE DES DONNÉES MÉTIER");
        $this->log("================================================================");
        $deleted = $this->purgeTransactionalData($includeYears);
        $this->log("================================================================");
        $this->log("  VIDAGE TERMINÉ AVEC SUCCÈS (schéma & RBAC 100% préservés)");
        $this->log("================================================================");
        foreach ($deleted as $tbl => $cnt) {
            if ($cnt > 0) {
                $this->log(sprintf("  - %-30s : %d ligne(s) supprimée(s)", $tbl, $cnt));
            }
        }
        return $deleted;
    }

    /**
     * Purge uniquement les données transactionnelles et académiques de test.
     * Préserve intactes : etablissements, configurations, roles, permissions, role_permissions,
     * users existants (admin/dir), cycles, filieres, niveaux, salles, type_depenses.
     */
    private function purgeTransactionalData(bool $includeYears = false): array
    {
        $this->log("[PURGE] Nettoyage des données métier/transactionnelles (schéma et RBAC préservés)...");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $tablesToClear = [
            'notes',
            'absences',
            'composition_matieres',
            'composition_niveau_filiere',
            'compositions',
            'relances_impayes',
            'paiements',
            'sessions_caisse',
            'depenses',
            'dossier_etudiant',
            'accessoire_inscription',
            'accessoire_niveau_filiere',
            'accessoires',
            'piece_fournir_cycle',
            'pieces_fournir',
            'inscriptions',
            'parents',
            'etudiants',
            'emplois_temps',
            'enseignant_matiere',
            'enseignants',
            'tranches_scolarite',
            'scolarites',
            'frais_annexes',
            'maquettes_pedagogiques',
            'classes',
            'semestres',
            'documents',
            'evenements',
            'user_annee_acces',
        ];
        if ($includeYears) {
            $tablesToClear[] = 'annees';
        }

        $deleted = [];
        foreach ($tablesToClear as $tbl) {
            $deleted[$tbl] = (int)$this->pdo->exec("DELETE FROM `{$tbl}`");
        }

        // Supprimer uniquement les comptes seedés précédemment (préfixe SEED-)
        $deleted['user_roles (SEED-*)'] = (int)$this->pdo->exec("DELETE FROM user_roles WHERE user_code LIKE 'SEED-%'");
        $deleted['users (SEED-*)']      = (int)$this->pdo->exec("DELETE FROM users WHERE code_user LIKE 'SEED-%'");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        return $deleted;
    }

    private function ensureStaffUsers(): void
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $staff = [
            ['SEED-CAIS001', 'KOUADIO', 'Aya Mireille', 'caissier@eicg.ci', 'F', '0707112233', 'ROLE_CAISSIER'],
            ['SEED-SCOL001', 'DIABATE', 'Moussa', 'scolarite@eicg.ci', 'M', '0505223344', 'ROLE_SCOLARITE'],
            ['SEED-COMP001', 'N\'GUESSAN', 'Franck Olivier', 'comptable@eicg.ci', 'M', '0101334455', 'ROLE_COMPTABLE'],
        ];

        $stmtCheck = $this->pdo->prepare("SELECT code_user FROM users WHERE email_user = ? LIMIT 1");
        $stmtIns = $this->pdo->prepare("
            INSERT INTO users (code_user, matricule_user, nom_user, prenom_user, email_user, sexe_user, password_user, telephone_user, etablissement_code, created_at_user, statut_user)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'actif')
        ");
        $stmtRole = $this->pdo->prepare("
            INSERT IGNORE INTO user_roles (user_code, role_code, create_permission, edit_permission, show_permission, delete_permission)
            VALUES (?, ?, 1, 1, 1, 0)
        ");

        foreach ($staff as $s) {
            $stmtCheck->execute([$s[3]]);
            $existing = $stmtCheck->fetchColumn();
            if ($existing) {
                $code = (string)$existing;
            } else {
                $code = $s[0];
                $stmtIns->execute([$code, 'MAT-' . substr($code, -4), $s[1], $s[2], $s[3], $s[4], $hash, $s[5], $this->etabCode]);
                $this->inc('users');
            }
            $stmtRole->execute([$code, $s[6]]);

            if ($s[6] === 'ROLE_CAISSIER') $this->caissierCode = $code;
            if ($s[6] === 'ROLE_SCOLARITE') $this->scolariteCode = $code;
            if ($s[6] === 'ROLE_COMPTABLE') $this->comptableCode = $code;
        }
    }

    private function seedTeachers(): array
    {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $profiles = [
            ['COULIBALY', 'Sonan Hamed', 'M', 'Docteur en Informatique', 'permanent'],
            ['KOFFI', 'Yao Serge', 'M', 'Ingénieur Systèmes & Réseaux', 'permanent'],
            ['BAMBA', 'Fatoumata', 'F', 'Professeur Certifié de Mathématiques', 'permanent'],
            ['OUATTARA', 'Issouf', 'M', 'Expert Comptable & Gestion', 'vacataire'],
            ['ASSI', 'N\'Cho Aimée', 'F', 'Maître de Conférences en Droit', 'vacataire'],
            ['TRAORE', 'Lacina', 'M', 'Professeur de Lettres Modernes', 'permanent'],
            ['KOUAME', 'Affoué Solange', 'F', 'Consultante RH & Communication', 'prestataire'],
            ['DIALLO', 'Mamadou', 'M', 'Ingénieur Développement Web & BD', 'permanent'],
            ['SANGARE', 'Cheick Oumar', 'M', 'Professeur d\'Anglais des Affaires', 'vacataire'],
            ['ZADI', 'Gérard', 'M', 'Consultant Marketing Stratégique', 'prestataire'],
        ];

        $count = min($this->config['teachers'], count($profiles));
        $teachers = [];

        $stmtCheckU = $this->pdo->prepare("SELECT code_user FROM users WHERE email_user = ? LIMIT 1");
        $stmtInsU = $this->pdo->prepare("
            INSERT INTO users (code_user, matricule_user, nom_user, prenom_user, email_user, sexe_user, password_user, telephone_user, etablissement_code, created_at_user, statut_user)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'actif')
        ");
        $stmtRole = $this->pdo->prepare("
            INSERT IGNORE INTO user_roles (user_code, role_code, create_permission, edit_permission, show_permission, delete_permission)
            VALUES (?, 'ROLE_ENSEIGNANT', 1, 1, 1, 0)
        ");
        $stmtCheckE = $this->pdo->prepare("SELECT code_enseignant FROM enseignants WHERE code_enseignant = ? LIMIT 1");
        $stmtInsE = $this->pdo->prepare("
            INSERT INTO enseignants (code_enseignant, statut_enseignant, created_at_enseignant, user_code, grade_enseignant, type_contrat, numero_autorisation, etablissement_code)
            VALUES (?, 'actif', NOW(), ?, ?, ?, ?, ?)
        ");

        for ($i = 0; $i < $count; $i++) {
            $p = $profiles[$i];
            $num = str_pad((string)($i + 1), 3, '0', STR_PAD_LEFT);
            $email = 'prof' . ($i + 1) . '@eicg.ci';
            $stmtCheckU->execute([$email]);
            $uCode = $stmtCheckU->fetchColumn();
            if (!$uCode) {
                $uCode = 'SEED-ENS' . $num;
                $tel = '07' . str_pad((string)(40000000 + $i * 111111), 8, '0', STR_PAD_LEFT);
                $stmtInsU->execute([$uCode, 'ENS-2023-' . $num, $p[0], $p[1], $email, $p[2], $hash, $tel, $this->etabCode]);
                $this->inc('users');
            }
            $uCode = (string)$uCode;
            $stmtRole->execute([$uCode]);

            $stmtCheckE->execute([$uCode]);
            if (!$stmtCheckE->fetchColumn()) {
                $stmtInsE->execute([$uCode, $this->adminCode, $p[3], $p[4], 'AUT-MESRS-' . (2020 + ($i % 5)) . '-' . $num, $this->etabCode]);
                $this->inc('enseignants');
            }
            $teachers[] = [
                'code_enseignant' => $uCode,
                'nom'             => $p[0] . ' ' . $p[1],
            ];
        }

        return $teachers;
    }

    private function ensureAcademicCatalog(): array
    {
        $cycles = $this->pdo->query("SELECT * FROM cycles WHERE statut_cycle = 'actif' ORDER BY id_cycle ASC")->fetchAll(PDO::FETCH_ASSOC);
        $btsCycle = $cycles[0]['code_cycle'] ?? 'CYC-RHUFSGDY';

        $filieres = $this->pdo->query("SELECT * FROM filieres WHERE statut_filiere = 'actif' ORDER BY id_filiere ASC")->fetchAll(PDO::FETCH_ASSOC);
        $niveaux  = $this->pdo->query("SELECT * FROM niveaux WHERE statut_niveau = 'actif' ORDER BY id_niveau ASC")->fetchAll(PDO::FETCH_ASSOC);
        $salles   = $this->pdo->query("SELECT * FROM salles WHERE statut_salle = 'actif' ORDER BY id_salle ASC")->fetchAll(PDO::FETCH_ASSOC);
        $typeDeps = $this->pdo->query("SELECT * FROM type_depenses WHERE statut_type_depense = 'actif' AND LOWER(libelle_type_depense) != 'test' ORDER BY id_type_depense ASC")->fetchAll(PDO::FETCH_ASSOC);

        // On cible les 2 niveaux BTS (1A & 2A) existants
        $niveauxBts = array_slice($niveaux, 0, 2);

        // S'assurer que filiere_cycles relie toutes les filières actives aux niveaux 1A et 2A
        $stmtChkFc = $this->pdo->prepare("SELECT code_filiere_cycle FROM filiere_cycles WHERE filiere_code = ? AND cycle_code = ? AND niveau_code = ? LIMIT 1");
        $stmtInsFc = $this->pdo->prepare("
            INSERT INTO filiere_cycles (code_filiere_cycle, filiere_code, cycle_code, niveau_code, type_filiere, etablissement_code, statut_filiere_cycle, created_at_filiere_cycle, user_code)
            VALUES (?, ?, ?, ?, ?, ?, 'actif', NOW(), ?)
        ");
        foreach ($filieres as $f) {
            foreach ($niveauxBts as $n) {
                $stmtChkFc->execute([$f['code_filiere'], $btsCycle, $n['code_niveau']]);
                if (!$stmtChkFc->fetchColumn()) {
                    $stmtInsFc->execute([
                        $this->genCode('FCYC-'),
                        $f['code_filiere'],
                        $btsCycle,
                        $n['code_niveau'],
                        $f['type_filiere'] ?: 'TERTIAIRE',
                        $this->etabCode,
                        $this->adminCode
                    ]);
                    $this->inc('filiere_cycles');
                }
            }
        }

        // Compléter le référentiel de matières (sans doublonner les 4 existantes)
        $matieresToEnsure = [
            ['ALGORITHME', 'PROFESSIONNELLE', 'INDUSTRIELLE'],
            ['BASE DE DONNEES', 'PROFESSIONNELLE', 'INDUSTRIELLE'],
            ['MATHEMATIQUE', 'GENERALE', 'TOUT'],
            ['ECONOMIE', 'GENERALE', 'TOUT'],
            ['FRANÇAIS & TECHNIQUES D\'EXPRESSION', 'GENERALE', 'TOUT'],
            ['ANGLAIS TECHNIQUE & DES AFFAIRES', 'GENERALE', 'TOUT'],
            ['DROIT DU TRAVAIL & DES AFFAIRES', 'GENERALE', 'TOUT'],
            ['SYSTÈMES & RÉSEAUX INFORMATIQUES', 'PROFESSIONNELLE', 'INDUSTRIELLE'],
            ['DÉVELOPPEMENT WEB & APPLICATIONS', 'PROFESSIONNELLE', 'INDUSTRIELLE'],
            ['COMPTABILITÉ GÉNÉRALE & ANALYTIQUE', 'PROFESSIONNELLE', 'TERTIAIRE'],
            ['MARKETING & ACTION COMMERCIALE', 'PROFESSIONNELLE', 'TERTIAIRE'],
            ['GESTION DES RESSOURCES HUMAINES', 'PROFESSIONNELLE', 'TERTIAIRE'],
        ];

        $stmtChkM = $this->pdo->prepare("SELECT code_matiere FROM matieres WHERE UPPER(TRIM(libelle_matiere)) = UPPER(TRIM(?)) LIMIT 1");
        $stmtInsM = $this->pdo->prepare("
            INSERT INTO matieres (code_matiere, libelle_matiere, etablissement_code, user_code, created_at_matiere, statut_matiere)
            VALUES (?, ?, ?, ?, NOW(), 'actif')
        ");

        $matieresCatalog = [];
        foreach ($matieresToEnsure as $mSpec) {
            $stmtChkM->execute([$mSpec[0]]);
            $mCode = $stmtChkM->fetchColumn();
            if (!$mCode) {
                $mCode = $this->genCode('MAT-');
                $stmtInsM->execute([$mCode, $mSpec[0], $this->etabCode, $this->adminCode]);
                $this->inc('matieres');
            }
            $matieresCatalog[] = [
                'code_matiere'    => (string)$mCode,
                'libelle_matiere' => $mSpec[0],
                'categorie'       => $mSpec[1],
                'domaine'         => $mSpec[2],
            ];
        }

        return [
            'bts_cycle_code' => $btsCycle,
            'filieres'       => $filieres,
            'niveaux'        => $niveauxBts,
            'salles'         => $salles,
            'matieres'       => $matieresCatalog,
            'type_depenses'  => $typeDeps,
        ];
    }

    private function seedPiecesFournir(string $btsCycleCode): array
    {
        $defs = [
            ['Extrait d\'acte de naissance', 'Extrait de naissance original de moins de 3 mois', 2, 'original'],
            ['Photocopie CNI / Attestation d\'identité', 'Pièce d\'identité officielle en cours de validité', 2, 'photocopie_simple'],
            ['Relevé de notes & Attestation BAC', 'Copie légalisée du relevé de notes ou diplôme BAC', 1, 'photocopie_legalisee'],
            ['Photos d\'identité couleur', '4 photos d\'identité récentes sur fond blanc', 4, 'original'],
            ['Fiche d\'orientation MESRS', 'Obligatoire pour les étudiants affectés par l\'État', 1, 'photocopie_simple'],
            ['Certificat de nationalité', 'Copie du certificat de nationalité', 1, 'photocopie_simple'],
        ];

        $stmtChkP = $this->pdo->prepare("SELECT code_piece_fournir FROM pieces_fournir WHERE libelle_piece = ? LIMIT 1");
        $stmtInsP = $this->pdo->prepare("
            INSERT INTO pieces_fournir (code_piece_fournir, libelle_piece, description_piece, etablissement_code, user_code, statut_piece, created_at_piece)
            VALUES (?, ?, ?, ?, ?, 'actif', NOW())
        ");
        $stmtChkC = $this->pdo->prepare("SELECT code_piece_cycle FROM piece_fournir_cycle WHERE cycle_code = ? AND piece_code = ? LIMIT 1");
        $stmtInsC = $this->pdo->prepare("
            INSERT INTO piece_fournir_cycle (code_piece_cycle, cycle_code, niveau_code, piece_code, nombre_exemplaires, nature_document, etablissement_code, user_code, statut_piece_cycle, created_at_piece_cycle)
            VALUES (?, ?, NULL, ?, ?, ?, ?, ?, 'actif', NOW())
        ");

        $pieces = [];
        foreach ($defs as $d) {
            $stmtChkP->execute([$d[0]]);
            $pCode = $stmtChkP->fetchColumn();
            if (!$pCode) {
                $pCode = $this->genCode('PCE-');
                $stmtInsP->execute([$pCode, $d[0], $d[1], $this->etabCode, $this->adminCode]);
                $this->inc('pieces_fournir');
            }
            $pCode = (string)$pCode;
            $stmtChkC->execute([$btsCycleCode, $pCode]);
            if (!$stmtChkC->fetchColumn()) {
                $stmtInsC->execute([$this->genCode('PFC-'), $btsCycleCode, $pCode, $d[2], $d[3], $this->etabCode, $this->adminCode]);
                $this->inc('piece_fournir_cycle');
            }
            $pieces[] = ['code_piece_fournir' => $pCode, 'libelle_piece' => $d[0]];
        }

        return $pieces;
    }

    private function seedAccessoires(): array
    {
        $defs = [
            ['Tenue de Ville Officielle EICG', 'TOUT'],
            ['Polo Officiel GROUPE EICG', 'TOUT'],
            ['Badge Étudiant & Porte-Badge', 'TOUT'],
            ['Blouse Technique d\'Atelier / Labo', 'INDUSTRIELLE'],
            ['Cravate & Macaron Institutionnel', 'TERTIAIRE'],
        ];

        $stmtChk = $this->pdo->prepare("SELECT code_accessoire FROM accessoires WHERE libelle_accessoire = ? LIMIT 1");
        $stmtIns = $this->pdo->prepare("
            INSERT INTO accessoires (code_accessoire, libelle_accessoire, type_filiere_cible, niveau_code, statut_accessoire, created_at_accessoire, etablissement_code, user_code)
            VALUES (?, ?, ?, NULL, 'actif', NOW(), ?, ?)
        ");

        $kits = [];
        foreach ($defs as $d) {
            $stmtChk->execute([$d[0]]);
            $aCode = $stmtChk->fetchColumn();
            if (!$aCode) {
                $aCode = $this->genCode('ACC-');
                $stmtIns->execute([$aCode, $d[0], $d[1], $this->etabCode, $this->adminCode]);
                $this->inc('accessoires');
            }
            $kits[] = ['code_accessoire' => (string)$aCode, 'libelle' => $d[0], 'type_cible' => $d[1]];
        }

        return $kits;
    }

    private function ensureBaremesBulletin(): void
    {
        $cnt = (int)$this->pdo->query("SELECT COUNT(*) FROM baremes_bulletin")->fetchColumn();
        if ($cnt > 0) {
            return;
        }

        $defaults = [
            ['BAR-TB', 1, 16.00, 20.00, 'Très Bien',        'Très Bien',        'FELICITATIONS',  'Félicitations du Conseil',                'Admis(e) avec Félicitations du Conseil'],
            ['BAR-B',  2, 14.00, 15.99, 'Bien',             'Bien',             'ENCOURAGEMENTS', 'Tableau d\'Honneur',                      'Admis(e) avec Tableau d\'Honneur'],
            ['BAR-AB', 3, 12.00, 13.99, 'Assez Bien',       'Assez Bien',       'HONNEUR',        'Encouragements',                          'Admis(e) avec Encouragements'],
            ['BAR-P',  4, 10.00, 11.99, 'Passable',         'Passable',         'AUCUN',          'Travail passable, peut mieux faire',      'Admis(e) en classe supérieure'],
            ['BAR-I',  5,  8.00,  9.99, 'Insuffisant',      'Insuffisant',      'AVERTISSEMENT',  'Travail insuffisant, redoubler d\'efforts','Autorisé(e) à redoubler'],
            ['BAR-TI', 6,  0.00,  7.99, 'Très Insuffisant', 'Très Insuffisant', 'BLAME',          'Résultats très faibles, blâme travail',   'Ajourné(e) / Résultats très faibles'],
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO baremes_bulletin (
                code_bareme, ordre_affichage, note_min, note_max,
                appreciation_matiere, mention_bulletin, code_distinction_sanction,
                appreciation_conseil, appreciation_conseil_s2, statut_bareme, etablissement_code
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', ?)
        ");

        foreach ($defaults as $d) {
            $stmt->execute([$d[0], $d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7], $d[8], $this->etabCode]);
            $this->inc('baremes_bulletin');
        }
    }

    private function ensureAnnee(string $yearLabel, string $status): array
    {
        $startYear = (int)substr($yearLabel, 0, 4);
        $endYear   = $startYear + 1;
        $debut     = "{$startYear}-09-15";
        $fin       = "{$endYear}-07-15";

        $stmt = $this->pdo->prepare("SELECT * FROM annees WHERE libelle_annee = ? LIMIT 1");
        $stmt->execute([$yearLabel]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $upd = $this->pdo->prepare("
                UPDATE annees
                SET statut_annee = ?,
                    date_debut_annee = ?,
                    date_fin_annee = ?,
                    coef_semestre1 = COALESCE(NULLIF(coef_semestre1, 0), 2),
                    coef_semestre2 = COALESCE(NULLIF(coef_semestre2, 0), 2),
                    coef_examen = COALESCE(NULLIF(coef_examen, 0), 1),
                    updated_at_annee = NOW()
                WHERE id_annee = ?
            ");
            $upd->execute([$status, $debut, $fin, $row['id_annee']]);
            $row['statut_annee']   = $status;
            $row['coef_semestre1'] = (int)($row['coef_semestre1'] ?? 2);
            $row['coef_semestre2'] = (int)($row['coef_semestre2'] ?? 2);
            $row['coef_examen']    = (int)($row['coef_examen'] ?? 1);
            return $row;
        }

        $code = $this->genCode('ANN-');
        $ins = $this->pdo->prepare("
            INSERT INTO annees (
                libelle_annee, date_debut_annee, date_fin_annee, code_annee,
                etablissement_code, created_at_annee, statut_annee, user_code,
                coef_semestre1, coef_semestre2, coef_examen
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 2, 2, 1)
        ");
        $ins->execute([$yearLabel, $debut, $fin, $code, $this->etabCode, "{$startYear}-09-01 08:00:00", $status, $this->adminCode]);
        $this->inc('annees');

        return [
            'id_annee'         => (int)$this->pdo->lastInsertId(),
            'code_annee'       => $code,
            'libelle_annee'    => $yearLabel,
            'statut_annee'     => $status,
            'date_debut_annee' => $debut,
            'date_fin_annee'   => $fin,
            'coef_semestre1'   => 2,
            'coef_semestre2'   => 2,
            'coef_examen'      => 1,
        ];
    }

    private function ensureUserAnneeAccess(string $anneeCode): void
    {
        $users = $this->pdo->query("SELECT code_user FROM users WHERE statut_user = 'actif'")->fetchAll(PDO::FETCH_COLUMN);
        $stmtChk = $this->pdo->prepare("SELECT id_user_annee_acces FROM user_annee_acces WHERE user_code = ? AND annee_code = ? LIMIT 1");
        $stmtIns = $this->pdo->prepare("
            INSERT INTO user_annee_acces (user_code, annee_code, niveau_acces, created_at_acces)
            VALUES (?, ?, 'ecriture', NOW())
        ");
        foreach ($users as $uCode) {
            $stmtChk->execute([$uCode, $anneeCode]);
            if (!$stmtChk->fetchColumn()) {
                $stmtIns->execute([$uCode, $anneeCode]);
                $this->inc('user_annee_acces');
            }
        }
    }

    private function seedSemestres(string $anneeCode, int $startYear, bool $isActiveYear): array
    {
        $endYear = $startYear + 1;
        $defs = [
            ['Semestre 1', "{$startYear}-09-15", "{$endYear}-02-15", 'cloture'],
            ['Semestre 2', "{$endYear}-02-16", "{$endYear}-07-15", $isActiveYear ? 'actif' : 'cloture'],
        ];

        $stmtChk = $this->pdo->prepare("SELECT * FROM semestres WHERE annee_code = ? AND libelle_semestre = ? LIMIT 1");
        $stmtIns = $this->pdo->prepare("
            INSERT INTO semestres (code_semestre, libelle_semestre, statut_semestre, etablissement_code, annee_code, date_debut_semestre, date_fin_semestre, created_at_semestre, user_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $out = [];
        foreach ($defs as $d) {
            $stmtChk->execute([$anneeCode, $d[0]]);
            $existing = $stmtChk->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $out[] = $existing;
                continue;
            }
            $code = $this->genCode('SEM-');
            $stmtIns->execute([$code, $d[0], $d[3], $this->etabCode, $anneeCode, $d[1], $d[2], "{$startYear}-09-05 09:00:00", $this->adminCode]);
            $this->inc('semestres');
            $out[] = [
                'code_semestre'       => $code,
                'libelle_semestre'    => $d[0],
                'statut_semestre'     => $d[3],
                'date_debut_semestre' => $d[1],
                'date_fin_semestre'   => $d[2],
            ];
        }
        return $out;
    }

    private function seedClasses(string $anneeCode, array $catalog): array
    {
        $stmtChk = $this->pdo->prepare("SELECT * FROM classes WHERE annee_code = ? AND filiere_code = ? AND niveau_code = ? LIMIT 1");
        $stmtIns = $this->pdo->prepare("
            INSERT INTO classes (code_classe, libelle_classe, capacite_max_classe, filiere_code, cycle_code, etablissement_code, niveau_code, annee_code, statut_classe, created_at_classe, user_code)
            VALUES (?, ?, 45, ?, ?, ?, ?, ?, 'actif', NOW(), ?)
        ");

        $classes = [];
        foreach ($catalog['filieres'] as $f) {
            $slugF = $f['slug_filiere'] ?: substr($f['libelle_filiere'], 0, 4);
            foreach ($catalog['niveaux'] as $nIdx => $n) {
                $nivTag = ($nIdx === 0) ? '1A' : '2A';
                $stmtChk->execute([$anneeCode, $f['code_filiere'], $n['code_niveau']]);
                $existing = $stmtChk->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    $existing['niv_index']    = $nIdx;
                    $existing['type_filiere'] = $f['type_filiere'] ?: 'TERTIAIRE';
                    $existing['slug_filiere'] = $slugF;
                    $classes[] = $existing;
                    continue;
                }
                $code = $this->genCode('CLA-');
                $lib  = trim($slugF . ' ' . $nivTag);
                $stmtIns->execute([$code, $lib, $f['code_filiere'], $catalog['bts_cycle_code'], $this->etabCode, $n['code_niveau'], $anneeCode, $this->adminCode]);
                $this->inc('classes');
                $classes[] = [
                    'code_classe'    => $code,
                    'libelle_classe' => $lib,
                    'filiere_code'   => $f['code_filiere'],
                    'niveau_code'    => $n['code_niveau'],
                    'cycle_code'     => $catalog['bts_cycle_code'],
                    'annee_code'     => $anneeCode,
                    'niv_index'      => $nIdx,
                    'type_filiere'   => $f['type_filiere'] ?: 'TERTIAIRE',
                    'slug_filiere'   => $slugF,
                ];
            }
        }
        return $classes;
    }

    private function seedMaquettes(string $anneeCode, array $catalog): array
    {
        $stmtChk = $this->pdo->prepare("SELECT code_maquette FROM maquettes_pedagogiques WHERE annee_code = ? AND filiere_code = ? AND niveau_code = ? AND matiere_code = ? LIMIT 1");
        $stmtIns = $this->pdo->prepare("
            INSERT INTO maquettes_pedagogiques (code_maquette, annee_code, filiere_code, niveau_code, matiere_code, categorie_matiere, coefficient, etablissement_code, statut_maquette, user_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'actif', ?)
        ");

        $maquettes = []; // ["filiere|niveau" => [matieres...]]
        foreach ($catalog['filieres'] as $f) {
            $typeF = $f['type_filiere'] ?: 'TERTIAIRE';
            // 3 matières générales + 3 professionnelles adaptées au type de filière
            $genMats = array_values(array_filter($catalog['matieres'], fn($m) => $m['categorie'] === 'GENERALE'));
            $proMats = array_values(array_filter($catalog['matieres'], fn($m) => $m['categorie'] === 'PROFESSIONNELLE' && $m['domaine'] === $typeF));
            $selected = array_merge(array_slice($genMats, 0, 3), array_slice($proMats, 0, 3));

            foreach ($catalog['niveaux'] as $n) {
                $key = $f['code_filiere'] . '|' . $n['code_niveau'];
                $maquettes[$key] = [];
                foreach ($selected as $idx => $mat) {
                    $coef = ($mat['categorie'] === 'PROFESSIONNELLE') ? (($idx % 2 === 0) ? 4.00 : 3.00) : 2.00;
                    $stmtChk->execute([$anneeCode, $f['code_filiere'], $n['code_niveau'], $mat['code_matiere']]);
                    if (!$stmtChk->fetchColumn()) {
                        $stmtIns->execute([
                            $this->genCode('MAQ-'),
                            $anneeCode,
                            $f['code_filiere'],
                            $n['code_niveau'],
                            $mat['code_matiere'],
                            $mat['categorie'],
                            $coef,
                            $this->etabCode,
                            $this->adminCode
                        ]);
                        $this->inc('maquettes_pedagogiques');
                    }
                    $maquettes[$key][] = [
                        'code_matiere'    => $mat['code_matiere'],
                        'libelle_matiere' => $mat['libelle_matiere'],
                        'categorie'       => $mat['categorie'],
                        'coefficient'     => $coef,
                    ];
                }
            }
        }
        return $maquettes;
    }

    private function seedScolaritesAndTranches(string $anneeCode, int $startYear, array $catalog): array
    {
        $endYear = $startYear + 1;
        $stmtChkS = $this->pdo->prepare("SELECT * FROM scolarites WHERE annee_code = ? AND filiere_code = ? AND niveau_code = ? AND affectation_etat = ? LIMIT 1");
        $stmtInsS = $this->pdo->prepare("
            INSERT INTO scolarites (code_scolarite, montant_scolarite, affectation_etat, niveau_code, filiere_code, annee_code, user_code, etablissement_code, statut_scolarite)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'actif')
        ");
        $stmtInsT = $this->pdo->prepare("
            INSERT INTO tranches_scolarite (code_tranche, libelle_tranche, montant_tranche, date_limite, scolarite_code, filiere_code, niveau_code, annee_code, etablissement_code, statut_tranche)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif')
        ");
        $stmtGetT = $this->pdo->prepare("SELECT * FROM tranches_scolarite WHERE scolarite_code = ? ORDER BY date_limite ASC");

        $tarifs = []; // ["filiere|niveau|affecte/non_affecte" => ['montant'=>..., 'code_scolarite'=>..., 'tranches'=>[...]]]
        foreach ($catalog['filieres'] as $f) {
            $isInd = (($f['type_filiere'] ?? '') === 'INDUSTRIELLE');
            foreach ($catalog['niveaux'] as $nIdx => $n) {
                $regimes = [
                    'affecte'     => ($nIdx === 0) ? 120000.00 : 135000.00,
                    'non_affecte' => $isInd ? (($nIdx === 0) ? 350000.00 : 380000.00) : (($nIdx === 0) ? 300000.00 : 330000.00),
                ];

                foreach ($regimes as $regime => $montant) {
                    $key = $f['code_filiere'] . '|' . $n['code_niveau'] . '|' . $regime;
                    $stmtChkS->execute([$anneeCode, $f['code_filiere'], $n['code_niveau'], $regime]);
                    $existing = $stmtChkS->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        $scolCode = $existing['code_scolarite'];
                        $montant  = (float)$existing['montant_scolarite'];
                    } else {
                        $scolCode = $this->genCode('SCO-');
                        $stmtInsS->execute([$scolCode, $montant, $regime, $n['code_niveau'], $f['code_filiere'], $anneeCode, $this->adminCode, $this->etabCode]);
                        $this->inc('scolarites');
                    }

                    $stmtGetT->execute([$scolCode]);
                    $tranches = $stmtGetT->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($tranches)) {
                        if ($regime === 'affecte') {
                            $split = [
                                ['Inscription & Tranche 1', round($montant * 0.40, -3), "{$startYear}-10-15"],
                                ['Tranche 2', round($montant * 0.30, -3), "{$endYear}-01-15"],
                                ['Tranche 3 (Solde)', $montant - round($montant * 0.40, -3) - round($montant * 0.30, -3), "{$endYear}-04-15"],
                            ];
                        } else {
                            $t1 = round($montant * 0.30, -3);
                            $t2 = round($montant * 0.25, -3);
                            $t3 = round($montant * 0.25, -3);
                            $t4 = $montant - $t1 - $t2 - $t3;
                            $split = [
                                ['Inscription', $t1, "{$startYear}-10-15"],
                                ['Tranche 1', $t2, "{$startYear}-12-15"],
                                ['Tranche 2', $t3, "{$endYear}-02-15"],
                                ['Tranche 3 (Solde)', $t4, "{$endYear}-04-30"],
                            ];
                        }
                        foreach ($split as $sp) {
                            $tCode = $this->genCode('TRN-');
                            $stmtInsT->execute([$tCode, $sp[0], $sp[1], $sp[2], $scolCode, $f['code_filiere'], $n['code_niveau'], $anneeCode, $this->etabCode]);
                            $this->inc('tranches_scolarite');
                            $tranches[] = [
                                'code_tranche'    => $tCode,
                                'libelle_tranche' => $sp[0],
                                'montant_tranche' => $sp[1],
                                'date_limite'     => $sp[2],
                            ];
                        }
                    }

                    $tarifs[$key] = [
                        'code_scolarite' => $scolCode,
                        'montant'        => $montant,
                        'tranches'       => $tranches,
                    ];
                }
            }
        }
        return $tarifs;
    }

    private function seedFraisAnnexes(string $anneeCode, int $startYear): array
    {
        $defs = [
            ['Frais Annexes Filières Industrielles (Uniforme, Blouse, Badge, Assurance)', 'INDUSTRIELLE', 60000.00],
            ['Frais Annexes Filières Tertiaires (Uniforme Ville, Badge, Assurance)', 'TERTIAIRE', 45000.00],
        ];
        $stmtChk = $this->pdo->prepare("SELECT * FROM frais_annexes WHERE annee_code = ? AND type_filiere = ? LIMIT 1");
        $stmtIns = $this->pdo->prepare("
            INSERT INTO frais_annexes (code_frais_annexe, libelle_frais_annexe, categorie_frais_annexe, type_filiere, niveau_code, montant_frais_annexe, annee_code, etablissement_code, statut_frais_annexe, user_code, created_at_frais_annexe)
            VALUES (?, ?, 'inscription', ?, NULL, ?, ?, ?, 'actif', ?, ?)
        ");

        $out = [];
        foreach ($defs as $d) {
            $stmtChk->execute([$anneeCode, $d[1]]);
            $row = $stmtChk->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $out[$d[1]] = (float)$row['montant_frais_annexe'];
                continue;
            }
            $stmtIns->execute([$this->genCode('FAN-'), $d[0], $d[1], $d[2], $anneeCode, $this->etabCode, $this->adminCode, "{$startYear}-09-02 09:00:00"]);
            $this->inc('frais_annexes');
            $out[$d[1]] = $d[2];
        }
        return $out;
    }

    private function seedAccessoireCibles(string $anneeCode, array $kits, array $catalog): void
    {
        $stmtChk = $this->pdo->prepare("SELECT id_accessoire_niveau_filiere FROM accessoire_niveau_filiere WHERE accessoire_code = ? AND annee_code = ? AND ((filiere_code IS NULL AND ? IS NULL) OR filiere_code = ?) AND niveau_code IS NULL LIMIT 1");
        $stmtIns = $this->pdo->prepare("
            INSERT INTO accessoire_niveau_filiere (code_accessoire_niveau_filiere, accessoire_code, filiere_code, niveau_code, annee_code, statut_accessoire_niveau_filiere, etablissement_code, user_code)
            VALUES (?, ?, ?, NULL, ?, 'actif', ?, ?)
        ");

        foreach ($kits as $k) {
            if ($k['type_cible'] === 'TOUT') {
                $stmtChk->execute([$k['code_accessoire'], $anneeCode, 'TOUTE_FILIERE', 'TOUTE_FILIERE']);
                if (!$stmtChk->fetchColumn()) {
                    $stmtIns->execute([$this->genCode('ANF-'), $k['code_accessoire'], 'TOUTE_FILIERE', $anneeCode, $this->etabCode, $this->adminCode]);
                    $this->inc('accessoire_niveau_filiere');
                }
            } else {
                foreach ($catalog['filieres'] as $f) {
                    if (($f['type_filiere'] ?? '') !== $k['type_cible']) continue;
                    $stmtChk->execute([$k['code_accessoire'], $anneeCode, $f['code_filiere'], $f['code_filiere']]);
                    if (!$stmtChk->fetchColumn()) {
                        $stmtIns->execute([$this->genCode('ANF-'), $k['code_accessoire'], $f['code_filiere'], $anneeCode, $this->etabCode, $this->adminCode]);
                        $this->inc('accessoire_niveau_filiere');
                    }
                }
            }
        }
    }

    private function seedEmploisDuTemps(string $anneeCode, array $classes, array $maquettes, array $teachers, array $salles): array
    {
        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        $creneaux = [
            ['08:00:00', '10:00:00'],
            ['10:15:00', '12:15:00'],
            ['14:00:00', '16:00:00'],
            ['16:15:00', '18:15:00'],
        ];

        $stmtInsEM = $this->pdo->prepare("
            INSERT INTO enseignant_matiere (enseignant_code, matiere_code, classe_code, coefficient, etablissement_code, annee_code, user_code, created_at_enseignant_matiere, statut_enseignant_matiere)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'actif')
        ");
        $stmtInsEDT = $this->pdo->prepare("
            INSERT INTO emplois_temps (code_emploi, classe_code, matiere_code, enseignant_code, salle_code, etablissement_code, annee_code, user_code, jour, heure_debut, heure_fin, created_at_emploi, statut_emploi)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'actif')
        ");

        $busyTeacher = []; // ["jour|hdeb|ens" => true]
        $busySalle   = []; // ["jour|hdeb|salle" => true]
        $emploisByClasse = [];

        foreach ($classes as $cIdx => $cl) {
            $mKey = $cl['filiere_code'] . '|' . $cl['niveau_code'];
            $mats = $maquettes[$mKey] ?? [];
            $emploisByClasse[$cl['code_classe']] = [];

            $slotCursor = $cIdx * 2;
            foreach ($mats as $mIdx => $mat) {
                $teacher = $teachers[($cIdx + $mIdx) % count($teachers)];
                $stmtInsEM->execute([
                    $teacher['code_enseignant'],
                    $mat['code_matiere'],
                    $cl['code_classe'],
                    $mat['coefficient'],
                    $this->etabCode,
                    $anneeCode,
                    $this->adminCode
                ]);
                $this->inc('enseignant_matiere');

                // Trouver un créneau libre sans conflit salle ni enseignant
                $placed = false;
                for ($attempt = 0; $attempt < 20; $attempt++) {
                    $idx   = ($slotCursor + $attempt) % 20;
                    $jour  = $jours[(int)floor($idx / 4)];
                    $cr    = $creneaux[$idx % 4];
                    $salle = $salles[($cIdx + $attempt) % count($salles)];

                    $tKey = "{$jour}|{$cr[0]}|{$teacher['code_enseignant']}";
                    $sKey = "{$jour}|{$cr[0]}|{$salle['code_salle']}";
                    if (!isset($busyTeacher[$tKey]) && !isset($busySalle[$sKey])) {
                        $busyTeacher[$tKey] = true;
                        $busySalle[$sKey]   = true;
                        $codeEdt = $this->genCode('EDT-');
                        $stmtInsEDT->execute([
                            $codeEdt,
                            $cl['code_classe'],
                            $mat['code_matiere'],
                            $teacher['code_enseignant'],
                            $salle['code_salle'],
                            $this->etabCode,
                            $anneeCode,
                            $this->adminCode,
                            $jour,
                            $cr[0],
                            $cr[1]
                        ]);
                        $this->inc('emplois_temps');
                        $emploisByClasse[$cl['code_classe']][] = [
                            'code_emploi'     => $codeEdt,
                            'matiere_code'    => $mat['code_matiere'],
                            'enseignant_code' => $teacher['code_enseignant'],
                            'jour'            => $jour,
                        ];
                        $slotCursor = $idx + 1;
                        $placed = true;
                        break;
                    }
                }
                if (!$placed) {
                    $jour  = $jours[$mIdx % 5];
                    $cr    = $creneaux[$cIdx % 4];
                    $salle = $salles[$cIdx % count($salles)];
                    $codeEdt = $this->genCode('EDT-');
                    $stmtInsEDT->execute([
                        $codeEdt, $cl['code_classe'], $mat['code_matiere'], $teacher['code_enseignant'],
                        $salle['code_salle'], $this->etabCode, $anneeCode, $this->adminCode, $jour, $cr[0], $cr[1]
                    ]);
                    $this->inc('emplois_temps');
                    $emploisByClasse[$cl['code_classe']][] = [
                        'code_emploi'     => $codeEdt,
                        'matiere_code'    => $mat['code_matiere'],
                        'enseignant_code' => $teacher['code_enseignant'],
                        'jour'            => $jour,
                    ];
                }
            }
        }
        return $emploisByClasse;
    }

    private function seedStudentsAndInscriptions(
        string $anneeCode,
        string $yearLabel,
        int $startYear,
        array $classes,
        array $tarifs,
        array &$cohort1AByYear,
        ?string $prevYearLabel,
        int &$studentCounter
    ): array {
        $stmtInsEtu = $this->pdo->prepare("
            INSERT INTO etudiants (
                code_etudiant, matricule_etudiant, matricule_menet, matricule_mesrs,
                nom_etudiant, prenom_etudiant, date_naissance_etudiant, lieu_naissance_etudiant,
                sexe_etudiant, nationalite_etudiant, lieu_residence_etudiant, telephone_etudiant,
                email_etudiant, photo_etudiant, numero_cni, statut_etudiant, etablissement_origine,
                created_at_etudiant, user_code, etablissement_code
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Ivoirienne', ?, ?, ?, NULL, ?, 'actif', ?, ?, ?, ?)
        ");
        $stmtInsPar = $this->pdo->prepare("
            INSERT INTO parents (
                code_parent, nom_pere, telephone_pere, profession_pere,
                nom_mere, telephone_mere, profession_mere,
                nom_tuteur, telephone_tuteur, profession_tuteur,
                parent_habitation, created_at_parent, etudiant_code, user_code, etablissement_code, statut_parent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif')
        ");
        $stmtInsInsc = $this->pdo->prepare("
            INSERT INTO inscriptions (
                code_inscription, etudiant_code, classe_code, etablissement_code, annee_code,
                user_code, created_at_inscription, montant_scolarite_inscription,
                canal_inscription, affectation_etat, statut_inscription, photo_inscription
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'valide', NULL)
        ");

        $inscriptionsByClasse = [];
        $cohort1AByYear[$yearLabel] = [];
        $targetCount = $this->config['students_per_class'];

        foreach ($classes as $cl) {
            $clCode = $cl['code_classe'];
            $filCode = $cl['filiere_code'];
            $nivCode = $cl['niveau_code'];
            $is1A = ((int)$cl['niv_index'] === 0);
            $inscriptionsByClasse[$clCode] = [];

            $studentsToEnroll = [];

            // Si 2A et année précédente disponible : réinscrire ~80% de la cohorte 1A précédente
            if (!$is1A && $prevYearLabel !== null && !empty($cohort1AByYear[$prevYearLabel][$filCode])) {
                $prevCohort = $cohort1AByYear[$prevYearLabel][$filCode];
                $promoteCount = min($targetCount, (int)max(2, round(count($prevCohort) * 0.85)));
                for ($k = 0; $k < $promoteCount; $k++) {
                    $studentsToEnroll[] = $prevCohort[$k];
                }
            }

            // Compléter avec de nouveaux étudiants jusqu'à $targetCount
            while (count($studentsToEnroll) < $targetCount) {
                $sexe   = (mt_rand(0, 100) < 55) ? 'M' : 'F';
                $nom    = $this->pick(self::NOMS_FAMILLE);
                $prenom = $sexe === 'M' ? $this->pick(self::PRENOMS_M) : $this->pick(self::PRENOMS_F);
                $eCode  = $this->genCode('ETU-');
                $matNum = str_pad((string)$studentCounter++, 4, '0', STR_PAD_LEFT);
                $mat    = 'EICG-' . substr((string)$startYear, -2) . '-' . $matNum;
                $menet  = (string)mt_rand(10000000, 99999999) . chr(mt_rand(65, 90));
                $mesrs  = 'MESRS' . $startYear . $matNum;
                $birthY = $startYear - ($is1A ? mt_rand(18, 22) : mt_rand(19, 24));
                $birthD = sprintf('%04d-%02d-%02d', $birthY, mt_rand(1, 12), mt_rand(1, 28));
                $tel    = $this->pick(['07', '05', '01']) . str_pad((string)mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
                $slugEmail = strtolower(preg_replace('/[^a-z0-9]/i', '', $nom . '.' . $prenom));
                $email  = substr($slugEmail, 0, 18) . $matNum . '@etudiant.eicg.ci';
                $cni    = 'CI' . str_pad((string)mt_rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
                $createdAt = sprintf('%04d-09-%02d %02d:%02d:00', $startYear, mt_rand(10, 29), mt_rand(8, 16), mt_rand(0, 59));

                $stmtInsEtu->execute([
                    $eCode, $mat, $menet, $mesrs,
                    $nom, $prenom, $birthD, $this->pick(self::VILLES),
                    $sexe, $this->pick(self::QUARTIERS), $tel,
                    $email, $cni, $this->pick(self::LYCEES_ORIGINE),
                    $createdAt, $this->scolariteCode, $this->etabCode
                ]);
                $this->inc('etudiants');

                // Parent / Tuteur
                $pereNom = $nom . ' ' . $this->pick(self::PRENOMS_M);
                $mereNom = $this->pick(self::NOMS_FAMILLE) . ' ' . $this->pick(self::PRENOMS_F);
                $telP    = '07' . str_pad((string)mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
                $telM    = '05' . str_pad((string)mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
                $stmtInsPar->execute([
                    $this->genCode('PAR-'),
                    $pereNom, $telP, $this->pick(self::PROFESSIONS),
                    $mereNom, $telM, $this->pick(self::PROFESSIONS),
                    $pereNom, $telP, $this->pick(self::PROFESSIONS),
                    $this->pick(self::QUARTIERS), $createdAt, $eCode, $this->scolariteCode, $this->etabCode
                ]);
                $this->inc('parents');

                $affecte = (mt_rand(1, 100) <= 42) ? 'oui' : 'non';
                $studentsToEnroll[] = [
                    'code_etudiant'    => $eCode,
                    'affectation_etat' => $affecte,
                    'sexe'             => $sexe,
                    'telephone'        => $tel,
                ];
            }

            if ($is1A) {
                $cohort1AByYear[$yearLabel][$filCode] = $studentsToEnroll;
            }

            foreach ($studentsToEnroll as $st) {
                $regimeKey = $st['affectation_etat'] === 'oui' ? 'affecte' : 'non_affecte';
                $tarifKey  = "{$filCode}|{$nivCode}|{$regimeKey}";
                $montant   = (float)($tarifs[$tarifKey]['montant'] ?? 300000.00);
                $canal     = (mt_rand(1, 100) <= 80) ? 'guichet' : 'en_ligne';
                $insCode   = $this->genCode('INS-');
                $insDate   = sprintf('%04d-%02d-%02d %02d:%02d:00', $startYear, mt_rand(9, 10), mt_rand(1, 28), mt_rand(8, 16), mt_rand(0, 59));

                $stmtInsInsc->execute([
                    $insCode,
                    $st['code_etudiant'],
                    $clCode,
                    $this->etabCode,
                    $anneeCode,
                    $this->scolariteCode,
                    $insDate,
                    $montant,
                    $canal,
                    $st['affectation_etat']
                ]);
                $this->inc('inscriptions');

                $inscriptionsByClasse[$clCode][] = [
                    'code_inscription' => $insCode,
                    'etudiant_code'    => $st['code_etudiant'],
                    'classe_code'      => $clCode,
                    'filiere_code'     => $filCode,
                    'niveau_code'      => $nivCode,
                    'type_filiere'     => $cl['type_filiere'],
                    'affectation_etat' => $st['affectation_etat'],
                    'regime'           => $regimeKey,
                    'montant_scolarite'=> $montant,
                    'telephone'        => $st['telephone'] ?? '0700000000',
                    'created_at'       => $insDate,
                ];
            }
        }

        return $inscriptionsByClasse;
    }

    private function seedDossiersEtudiants(array $inscriptionsByClasse, array $pieces, int $startYear, bool $isActiveYear): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO dossier_etudiant (
                code_dossier_etudiant, inscription_code, etudiant_code, piece_code,
                statut_depot, date_depot, observations, user_code, etablissement_code, created_at_dossier_etudiant
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($inscriptionsByClasse as $inscs) {
            foreach ($inscs as $ins) {
                foreach ($pieces as $pIdx => $p) {
                    // La fiche d'orientation MESRS n'est requise que pour les affectés
                    if ($pIdx === 4 && $ins['affectation_etat'] !== 'oui') {
                        $statut = 'non_requis';
                    } else {
                        $r = mt_rand(1, 100);
                        if (!$isActiveYear) {
                            $statut = ($r <= 92) ? 'depose' : 'en_attente';
                        } else {
                            if ($r <= 72) $statut = 'depose';
                            elseif ($r <= 94) $statut = 'en_attente';
                            else $statut = 'rejete';
                        }
                    }
                    $dateDepot = ($statut === 'depose') ? sprintf('%04d-10-%02d 10:00:00', $startYear, mt_rand(1, 28)) : null;
                    $obs = ($statut === 'rejete') ? 'Document illisible, copie à refournir' : null;
                    $stmt->execute([
                        $this->genCode('DOS-'),
                        $ins['code_inscription'],
                        $ins['etudiant_code'],
                        $p['code_piece_fournir'],
                        $statut,
                        $dateDepot,
                        $obs,
                        $this->scolariteCode,
                        $this->etabCode,
                        $ins['created_at']
                    ]);
                    $this->inc('dossier_etudiant');
                }
            }
        }
    }

    private function seedAccessoireInscriptions(string $anneeCode, array $inscriptionsByClasse, array $kits, int $startYear, bool $isActiveYear): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO accessoire_inscription (
                code_accessoire_inscription, inscription_code, accessoire_code, annee_code,
                statut_accessoire_inscription, etat_retrait, date_retrait, created_at_accessoire_inscription,
                etablissement_code, user_code
            ) VALUES (?, ?, ?, ?, 'actif', ?, ?, ?, ?, ?)
        ");

        foreach ($inscriptionsByClasse as $inscs) {
            foreach ($inscs as $ins) {
                foreach ($kits as $k) {
                    if ($k['type_cible'] !== 'TOUT' && $k['type_cible'] !== $ins['type_filiere']) {
                        continue;
                    }
                    $retire = (!$isActiveYear) ? (mt_rand(1, 100) <= 95) : (mt_rand(1, 100) <= 65);
                    $etat   = $retire ? 'retire' : 'en_attente';
                    $dRet   = $retire ? sprintf('%04d-10-%02d %02d:30:00', $startYear, mt_rand(5, 28), mt_rand(9, 16)) : null;

                    $stmt->execute([
                        $this->genCode('AKI-'),
                        $ins['code_inscription'],
                        $k['code_accessoire'],
                        $anneeCode,
                        $etat,
                        $dRet,
                        $ins['created_at'],
                        $this->etabCode,
                        $this->scolariteCode
                    ]);
                    $this->inc('accessoire_inscription');
                }
            }
        }
    }

    private function seedSessionsCaisse(string $anneeCode, int $startYear, bool $isActiveYear): array
    {
        $endYear = $startYear + 1;
        $dates = [
            "{$startYear}-10-15",
            "{$startYear}-12-12",
            "{$endYear}-02-14",
            "{$endYear}-04-18",
        ];
        $count = min($this->config['sessions_per_year'], count($dates));
        $stmt = $this->pdo->prepare("
            INSERT INTO sessions_caisse (
                code_session, date_session, heure_ouverture, fond_initial, observations_ouverture,
                heure_cloture, total_especes, total_mobile_money, total_cheque_virement,
                total_general, solde_attendu, ecart_caisse, observations_cloture,
                statut_session, user_code, user_validation, annee_code, etablissement_code, created_at_session
            ) VALUES (?, ?, '08:00:00', 50000.00, 'Ouverture journalière de caisse', ?, 0, 0, 0, 0, 50000.00, 0.00, ?, ?, ?, ?, ?, ?, ?)
        ");

        $sessions = [];
        for ($i = 0; $i < $count; $i++) {
            $isLastOpen = ($isActiveYear && $i === $count - 1);
            $code = $this->genCode('SES-');
            $d    = $dates[$i];
            $stat = $isLastOpen ? 'ouverte' : 'valide';
            $hClo = $isLastOpen ? null : '17:30:00';
            $obsC = $isLastOpen ? null : 'Clôture conforme après pointage comptable';
            $uVal = $isLastOpen ? null : $this->comptableCode;

            $stmt->execute([$code, $d, $hClo, $obsC, $stat, $this->caissierCode, $uVal, $anneeCode, $this->etabCode, "{$d} 08:00:00"]);
            $this->inc('sessions_caisse');

            $sessions[] = [
                'code_session'  => $code,
                'date_session'  => $d,
                'statut'        => $stat,
                'especes'       => 0.0,
                'mobile_money'  => 0.0,
                'cheque_vir'    => 0.0,
            ];
        }
        return $sessions;
    }

    private function seedPaiementsAndRelances(
        string $anneeCode,
        int $startYear,
        bool $isActiveYear,
        array $inscriptionsByClasse,
        array $tarifs,
        array $annexes,
        array $sessions,
        array $previousArrears
    ): array {
        $stmtPay = $this->pdo->prepare("
            INSERT INTO paiements (
                code_paiement, montant_paiement, date_paiement, statut_paiement, canal_paiement,
                reference_paiement, observations, type_paiement, categorie_paiement, mode_paiement,
                user_code, annee_code, etablissement_code, inscription_code, tranche_code, session_caisse_code
            ) VALUES (?, ?, ?, 'confirme', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtUpdIns = $this->pdo->prepare("UPDATE inscriptions SET statut_inscription = 'solde' WHERE code_inscription = ?");
        $stmtRel = $this->pdo->prepare("
            INSERT INTO relances_impayes (
                code_relance, etudiant_code, inscription_code, niveau_relance, canal_relance,
                montant_impaye, telephone_destinataire, message_relance, statut_relance,
                user_code, annee_code, etablissement_code, created_at_relance
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $newArrears = [];
        $numSess = count($sessions);

        // Helper pour créditer la session de caisse UNIQUEMENT si canal_paiement = 'guichet'
        $recordPayment = function (
            float $montant,
            int $sessIdx,
            string $canal,
            string $typePaiement,
            string $categorie,
            string $inscCode,
            ?string $trancheCode,
            string $obs
        ) use (&$sessions, $numSess, $stmtPay, $anneeCode) {
            $sIdx = $sessIdx % $numSess;
            $sess = &$sessions[$sIdx];
            $mode = ($canal === 'guichet') ? $this->pick(self::MODES_GUICHET) : $this->pick(self::MODES_EN_LIGNE);
            $sessCode = ($canal === 'guichet') ? $sess['code_session'] : null;
            $datePay  = $sess['date_session'] . sprintf(' %02d:%02d:00', mt_rand(8, 16), mt_rand(0, 59));
            $modePrefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $mode), 0, 3)) ?: 'REF';
            $ref      = ($mode === 'Espèces') ? null : $modePrefix . '-' . mt_rand(100000, 999999);

            $stmtPay->execute([
                $this->genCode('PAI-'),
                $montant,
                $datePay,
                $canal,
                $ref,
                $obs,
                $typePaiement,
                $categorie,
                $mode,
                $this->caissierCode,
                $anneeCode,
                $this->etabCode,
                $inscCode,
                $trancheCode,
                $sessCode
            ]);
            $this->inc('paiements');

            if ($canal === 'guichet') {
                if ($mode === 'Espèces') {
                    $sess['especes'] += $montant;
                } elseif (in_array($mode, ['Chèque', 'Virement Bancaire'], true)) {
                    $sess['cheque_vir'] += $montant;
                } else {
                    $sess['mobile_money'] += $montant;
                }
            }
        };

        // 1. Paiements d'arriérés de quelques étudiants d'années précédentes (sur la session 0 de l'année courante)
        foreach ($previousArrears as $idx => $arr) {
            if ($idx % 3 !== 0) continue; // ~1/3 règlent partiellement leur arriéré
            $reglement = round($arr['solde'] * 0.5, -3);
            if ($reglement <= 0) continue;
            $recordPayment(
                $reglement,
                0,
                'guichet',
                'Règlement Arriéré Scolarité',
                'ARRIERE',
                $arr['code_inscription'],
                null,
                'Apurement partiel arriéré scolarité année antérieure'
            );
        }

        // 2. Paiements de l'année en cours (Frais Annexes strictement isolés + Scolarité par tranches)
        foreach ($inscriptionsByClasse as $inscs) {
            foreach ($inscs as $ins) {
                $tarifKey = "{$ins['filiere_code']}|{$ins['niveau_code']}|{$ins['regime']}";
                $tranches = $tarifs[$tarifKey]['tranches'] ?? [];
                $montantDu = (float)$ins['montant_scolarite'];

                // A) Frais Annexes (Caisse Annexe) — ~85% payent leurs frais annexes à la rentrée
                $montantAnnexe = (float)($annexes[$ins['type_filiere']] ?? 45000.00);
                if (mt_rand(1, 100) <= 85) {
                    $canalFA = (mt_rand(1, 100) <= 75) ? 'guichet' : 'en_ligne';
                    $recordPayment(
                        $montantAnnexe,
                        0,
                        $canalFA,
                        'Frais Annexes',
                        'FRAIS_ANNEXES',
                        $ins['code_inscription'],
                        'FRAIS_ANNEXES',
                        'Encaissement frais annexes (kit, tenue, badge, assurance)'
                    );
                }

                // B) Paiements de Scolarité (strictement séparés des Frais Annexes)
                // Années passées : ~85% soldés, ~15% avec arriérés réalistes
                // Année active 2025-2026 : ~45% soldés, ~40% partiels, ~15% seulement tranche 1
                $r = mt_rand(1, 100);
                if (!$isActiveYear) {
                    $tranchesToPay = ($r <= 85) ? count($tranches) : max(1, count($tranches) - 1);
                } else {
                    if ($r <= 45) $tranchesToPay = count($tranches);
                    elseif ($r <= 85) $tranchesToPay = max(1, count($tranches) - 1);
                    else $tranchesToPay = 1;
                }

                $totalScolaritePaye = 0.0;
                for ($t = 0; $t < $tranchesToPay && $t < count($tranches); $t++) {
                    $tr = $tranches[$t];
                    $mt = (float)$tr['montant_tranche'];
                    $canalSc = (mt_rand(1, 100) <= 75) ? 'guichet' : 'en_ligne';
                    $recordPayment(
                        $mt,
                        $t,
                        $canalSc,
                        $tr['libelle_tranche'],
                        'SCOLARITE',
                        $ins['code_inscription'],
                        $tr['code_tranche'],
                        'Règlement ' . $tr['libelle_tranche']
                    );
                    $totalScolaritePaye += $mt;
                }

                $reste = max(0.0, $montantDu - $totalScolaritePaye);
                if ($reste <= 0.01) {
                    $stmtUpdIns->execute([$ins['code_inscription']]);
                } else {
                    if (!$isActiveYear) {
                        $newArrears[] = [
                            'code_inscription' => $ins['code_inscription'],
                            'etudiant_code'    => $ins['etudiant_code'],
                            'solde'            => $reste,
                        ];
                    } else {
                        // Relance impayé pour ~60% des débiteurs de l'année active
                        if (mt_rand(1, 100) <= 60) {
                            $nivRel = ($reste > ($montantDu * 0.5)) ? 'relance_ferme' : 'rappel_amiable';
                            $canalR = $this->pick(['sms', 'whatsapp', 'email', 'appel']);
                            $stmtRel->execute([
                                $this->genCode('REL-'),
                                $ins['etudiant_code'],
                                $ins['code_inscription'],
                                $nivRel,
                                $canalR,
                                $reste,
                                $ins['telephone'],
                                "Rappel d'échéance de scolarité : solde restant de " . number_format($reste, 0, ',', ' ') . " FCFA.",
                                'envoye',
                                $this->comptableCode,
                                $anneeCode,
                                $this->etabCode,
                                ($startYear + 1) . '-03-' . str_pad((string)mt_rand(1, 25), 2, '0', STR_PAD_LEFT) . ' 10:30:00'
                            ]);
                            $this->inc('relances_impayes');
                        }
                    }
                }
            }
        }

        // 3. Mettre à jour les totaux exacts des sessions de caisse (Guichet uniquement)
        $stmtUpdSess = $this->pdo->prepare("
            UPDATE sessions_caisse
            SET total_especes = ?, total_mobile_money = ?, total_cheque_virement = ?,
                total_general = ?, solde_attendu = fond_initial + ?
            WHERE code_session = ?
        ");
        foreach ($sessions as $s) {
            $tot = $s['especes'] + $s['mobile_money'] + $s['cheque_vir'];
            $stmtUpdSess->execute([$s['especes'], $s['mobile_money'], $s['cheque_vir'], $tot, $s['especes'], $s['code_session']]);
        }

        return $newArrears;
    }

    private function seedDepenses(string $anneeCode, int $startYear, bool $isActiveYear, array $typeDepenses): void
    {
        if (empty($typeDepenses)) return;

        $descriptions = [
            'Règlement factures électricité CIE et eau SODECI du mois',
            'Paiement honoraires et vacations enseignants mensuels',
            'Achat ramettes papier A4, cartouches encre et fournitures secrétariat',
            'Abonnement fibre optique campus et maintenance réseau',
            'Entretien climatiseurs, salles de cours et sanitaires',
            'Loyer mensuel annexe pédagogique et charges locatives',
            'Acquisition consommables informatiques et câblage labo',
            'Frais de mission administrative et déplacements examens MESRS',
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO depenses (
                type_depense_code, code_depense, user_code, montant_depense, description_depense,
                piece_justificative, periode_depense, created_at_depense, annee_code,
                statut_depense, user_confirm, created_at_confirm, etablissement_code
            ) VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?)
        ");

        $months = [10, 11, 12, 1, 2, 3, 4, 5, 6];
        for ($i = 0; $i < $this->config['depenses_per_year']; $i++) {
            $td = $typeDepenses[$i % count($typeDepenses)];
            $m  = $months[$i % count($months)];
            $y  = ($m >= 9) ? $startYear : ($startYear + 1);
            $dt = sprintf('%04d-%02d-%02d 11:00:00', $y, $m, mt_rand(5, 25));

            $isPending = ($isActiveYear && $i >= $this->config['depenses_per_year'] - 2);
            $statut    = $isPending ? 'en_attente' : 'approuve';
            $uConf     = $isPending ? null : $this->adminCode;
            $dConf     = $isPending ? null : $dt;
            $montant   = (float)(mt_rand(3, 28) * 25000);

            $stmt->execute([
                $td['code_type_depense'],
                $this->genCode('DEP-'),
                $this->comptableCode,
                $montant,
                $descriptions[$i % count($descriptions)],
                $dt,
                $dt,
                $anneeCode,
                $statut,
                $uConf,
                $dConf,
                $this->etabCode
            ]);
            $this->inc('depenses');
        }
    }

    private function seedCompositionsAndNotes(
        string $anneeCode,
        int $startYear,
        bool $isActiveYear,
        array $semestres,
        array $classes,
        array $maquettes,
        array $inscriptionsByClasse
    ): void {
        $stmtComp = $this->pdo->prepare("
            INSERT INTO compositions (
                code_composition, libelle_composition, type_composition, semestre_code, coefficient,
                date_composition, date_fin_composition, statut_composition, annee_code,
                etablissement_code, user_code, created_at_composition
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtCnf = $this->pdo->prepare("
            INSERT INTO composition_niveau_filiere (code_composition_niveau_filiere, composition_code, niveau_code, filiere_code)
            VALUES (?, ?, ?, ?)
        ");
        $stmtCm = $this->pdo->prepare("
            INSERT INTO composition_matieres (composition_code, composition_niveau_filiere_code, matiere_code)
            VALUES (?, ?, ?)
        ");
        $stmtNote = $this->pdo->prepare("
            INSERT INTO notes (
                code_note, valeur_note, composition_code, composition_matiere_id, etudiant_code,
                observations, inscription_code, user_code, etablissement_code, created_at_note, statut_note
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif')
        ");

        $endYear = $startYear + 1;
        foreach ($semestres as $sIdx => $sem) {
            $isS1 = ($sIdx === 0);
            $evalDefs = [
                [
                    'libelle' => 'Devoir Surveillé — ' . $sem['libelle_semestre'],
                    'type'    => 'DEVOIR',
                    'coef'    => 1.00,
                    'd_deb'   => $isS1 ? "{$startYear}-11-18" : "{$endYear}-04-07",
                    'd_fin'   => $isS1 ? "{$startYear}-11-22" : "{$endYear}-04-11",
                    'statut'  => 'termine',
                    'fill_rate' => 100,
                ],
                [
                    'libelle' => 'Examen de Fin de ' . $sem['libelle_semestre'],
                    'type'    => 'EXAMEN',
                    'coef'    => 2.00,
                    'd_deb'   => $isS1 ? "{$endYear}-01-20" : "{$endYear}-06-15",
                    'd_fin'   => $isS1 ? "{$endYear}-01-25" : "{$endYear}-06-20",
                    'statut'  => ($isActiveYear && !$isS1) ? 'programme' : 'termine',
                    'fill_rate' => ($isActiveYear && !$isS1) ? 65 : 100,
                ],
            ];

            foreach ($evalDefs as $ev) {
                $compCode = $this->genCode('CMP-');
                $stmtComp->execute([
                    $compCode,
                    $ev['libelle'],
                    $ev['type'],
                    $sem['code_semestre'],
                    $ev['coef'],
                    $ev['d_deb'],
                    $ev['d_fin'],
                    $ev['statut'],
                    $anneeCode,
                    $this->etabCode,
                    $this->adminCode,
                    $ev['d_deb'] . ' 08:00:00'
                ]);
                $this->inc('compositions');

                foreach ($classes as $cl) {
                    $cnfCode = $this->genCode('CNF-');
                    $stmtCnf->execute([$cnfCode, $compCode, $cl['niveau_code'], $cl['filiere_code']]);
                    $this->inc('composition_niveau_filiere');

                    $mKey = $cl['filiere_code'] . '|' . $cl['niveau_code'];
                    $mats = $maquettes[$mKey] ?? [];
                    $inscs = $inscriptionsByClasse[$cl['code_classe']] ?? [];

                    foreach ($mats as $mat) {
                        $stmtCm->execute([$compCode, $cnfCode, $mat['code_matiere']]);
                        $cmId = (int)$this->pdo->lastInsertId();
                        $this->inc('composition_matieres');

                        foreach ($inscs as $ins) {
                            if ($ev['fill_rate'] < 100 && mt_rand(1, 100) > $ev['fill_rate']) {
                                continue;
                            }
                            // Note réaliste entre 5.5 et 18.5 (moyenne autour de 12.2)
                            $raw = (mt_rand(60, 175) + mt_rand(70, 185)) / 20.0;
                            $val = round(min(19.5, max(4.0, $raw)), 2);

                            if ($val >= 16) $obs = 'Très Bien';
                            elseif ($val >= 14) $obs = 'Bien';
                            elseif ($val >= 12) $obs = 'Assez Bien';
                            elseif ($val >= 10) $obs = 'Passable';
                            else $obs = 'Insuffisant';

                            $stmtNote->execute([
                                $this->genCode('NOT-'),
                                $val,
                                $compCode,
                                $cmId,
                                $ins['etudiant_code'],
                                $obs,
                                $ins['code_inscription'],
                                $this->adminCode,
                                $this->etabCode,
                                $ev['d_fin'] . ' 16:00:00'
                            ]);
                            $this->inc('notes');
                        }
                    }
                }
            }
        }
    }

    private function seedAbsences(string $anneeCode, int $startYear, array $inscriptionsByClasse, array $emploisByClasse): void
    {
        $motifs = ['Maladie (certificat médical fourni)', 'Retard prolongé non justifié', 'Raison familiale', 'Absence injustifiée au cours', 'Rendez-vous administratif'];
        $stmt = $this->pdo->prepare("
            INSERT INTO absences (
                code_absence, inscription_code, emploi_code, matiere_code, date_absence,
                duree_heures, justifiee, motif, annee_code, etablissement_code, created_at_absence
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($inscriptionsByClasse as $clCode => $inscs) {
            $slots = $emploisByClasse[$clCode] ?? [];
            if (empty($inscs) || empty($slots)) continue;

            // 3 absences réalistes par classe/année
            $nbAbs = min(3, count($inscs));
            for ($a = 0; $a < $nbAbs; $a++) {
                $ins   = $inscs[mt_rand(0, count($inscs) - 1)];
                $slot  = $slots[mt_rand(0, count($slots) - 1)];
                $month = $this->pick([10, 11, 1, 2, 3]);
                $y     = ($month >= 9) ? $startYear : ($startYear + 1);
                $dAbs  = sprintf('%04d-%02d-%02d', $y, $month, mt_rand(3, 25));
                $just  = (mt_rand(1, 100) <= 45) ? 'oui' : 'non';
                $motif = ($just === 'oui') ? $motifs[0] : $motifs[mt_rand(1, count($motifs) - 1)];

                $stmt->execute([
                    $this->genCode('ABS-'),
                    $ins['code_inscription'],
                    $slot['code_emploi'],
                    $slot['matiere_code'],
                    $dAbs,
                    2.00,
                    $just,
                    $motif,
                    $anneeCode,
                    $this->etabCode,
                    "{$dAbs} 12:30:00"
                ]);
                $this->inc('absences');
            }
        }
    }

    private function seedDocuments(string $anneeCode, int $startYear, array $catalog): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO documents (
                code_document, libelle_document, type_document, lien_document,
                filiere_code, annee_code, niveaux_code, etablisement_code, statut_document, created_at_document, user_code
            ) VALUES (?, ?, 'PDF', ?, ?, ?, ?, ?, 'actif', ?, ?)
        ");

        foreach (array_slice($catalog['filieres'], 0, 2) as $f) {
            $n = $catalog['niveaux'][0];
            $stmt->execute([
                $this->genCode('DOC-'),
                'Syllabus & Maquette Officielle ' . ($f['slug_filiere'] ?: $f['code_filiere']) . ' (' . $startYear . ')',
                'public/uploads/dossiers/syllabus_' . strtolower($f['slug_filiere'] ?: 'fil') . '_' . $startYear . '.pdf',
                $f['code_filiere'],
                $anneeCode,
                $n['code_niveau'],
                $this->etabCode,
                "{$startYear}-10-05 09:00:00",
                $this->adminCode
            ]);
            $this->inc('documents');
        }
    }

    private function seedEvenements(): void
    {
        $events = [
            ['Rentrée Solennelle & Journée d\'Intégration 2025-2026', 'Accueil des nouveaux étudiants et présentation des filières BTS & Licences du GROUPE EICG.', 1, '2025-09-20 09:00:00'],
            ['Journées Portes Ouvertes & Orientation BTS', 'Rencontre avec le corps enseignant, démonstrations informatiques et ateliers métiers.', 0, '2025-11-14 08:30:00'],
            ['Session de Soutenances de Rapports de Stage BTS', 'Présentation publique des mémoires de fin de cycle devant les jurys académiques et professionnels.', 0, '2026-06-25 08:00:00'],
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO evenements (titre_evenement, image_evenement, description_evenement, is_principal_evenement, date_creation_evenement, statut_evenement)
            VALUES (?, NULL, ?, ?, ?, 'actif')
        ");
        foreach ($events as $e) {
            $stmt->execute([$e[0], $e[1], $e[2], $e[3]]);
            $this->inc('evenements');
        }
    }
}

// Exécution directe via CLI
if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    $opts = [];
    $clearOnly = false;
    $clearYears = false;

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            echo "GEICG DatabaseSeeder — Options disponibles :\n"
               . "  --years=2023-2024,2024-2025,2025-2026  Années académiques à générer\n"
               . "  --students=10                          Nombre d'étudiants par classe\n"
               . "  --teachers=8                           Nombre d'enseignants (max 10)\n"
               . "  --depenses=10                          Nombre de dépenses par année\n"
               . "  --sessions=4                           Nombre de sessions de caisse par année\n"
               . "  --seed=2026                            Graine aléatoire déterministe\n"
               . "  --fresh                                Vide puis ré-injecte les données\n"
               . "  --clear                                Vide uniquement les données métier (sans ré-injecter)\n"
               . "  --clear-years                          À combiner avec --clear pour vider aussi la table annees\n"
               . "  --quiet                                Mode silencieux\n";
            exit(0);
        } elseif ($arg === '--clear') {
            $clearOnly = true;
        } elseif ($arg === '--clear-years') {
            $clearYears = true;
        } elseif ($arg === '--fresh') {
            $opts['fresh'] = true;
        } elseif (strpos($arg, '--years=') === 0) {
            $opts['years'] = explode(',', substr($arg, 8));
        } elseif (strpos($arg, '--students=') === 0) {
            $opts['students_per_class'] = (int)substr($arg, 11);
        } elseif (strpos($arg, '--teachers=') === 0) {
            $opts['teachers'] = (int)substr($arg, 11);
        } elseif (strpos($arg, '--depenses=') === 0) {
            $opts['depenses_per_year'] = (int)substr($arg, 11);
        } elseif (strpos($arg, '--sessions=') === 0) {
            $opts['sessions_per_year'] = (int)substr($arg, 11);
        } elseif (strpos($arg, '--seed=') === 0) {
            $opts['seed'] = (int)substr($arg, 7);
        } elseif ($arg === '--quiet') {
            $opts['verbose'] = false;
        }
    }

    try {
        $seeder = new DatabaseSeeder(null, $opts);
        if ($clearOnly) {
            $seeder->clear($clearYears);
        } else {
            $seeder->run();
        }
    } catch (Throwable $e) {
        fwrite(STDERR, "[ERREUR SEEDER] " . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
