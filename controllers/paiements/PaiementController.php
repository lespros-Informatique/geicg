<?php

class PaiementController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelPaiement();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/paiements/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $paiements = $this->model->getAll();
        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode !== null) {
            $commandeCodes = array_filter(array_map(function($c) { return $c['code_commande'] ?? null; }, (new ModelCommande())->getAll()));
            $commandeModel = new ModelCommande();
            $allowedCommandeCodes = [];
            foreach ($commandeModel->getAll() as $c) {
                if ($c['pressing_code'] === $pressingCode) {
                    $allowedCommandeCodes[] = $c['code_commande'];
                }
            }
            $paiements = array_filter($paiements, function($p) use ($allowedCommandeCodes) {
                return in_array($p['commande_code'], $allowedCommandeCodes, true);
            });
        }
        $data = [];

        foreach ($paiements as $p) {
            $idCrypte = $this->validator->crypter($p['id_paiement']);
            $data[] = [
                'code' => $p['code_paiement'],
                'commande_code' => $p['commande_code'],
                'montant' => $p['montant_paiement'],
                'mode' => $p['mode_paiement'],
                'statut' => $p['statut_paiement'],
                'created_at' => $p['created_at_paiement'] ?? '',
                'id' => $p['id_paiement'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields([
            'commande_code' => $_POST['commande_code'] ?? '',
            'montant_paiement' => $_POST['montant_paiement'] ?? ''
        ]);

        if ($notEmpty === true) {
            $code = $this->post('code') ?: $this->validator->generateCode(TABLES::PAIEMENTS, 'code_paiement', 'PAY-', 6);
            $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
            $mode = in_array($this->post('mode_paiement'), ['especes', 'orange_money', 'mtn_money', 'wave']) ? $this->post('mode_paiement') : 'especes';
            $datePaiement = $this->post('date_paiement');
            $createdAt = $datePaiement
                ? date('Y-m-d H:i:s', strtotime($datePaiement . ' ' . date('H:i:s')))
                : date('Y-m-d H:i:s');
            $data = [
                'code_paiement' => $code,
                'commande_code' => $this->post('commande_code'),
                'montant_paiement' => $this->post('montant_paiement'),
                'mode_paiement' => $mode,
                'user_code' => $userCode,
                'statut_paiement' => 'valide',
                'created_at_paiement' => $createdAt
            ];

            if ($this->model->create($data)) {
                $this->success('Paiement ajouté!');
            } else {
                $this->error('Erreur ajout');
            }
        } else {
            $this->error('Champs requis!');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields([
            'commande_code' => $_POST['commande_code'] ?? '',
            'montant_paiement' => $_POST['montant_paiement'] ?? '',
            'id_paiement' => $_POST['id_paiement'] ?? ''
        ]);

        if ($notEmpty === true) {
            $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
            $mode = in_array($this->post('mode_paiement'), ['especes', 'orange_money', 'mtn_money', 'wave']) ? $this->post('mode_paiement') : 'especes';
            $statut = in_array($this->post('statut_paiement'), ['valide', 'annule', 'en_attente']) ? $this->post('statut_paiement') : 'valide';
            $id = (int) $this->post('id_paiement');

            $data = [
                'commande_code' => $this->post('commande_code'),
                'montant_paiement' => $this->post('montant_paiement'),
                'mode_paiement' => $mode,
                'user_code' => $userCode,
                'statut_paiement' => $statut,
                'updated_at_paiement' => date('Y-m-d H:i:s')
            ];

            if ($this->model->update($data)) {
                $this->success('Paiement modifié!');
            } else {
                $this->error('Erreur modification');
            }
        } else {
            $this->error('Champs requis!');
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
            $this->error('Paiement introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $paiementId = $this->validator->decrypter($details);
            $paiementProfile = $this->model->getById($paiementId);
            if (!$paiementProfile) {
                header('Location: ' . RACINE . 'paiement/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($paiementId);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'paiement/list');
            exit();
        }

        $this->loadView('../views/paiements/details.php', ['paiement' => $paiementProfile, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $decryptedId = $this->validator->decrypter($details);
            $paiement = $this->model->getById($decryptedId);

            if (!$paiement) {
                header('Location: ' . RACINE . 'paiement/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'paiement/list');
            exit();
        }

        $this->loadView('../views/paiements/edit.php', ['paiement' => $paiement]);
    }
}
