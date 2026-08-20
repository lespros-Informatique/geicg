<?php
class ClientController extends BaseController
{
    use PressingAware;
    protected function resolveModel()
    {
        return new ModelClient();
    }

    public function list()
    {
        $this->requireAuth();
        $isSuperAdmin = $this->isSuperAdmin();
        $isPressing = $this->isPressing();
        $this->loadView('../views/clients/list.php', [
            'isSuperAdmin' => $isSuperAdmin,
            'isPressing' => $isPressing
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();

        if (!empty($pressingCode)) {
            $clients = $this->model->getByPressing($pressingCode);
        } else {
            $clients = $this->model->getAll();
        }

        $data = [];

        foreach ($clients as $c) {
            $idCrypte = $this->validator->crypter($c['id_client']);
            $data[] = [
                'code' => $c['code_client'],
                'nom' => $c['nom_client'],
                'telephone' => $c['telephone_client'],
                'quartier' => $c['quartier_client'] ?? '',
                'adresse' => $c['adresse_client'] ?? '',
                'statut' => $c['statut_client'],
                'id' => $c['id_client'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $notEmpty = Validator::validateRequiredFields(['nom' => $_POST['nom'] ?? '', 'telephone' => $_POST['telephone'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs obligatoires (Nom et Téléphone) !');
            return;
        }

        if (!Validator::validNumber($this->post('telephone'), 10)) {
            $this->error('Le numéro de téléphone doit contenir 10 chiffres !');
            return;
        }

        if (!$this->checkUnique(TABLES::CLIENTS, 'telephone_client', $this->post('telephone'), 'numéro de téléphone client')) return;

        if (!empty($this->post('email_client'))) {
            if (!$this->checkUnique(TABLES::CLIENTS, 'email_client', $this->post('email_client'), 'adresse email client')) return;
        }

        try {
            $code_client = $this->validator->generateCode(TABLES::CLIENTS, 'code_client', 'CLI-', 6);
            $pressingCode = $this->getCurrentPressingCode() ?: ($this->post('pressing_code') ?: 'PRS-001');
            $data = [
                'code_client' => $code_client,
                'pressing_code' => $pressingCode,
                'nom_client' => $this->post('nom'),
                'telephone_client' => $this->post('telephone'),
                'email_client' => $this->post('email_client') ?: null,
                'quartier_client' => $this->post('quartier_client') ?? '',
                'adresse_client' => $this->post('adresse_client') ?? '',
                'statut_client' => 'actif',
                'created_at_client' => date('Y-m-d H:i:s')
            ];

            if ($this->model->create($data)) {
                $this->success('Client ajouté avec succès !', ['client_code' => $code_client]);
            } else {
                $this->error('Erreur lors de l\'ajout du client');
            }
        } catch (Exception $e) {
            error_log('Client add error: ' . $e->getMessage());
            $this->error('Erreur serveur: ' . $e->getMessage(), 500);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $notEmpty = Validator::validateRequiredFields(['nom' => $_POST['nom'] ?? '', 'telephone' => $_POST['telephone'] ?? '', 'id_client' => $_POST['id_client'] ?? '']);

        if ($notEmpty === true) {
            if (!Validator::validNumber($this->post('telephone'), 10)) {
                $this->error('Le numéro de téléphone doit contenir 10 chiffres!');
            } elseif ($this->validator->_verif(TABLES::CLIENTS, 'telephone_client', $this->post('telephone'), 'id_client', $this->post('id_client'))) {
                $this->error('Ce numéro de téléphone est déjà utilisé par un autre client!');
            } else {
                $current = $this->model->getById((int)$this->post('id_client'));
                $statut = ($this->post('actif') == 1) ? 'actif' : ($current['statut_client'] ?? 'actif');
                $id = (int) $this->post('id_client');

                $data = [
                    'id_client' => $id,
                    'nom_client' => $this->post('nom'),
                    'telephone_client' => $this->post('telephone'),
                    'email_client' => $this->post('email_client') ?: null,
                    'quartier_client' => $this->post('quartier_client') ?? '',
                    'adresse_client' => $this->post('adresse_client') ?? '',
                    'statut_client' => $statut,
                    'updated_at_client' => date('Y-m-d H:i:s')
                ];

                if ($this->model->update($data, $id)) {
                    $this->success('Client modifié avec succès!');
                } else {
                    $this->error('Erreur lors de la modification');
                }
            }
        } else {
            $this->error('Veuillez renseigner tous les champs!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $clientId = $this->validator->decrypter($details);
            $clientProfile = $this->model->getById($clientId);
            if (!$clientProfile) {
                header('Location: ' . RACINE . 'client/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($clientId);

            error_log('[ClientController::details] client=' . $clientProfile['code_client'] . ' id=' . $clientId);

            $commandeModel = new ModelCommande();
            $commandes = $commandeModel->getByClient($clientProfile['code_client']);
            error_log('[ClientController::details] commandes=' . count($commandes));

            foreach ($commandes as $idx => $commande) {
                $commandes[$idx]['editId'] = $this->validator->crypter((int)($commande['id_commande'] ?? 0));
            }
        } catch (Exception $e) {
            error_log('[ClientController::details] erreur: ' . $e->getMessage() . ' trace: ' . $e->getTraceAsString());
            header('Location: ' . RACINE . 'client/list');
            exit();
        }

        $this->loadView('../views/clients/details.php', [
            'client' => $clientProfile,
            'encryptedId' => $encryptedId,
            'commandes' => $commandes,
            'isSuperAdmin' => $this->isSuperAdmin(),
            'isPressing' => $this->isPressing()
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        if ($this->isSuperAdmin()) {
            header('Location: ' . RACINE . 'client/list');
            exit();
        }

        try {
            $decryptedId = $this->validator->decrypter($details);
            $client = $this->model->getById($decryptedId);

            if (!$client) {
                header('Location: ' . RACINE . 'client/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'client/list');
            exit();
        }

        $this->loadView('../views/clients/edit.php', ['client' => $client]);
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if ($this->isSuperAdmin()) {
            $this->error("L'administrateur général n'a pas accès à la modification du statut client.");
            return;
        }

        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur lors de la modification');
            }
        } else {
            $this->error('Client introuvable!');
        }
    }
}