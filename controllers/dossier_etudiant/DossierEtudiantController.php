<?php

class DossierEtudiantController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelDossierEtudiant();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_DEPOT_DOSSIERS', 'VIEW_DEPOT_DOSSIERS']);

        $anneeModel = new ModelAnnee();
        $annees = $anneeModel->getAll();

        if (isset($_GET['annee_code']) && !empty($_GET['annee_code'])) {
            $selectedAnneeCode = trim($_GET['annee_code']);
            foreach ($annees as $a) {
                if ($a['code_annee'] === $selectedAnneeCode) {
                    $_SESSION['annee_active_code'] = $a['code_annee'];
                    $_SESSION['annee_active_libelle'] = $a['libelle_annee'];
                    break;
                }
            }
        } else {
            $selectedAnneeCode = $_SESSION['annee_active_code'] ?? null;
        }

        $classeModel = new ModelClasse();
        $classes = $classeModel->getAll();

        $this->loadView('../views/dossier_etudiant/list.php', [
            'annees' => $annees,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_DEPOT_DOSSIERS', 'VIEW_DEPOT_DOSSIERS']);

        $anneeCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? '';
        $classeCode = $_GET['classe_code'] ?? '';

        $db = $this->model->getCon();

        $sql = "
            SELECT 
                i.code_inscription,
                i.date_inscription,
                i.statut_inscription,
                e.code_etudiant,
                e.matricule_etudiant,
                e.nom_etudiant,
                e.prenom_etudiant,
                e.photo_etudiant,
                e.telephone_etudiant,
                cl.code_classe,
                cl.libelle_classe,
                cl.filiere_code,
                fc.cycle_code
            FROM inscriptions i
            JOIN etudiants e ON e.code_etudiant = i.etudiant_code
            JOIN classes cl ON cl.code_classe = i.classe_code
            LEFT JOIN filiere_cycles fc ON fc.filiere_code = cl.filiere_code
            WHERE 1=1
        ";

        $params = [];
        if (!empty($anneeCode)) {
            $sql .= " AND i.annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($classeCode)) {
            $sql .= " AND i.classe_code = ?";
            $params[] = $classeCode;
        }

        $sql .= " ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC, e.prenom_etudiant ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($rows as $r) {
            $dossierItems = $this->model->getDossierByInscription($r['code_inscription']);
            
            $totalPieces = count($dossierItems);
            $deposeesCount = 0;
            $enAttenteCount = 0;
            $rejeteesCount = 0;

            foreach ($dossierItems as $item) {
                if ($item['statut_depot'] === 'depose') {
                    $deposeesCount++;
                } elseif ($item['statut_depot'] === 'rejete') {
                    $rejeteesCount++;
                } else {
                    $enAttenteCount++;
                }
            }

            $pourcentage = ($totalPieces > 0) ? round(($deposeesCount / $totalPieces) * 100) : 100;

            $data[] = [
                'code_inscription' => $r['code_inscription'],
                'code_etudiant' => $r['code_etudiant'],
                'matricule' => $r['matricule_etudiant'],
                'nom_complet' => trim($r['nom_etudiant'] . ' ' . $r['prenom_etudiant']),
                'nom_etudiant' => $r['nom_etudiant'],
                'prenom_etudiant' => $r['prenom_etudiant'],
                'photo' => $r['photo_etudiant'],
                'classe' => $r['libelle_classe'],
                'total_pieces' => $totalPieces,
                'deposees_count' => $deposeesCount,
                'en_attente_count' => $enAttenteCount,
                'rejetees_count' => $rejeteesCount,
                'pourcentage' => $pourcentage,
                'est_complet' => ($totalPieces > 0 && $deposeesCount === $totalPieces),
                'dossier_items' => $dossierItems
            ];
        }

        $this->json(['data' => $data]);
    }

    public function saveStatut()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['MANAGE_DEPOT_DOSSIERS', 'MANAGE_PIECES', 'MANAGE_INSCRIPTIONS']);

        $inscriptionCode = trim($_POST['inscription_code'] ?? '');
        $etudiantCode = trim($_POST['etudiant_code'] ?? '');
        $pieceCode = trim($_POST['piece_code'] ?? '');
        $statut = trim($_POST['statut'] ?? 'depose');
        $observations = trim($_POST['observations'] ?? '');
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';

        if (empty($inscriptionCode) || empty($pieceCode)) {
            $this->error('Informations insuffisantes pour modifier le statut du dépôt.');
            return;
        }

        $allowedStatuts = ['depose', 'en_attente', 'rejete', 'non_requis'];
        if (!in_array($statut, $allowedStatuts, true)) {
            $statut = 'depose';
        }

        $ok = $this->model->saveStatutPiece($inscriptionCode, $etudiantCode, $pieceCode, $statut, $observations, $userCode);

        if ($ok) {
            $this->success('Statut de la pièce mis à jour avec succès !', [
                'statut' => $statut,
                'date_depot' => date('d/m/Y H:i')
            ]);
        } else {
            $this->error('Erreur lors de l\'enregistrement du statut de dépôt.');
        }
    }

    public function saveAll()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['MANAGE_DEPOT_DOSSIERS', 'MANAGE_PIECES', 'MANAGE_INSCRIPTIONS']);

        $inscriptionCode = trim($_POST['inscription_code'] ?? '');
        $etudiantCode = trim($_POST['etudiant_code'] ?? '');
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';

        if (empty($inscriptionCode)) {
            $this->error('Inscription introuvable.');
            return;
        }

        $items = $this->model->getDossierByInscription($inscriptionCode);
        if (empty($items)) {
            $this->error('Aucune pièce répertoriée pour ce dossier.');
            return;
        }

        $count = 0;
        foreach ($items as $item) {
            $ok = $this->model->saveStatutPiece($inscriptionCode, $etudiantCode, $item['piece_code'], 'depose', 'Tout déposé en 1 clic', $userCode);
            if ($ok) $count++;
        }

        if ($count > 0) {
            $this->success("{$count} pièce(s) marquée(s) comme DÉPOSÉES avec succès !");
        } else {
            $this->error('Erreur lors de la validation globale du dossier.');
        }
    }
}
