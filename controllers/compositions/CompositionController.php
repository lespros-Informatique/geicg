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
        $anneeModel = new ModelAnnee();
        $annees = $anneeModel->getAll();
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

        // Resolve target items (niveau, filiere, classe)
        $targetItems = [];
        if (!empty($_POST['niveaux']) && is_array($_POST['niveaux'])) {
            $allClasses = (new ModelClasse())->getAll();
            foreach ($_POST['niveaux'] as $row) {
                $nivCode = $row['niveau_code'] ?? '';
                $filCodes = $row['filiere_codes'] ?? [];
                if (is_string($filCodes)) $filCodes = array_filter(explode(',', $filCodes));
                if (empty($nivCode) || empty($filCodes)) continue;

                foreach ($allClasses as $c) {
                    if (($c['niveau_code'] ?? '') === $nivCode) {
                        if (in_array($c['filiere_code'] ?? '', $filCodes)) {
                            $targetItems[] = [
                                'niveau_code' => $c['niveau_code'],
                                'filiere_code' => $c['filiere_code'],
                                'classe_code' => $c['code_classe']
                            ];
                        }
                    }
                }
            }
        } elseif (!empty($data['classe_code'])) {
            $cItem = (new ModelClasse())->getByCode($data['classe_code']);
            $targetItems[] = [
                'niveau_code' => $cItem['niveau_code'] ?? null,
                'filiere_code' => $cItem['filiere_code'] ?? null,
                'classe_code' => $data['classe_code']
            ];
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

        // Resolve target items (niveau, filiere, classe)
        $targetItems = [];
        if (!empty($_POST['niveaux']) && is_array($_POST['niveaux'])) {
            $allClasses = (new ModelClasse())->getAll();
            foreach ($_POST['niveaux'] as $row) {
                $nivCode = $row['niveau_code'] ?? '';
                $filCodes = $row['filiere_codes'] ?? [];
                if (is_string($filCodes)) $filCodes = array_filter(explode(',', $filCodes));
                if (empty($nivCode) || empty($filCodes)) continue;

                foreach ($allClasses as $c) {
                    if (($c['niveau_code'] ?? '') === $nivCode) {
                        if (in_array($c['filiere_code'] ?? '', $filCodes)) {
                            $targetItems[] = [
                                'niveau_code' => $c['niveau_code'],
                                'filiere_code' => $c['filiere_code'],
                                'classe_code' => $c['code_classe']
                            ];
                        }
                    }
                }
            }
        } elseif (!empty($data['classe_code'])) {
            $cItem = (new ModelClasse())->getByCode($data['classe_code']);
            $targetItems[] = [
                'niveau_code' => $cItem['niveau_code'] ?? null,
                'filiere_code' => $cItem['filiere_code'] ?? null,
                'classe_code' => $data['classe_code']
            ];
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

        $this->loadView('../views/compositions/details.php', [
            'item' => $item,
            'encryptedId' => $encryptedId,
            'semestre' => $semestre,
            'annee' => $annee,
            'targetClasses' => $targetClasses
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

