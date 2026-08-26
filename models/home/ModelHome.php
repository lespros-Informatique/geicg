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
                $activeRow = $stmtA->fetch(PDO::FETCH_ASSOC);
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
            $stmtNotes = $db->prepare("
                SELECT COUNT(*) FROM notes n 
                INNER JOIN inscriptions i ON i.code_inscription = n.inscription_code 
                WHERE i.annee_code = ? AND n.statut_note = 'actif'
            ");
            $stmtNotes->execute([$anneeCode ?: '']);
            $totalNotes = (int)$stmtNotes->fetchColumn();

            $stmtAbs = $db->prepare("
                SELECT COUNT(*) FROM absences a 
                INNER JOIN inscriptions i ON i.code_inscription = a.inscription_code 
                WHERE i.annee_code = ? AND a.statut_absence = 'actif'
            ");
            $stmtAbs->execute([$anneeCode ?: '']);
            $totalAbsences = (int)$stmtAbs->fetchColumn();

            // 3. Stats pour le Corps Enseignant (si profil Enseignant)
            $teacherCoursesCount = 0;
            $teacherClassesCount = 0;
            if ($roleCode === 'ROLE_ENSEIGNANT' && $userCode) {
                // Chercher le code_enseignant relié
                $stmtTeach = $db->prepare("SELECT code_enseignant FROM enseignants WHERE user_code = ? OR code_enseignant = ?");
                $stmtTeach->execute([$userCode, $userCode]);
                $teacherCode = $stmtTeach->fetchColumn() ?: $userCode;

                $stmtTC = $db->prepare("SELECT COUNT(*), COUNT(DISTINCT classe_code) FROM v_dash_pedagogie_affectations WHERE (enseignant_code = ? OR enseignant_code = ?)");
                $stmtTC->execute([$teacherCode, $userCode]);
                $resTC = $stmtTC->fetch(PDO::FETCH_NUM);
                $teacherCoursesCount = (int)($resTC[0] ?? 0);
                $teacherClassesCount = (int)($resTC[1] ?? 0);
            }

            // 4. Stats Communication
            $totalActualites = (int)$db->query("SELECT COUNT(*) FROM actualites WHERE statut_actualite = 'actif'")->fetchColumn();
            $totalEvenements = (int)$db->query("SELECT COUNT(*) FROM evenements WHERE statut_evenement = 'actif'")->fetchColumn();
            $totalDocuments = (int)$db->query("SELECT COUNT(*) FROM documents WHERE statut_document = 'actif'")->fetchColumn();

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
            ];
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
                    WHERE (annee_code = ? OR annee_code IS NULL OR annee_code = '')
                      AND (statut_paiement = 'confirme' OR statut_paiement != 'annule')
                    ORDER BY id_paiement DESC
                    LIMIT $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute([$anneeCode ?: '']);
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
                    WHERE annee_code = ? AND (statut_depense != 'annule' OR statut_depense IS NULL)
                    ORDER BY id_depense DESC
                    LIMIT $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute([$anneeCode ?: '']);
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
