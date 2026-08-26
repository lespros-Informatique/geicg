<?php

class InscriptionController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelInscription();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/inscriptions/list.php');
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
        if (empty($data['code_inscription'])) {
            $data['code_inscription'] = $this->validator->generateCode('inscriptions', 'code_inscription', 'INS-', 8);
        }
        $data['statut_inscription'] = $data['statut_inscription'] ?? 'actif';
        $data['created_at_inscription'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_inscription');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
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
                SELECT ins.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant, e.sexe_etudiant, e.date_naissance_etudiant,
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       a.libelle_annee
                FROM inscriptions ins
                LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN annees a ON a.code_annee = ins.annee_code
                WHERE ins.id_inscription = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { 
                $this->renderNotFound("L'inscription demandée est introuvable.");
                return;
            }

            // Paiements pour cette inscription
            $stmtP = $this->model->getCon()->prepare("
                SELECT * FROM paiements 
                WHERE inscription_code = ?
                ORDER BY date_paiement DESC
            ");
            $stmtP->execute([$item['code_inscription']]);
            $paiements = $stmtP->fetchAll(PDO::FETCH_ASSOC);

            $scolarite = (float)($item['montant_scolarite_inscription'] ?? 0);
            $totalPaye = 0;
            foreach ($paiements as $p) {
                if (($p['statut_paiement'] ?? '') !== 'annule') {
                    $totalPaye += (float)($p['montant_paiement'] ?? 0);
                }
            }
            $solde = max(0, $scolarite - $totalPaye);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("InscriptionController::details error: " . $e->getMessage());
            $this->renderNotFound("L'inscription demandée est introuvable.");
            return;
        }
        $this->loadView('../views/inscriptions/details.php', [
            'item' => $item, 
            'paiements' => $paiements,
            'totalPaye' => $totalPaye,
            'solde' => $solde,
            'scolarite' => $scolarite,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'inscription/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'inscription/list'); exit();
        }
        $this->loadView('../views/inscriptions/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/inscriptions/edit.php', ['item' => []]);
    }
}
