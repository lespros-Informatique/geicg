<?php

class TarifArticleController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelTarifArticle();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/tarifs_articles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $tarifs = $this->model->getAll();
        $data = [];

        foreach ($tarifs as $t) {
            $idCrypte = $this->validator->crypter($t['id_tarif']);
            $data[] = [
                'code' => $t['code_tarif'],
                'pressing' => $t['pressing_code'] ?? '',
                'article' => $t['article_code'] ?? '',
                'service' => $t['service_code'] ?? '',
                'prix' => $t['prix_tarif'] ?? 0,
                'statut' => $t['statut_tarif'],
                'id' => $t['id_tarif'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['pressing_code' => $_POST['pressing_code'] ?? '', 'article_code' => $_POST['article_code'] ?? '', 'service_code' => $_POST['service_code'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $code = $this->post('code_tarif') ?: $this->validator->generateCode(TABLES::TARIFS_ARTICLES, 'code_tarif', 'TAR-', 6);
        if ($this->validator->getByElement(TABLES::TARIFS_ARTICLES, 'code_tarif', $code)) {
            $this->error('Ce code tarif existe déjà!');
            return;
        }

        $data = [
            'code_tarif' => $code,
            'pressing_code' => $this->post('pressing_code'),
            'article_code' => $this->post('article_code'),
            'service_code' => $this->post('service_code'),
            'prix_tarif' => $this->post('prix_tarif') ?: 0,
            'statut_tarif' => 'actif',
            'created_at_tarif' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Tarif ajouté avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['pressing_code' => $_POST['pressing_code'] ?? '', 'article_code' => $_POST['article_code'] ?? '', 'service_code' => $_POST['service_code'] ?? '', 'id_tarif' => $_POST['id_tarif'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $statut = ($this->post('actif') == 1) ? 'actif' : 'inactif';
        $id = (int) $this->post('id_tarif');

        $data = [
            'pressing_code' => $this->post('pressing_code'),
            'article_code' => $this->post('article_code'),
            'service_code' => $this->post('service_code'),
            'prix_tarif' => $this->post('prix_tarif') ?: 0,
            'statut_tarif' => $statut,
            'updated_at_tarif' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Tarif modifié avec succès!');
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
            $this->error('Tarif introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'tarif/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'tarif/list');
            exit();
        }

        $this->loadView('../views/tarifs_articles/details.php', [
            'tarif' => $item,
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
                header('Location: ' . RACINE . 'tarif/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'tarif/list');
            exit();
        }

        $this->loadView('../views/tarifs_articles/edit.php', ['tarif' => $item]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner un tarif';
        foreach ($items as $i) {
            $options[$i['code_tarif']] = $i['code_tarif'];
        }
        $this->json(['options' => $options]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/tarifs_articles/edit.php', ['tarif' => []]);
    }
}
