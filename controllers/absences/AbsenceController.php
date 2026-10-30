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
        $this->requirePermission(PERMISSIONS::VIEW_ABSENCES);
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
        $this->requirePermission(PERMISSIONS::VIEW_ABSENCES);
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
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
        $this->requirePermission(PERMISSIONS::MANAGE_ABSENCES);
        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        $classeCode = trim($data['classe_code'] ?? '');
        $etudiantCode = trim($data['etudiant_code'] ?? '');
        $inscCode = trim($data['inscription_code'] ?? '');

        if (empty($inscCode) && !empty($etudiantCode)) {
            $inscCode = $this->model->getInscriptionCodeByEtudiant($etudiantCode, $classeCode, $anneeCode) ?: '';
        }
        $data['inscription_code'] = $inscCode;

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'inscription_code' => $inscCode
        ]);

        if (empty($data['code_absence'])) {
            $data['code_absence'] = $this->validator->generateCode('absences', 'code_absence', 'ABS-', 8);
        }
        if (isset($data['motif_absence']) && !isset($data['motif'])) {
            $data['motif'] = $data['motif_absence'];
        }
        $data['emploi_code'] = $data['emploi_code'] ?? '';
        $data['matiere_code'] = $data['matiere_code'] ?? '';
        $data['created_at_absence'] = date('Y-m-d H:i:s');
        $data['annee_code'] = $anneeCode;
        $data['etablissement_code'] = $etabCode;

        $filteredData = $this->model->filterData($data);
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
        $this->requirePermission(PERMISSIONS::MANAGE_ABSENCES);
        $id = (int)$this->post('id_absence');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        $anneeCode = $this->getActiveAnneeCode();
        $classeCode = trim($data['classe_code'] ?? '');
        $etudiantCode = trim($data['etudiant_code'] ?? '');
        $inscCode = trim($data['inscription_code'] ?? '');

        if (empty($inscCode) && !empty($etudiantCode)) {
            $inscCode = $this->model->getInscriptionCodeByEtudiant($etudiantCode, $classeCode, $anneeCode) ?: '';
        }
        if (!empty($inscCode)) {
            $data['inscription_code'] = $inscCode;
        }

        if (isset($data['motif_absence']) && !isset($data['motif'])) {
            $data['motif'] = $data['motif_absence'];
        }

        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ABSENCES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getByIdWithDetails($id);
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
        $this->requirePermission(PERMISSIONS::MANAGE_ABSENCES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getByIdWithDetails($id);
            if (!$item) { header('Location: ' . RACINE . 'absence/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'absence/list'); exit();
        }
        $deps = $this->model->getFormDataDependencies();
        $this->loadView('../views/absences/edit.php', array_merge([
            'item' => $item, 
            'encryptedId' => $encryptedId
        ], $deps));
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ABSENCES);
        $deps = $this->model->getFormDataDependencies();
        $this->loadView('../views/absences/edit.php', array_merge([
            'item' => []
        ], $deps));
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ABSENCES);
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && ($cur = $this->model->getById($id))) {
            $allowed = ['oui', 'non'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $newStat = $statut;
            } else {
                $newStat = ($cur['justifiee'] === 'oui') ? 'non' : 'oui';
            }
            $success = $this->model->updateStatus($id, $newStat, 'justifiee');
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
        $this->requirePermission(PERMISSIONS::MANAGE_ABSENCES);
        $anneeCode = $this->getActiveAnneeCode();
        $classes = (new ModelClasse())->getAll();
        $matieres = (new ModelMatiere())->getAll();
        
        $selectedClasseCode = $_GET['classe_code'] ?? '';
        $selectedDate = $_GET['date_absence'] ?? date('Y-m-d');
        $selectedMatiereCode = $_GET['matiere_code'] ?? '';

        $etudiants = [];
        $existingAbsences = [];

        if (!empty($selectedClasseCode)) {
            $etudiants = $this->model->getStudentsByClasse($selectedClasseCode, $anneeCode);
            if (!empty($selectedDate)) {
                $existingAbsences = $this->model->getExistingAbsences($selectedClasseCode, $selectedDate, $selectedMatiereCode);
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
        $this->requirePermission(PERMISSIONS::MANAGE_ABSENCES);

        $csrf = $this->post('csrf_token');
        if (!Validator::validateCsrfToken($csrf)) {
            $this->error('Jeton CSRF invalide ou session expirée. Veuillez recharger la page.');
            return;
        }

        $classeCode = $this->post('classe_code');
        $dateAbsence = $this->post('date_absence');
        $matiereCode = $this->post('matiere_code') ?: null;
        $dureeHeures = (float)($this->post('duree_heures') ?? 2);
        $absencesData = $_POST['absences'] ?? [];

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'classe_code' => $classeCode
        ]);

        try {
            $this->model->saveBatchAbsences(
                $classeCode,
                $dateAbsence,
                $matiereCode,
                $dureeHeures,
                $absencesData,
                $anneeCode,
                $etabCode
            );
            $this->success('Registre des absences de la classe mis à jour avec succès !', ['reload' => true]);
        } catch (Exception $e) {
            error_log("AbsenceController::saveBatch error: " . $e->getMessage());
            $this->error('Erreur lors de la sauvegarde : ' . $e->getMessage());
        }
    }
}
