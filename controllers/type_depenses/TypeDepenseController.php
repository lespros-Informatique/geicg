<?php

class TypeDepenseController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelTypeDepense();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/type_depenses/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_type_depense'];
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
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_type_depense'])) {
            if (!$this->checkUnique('type_depenses', 'libelle_type_depense', $data['libelle_type_depense'], 'Type de depense')) return;
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        if (empty($data['code_type_depense'])) {
            $data['code_type_depense'] = $this->validator->generateCode('type_depenses', 'code_type_depense', 'TYP-', 8);
        }
        $data['statut_type_depense'] = $data['statut_type_depense'] ?? 'actif';
        $data['created_at_type_depense'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE type_depenses")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $libelle = trim($data['libelle_type_depense'] ?? '');
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $msg = !empty($libelle) ? "Le type de dépense « {$libelle} » a été créé avec succès !" : "Type de dépense créé avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la création du type de dépense';
            $this->error($msg);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_type_depense');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_type_depense'])) {
            if (!$this->checkUnique('type_depenses', 'libelle_type_depense', $data['libelle_type_depense'], 'Type de depense', 'id_type_depense', $id)) return;
        }

        $libelle = trim($data['libelle_type_depense'] ?? '');
        $cols = $this->model->getCon()->query("DESCRIBE type_depenses")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $msg = !empty($libelle) ? "Le type de dépense « {$libelle} » a été modifié avec succès !" : "Type de dépense modifié avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la modification du type de dépense';
            $this->error($msg);
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
            if (!$item) { header('Location: ' . RACINE . 'type_depense/list'); exit(); }

            // Dépenses de cette catégorie
            $stmtDep = $this->model->getCon()->prepare("
                SELECT * FROM depenses 
                WHERE type_depense_code = ? 
                ORDER BY id_depense DESC
            ");
            $stmtDep->execute([$item['code_type_depense']]);
            $depenses = $stmtDep->fetchAll(PDO::FETCH_ASSOC);

            $totalDepenses = 0;
            foreach ($depenses as $d) {
                if (($d['statut_depense'] ?? '') !== 'annule') {
                    $totalDepenses += (float)($d['montant_depense'] ?? 0);
                }
            }

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("TypeDepenseController::details error: " . $e->getMessage());
            $this->renderNotFound("Le type de dépense demandé est introuvable.");
        }
        $this->loadView('../views/type_depenses/details.php', [
            'item' => $item, 
            'depenses' => $depenses,
            'totalDepenses' => $totalDepenses,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        header('Location: ' . RACINE . 'type_depense/list');
        exit();
    }

    public function formulaire()
    {
        header('Location: ' . RACINE . 'type_depense/list');
        exit();
    }
}