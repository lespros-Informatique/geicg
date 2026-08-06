<?php

class ArticleController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelArticle();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/articles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $articles = $this->model->getAll();
        $data = [];

        foreach ($articles as $a) {
            $idCrypte = $this->validator->crypter($a['id_article']);
            $data[] = [
                'code' => $a['code_article'],
                'nom' => $a['libelle_article'],
                'categorie' => $a['categorie_article_code'] ?? '',
                'pressing' => $a['pressing_code'] ?? '',
                'statut' => $a['statut_article'],
                'id' => $a['id_article'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['libelle_article' => $_POST['libelle_article'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $code = $this->post('code_article') ?: $this->validator->generateCode(TABLES::ARTICLES_PRESSINGS, 'code_article', 'ART-', 6);
        if ($this->validator->getByElement(TABLES::ARTICLES_PRESSINGS, 'code_article', $code)) {
            $this->error('Ce code article existe déjà!');
            return;
        }

        $data = [
            'code_article' => $code,
            'pressing_code' => $this->post('pressing_code') ?: 'PRS-001',
            'categorie_article_code' => $this->post('categorie_article_code') ?: '',
            'libelle_article' => $this->post('libelle_article'),
            'description_article' => $this->post('description_article') ?? '',
            'statut_article' => 'actif',
            'created_at_article' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Article ajouté avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['libelle_article' => $_POST['libelle_article'] ?? '', 'id_article' => $_POST['id_article'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $statut = ($this->post('actif') == 1) ? 'actif' : 'inactif';
        $id = (int) $this->post('id_article');

        $data = [
            'pressing_code' => $this->post('pressing_code') ?: 'PRS-001',
            'categorie_article_code' => $this->post('categorie_article_code') ?: '',
            'libelle_article' => $this->post('libelle_article'),
            'description_article' => $this->post('description_article') ?? '',
            'statut_article' => $statut,
            'updated_at_article' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Article modifié avec succès!');
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
            $this->error('Article introuvable!');
        }
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/articles/edit.php', ['article' => []]);
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'article/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'article/list');
            exit();
        }

        $this->loadView('../views/articles/details.php', [
            'article' => $item,
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
                header('Location: ' . RACINE . 'article/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'article/list');
            exit();
        }

        $this->loadView('../views/articles/edit.php', ['article' => $item]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner un article';
        foreach ($items as $i) {
            $options[$i['code_article']] = $i['libelle_article'];
        }
        $this->json(['options' => $options]);
    }
}