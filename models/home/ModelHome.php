<?php

class ModelHome extends BaseModel
{
    protected string $table = 'inscriptions';
    protected string $primaryKey = 'id_inscription';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Récupère l'ensemble des statistiques du Dashboard adaptées aux vues SQL
     */
    public function getStats(?string $anneeCode = null, ?string $userCode = null, ?string $roleCode = null): array
    {
        try {
            $db = $this->pdo->getCon();

            if (!$anneeCode) {
                $anneeCode = $_SESSION['annee_active_code'] ?? null;
            }
            if (!$anneeCode) {
                $stmtA = $db->query("SELECT code_annee, libelle_annee FROM annees WHERE statut_annee = 'actif' ORDER BY id_annee DESC LIMIT 1");
                $activeRow = $stmtA ? $stmtA->fetch(PDO::FETCH_ASSOC) : null;
                if (!$activeRow) {
                    $stmtFallback = $db->query("SELECT code_annee, libelle_annee FROM annees ORDER BY id_annee DESC LIMIT 1");
                    $activeRow = $stmtFallback ? $stmtFallback->fetch(PDO::FETCH_ASSOC) : null;
                }
                if ($activeRow) {
                    $anneeCode = $activeRow['code_annee'];
                    $_SESSION['annee_active_code'] = $activeRow['code_annee'];
                    $_SESSION['annee_active_libelle'] = $activeRow['libelle_annee'];
                }
            }

            // 1. Synthèse Annuelle Globale via la Vue SQL v_dash_synthese_annuelle
            $stmtSynthese = $db->prepare("SELECT * FROM v_dash_synthese_annuelle WHERE code_annee = ?");
            $stmtSynthese->execute([$anneeCode ?: '']);
            $synthese = $stmtSynthese->fetch(PDO::FETCH_ASSOC) ?: [];

            $totalEtudiants = (int)($synthese['total_inscrits'] ?? 0);
            $totalClasses = (int)($synthese['total_classes'] ?? 0);
            $caAttendu = (float)($synthese['total_scolarite_attendue'] ?? 0);
            $caEncaisse = (float)($synthese['total_encaisse'] ?? 0);
            $totalDepenses = (float)($synthese['total_depenses'] ?? 0);
            $reliquatImpayes = max(0, $caAttendu - $caEncaisse);
            $soldeNet = $caEncaisse - $totalDepenses;

            // 2. Compteurs Pédagogiques
            $totalEnseignants = (int)$db->query("SELECT COUNT(*) FROM enseignants WHERE statut_enseignant = 'actif'")->fetchColumn();
            $totalMatieres = (int)$db->query("SELECT COUNT(*) FROM matieres WHERE statut_matiere = 'actif'")->fetchColumn();
            $totalSalles = (int)$db->query("SELECT COUNT(*) FROM salles WHERE statut_salle = 'actif'")->fetchColumn();
            
            // Notes & Absences de l'année
            $totalNotes = 0;
            try {
                $stmtNotes = $db->prepare("
                    SELECT COUNT(*) FROM notes n 
                    INNER JOIN inscriptions i ON i.code_inscription = n.inscription_code 
                    WHERE i.annee_code = ? AND (n.statut_note = 'actif' OR n.statut_note IS NULL)
                ");
                $stmtNotes->execute([$anneeCode ?: '']);
                $totalNotes = (int)$stmtNotes->fetchColumn();
            } catch (Exception $e) {}

            $totalAbsences = 0;
            try {
                $stmtAbs = $db->prepare("
                    SELECT COUNT(*) FROM absences a 
                    INNER JOIN inscriptions i ON i.code_inscription = a.inscription_code 
                    WHERE i.annee_code = ?
                ");
                $stmtAbs->execute([$anneeCode ?: '']);
                $totalAbsences = (int)$stmtAbs->fetchColumn();
            } catch (Exception $e) {}

            // 3. Stats pour le Corps Enseignant (si profil Enseignant)
            $teacherCoursesCount = 0;
            $teacherClassesCount = 0;
            if ($roleCode === 'ROLE_ENSEIGNANT' && $userCode) {
                try {
                    $stmtTeach = $db->prepare("SELECT code_enseignant FROM enseignants WHERE user_code = ? OR code_enseignant = ?");
                    $stmtTeach->execute([$userCode, $userCode]);
                    $teacherCode = $stmtTeach->fetchColumn() ?: $userCode;

                    $stmtTC = $db->prepare("SELECT COUNT(*), COUNT(DISTINCT classe_code) FROM v_dash_pedagogie_affectations WHERE (enseignant_code = ? OR enseignant_code = ?)");
                    $stmtTC->execute([$teacherCode, $userCode]);
                    $resTC = $stmtTC->fetch(PDO::FETCH_NUM);
                    $teacherCoursesCount = (int)($resTC[0] ?? 0);
                    $teacherClassesCount = (int)($resTC[1] ?? 0);
                } catch (Exception $e) {}
            }

            // 4. Stats pour les Modules Complémentaires
            $totalActualites = 0;
            try {
                $totalActualites = (int)$db->query("SELECT COUNT(*) FROM actualites WHERE statut_actualite = 'actif'")->fetchColumn();
            } catch (Exception $e) {}

            $totalEvenements = 0;
            try {
                $totalEvenements = (int)$db->query("SELECT COUNT(*) FROM evenements WHERE statut_evenement = 'actif'")->fetchColumn();
            } catch (Exception $e) {}

            $totalDocuments = 0;
            try {
                $totalDocuments = (int)$db->query("SELECT COUNT(*) FROM documents WHERE statut_document = 'actif'")->fetchColumn();
            } catch (Exception $e) {}

            $totalFilieres = 0;
            try {
                $totalFilieres = (int)$db->query("SELECT COUNT(*) FROM filieres WHERE statut_filiere = 'actif'")->fetchColumn();
            } catch (Exception $e) {}

            $totalCycles = 0;
            try {
                $totalCycles = (int)$db->query("SELECT COUNT(*) FROM cycles WHERE statut_cycle = 'actif'")->fetchColumn();
            } catch (Exception $e) {}

            $totalNiveaux = 0;
            try {
                $totalNiveaux = (int)$db->query("SELECT COUNT(*) FROM niveaux WHERE statut_niveau = 'actif'")->fetchColumn();
            } catch (Exception $e) {}

            $totalParents = 0;
            try {
                $totalParents = (int)$db->query("SELECT COUNT(*) FROM parents")->fetchColumn();
            } catch (Exception $e) {}

            $totalSessionsCaisse = 0;
            try {
                $totalSessionsCaisse = (int)$db->query("SELECT COUNT(*) FROM ouvertures_caisse WHERE statut_ouverture = 'ouverte'")->fetchColumn();
            } catch (Exception $e) {}

            $totalPieces = 0;
            try {
                $totalPieces = (int)$db->query("SELECT COUNT(*) FROM piece_fournir_cycle WHERE statut_piece_cycle = 'actif'")->fetchColumn();
            } catch (Exception $e) {}

            $totalAccessoires = 0;
            try {
                $totalAccessoires = (int)$db->query("SELECT COUNT(*) FROM accessoires WHERE statut_accessoire = 'actif'")->fetchColumn();
            } catch (Exception $e) {}

            $totalUsers = 0;
            try {
                $totalUsers = (int)$db->query("SELECT COUNT(*) FROM users WHERE statut_user = 'actif'")->fetchColumn();
            } catch (Exception $e) {}

            $monthlyFinancials = $this->getMonthlyFinancials($anneeCode);
            $filieresDistribution = $this->getFilieresDistribution($anneeCode);
            $guichetAlerts = $this->getGuichetAlerts($anneeCode);

            return [
                'annee_code' => $anneeCode,
                'total_etudiants' => $totalEtudiants,
                'ca_encaisse' => $caEncaisse,
                'ca_attendu' => $caAttendu,
                'reliquat_impayes' => $reliquatImpayes,
                'total_depenses' => $totalDepenses,
                'solde_net' => $soldeNet,
                'total_classes' => $totalClasses,
                'total_enseignants' => $totalEnseignants,
                'total_matieres' => $totalMatieres,
                'total_salles' => $totalSalles,
                'total_notes' => $totalNotes,
                'total_absences' => $totalAbsences,
                'teacher_courses' => $teacherCoursesCount,
                'teacher_classes' => $teacherClassesCount,
                'total_actualites' => $totalActualites,
                'total_evenements' => $totalEvenements,
                'total_documents' => $totalDocuments,
                'total_filieres' => $totalFilieres,
                'total_cycles' => $totalCycles,
                'total_niveaux' => $totalNiveaux,
                'total_parents' => $totalParents,
                'total_sessions_caisse' => $totalSessionsCaisse,
                'total_pieces' => $totalPieces,
                'total_accessoires' => $totalAccessoires,
                'total_users' => $totalUsers,
                'monthly_financials' => $monthlyFinancials,
                'filieres_distribution' => $filieresDistribution,
                'guichet_alerts' => $guichetAlerts,
            ];
        } catch (Exception $e) {
            error_log("ModelHome::getStats error: " . $e->getMessage());
            return [
                'annee_code' => $anneeCode,
                'total_etudiants' => 0,
                'ca_encaisse' => 0,
                'ca_attendu' => 0,
                'reliquat_impayes' => 0,
                'total_depenses' => 0,
                'solde_net' => 0,
                'total_classes' => 0,
                'total_enseignants' => 0,
                'total_matieres' => 0,
                'total_salles' => 0,
                'total_notes' => 0,
                'total_absences' => 0,
                'teacher_courses' => 0,
                'teacher_classes' => 0,
                'total_actualites' => 0,
                'total_evenements' => 0,
                'total_documents' => 0,
                'monthly_financials' => ['labels' => [], 'encaissements' => [], 'depenses' => []],
                'filieres_distribution' => ['labels' => [], 'series' => []],
                'guichet_alerts' => ['dossiers_incomplets' => 0, 'kits_en_attente' => 0],
            ];
        }
    }

    /**
     * Flux de trésorerie mensuel (Encaissements vs Dépenses) pour les graphiques
     */
    public function getMonthlyFinancials(?string $anneeCode = null): array
    {
        try {
            $db = $this->pdo->getCon();
            $anneeCode = $anneeCode ?: ($_SESSION['annee_active_code'] ?? '');

            $encaissements = array_fill(1, 12, 0);
            $depenses = array_fill(1, 12, 0);

            $stmtP = $db->prepare("
                SELECT MONTH(date_paiement) as mois, SUM(montant_paiement) as total
                FROM paiements p
                JOIN inscriptions i ON i.code_inscription = p.inscription_code
                WHERE (i.annee_code = ? OR ? = '') AND (p.statut_paiement = 'confirme' OR p.statut_paiement != 'annule')
                GROUP BY MONTH(date_paiement)
            ");
            $stmtP->execute([$anneeCode, $anneeCode]);
            while ($r = $stmtP->fetch(PDO::FETCH_ASSOC)) {
                $m = (int)$r['mois'];
                if ($m >= 1 && $m <= 12) {
                    $encaissements[$m] = (float)$r['total'];
                }
            }

            $stmtD = $db->prepare("
                SELECT MONTH(COALESCE(periode_depense, created_at_depense)) as mois, SUM(montant_depense) as total
                FROM depenses
                WHERE (annee_code = ? OR ? = '') AND (statut_depense != 'annule' OR statut_depense IS NULL)
                GROUP BY MONTH(COALESCE(periode_depense, created_at_depense))
            ");
            $stmtD->execute([$anneeCode, $anneeCode]);
            while ($r = $stmtD->fetch(PDO::FETCH_ASSOC)) {
                $m = (int)$r['mois'];
                if ($m >= 1 && $m <= 12) {
                    $depenses[$m] = (float)$r['total'];
                }
            }

            $labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

            return [
                'labels' => $labels,
                'encaissements' => array_values($encaissements),
                'depenses' => array_values($depenses)
            ];
        } catch (Exception $e) {
            error_log("ModelHome::getMonthlyFinancials error: " . $e->getMessage());
            return ['labels' => [], 'encaissements' => [], 'depenses' => []];
        }
    }

    /**
     * Répartition des effectifs d'étudiants par Filières pour les graphiques
     */
    public function getFilieresDistribution(?string $anneeCode = null): array
    {
        try {
            $db = $this->pdo->getCon();
            $anneeCode = $anneeCode ?: ($_SESSION['annee_active_code'] ?? '');

            $stmt = $db->prepare("
                SELECT f.libelle_filiere, COUNT(DISTINCT i.code_inscription) as total_inscrits
                FROM inscriptions i
                JOIN classes cl ON cl.code_classe = i.classe_code
                JOIN filieres f ON f.code_filiere = cl.filiere_code
                WHERE (i.annee_code = ? OR ? = '') AND (i.statut_inscription != 'annule' OR i.statut_inscription IS NULL)
                GROUP BY f.code_filiere, f.libelle_filiere
                ORDER BY total_inscrits DESC
                LIMIT 8
            ");
            $stmt->execute([$anneeCode, $anneeCode]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $labels = [];
            $series = [];
            foreach ($rows as $r) {
                $labels[] = $r['libelle_filiere'];
                $series[] = (int)$r['total_inscrits'];
            }

            return [
                'labels' => $labels,
                'series' => $series
            ];
        } catch (Exception $e) {
            error_log("ModelHome::getFilieresDistribution error: " . $e->getMessage());
            return ['labels' => [], 'series' => []];
        }
    }

    /**
     * Compteurs d'alertes guichet (dossiers incomplets & kits en attente)
     */
    public function getGuichetAlerts(?string $anneeCode = null): array
    {
        try {
            $db = $this->pdo->getCon();
            $anneeCode = $anneeCode ?: ($_SESSION['annee_active_code'] ?? '');

            $stmtDossiers = $db->prepare("
                SELECT COUNT(DISTINCT i.code_inscription)
                FROM inscriptions i
                JOIN classes cl ON cl.code_classe = i.classe_code
                LEFT JOIN filiere_cycles fc ON fc.filiere_code = cl.filiere_code
                JOIN piece_fournir_cycle pfc ON (pfc.cycle_code = fc.cycle_code OR pfc.cycle_code IS NULL OR pfc.cycle_code = '') 
                     AND (pfc.niveau_code = cl.niveau_code OR pfc.niveau_code IS NULL OR pfc.niveau_code = '') 
                     AND pfc.statut_piece_cycle = 'actif'
                LEFT JOIN dossier_etudiant de ON de.inscription_code = i.code_inscription AND de.piece_code = pfc.piece_code
                WHERE (i.annee_code = ? OR ? = '')
                  AND (de.statut_depot IS NULL OR de.statut_depot = 'en_attente')
            ");
            $stmtDossiers->execute([$anneeCode, $anneeCode]);
            $dossiersIncomplets = (int)$stmtDossiers->fetchColumn();

            $stmtKits = $db->prepare("
                SELECT COUNT(*)
                FROM accessoire_inscription ai
                JOIN inscriptions i ON i.code_inscription = ai.inscription_code
                WHERE (i.annee_code = ? OR ? = '') AND ai.etat_retrait = 'en_attente'
            ");
            $stmtKits->execute([$anneeCode, $anneeCode]);
            $kitsEnAttente = (int)$stmtKits->fetchColumn();

            return [
                'dossiers_incomplets' => $dossiersIncomplets,
                'kits_en_attente' => $kitsEnAttente
            ];
        } catch (Exception $e) {
            return ['dossiers_incomplets' => 0, 'kits_en_attente' => 0];
        }
    }

    /**
     * Dernières inscriptions depuis la Vue SQL v_dash_inscriptions_details
     */
    public function getRecentInscriptions(int $limit = 5, ?string $anneeCode = null): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT * FROM v_dash_inscriptions_details
                    WHERE annee_code = ? AND (statut_inscription != 'annule' OR statut_inscription IS NULL)
                    ORDER BY id_inscription DESC
                    LIMIT $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute([$anneeCode ?: '']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelHome::getRecentInscriptions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Derniers règlements depuis la Vue SQL v_dash_paiements_details
     */
    public function getRecentPaiements(int $limit = 5, ?string $anneeCode = null): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT * FROM v_dash_paiements_details
                    WHERE (annee_code = ? OR ? = '')
                      AND (statut_paiement = 'confirme' OR statut_paiement != 'annule')
                    ORDER BY id_paiement DESC
                    LIMIT $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute([$anneeCode ?? '', $anneeCode ?? '']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelHome::getRecentPaiements error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Dernières dépenses engagées depuis la Vue SQL v_dash_depenses_details
     */
    public function getRecentDepenses(int $limit = 5, ?string $anneeCode = null): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT * FROM v_dash_depenses_details
                    WHERE (annee_code = ? OR ? = '') AND (statut_depense != 'annule' OR statut_depense IS NULL)
                    ORDER BY id_depense DESC
                    LIMIT $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute([$anneeCode ?? '', $anneeCode ?? '']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelHome::getRecentDepenses error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cours attribués à un enseignant depuis la Vue SQL v_dash_pedagogie_affectations
     */
    public function getTeacherCourses(string $userOrTeacherCode, int $limit = 6): array
    {
        try {
            $db = $this->pdo->getCon();
            $stmt = $db->prepare("
                SELECT * FROM v_dash_pedagogie_affectations 
                WHERE enseignant_code = ?
                ORDER BY libelle_matiere ASC
                LIMIT $limit
            ");
            $stmt->execute([$userOrTeacherCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}
