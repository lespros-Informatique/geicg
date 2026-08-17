<?php

class HorairePressingController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelHorairePressing();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/horaires_pressings/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $horaires = $this->model->getAll();
        $data = [];

        foreach ($horaires as $h) {
            $idCrypte = $this->validator->crypter($h['id_horaire']);
            $data[] = [
                'code' => $h['pressing_code'],
                'jour' => $h['jour'] ?? '',
                'heure_ouverture' => $h['heure_ouverture'] ?? '',
                'heure_fermeture' => $h['heure_fermeture'] ?? '',
                'est_ferme' => $h['est_ferme'] ?? 0,
                'statut' => ($h['est_ferme'] ?? 0) ? 'ferme' : 'actif',
                'id' => $h['id_horaire'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['pressing_code' => $_POST['pressing_code'] ?? '', 'jour' => $_POST['jour'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $data = [
            'pressing_code' => $this->post('pressing_code'),
            'jour' => $this->post('jour'),
            'heure_ouverture' => $this->post('heure_ouverture') ?: null,
            'heure_fermeture' => $this->post('heure_fermeture') ?: null,
            'est_ferme' => $this->post('est_ferme') ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Horaire ajouté avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['pressing_code' => $_POST['pressing_code'] ?? '', 'jour' => $_POST['jour'] ?? '', 'id_horaire' => $_POST['id_horaire'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $id = (int) $this->post('id_horaire');

        $data = [
            'pressing_code' => $this->post('pressing_code'),
            'jour' => $this->post('jour'),
            'heure_ouverture' => $this->post('heure_ouverture') ?: null,
            'heure_fermeture' => $this->post('heure_fermeture') ?: null,
            'est_ferme' => $this->post('est_ferme') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Horaire modifié avec succès!');
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
            $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
        } else {
            $this->error('Horaire introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'horaire/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'horaire/list');
            exit();
        }

        $this->loadView('../views/horaires_pressings/details.php', [
            'horaire' => $item,
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
                header('Location: ' . RACINE . 'horaire/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'horaire/list');
            exit();
        }

        $this->loadView('../views/horaires_pressings/edit.php', ['horaire' => $item]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $this->json(['options' => []]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/horaires_pressings/edit.php', ['horaire' => []]);
    }
}
