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
                    $_SESSION['annee_active_code'] = $a['code_annee'];
                    $_SESSION['annee_active_libelle'] = $a['libelle_annee'];
                    break;
                }
            }
        } else {
            $selectedAnneeCode = $_SESSION['annee_active_code'] ?? null;
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
        $anneeCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
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
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'inscription_code' => $data['inscription_code'] ?? '',
            'matiere_code' => $data['matiere_code'] ?? '',
            'semestre_code' => $data['semestre_code'] ?? ''
        ]);

        if (empty($data['code_note'])) {
            $data['code_note'] = $this->validator->generateCode('notes', 'code_note', 'NOT-', 8);
        }
        $data['statut_note'] = $data['statut_note'] ?? 'actif';
        $data['created_at_note'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE notes")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
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
        $cols = $this->model->getCon()->query("DESCRIBE notes")->fetchAll(PDO::FETCH_COLUMN);
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
        $this->requirePermission('VIEW_NOTES');
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT n.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant,
                       m.libelle_matiere,
                       cl.libelle_classe, f.libelle_filiere, niv.libelle_niveau,
                       s.libelle_semestre, a.libelle_annee,
                       COALESCE(em.coefficient, 1.0) as coef_cours
                FROM notes n
                LEFT JOIN inscriptions ins ON ins.code_inscription = n.inscription_code
                LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux niv ON niv.code_niveau = cl.niveau_code
                LEFT JOIN matieres m ON m.code_matiere = n.matiere_code
                LEFT JOIN semestres s ON s.code_semestre = n.semestre_code
                LEFT JOIN annees a ON a.code_annee = ins.annee_code
                LEFT JOIN enseignant_matiere em ON (em.classe_code = ins.classe_code AND em.matiere_code = n.matiere_code)
                WHERE n.id_note = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
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
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'note/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'note/list'); exit();
        }
        $this->loadView('../views/notes/edit.php', [
            'item' => $item, 
            'encryptedId' => $encryptedId,
            'anneeCode' => $anneeCode,
            'anneeLibelle' => $anneeLibelle
        ]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission('ENTER_NOTES');
        $anneeCode = $this->getActiveAnneeCode();
        $anneeItem = (new ModelAnnee())->getByCode($anneeCode);
        $anneeLibelle = $anneeItem['libelle_annee'] ?? $anneeCode;
        $selectedClasseCode = $_GET['classe_code'] ?? ($_SESSION['last_note_classe_code'] ?? '');
        $this->loadView('../views/notes/edit.php', [
            'item' => [],
            'selectedClasseCode' => $selectedClasseCode,
            'anneeCode' => $anneeCode,
            'anneeLibelle' => $anneeLibelle
        ]);
    }


    /**
     * Récupère dynamiquement la liste des matières enseignées dans une classe donnée
     * Selon la hiérarchie :
     * 1. Matières de l'Examen spécifique (si composition_code renseigné)
     * 2. Matières enseignées à cette classe selon l'Emploi du temps (emplois_temps)
     * 3. Matières affectées à la classe via enseignant_matiere
     * 4. Matières liées au Niveau & Filière de la classe (composition_niveau_filiere)
     * 5. Repli : Catalogue des matières actives
     */
    protected function getMatieresForClasse(string $classeCode, string $anneeCode, string $compositionCode = ''): array
    {
        if (empty($classeCode)) return [];
        $db = $this->model->getCon();

        // 1. Si une composition/examen est spécifié et possède des matières pour cette classe
        if (!empty($compositionCode)) {
            $stmtCompMat = $db->prepare("
                SELECT DISTINCT m.code_matiere, m.libelle_matiere, 
                       COALESCE(em.coefficient, comp.coefficient, 1.00) AS coefficient,
                       TRIM(CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, ''))) AS prof_nom,
                       1 AS is_assigned
                FROM composition_matieres cm
                INNER JOIN compositions comp ON comp.code_composition = cm.composition_code
                INNER JOIN composition_niveau_filiere cnf ON cnf.code_composition_niveau_filiere = cm.composition_niveau_filiere_code
                INNER JOIN classes cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
                INNER JOIN matieres m ON m.code_matiere = cm.matiere_code
                LEFT JOIN enseignant_matiere em ON (em.matiere_code = m.code_matiere AND em.classe_code = cl.code_classe)
                LEFT JOIN emplois_temps edt ON (edt.classe_code = cl.code_classe AND edt.matiere_code = m.code_matiere)
                LEFT JOIN users u ON (u.code_user = em.enseignant_code OR u.code_user = edt.enseignant_code)
                WHERE cm.composition_code = ? AND cl.code_classe = ?
                GROUP BY m.code_matiere, m.libelle_matiere, coefficient, prof_nom
                ORDER BY m.libelle_matiere ASC
            ");
            $stmtCompMat->execute([$compositionCode, $classeCode]);
            $compItems = $stmtCompMat->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($compItems)) {
                return $compItems;
            }
        }

        // 2. Matières enseignées dans cette classe selon l'emploi du temps (emplois_temps)
        $stmtEdt = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   COALESCE(em.coefficient, 1.00) AS coefficient,
                   TRIM(GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ')) AS prof_nom,
                   1 AS is_assigned
            FROM emplois_temps edt
            INNER JOIN matieres m ON m.code_matiere = edt.matiere_code
            LEFT JOIN enseignant_matiere em ON (em.classe_code = edt.classe_code AND em.matiere_code = edt.matiere_code)
            LEFT JOIN users u ON (u.code_user = edt.enseignant_code OR u.code_user = em.enseignant_code)
            WHERE edt.classe_code = ? 
              AND (edt.annee_code = ? OR ? IS NULL OR ? = '')
              AND (edt.statut_emploi = 'actif' OR edt.statut_emploi IS NULL)
            GROUP BY m.code_matiere, m.libelle_matiere, em.coefficient
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtEdt->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        $edtItems = $stmtEdt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($edtItems)) {
            return $edtItems;
        }

        // 3. Matières affectées à la classe via enseignant_matiere
        $stmtEm = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   COALESCE(em.coefficient, 1.00) AS coefficient,
                   TRIM(GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ')) AS prof_nom,
                   1 AS is_assigned
            FROM enseignant_matiere em
            INNER JOIN matieres m ON m.code_matiere = em.matiere_code
            LEFT JOIN users u ON u.code_user = em.enseignant_code
            WHERE em.classe_code = ?
              AND (em.annee_code = ? OR ? IS NULL OR ? = '')
              AND (em.statut_enseignant_matiere = 'actif' OR em.statut_enseignant_matiere IS NULL)
            GROUP BY m.code_matiere, m.libelle_matiere, em.coefficient
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtEm->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        $emItems = $stmtEm->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($emItems)) {
            return $emItems;
        }

        // 4. Matières liées au Niveau & Filière de la classe (composition_niveau_filiere)
        $stmtCnf = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   COALESCE(comp.coefficient, 1.00) AS coefficient,
                   '' AS prof_nom,
                   1 AS is_assigned
            FROM composition_matieres cm
            INNER JOIN composition_niveau_filiere cnf ON cnf.code_composition_niveau_filiere = cm.composition_niveau_filiere_code
            INNER JOIN classes cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
            INNER JOIN matieres m ON m.code_matiere = cm.matiere_code
            INNER JOIN compositions comp ON comp.code_composition = cm.composition_code
            WHERE cl.code_classe = ?
              AND (comp.annee_code = ? OR ? IS NULL OR ? = '')
            GROUP BY m.code_matiere, m.libelle_matiere, comp.coefficient
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtCnf->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        $cnfItems = $stmtCnf->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($cnfItems)) {
            return $cnfItems;
        }

        // 5. Repli : Catalogue des matières actives
        $stmtAll = $db->query("
            SELECT code_matiere, libelle_matiere, 1.00 AS coefficient, '' AS prof_nom, 0 AS is_assigned
            FROM matieres 
            WHERE statut_matiere = 'actif'
            ORDER BY libelle_matiere ASC
        ");
        return $stmtAll->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
                $db = $this->model->getCon();
                $items = $db->query("SELECT code_matiere, libelle_matiere, 1.00 AS coefficient, '' AS prof_nom, 0 AS is_assigned FROM matieres WHERE statut_matiere = 'actif' ORDER BY libelle_matiere ASC")->fetchAll(PDO::FETCH_ASSOC);
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

        $db = $this->model->getCon();
        $stmt = $db->prepare("
            SELECT i.id_inscription, i.code_inscription, e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
            FROM inscriptions i
            INNER JOIN etudiants e ON e.code_etudiant = i.etudiant_code
            WHERE i.classe_code = ? AND (i.annee_code = ? OR ? IS NULL OR ? = '') AND (i.statut_inscription != 'annule' OR i.statut_inscription IS NULL)
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
        ");
        $stmt->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        
        $db = $this->model->getCon();
        $semestres = $db->query("SELECT * FROM semestres WHERE (annee_code = " . $db->quote($anneeCode) . " OR " . $db->quote($anneeCode) . " = '') ORDER BY id_semestre ASC")->fetchAll(PDO::FETCH_ASSOC);
        
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
            $stmtFindComp = $db->prepare("
                SELECT * FROM compositions 
                WHERE classe_code = ? AND matiere_code = ? AND semestre_code = ? AND type_composition = ? AND (annee_code = ? OR ? IS NULL OR ? = '')
                ORDER BY id_composition DESC LIMIT 1
            ");
            $stmtFindComp->execute([$selectedClasseCode, $selectedMatiereCode, $selectedSemestreCode, $selectedTypeEval, $anneeCode, $anneeCode, $anneeCode]);
            $existingComp = $stmtFindComp->fetch(PDO::FETCH_ASSOC);
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

            $stmt = $db->prepare("
                SELECT i.id_inscription, i.code_inscription, e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
                FROM inscriptions i
                INNER JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                WHERE i.classe_code = ? AND (i.annee_code = ? OR ? IS NULL OR ? = '') AND (i.statut_inscription != 'annule' OR i.statut_inscription IS NULL)
                ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
            ");
            $stmt->execute([$selectedClasseCode, $anneeCode, $anneeCode, $anneeCode]);
            $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($selectedMatiereCode)) {
                $compositionsProgrammees = $compModel->getByClasseMatiere($selectedClasseCode, $selectedMatiereCode, $selectedSemestreCode);

                if (!empty($selectedSemestreCode)) {
                    $sqlNotes = "
                        SELECT n.*, n.inscription_code 
                        FROM notes n
                        INNER JOIN inscriptions i ON i.code_inscription = n.inscription_code
                        WHERE i.classe_code = ? AND n.matiere_code = ? AND n.semestre_code = ? AND n.statut_note = 'actif'
                    ";
                    $paramsNotes = [$selectedClasseCode, $selectedMatiereCode, $selectedSemestreCode];

                    if (!empty($selectedCompositionCode)) {
                        $sqlNotes .= " AND n.composition_code = ?";
                        $paramsNotes[] = $selectedCompositionCode;
                    } else if (!empty($selectedTypeEval)) {
                        $sqlNotes .= " AND n.type_evaluation_code = ?";
                        $paramsNotes[] = $selectedTypeEval;
                    }

                    $stmtNotes = $db->prepare($sqlNotes);
                    $stmtNotes->execute($paramsNotes);
                    $rows = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        $existingNotes[$r['inscription_code']] = $r;
                    }
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

        $db = $this->model->getCon();
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
            $stmtFindComp = $db->prepare("
                SELECT * FROM compositions 
                WHERE classe_code = ? AND matiere_code = ? AND semestre_code = ? AND type_composition = ? AND (annee_code = ? OR ? IS NULL OR ? = '')
                ORDER BY id_composition DESC LIMIT 1
            ");
            $stmtFindComp->execute([$selectedClasseCode, $selectedMatiereCode, $selectedSemestreCode, $selectedTypeEval, $anneeCode, $anneeCode, $anneeCode]);
            $existingComp = $stmtFindComp->fetch(PDO::FETCH_ASSOC);
            if ($existingComp) {
                $selectedCompositionCode = $existingComp['code_composition'];
                if (empty($selectedLibelleEval)) $selectedLibelleEval = $existingComp['libelle_composition'];
                if (empty($selectedCoefEval)) $selectedCoefEval = (string)$existingComp['coefficient'];
            }
        }

        $classes = (new ModelClasse())->getAll();
        $matieres = $this->getMatieresForClasse($selectedClasseCode, $anneeCode, $selectedCompositionCode);
        $semestres = $db->query("SELECT * FROM semestres WHERE (annee_code = " . $db->quote($anneeCode) . " OR " . $db->quote($anneeCode) . " = '') ORDER BY id_semestre ASC")->fetchAll(PDO::FETCH_ASSOC);

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
        $stmt = $db->prepare("
            SELECT i.id_inscription, i.code_inscription, e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
            FROM inscriptions i
            INNER JOIN etudiants e ON e.code_etudiant = i.etudiant_code
            WHERE i.classe_code = ? AND (i.annee_code = ? OR ? IS NULL OR ? = '') AND (i.statut_inscription != 'annule' OR i.statut_inscription IS NULL)
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
        ");
        $stmt->execute([$selectedClasseCode, $anneeCode, $anneeCode, $anneeCode]);
        $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les notes existantes
        $existingNotes = [];
        $sqlNotes = "
            SELECT n.*, n.inscription_code 
            FROM notes n
            INNER JOIN inscriptions i ON i.code_inscription = n.inscription_code
            WHERE i.classe_code = ? AND n.matiere_code = ? AND n.semestre_code = ? AND n.statut_note = 'actif'
        ";
        $paramsNotes = [$selectedClasseCode, $selectedMatiereCode, $selectedSemestreCode];

        if (!empty($selectedCompositionCode)) {
            $sqlNotes .= " AND n.composition_code = ?";
            $paramsNotes[] = $selectedCompositionCode;
        } else if (!empty($selectedTypeEval)) {
            $sqlNotes .= " AND n.type_evaluation_code = ?";
            $paramsNotes[] = $selectedTypeEval;
        }

        $stmtNotes = $db->prepare($sqlNotes);
        $stmtNotes->execute($paramsNotes);
        $rows = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $existingNotes[$r['inscription_code']] = $r;
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

        $db = $this->model->getCon();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
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
            $db->beginTransaction();

            // Si le type est différent de 'EXAMEN', enregistrer directement les infos dans 'compositions'
            if ($typeEval !== 'EXAMEN') {
                if (empty($compositionCode)) {
                    $compositionCode = $this->validator->generateCode('compositions', 'code_composition', 'CMP-', 8);
                    $defaultLbl = ($typeEval === 'DEVOIR') ? 'Devoir Sur Table' : (($typeEval === 'INTERROGATION') ? 'Interrogation' : 'Évaluation');
                    $finalLibelle = !empty($libelleEval) ? $libelleEval : $defaultLbl;
                    $today = date('Y-m-d');

                    $stmtNewComp = $db->prepare("
                        INSERT INTO compositions (
                            code_composition, libelle_composition, type_composition, semestre_code, classe_code, matiere_code, coefficient,
                            date_composition, date_fin_composition, statut_composition, annee_code,
                            etablissement_code, user_code, created_at_composition
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'programme', ?, ?, ?, NOW())
                    ");
                    $stmtNewComp->execute([
                        $compositionCode,
                        $finalLibelle,
                        $typeEval,
                        $semestreCode,
                        $classeCode,
                        $matiereCode,
                        $coefEval,
                        $today,
                        $today,
                        $anneeCode,
                        $etabCode,
                        $userCode
                    ]);

                    // Associer également au niveau/filière pour rétrocompatibilité
                    $stmtCl = $db->prepare("SELECT niveau_code, filiere_code FROM classes WHERE code_classe = ?");
                    $stmtCl->execute([$classeCode]);
                    $clRow = $stmtCl->fetch(PDO::FETCH_ASSOC);

                    if ($clRow && !empty($clRow['niveau_code']) && !empty($clRow['filiere_code'])) {
                        $codeCNF = $this->validator->generateCode('composition_niveau_filiere', 'code_composition_niveau_filiere', 'CNF-', 8);
                        $stmtInsCNF = $db->prepare("
                            INSERT IGNORE INTO composition_niveau_filiere (code_composition_niveau_filiere, composition_code, niveau_code, filiere_code)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmtInsCNF->execute([$codeCNF, $compositionCode, $clRow['niveau_code'], $clRow['filiere_code']]);

                        $stmtInsCM = $db->prepare("
                            INSERT IGNORE INTO composition_matieres (composition_code, composition_niveau_filiere_code, matiere_code)
                            VALUES (?, ?, ?)
                        ");
                        $stmtInsCM->execute([$compositionCode, $codeCNF, $matiereCode]);
                    }
                } else {
                    // Mise à jour de l'évaluation existante dans 'compositions'
                    $stmtUpdComp = $db->prepare("
                        UPDATE compositions 
                        SET libelle_composition = COALESCE(NULLIF(?, ''), libelle_composition),
                            coefficient = ?,
                            classe_code = COALESCE(classe_code, ?),
                            matiere_code = COALESCE(matiere_code, ?),
                            updated_at_composition = NOW()
                        WHERE code_composition = ?
                    ");
                    $stmtUpdComp->execute([$libelleEval, $coefEval, $classeCode, $matiereCode, $compositionCode]);
                }
            }

            $savedCount = 0;
            $deletedCount = 0;

            foreach ($notesData as $inscCode => $info) {
                $valNoteStr = trim($info['valeur_note'] ?? '');
                $obs = trim($info['observations'] ?? '');

                // Vérifier s'il y a déjà une note existante pour cet étudiant
                if (!empty($compositionCode)) {
                    $stmtCheck = $db->prepare("
                        SELECT id_note FROM notes 
                        WHERE inscription_code = ? AND matiere_code = ? AND semestre_code = ? AND composition_code = ?
                    ");
                    $stmtCheck->execute([$inscCode, $matiereCode, $semestreCode, $compositionCode]);
                } else {
                    $stmtCheck = $db->prepare("
                        SELECT id_note FROM notes 
                        WHERE inscription_code = ? AND matiere_code = ? AND semestre_code = ? AND type_evaluation_code = ? AND (composition_code IS NULL OR composition_code = '')
                    ");
                    $stmtCheck->execute([$inscCode, $matiereCode, $semestreCode, $typeEval]);
                }

                $existingId = $stmtCheck->fetchColumn();

                // Gestion champ note vide
                if ($valNoteStr === '') {
                    // Si l'étudiant est explicitement noté Absent, on enregistre 0.00 avec observation ABSENT
                    if (!empty($obs) && stripos($obs, 'absent') !== false) {
                        $valNote = 0.00;
                    } else {
                        // Si une note existait auparavant et a été effacée par l'enseignant, la supprimer
                        if ($existingId) {
                            $stmtDel = $db->prepare("DELETE FROM notes WHERE id_note = ?");
                            $stmtDel->execute([$existingId]);
                            $deletedCount++;
                        }
                        continue;
                    }
                } else {
                    $valNote = (float)str_replace(',', '.', $valNoteStr);
                    if ($valNote < 0 || $valNote > 20) {
                        throw new Exception("La note doit être comprise entre 0 et 20.");
                    }
                }

                if ($existingId) {
                    $stmtUpd = $db->prepare("
                        UPDATE notes 
                        SET valeur_note = ?, observations = ?, type_evaluation_code = ?, composition_code = ?, updated_at_note = NOW(), statut_note = 'actif'
                        WHERE id_note = ?
                    ");
                    $stmtUpd->execute([$valNote, $obs, $typeEval, $compositionCode, $existingId]);
                } else {
                    $fkErr = ForeignKeyValidator::validate($db, 'notes', [
                        'inscription_code' => $inscCode,
                        'matiere_code' => $matiereCode,
                        'semestre_code' => $semestreCode,
                        'etablissement_code' => $etabCode
                    ]);
                    if ($fkErr !== null) {
                        $db->rollBack();
                        $this->error($fkErr);
                        return;
                    }

                    $codeNote = $this->validator->generateCode('notes', 'code_note', 'NOT-', 8);
                    $stmtIns = $db->prepare("
                        INSERT INTO notes (code_note, inscription_code, matiere_code, semestre_code, type_evaluation_code, composition_code, valeur_note, observations, statut_note, user_code, etablissement_code, created_at_note)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'actif', ?, ?, ?)
                    ");
                    $stmtIns->execute([$codeNote, $inscCode, $matiereCode, $semestreCode, $typeEval, $compositionCode, $valNote, $obs, $userCode, $etabCode, date('Y-m-d H:i:s')]);
                }
                $savedCount++;
            }

            $db->commit();
            $msg = "$savedCount note(s) enregistrée(s) avec succès !";
            if ($deletedCount > 0) {
                $msg .= " ($deletedCount note(s) effacée(s))";
            }
            $this->success($msg, [
                'saved_count' => $savedCount, 
                'deleted_count' => $deletedCount,
                'composition_code' => $compositionCode
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            error_log("NoteController::saveBatch error: " . $e->getMessage());
            $this->error('Erreur lors de l\'enregistrement des notes : ' . $e->getMessage());
        }
    }

}
