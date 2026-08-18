<?php

class ServiceController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelService();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/services/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $services = $this->model->getAll();
        $data = [];

        foreach ($services as $s) {
            $idCrypte = $this->validator->crypter($s['id_service']);
            $data[] = [
                'code' => $s['code_service'],
                'libelle' => $s['libelle_service'],
                'description' => $s['description_service'] ?? '',
                'statut' => $s['statut_service'],
                'id' => $s['id_service'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requireActiveAbonnement(null, 'créer des services');
        $notEmpty = Validator::validateRequiredFields(['libelle_service' => $_POST['libelle_service'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $code = $this->post('code_service') ?: $this->validator->generateCode(TABLES::SERVICES, 'code_service', 'SERV-', 6);
        if ($this->validator->getByElement(TABLES::SERVICES, 'code_service', $code)) {
            $this->error('Ce code service existe déjà!');
            return;
        }

        $data = [
            'code_service' => $code,
            'libelle_service' => $this->post('libelle_service'),
            'description_service' => $this->post('description_service') ?? '',
            'statut_service' => 'actif',
            'created_at_service' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Service ajouté avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requireActiveAbonnement(null, 'modifier des services');
        $notEmpty = Validator::validateRequiredFields(['libelle_service' => $_POST['libelle_service'] ?? '', 'id_service' => $_POST['id_service'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $statut = ($this->post('actif') == 1) ? 'actif' : 'inactif';
        $id = (int) $this->post('id_service');

        $data = [
            'id_service' => $id,
            'libelle_service' => $this->post('libelle_service'),
            'description_service' => $this->post('description_service') ?? '',
            'statut_service' => $statut,
            'updated_at_service' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Service modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requireActiveAbonnement(null, 'activer ou désactiver des services');
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Service introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'service/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'service/list');
            exit();
        }

        $this->loadView('../views/services/details.php', [
            'service' => $item,
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
                header('Location: ' . RACINE . 'service/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'service/list');
            exit();
        }

        $this->loadView('../views/services/edit.php', ['service' => $item]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner un service';
        foreach ($items as $i) {
            $options[$i['code_service']] = $i['libelle_service'];
        }
        $this->json(['options' => $options]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/services/edit.php', ['service' => []]);
    }
}
