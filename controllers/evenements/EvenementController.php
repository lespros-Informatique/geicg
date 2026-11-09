<?php

class EvenementController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEvenement();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_EVENEMENTS, PERMISSIONS::MANAGE_EVENEMENTS]);
        $this->loadView('../views/evenements/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_EVENEMENTS, PERMISSIONS::MANAGE_EVENEMENTS]);
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_evenement'];
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
        $this->requirePermission(PERMISSIONS::MANAGE_EVENEMENTS);
        $userCode = Auth::code() ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);
        $data['statut_evenement'] = $data['statut_evenement'] ?? 'actif';
        $data['date_creation_evenement'] = date('Y-m-d H:i:s');
        $cols = $this->model->getTableColumns();
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = $this->model->filterData($data);
        $newId = $this->model->create($filteredData);
        if ($newId) {
            $this->logAction(
                AuditLogger::ACTION_CREATE,
                'evenements',
                $newId,
                "Création de l'événement: " . ($filteredData['titre_evenement'] ?? ''),
                ['id_evenement' => $newId, 'titre' => $filteredData['titre_evenement'] ?? '', 'type' => $filteredData['type_evenement'] ?? '']
            );
            $this->success('Item créé avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EVENEMENTS);
        $id = (int)$this->post('id_evenement');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $this->logAction(
                AuditLogger::ACTION_UPDATE,
                'evenements',
                $id,
                "Modification de l'événement: " . ($filteredData['titre_evenement'] ?? "#{$id}"),
                ['id_evenement' => $id, 'titre' => $filteredData['titre_evenement'] ?? '']
            );
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EVENEMENTS);
        $id = $this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->logAction(
                    AuditLogger::ACTION_UPDATE,
                    'evenements',
                    $id,
                    "Changement du statut de l'événement ID #{$id}",
                    ['id_evenement' => $id]
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
        $this->requirePermission([PERMISSIONS::VIEW_EVENEMENTS, PERMISSIONS::MANAGE_EVENEMENTS]);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { redirect('evenement/list'); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            redirect('evenement/list');
        }
        $this->loadView('../views/evenements/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EVENEMENTS);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { redirect('evenement/list'); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            redirect('evenement/list');
        }
        $this->loadView('../views/evenements/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EVENEMENTS);
        $this->loadView('../views/evenements/edit.php', ['item' => []]);
    }
}
