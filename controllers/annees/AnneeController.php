<?php

class AnneeController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelAnnee();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/annees/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_annee'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function validateDatesAnnee(string $dateDebut, string $dateFin): ?string
    {
        if (empty($dateDebut) || empty($dateFin)) {
            return "Les dates de début et de fin de l'année académique sont obligatoires.";
        }

        $timeDebut = strtotime($dateDebut);
        $timeFin = strtotime($dateFin);

        if (!$timeDebut || !$timeFin) {
            return "Les dates saisies ne sont pas valide (AAAA-MM-JJ).";
        }

        if ($timeFin <= $timeDebut) {
            $debutFormatted = date('d/m/Y', $timeDebut);
            $finFormatted = date('d/m/Y', $timeFin);
            return "Incohérence des dates : La date de fin ($finFormatted) doit être strictement postérieure à la date de début ($debutFormatted).";
        }

        if (($timeFin - $timeDebut) < (30 * 86400)) {
            return "Une année académique doit couvrir une durée minimale d'au moins 30 jours.";
        }

        return null;
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_annee'])) {
            if (!$this->checkUnique('annees', 'libelle_annee', $data['libelle_annee'], 'Annee academique')) return;
        }

        // Validation stricte des dates
        $dateErr = $this->validateDatesAnnee($data['date_debut_annee'] ?? '', $data['date_fin_annee'] ?? '');
        if ($dateErr) {
            $this->error($dateErr);
            return;
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        if (empty($data['code_annee'])) {
            $data['code_annee'] = $this->validator->generateCode('annees', 'code_annee', 'ANN-', 8);
        }
        
        // Toute nouvelle année académique est obligatoirement créée avec le statut inactif
        $data['statut_annee'] = 'inactif';
        $data['created_at_annee'] = date('Y-m-d H:i:s');
        
        $cols = $this->model->getCon()->query("DESCRIBE annees")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Année académique créée avec succès (statut initial : inactif).');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_annee');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        
        $currentItem = $this->model->getById($id);
        if (!$currentItem) { $this->error('Année académique introuvable'); return; }

        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_annee'])) {
            if (!$this->checkUnique('annees', 'libelle_annee', $data['libelle_annee'], 'Annee academique', 'id_annee', $id)) return;
        }

        // Validation stricte des dates
        $dateDebut = $data['date_debut_annee'] ?? ($currentItem['date_debut_annee'] ?? '');
        $dateFin = $data['date_fin_annee'] ?? ($currentItem['date_fin_annee'] ?? '');
        $dateErr = $this->validateDatesAnnee($dateDebut, $dateFin);
        if ($dateErr) {
            $this->error($dateErr);
            return;
        }

        // Vérification des règles de dates si changement de statut
        $newStatus = $data['statut_annee'] ?? ($currentItem['statut_annee'] ?? 'inactif');
        $oldStatus = $currentItem['statut_annee'] ?? 'inactif';

        $evalItem = array_merge($currentItem, $data);

        if ($newStatus === 'actif' && $oldStatus !== 'actif') {
            $errorMsg = '';
            if (!$this->model->canActivate($evalItem, $errorMsg)) {
                $this->error($errorMsg);
                return;
            }
        } elseif ($newStatus === 'inactif' && $oldStatus === 'actif') {
            $errorMsg = '';
            if (!$this->model->canClose($evalItem, $errorMsg)) {
                $this->error($errorMsg);
                return;
            }
        }

        $cols = $this->model->getCon()->query("DESCRIBE annees")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            if ($newStatus === 'actif') {
                $this->model->setActiveYear($id);
                $updatedRow = $this->model->getById($id);
                if ($updatedRow) {
                    $_SESSION['annee_active_code'] = $updatedRow['code_annee'];
                    $_SESSION['annee_active_libelle'] = $updatedRow['libelle_annee'];
                }
            }
            $this->success('Année académique modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');
        $item = $id ? $this->model->getById($id) : null;
        if ($item) {
            $currentStatus = $item['statut_annee'] ?? 'inactif';
            $errorMsg = '';

            // 1. Tentative d'activation : la date de début doit être arrivée
            if ($currentStatus !== 'actif') {
                if (!$this->model->canActivate($item, $errorMsg)) {
                    $this->error($errorMsg);
                    return;
                }
            } 
            // 2. Tentative de clôture / désactivation : la date de fin doit être arrivée
            else {
                if (!$this->model->canClose($item, $errorMsg)) {
                    $this->error($errorMsg);
                    return;
                }
            }

            if ($this->model->toggleStatus($id)) {
                $activeYear = $this->model->getActiveYear();
                if ($activeYear) {
                    $_SESSION['annee_active_code'] = $activeYear['code_annee'];
                    $_SESSION['annee_active_libelle'] = $activeYear['libelle_annee'];
                }
                $this->success('Statut mis à jour avec succès (Une seule année active à la fois) !', [
                    'reload' => true,
                    'activeYear' => $activeYear
                ]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Année académique introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { 
                $this->renderNotFound("L'année académique demandée est introuvable.");
                return;
            }
            
            $anneeCode = $item['code_annee'];
            
            // Statistiques
            $stmtStats = $this->model->getCon()->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM inscriptions WHERE annee_code = ? AND statut_inscription = 'actif') as total_etudiants,
                    (SELECT COUNT(*) FROM classes WHERE annee_code = ? AND statut_classe = 'actif') as total_classes,
                    (SELECT COUNT(*) FROM semestres WHERE annee_code = ? AND statut_semestre = 'actif') as total_semestres,
                    (SELECT COALESCE(SUM(montant_paiement), 0) FROM paiements WHERE annee_code = ? AND statut_paiement != 'annule') as total_recouvrement
            ");
            $stmtStats->execute([$anneeCode, $anneeCode, $anneeCode, $anneeCode]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: [
                'total_etudiants' => 0, 'total_classes' => 0, 'total_semestres' => 0, 'total_recouvrement' => 0
            ];

            // Liste des classes de cette année
            $stmtCls = $this->model->getCon()->prepare("
                SELECT cl.*, f.libelle_filiere, n.libelle_niveau,
                       (SELECT COUNT(*) FROM inscriptions ins WHERE ins.classe_code = cl.code_classe AND ins.statut_inscription = 'actif') as nb_eleves
                FROM classes cl
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                WHERE cl.annee_code = ?
                ORDER BY cl.libelle_classe ASC
            ");
            $stmtCls->execute([$anneeCode]);
            $classes = $stmtCls->fetchAll(PDO::FETCH_ASSOC);

            // Liste des semestres de cette année
            $stmtSem = $this->model->getCon()->prepare("
                SELECT * FROM semestres WHERE annee_code = ? ORDER BY id_semestre ASC
            ");
            $stmtSem->execute([$anneeCode]);
            $semestres = $stmtSem->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("AnneeController::details error: " . $e->getMessage());
            $this->renderNotFound("L'année académique demandée est introuvable.");
            return;
        }
        $this->loadView('../views/annees/details.php', [
            'item' => $item, 
            'stats' => $stats,
            'classes' => $classes,
            'semestres' => $semestres,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'annee/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'annee/list'); exit();
        }
        $this->loadView('../views/annees/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/annees/edit.php', ['item' => []]);
    }

    /**
     * Active une année académique pour la session PHP de l'utilisateur connecté
     */
    public function setSession()
    {
        $this->requireAuth();
        $code = trim($_POST['code_annee'] ?? ($_GET['code_annee'] ?? ''));

        if (empty($code)) {
            $this->json(['status' => 0, 'message' => 'Code année académique manquant']);
            return;
        }

        $db = $this->model->getCon();
        $stmt = $db->prepare("SELECT * FROM annees WHERE code_annee = ? OR id_annee = ? LIMIT 1");
        $stmt->execute([$code, is_numeric($code) ? (int)$code : 0]);
        $annee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($annee) {
            $_SESSION['annee_active_code'] = $annee['code_annee'];
            $_SESSION['annee_active_libelle'] = $annee['libelle_annee'];
            
            $this->json([
                'status' => 1,
                'message' => "Année académique {$annee['libelle_annee']} activée avec succès pour votre session.",
                'annee' => [
                    'code' => $annee['code_annee'],
                    'libelle' => $annee['libelle_annee']
                ]
            ]);
        } else {
            $this->json(['status' => 0, 'message' => 'Année académique sélectionnée introuvable']);
        }
    }
}