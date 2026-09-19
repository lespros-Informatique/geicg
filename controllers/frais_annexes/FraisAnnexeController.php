<?php

class FraisAnnexeController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelFraisAnnexe();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_FRAIS_ANNEXES', 'VIEW_FRAIS_ANNEXES', 'MANAGE_FRAIS_SCOLARITE', 'VIEW_FRAIS_SCOLARITE']);
        $db = $this->model->getCon();

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }

        $activeYear = $this->getActiveAnneeCode();
        $annees = $this->getAccessibleAnnees();

        $this->loadView('../views/frais_annexes/list.php', [
            'annees' => $annees,
            'selectedAnneeCode' => $activeYear
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_FRAIS_ANNEXES', 'VIEW_FRAIS_ANNEXES', 'MANAGE_FRAIS_SCOLARITE', 'VIEW_FRAIS_SCOLARITE']);

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $db = $this->model->getCon();
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }

        $anneeCode = $this->getActiveAnneeCode();
        $typeFiliere = $_GET['type_filiere'] ?? null;
        $items = $this->model->getAll($anneeCode, $typeFiliere);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_frais_annexe'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function store()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['MANAGE_FRAIS_ANNEXES', 'MANAGE_FRAIS_SCOLARITE']);
        $data = $_POST;
        unset($data['csrf_token']);

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        if (empty($data['code_frais_annexe'])) {
            $data['code_frais_annexe'] = $this->validator->generateCode('frais_annexes', 'code_frais_annexe', 'FRAIS-ANN-', 8);
        }

        $data['annee_code'] = $anneeCode;
        $data['etablissement_code'] = $etabCode;
        $data['user_code'] = $userCode;
        $data['statut_frais_annexe'] = $data['statut_frais_annexe'] ?? 'actif';
        $data['created_at_frais_annexe'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE frais_annexes")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success("Tarif de frais annexes enregistré avec succès !");
        } else {
            $this->error("Erreur lors de l'enregistrement du tarif de frais annexes.");
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['MANAGE_FRAIS_ANNEXES', 'MANAGE_FRAIS_SCOLARITE']);
        $id = (int)$this->post('id_frais_annexe');
        if (!$id) {
            $this->error('Identifiant invalide');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);
        $data['updated_at_frais_annexe'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE frais_annexes")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->update($filteredData, $id)) {
            $this->success("Tarif de frais annexes modifié avec succès !");
        } else {
            $this->error("Erreur lors de la modification du tarif de frais annexes.");
        }
    }

    public function toggleStatut()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['MANAGE_FRAIS_ANNEXES', 'MANAGE_FRAIS_SCOLARITE']);
        $id = (int)$this->post('id_frais_annexe');
        if (!$id) {
            $this->error('Identifiant invalide');
            return;
        }

        $item = $this->model->getById($id);
        if (!$item) {
            $this->error('Enregistrement introuvable');
            return;
        }

        $newStatut = ($item['statut_frais_annexe'] === 'actif') ? 'inactif' : 'actif';
        if ($this->model->update(['statut_frais_annexe' => $newStatut, 'updated_at_frais_annexe' => date('Y-m-d H:i:s')], $id)) {
            $this->success("Statut mis à jour avec succès (" . ucfirst($newStatut) . ")");
        } else {
            $this->error("Erreur lors du changement de statut.");
        }
    }
}
