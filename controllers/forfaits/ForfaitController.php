<?php

class ForfaitController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelForfait();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/forfaits/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];

        foreach ($items as $i) {
            $idCrypte = $this->validator->crypter($i['id_forfait']);
            $data[] = [
                'code' => $i['code_forfait'],
                'libelle' => $i['libelle_forfait'],
                'montant' => $i['montant_forfait'] ?? 0,
                'statut' => $i['statut_forfait'],
                'id' => $i['id_forfait'],
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
}
