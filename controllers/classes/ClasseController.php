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
        $this->requirePermission(PERMISSIONS::VIEW_CLASSES);

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $aRow = (new ModelAnnee())->getByCode($getAnnee);
            if ($aRow) {
                Session::set('annee_active_code', $aRow['code_annee']);
                Session::set('annee_active_libelle', $aRow['libelle_annee']);
            }
        }

        $activeYear = $this->getActiveAnneeCode();
        $annees = (new ModelAnnee())->getAll();
        $parcoursPivots = $this->model->getParcoursPivots();

        $this->loadView('../views/classes/list.php', [
            'annees' => $annees,
            'selectedAnneeCode' => $activeYear,
            'parcoursPivots' => $parcoursPivots
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_CLASSES);

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $aRow = (new ModelAnnee())->getByCode($getAnnee);
            if ($aRow) {
                Session::set('annee_active_code', $aRow['code_annee']);
                Session::set('annee_active_libelle', $aRow['libelle_annee']);
            }
        }

        $anneeCode = $this->getActiveAnneeCode();
        $items = $this->model->getAllWithDetails($anneeCode);
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
        $this->requirePermission(PERMISSIONS::MANAGE_CLASSES);
        $data = $_POST;
        unset($data['csrf_token']);

        // Déduction automatique de cycle_code depuis filiere_cycles si non fourni
        if (empty($data['cycle_code']) && !empty($data['filiere_code'])) {
            $cyCode = $this->model->getCycleForFiliereNiveau($data['filiere_code'], $data['niveau_code'] ?? null);
            if ($cyCode) {
                $data['cycle_code'] = $cyCode;
            }
        }

        // Génération automatique intelligente de secours si le champ libelle_classe est vide
        if (empty($data['libelle_classe']) && !empty($data['filiere_code']) && !empty($data['niveau_code'])) {
            $names = $this->model->getFiliereAndNiveauNames($data['filiere_code'], $data['niveau_code']);
            if (!empty($names['filiere']) && !empty($names['niveau'])) {
                $data['libelle_classe'] = trim($names['filiere']) . ' - ' . trim($names['niveau']);
            }
        }

        $anneeCode = !empty($data['annee_code']) ? trim($data['annee_code']) : $this->getActiveAnneeCode();
        $userCode = Auth::code();

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'filiere_code' => $data['filiere_code'] ?? '',
            'niveau_code' => $data['niveau_code'] ?? '',
            'cycle_code' => $data['cycle_code'] ?? null
        ]);

        if (!empty($data['libelle_classe'])) {
            if (!$this->checkUniquePair('classes', [
                'libelle_classe' => $data['libelle_classe'],
                'annee_code' => $anneeCode
            ], 'nom de classe', 'id_classe', null)) return;
        }

        $etabCode = $this->getActiveEtablissementCode();
        if (empty($data['code_classe'])) {
            $data['code_classe'] = $this->validator->generateCode(TABLES::CLASSES, 'code_classe', 'CLA-', 8);
        }
        $data['statut_classe'] = (!empty($data['statut_classe']) && in_array($data['statut_classe'], ['actif', 'inactif'], true)) ? $data['statut_classe'] : 'actif';
        $data['created_at_classe'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;

        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $this->success('Classe créée avec succès!', ['redirect' => RACINE . 'classe/list']);
        } else {
            $err = $this->model->getLastError() ?: 'Erreur lors de la création de la classe.';
            $this->error($err);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_CLASSES);
        $id = (int)$this->post('id_classe');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        // Déduction automatique de cycle_code depuis filiere_cycles si non fourni
        if (empty($data['cycle_code']) && !empty($data['filiere_code'])) {
            $cyCode = $this->model->getCycleForFiliereNiveau($data['filiere_code'], $data['niveau_code'] ?? null);
            if ($cyCode) {
                $data['cycle_code'] = $cyCode;
            }
        }

        // Génération automatique intelligente de secours si le champ libelle_classe est vide
        if (empty($data['libelle_classe']) && !empty($data['filiere_code']) && !empty($data['niveau_code'])) {
            $names = $this->model->getFiliereAndNiveauNames($data['filiere_code'], $data['niveau_code']);
            if (!empty($names['filiere']) && !empty($names['niveau'])) {
                $data['libelle_classe'] = trim($names['filiere']) . ' - ' . trim($names['niveau']);
            }
        }

        $anneeCode = !empty($data['annee_code']) ? trim($data['annee_code']) : null;
        if (!$anneeCode) {
            $existing = $this->model->getById($id);
            $anneeCode = $existing['annee_code'] ?? $this->getActiveAnneeCode();
        }

        if (!empty($data['libelle_classe'])) {
            if (!$this->checkUniquePair('classes', [
                'libelle_classe' => $data['libelle_classe'],
                'annee_code' => $anneeCode
            ], 'nom de classe', 'id_classe', $id)) return;
        }

        if (isset($data['statut_classe'])) {
            $data['statut_classe'] = in_array($data['statut_classe'], ['actif', 'inactif'], true) ? $data['statut_classe'] : 'actif';
        }
        if (!empty($anneeCode)) {
            $data['annee_code'] = $anneeCode;
        }

        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $this->success('Classe modifiée avec succès!', ['redirect' => RACINE . 'classe/list']);
        } else {
            $err = $this->model->getLastError() ?: 'Erreur lors de la modification de la classe.';
            $this->error($err);
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_CLASSES);
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
        $this->requirePermission(PERMISSIONS::VIEW_CLASSES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getClasseWithDetails($id);
            if (!$item) { 
                $this->renderNotFound("La classe demandée est introuvable.");
                return;
            }

            $classeCode = $item['code_classe'];
            $etudiants = $this->model->getEtudiantsForClasse($classeCode);
            $matieres = $this->model->getMatieresForClasse($classeCode);
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("ClasseController::details error: " . $e->getMessage());
            $this->renderNotFound("La classe demandée est introuvable.");
            return;
        }
        $this->loadView('../views/classes/details.php', [
            'item' => $item, 
            'etudiants' => $etudiants,
            'matieres' => $matieres,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_CLASSES);
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
        $this->requirePermission(PERMISSIONS::MANAGE_CLASSES);
        $this->loadView('../views/classes/edit.php', ['item' => []]);
    }

    public function getClassesWithScolarite()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_CLASSES, PERMISSIONS::MANAGE_ETUDIANTS, PERMISSIONS::VIEW_INSCRIPTIONS, PERMISSIONS::MANAGE_INSCRIPTIONS]);

        $anneeCode = trim($_GET['annee_code'] ?? ($_POST['annee_code'] ?? ''));
        $affectationEtat = trim($_GET['affectation_etat'] ?? ($_POST['affectation_etat'] ?? ''));
        if ($affectationEtat === 'oui') $affectationEtat = 'affecte';
        if ($affectationEtat === 'non') $affectationEtat = 'non_affecte';

        if (empty($anneeCode)) {
            $anneeCode = $this->getActiveAnneeCode();
        }

        $classes = $this->model->getClassesWithScolarite($anneeCode, $affectationEtat);

        $this->json([
            'status' => 1,
            'data' => $classes,
            'annee_code' => $anneeCode,
            'total' => count($classes)
        ]);
    }

    public function reconduire()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_CLASSES);

        $anneeSourceCode = trim($_POST['annee_source_code'] ?? '');
        $anneeCibleCode = trim($_POST['annee_cible_code'] ?? '');

        if (empty($anneeSourceCode) || empty($anneeCibleCode)) {
            $this->error("Veuillez sélectionner l'année source et l'année de destination.");
            return;
        }

        if ($anneeSourceCode === $anneeCibleCode) {
            $this->error("L'année source et l'année de destination doivent être différentes.");
            return;
        }

        $etabCode = $this->getActiveEtablissementCode();
        $userCode = Auth::code();

        $res = $this->model->reconduireClassesAnnee($anneeSourceCode, $anneeCibleCode, $etabCode, $userCode);

        if ($res['success']) {
            $this->success($res['message'], ['reload' => true, 'count' => $res['count'], 'skipped' => $res['skipped']]);
        } else {
            $this->error($res['message']);
        }
    }

    /**
     * Impression du Répertoire des Classes via mPDF
     */
    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::PRINT_CLASSES, PERMISSIONS::VIEW_CLASSES, PERMISSIONS::PRINT_OFFRE_ACADEMIQUE]);

        require_once __DIR__ . '/../../core/PdfService.php';

        $anneeCode = $this->getActiveAnneeCode();
        $anneeLibelle = $this->getActiveAnneeLibelle();
        $classes = $this->model->getAllWithDetails($anneeCode);
        $etablissement = $this->getEtablissementConfig();

        $editeurNom = Auth::fullName();
        if (empty($editeurNom)) {
            $editeurNom = 'Direction des Études & Scolarité';
        }

        $data = [
            'etablissement' => $etablissement,
            'annee_libelle' => $anneeLibelle,
            'editeur_nom' => $editeurNom,
            'classes' => $classes
        ];

        $html = PdfService::renderTemplate('repertoire_classes.php', $data);
        $filename = 'Repertoire_Classes_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Répertoire des Classes - ' . ($etablissement['libelle_etablissement'] ?? 'GEICG'),
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 12
        ]);
    }

    public function imprimerListe($details)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::PRINT_CLASSES, PERMISSIONS::VIEW_CLASSES, PERMISSIONS::VIEW_ETUDIANTS, PERMISSIONS::MANAGE_CLASSES]);
        try {
            $id = $this->validator->decrypter($details);
            if (empty($id)) $id = $details;
            $item = $this->model->getById($id);
            if (!$item) {
                $this->renderNotFound("Classe introuvable.");
                return;
            }
            $classeCode = $item['code_classe'] ?? '';
            $anneeCode = $item['annee_code'] ?? $this->getActiveAnneeCode();

            header('Location: ' . RACINE . 'etudiant/imprimerPdf?annee_code=' . urlencode($anneeCode) . '&classe_code=' . urlencode($classeCode));
            exit;
        } catch (Exception $e) {
            $this->renderNotFound("Erreur lors de l'accès à la classe.");
        }
    }
}