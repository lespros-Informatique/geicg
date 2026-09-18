<?php

class CompositionController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelComposition();
    }

    public function list()
    {
        $this->requireAuth();
        $annees = $this->getAccessibleAnnees();
        $niveaux = (new ModelNiveau())->getAll();
        $classes = (new ModelClasse())->getAll();
        $matieres = (new ModelMatiere())->getAll();
        
        $selectedAnneeCode = $_GET['annee_code'] ?? ($_SESSION['annee_active_code'] ?? null);

        $this->loadView('../views/compositions/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'matieres' => $matieres,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $anneeCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $typeComposition = $_GET['type_composition'] ?? null;

        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode, $typeComposition);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_composition'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $selectedClasseCode = $_GET['classe_code'] ?? '';
        $selectedMatiereCode = $_GET['matiere_code'] ?? '';
        $typeEval = $_GET['type'] ?? 'COMPOSITION';

        $activeAnneeCode = $this->getActiveAnneeCode();
        $annees = (new ModelAnnee())->getAll();
        $niveaux = (new ModelNiveau())->getAll();
        $filieres = (new ModelFiliere())->getAll();
        $classes = (new ModelClasse())->getAll();
        $matieres = (new ModelMatiere())->getAll();
        $semestres = (new ModelSemestre())->getAll($activeAnneeCode);
        $salles = (new ModelSalle())->getAll();

        $teachersWithUsers = $this->model->getCon()->query("
            SELECT e.code_enseignant, CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS nom_complet
            FROM enseignants e
            JOIN users u ON u.code_user = e.code_enseignant
            ORDER BY u.nom_user ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $edtActiveTargets = $this->model->getEdtActiveTargets($activeAnneeCode);

        $this->loadView('../views/compositions/edit.php', [
            'item' => [],
            'annees' => $annees,
            'niveaux' => $niveaux,
            'filieres' => $filieres,
            'classes' => $classes,
            'matieres' => $matieres,
            'semestres' => $semestres,
            'salles' => $salles,
            'teachersWithUsers' => $teachersWithUsers,
            'activeAnneeCode' => $activeAnneeCode,
            'edtActiveTargets' => $edtActiveTargets,
            'selectedClasseCode' => $selectedClasseCode,
            'selectedMatiereCode' => $selectedMatiereCode,
            'selectedTypeEval' => $typeEval
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = !empty($_POST['annee_code']) ? $_POST['annee_code'] : $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode
        ]);

        $data = $_POST;
        unset($data['csrf_token']);

        $semestres = (new ModelSemestre())->getAll();
        if (empty($data['semestre_code'])) $data['semestre_code'] = !empty($semestres) ? $semestres[0]['code_semestre'] : 'SEM-1';

        $data['statut_composition'] = $data['statut_composition'] ?? 'programme';
        $data['created_at_composition'] = date('Y-m-d H:i:s');
        
        $cols = $this->model->getCon()->query("DESCRIBE compositions")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;

        if (empty($data['code_composition'])) {
            $data['code_composition'] = $this->validator->generateCode('compositions', 'code_composition', 'CMP-', 8);
        }

        // Resolve target items (niveau, filiere)
        $targetItems = [];
        if (!empty($_POST['niveaux']) && is_array($_POST['niveaux'])) {
            foreach ($_POST['niveaux'] as $row) {
                $nivCode = $row['niveau_code'] ?? '';
                $filCodes = $row['filiere_codes'] ?? [];
                if (is_string($filCodes)) $filCodes = array_filter(explode(',', $filCodes));
                if (empty($nivCode) || empty($filCodes)) continue;

                foreach ($filCodes as $fCode) {
                    $targetItems[] = [
                        'niveau_code' => $nivCode,
                        'filiere_code' => $fCode
                    ];
                }
            }
        }

        if (empty($targetItems)) {
            $this->error('Veuillez sélectionner au moins un niveau et une filière cible.');
            return;
        }

        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->model->saveTargetClasses($data['code_composition'], $targetItems);
            $this->success('Composition / Examen programmé avec succès !', ['redirect' => RACINE . 'composition/list']);
        } else {
            $this->error('Erreur lors de la programmation de la composition');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_composition');
        if (!$id) { $this->error('Identifiant invalide'); return; }

        $item = $this->model->getById($id);
        if (!$item) { $this->error('Composition introuvable'); return; }

        $data = $_POST;
        unset($data['csrf_token']);

        $cols = $this->model->getCon()->query("DESCRIBE compositions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        $filteredData['updated_at_composition'] = date('Y-m-d H:i:s');

        // Resolve target items (niveau, filiere)
        $targetItems = [];
        if (!empty($_POST['niveaux']) && is_array($_POST['niveaux'])) {
            foreach ($_POST['niveaux'] as $row) {
                $nivCode = $row['niveau_code'] ?? '';
                $filCodes = $row['filiere_codes'] ?? [];
                if (is_string($filCodes)) $filCodes = array_filter(explode(',', $filCodes));
                if (empty($nivCode) || empty($filCodes)) continue;

                foreach ($filCodes as $fCode) {
                    $targetItems[] = [
                        'niveau_code' => $nivCode,
                        'filiere_code' => $fCode
                    ];
                }
            }
        }

        if ($this->model->update($filteredData, $id)) {
            if (!empty($targetItems)) {
                $this->model->saveTargetClasses($item['code_composition'], $targetItems);
            }
            $this->success('Programmation de la composition mise à jour avec succès !', ['redirect' => RACINE . 'composition/list']);
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'composition/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'composition/list'); exit();
        }

        $activeAnneeCode = $this->getActiveAnneeCode();
        $anneeCodeToUse = !empty($item['annee_code']) ? $item['annee_code'] : $activeAnneeCode;

        $annees = (new ModelAnnee())->getAll();
        $niveaux = (new ModelNiveau())->getAll();
        $filieres = (new ModelFiliere())->getAll();
        $classes = (new ModelClasse())->getAll();
        $matieres = (new ModelMatiere())->getAll();
        $semestres = (new ModelSemestre())->getAll($anneeCodeToUse);
        $salles = (new ModelSalle())->getAll();

        $teachersWithUsers = $this->model->getCon()->query("
            SELECT e.code_enseignant, CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS nom_complet
            FROM enseignants e
            JOIN users u ON u.code_user = e.code_enseignant
            ORDER BY u.nom_user ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Extraire les niveaux et filières existants ciblés par la composition
        $targetClasses = $this->model->getTargetClasses($item['code_composition']);
        $existingNiveaux = [];
        $grouped = [];
        foreach ($targetClasses as $tc) {
            $nCode = $tc['niveau_code'] ?? '';
            $fCode = $tc['filiere_code'] ?? '';
            if ($nCode) {
                if (!isset($grouped[$nCode])) {
                    $grouped[$nCode] = [];
                }
                if ($fCode && !in_array($fCode, $grouped[$nCode])) {
                    $grouped[$nCode][] = $fCode;
                }
            }
        }
        foreach ($grouped as $nCode => $fCodes) {
            $existingNiveaux[] = [
                'niveau_code' => $nCode,
                'filiere_codes' => $fCodes
            ];
        }

        $edtActiveTargets = $this->model->getEdtActiveTargets($anneeCodeToUse);

        $this->loadView('../views/compositions/edit.php', [
            'item' => $item, 
            'encryptedId' => $encryptedId,
            'annees' => $annees,
            'niveaux' => $niveaux,
            'filieres' => $filieres,
            'classes' => $classes,
            'matieres' => $matieres,
            'semestres' => $semestres,
            'salles' => $salles,
            'teachersWithUsers' => $teachersWithUsers,
            'activeAnneeCode' => $activeAnneeCode,
            'edtActiveTargets' => $edtActiveTargets,
            'existingNiveaux' => $existingNiveaux
        ]);
    }

    public function delete()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $rawId = $_POST['id'] ?? ($_GET['id'] ?? $this->post('id'));
        $id = (int)$rawId;

        if ($id > 0) {
            $item = $this->model->getById($id);
            if ($item) {
                if ($this->model->delete($id)) {
                    $this->success('Épreuve / Composition supprimée du calendrier avec succès !');
                    return;
                }
            }
        }
        $this->error('Composition introuvable');
    }

    public function getByClasseMatiereApi()
    {
        $this->requireAuth();
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        $matiereCode = trim($_GET['matiere_code'] ?? ($_POST['matiere_code'] ?? ''));
        $semestreCode = trim($_GET['semestre_code'] ?? ($_POST['semestre_code'] ?? ''));
        $typeEval = trim($_GET['type_evaluation_code'] ?? ($_POST['type_evaluation_code'] ?? ''));

        if (empty($classeCode) || empty($matiereCode)) {
            $this->json(['status' => 1, 'data' => []]);
            return;
        }

        $items = $this->model->getByClasseMatiere($classeCode, $matiereCode, $semestreCode, $typeEval);
        $this->json(['status' => 1, 'data' => $items]);
    }

    public function getMatieresClasseApi()
    {
        $this->requireAuth();
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        $compositionCode = trim($_GET['composition_code'] ?? ($_POST['composition_code'] ?? ''));
        $compositionNiveauFiliereCode = trim($_GET['composition_niveau_filiere_code'] ?? ($_POST['composition_niveau_filiere_code'] ?? ($_GET['composition_cible_code'] ?? ($_POST['composition_cible_code'] ?? ''))));
        $anneeCode = $this->getActiveAnneeCode();

        if (empty($classeCode) && !empty($compositionNiveauFiliereCode)) {
            $stmtCible = $this->model->getCon()->prepare("SELECT niveau_code, filiere_code FROM composition_niveau_filiere WHERE code_composition_niveau_filiere = ?");
            $stmtCible->execute([$compositionNiveauFiliereCode]);
            $targetRow = $stmtCible->fetch(PDO::FETCH_ASSOC);
            if ($targetRow) {
                $stmtCl = $this->model->getCon()->prepare("SELECT code_classe FROM classes WHERE niveau_code = ? AND filiere_code = ? LIMIT 1");
                $stmtCl->execute([$targetRow['niveau_code'], $targetRow['filiere_code']]);
                $cRow = $stmtCl->fetch(PDO::FETCH_ASSOC);
                if ($cRow) {
                    $classeCode = $cRow['code_classe'];
                }
            }
        }

        if (empty($classeCode)) {
            $this->json(['status' => 1, 'data' => [], 'saved_codes' => [], 'source' => 'none']);
            return;
        }

        $db = $this->model->getCon();

        // Chercher les matières déjà enregistrées pour cette cible dans composition_matieres
        $savedCodes = [];
        if (!empty($compositionCode) && !empty($compositionNiveauFiliereCode)) {
            $saved = $this->model->getCompositionMatieres($compositionCode, $compositionNiveauFiliereCode);
            foreach ($saved as $s) {
                $savedCodes[] = $s['matiere_code'];
            }
        }

        // 1. Matières enseignées dans cette classe selon l'emploi du temps de l'année en session
        $stmtEdt = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ') AS prof_nom
            FROM emplois_temps edt
            INNER JOIN matieres m ON m.code_matiere = edt.matiere_code
            LEFT JOIN enseignant_matiere em ON (em.classe_code = edt.classe_code AND em.matiere_code = edt.matiere_code)
            LEFT JOIN enseignants e ON e.code_enseignant = em.enseignant_code
            LEFT JOIN users u ON u.code_user = e.code_enseignant
            WHERE edt.classe_code = ? AND (edt.annee_code = ? OR ? = '')
            GROUP BY m.code_matiere, m.libelle_matiere
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtEdt->execute([$classeCode, $anneeCode, $anneeCode]);
        $items = $stmtEdt->fetchAll(PDO::FETCH_ASSOC);

        $source = 'emploi_temps';

        // 2. Fallback sur enseignant_matiere si l'emploi du temps n'est pas encore configuré
        if (empty($items)) {
            $stmtEm = $db->prepare("
                SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                       GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ') AS prof_nom
                FROM enseignant_matiere em
                INNER JOIN matieres m ON m.code_matiere = em.matiere_code
                LEFT JOIN enseignants e ON e.code_enseignant = em.enseignant_code
                LEFT JOIN users u ON u.code_user = e.code_enseignant
                WHERE em.classe_code = ?
                GROUP BY m.code_matiere, m.libelle_matiere
                ORDER BY m.libelle_matiere ASC
            ");
            $stmtEm->execute([$classeCode]);
            $items = $stmtEm->fetchAll(PDO::FETCH_ASSOC);
            $source = 'enseignant_matiere';
        }

        // 3. Fallback sur toutes les matières
        if (empty($items)) {
            $stmtAll = $db->query("SELECT code_matiere, libelle_matiere FROM matieres ORDER BY libelle_matiere ASC");
            $items = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
            $source = 'all';
        }

        $this->json(['status' => 1, 'data' => $items, 'saved_codes' => $savedCodes, 'source' => $source]);
    }

    public function saveMatieresClasseApi()
    {
        $this->requireAuth();
        $compositionCode = trim($_POST['composition_code'] ?? ($_GET['composition_code'] ?? ''));
        $compositionNiveauFiliereCode = trim($_POST['composition_niveau_filiere_code'] ?? ($_GET['composition_niveau_filiere_code'] ?? ($_POST['composition_cible_code'] ?? ($_GET['composition_cible_code'] ?? ''))));
        $matiereCodes = $_POST['matiere_codes'] ?? ($_GET['matiere_codes'] ?? []);

        if (is_string($matiereCodes)) {
            $matiereCodes = array_filter(explode(',', $matiereCodes));
        }

        if (empty($compositionCode) || empty($compositionNiveauFiliereCode)) {
            $this->json(['status' => 0, 'message' => 'Composition et cible sont obligatoires']);
            return;
        }

        if ($this->model->saveCompositionMatieres($compositionCode, $compositionNiveauFiliereCode, $matiereCodes)) {
            $this->json(['status' => 1, 'message' => 'Matières enregistrées avec succès dans composition_matieres !']);
        } else {
            $this->json(['status' => 0, 'message' => 'Erreur lors de l\'enregistrement']);
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'composition/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'composition/list'); exit();
        }

        $semestre = (new ModelSemestre())->getByCode($item['semestre_code'] ?? '');
        $annee = (new ModelAnnee())->getByCode($item['annee_code'] ?? '');
        $targetClasses = $this->model->getTargetClasses($item['code_composition']);
        $matieres = (new ModelMatiere())->getAll();

        $this->loadView('../views/compositions/details.php', [
            'item' => $item,
            'encryptedId' => $encryptedId,
            'semestre' => $semestre,
            'annee' => $annee,
            'targetClasses' => $targetClasses,
            'matieres' => $matieres
        ]);
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Composition introuvable');
        }
    }
}

