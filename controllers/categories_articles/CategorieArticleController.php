<?php

class CategorieArticleController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelCategorieArticle();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/categories_articles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $categories = $this->model->getAll();
        $data = [];

        foreach ($categories as $c) {
            $idCrypte = $this->validator->crypter($c['id_categorie_article']);
            $data[] = [
                'code' => $c['code_categorie_article'],
                'libelle' => $c['libelle_categorie_article'],
                'description' => $c['description_categorie_article'] ?? '',
                'statut' => $c['statut_categorie_article'],
                'id' => $c['id_categorie_article'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['libelle_categorie_article' => $_POST['libelle_categorie_article'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $code = $this->post('code_categorie_article') ?: $this->validator->generateCode(TABLES::CATEGORIES_ARTICLES, 'code_categorie_article', 'CAT-', 6);
        if ($this->validator->getByElement(TABLES::CATEGORIES_ARTICLES, 'code_categorie_article', $code)) {
            $this->error('Ce code catégorie existe déjà!');
            return;
        }

        $data = [
            'code_categorie_article' => $code,
            'libelle_categorie_article' => $this->post('libelle_categorie_article'),
            'description_categorie_article' => $this->post('description_categorie_article') ?? '',
            'icon_categorie_article' => $this->post('icon_categorie_article') ?? '',
            'statut_categorie_article' => 'actif',
            'created_at_categorie_article' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Catégorie ajoutée avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['libelle_categorie_article' => $_POST['libelle_categorie_article'] ?? '', 'id_categorie_article' => $_POST['id_categorie_article'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $statut = ($this->post('actif') == 1) ? 'actif' : 'inactif';
        $id = (int) $this->post('id_categorie_article');

        $data = [
            'id_categorie_article' => $id,
            'libelle_categorie_article' => $this->post('libelle_categorie_article'),
            'description_categorie_article' => $this->post('description_categorie_article') ?? '',
            'icon_categorie_article' => $this->post('icon_categorie_article') ?? '',
            'statut_categorie_article' => $statut,
            'updated_at_categorie_article' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Catégorie modifiée avec succès!');
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
            $this->error('Catégorie introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'categorie/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'categorie/list');
            exit();
        }

        $this->loadView('../views/categories_articles/details.php', [
            'categorie' => $item,
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
                header('Location: ' . RACINE . 'categorie/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'categorie/list');
            exit();
        }

        $this->loadView('../views/categories_articles/edit.php', ['categorie' => $item]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner une catégorie';
        foreach ($items as $i) {
            $options[$i['code_categorie_article']] = $i['libelle_categorie_article'];
        }
        $this->json(['options' => $options]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/categories_articles/edit.php', ['categorie' => []]);
    }
}
