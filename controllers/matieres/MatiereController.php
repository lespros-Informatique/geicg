<?php

class MatiereController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelMatiere();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_MATIERES);
        $this->loadView('../views/matieres/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_MATIERES);
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_matiere'];
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
        $this->requirePermission(PERMISSIONS::MANAGE_MATIERES);
        $data = $_POST;
        unset($data['csrf_token']);
        unset($data['id_matiere']);
        unset($data['id']);
        if (!empty($data['libelle_matiere'])) {
            if (!$this->checkUnique('matieres', 'libelle_matiere', $data['libelle_matiere'], 'Nom de la matiere')) return;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        if (empty($data['code_matiere'])) {
            $data['code_matiere'] = $this->validator->generateCode(TABLES::MATIERES, 'code_matiere', 'MAT-', 8);
        }
        $data['statut_matiere'] = $data['statut_matiere'] ?? 'actif';
        $data['created_at_matiere'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        $libelle = trim($data['libelle_matiere'] ?? '');
        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $this->logAction(
                AuditLogger::ACTION_CREATE,
                'matieres',
                $data['code_matiere'],
                "Création de la matière '{$libelle}' ({$data['code_matiere']})",
                $filteredData,
                $etabCode
            );
            $msg = !empty($libelle) ? "La matière « {$libelle} » a été créée avec succès !" : "Matière créée avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la création de la matière';
            $this->error($msg);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_MATIERES);
        $id = (int)$this->post('id_matiere');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_matiere'])) {
            if (!$this->checkUnique('matieres', 'libelle_matiere', $data['libelle_matiere'], 'Nom de la matiere', 'id_matiere', $id)) return;
        }

        $libelle = trim($data['libelle_matiere'] ?? '');
        $data['updated_at_matiere'] = date('Y-m-d H:i:s');
        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $this->logAction(
                AuditLogger::ACTION_UPDATE,
                'matieres',
                (string)$id,
                "Modification de la matière '{$libelle}'",
                $filteredData
            );
            $msg = !empty($libelle) ? "La matière « {$libelle} » a été modifiée avec succès !" : "Matière modifiée avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la modification de la matière';
            $this->error($msg);
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_MATIERES);
        $id = $this->post('id') ?: $this->post('id_matiere');
        if ($id && ($mat = $this->model->getById($id))) {
            if ($this->model->toggleStatus($id)) {
                $this->logAction(
                    AuditLogger::ACTION_UPDATE,
                    'matieres',
                    $mat['code_matiere'] ?? (string)$id,
                    "Basculement de statut de la matière '" . ($mat['libelle_matiere'] ?? $id) . "'",
                    ['id_matiere' => $id, 'code_matiere' => $mat['code_matiere'] ?? null]
                );
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
        $this->requirePermission(PERMISSIONS::VIEW_MATIERES);
        try {
            $item = null;
            if (is_numeric($details)) {
                $item = $this->model->getById((int)$details);
            }
            if (!$item) {
                $decrypted = $this->validator->decrypter($details);
                if (is_numeric($decrypted) && (int)$decrypted > 0) {
                    $item = $this->model->getById((int)$decrypted);
                } elseif (is_string($decrypted) && $decrypted !== '') {
                    $item = $this->model->getByCode($decrypted, 'code_matiere');
                }
            }
            if (!$item && is_string($details) && $details !== '') {
                $item = $this->model->getByCode($details, 'code_matiere');
            }
            if (!$item) {
                redirect('matiere/list');
                return;
            }

            $matiereCode = (string)$item['code_matiere'];
            $anneeCode = $this->getActiveAnneeCode();
            $anneeLibelle = $this->getActiveAnneeLibelle();

            $affectations = $this->model->getAffectationsForMatiere($matiereCode, $anneeCode);
            foreach ($affectations as &$aff) {
                $aff['encrypted_classe_id'] = !empty($aff['id_classe'])
                    ? $this->validator->crypter((int)$aff['id_classe'])
                    : '';
                $aff['encrypted_enseignant_id'] = !empty($aff['id_enseignant'])
                    ? $this->validator->crypter((int)$aff['id_enseignant'])
                    : '';
            }
            unset($aff);

            $maquettes = $this->model->getMaquettesForMatiere($matiereCode, $anneeCode);
            $stats = $this->model->getStatsForMatiere($matiereCode, count($affectations), $anneeCode);
            $encryptedId = $this->validator->crypter((int)$item['id_matiere']);
        } catch (Exception $e) {
            redirect('matiere/list');
            return;
        }
        $this->loadView('../views/matieres/details.php', [
            'item'         => $item,
            'affectations' => $affectations,
            'maquettes'    => $maquettes,
            'stats'        => $stats,
            'anneeCode'    => $anneeCode,
            'anneeLibelle' => $anneeLibelle,
            'encryptedId'  => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_MATIERES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { redirect('matiere/list'); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            redirect('matiere/list');
        }
        $this->loadView('../views/matieres/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_MATIERES);
        $this->loadView('../views/matieres/edit.php', ['item' => []]);
    }
}
