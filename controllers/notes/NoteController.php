<?php

class NoteController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelNote();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_NOTES');
        $annees = $this->getAccessibleAnnees();
        $niveaux = (new ModelNiveau())->getAll();
        $classes = (new ModelClasse())->getAll();
        
        if (isset($_GET['annee_code']) && !empty($_GET['annee_code'])) {
            $selectedAnneeCode = trim($_GET['annee_code']);
            foreach ($annees as $a) {
                if ($a['code_annee'] === $selectedAnneeCode) {
                    Session::set('annee_active_code', $a['code_annee']);
                    Session::set('annee_active_libelle', $a['libelle_annee']);
                    break;
                }
            }
        } else {
            $selectedAnneeCode = Session::get('annee_active_code');
        }

        $this->loadView('../views/notes/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_NOTES');
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_note'];
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
        $this->requirePermission('ENTER_NOTES');
        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        $db = $this->model->getCon();
        $inscCode = trim($data['inscription_code'] ?? '');
        $etudiantCode = trim($data['etudiant_code'] ?? '');
        if (empty($etudiantCode) && !empty($inscCode)) {
            $etudiantCode = $this->model->getEtudiantCodeByInscription($inscCode) ?: '';
        }
        $data['etudiant_code'] = $etudiantCode;

        $compositionCode = trim($data['composition_code'] ?? '');
        $typeEval = trim($data['type_evaluation_code'] ?? 'INTERROGATION');
        $matiereCode = trim($data['matiere_code'] ?? '');
        $semestreCode = trim($data['semestre_code'] ?? '');
        $classeCode = trim($data['classe_code'] ?? '');

        if (empty($compositionCode) && !empty($matiereCode)) {
            $compositionCode = $this->model->createQuickComposition(
                $typeEval,
                $semestreCode,
                $classeCode,
                $matiereCode,
                1.00,
                null,
                $anneeCode,
                $etabCode,
                $userCode
            );
        }
        $data['composition_code'] = $compositionCode;

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'inscription_code' => $inscCode,
            'composition_code' => $compositionCode,
            'etudiant_code' => $etudiantCode
        ]);

        if (empty($data['code_note'])) {
            $data['code_note'] = $this->validator->generateCode('notes', 'code_note', 'NOT-', 8);
        }
        $data['statut_note'] = $data['statut_note'] ?? 'actif';
        $data['created_at_note'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $this->success('Note enregistrée avec succès!');
        } else {
            $this->error($this->model->getLastError() ?: 'Erreur lors de l\'enregistrement de la note');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('ENTER_NOTES');
        $id = (int)$this->post('id_note');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $filteredData = $this->model->filterData($data);
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
        $this->requirePermission('VIEW_NOTES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getByIdWithDetails($id);
            if (!$item) { header('Location: ' . RACINE . 'note/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("NoteController::details error: " . $e->getMessage());
            $this->renderNotFound("L'évaluation / note demandée est introuvable.");
            return;
        }
        $this->loadView('../views/notes/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission('ENTER_NOTES');
        $anneeCode = $this->getActiveAnneeCode();
        $anneeItem = (new ModelAnnee())->getByCode($anneeCode);
        $anneeLibelle = $anneeItem['libelle_annee'] ?? $anneeCode;
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getByIdWithDetails($id);
            if (!$item) { header('Location: ' . RACINE . 'note/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'note/list'); exit();
        }
        $deps = $this->model->getFormDataDependencies($anneeCode);
        $this->loadView('../views/notes/edit.php', array_merge([
            'item' => $item, 
            'encryptedId' => $encryptedId,
            'anneeCode' => $anneeCode,
            'anneeLibelle' => $anneeLibelle
        ], $deps));
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission('ENTER_NOTES');
        $anneeCode = $this->getActiveAnneeCode();
        $anneeItem = (new ModelAnnee())->getByCode($anneeCode);
        $anneeLibelle = $anneeItem['libelle_annee'] ?? $anneeCode;
        $selectedClasseCode = $_GET['classe_code'] ?? Session::get('last_note_classe_code', '');
        $deps = $this->model->getFormDataDependencies($anneeCode);
        $this->loadView('../views/notes/edit.php', array_merge([
            'item' => [],
            'selectedClasseCode' => $selectedClasseCode,
            'anneeCode' => $anneeCode,
            'anneeLibelle' => $anneeLibelle
        ], $deps));
    }

    protected function getMatieresForClasse(string $classeCode, string $anneeCode, string $compositionCode = ''): array
    {
        return $this->model->getMatieresForClasse($classeCode, $anneeCode, $compositionCode);
    }

    public function getMatieresByClasseApi()
    {
        $this->requireAuth();
        $this->requirePermission('ENTER_NOTES');
        $classeCode = trim($_POST['classe_code'] ?? ($_GET['classe_code'] ?? ''));
        $compositionCode = trim($_POST['composition_code'] ?? ($_GET['composition_code'] ?? ''));
        $anneeCode = $this->getActiveAnneeCode();

        if (empty($classeCode)) {
            $this->json(['status' => 1, 'data' => []]);
            return;
        }

        try {
            $items = $this->getMatieresForClasse($classeCode, $anneeCode, $compositionCode);
            $this->json(['status' => 1, 'data' => $items]);
        } catch (\Throwable $e) {
            error_log("NoteController::getMatieresByClasseApi error: " . $e->getMessage());
            // Fallback robuste vers les matières actives en cas d'erreur
            try {
                $items = $this->model->getActiveMatieresCatalogue();
                $this->json(['status' => 1, 'data' => $items]);
            } catch (\Throwable $fallbackErr) {
                $this->json(['status' => 0, 'data' => [], 'message' => $e->getMessage()]);
            }
        }
    }

    public function getStudentsByClasse()
    {
        $this->requireAuth();
        $this->requirePermission('ENTER_NOTES');
        $classeCode = trim($_POST['classe_code'] ?? ($_GET['classe_code'] ?? ''));
        $anneeCode = $this->getActiveAnneeCode();

        if (empty($classeCode)) {
            $this->json(['status' => 1, 'data' => []]);
            return;
        }

        $etudiants = $this->model->getStudentsByClasse($classeCode, $anneeCode);
        $this->json(['status' => 1, 'data' => $etudiants]);
    }

    public function saisieClasse()
    {
        $this->requireAuth();
        $this->requirePermission('ENTER_NOTES');
        $anneeCode = $this->getActiveAnneeCode();
        $anneeItem = (new ModelAnnee())->getByCode($anneeCode);
        $anneeLibelle = $anneeItem['libelle_annee'] ?? $anneeCode;

        $classes = (new ModelClasse())->getAll();
        $semestres = (new ModelSemestre())->getAll($anneeCode);
        
        $selectedClasseCode = $_GET['classe_code'] ?? '';
        $selectedMatiereCode = $_GET['matiere_code'] ?? '';
        $selectedSemestreCode = $_GET['semestre_code'] ?? ($_GET['semestre_code_hidden'] ?? '');
        $selectedTypeEval = $_GET['type_evaluation_code'] ?? '';
        $selectedCompositionCode = $_GET['composition_code'] ?? '';
        $selectedLibelleEval = $_GET['libelle_eval'] ?? '';
        $selectedCoefEval = $_GET['coefficient'] ?? '';

        $etudiants = [];
        $existingNotes = [];
        $compositionsProgrammees = [];

        $compModel = new ModelComposition();
        $tousExamens = $compModel->getAll($anneeCode, null, null, 'EXAMEN');
        foreach ($tousExamens as &$exam) {
            $targetCls = $compModel->getTargetClasses($exam['code_composition']);
            $exam['target_classes'] = array_values(array_filter(array_map(function($tc) {
                return !empty($tc['code_classe']) ? [
                    'code_classe' => $tc['code_classe'],
                    'libelle_classe' => $tc['libelle_classe']
                ] : null;
            }, $targetCls)));
        }
        unset($exam);

        if (!empty($selectedCompositionCode)) {
            $selectedCompItem = $compModel->getByCode($selectedCompositionCode);
            if ($selectedCompItem) {
                if (empty($selectedLibelleEval)) $selectedLibelleEval = $selectedCompItem['libelle_composition'];
                if (empty($selectedCoefEval)) $selectedCoefEval = (string)$selectedCompItem['coefficient'];
                if (empty($selectedSemestreCode)) $selectedSemestreCode = $selectedCompItem['semestre_code'];
                if (empty($selectedTypeEval)) $selectedTypeEval = $selectedCompItem['type_composition'] ?? 'EXAMEN';
            }
        } else if (!empty($selectedClasseCode) && !empty($selectedMatiereCode) && !empty($selectedSemestreCode) && !empty($selectedTypeEval) && $selectedTypeEval !== 'EXAMEN') {
            $existingComp = $compModel->findExistingComposition($selectedClasseCode, $selectedMatiereCode, $selectedSemestreCode, $selectedTypeEval, $anneeCode);
            if ($existingComp) {
                $selectedCompositionCode = $existingComp['code_composition'];
                if (empty($selectedLibelleEval)) $selectedLibelleEval = $existingComp['libelle_composition'];
                if (empty($selectedCoefEval)) $selectedCoefEval = (string)$existingComp['coefficient'];
            }
        }

        if (!empty($selectedClasseCode)) {
            $matieres = $this->getMatieresForClasse($selectedClasseCode, $anneeCode, $selectedCompositionCode);

            // Déduire automatiquement le coefficient si non spécifié
            if (empty($selectedCoefEval) && !empty($selectedMatiereCode)) {
                foreach ($matieres as $m) {
                    if ($m['code_matiere'] === $selectedMatiereCode && isset($m['coefficient'])) {
                        $selectedCoefEval = (string)$m['coefficient'];
                        break;
                    }
                }
            }

            $etudiants = $this->model->getStudentsByClasse($selectedClasseCode, $anneeCode);

            if (!empty($selectedMatiereCode)) {
                $compositionsProgrammees = $compModel->getByClasseMatiere($selectedClasseCode, $selectedMatiereCode, $selectedSemestreCode);

                if (!empty($selectedCompositionCode)) {
                    $existingNotes = $this->model->getNotesByComposition($selectedCompositionCode);
                }
            }
        } else {
            $matieres = [];
        }

        $this->loadView('../views/notes/saisie_classe.php', [
            'anneeCode' => $anneeCode,
            'anneeLibelle' => $anneeLibelle,
            'classes' => $classes,
            'matieres' => $matieres,
            'semestres' => $semestres,
            'selectedClasseCode' => $selectedClasseCode,
            'selectedMatiereCode' => $selectedMatiereCode,
            'selectedSemestreCode' => $selectedSemestreCode,
            'selectedTypeEval' => $selectedTypeEval,
            'selectedCompositionCode' => $selectedCompositionCode,
            'selectedLibelleEval' => $selectedLibelleEval,
            'selectedCoefEval' => $selectedCoefEval,
            'compositionsProgrammees' => $compositionsProgrammees,
            'tousExamens' => $tousExamens,
            'etudiants' => $etudiants,
            'existingNotes' => $existingNotes
        ]);
    }

    /**
     * Endpoint AJAX pour charger dynamiquement la grille des notes et KPIs
     * sans rechargement de page GET
     */
    public function getGrilleAjax()
    {
        $this->requireAuth();
        $this->requirePermission('ENTER_NOTES');

        $anneeCode = $this->getActiveAnneeCode();
        $anneeItem = (new ModelAnnee())->getByCode($anneeCode);
        $anneeLibelle = $anneeItem['libelle_annee'] ?? $anneeCode;

        $selectedClasseCode = trim($_POST['classe_code'] ?? ($_GET['classe_code'] ?? ''));
        $selectedMatiereCode = trim($_POST['matiere_code'] ?? ($_GET['matiere_code'] ?? ''));
        $selectedSemestreCode = trim($_POST['semestre_code'] ?? ($_GET['semestre_code'] ?? ''));
        $selectedTypeEval = trim($_POST['type_evaluation_code'] ?? ($_GET['type_evaluation_code'] ?? ''));
        $selectedCompositionCode = trim($_POST['composition_code'] ?? ($_GET['composition_code'] ?? ''));
        $selectedLibelleEval = trim($_POST['libelle_eval'] ?? ($_GET['libelle_eval'] ?? ''));
        $selectedCoefEval = trim($_POST['coefficient'] ?? ($_GET['coefficient'] ?? ''));

        if (empty($selectedClasseCode) || empty($selectedMatiereCode) || empty($selectedSemestreCode)) {
            echo '<div style="padding: 24px; text-align: center; color: #64748B; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1; margin: 20px 0;">'
                . '<i data-lucide="info" style="width: 28px; height: 28px; color: #3B82F6; margin-bottom: 6px;"></i>'
                . '<p style="margin: 0; font-weight: 700; font-size: 14px;">Veuillez renseigner le Semestre, la Classe et la Matière pour afficher la grille des notes.</p>'
                . '</div>';
            return;
        }

        $compModel = new ModelComposition();

        if (!empty($selectedCompositionCode)) {
            $selectedCompItem = $compModel->getByCode($selectedCompositionCode);
            if ($selectedCompItem) {
                if (empty($selectedLibelleEval)) $selectedLibelleEval = $selectedCompItem['libelle_composition'];
                if (empty($selectedCoefEval)) $selectedCoefEval = (string)$selectedCompItem['coefficient'];
                if (empty($selectedSemestreCode)) $selectedSemestreCode = $selectedCompItem['semestre_code'];
                if (empty($selectedTypeEval)) $selectedTypeEval = $selectedCompItem['type_composition'] ?? 'EXAMEN';
            }
        } else if (!empty($selectedClasseCode) && !empty($selectedMatiereCode) && !empty($selectedSemestreCode) && !empty($selectedTypeEval) && $selectedTypeEval !== 'EXAMEN') {
            $existingComp = $compModel->findExistingComposition($selectedClasseCode, $selectedMatiereCode, $selectedSemestreCode, $selectedTypeEval, $anneeCode);
            if ($existingComp) {
                $selectedCompositionCode = $existingComp['code_composition'];
                if (empty($selectedLibelleEval)) $selectedLibelleEval = $existingComp['libelle_composition'];
                if (empty($selectedCoefEval)) $selectedCoefEval = (string)$existingComp['coefficient'];
            }
        }

        $classes = (new ModelClasse())->getAll();
        $matieres = $this->getMatieresForClasse($selectedClasseCode, $anneeCode, $selectedCompositionCode);
        $semestres = (new ModelSemestre())->getAll($anneeCode);

        // Déduire le coefficient si non spécifié
        if (empty($selectedCoefEval) && !empty($selectedMatiereCode)) {
            foreach ($matieres as $m) {
                if ($m['code_matiere'] === $selectedMatiereCode && isset($m['coefficient'])) {
                    $selectedCoefEval = (string)$m['coefficient'];
                    break;
                }
            }
        }

        // Récupérer les étudiants de la classe
        $etudiants = $this->model->getStudentsByClasse($selectedClasseCode, $anneeCode);

        // Récupérer les notes existantes
        $existingNotes = [];
        if (!empty($selectedCompositionCode)) {
            $existingNotes = $this->model->getNotesByComposition($selectedCompositionCode);
        }

        $typeEvals = [
            'EXAMEN' => 'Examen / Épreuve',
            'DEVOIR' => 'Devoir Surveillé',
            'INTERROGATION' => 'Interrogation Écrite',
            'AUTRE' => 'Autre Évaluation'
        ];

        include __DIR__ . '/../../views/notes/partials/grille_notes_table.php';
    }

    public function saveBatch()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('ENTER_NOTES');

        $csrf = $this->post('csrf_token');
        if (!Validator::validateCsrfToken($csrf)) {
            $this->error('Jeton CSRF invalide ou session expirée. Veuillez recharger la page.');
            return;
        }

        $classeCode = $this->post('classe_code');
        $matiereCode = $this->post('matiere_code');
        $semestreCode = $this->post('semestre_code');
        $typeEval = $this->post('type_evaluation_code') ?? 'INTERROGATION';
        $compositionCode = $this->post('composition_code') ?: null;
        $libelleEval = trim($this->post('libelle_eval') ?? '');
        $coefEval = $this->post('coefficient') ? (float)$this->post('coefficient') : 1.00;
        $notesData = $_POST['notes'] ?? [];

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $validFk = $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'classe_code' => $classeCode,
            'matiere_code' => $matiereCode,
            'semestre_code' => $semestreCode
        ], null, [
            'classe_code' => 'Classe',
            'matiere_code' => 'Matière',
            'semestre_code' => 'Semestre'
        ]);
        if (!$validFk) {
            return;
        }

        try {
            $result = $this->model->saveBatchNotes(
                $classeCode,
                $matiereCode,
                $semestreCode,
                $typeEval,
                $compositionCode,
                $libelleEval,
                $coefEval,
                $notesData,
                $userCode,
                $anneeCode,
                $etabCode
            );

            $savedCount = $result['saved_count'];
            $deletedCount = $result['deleted_count'];
            $compCode = $result['composition_code'];

            $msg = "$savedCount note(s) enregistrée(s) avec succès !";
            if ($deletedCount > 0) {
                $msg .= " ($deletedCount note(s) effacée(s))";
            }
            $this->success($msg, [
                'saved_count' => $savedCount, 
                'deleted_count' => $deletedCount,
                'composition_code' => $compCode
            ]);
        } catch (Exception $e) {
            error_log("NoteController::saveBatch error: " . $e->getMessage());
            $this->error('Erreur lors de l\'enregistrement des notes : ' . $e->getMessage());
        }
    }

}
