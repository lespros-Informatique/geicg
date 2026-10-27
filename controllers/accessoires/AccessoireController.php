<?php

class AccessoireController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelAccessoire();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_ACCESSOIRES, PERMISSIONS::VIEW_ACCESSOIRES, PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::MANAGE_INSCRIPTIONS]);
        $annees = $this->getAccessibleAnnees();
        
        if (isset($_GET['annee_code']) && !empty($_GET['annee_code'])) {
            $selectedAnneeCode = trim($_GET['annee_code']);
            foreach ($annees as $a) {
                if ($a['code_annee'] === $selectedAnneeCode) {
                    Session::set('annee_active_code', $a['code_annee']);
                    Session::set('annee_active_libelle', $a['libelle_annee']);
                    break;
                }
            }
        } else {
            $selectedAnneeCode = Session::get('annee_active_code');
        }

        $stats = $this->model->getStats($selectedAnneeCode);
        $niveaux = (new ModelNiveau())->getActifs();

        $this->loadView('../views/accessoires/list.php', [
            'stats' => $stats,
            'annees' => $annees,
            'niveaux' => $niveaux,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function registre()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_REMISE_KITS, PERMISSIONS::VIEW_REMISE_KITS]);

        $annees = $this->getAccessibleAnnees();
        
        if (isset($_GET['annee_code']) && !empty($_GET['annee_code'])) {
            $selectedAnneeCode = trim($_GET['annee_code']);
            foreach ($annees as $a) {
                if ($a['code_annee'] === $selectedAnneeCode) {
                    Session::set('annee_active_code', $a['code_annee']);
                    Session::set('annee_active_libelle', $a['libelle_annee']);
                    break;
                }
            }
        } else {
            $selectedAnneeCode = Session::get('annee_active_code');
        }

        $classeModel = new ModelClasse();
        $classes = $classeModel->getAll();

        $stats = $this->model->getStats($selectedAnneeCode);

        $this->loadView('../views/accessoires/registre.php', [
            'stats' => $stats,
            'annees' => $annees,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_ACCESSOIRES, PERMISSIONS::VIEW_ACCESSOIRES]);
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $items = $this->model->getAll($anneeCode);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_accessoire'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ACCESSOIRES);
        $data = $_POST;
        unset($data['csrf_token']);
        unset($data['id_accessoire']);
        unset($data['id']);
        if (!empty($data['libelle_accessoire'])) {
            if (!$this->checkUnique(TABLES::ACCESSOIRES, 'libelle_accessoire', $data['libelle_accessoire'], 'Libelle de l accessoire')) return;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        if (empty($data['code_accessoire'])) {
            $data['code_accessoire'] = $this->validator->generateCode(TABLES::ACCESSOIRES, 'code_accessoire', 'ACC-', 8);
        }
        $data['statut_accessoire'] = $data['statut_accessoire'] ?? 'actif';
        $data['created_at_accessoire'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;

        $libelle = trim($data['libelle_accessoire'] ?? '');
        if (isset($data['niveau_code']) && empty(trim($data['niveau_code']))) {
            $data['niveau_code'] = null;
        }

        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $msg = !empty($libelle) ? "L'accessoire « {$libelle} » a été créé avec succès !" : "Accessoire créé avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la création de l\'accessoire';
            $this->error($msg);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ACCESSOIRES);
        $id = (int)$this->post('id_accessoire');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_accessoire'])) {
            if (!$this->checkUnique(TABLES::ACCESSOIRES, 'libelle_accessoire', $data['libelle_accessoire'], 'Libelle de l accessoire', 'id_accessoire', $id)) return;
        }

        $libelle = trim($data['libelle_accessoire'] ?? '');
        if (isset($data['niveau_code']) && empty(trim($data['niveau_code']))) {
            $data['niveau_code'] = null;
        }

        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $msg = !empty($libelle) ? "L'accessoire « {$libelle} » a été modifié avec succès !" : "Accessoire modifié avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la modification de l\'accessoire';
            $this->error($msg);
        }
    }

    public function apiDistributions()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_REMISE_KITS, PERMISSIONS::VIEW_REMISE_KITS]);

        $filter = trim($_GET['filter'] ?? 'all');
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $classeCode = $_GET['classe_code'] ?? null;

        try {
            $items = $this->model->getDistributions($filter, $anneeCode, $classeCode);
            $this->json(['data' => $items]);
        } catch (Exception $e) {
            error_log("AccessoireController::apiDistributions error: " . $e->getMessage());
            $this->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    public function getBonRemiseData()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_REMISE_KITS, PERMISSIONS::VIEW_REMISE_KITS]);

        $inscriptionCode = trim($_GET['inscription_code'] ?? ($_POST['inscription_code'] ?? ''));
        if (empty($inscriptionCode)) {
            $this->error('Inscription introuvable.');
            return;
        }

        try {
            $data = $this->model->getBonRemiseData($inscriptionCode);
            if (empty($data)) {
                $this->error('Données de remise introuvables.');
                return;
            }

            $user = Auth::user();
            $agentName = ($user['prenom_user'] ?? '') . ' ' . ($user['nom_user'] ?? '');
            $data['status'] = 1;
            $data['date_emission'] = date('d/m/Y H:i');
            $data['agent_nom'] = trim($agentName) ?: 'Administration';

            $this->json($data);
        } catch (Exception $e) {
            error_log("AccessoireController::getBonRemiseData error: " . $e->getMessage());
            $this->error($e->getMessage());
        }
    }

    public function apiStats()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_ACCESSOIRES, PERMISSIONS::VIEW_ACCESSOIRES, PERMISSIONS::MANAGE_REMISE_KITS, PERMISSIONS::VIEW_REMISE_KITS]);
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $stats = $this->model->getStats($anneeCode);
        $this->json(['status' => 1, 'stats' => $stats]);
    }

    public function toggleRetrait()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_REMISE_KITS);

        $id = (int)$this->post('id');
        $code = trim($this->post('code_accessoire_inscription') ?? '');

        $row = $this->model->getAccessoireInscription($id, $code);
        if (!$row) {
            $this->error('Enregistrement de kit introuvable');
            return;
        }

        $newEtat = ($row['etat_retrait'] === 'retire') ? 'en_attente' : 'retire';
        $dateRetrait = ($newEtat === 'retire') ? date('Y-m-d H:i:s') : null;

        $success = $this->model->updateEtatRetrait($row['id_accessoire_inscription'], $newEtat, $dateRetrait);

        if ($success) {
            $msg = ($newEtat === 'retire') 
                ? 'Kit marqué comme RETIRÉ avec succès !' 
                : 'Kit remis EN ATTENTE de retrait.';
            $this->success($msg, [
                'new_state' => $newEtat,
                'date_retrait' => $dateRetrait ? date('d/m/Y H:i', strtotime($dateRetrait)) : '-',
                'reload' => false
            ]);
        } else {
            $this->error('Erreur lors de la mise à jour du statut de distribution.');
        }
    }

    public function toggleStudentAllKits()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_REMISE_KITS);

        $inscriptionCode = trim($this->post('inscription_code') ?? '');
        $targetEtat = trim($this->post('target_etat') ?? 'retire'); // 'retire' or 'en_attente'

        if (empty($inscriptionCode)) {
            $this->error('Inscription introuvable.');
            return;
        }

        $dateRetrait = ($targetEtat === 'retire') ? date('Y-m-d H:i:s') : null;
        $success = $this->model->updateAllKitsForInscription($inscriptionCode, $targetEtat, $dateRetrait);

        if ($success) {
            $msg = ($targetEtat === 'retire') 
                ? 'Tous les kits de cet étudiant ont été marqués comme RETIRÉS !' 
                : 'Tous les kits de cet étudiant ont été remis EN ATTENTE.';
            $this->success($msg);
        } else {
            $this->error('Erreur lors de la mise à jour des kits de l\'étudiant.');
        }
    }

    public function getStudentKits()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_REMISE_KITS, PERMISSIONS::VIEW_REMISE_KITS]);
        $etudiantCode = trim($_GET['etudiant_code'] ?? $_POST['etudiant_code'] ?? '');
        if (empty($etudiantCode)) {
            $this->json(['status' => 0, 'message' => 'Étudiant non spécifié', 'existing_kits' => [], 'existing_codes' => []]);
            return;
        }

        $anneeCode = $this->getActiveAnneeCode();
        $existingKits = $this->model->getKitsByEtudiant($etudiantCode, $anneeCode);
        $existingCodes = array_values(array_unique(array_column($existingKits, 'accessoire_code')));
        $allKits = $this->model->getAllActiveKits();

        $this->json([
            'status' => 1,
            'existing_kits' => $existingKits,
            'existing_codes' => $existingCodes,
            'all_kits' => $allKits,
            'has_all' => (count($existingCodes) >= count($allKits) && count($allKits) > 0)
        ]);
    }

    public function attribuerKit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_REMISE_KITS);

        $etudiantCode = trim($_POST['etudiant_code'] ?? '');
        $etatRetrait = trim($_POST['etat_retrait'] ?? 'en_attente');

        // Support multiple accessoires
        $accessoireCodes = [];
        if (!empty($_POST['accessoires']) && is_array($_POST['accessoires'])) {
            $accessoireCodes = array_filter($_POST['accessoires']);
        } elseif (!empty($_POST['accessoire_code'])) {
            $accessoireCodes = [trim($_POST['accessoire_code'])];
        }

        if (empty($etudiantCode) || empty($accessoireCodes)) {
            $this->error('Veuillez sélectionner un étudiant et au moins un kit / accessoire.');
            return;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        // Trouver la dernière inscription de l'étudiant via modèle
        $ins = $this->model->getLatestInscriptionForStudent($etudiantCode);
        $insCode = $ins['code_inscription'] ?? '';

        if (empty($insCode)) {
            $this->error('Aucune inscription active trouvée pour cet étudiant. Veuillez d\'abord procéder à son inscription.');
            return;
        }

        // VÉRIFICATION D'UNICITÉ : Ne pas attribuer un kit déjà existant pour cet étudiant cette année
        $toInsert = [];
        $duplicates = [];

        foreach ($accessoireCodes as $accCode) {
            $existing = $this->model->checkKitAlreadyAttributed($etudiantCode, $insCode, $accCode, $anneeCode);
            if ($existing) {
                $duplicates[] = $existing['libelle_accessoire'] ?? $accCode;
            } else {
                $toInsert[] = $accCode;
            }
        }

        if (empty($toInsert)) {
            $nomsDoublons = implode(', ', $duplicates);
            $this->error("Attribution impossible : Cet étudiant possède déjà le(s) kit(s) [{$nomsDoublons}] pour cette année scolaire.");
            return;
        }

        $dateRetrait = ($etatRetrait === 'retire') ? date('Y-m-d H:i:s') : null;

        $count = 0;
        foreach ($toInsert as $accCode) {
            $this->validateForeignKeys([
                'inscription_code' => $insCode,
                'accessoire_code' => $accCode,
                'annee_code' => $anneeCode,
                'etablissement_code' => $etabCode
            ]);

            $codeAccIns = $this->validator->generateCode(TABLES::ACCESSOIRE_INSCRIPTION, 'code_accessoire_inscription', 'ACI-', 8);
            $ok = $this->model->insertAccessoireInscription([
                'code_accessoire_inscription' => $codeAccIns,
                'inscription_code' => $insCode,
                'accessoire_code' => $accCode,
                'annee_code' => $anneeCode,
                'etat_retrait' => $etatRetrait,
                'date_retrait' => $dateRetrait,
                'user_code' => $userCode,
                'etablissement_code' => $etabCode
            ]);
            if ($ok) $count++;
        }

        if ($count > 0) {
            $msg = "{$count} nouveau(x) kit(s) attribué(s) avec succès !";
            if (!empty($duplicates)) {
                $msg .= " (Note : " . implode(', ', $duplicates) . " déjà possédé(s) et ignoré(s)).";
            }
            $this->success($msg, ['reload' => true]);
        } else {
            $this->error('Erreur lors de l\'attribution des kits.');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ACCESSOIRES);
        $id = $this->post('id') ?: $this->post('id_accessoire');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Item introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ACCESSOIRES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'accessoire/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'accessoire/list'); exit();
        }
        header('Location: ' . RACINE . 'accessoire/list');
        exit();
    }

    public function edition($details)
    {
        header('Location: ' . RACINE . 'accessoire/list');
        exit();
    }

    public function formulaire()
    {
        header('Location: ' . RACINE . 'accessoire/list');
        exit();
    }
}