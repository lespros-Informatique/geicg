<?php

class NiveauController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelNiveau();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/niveaux/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $etabCfg = $this->getEtablissementConfig();
        $useSlugNiveau = !empty($etabCfg['use_slug_niveau']);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_niveau'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'use_slug_niveau' => $useSlugNiveau
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $data = $_POST;
        unset($data['csrf_token'], $data['id_niveau'], $data['id']);

        if (!empty($data['libelle_niveau'])) {
            if (!$this->checkUnique('niveaux', 'libelle_niveau', $data['libelle_niveau'], 'Libelle du niveau')) return;
        }
        if (isset($data['slug_niveau'])) {
            $data['slug_niveau'] = trim($data['slug_niveau']) !== '' ? trim($data['slug_niveau']) : null;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        if (empty($data['code_niveau'])) {
            $data['code_niveau'] = $this->validator->generateCode('niveaux', 'code_niveau', 'NIV-', 8);
        }
        $data['statut_niveau'] = $data['statut_niveau'] ?? 'actif';
        $data['created_at_niveau'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;

        $libelle = trim($data['libelle_niveau'] ?? '');
        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $msg = !empty($libelle) ? "Le niveau « {$libelle} » a été créé avec succès !" : "Niveau créé avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la création du niveau';
            $this->error($msg);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_niveau');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['libelle_niveau'])) {
            if (!$this->checkUnique('niveaux', 'libelle_niveau', $data['libelle_niveau'], 'Libelle du niveau', 'id_niveau', $id)) return;
        }
        if (isset($data['slug_niveau'])) {
            $data['slug_niveau'] = trim($data['slug_niveau']) !== '' ? trim($data['slug_niveau']) : null;
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

        $libelle = trim($data['libelle_niveau'] ?? '');
        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $msg = !empty($libelle) ? "Le niveau « {$libelle} » a été modifié avec succès !" : "Niveau modifié avec succès !";
            $this->success($msg);
        } else {
            $this->error($this->model->getLastError() ?: 'Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id') ?: $this->post('id_niveau');
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
            if (!$item) { header('Location: ' . RACINE . 'niveau/list'); exit(); }

            $niveauCode = $item['code_niveau'];
            $classes = $this->model->getClassesForNiveau($niveauCode);
            $stats = $this->model->getStatsForNiveau($niveauCode, count($classes));

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'niveau/list'); exit();
        }
        $this->loadView('../views/niveaux/details.php', [
            'item' => $item, 
            'classes' => $classes,
            'stats' => $stats,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'niveau/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'niveau/list'); exit();
        }
        $this->loadView('../views/niveaux/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/niveaux/edit.php', ['item' => []]);
    }
}