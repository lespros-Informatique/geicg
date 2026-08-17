<?php

class MissionController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelMission();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/missions/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();

        if ($this->isLivreur()) {
            $livreurCode = $this->getCurrentLivreurCode();
            $missions = $livreurCode ? $this->model->getByLivreur($livreurCode) : [];
        } else {
            $missions = $this->model->getAll();
        }

        $data = [];

        foreach ($missions as $m) {
            $idCrypte = $this->validator->crypter($m['id_mission']);
            $data[] = [
                'code' => $m['code_mission'],
                'commande' => $m['commande_code'] ?? '',
                'livreur' => $m['livreur_code'] ?? '',
                'type' => $m['type_mission'] ?? '',
                'adresse' => $m['adresse_mission'] ?? '',
                'statut' => $m['statut_mission'],
                'id' => $m['id_mission'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['commande_code' => $_POST['commande_code'] ?? '', 'type_mission' => $_POST['type_mission'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $code = $this->post('code_mission') ?: $this->validator->generateCode(TABLES::MISSIONS, 'code_mission', 'MIS-', 6);
        if ($this->validator->getByElement(TABLES::MISSIONS, 'code_mission', $code)) {
            $this->error('Ce code mission existe déjà!');
            return;
        }

        $data = [
            'code_mission' => $code,
            'commande_code' => $this->post('commande_code'),
            'livreur_code' => $this->post('livreur_code') ?? '',
            'type_mission' => $this->post('type_mission'),
            'adresse_mission' => $this->post('adresse_mission') ?? '',
            'latitude_mission' => $this->post('latitude_mission') ?? null,
            'longitude_mission' => $this->post('longitude_mission') ?? null,
            'observation_mission' => $this->post('observation_mission') ?? '',
            'statut_mission' => 'en_attente',
            'created_at_mission' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Mission ajoutée avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['commande_code' => $_POST['commande_code'] ?? '', 'type_mission' => $_POST['type_mission'] ?? '', 'id_mission' => $_POST['id_mission'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $id = (int) $this->post('id_mission');
        $item = $this->model->getById($id);
        if (!$item) {
            $this->error('Mission introuvable!');
            return;
        }

        if ($this->isLivreur()) {
            $livreurCode = $this->getCurrentLivreurCode();
            if ($livreurCode === null || ($item['livreur_code'] ?? '') !== $livreurCode) {
                $this->error('Accès refusé : vous n\'êtes pas assigné à cette mission', 403);
                return;
            }
        }

        $statut = in_array($this->post('statut_mission'), ['en_attente', 'en_cours', 'terminee', 'annulee']) ? $this->post('statut_mission') : 'en_attente';

        $data = [
            'commande_code' => $this->post('commande_code'),
            'livreur_code' => $this->post('livreur_code') ?? '',
            'type_mission' => $this->post('type_mission'),
            'adresse_mission' => $this->post('adresse_mission') ?? '',
            'latitude_mission' => $this->post('latitude_mission') ?? null,
            'longitude_mission' => $this->post('longitude_mission') ?? null,
            'observation_mission' => $this->post('observation_mission') ?? '',
            'statut_mission' => $statut,
            'updated_at_mission' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Mission modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->isLivreur()) {
                $item = $this->model->getById($id);
                $livreurCode = $this->getCurrentLivreurCode();
                if ($livreurCode === null || ($item['livreur_code'] ?? '') !== $livreurCode) {
                    $this->json(['status' => 0, 'message' => 'Accès refusé : vous n\'êtes pas assigné à cette mission'], 403);
                    return;
                }
            }

            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Mission introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'mission/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'mission/list');
            exit();
        }

        $this->loadView('../views/missions/details.php', [
            'mission' => $item,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'mission/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'mission/list');
            exit();
        }

        $this->loadView('../views/missions/edit.php', ['mission' => $item]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner une mission';
        foreach ($items as $i) {
            $options[$i['code_mission']] = $i['code_mission'];
        }
        $this->json(['options' => $options]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/missions/edit.php', ['mission' => []]);
    }
}
