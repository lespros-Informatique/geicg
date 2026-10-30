<?php

class EmploiController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEmploi();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_EMPLOI_TEMPS);
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

        $this->loadView('../views/emplois_temps/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_EMPLOI_TEMPS);
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;

        $classesWithEmploi = $this->model->getClassesSummary($anneeCode, $niveauCode, $classeCode);

        $data = [];
        foreach ($classesWithEmploi as $i) {
            $totalMinutes = round(((float)($i['total_heures'] ?? 0)) * 60);
            $h = floor($totalMinutes / 60);
            $m = $totalMinutes % 60;
            $volHoraire = sprintf("%dh%02d", $h, $m);

            $data[] = [
                'code_classe' => $i['code_classe'],
                'libelle_classe' => $i['libelle_classe'],
                'libelle_niveau' => $i['libelle_niveau'] ?: 'Niveau non défini',
                'nb_creneaux' => (int)$i['nb_creneaux'],
                'nb_jours' => (int)$i['nb_jours'],
                'nb_matieres' => (int)$i['nb_matieres'],
                'nb_profs' => (int)$i['nb_profs'],
                'matieres_noms' => $i['matieres_noms'] ?: '',
                'total_heures_formatted' => $volHoraire,
                'last_update' => $i['last_update'] ? date('d/m/Y H:i', strtotime($i['last_update'])) : '-',
                'statut_planning' => ((int)$i['nb_jours'] >= 5) ? 'Complet' : 'Brouillon'
            ];
        }

        $this->json(['data' => $data]);
    }

    public function apiSlots()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_EMPLOI_TEMPS);
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_emploi'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['status' => 1, 'data' => $data]);
    }

    public function getAssignedTeacher()
    {
        $this->requireAuth();
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        $matiereCode = trim($_GET['matiere_code'] ?? ($_POST['matiere_code'] ?? ''));

        if (empty($classeCode) || empty($matiereCode)) {
            $this->json(['status' => 0, 'message' => 'Classe ou matière non spécifiée']);
            return;
        }

        $row = $this->model->getAssignedTeacher($classeCode, $matiereCode);

        if ($row) {
            $nomComplet = trim(($row['nom_enseignant'] ?? '') . ' ' . ($row['prenom_enseignant'] ?? ''));
            $this->json([
                'status' => 1,
                'data' => [
                    'enseignant_code' => $row['code_enseignant'],
                    'nom_complet' => $nomComplet,
                    'grade' => $row['grade_enseignant'] ?? '',
                    'matiere' => $row['libelle_matiere'] ?? ''
                ]
            ]);
        } else {
            $this->json(['status' => 0, 'message' => 'Aucun enseignant spécifiquement affecté']);
        }
    }

    public function getScheduleConflicts(string $classeCode, string $salleCode, string $enseignantCode, string $jour, string $heureDebut, string $heureFin, $excludeId = null): array
    {
        return $this->model->findScheduleConflicts($classeCode, $salleCode, $enseignantCode, $jour, $heureDebut, $heureFin, $excludeId);
    }

    public function checkScheduleConflicts()
    {
        $this->requireAuth();
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        $salleCode = trim($_GET['salle_code'] ?? ($_POST['salle_code'] ?? ''));
        $enseignantCode = trim($_GET['enseignant_code'] ?? ($_POST['enseignant_code'] ?? ''));
        $jour = trim($_GET['jour'] ?? ($_POST['jour'] ?? ''));
        $heureDebut = trim($_GET['heure_debut'] ?? ($_POST['heure_debut'] ?? ''));
        $heureFin = trim($_GET['heure_fin'] ?? ($_POST['heure_fin'] ?? ''));
        $excludeId = $_GET['id_emploi'] ?? ($_POST['id_emploi'] ?? null);

        $conflicts = $this->getScheduleConflicts($classeCode, $salleCode, $enseignantCode, $jour, $heureDebut, $heureFin, $excludeId);

        $this->json([
            'status' => 1,
            'has_conflict' => !empty($conflicts),
            'conflicts' => $conflicts
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'classe_code' => $data['classe_code'] ?? '',
            'matiere_code' => $data['matiere_code'] ?? '',
            'enseignant_code' => $data['enseignant_code'] ?? ''
        ]);

        if (!empty($data['salle_code'])) {
            $this->validateForeignKeys([
                'salle_code' => $data['salle_code']
            ]);
        }

        // Contrôle de conflits côté serveur
        $conflicts = $this->getScheduleConflicts(
            $data['classe_code'] ?? '',
            $data['salle_code'] ?? '',
            $data['enseignant_code'] ?? '',
            $data['jour'] ?? '',
            $data['heure_debut'] ?? '',
            $data['heure_fin'] ?? ''
        );

        if (!empty($conflicts)) {
            $msg = strip_tags($conflicts[0]['message']);
            $this->error("Impossible d'enregistrer : $msg");
            return;
        }

        if (empty($data['code_emploi'])) {
            $data['code_emploi'] = $this->validator->generateCode('emplois_temps', 'code_emploi', 'EMP-', 8);
        }
        $data['jour'] = strtolower($data['jour'] ?? 'lundi');
        $data['statut_emploi'] = $data['statut_emploi'] ?? 'actif';
        $data['created_at_emploi'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        $filteredData = $this->model->filterData($data);
        $submitAction = $_POST['submit_action'] ?? 'save';
        if (!empty($data['classe_code'])) {
            Session::set('last_emploi_classe_code', $data['classe_code']);
            $libelleCls = $this->model->getClasseLibelle($data['classe_code']);
            if ($libelleCls) Session::set('last_emploi_classe_libelle', $libelleCls);
        }

        if ($this->model->create($filteredData)) {
            if ($submitAction === 'save_and_new') {
                $redirectUrl = RACINE . 'emploi/formulaire?classe_code=' . urlencode($data['classe_code']);
                $this->success('Créneau horaire planifié avec succès ! Saisissez le créneau suivant.', ['redirect' => $redirectUrl]);
            } else {
                $this->success('Créneau horaire planifié avec succès!', ['redirect' => RACINE . 'emploi/list']);
            }
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
        $id = (int)$this->post('id_emploi');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        // Contrôle de conflits côté serveur
        $conflicts = $this->getScheduleConflicts(
            $data['classe_code'] ?? '',
            $data['salle_code'] ?? '',
            $data['enseignant_code'] ?? '',
            $data['jour'] ?? '',
            $data['heure_debut'] ?? '',
            $data['heure_fin'] ?? '',
            $id
        );

        if (!empty($conflicts)) {
            $msg = strip_tags($conflicts[0]['message']);
            $this->error("Impossible de modifier : $msg");
            return;
        }

        if (isset($data['jour'])) {
            $data['jour'] = strtolower($data['jour']);
        }

        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $this->success('Créneau horaire mis à jour avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
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
        $this->requirePermission(PERMISSIONS::VIEW_EMPLOI_TEMPS);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getDetailsById((int)$id);
            if (!$item) { header('Location: ' . RACINE . 'emploi/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'emploi/list'); exit();
        }
        $this->loadView('../views/emplois_temps/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'emploi/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'emploi/list'); exit();
        }
        $this->loadView('../views/emplois_temps/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function setSessionClasse()
    {
        $this->requireAuth();
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        if (!empty($classeCode)) {
            Session::set('last_emploi_classe_code', $classeCode);
            $libelle = $this->model->getClasseLibelle($classeCode) ?: $classeCode;
            Session::set('last_emploi_classe_libelle', $libelle);
            $this->json(['status' => 1, 'classe_code' => $classeCode, 'libelle_classe' => $libelle]);
        } else {
            Session::remove('last_emploi_classe_code');
            Session::remove('last_emploi_classe_libelle');
            $this->json(['status' => 1, 'message' => 'Session classe réinitialisée']);
        }
    }

    public function getTeacherSchedule()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_EMPLOI_TEMPS);
        $ensCode = trim($_GET['enseignant_code'] ?? ($_POST['enseignant_code'] ?? ''));
        if (empty($ensCode)) {
            $this->json(['status' => 1, 'data' => []]);
            return;
        }
        $items = $this->model->getTeacherSchedule($ensCode);
        $this->json(['status' => 1, 'data' => $items]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
        $selectedClasseCode = $_GET['classe_code'] ?? Session::get('last_emploi_classe_code', '');
        $activeClasseLibelle = '';

        if (!empty($selectedClasseCode)) {
            Session::set('last_emploi_classe_code', $selectedClasseCode);
            $activeClasseLibelle = $this->model->getClasseLibelle($selectedClasseCode) ?: '';
            if ($activeClasseLibelle) {
                Session::set('last_emploi_classe_libelle', $activeClasseLibelle);
            }
        }

        $this->loadView('../views/emplois_temps/edit.php', [
            'item' => [],
            'selectedClasseCode' => $selectedClasseCode,
            'activeClasseLibelle' => $activeClasseLibelle
        ]);
    }

    public function delete()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
        $rawId = $_POST['id'] ?? ($_GET['id'] ?? $this->post('id'));
        $id = (int)$rawId;

        if ($id > 0) {
            $item = $this->model->getById($id);
            if ($item) {
                if ($this->model->delete($id)) {
                    $this->success('Créneau horaire supprimé avec succès!');
                    return;
                } else {
                    $this->error('Erreur lors de la suppression en base de données');
                    return;
                }
            }
        }
        $this->error('Créneau introuvable');
    }

    public function resetClasseSchedule()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
        $classeCode = trim($this->post('classe_code') ?? ($_GET['classe_code'] ?? ''));
        if (empty($classeCode)) {
            $this->error('Classe non spécifiée');
            return;
        }
        if ($this->model->resetByClasse($classeCode)) {
            $this->success('L\'emploi du temps de la classe a été entièrement réinitialisé !');
        } else {
            $this->error('Erreur lors de la réinitialisation de l\'emploi du temps');
        }
    }

    public function getClassScheduleMatrix()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_EMPLOI_TEMPS);
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        if (empty($classeCode)) {
            $this->json(['status' => 0, 'message' => 'Classe non spécifiée']);
            return;
        }

        $matrix = $this->model->getClassScheduleMatrix($classeCode);

        $this->json([
            'status' => 1,
            'classe' => $matrix['classe'],
            'slots' => $matrix['slots']
        ]);
    }
}

