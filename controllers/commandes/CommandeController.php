<?php

class CommandeController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelCommande();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/commandes/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $commandes = $this->model->getAll();
        $data = [];

        foreach ($commandes as $c) {
            $idCrypte = $this->validator->crypter($c['id_commande']);
            $data[] = [
                'code' => $c['code_commande'],
                'client' => $c['client_code'] ?? 'N/A',
                'user' => $c['user_code'] ?? 'N/A',
                'date' => $c['date_livraison_commande'] ?? '',
                'statut' => $c['statut_commande'],
                'id' => $c['id_commande'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['client_code' => $_POST['client_code'] ?? '']);

        if ($notEmpty === true) {
            if ($this->validator->getByElement(TABLES::COMMANDES, 'code_commande', $this->post('code'))) {
                $this->error('Ce code commande existe déjà!');
            } else {
                $code = $this->post('code') ?: $this->validator->generateCode(TABLES::COMMANDES, 'code_commande', 'CMD-', 6);
                $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';

                $data = [
                    'code_commande' => $code,
                    'pressing_code' => $this->post('pressing_code') ?: 'PRS-001',
                    'client_code' => $this->post('client_code'),
                    'user_code' => $userCode,
                    'remise_commande' => $this->post('remise_commande') ?: 0,
                    'frais_collecte_commande' => $this->post('frais_collecte_commande') ?: 0,
                    'frais_livraison_commande' => $this->post('frais_livraison_commande') ?: 0,
                    'montant_total_commande' => $this->post('montant_total_commande') ?: 0,
                    'observation_commande' => $this->post('observation_commande') ?? '',
                    'date_livraison_commande' => $this->post('date_livraison_commande') ?: null,
                    'statut_commande' => 'actif',
                    'statut_suivi_commande' => 'creee',
                    'created_at_commande' => date('Y-m-d H:i:s')
                ];

                if ($this->model->create($data)) {
                    $this->success('Commande ajoutée avec succès!', ['commande_code' => $code]);
                } else {
                    $this->error('Erreur lors de l\'ajout');
                }
            }
        } else {
            $this->error('Veuillez renseigner tous les champs!');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['client_code' => $_POST['client_code'] ?? '', 'id_commande' => $_POST['id_commande'] ?? '']);

        if ($notEmpty === true) {
            $statut = in_array($this->post('statut_commande'), ['actif', 'inactif']) ? $this->post('statut_commande') : 'actif';
            $id = (int) $this->post('id_commande');

            $data = [
                'client_code' => $this->post('client_code'),
                'user_code' => $_SESSION[USERS_AUTH]['code_user'] ?? '',
                'remise_commande' => $this->post('remise_commande') ?: 0,
                'frais_collecte_commande' => $this->post('frais_collecte_commande') ?: 0,
                'frais_livraison_commande' => $this->post('frais_livraison_commande') ?: 0,
                'montant_total_commande' => $this->post('montant_total_commande') ?: 0,
                'observation_commande' => $this->post('observation_commande') ?? '',
                'date_livraison_commande' => $this->post('date_livraison_commande') ?: null,
                'statut_commande' => $statut,
                'updated_at_commande' => date('Y-m-d H:i:s')
            ];

            if ($this->model->update($data)) {
                $this->success('Commande modifiée avec succès!');
            } else {
                $this->error('Erreur lors de la modification');
            }
        } else {
            $this->error('Veuillez renseigner tous les champs!');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Commande introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getWithDetails($id);
            if (!$item) {
                header('Location: ' . RACINE . 'commande/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'commande/list');
            exit();
        }

        $ligneModel = new ModelCommandeDetail();
        $lignes = $ligneModel->getByCommande($item['code_commande']);

        $this->loadView('../views/commandes/details.php', [
            'order' => $item,
            'encryptedId' => $encryptedId,
            'lignes' => $lignes
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'commande/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'commande/list');
            exit();
        }

        $this->loadView('../views/commandes/edit.php', ['commande' => $item]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner une commande';
        foreach ($items as $i) {
            $options[$i['code_commande']] = $i['code_commande'];
        }
        $this->json(['options' => $options]);
    }

    public function listByOrder()
    {
        $this->requireAuth();
        $code = $this->post('code');
        if (!$code) {
            $this->error('Code commande requis');
            return;
        }

        $ligneModel = new ModelCommandeDetail();
        $lignes = $ligneModel->getByCommande($code);
        $this->json(['data' => $lignes]);
    }
}