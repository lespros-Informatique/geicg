<?php

class ModelHome extends BaseModel
{
    protected string $table = 'inscriptions';
    protected string $primaryKey = 'id_inscription';

    public function __construct()
    {
        parent::__construct();
    }

    public function getStats(?string $anneeCode = null): array
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

            // Total Inscriptions
            $stmtCountInsc = $db->prepare("SELECT COUNT(*) FROM inscriptions WHERE annee_code = ? AND statut_inscription != 'annule'");
            $stmtCountInsc->execute([$anneeCode ?: '']);
            $totalEtudiants = (int)$stmtCountInsc->fetchColumn();

            // Total Recouvrement (Paiements de l'année)
            $stmtPaiements = $db->prepare("SELECT COALESCE(SUM(montant_paiement), 0) FROM paiements WHERE (annee_code = ? OR annee_code IS NULL OR annee_code = '') AND statut_paiement = 'confirme'");
            $stmtPaiements->execute([$anneeCode ?: '']);
            $caEncaisse = (float)$stmtPaiements->fetchColumn();

            // Total Scolarité Attendue
            $stmtScolAtt = $db->prepare("SELECT COALESCE(SUM(montant_scolarite_inscription), 0) FROM inscriptions WHERE annee_code = ? AND statut_inscription != 'annule'");
            $stmtScolAtt->execute([$anneeCode ?: '']);
            $caAttendu = (float)$stmtScolAtt->fetchColumn();

            $reliquatImpayes = max(0, $caAttendu - $caEncaisse);

            // Classes actives dans l'année
            $stmtClasses = $db->prepare("SELECT COUNT(*) FROM classes WHERE annee_code = ? AND statut_classe = 'actif'");
            $stmtClasses->execute([$anneeCode ?: '']);
            $totalClasses = (int)$stmtClasses->fetchColumn();

            // Total Enseignants
            $stmtEns = $db->query("SELECT COUNT(*) FROM enseignants WHERE statut_enseignant = 'actif'");
            $totalEnseignants = (int)$stmtEns->fetchColumn();

            // Total Matières
            $stmtMat = $db->query("SELECT COUNT(*) FROM matieres WHERE statut_matiere = 'actif'");
            $totalMatieres = (int)$stmtMat->fetchColumn();

            // Total Dépenses de l'année
            $stmtDep = $db->prepare("SELECT COALESCE(SUM(montant_depense), 0) FROM depenses WHERE annee_code = ?");
            $stmtDep->execute([$anneeCode ?: '']);
            $totalDepenses = (float)$stmtDep->fetchColumn();

            return [
                'annee_code' => $anneeCode,
                'total_etudiants' => $totalEtudiants,
                'ca_encaisse' => $caEncaisse,
                'ca_attendu' => $caAttendu,
                'reliquat_impayes' => $reliquatImpayes,
                'total_classes' => $totalClasses,
                'total_enseignants' => $totalEnseignants,
                'total_matieres' => $totalMatieres,
                'total_depenses' => $totalDepenses,
            ];
        } catch (Exception $e) {
            error_log("ModelHome::getStats error: " . $e->getMessage());
            return [
                'total_etudiants' => 0,
                'ca_encaisse' => 0,
                'ca_attendu' => 0,
                'reliquat_impayes' => 0,
                'total_classes' => 0,
                'total_enseignants' => 0,
                'total_matieres' => 0,
                'total_depenses' => 0,
            ];
        }
    }

    public function getRecentInscriptions(int $limit = 5, ?string $anneeCode = null): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT i.*, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, c.libelle_classe
                    FROM inscriptions i
                    JOIN etudiants e ON i.etudiant_code = e.code_etudiant
                    LEFT JOIN classes c ON i.classe_code = c.code_classe
                    WHERE i.annee_code = ?
                    ORDER BY i.id_inscription DESC
                    LIMIT $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute([$anneeCode ?: '']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getRecentPaiements(int $limit = 5, ?string $anneeCode = null): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT p.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
                    FROM paiements p
                    JOIN inscriptions i ON p.inscription_code = i.code_inscription
                    JOIN etudiants e ON i.etudiant_code = e.code_etudiant
                    ORDER BY p.id_paiement DESC
                    LIMIT $limit";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}
