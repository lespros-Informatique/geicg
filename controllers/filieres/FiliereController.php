<?php

class FiliereController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelFiliere();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_FILIERES);
        $this->loadView('../views/filieres/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_FILIERES);
        $status = $_GET['statut'] ?? ($_GET['status'] ?? null);
        if ($status !== null && $status !== '') {
            $items = $this->model->getByStatus($status);
        } else {
            $items = $this->model->getAll();
        }
        $etabCfg = $this->getEtablissementConfig();
        $useSlugFiliere = !empty($etabCfg['use_slug_filiere']);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_filiere'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'use_slug_filiere' => $useSlugFiliere
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_FILIERES);
        $data = $_POST;
        unset($data['csrf_token'], $data['id_filiere'], $data['id']);

        if (!empty($data['libelle_filiere'])) {
            if (!$this->checkUnique('filieres', 'libelle_filiere', $data['libelle_filiere'], 'Nom de la filière')) return;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        if (empty($data['code_filiere'])) {
            $data['code_filiere'] = $this->validator->generateCode(TABLES::FILIERES, 'code_filiere', 'FIL-', 8);
        }
        $data['statut_filiere'] = $data['statut_filiere'] ?? 'actif';
        $data['created_at_filiere'] = date('Y-m-d H:i:s');
        if (isset($data['type_filiere']) && trim($data['type_filiere']) === '') {
            $data['type_filiere'] = null;
        }
        if (isset($data['slug_filiere'])) {
            $data['slug_filiere'] = trim($data['slug_filiere']) !== '' ? trim($data['slug_filiere']) : null;
        }
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;

        $libelle = trim($data['libelle_filiere'] ?? '');
        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $msg = !empty($libelle) ? "La filière « {$libelle} » a été créée avec succès !" : "Filière créée avec succès !";
            $this->success($msg);
        } else {
            $msg = method_exists($this->model, 'getLastError') && $this->model->getLastError() 
                ? $this->model->getLastError() 
                : 'Erreur lors de la création de la filière';
            $this->error($msg);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_FILIERES);
        $id = (int)$this->post('id_filiere');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['libelle_filiere'])) {
            if (!$this->checkUnique('filieres', 'libelle_filiere', $data['libelle_filiere'], 'Nom de la filière', 'id_filiere', $id)) return;
        }
        if (isset($data['type_filiere']) && trim($data['type_filiere']) === '') {
            $data['type_filiere'] = null;
        }
        if (isset($data['slug_filiere'])) {
            $data['slug_filiere'] = trim($data['slug_filiere']) !== '' ? trim($data['slug_filiere']) : null;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        if (empty($data['user_code'])) $data['user_code'] = $userCode;
        if (empty($data['etablissement_code'])) {
            $existingItem = $this->model->getById($id);
            $data['etablissement_code'] = $existingItem['etablissement_code'] ?? $etabCode;
        }
        if (empty($data['annee_code'])) $data['annee_code'] = $anneeCode;

        $libelle = trim($data['libelle_filiere'] ?? '');
        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $msg = !empty($libelle) ? "La filière « {$libelle} » a été modifiée avec succès !" : "Filière modifiée avec succès !";
            $this->success($msg);
        } else {
            $this->error($this->model->getLastError() ?: 'Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_FILIERES);
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
        $this->requirePermission(PERMISSIONS::VIEW_FILIERES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'filiere/list'); exit(); }

            $filiereCode = $item['code_filiere'];
            $cycles = $this->model->getCyclesForFiliere($filiereCode);
            $classes = $this->model->getClassesForFiliere($filiereCode);
            $stats = $this->model->getStatsForFiliere($filiereCode, count($classes));

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'filiere/list'); exit();
        }
        $this->loadView('../views/filieres/details.php', [
            'item' => $item, 
            'cycles' => $cycles,
            'classes' => $classes,
            'stats' => $stats,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_FILIERES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'filiere/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'filiere/list'); exit();
        }
        $this->loadView('../views/filieres/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_FILIERES);
        $this->loadView('../views/filieres/edit.php', ['item' => []]);
    }

    /**
     * Impression du Catalogue Général des Filières via mPDF
     */
    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::PRINT_FILIERES, PERMISSIONS::VIEW_FILIERES, PERMISSIONS::PRINT_OFFRE_ACADEMIQUE]);

        require_once __DIR__ . '/../../core/PdfService.php';

        $filieres = $this->model->getCatalogueForPdf();
        $etablissement = $this->getEtablissementConfig();
        $anneeLibelle = $this->getActiveAnneeLibelle();

        $editeurNom = Auth::fullName();
        if (empty($editeurNom)) {
            $editeurNom = 'Direction des Études & Scolarité';
        }

        $data = [
            'etablissement' => $etablissement,
            'annee_libelle' => $anneeLibelle,
            'editeur_nom' => $editeurNom,
            'filieres' => $filieres
        ];

        $html = PdfService::renderTemplate('catalogue_filieres.php', $data);
        $filename = 'Catalogue_Filieres_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Catalogue des Filières - ' . ($etablissement['libelle_etablissement'] ?? 'GEICG'),
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 12
        ]);
    }
}