<?php

class TrancheController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelTranche();
    }

    public function list()
    {
        $this->requireAuth();
        header('Location: ' . RACINE . 'scolarite/list?tab=tranches');
        exit();
    }

    public function apiList()
    {
        $this->requireAuth();
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

        $items = $this->model->getAll($anneeCode);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_tranche'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function validateCumulTranches($scolariteCode, $montantSaisie, $excludeId = null): ?string
    {
        if (empty($scolariteCode)) {
            return 'Veuillez sélectionner une grille de scolarité valide.';
        }

        $scolariteModel = new ModelScolarite();
        $scolarite = $scolariteModel->getByElement('code_scolarite', $scolariteCode);

        if (!$scolarite) {
            return 'La scolarité sélectionnée est introuvable.';
        }

        $montantTotalScolarite = (float)($scolarite['montant_scolarite'] ?? 0);
        $montantNouveau = (float)$montantSaisie;

        if ($montantNouveau <= 0) {
            return 'Le montant de la tranche doit être supérieur à 0 FCFA.';
        }

        $db = $this->model->getCon();
        $sql = "SELECT SUM(montant_tranche) FROM tranches_scolarite WHERE scolarite_code = ? AND statut_tranche = 'actif'";
        $params = [$scolariteCode];

        if ($excludeId !== null) {
            $sql .= " AND id_tranche != ?";
            $params[] = (int)$excludeId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $sommeExistante = (float)($stmt->fetchColumn() ?: 0);

        $cumulFutur = $sommeExistante + $montantNouveau;

        if ($cumulFutur > $montantTotalScolarite) {
            $resteAutorise = max(0, $montantTotalScolarite - $sommeExistante);
            $totalFmt = number_format($montantTotalScolarite, 0, ',', ' ');
            $cumulFmt = number_format($cumulFutur, 0, ',', ' ');
            $resteFmt = number_format($resteAutorise, 0, ',', ' ');

            return "Impossible d'enregistrer : Le montant total de la scolarité ($totalFmt FCFA) est inférieur au cumul des tranches ($cumulFmt FCFA). Montant maximum autorisé pour cette tranche : $resteFmt FCFA.";
        }

        return null;
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        $scolariteCode = $data['scolarite_code'] ?? '';
        $montantTranche = $data['montant_tranche'] ?? 0;

        $errorMsg = $this->validateCumulTranches($scolariteCode, $montantTranche);
        if ($errorMsg !== null) {
            $this->error($errorMsg);
            return;
        }

        if (empty($data['code_tranche'])) {
            $data['code_tranche'] = $this->validator->generateCode('tranches_scolarite', 'code_tranche', 'TRA-', 8);
        }
        $data['statut_tranche'] = $data['statut_tranche'] ?? 'actif';
        $data['created_at_tranche'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE tranches_scolarite")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Tranche créée avec succès!');
        } else {
            $this->error('Erreur lors de la création de la tranche');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_tranche');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        $scolariteCode = $data['scolarite_code'] ?? '';
        $montantTranche = $data['montant_tranche'] ?? 0;

        $errorMsg = $this->validateCumulTranches($scolariteCode, $montantTranche, $id);
        if ($errorMsg !== null) {
            $this->error($errorMsg);
            return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE tranches_scolarite")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Tranche modifiée avec succès!');
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
            $id = is_numeric($details) ? (int)$details : $this->validator->decrypter($details);
            if (!$id && is_numeric($details)) {
                $id = (int)$details;
            }
            $item = $this->model->getById($id);
            if (!$item) {
                $this->renderNotFound("La tranche de scolarité demandée est introuvable.");
                return;
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("TrancheController::details error: " . $e->getMessage());
            $this->renderNotFound("La tranche de scolarité demandée est introuvable.");
            return;
        }
        $this->loadView('../views/tranches_scolarite/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = is_numeric($details) ? (int)$details : $this->validator->decrypter($details);
            if (!$id && is_numeric($details)) {
                $id = (int)$details;
            }
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'scolarite/list?tab=tranches');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'scolarite/list?tab=tranches');
            exit();
        }
        $this->loadView('../views/tranches_scolarite/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/tranches_scolarite/edit.php', ['item' => []]);
    }
}
