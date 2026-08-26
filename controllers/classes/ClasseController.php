<?php

class ClasseController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelClasse();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/classes/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $sql = "SELECT c.*, 
                       f.libelle_filiere,
                       n.libelle_niveau
                FROM classes c
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                ORDER BY c.id_classe DESC";
        $items = $this->model->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_classe'];
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
        $data = $_POST;
        unset($data['csrf_token']);

        // Génération automatique intelligente de secours si le champ libelle_classe est vide
        if (empty($data['libelle_classe']) && !empty($data['filiere_code']) && !empty($data['niveau_code'])) {
            $stmtF = $this->model->getCon()->prepare("SELECT libelle_filiere FROM filieres WHERE code_filiere = ?");
            $stmtF->execute([$data['filiere_code']]);
            $fName = $stmtF->fetchColumn();

            $stmtN = $this->model->getCon()->prepare("SELECT libelle_niveau FROM niveaux WHERE code_niveau = ?");
            $stmtN->execute([$data['niveau_code']]);
            $nName = $stmtN->fetchColumn();

            if ($fName && $nName) {
                $data['libelle_classe'] = trim($fName) . ' - ' . trim($nName);
            }
        }

        if (!empty($data['libelle_classe'])) {
            if (!$this->checkUnique('classes', 'libelle_classe', $data['libelle_classe'], 'Nom de la classe')) return;
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $_SESSION['annee_active_code'] ?? '0GklBk07waYoLB6pHwY';
        $etabCode = '5454544456';
        if (empty($data['code_classe'])) {
            $data['code_classe'] = $this->validator->generateCode('classes', 'code_classe', 'CLA-', 8);
        }
        $data['statut_classe'] = $data['statut_classe'] ?? 'actif';
        $data['created_at_classe'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE classes")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Classe créée avec succès!');
        } else {
            $this->error('Erreur lors de la création de la classe.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_classe');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        // Génération automatique intelligente de secours si le champ libelle_classe est vide
        if (empty($data['libelle_classe']) && !empty($data['filiere_code']) && !empty($data['niveau_code'])) {
            $stmtF = $this->model->getCon()->prepare("SELECT libelle_filiere FROM filieres WHERE code_filiere = ?");
            $stmtF->execute([$data['filiere_code']]);
            $fName = $stmtF->fetchColumn();

            $stmtN = $this->model->getCon()->prepare("SELECT libelle_niveau FROM niveaux WHERE code_niveau = ?");
            $stmtN->execute([$data['niveau_code']]);
            $nName = $stmtN->fetchColumn();

            if ($fName && $nName) {
                $data['libelle_classe'] = trim($fName) . ' - ' . trim($nName);
            }
        }

        if (!empty($data['libelle_classe'])) {
            if (!$this->checkUnique('classes', 'libelle_classe', $data['libelle_classe'], 'Nom de la classe', 'id_classe', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE classes")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Classe modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification de la classe.');
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
                SELECT c.*, 
                       f.libelle_filiere,
                       n.libelle_niveau
                FROM classes c
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                WHERE c.id_classe = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'classe/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'classe/list'); exit();
        }
        $this->loadView('../views/classes/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'classe/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'classe/list'); exit();
        }
        $this->loadView('../views/classes/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/classes/edit.php', ['item' => []]);
    }
}