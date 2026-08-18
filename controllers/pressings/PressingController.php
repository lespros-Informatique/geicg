<?php

class PressingController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelPressing();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/pressings/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressings = $this->model->getAll();
        $pressingCode = $this->getCurrentPressingCode();

        if ($pressingCode !== null) {
            $pressings = array_filter($pressings, function($p) use ($pressingCode) {
                return $p['code_pressing'] === $pressingCode;
            });
        }

        $data = [];

        foreach ($pressings as $p) {
            $idCrypte = $this->validator->crypter($p['id_pressing']);
            $data[] = [
                'code' => $p['code_pressing'],
                'libelle' => $p['libelle_pressing'],
                'telephone' => $p['telephone_pressing'] ?? '',
                'email' => $p['email_pressing'] ?? '',
                'adresse' => $p['adresse_pressing'] ?? '',
                'statut' => $p['statut_pressing'],
                'id' => $p['id_pressing'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['libelle_pressing' => $_POST['libelle_pressing'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $code = $this->post('code_pressing') ?: $this->validator->generateCode(TABLES::PRESSINGS, 'code_pressing', 'PRS-', 6);
        if ($this->validator->getByElement(TABLES::PRESSINGS, 'code_pressing', $code)) {
            $this->error('Ce code pressing existe déjà!');
            return;
        }

        $data = [
            'code_pressing' => $code,
            'libelle_pressing' => $this->post('libelle_pressing'),
            'telephone_pressing' => $this->post('telephone_pressing') ?? '',
            'email_pressing' => $this->post('email_pressing') ?? '',
            'adresse_pressing' => $this->post('adresse_pressing') ?? '',
            'ville_code' => $this->post('ville_code') ?? '',
            'quartier_code' => $this->post('quartier_code') ?? '',
            'latitude_pressing' => $this->post('latitude_pressing') ?? null,
            'longitude_pressing' => $this->post('longitude_pressing') ?? null,
            'logo_pressing' => $this->post('logo_pressing') ?? '',
            'statut_pressing' => 'actif',
            'created_at_pressing' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Pressing ajouté avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['libelle_pressing' => $_POST['libelle_pressing'] ?? '', 'id_pressing' => $_POST['id_pressing'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $statut = in_array($this->post('statut_pressing'), ['actif', 'inactif', 'suspendu']) ? $this->post('statut_pressing') : 'actif';
        $id = (int) $this->post('id_pressing');

        $data = [
            'libelle_pressing' => $this->post('libelle_pressing'),
            'telephone_pressing' => $this->post('telephone_pressing') ?? '',
            'email_pressing' => $this->post('email_pressing') ?? '',
            'adresse_pressing' => $this->post('adresse_pressing') ?? '',
            'ville_code' => $this->post('ville_code') ?? '',
            'quartier_code' => $this->post('quartier_code') ?? '',
            'latitude_pressing' => $this->post('latitude_pressing') ?? null,
            'longitude_pressing' => $this->post('longitude_pressing') ?? null,
            'logo_pressing' => $this->post('logo_pressing') ?? '',
            'statut_pressing' => $statut,
            'updated_at_pressing' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Pressing modifié avec succès!');
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
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Pressing introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $item = null;
        $id = null;

        // Tenter le décryptage de l'identifiant
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
        } catch (Exception $e) {
            $item = null;
        }

        // Si non trouvé par ID crypté, chercher par code_pressing
        if (!$item) {
            $item = $this->validator->getByElement(TABLES::PRESSINGS, 'code_pressing', $details);
            if ($item) {
                $id = $item['id_pressing'];
            }
        }

        // Si non trouvé, chercher par ID direct numérique
        if (!$item && is_numeric($details)) {
            $item = $this->model->getById((int)$details);
            if ($item) {
                $id = $item['id_pressing'];
            }
        }

        if (!$item) {
            header('Location: ' . RACINE . 'pressing/list');
            exit();
        }

        $encryptedId = $this->validator->crypter($id);
        $pressingCode = $item['code_pressing'];

        // Si l'utilisateur est un gérant de pressing (ROLE-PRO), s'assurer qu'il accède bien à son propre pressing
        $this->requirePressingAccess($pressingCode);

        // Chargement de l'écosystème 360° du pressing
        $stats = $this->model->getPressingStats($pressingCode);
        $orders = $this->model->getPressingOrders($pressingCode, 100);
        $tarifs = $this->model->getPressingTarifs($pressingCode);
        $horaires = $this->model->getPressingHoraires($pressingCode);
        $clients = $this->model->getPressingClients($pressingCode);
        $missions = $this->model->getPressingMissions($pressingCode, 100);
        $abonnement = $this->model->getPressingAbonnement($pressingCode);

        $this->loadView('../views/pressings/details.php', [
            'pressing' => $item,
            'encryptedId' => $encryptedId,
            'stats' => $stats,
            'orders' => $orders,
            'tarifs' => $tarifs,
            'horaires' => $horaires,
            'clients' => $clients,
            'missions' => $missions,
            'abonnement' => $abonnement
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'pressing/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'pressing/list');
            exit();
        }

        $this->loadView('../views/pressings/edit.php', ['pressing' => $item]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner un pressing';
        foreach ($items as $i) {
            $options[$i['code_pressing']] = $i['libelle_pressing'];
        }
        $this->json(['options' => $options]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/pressings/edit.php', ['pressing' => []]);
    }
}
