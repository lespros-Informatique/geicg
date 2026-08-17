<?php

class AbonnementPressingController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelAbonnementPressing();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/abonnements_pressings/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode !== null) {
            $items = array_filter($items, function($i) use ($pressingCode) {
                return $i['pressing_code'] === $pressingCode;
            });
        }
        $data = [];

        foreach ($items as $i) {
            $idCrypte = $this->validator->crypter($i['id_abonnement_pressing']);
            $data[] = [
                'code' => $i['code_abonnement_pressing'],
                'pressing' => $i['pressing_code'] ?? '',
                'forfait' => $i['forfait_code'] ?? '',
                'montant' => $i['montant_abonnement'] ?? 0,
                'statut' => $i['statut_abonnement_pressing'],
                'id' => $i['id_abonnement_pressing'],
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
