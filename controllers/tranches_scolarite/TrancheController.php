<?php

class TrancheController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelTranche();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_FRAIS_SCOLARITE);
        redirect('scolarite/list', ['tab' => 'tranches']);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_FRAIS_SCOLARITE);
        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $aRow = $this->model->getAnneeByCode($getAnnee);
            if ($aRow) {
                Session::set('annee_active_code', $aRow['code_annee']);
                Session::set('annee_active_libelle', $aRow['libelle_annee']);
            }
        }
        $anneeCode = $this->getActiveAnneeCode();
        $filiereCode = !empty($_GET['filiere_code']) ? trim($_GET['filiere_code']) : null;
        $niveauCode = !empty($_GET['niveau_code']) ? trim($_GET['niveau_code']) : null;
        $statut = !empty($_GET['statut_tranche']) ? trim($_GET['statut_tranche']) : (!empty($_GET['statut']) ? trim($_GET['statut']) : null);

        $items = $this->model->getAll($anneeCode, $filiereCode, $niveauCode, $statut);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_tranche'];
            $idCrypte = $this->validator->crypter($id);
            $scolCrypte = !empty($i['id_scolarite']) ? $this->validator->crypter($i['id_scolarite']) : null;
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'scolariteEditId' => $scolCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function validateCumulTranches($scolariteCode, $montantSaisie, $excludeId = null): ?string
    {
        return $this->model->validateCumul((string)$scolariteCode, (float)$montantSaisie, $excludeId ? (int)$excludeId : null);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_FRAIS_SCOLARITE);
        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        $scolariteCode = $data['scolarite_code'] ?? '';
        $montantTranche = $data['montant_tranche'] ?? 0;

        $errorMsg = $this->validateCumulTranches($scolariteCode, $montantTranche);
        if ($errorMsg !== null) {
            $this->error($errorMsg);
            return;
        }

        if (empty($data['code_tranche'])) {
            $data['code_tranche'] = $this->validator->generateCode(TABLES::TRANCHES_SCOLARITE, 'code_tranche', 'TRA-', 8);
        }
        $data['statut_tranche'] = $data['statut_tranche'] ?? 'actif';
        $data['created_at_tranche'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $this->success('Tranche créée avec succès!', url('scolarite/list', ['tab' => 'tranches']));
        } else {
            $this->error('Erreur lors de la création de la tranche');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_FRAIS_SCOLARITE);
        $id = (int)$this->post('id_tranche');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        $scolariteCode = $data['scolarite_code'] ?? '';
        $montantTranche = $data['montant_tranche'] ?? 0;

        $errorMsg = $this->validateCumulTranches($scolariteCode, $montantTranche, $id);
        if ($errorMsg !== null) {
            $this->error($errorMsg);
            return;
        }

        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $this->success('Tranche modifiée avec succès!', url('scolarite/list', ['tab' => 'tranches']));
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_FRAIS_SCOLARITE);
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
        $this->requirePermission(PERMISSIONS::VIEW_FRAIS_SCOLARITE);
        try {
            $id = is_numeric($details) ? (int)$details : $this->validator->decrypter($details);
            if (!$id && is_numeric($details)) {
                $id = (int)$details;
            }
            $item = $this->model->getById($id);
            if (!$item) {
                $this->renderNotFound("La tranche de scolarité demandée est introuvable.");
                return;
            }
            $encryptedId = $this->validator->crypter($id);
            $scolariteEncryptedId = !empty($item['id_scolarite']) ? $this->validator->crypter($item['id_scolarite']) : '';
        } catch (Exception $e) {
            error_log("TrancheController::details error: " . $e->getMessage());
            $this->renderNotFound("La tranche de scolarité demandée est introuvable.");
            return;
        }
        $this->loadView('../views/tranches_scolarite/details.php', [
            'item' => $item, 
            'encryptedId' => $encryptedId,
            'scolariteEncryptedId' => $scolariteEncryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_FRAIS_SCOLARITE);
        try {
            $id = is_numeric($details) ? (int)$details : $this->validator->decrypter($details);
            if (!$id && is_numeric($details)) {
                $id = (int)$details;
            }
            $item = $this->model->getById($id);
            if ($item && !empty($item['id_scolarite'])) {
                $scolEncId = $this->validator->crypter($item['id_scolarite']);
                redirect('scolarite/edition/' . $scolEncId);
                return;
            } elseif ($item && !empty($item['scolarite_code'])) {
                $scol = (new ModelScolarite())->getById($item['scolarite_code']);
                if ($scol && !empty($scol['id_scolarite'])) {
                    $scolEncId = $this->validator->crypter($scol['id_scolarite']);
                    redirect('scolarite/edition/' . $scolEncId);
                    return;
                }
            }
        } catch (Exception $e) {
            error_log("TrancheController::edition redirect error: " . $e->getMessage());
        }
        redirect('scolarite/list', ['tab' => 'tranches']);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_FRAIS_SCOLARITE);
        redirect('scolarite/formulaire');
    }
}
