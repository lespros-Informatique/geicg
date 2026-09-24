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

        $annees = $this->getAccessibleAnnees();

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
        $filterStatut = $_GET['filter_statut'] ?? 'all';

        try {
            $db = $this->model->getCon();

            $sql = "
                SELECT 
                    i.code_inscription,
                    i.created_at_inscription AS date_inscription,
                    i.statut_inscription,
                    e.code_etudiant,
                    e.matricule_etudiant,
                    e.nom_etudiant,
                    e.prenom_etudiant,
                    e.photo_etudiant,
                    e.telephone_etudiant,
                    cl.code_classe,
                    cl.libelle_classe,
                    cl.filiere_code
                FROM inscriptions i
                JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                JOIN classes cl ON cl.code_classe = i.classe_code
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

            $sql .= " GROUP BY i.code_inscription ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC, e.prenom_etudiant ASC";

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
                $estComplet = ($totalPieces > 0 && $deposeesCount === $totalPieces);

                // Filtrage selon le statut sélectionné
                if ($filterStatut === 'incomplet' && $estComplet) continue;
                if ($filterStatut === 'complet' && !$estComplet) continue;
                if ($filterStatut === 'rejete' && $rejeteesCount === 0) continue;

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
                    'est_complet' => $estComplet,
                    'dossier_items' => $dossierItems
                ];
            }

            $this->json(['data' => $data]);
        } catch (Exception $e) {
            error_log("DossierEtudiantController::apiList error: " . $e->getMessage());
            $this->json(['data' => [], 'error' => $e->getMessage()]);
        }
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

        // Traitement de l'upload du fichier scanné joint
        $fichierJoint = null;
        if (isset($_FILES['fichier_joint']) && $_FILES['fichier_joint']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['fichier_joint'];
            $maxSize = 10 * 1024 * 1024; // 10 MB
            if ($file['size'] > $maxSize) {
                $this->error('Le fichier scanné dépasse la taille maximale autorisée de 10 Mo.');
                return;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx'];
            if (!in_array($ext, $allowedExts, true)) {
                $this->error('Format de fichier non autorisé. Formats acceptés : PDF, PNG, JPG, JPEG, DOC, DOCX.');
                return;
            }

            $uploadDir = __DIR__ . '/../../public/uploads/dossiers/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            $safePieceCode = preg_replace('/[^a-zA-Z0-9_-]/', '', $pieceCode);
            $safeInscrCode = preg_replace('/[^a-zA-Z0-9_-]/', '', $inscriptionCode);
            $filename = 'DOS_' . $safeInscrCode . '_' . $safePieceCode . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $fichierJoint = 'public/uploads/dossiers/' . $filename;
            } else {
                error_log("DossierEtudiantController::saveStatut failed to upload file: " . $file['name']);
            }
        }

        $ok = $this->model->saveStatutPiece($inscriptionCode, $etudiantCode, $pieceCode, $statut, $observations, $userCode, $fichierJoint);

        if ($ok) {
            $this->success('Statut et documents du dossier mis à jour avec succès !', [
                'statut' => $statut,
                'date_depot' => date('d/m/Y H:i'),
                'fichier_joint' => $fichierJoint ? (RACINE . $fichierJoint) : null
            ]);
        } else {
            $this->error($this->model->getLastError() ?: 'Erreur lors de l\'enregistrement du statut de dépôt.');
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

    /**
     * Génère les données complètes pour l'impression du Récépissé  de Dépôt
     */
    public function getRecepisseData()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_DEPOT_DOSSIERS', 'VIEW_DEPOT_DOSSIERS']);

        $inscriptionCode = trim($_GET['inscription_code'] ?? ($_POST['inscription_code'] ?? ''));
        if (empty($inscriptionCode)) {
            $this->error('Code d\'inscription manquant.');
            return;
        }

        try {
            $db = $this->model->getCon();
            $stmt = $db->prepare("
                SELECT 
                    i.code_inscription, i.created_at_inscription AS date_inscription, i.statut_inscription,
                    e.code_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, e.telephone_etudiant, e.photo_etudiant,
                    cl.libelle_classe, cl.filiere_code,
                    a.libelle_annee,
                    et.libelle_etablissement AS nom_etablissement, et.logo_etablissement, et.telephone_etablissement, et.adresse_etablissement
                FROM inscriptions i
                JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                JOIN classes cl ON cl.code_classe = i.classe_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code
                LEFT JOIN etablissements et ON et.code_etablissement = i.etablissement_code
                WHERE i.code_inscription = ?
                LIMIT 1
            ");
            $stmt->execute([$inscriptionCode]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                $this->error('Dossier d\'inscription introuvable.');
                return;
            }

            $dossierItems = $this->model->getDossierByInscription($inscriptionCode);
            $piecesDeposees = [];
            $piecesManquantes = [];

            foreach ($dossierItems as $p) {
                if ($p['statut_depot'] === 'depose') {
                    $piecesDeposees[] = $p;
                } elseif ($p['statut_depot'] !== 'non_requis') {
                    $piecesManquantes[] = $p;
                }
            }

            $agentName = ($_SESSION[USERS_AUTH]['prenom_user'] ?? '') . ' ' . ($_SESSION[USERS_AUTH]['nom_user'] ?? '');

            $this->json([
                'status' => 1,
                'student' => $student,
                'pieces_deposees' => $piecesDeposees,
                'pieces_manquantes' => $piecesManquantes,
                'total_pieces' => count($dossierItems),
                'deposees_count' => count($piecesDeposees),
                'manquantes_count' => count($piecesManquantes),
                'pourcentage' => (count($dossierItems) > 0) ? round((count($piecesDeposees) / count($dossierItems)) * 100) : 100,
                'date_emission' => date('d/m/Y H:i'),
                'agent_nom' => trim($agentName) ?: 'Administration'
            ]);
        } catch (Exception $e) {
            error_log("DossierEtudiantController::getRecepisseData error: " . $e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
