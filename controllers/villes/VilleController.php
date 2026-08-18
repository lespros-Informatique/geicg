<?php

class VilleController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelVille();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/villes/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];

        foreach ($items as $i) {
            $idCrypte = $this->validator->crypter($i['id_ville']);
            $data[] = [
                'code' => $i['code_ville'],
                'libelle' => $i['libelle_ville'],
                'statut' => $i['statut_ville'],
                'id' => $i['id_ville'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->error('Non implemente');
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->error('Non implemente');
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut de la ville modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur lors du changement de statut');
            }
        } else {
            $this->error('Ville introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->error('Non implemente');
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->error('Non implemente');
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Selectionner une ville';
        foreach ($items as $i) {
            $options[$i['code_ville']] = $i['libelle_ville'];
        }
        $this->json(['options' => $options]);
    }
}
