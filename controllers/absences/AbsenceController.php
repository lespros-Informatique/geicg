<?php

class AbsenceController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelAbsence();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_ABSENCES');
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

        $this->loadView('../views/absences/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_ABSENCES');
        $anneeCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_absence'];
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
        $this->requirePermission('MANAGE_ABSENCES');
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'etudiant_code' => $data['etudiant_code'] ?? '',
            'classe_code' => $data['classe_code'] ?? ''
        ]);

        if (empty($data['code_absence'])) {
            $data['code_absence'] = $this->validator->generateCode('absences', 'code_absence', 'ABS-', 8);
        }
        $data['created_at_absence'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE absences")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Absence enregistrée avec succès!');
        } else {
            $this->error($this->model->getLastError() ?: 'Erreur lors de l\'enregistrement de l\'absence');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_ABSENCES');
        $id = (int)$this->post('id_absence');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE absences")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_ABSENCES');
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT abs.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant,
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       m.libelle_matiere
                FROM absences abs
                LEFT JOIN etudiants e ON e.code_etudiant = abs.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = abs.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN matieres m ON m.code_matiere = abs.matiere_code
                WHERE abs.id_absence = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'absence/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'absence/list'); exit();
        }
        $this->loadView('../views/absences/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission('MANAGE_ABSENCES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'absence/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'absence/list'); exit();
        }
        $this->loadView('../views/absences/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission('MANAGE_ABSENCES');
        $this->loadView('../views/absences/edit.php', ['item' => []]);
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_ABSENCES');
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = ['oui', 'non'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'justifiee');
            } else {
                $cur = $this->model->getById($id);
                $newStat = ($cur['justifiee'] === 'oui') ? 'non' : 'oui';
                $success = $this->model->updateStatus($id, $newStat, 'justifiee');
            }
            if ($success) {
                $this->success('Statut de justification mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Absence introuvable');
        }
    }

    public function saisieClasse()
    {
        $this->requireAuth();
        $this->requirePermission('MANAGE_ABSENCES');
        $anneeCode = $this->getActiveAnneeCode();
        $classes = (new ModelClasse())->getAll();
        $matieres = (new ModelMatiere())->getAll();
        
        $selectedClasseCode = $_GET['classe_code'] ?? '';
        $selectedDate = $_GET['date_absence'] ?? date('Y-m-d');
        $selectedMatiereCode = $_GET['matiere_code'] ?? '';

        $etudiants = [];
        $existingAbsences = [];

        if (!empty($selectedClasseCode)) {
            $stmt = $this->model->getCon()->prepare("
                SELECT i.code_inscription, e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
                FROM inscriptions i
                INNER JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                WHERE i.classe_code = ? AND (i.annee_code = ? OR ? IS NULL OR ? = '')
                ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
            ");
            $stmt->execute([$selectedClasseCode, $anneeCode, $anneeCode, $anneeCode]);
            $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($selectedDate)) {
                $sqlAbs = "SELECT * FROM absences WHERE classe_code = ? AND date_absence = ?";
                $paramsAbs = [$selectedClasseCode, $selectedDate];
                if (!empty($selectedMatiereCode)) {
                    $sqlAbs .= " AND matiere_code = ?";
                    $paramsAbs[] = $selectedMatiereCode;
                }
                $stmtAbs = $this->model->getCon()->prepare($sqlAbs);
                $stmtAbs->execute($paramsAbs);
                $rows = $stmtAbs->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $r) {
                    $existingAbsences[$r['etudiant_code']] = $r;
                }
            }
        }

        $this->loadView('../views/absences/saisie_classe.php', [
            'classes' => $classes,
            'matieres' => $matieres,
            'selectedClasseCode' => $selectedClasseCode,
            'selectedDate' => $selectedDate,
            'selectedMatiereCode' => $selectedMatiereCode,
            'etudiants' => $etudiants,
            'existingAbsences' => $existingAbsences
        ]);
    }

    public function saveBatch()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_ABSENCES');

        $classeCode = $this->post('classe_code');
        $dateAbsence = $this->post('date_absence');
        $matiereCode = $this->post('matiere_code');
        $dureeHeures = (float)($this->post('duree_heures') ?? 2);
        $absencesData = $_POST['absences'] ?? [];

        $db = $this->model->getCon();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'classe_code' => $classeCode
        ]);

        try {
            $db->beginTransaction();

            foreach ($absencesData as $etudiantCode => $info) {
                $isAbsent = !empty($info['is_absent']);
                $justifiee = !empty($info['justifiee']) ? $info['justifiee'] : 'non';
                $motif = trim($info['motif'] ?? '');

                $stmtCheck = $db->prepare("
                    SELECT id_absence FROM absences 
                    WHERE etudiant_code = ? AND classe_code = ? AND date_absence = ? AND (matiere_code = ? OR (matiere_code IS NULL AND ? = ''))
                ");
                $stmtCheck->execute([$etudiantCode, $classeCode, $dateAbsence, $matiereCode, $matiereCode]);
                $existingId = $stmtCheck->fetchColumn();

                if ($isAbsent) {
                    if ($existingId) {
                        $stmtUpd = $db->prepare("
                            UPDATE absences 
                            SET duree_heures = ?, justifiee = ?, motif_absence = ?
                            WHERE id_absence = ?
                        ");
                        $stmtUpd->execute([$dureeHeures, $justifiee, $motif, $existingId]);
                    } else {
                        $codeAbs = $this->validator->generateCode('absences', 'code_absence', 'ABS-', 8);
                        $stmtIns = $db->prepare("
                            INSERT INTO absences (code_absence, etudiant_code, classe_code, matiere_code, date_absence, duree_heures, justifiee, motif_absence, user_code, etablissement_code, annee_code, created_at_absence)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmtIns->execute([$codeAbs, $etudiantCode, $classeCode, $matiereCode ?: null, $dateAbsence, $dureeHeures, $justifiee, $motif, $userCode, $etabCode, $anneeCode, date('Y-m-d H:i:s')]);
                    }
                } else {
                    if ($existingId) {
                        $stmtDel = $db->prepare("DELETE FROM absences WHERE id_absence = ?");
                        $stmtDel->execute([$existingId]);
                    }
                }
            }

            $db->commit();
            $this->success('Registre des absences de la classe mis à jour avec succès !', ['reload' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            error_log("AbsenceController::saveBatch error: " . $e->getMessage());
            $this->error('Erreur lors de la sauvegarde : ' . $e->getMessage());
        }
    }
}
