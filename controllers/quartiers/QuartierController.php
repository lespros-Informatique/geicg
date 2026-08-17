<?php

class QuartierController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelQuartier();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/quartiers/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];

        foreach ($items as $i) {
            $idCrypte = $this->validator->crypter($i['id_quartier']);
            $data[] = [
                'code' => $i['code_quartier'],
                'libelle' => $i['libelle_quartier'],
                'statut' => $i['statut_quartier'],
                'id' => $i['id_quartier'],
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
        $this->error('Non implemente');
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
        $options[''] = 'Selectionner un quartier';
        foreach ($items as $i) {
            $options[$i['code_quartier']] = $i['libelle_quartier'];
        }
        $this->json(['options' => $options]);
    }
}
