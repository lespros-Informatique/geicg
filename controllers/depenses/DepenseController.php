<?php

class DepenseController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelDepense();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_DEPENSES');
        $db = $this->model->getCon();

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }

        $activeYear = $this->getActiveAnneeCode();
        $annees = $this->getAccessibleAnnees();
        $stats = $this->model->getStats($activeYear);

        $typeDepenses = (new ModelTypeDepense())->getAll();

        $canValidate = $this->hasPermission('VALIDATE_DEPENSES');
        $canRecord = $this->hasPermission('RECORD_DEPENSES');

        $this->loadView('../views/depenses/list.php', [
            'annees' => $annees,
            'selectedAnneeCode' => $activeYear,
            'stats' => $stats,
            'typeDepenses' => $typeDepenses,
            'canValidate' => $canValidate,
            'canRecord' => $canRecord
        ]);
    }

    public function apiStats()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_DEPENSES');
        $anneeCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
        $typeCode = !empty($_GET['type_depense_code']) ? trim($_GET['type_depense_code']) : null;
        $dateDebut = !empty($_GET['date_debut']) ? trim($_GET['date_debut']) : null;
        $dateFin = !empty($_GET['date_fin']) ? trim($_GET['date_fin']) : null;

        $stats = $this->model->getStats($anneeCode, $typeCode, $dateDebut, $dateFin);
        $this->json(['status' => 1, 'stats' => $stats]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_DEPENSES');
        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $db = $this->model->getCon();
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }

        $anneeCode = $this->getActiveAnneeCode();
        $typeCode = !empty($_GET['type_depense_code']) ? trim($_GET['type_depense_code']) : null;
        $dateDebut = !empty($_GET['date_debut']) ? trim($_GET['date_debut']) : null;
        $dateFin = !empty($_GET['date_fin']) ? trim($_GET['date_fin']) : null;

        $items = $this->model->getAll($anneeCode, $typeCode, $dateDebut, $dateFin);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_depense'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function handleFileUpload(?array $fileInfo): ?string
    {
        if (empty($fileInfo) || empty($fileInfo['name']) || $fileInfo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $maxBytes = 3 * 1024 * 1024; // 3 Mo
        if ($fileInfo['size'] > $maxBytes) {
            $this->error('La pièce justificative dépasse la taille maximale autorisée de 3 Mo');
            exit;
        }

        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            $this->error('Format de fichier non autorisé. Formats acceptés : PDF, JPG, JPEG, PNG.');
            exit;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/depenses/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $filename = 'depense_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($fileInfo['tmp_name'], $targetPath)) {
            return 'uploads/depenses/' . $filename;
        }

        return null;
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('RECORD_DEPENSES');
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['description_depense']) && !empty($data['libelle_depense'])) {
            $data['description_depense'] = $data['libelle_depense'];
        }

        if (!empty($_FILES['piece_justificative'])) {
            $uploadedPath = $this->handleFileUpload($_FILES['piece_justificative']);
            if ($uploadedPath) {
                $data['piece_justificative'] = $uploadedPath;
            }
        }

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'type_depense_code' => $data['type_depense_code'] ?? ''
        ]);

        if (empty($data['code_depense'])) {
            $data['code_depense'] = $this->validator->generateCode('depenses', 'code_depense', 'DEP-', 8);
        }
        if (empty($data['periode_depense'])) {
            $data['periode_depense'] = date('Y-m-d H:i:s');
        }
        $data['statut_depense'] = 'en_attente';
        $data['created_at_depense'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE depenses")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Dépense enregistrée avec le statut "En attente" !');
        } else {
            $this->error($this->model->getLastError() ?: 'Erreur lors de la création de la dépense');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('RECORD_DEPENSES');
        $id = (int)$this->post('id_depense');
        if (!$id) { $this->error('Identifiant invalide'); return; }

        $item = $this->model->getById($id);
        if (!$item) { $this->error('Dépense introuvable'); return; }

        if (($item['statut_depense'] ?? '') !== 'en_attente') {
            $currentLabel = ($item['statut_depense'] === 'approuve') ? 'Approuvée' : 'Annulée';
            $this->error('Seule une dépense au statut "En attente" peut être modifiée. Cette dépense est actuellement : ' . $currentLabel);
            return;
        }

        $data = $_POST;
        unset($data['csrf_token'], $data['statut_depense']);
        $cols = $this->model->getCon()->query("DESCRIBE depenses")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if (!empty($_FILES['piece_justificative']) && $_FILES['piece_justificative']['error'] === UPLOAD_ERR_OK) {
            $uploadedPath = $this->handleFileUpload($_FILES['piece_justificative']);
            if ($uploadedPath) {
                $filteredData['piece_justificative'] = $uploadedPath;
            }
        }

        $filteredData['updated_at_depense'] = date('Y-m-d H:i:s');

        if ($this->model->update($filteredData, $id)) {
            $this->success('Dépense modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('VALIDATE_DEPENSES');
        $id = (int)$this->post('id');
        $statut = trim($this->post('statut') ?: $this->post('status') ?: '');

        $allowed = ['en_attente', 'approuve', 'annule'];
        if (!$id || !in_array($statut, $allowed, true)) {
            $this->error('Paramètres invalides pour le changement de statut');
            return;
        }

        $item = $this->model->getById($id);
        if (!$item) {
            $this->error('Dépense introuvable');
            return;
        }

        if (($item['statut_depense'] ?? '') === 'approuve') {
            $this->error('Une dépense approuvée le reste indéfiniment. Son statut est définitif et ne peut plus être modifié.');
            return;
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        if ($this->model->updateStatusWithConfirm($id, $statut, $userCode)) {
            $labels = [
                'en_attente' => 'remise en attente',
                'approuve' => 'approuvée',
                'annule' => 'annulée'
            ];
            $this->success('Dépense marquée comme ' . ($labels[$statut] ?? $statut) . ' avec succès!', ['reload' => true]);
        } else {
            $this->error('Erreur lors de la mise à jour du statut');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_DEPENSES');
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT d.*, 
                       td.libelle_type_depense, 
                       a.libelle_annee, 
                       CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) as auteur_nom_complet,
                       CONCAT(COALESCE(uc.nom_user, ''), ' ', COALESCE(uc.prenom_user, '')) as confirmateur_nom_complet
                FROM depenses d
                LEFT JOIN type_depenses td ON td.code_type_depense = d.type_depense_code
                LEFT JOIN annees a ON a.code_annee = d.annee_code
                LEFT JOIN users u ON u.code_user = d.user_code
                LEFT JOIN users uc ON uc.code_user = d.user_confirm
                WHERE d.id_depense = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'depense/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'depense/list'); exit();
        }
        $this->loadView('../views/depenses/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission('RECORD_DEPENSES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'depense/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'depense/list'); exit();
        }
        $this->loadView('../views/depenses/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission('RECORD_DEPENSES');
        $this->loadView('../views/depenses/edit.php', ['item' => []]);
    }
}
