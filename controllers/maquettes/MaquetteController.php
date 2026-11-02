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
        $this->requirePermission([PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::VIEW_MAQUETTES, PERMISSIONS::VIEW_FILIERES, PERMISSIONS::VIEW_MATIERES]);

        $annees = (new ModelAnnee())->getAll();
        $filieres = (new ModelFiliere())->getByStatus(STATUTS::ACTIF);
        $niveaux = (new ModelNiveau())->getActifs();
        $matieres = (new ModelMatiere())->getByStatus(STATUTS::ACTIF);

        $activeAnnee = Session::get('annee_active_code', '');

        $this->loadView('../views/maquettes/list.php', [
            'annees' => $annees,
            'filieres' => $filieres,
            'niveaux' => $niveaux,
            'matieres' => $matieres,
            'categories' => CATEGORIES_MATIERES::ALL,
            'activeAnnee' => $activeAnnee,
            'scripts' => ['public/assets/js/modules/maquettes.js']
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::VIEW_MAQUETTES, PERMISSIONS::VIEW_FILIERES, PERMISSIONS::VIEW_MATIERES]);

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

    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::PRINT_MAQUETTES, PERMISSIONS::PRINT_OFFRE_ACADEMIQUE, PERMISSIONS::VIEW_FILIERES, PERMISSIONS::VIEW_MATIERES]);

        require_once __DIR__ . '/../../core/PdfService.php';

        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code', '');
        $filiereCode = !empty($_GET['filiere_code']) ? $_GET['filiere_code'] : null;
        $niveauCode = !empty($_GET['niveau_code']) ? $_GET['niveau_code'] : null;
        $categorie = !empty($_GET['categorie']) ? $_GET['categorie'] : null;

        $items = $this->model->getFilteredList($anneeCode, $filiereCode, $niveauCode, $categorie);
        $etablissement = $this->getEtablissementConfig();

        $anneeModel = new ModelAnnee();
        $anneeObj = !empty($anneeCode) ? $anneeModel->getByCode($anneeCode) : null;
        $anneeLibelle = $anneeObj['libelle_annee'] ?? Session::get('annee_active_libelle', $anneeCode ?: 'Toutes les années');

        $filiereLibelle = 'Toutes les filières';
        if (!empty($filiereCode)) {
            $fModel = new ModelFiliere();
            $fObj = $fModel->getByCode($filiereCode);
            $filiereLibelle = $fObj['libelle_filiere'] ?? $filiereCode;
        }

        $niveauLibelle = 'Tous les niveaux';
        if (!empty($niveauCode)) {
            $nModel = new ModelNiveau();
            $nObj = $nModel->getByCode($niveauCode);
            $niveauLibelle = $nObj['libelle_niveau'] ?? $niveauCode;
        }

        $editeurNom = Auth::fullName();
        if (empty($editeurNom)) {
            $editeurNom = 'Direction des Études & Scolarité';
        }

        // Calcul des métriques & KPI (strictement calculés dans le contrôleur)
        $totalMatieres = count($items);
        $totalGenerales = 0;
        $totalProfessionnelles = 0;
        foreach ($items as $it) {
            if (($it['categorie_matiere'] ?? CATEGORIES_MATIERES::GENERALE) === CATEGORIES_MATIERES::GENERALE) {
                $totalGenerales++;
            } else {
                $totalProfessionnelles++;
            }
        }

        // Résolution du logo
        $logoPath = null;
        if (!empty($etablissement['logo_etablissement'])) {
            $candidateLogo = dirname(__DIR__, 2) . '/public/assets/images/logo/' . $etablissement['logo_etablissement'];
            if (file_exists($candidateLogo)) {
                $logoPath = $candidateLogo;
            }
        }

        $data = [
            'etablissement'         => $etablissement,
            'logoPath'              => $logoPath,
            'annee_libelle'         => $anneeLibelle,
            'filiere_libelle'       => $filiereLibelle,
            'niveau_libelle'        => $niveauLibelle,
            'categorie_filtre'      => $categorie,
            'editeur_nom'           => $editeurNom,
            'date_edition'          => date('d/m/Y à H:i'),
            'totalMatieres'         => $totalMatieres,
            'totalGenerales'        => $totalGenerales,
            'totalProfessionnelles' => $totalProfessionnelles,
            'items'                 => $items
        ];

        $html = PdfService::renderTemplate('maquette_pedagogique.php', $data);
        $filename = 'Maquette_Pedagogique_' . ($anneeCode ?: 'All') . '_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Maquette Pédagogique - ' . ($etablissement['libelle_etablissement'] ?? 'GEICG'),
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 10
        ]);
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

