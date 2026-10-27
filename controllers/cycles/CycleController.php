<?php

class CycleController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelCycle();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/cycles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $status = $_GET['statut'] ?? ($_GET['status'] ?? null);
        if ($status !== null && $status !== '') {
            $items = $this->model->getByStatus($status);
        } else {
            $items = $this->model->getAll();
        }
        $etabCfg = $this->getEtablissementConfig();
        $useSlugCycle = !empty($etabCfg['use_slug_cycle']);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_cycle'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'use_slug_cycle' => $useSlugCycle
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_CYCLES');
        $data = $_POST;
        unset($data['csrf_token'], $data['id_cycle'], $data['id']);

        if (!empty($data['libelle_cycle'])) {
            if (!$this->checkUnique('cycles', 'libelle_cycle', $data['libelle_cycle'], 'Libellé du cycle')) return;
        }
        if (isset($data['slug_cycle'])) {
            $data['slug_cycle'] = trim($data['slug_cycle']) !== '' ? trim($data['slug_cycle']) : null;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        if (empty($data['code_cycle'])) {
            $data['code_cycle'] = $this->validator->generateCode('cycles', 'code_cycle', 'CYC-', 8);
        }
        $data['statut_cycle'] = $data['statut_cycle'] ?? 'actif';
        $data['created_at_cycle'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;

        $libelle = trim($data['libelle_cycle'] ?? '');
        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $msg = !empty($libelle) ? "Le cycle d'études « {$libelle} » a été créé avec succès !" : "Cycle d'études créé avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la création du cycle';
            $this->error($msg);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_CYCLES');
        $id = (int)$this->post('id_cycle');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['libelle_cycle'])) {
            if (!$this->checkUnique('cycles', 'libelle_cycle', $data['libelle_cycle'], 'Libellé du cycle', 'id_cycle', $id)) return;
        }
        if (isset($data['slug_cycle'])) {
            $data['slug_cycle'] = trim($data['slug_cycle']) !== '' ? trim($data['slug_cycle']) : null;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        if (empty($data['user_code'])) $data['user_code'] = $userCode;
        if (empty($data['etablissement_code'])) {
            $existingItem = $this->model->getById($id);
            $data['etablissement_code'] = $existingItem['etablissement_code'] ?? $etabCode;
        }
        if (empty($data['annee_code'])) $data['annee_code'] = $anneeCode;

        $libelle = trim($data['libelle_cycle'] ?? '');
        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $msg = !empty($libelle) ? "Le cycle d'études « {$libelle} » a été modifié avec succès !" : "Cycle d'études modifié avec succès !";
            $this->success($msg);
        } else {
            $this->error($this->model->getLastError() ?: 'Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
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
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'cycle/list'); exit(); }

            $cycleCode = $item['code_cycle'];
            $filieres = $this->model->getFilieresForCycle($cycleCode);
            $stats = $this->model->getStatsForCycle($cycleCode, count($filieres));

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'cycle/list'); exit();
        }
        $this->loadView('../views/cycles/details.php', [
            'item' => $item, 
            'stats' => $stats,
            'filieres' => $filieres,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'cycle/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'cycle/list'); exit();
        }
        $this->loadView('../views/cycles/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/cycles/edit.php', ['item' => []]);
    }
}