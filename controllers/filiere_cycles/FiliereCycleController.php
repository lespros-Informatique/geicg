<?php

class FiliereCycleController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelFiliereCycle();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/filiere_cycles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_filiere_cycle'];
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
        $this->requirePermission(['MANAGE_FILIERES', 'MANAGE_CYCLES', 'MANAGE_CLASSES']);
        $data = $_POST;
        unset($data['csrf_token']);
        unset($data['id_filiere_cycle']);
        unset($data['id']);

        if (!empty($data['filiere_code']) && !empty($data['cycle_code'])) {
            $uniqueParams = [
                'filiere_code' => $data['filiere_code'],
                'cycle_code' => $data['cycle_code']
            ];
            if (!empty($data['niveau_code'])) {
                $uniqueParams['niveau_code'] = $data['niveau_code'];
            }
            if (!$this->checkUniquePair('filiere_cycles', $uniqueParams, 'Assignation Parcours (Cycle - Filière - Niveau)')) return;
        }

        $userCode = $this->getCurrentUserCode();
        $etabCode = $this->getActiveEtablissementCode();

        if (empty($data['code_filiere_cycle'])) {
            $data['code_filiere_cycle'] = $this->validator->generateCode('filiere_cycles', 'code_filiere_cycle', 'FCYC-', 8);
        }
        $data['statut_filiere_cycle'] = $data['statut_filiere_cycle'] ?? 'actif';
        $data['created_at_filiere_cycle'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE filiere_cycles")->fetchAll(PDO::FETCH_COLUMN);

        if (in_array('user_code', $cols) && !empty($userCode)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;

        $filteredData = array_intersect_key($data, array_flip($cols));
        unset($filteredData['id_filiere_cycle']);

        if ($this->model->create($filteredData)) {
            $this->success('Assignation Parcours Pivot créée avec succès!', RACINE . 'filiere_cycle/list');
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la création de l\'assignation';
            $this->error($msg);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['MANAGE_FILIERES', 'MANAGE_CYCLES', 'MANAGE_CLASSES']);
        $id = (int)$this->post('id_filiere_cycle');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['filiere_code']) && !empty($data['cycle_code'])) {
            $uniqueParams = [
                'filiere_code' => $data['filiere_code'],
                'cycle_code' => $data['cycle_code']
            ];
            if (!empty($data['niveau_code'])) {
                $uniqueParams['niveau_code'] = $data['niveau_code'];
            }
            if (!$this->checkUniquePair('filiere_cycles', $uniqueParams, 'Assignation Parcours (Cycle - Filière - Niveau)', 'id_filiere_cycle', $id)) return;
        }

        $userCode = $this->getCurrentUserCode();
        $cols = $this->model->getCon()->query("DESCRIBE filiere_cycles")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('etablissement_code', $cols) && empty($data['etablissement_code'])) {
            $existingItem = $this->model->getById($id);
            $data['etablissement_code'] = $existingItem['etablissement_code'] ?? $this->getActiveEtablissementCode();
        }
        if (in_array('user_code', $cols) && !empty($userCode)) $data['user_code'] = $userCode;
        $data['updated_at_filiere_cycle'] = date('Y-m-d H:i:s');

        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Assignation Parcours Pivot modifiée avec succès!', RACINE . 'filiere_cycle/list');
        } else {
            $this->error('Erreur lors de la modification');
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
            $stmt = $this->model->getCon()->prepare("
                SELECT fc.*, f.libelle_filiere, c.libelle_cycle
                FROM filiere_cycles fc
                LEFT JOIN filieres f ON f.code_filiere = fc.filiere_code
                LEFT JOIN cycles c ON c.code_cycle = fc.cycle_code
                WHERE fc.id_filiere_cycle = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { 
                $this->renderNotFound("L'assignation Filière - Cycle demandée est introuvable.");
                return;
            }

            // Classes liées à cette filière
            $stmtClasses = $this->model->getCon()->prepare("
                SELECT cl.*, n.libelle_niveau, COUNT(i.id_inscription) as nb_etudiants
                FROM classes cl
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN inscriptions i ON i.classe_code = cl.code_classe AND i.statut_inscription = 'actif'
                WHERE cl.filiere_code = ?
                GROUP BY cl.id_classe
                ORDER BY cl.libelle_classe ASC
            ");
            $stmtClasses->execute([$item['filiere_code']]);
            $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("FiliereCycleController::details error: " . $e->getMessage());
            $this->renderNotFound("L'assignation Filière - Cycle demandée est introuvable.");
            return;
        }
        $this->loadView('../views/filiere_cycles/details.php', [
            'item' => $item, 
            'classes' => $classes,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'filiere_cycle/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'filiere_cycle/list'); exit();
        }
        $this->loadView('../views/filiere_cycles/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/filiere_cycles/edit.php', ['item' => []]);
    }
}