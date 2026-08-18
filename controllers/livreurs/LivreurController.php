<?php

class LivreurController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelLivreur();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/livreurs/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();

        if ($pressingCode !== null) {
            $livreurs = $this->model->getByPressing($pressingCode);
        } else {
            $livreurs = $this->model->getAll();
        }

        $data = [];

        foreach ($livreurs as $l) {
            $idCrypte = $this->validator->crypter($l['id_livreur']);
            $data[] = [
                'code' => $l['code_livreur'],
                'nom' => $l['nom_livreur'] ?? '',
                'prenom' => $l['prenom_livreur'] ?? '',
                'telephone' => $l['telephone_livreur'] ?? '',
                'pressing' => $l['pressing_code'] ?? '',
                'statut' => $l['statut_livreur'],
                'id' => $l['id_livreur'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['nom_livreur' => $_POST['nom_livreur'] ?? '', 'telephone_livreur' => $_POST['telephone_livreur'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $code = $this->post('code_livreur') ?: $this->validator->generateCode(TABLES::LIVREURS, 'code_livreur', 'LIV-', 6);
        if ($this->validator->getByElement(TABLES::LIVREURS, 'code_livreur', $code)) {
            $this->error('Ce code livreur existe déjà!');
            return;
        }

        // Attribution automatique du pressing pour le gérant Pro
        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode === null) {
            $pressingCode = $this->post('pressing_code') ?: null;
        }

        $data = [
            'code_livreur' => $code,
            'user_code' => $this->post('user_code') ?: null,
            'pressing_code' => $pressingCode,
            'nom_livreur' => $this->post('nom_livreur'),
            'prenom_livreur' => $this->post('prenom_livreur') ?? '',
            'telephone_livreur' => $this->post('telephone_livreur'),
            'statut_livreur' => 'actif',
            'created_at_livreur' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Livreur ajouté avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['nom_livreur' => $_POST['nom_livreur'] ?? '', 'telephone_livreur' => $_POST['telephone_livreur'] ?? '', 'id_livreur' => $_POST['id_livreur'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $statut = ($this->post('actif') == 1) ? 'actif' : 'inactif';
        $id = (int) $this->post('id_livreur');

        $currentLivreur = $this->model->getById($id);
        if (!$currentLivreur) {
            $this->error('Livreur introuvable');
            return;
        }

        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode !== null) {
            // Si gérant de pressing, sécuriser qu'il n'édite que ses propres livreurs
            if (($currentLivreur['pressing_code'] ?? '') !== $pressingCode) {
                $this->error('Accès refusé', 403);
                return;
            }
        } else {
            $pressingCode = $this->post('pressing_code') ?: null;
        }

        $data = [
            'id_livreur' => $id,
            'user_code' => $this->post('user_code') ?: null,
            'pressing_code' => $pressingCode,
            'nom_livreur' => $this->post('nom_livreur'),
            'prenom_livreur' => $this->post('prenom_livreur') ?? '',
            'telephone_livreur' => $this->post('telephone_livreur'),
            'statut_livreur' => $statut,
            'updated_at_livreur' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Livreur modifié avec succès!');
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
            $this->error('Livreur introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'livreur/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'livreur/list');
            exit();
        }

        $this->loadView('../views/livreurs/details.php', [
            'livreur' => $item,
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
                header('Location: ' . RACINE . 'livreur/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'livreur/list');
            exit();
        }

        $pressings = (new ModelPressing())->getByStatus('actif');

        $this->loadView('../views/livreurs/edit.php', [
            'livreur' => $item,
            'pressings' => $pressings
        ]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode !== null) {
            $items = $this->model->getByPressing($pressingCode);
        } else {
            $items = $this->model->getByStatus('actif');
        }
        $options = [];
        $options[''] = 'Sélectionner un livreur';
        foreach ($items as $i) {
            if (($i['statut_livreur'] ?? '') === 'actif') {
                $options[$i['code_livreur']] = ($i['nom_livreur'] ?? '') . ' ' . ($i['prenom_livreur'] ?? '');
            }
        }
        $this->json(['options' => $options]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $pressings = (new ModelPressing())->getByStatus('actif');
        $this->loadView('../views/livreurs/edit.php', [
            'livreur' => [],
            'pressings' => $pressings
        ]);
    }
}
