<?php

class EmploiController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEmploi();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/emplois_temps/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_emploi'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $_SESSION['annee_active_code'] ?? '0GklBk07waYoLB6pHwY';
        $etabCode = '5454544456';
        $data = $_POST;
        unset($data['csrf_token']);
        if (empty($data['code_emploi'])) {
            $data['code_emploi'] = $this->validator->generateCode('emplois_temps', 'code_emploi', 'EMP-', 8);
        }
        $data['statut_emploi'] = $data['statut_emploi'] ?? 'actif';
        $data['created_at_emploi'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE emplois_temps")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Item créé avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_emploi');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE emplois_temps")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Item introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT edt.*, 
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       m.libelle_matiere,
                       s.libelle_salle, s.capacite_salle,
                       COALESCE(e.nom_enseignant, u.nom_user) as nom_prof,
                       COALESCE(e.prenom_enseignant, u.prenom_user) as prenom_prof,
                       e.grade_enseignant
                FROM emplois_temps edt
                LEFT JOIN classes cl ON cl.code_classe = edt.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN matieres m ON m.code_matiere = edt.matiere_code
                LEFT JOIN salles s ON s.code_salle = edt.salle_code
                LEFT JOIN enseignants e ON e.code_enseignant = edt.enseignant_code
                LEFT JOIN users u ON u.code_user = edt.user_code
                WHERE edt.id_emploi = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'emploi/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'emploi/list'); exit();
        }
        $this->loadView('../views/emplois_temps/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'emploi/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'emploi/list'); exit();
        }
        $this->loadView('../views/emplois_temps/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/emplois_temps/edit.php', ['item' => []]);
    }
}
