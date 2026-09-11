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
        $anneeModel = new ModelAnnee();
        $annees = $anneeModel->getAll();
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
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);
        if (empty($data['code_note'])) {
            $data['code_note'] = $this->validator->generateCode('notes', 'code_note', 'NOT-', 8);
        }
        $data['statut_note'] = $data['statut_note'] ?? 'actif';
        $data['created_at_note'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE notes")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Item créé avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
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
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT n.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant,
                       m.libelle_matiere,
                       cl.libelle_classe, f.libelle_filiere, niv.libelle_niveau,
                       s.libelle_semestre, a.libelle_annee,
                       COALESCE(em.coefficient_enseignant_matiere, em.coefficient, 1.0) as coef_cours
                FROM notes n
                LEFT JOIN inscriptions ins ON ins.code_inscription = n.inscription_code
                LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux niv ON niv.code_niveau = cl.niveau_code
                LEFT JOIN matieres m ON m.code_matiere = n.matiere_code
                LEFT JOIN semestres s ON s.code_semestre = n.semestre_code
                LEFT JOIN annees a ON a.code_annee = n.annee_code
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
        }
        $this->loadView('../views/notes/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
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


    public function saisieClasse()
    {
        $this->requireAuth();
        $anneeCode = $this->getActiveAnneeCode();
        $anneeItem = (new ModelAnnee())->getByCode($anneeCode);
        $anneeLibelle = $anneeItem['libelle_annee'] ?? $anneeCode;

        $classes = (new ModelClasse())->getAll();
        $matieres = (new ModelMatiere())->getAll();
        
        $db = $this->model->getCon();
        $semestres = $db->query("SELECT * FROM semestres WHERE (annee_code = " . $db->quote($anneeCode) . " OR " . $db->quote($anneeCode) . " = '') ORDER BY id_semestre ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        $selectedClasseCode = $_GET['classe_code'] ?? '';
        $selectedMatiereCode = $_GET['matiere_code'] ?? '';
        $selectedSemestreCode = $_GET['semestre_code'] ?? '';
        $selectedTypeEval = $_GET['type_evaluation_code'] ?? 'INTERROGATION';
        $selectedCompositionCode = $_GET['composition_code'] ?? '';
        $selectedLibelleEval = $_GET['libelle_eval'] ?? '';
        $selectedCoefEval = $_GET['coefficient'] ?? '';

        $etudiants = [];
        $existingNotes = [];
        $compositionsProgrammees = [];

        if (!empty($selectedClasseCode)) {
            $stmt = $db->prepare("
                SELECT i.id_inscription, i.code_inscription, e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
                FROM inscriptions i
                INNER JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                WHERE i.classe_code = ? AND (i.annee_code = ? OR ? IS NULL OR ? = '')
                ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
            ");
            $stmt->execute([$selectedClasseCode, $anneeCode, $anneeCode, $anneeCode]);
            $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($selectedMatiereCode)) {
                $compModel = new ModelComposition();
                $compositionsProgrammees = $compModel->getByClasseMatiere($selectedClasseCode, $selectedMatiereCode);

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
            'etudiants' => $etudiants,
            'existingNotes' => $existingNotes
        ]);
    }


    public function saveBatch()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $classeCode = $this->post('classe_code');
        $matiereCode = $this->post('matiere_code');
        $semestreCode = $this->post('semestre_code');
        $typeEval = $this->post('type_evaluation_code') ?? 'COMPOSITION';
        $compositionCode = $this->post('composition_code') ?? null;
        $notesData = $_POST['notes'] ?? [];

        if (empty($classeCode) || empty($matiereCode) || empty($semestreCode)) {
            $this->error('Classe, Matière et Semestre sont obligatoires pour la saisie groupée');
            return;
        }

        $db = $this->model->getCon();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        try {
            $db->beginTransaction();

            $savedCount = 0;
            foreach ($notesData as $inscCode => $info) {
                $valNoteStr = trim($info['valeur_note'] ?? '');
                if ($valNoteStr === '') {
                    continue;
                }

                $valNote = (float)$valNoteStr;
                if ($valNote < 0 || $valNote > 20) {
                    throw new Exception("La note doit être comprise entre 0 et 20.");
                }

                $obs = trim($info['observations'] ?? '');

                if (!empty($compositionCode)) {
                    $stmtCheck = $db->prepare("
                        SELECT id_note FROM notes 
                        WHERE inscription_code = ? AND matiere_code = ? AND semestre_code = ? AND composition_code = ?
                    ");
                    $stmtCheck->execute([$inscCode, $matiereCode, $semestreCode, $compositionCode]);
                } else {
                    $stmtCheck = $db->prepare("
                        SELECT id_note FROM notes 
                        WHERE inscription_code = ? AND matiere_code = ? AND semestre_code = ? AND type_evaluation_code = ?
                    ");
                    $stmtCheck->execute([$inscCode, $matiereCode, $semestreCode, $typeEval]);
                }

                $existingId = $stmtCheck->fetchColumn();

                if ($existingId) {
                    $stmtUpd = $db->prepare("
                        UPDATE notes 
                        SET valeur_note = ?, observations = ?, composition_code = ?, statut_note = 'actif'
                        WHERE id_note = ?
                    ");
                    $stmtUpd->execute([$valNote, $obs, $compositionCode, $existingId]);
                } else {
                    $codeNote = $this->validator->generateCode('notes', 'code_note', 'NOT-', 8);
                    $stmtIns = $db->prepare("
                        INSERT INTO notes (code_note, inscription_code, matiere_code, semestre_code, type_evaluation_code, composition_code, valeur_note, observations, statut_note, user_code, etablissement_code, annee_code, created_at_note)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'actif', ?, ?, ?, ?)
                    ");
                    $stmtIns->execute([$codeNote, $inscCode, $matiereCode, $semestreCode, $typeEval, $compositionCode, $valNote, $obs, $userCode, $etabCode, $anneeCode, date('Y-m-d H:i:s')]);
                }
                $savedCount++;
            }

            $db->commit();
            $this->success("$savedCount note(s) enregistrée(s) avec succès pour la classe !", ['reload' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            error_log("NoteController::saveBatch error: " . $e->getMessage());
            $this->error('Erreur lors de l\'enregistrement des notes : ' . $e->getMessage());
        }
    }

}
