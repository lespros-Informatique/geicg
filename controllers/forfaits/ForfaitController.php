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
                'montant' => (float)($i['montant_forfait'] ?? 0),
                'duree_mois' => (int)($i['duree_mois_forfait'] ?? 1),
                'statut' => $i['statut_forfait'] ?? 'actif',
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
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut du forfait modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur lors du changement de statut');
            }
        } else {
            $this->error('Forfait introuvable!');
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
}
