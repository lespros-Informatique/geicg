<?php

class MaquetteController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelMaquette();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::VIEW_FILIERES, PERMISSIONS::VIEW_MATIERES]);

        $annees = (new ModelAnnee())->getAll();
        $filieres = (new ModelFiliere())->getByStatus('actif');
        $niveaux = (new ModelNiveau())->getActifs();
        $matieres = (new ModelMatiere())->getByStatus('actif');

        $activeAnnee = Session::get('annee_active_code', '');

        $this->loadView('../views/maquettes/list.php', [
            'annees' => $annees,
            'filieres' => $filieres,
            'niveaux' => $niveaux,
            'matieres' => $matieres,
            'activeAnnee' => $activeAnnee
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::VIEW_FILIERES, PERMISSIONS::VIEW_MATIERES]);

        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code', '');
        $filiereCode = $_GET['filiere_code'] ?? null;
        $niveauCode = $_GET['niveau_code'] ?? null;
        $categorie = $_GET['categorie'] ?? null;

        $items = $this->model->getFilteredList($anneeCode, $filiereCode, $niveauCode, $categorie);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_maquette'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function apiGetMaquetteForFiliere()
    {
        $this->requireAuth();
        $filiereCode = $_GET['filiere_code'] ?? '';
        $niveauCode = $_GET['niveau_code'] ?? '';
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code', '');

        if (empty($filiereCode) || empty($niveauCode)) {
            $this->json(['status' => 0, 'data' => []]);
            return;
        }

        $items = $this->model->getMaquetteForFiliereNiveau($filiereCode, $niveauCode, $anneeCode);
        $this->json(['status' => 1, 'data' => $items]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::MANAGE_FILIERES, PERMISSIONS::MANAGE_MATIERES]);

        $data = $_POST;
        unset($data['csrf_token']);
        unset($data['id_maquette']);
        unset($data['id']);

        $data['user_code'] = $this->getCurrentUserCode();
        $data['etablissement_code'] = $this->getActiveEtablissementCode();

        $res = $this->model->saveMatiereToMaquette($data);
        $this->json($res);
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::MANAGE_FILIERES, PERMISSIONS::MANAGE_MATIERES]);

        $data = $_POST;
        unset($data['csrf_token']);

        $id = $data['id_maquette'] ?? $data['id'] ?? null;
        if (!empty($id) && !is_numeric($id)) {
            $id = $this->validator->decrypter($id);
            $data['id_maquette'] = $id;
        }

        $data['user_code'] = $this->getCurrentUserCode();
        $data['etablissement_code'] = $this->getActiveEtablissementCode();

        $res = $this->model->saveMatiereToMaquette($data);
        $this->json($res);
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::MANAGE_FILIERES, PERMISSIONS::MANAGE_MATIERES]);

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->json(['status' => 0, 'message' => 'Identifiant manquant']);
            return;
        }
        if (!is_numeric($id)) {
            $id = $this->validator->decrypter($id);
        }

        $item = $this->model->getById($id);
        if (!$item) {
            $this->json(['status' => 0, 'message' => 'Élément introuvable']);
            return;
        }

        $newStatut = ($item['statut_maquette'] ?? 'actif') === 'actif' ? 'inactif' : 'actif';
        $success = $this->model->update($id, ['statut_maquette' => $newStatut]);

        if ($success) {
            $this->json(['status' => 1, 'message' => 'Statut mis à jour', 'nouveau_statut' => $newStatut]);
        } else {
            $this->json(['status' => 0, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    public function delete()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::MANAGE_FILIERES, PERMISSIONS::MANAGE_MATIERES]);

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->json(['status' => 0, 'message' => 'Identifiant manquant']);
            return;
        }
        if (!is_numeric($id)) {
            $id = $this->validator->decrypter($id);
        }

        $success = $this->model->delete($id);
        if ($success) {
            $this->json(['status' => 1, 'message' => 'Matière retirée de la maquette avec succès']);
        } else {
            $this->json(['status' => 0, 'message' => 'Erreur lors de la suppression']);
        }
    }

    public function dupliquer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::MANAGE_FILIERES, PERMISSIONS::MANAGE_MATIERES]);

        $sourceAnnee = trim($_POST['source_annee_code'] ?? '');
        $targetAnnee = trim($_POST['target_annee_code'] ?? '');
        $filiereCode = !empty($_POST['filiere_code']) ? trim($_POST['filiere_code']) : null;
        $niveauCode = !empty($_POST['niveau_code']) ? trim($_POST['niveau_code']) : null;

        $res = $this->model->duplicateMaquette($sourceAnnee, $targetAnnee, $filiereCode, $niveauCode);
        $this->json($res);
    }
}
