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
        $niveaux = (new ModelNiveau())->getActifs();

        $this->loadView('../views/frais_annexes/list.php', [
            'annees' => $annees,
            'selectedAnneeCode' => $activeYear,
            'niveaux' => $niveaux
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
        $niveauCode = $_GET['niveau_code'] ?? null;

        $items = $this->model->getAll($anneeCode, $typeFiliere, $niveauCode);
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

        $libelle = trim($data['libelle_frais_annexe'] ?? '');
        $typeFiliere = $data['type_filiere'] ?? 'TOUT';
        $niveauCode = (empty($data['niveau_code']) || $data['niveau_code'] === 'TOUT') ? null : $data['niveau_code'];

        if (empty($libelle)) {
            $this->error("Le libellé de la tarification est obligatoire.");
            return;
        }
        // Contrôle Anti-Doublon (Cible Filière x Niveau d'étude pour la même année)
        $duplicate = $this->model->checkDuplicate($anneeCode, $typeFiliere, $niveauCode);
        if ($duplicate) {
            $cibleStr = "Filière : " . htmlspecialchars($typeFiliere) . ($niveauCode ? " | Niveau : " . htmlspecialchars($niveauCode) : " | Tous les Niveaux");
            $this->error("Création impossible : Un tarif de frais annexes est déjà configuré pour la cible [{$cibleStr}] sur cette année académique (Réf: " . htmlspecialchars($duplicate['code_frais_annexe'] ?? '') . ").");
            return;
        }

        if (empty($data['code_frais_annexe'])) {
            $data['code_frais_annexe'] = $this->validator->generateCode('frais_annexes', 'code_frais_annexe', 'FRAIS-ANN-', 8);
        }

        $data['niveau_code'] = $niveauCode;
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
        
        $rawId = $this->post('id_frais_annexe');
        $id = (int)$rawId;
        if (!$id && !empty($rawId)) {
            $id = (int)$this->validator->decrypter($rawId);
        }
        if (!$id) {
            $this->error('Identifiant invalide pour la modification');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        $libelle = trim($data['libelle_frais_annexe'] ?? '');
        $typeFiliere = $data['type_filiere'] ?? 'TOUT';
        $niveauCode = (empty($data['niveau_code']) || $data['niveau_code'] === 'TOUT') ? null : $data['niveau_code'];

        if (empty($libelle)) {
            $this->error("Le libellé de la tarification est obligatoire.");
            return;
        }

        $existingItem = $this->model->getById($id);
        if (!$existingItem) {
            $this->error('Enregistrement introuvable pour la modification');
            return;
        }
        $itemAnneeCode = $existingItem['annee_code'] ?? $this->getActiveAnneeCode();

        // Contrôle Anti-Doublon sur la combinaison cible
        $duplicate = $this->model->checkDuplicate($itemAnneeCode, $typeFiliere, $niveauCode, $id);
        if ($duplicate) {
            $cibleStr = "Filière : " . htmlspecialchars($typeFiliere) . ($niveauCode ? " | Niveau : " . htmlspecialchars($niveauCode) : " | Tous les Niveaux");
            $this->error("Modification impossible : Un autre tarif de frais annexes est déjà configuré pour la cible [{$cibleStr}] sur cette année académique (Réf: " . htmlspecialchars($duplicate['code_frais_annexe'] ?? '') . ").");
            return;
        }

        $data['niveau_code'] = $niveauCode;
        $data['updated_at_frais_annexe'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE frais_annexes")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->update($filteredData, $id)) {
            $this->success("Tarif de frais annexes modifié avec succès !");
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : "Erreur lors de la modification du tarif de frais annexes.";
            $this->error($msg);
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
