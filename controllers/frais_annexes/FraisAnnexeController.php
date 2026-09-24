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
        $categorie = $_GET['categorie'] ?? null;

        $items = $this->model->getAll($anneeCode, $typeFiliere, $niveauCode, $categorie);
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
        $categorie = in_array($data['categorie_frais_annexe'] ?? '', ['inscription', 'autre'], true) ? $data['categorie_frais_annexe'] : 'autre';
        $data['categorie_frais_annexe'] = $categorie;
        $typeFiliere = $data['type_filiere'] ?? 'TOUT';
        $niveauCode = (empty($data['niveau_code']) || $data['niveau_code'] === 'TOUT') ? null : $data['niveau_code'];

        if (empty($libelle)) {
            $this->error("Le libellé de la tarification est obligatoire.");
            return;
        }
        // Contrôle Anti-Doublon (Filière x Niveau d'étude x Catégorie pour la même année)
        $duplicate = $this->model->checkDuplicate($anneeCode, $typeFiliere, $niveauCode, $categorie);
        if ($duplicate) {
            $catLabel = ($categorie === 'inscription') ? 'Inscription' : 'Autre';
            $configStr = "Catégorie : " . htmlspecialchars($catLabel) . " | Filière : " . htmlspecialchars($typeFiliere) . ($niveauCode ? " | Niveau : " . htmlspecialchars($niveauCode) : " | Tous les Niveaux");
            $this->error("Création impossible : Un tarif de frais annexes est déjà configuré pour [{$configStr}] sur cette année académique (Réf: " . htmlspecialchars($duplicate['code_frais_annexe'] ?? '') . ").");
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
        $categorie = in_array($data['categorie_frais_annexe'] ?? '', ['inscription', 'autre'], true) ? $data['categorie_frais_annexe'] : 'autre';
        $data['categorie_frais_annexe'] = $categorie;
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

        // Contrôle Anti-Doublon sur la combinaison
        $duplicate = $this->model->checkDuplicate($itemAnneeCode, $typeFiliere, $niveauCode, $categorie, $id);
        if ($duplicate) {
            $catLabel = ($categorie === 'inscription') ? 'Inscription' : 'Autre';
            $configStr = "Catégorie : " . htmlspecialchars($catLabel) . " | Filière : " . htmlspecialchars($typeFiliere) . ($niveauCode ? " | Niveau : " . htmlspecialchars($niveauCode) : " | Tous les Niveaux");
            $this->error("Modification impossible : Un autre tarif de frais annexes est déjà configuré pour [{$configStr}] sur cette année académique (Réf: " . htmlspecialchars($duplicate['code_frais_annexe'] ?? '') . ").");
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

    /**
     * Impression de la Grille des Frais Annexes via mPDF
     */
    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission(['PRINT_FRAIS_ANNEXES', 'VIEW_FRAIS_ANNEXES', 'MANAGE_FRAIS_ANNEXES', 'PRINT_FRAIS_SCOLARITE', 'VIEW_FRAIS_SCOLARITE']);

        require_once __DIR__ . '/../../core/PdfService.php';

        $db = $this->model->getCon();
        $anneeCode = !empty($_GET['annee_code']) ? trim($_GET['annee_code']) : $this->getActiveAnneeCode();
        $categorie = !empty($_GET['categorie']) ? trim($_GET['categorie']) : null;
        $typeFiliere = !empty($_GET['type_filiere']) ? trim($_GET['type_filiere']) : null;
        $niveauCode = !empty($_GET['niveau_code']) ? trim($_GET['niveau_code']) : null;

        $anneeLibelle = $this->getActiveAnneeLibelle();
        if ($anneeCode) {
            $stmtA = $db->prepare("SELECT libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$anneeCode]);
            $aName = $stmtA->fetchColumn();
            if ($aName) $anneeLibelle = $aName;
        }

        $fraisAnnexes = $this->model->getAll($anneeCode, $typeFiliere, $niveauCode, $categorie);

        $etablissement = $this->getEtablissementConfig();

        $authSession = $_SESSION[USERS_AUTH] ?? [];
        $editeurNom = trim(($authSession['prenom_user'] ?? '') . ' ' . ($authSession['nom_user'] ?? ''));
        if (empty($editeurNom)) {
            $editeurNom = 'Service Comptabilité & Scolarité';
        }

        $data = [
            'etablissement' => $etablissement,
            'annee_libelle' => $anneeLibelle,
            'editeur_nom' => $editeurNom,
            'frais_annexes' => $fraisAnnexes,
            'filtres' => [
                'annee_code' => $anneeCode,
                'categorie' => $categorie,
                'type_filiere' => $typeFiliere,
                'niveau_code' => $niveauCode
            ]
        ];

        $html = PdfService::renderTemplate('grille_frais_annexes.php', $data);
        $filename = 'Grille_Frais_Annexes_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Grille des Frais Annexes - ' . ($etablissement['libelle_etablissement'] ?? 'GROUPE EICG'),
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 12
        ]);
    }
}
