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
                'description' => $i['description_forfait'] ?? '',
                'statut' => $i['statut_forfait'] ?? 'actif',
                'id' => (int)$i['id_forfait'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $libelle = trim($this->post('libelle_forfait'));
        $montant = (float)$this->post('montant_forfait');
        $duree = (int)$this->post('duree_mois_forfait') ?: 1;
        $description = trim($this->post('description_forfait'));

        if (empty($libelle) || $montant <= 0) {
            $this->error('Veuillez renseigner le libellé et un montant valide supérieur à 0 !');
            return;
        }

        if (!$this->checkUnique(TABLES::FORFAITS, 'libelle_forfait', $libelle, 'nom de forfait')) return;

        $code = trim($this->post('code_forfait'));
        if (empty($code)) {
            $code = $this->validator->generateCode(TABLES::FORFAITS, 'code_forfait', 'FOR-', 5);
        }

        if ($this->validator->getByElement(TABLES::FORFAITS, 'code_forfait', $code)) {
            $this->error('Ce code forfait existe déjà !');
            return;
        }

        $data = [
            'code_forfait' => $code,
            'libelle_forfait' => $libelle,
            'description_forfait' => $description,
            'montant_forfait' => $montant,
            'duree_mois_forfait' => $duree,
            'statut_forfait' => 'actif',
            'created_at_forfait' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Forfait créé avec succès !', ['code_forfait' => $code]);
        } else {
            $this->error('Erreur lors de la création du forfait');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $id = (int)$this->post('id_forfait');
        $libelle = trim($this->post('libelle_forfait'));
        $montant = (float)$this->post('montant_forfait');
        $duree = (int)$this->post('duree_mois_forfait') ?: 1;
        $description = trim($this->post('description_forfait'));
        $statut = in_array($this->post('statut_forfait'), ['actif', 'inactif']) ? $this->post('statut_forfait') : 'actif';

        if (!$id || empty($libelle) || $montant <= 0) {
            $this->error('Veuillez renseigner tous les champs obligatoires !');
            return;
        }

        $data = [
            'id_forfait' => $id,
            'libelle_forfait' => $libelle,
            'description_forfait' => $description,
            'montant_forfait' => $montant,
            'duree_mois_forfait' => $duree,
            'statut_forfait' => $statut,
            'updated_at_forfait' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data, $id)) {
            $this->success('Forfait modifié avec succès !');
        } else {
            $this->error('Erreur lors de la modification du forfait');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');

        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut du forfait modifié avec succès !', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur lors du changement de statut');
            }
        } else {
            $this->error('Forfait introuvable !');
        }
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById((int)$id);
            if (!$item) {
                header('Location: ' . RACINE . 'forfait/list');
                exit();
            }

            $encryptedId = $this->validator->crypter($id);
            $this->loadView('../views/forfaits/edit.php', [
                'forfait' => $item,
                'encryptedId' => $encryptedId
            ]);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'forfait/list');
            exit();
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById((int)$id);
            if (!$item) {
                $this->error('Forfait introuvable');
                return;
            }

            $this->json(['data' => $item]);
        } catch (Exception $e) {
            $this->error('Identifiant invalide');
        }
    }
}
