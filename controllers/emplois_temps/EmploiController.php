<?php

class EmploiController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEmploi();
    }

    /**
     * Détermine le contexte de l'utilisateur : gestionnaire ou enseignant en consultation restreinte
     */
    protected function getTeacherContext(): array
    {
        $canManage = Auth::can(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
        $userCode = Auth::code();
        $teacherCodes = [];
        $isTeacherOnly = false;

        if (!$canManage) {
            $teacherCodes = [$userCode];
            try {
                $db = (new Database())->getCon();
                $stmt = $db->prepare("SELECT code_enseignant, user_code FROM " . TABLES::ENSEIGNANTS . " WHERE user_code = ? OR code_enseignant = ?");
                $stmt->execute([$userCode, $userCode]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($row['code_enseignant'])) $teacherCodes[] = $row['code_enseignant'];
                    if (!empty($row['user_code'])) $teacherCodes[] = $row['user_code'];
                }
                $teacherCodes = array_values(array_unique(array_filter($teacherCodes)));
            } catch (Exception $e) {}

            if (Auth::hasRole(ROLES::ENSEIGNANT) || count($teacherCodes) > 1) {
                $isTeacherOnly = true;
            }
        }

        return [
            'canManage' => $canManage,
            'isTeacherOnly' => $isTeacherOnly,
            'teacherCodes' => $teacherCodes
        ];
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_EMPLOI_TEMPS);

        $ctx = $this->getTeacherContext();
        $canManage = $ctx['canManage'];
        $isTeacherOnly = $ctx['isTeacherOnly'];
        $teacherCodes = $ctx['teacherCodes'];

        $annees = $this->getAccessibleAnnees();
        $niveaux = (new ModelNiveau())->getAll();
        
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

        if ($isTeacherOnly) {
            $classes = $this->model->getClassesForTeacher($teacherCodes, $selectedAnneeCode);
            $classesWithEmploi = $this->model->getClassesSummaryForTeacher($teacherCodes, $selectedAnneeCode);
        } else {
            $classes = (new ModelClasse())->getAll();
            $classesWithEmploi = $this->model->getClassesSummary($selectedAnneeCode);
        }

        // Statistiques globales pour les cartes KPI
        $stats = [
            'total_classes' => count($classesWithEmploi),
            'total_heures' => 0,
            'total_creneaux' => 0,
            'plannings_complets' => 0,
            'plannings_brouillons' => 0,
            'total_matieres' => 0
        ];

        $matieresSet = [];
        foreach ($classesWithEmploi as $item) {
            $stats['total_heures'] += (float)($item['total_heures'] ?? 0);
            $stats['total_creneaux'] += (int)($item['nb_creneaux'] ?? 0);
            if ((int)($item['nb_jours'] ?? 0) >= 5) {
                $stats['plannings_complets']++;
            } else {
                $stats['plannings_brouillons']++;
            }
            if (!empty($item['matieres_noms'])) {
                $parts = explode(',', $item['matieres_noms']);
                foreach ($parts as $p) {
                    $trimmed = trim($p);
                    if ($trimmed !== '') {
                        $matieresSet[$trimmed] = true;
                    }
                }
            }
        }
        $stats['total_matieres'] = count($matieresSet);
        $totalMin = round($stats['total_heures'] * 60);
        $h = floor($totalMin / 60);
        $m = $totalMin % 60;
        $stats['total_heures_formatted'] = sprintf("%dh%02d", $h, $m);

        $this->loadView('../views/emplois_temps/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode,
            'canManage' => $canManage,
            'isTeacherOnly' => $isTeacherOnly,
            'stats' => $stats
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_EMPLOI_TEMPS);

        $ctx = $this->getTeacherContext();
        $canManage = $ctx['canManage'];
        $isTeacherOnly = $ctx['isTeacherOnly'];
        $teacherCodes = $ctx['teacherCodes'];

        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;

        if ($isTeacherOnly) {
            $classesWithEmploi = $this->model->getClassesSummaryForTeacher($teacherCodes, $anneeCode, $niveauCode, $classeCode);
        } else {
            $classesWithEmploi = $this->model->getClassesSummary($anneeCode, $niveauCode, $classeCode);
        }

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
                'total_heures' => (float)($i['total_heures'] ?? 0),
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
            $data['code_emploi'] = $this->validator->generateCode(TABLES::EMPLOIS_TEMPS, 'code_emploi', 'EMP-', 8);
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

        $ctx = $this->getTeacherContext();
        $isTeacherOnly = $ctx['isTeacherOnly'];
        $teacherCodes = $ctx['teacherCodes'];

        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        if (empty($classeCode)) {
            $this->json(['status' => 0, 'message' => 'Classe non spécifiée']);
            return;
        }

        $filterTeachers = $isTeacherOnly ? $teacherCodes : null;
        $matrix = $this->model->getClassScheduleMatrix($classeCode, $filterTeachers);

        $this->json([
            'status' => 1,
            'classe' => $matrix['classe'],
            'slots' => $matrix['slots'],
            'isTeacherOnly' => $isTeacherOnly
        ]);
    }

    /**
     * Impression du Répertoire des Emplois du Temps via mPDF
     */
    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_EMPLOI_TEMPS);

        require_once __DIR__ . '/../../core/PdfService.php';

        $ctx = $this->getTeacherContext();
        $canManage = $ctx['canManage'];
        $isTeacherOnly = $ctx['isTeacherOnly'];
        $teacherCodes = $ctx['teacherCodes'];

        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;

        if ($isTeacherOnly) {
            $classesWithEmploi = $this->model->getClassesSummaryForTeacher($teacherCodes, $anneeCode, $niveauCode, $classeCode);
        } else {
            $classesWithEmploi = $this->model->getClassesSummary($anneeCode, $niveauCode, $classeCode);
        }

        $etablissement = $this->getEtablissementConfig();
        
        $anneeLibelle = $this->getActiveAnneeLibelle();
        if (!empty($anneeCode)) {
            try {
                $db = (new Database())->getCon();
                $stmt = $db->prepare("SELECT libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
                $stmt->execute([$anneeCode]);
                $lbl = $stmt->fetchColumn();
                if ($lbl) $anneeLibelle = $lbl;
            } catch (Exception $e) {}
        }

        $editeurNom = Auth::fullName();
        if (empty($editeurNom)) {
            $editeurNom = $isTeacherOnly ? 'Enseignant' : 'Direction des Études';
        }

        $data = [
            'etablissement' => $etablissement,
            'annee_libelle' => $anneeLibelle,
            'editeur_nom' => $editeurNom,
            'classes' => $classesWithEmploi,
            'isTeacherOnly' => $isTeacherOnly,
            'canManage' => $canManage
        ];

        $html = PdfService::renderTemplate('repertoire_emplois_temps.php', $data);
        $filename = ($isTeacherOnly ? 'Mon_Emploi_Temps_' : 'Repertoire_Emplois_Temps_') . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => ($isTeacherOnly ? 'Mon Emploi du Temps' : 'Répertoire des Emplois du Temps') . ' - ' . ($etablissement['libelle_etablissement'] ?? 'GEICG'),
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 10
        ]);
    }
}

