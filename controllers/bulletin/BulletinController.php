<?php

class BulletinController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelBulletin();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/bulletin/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $sql = "SELECT b.*, 
                       i.code_inscription,
                       CONCAT(e.nom_etudiant, ' ', e.prenom_etudiant) AS etudiant_nom,
                       cl.libelle_classe AS classe_nom,
                       s.libelle_semestre AS semestre_nom
                FROM bulletins b
                LEFT JOIN inscriptions i ON i.code_inscription = b.inscription_code
                LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = i.classe_code
                LEFT JOIN semestres s ON s.code_semestre = b.semestre_code
                ORDER BY b.id_bulletin DESC";
        $items = $this->model->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_bulletin'];
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

        $inscriptionCode = $data['inscription_code'] ?? '';
        $semestreCode    = $data['semestre_code'] ?? '';

        if (empty($inscriptionCode)) {
            $this->error('Veuillez sélectionner une inscription.');
            return;
        }

        // Vérifier si un bulletin existe déjà pour cette inscription + semestre + année
        $stmt = $this->model->getCon()->prepare(
            "SELECT id_bulletin FROM bulletins WHERE inscription_code = ? AND semestre_code = ? AND annee_code = ?"
        );
        $stmt->execute([$inscriptionCode, $semestreCode ?: null, $anneeCode]);
        if ($stmt->fetch()) {
            $this->error('Un bulletin existe déjà pour cette inscription et ce semestre.');
            return;
        }

        $data['code_bulletin'] = $this->validator->generateCode('bulletins', 'code_bulletin', 'BUL-', 8);
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        $data['created_at_bulletin'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE bulletins")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success('Bulletin créé avec succès!');
        } else {
            $this->error('Erreur lors de la création du bulletin.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_bulletin');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        $data['updated_at_bulletin'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE bulletins")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Bulletin modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification.');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'bulletin/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'bulletin/list'); exit();
        }
        $this->loadView('../views/bulletin/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'bulletin/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'bulletin/list'); exit();
        }
        $this->loadView('../views/bulletin/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/bulletin/edit.php', ['item' => []]);
    }
}

    protected function resolveModel()
    {
        return new ModelBulletin();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/bulletin/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_inscription'];
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

        $etudiantCode = $data['etudiant_code'] ?? '';
        $classeCode   = $data['classe_code'] ?? '';

        if (empty($etudiantCode) || empty($classeCode)) {
            $this->error('Veuillez sélectionner un étudiant et une classe.');
            return;
        }

        // Vérifier si cet étudiant est déjà inscrit pour cette année
        $stmt = $this->model->getCon()->prepare(
            "SELECT id_inscription FROM inscriptions WHERE etudiant_code = ? AND annee_code = ?"
        );
        $stmt->execute([$etudiantCode, $anneeCode]);
        if ($stmt->fetch()) {
            $this->error('Cet étudiant est déjà inscrit pour l\'année académique en cours.');
            return;
        }

        // Générer le code_inscription unique
        $data['code_inscription'] = $this->validator->generateCode('inscriptions', 'code_inscription', 'INS-', 8);

        // Ajouter les champs système
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        $data['created_at_inscription'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success('Inscription créée avec succès!');
        } else {
            $this->error('Erreur lors de la création.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_inscription');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        $data['updated_at_inscription'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Inscription modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'bulletin/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'bulletin/list'); exit();
        }
        $this->loadView('../views/bulletin/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'bulletin/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'bulletin/list'); exit();
        }
        $this->loadView('../views/bulletin/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/bulletin/edit.php', ['item' => []]);
    }
}
