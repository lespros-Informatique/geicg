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
        
        $selectedAnneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
        if ($selectedAnneeCode !== null) {
            foreach ($annees as $a) {
                if ($a['code_annee'] === $selectedAnneeCode) {
                    Session::set('annee_active_code', $a['code_annee']);
                    Session::set('annee_active_libelle', $a['libelle_annee']);
                    break;
                }
            }
        }

        $classes = !empty($selectedAnneeCode) ? (new ModelClasse())->getAll($selectedAnneeCode) : [];
        $modelSemestre = new ModelSemestre();
        $allSemestres = $modelSemestre->getAll();
        usort($allSemestres, static fn($a, $b) => ((int)($a['id_semestre'] ?? 0)) <=> ((int)($b['id_semestre'] ?? 0)));

        $semestresByAnnee = [];
        foreach ($allSemestres as $sem) {
            $ac = (string)($sem['annee_code'] ?? '');
            if ($ac === '') continue;
            $semestresByAnnee[$ac][] = [
                'code_semestre' => $sem['code_semestre'],
                'libelle_semestre' => $sem['libelle_semestre'],
                'statut_semestre' => $sem['statut_semestre'] ?? ''
            ];
        }
        $semestres = !empty($selectedAnneeCode) ? ($semestresByAnnee[$selectedAnneeCode] ?? []) : [];

        $this->loadView('../views/absences/list.php', [
            'annees' => $annees,
            'semestres' => $semestres,
            'semestresByAnnee' => $semestresByAnnee,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ABSENCES);
        $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
        if (empty($anneeCode)) {
            $this->json(['data' => []]);
            return;
        }

        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $semestreCode = $_GET['semestre_code'] ?? null;
        $dateDebut = trim((string)($_GET['date_debut'] ?? ''));
        $dateFin = trim((string)($_GET['date_fin'] ?? ''));
        $dateDebut = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateDebut) ? $dateDebut : null;
        $dateFin = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFin) ? $dateFin : null;
        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode, $semestreCode, $dateDebut, $dateFin);
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

    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ABSENCES);

        require_once __DIR__ . '/../../core/PdfService.php';

        $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
        if (empty($anneeCode)) {
            $this->error("Accès non autorisé aux absences de cette année académique.");
            return;
        }

        $niveauCode = trim((string)($_GET['niveau_code'] ?? '')) ?: null;
        $classeCode = trim((string)($_GET['classe_code'] ?? '')) ?: null;
        $semestreCode = trim((string)($_GET['semestre_code'] ?? '')) ?: null;
        $dateDebutRaw = trim((string)($_GET['date_debut'] ?? ''));
        $dateFinRaw = trim((string)($_GET['date_fin'] ?? ''));
        $dateDebut = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateDebutRaw) ? $dateDebutRaw : null;
        $dateFin = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFinRaw) ? $dateFinRaw : null;

        $etablissement = $this->getEtablissementConfig();
        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode, $semestreCode, $dateDebut, $dateFin);
        $filterLabels = $this->model->getFilterLabels($anneeCode, $semestreCode, $niveauCode, $classeCode);

        $totalHeures = 0.0;
        $heuresJustifiees = 0.0;
        $heuresInjustifiees = 0.0;
        $nbJustifiees = 0;
        $nbInjustifiees = 0;
        foreach ($items as $it) {
            $h = (float)($it['duree_heures'] ?? 0);
            $totalHeures += $h;
            if (($it['justifiee'] ?? 'non') === STATUTS::JUSTIFIE) {
                $nbJustifiees++;
                $heuresJustifiees += $h;
            } else {
                $nbInjustifiees++;
                $heuresInjustifiees += $h;
            }
        }

        $periodeLabel = 'Toute la période';
        if ($dateDebut && $dateFin) {
            $periodeLabel = 'Du ' . date('d/m/Y', strtotime($dateDebut)) . ' au ' . date('d/m/Y', strtotime($dateFin));
        } elseif ($dateDebut) {
            $periodeLabel = 'À partir du ' . date('d/m/Y', strtotime($dateDebut));
        } elseif ($dateFin) {
            $periodeLabel = "Jusqu'au " . date('d/m/Y', strtotime($dateFin));
        }

        $editeurNom = Auth::fullName();

        $data = [
            'etablissement' => $etablissement,
            'annee_libelle' => $filterLabels['annee'] !== '' ? $filterLabels['annee'] : $this->getActiveAnneeLibelle(),
            'editeur_nom' => $editeurNom,
            'absences' => $items,
            'filtres' => [
                'semestre' => $filterLabels['semestre'],
                'niveau' => $filterLabels['niveau'],
                'classe' => $filterLabels['classe'],
                'periode' => $periodeLabel
            ],
            'kpis' => [
                'total_absences' => count($items),
                'total_heures' => $totalHeures,
                'nb_justifiees' => $nbJustifiees,
                'heures_justifiees' => $heuresJustifiees,
                'nb_injustifiees' => $nbInjustifiees,
                'heures_injustifiees' => $heuresInjustifiees
            ]
        ];

        $html = PdfService::renderTemplate('registre_absences.php', $data);

        $this->logAction(
            AuditLogger::ACTION_PRINT,
            'absences',
            $anneeCode,
            "Impression du registre des absences (" . count($items) . " enregistrement(s))",
            [
                'annee_code' => $anneeCode,
                'semestre_code' => $semestreCode,
                'niveau_code' => $niveauCode,
                'classe_code' => $classeCode,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin
            ]
        );

        $sigle = trim((string)($etablissement['sigle_etablissement'] ?? ''));
        $filename = 'Registre_Absences_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'Registre des Absences' . ($sigle !== '' ? ' - ' . $sigle : ''),
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 10
        ]);
    }

    public function imprimerHistoriqueEtudiant($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ABSENCES);

        require_once __DIR__ . '/../../core/PdfService.php';

        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getByIdWithDetails($id);
            if (!$item) {
                redirect('absence/list');
            }
        } catch (Exception $e) {
            redirect('absence/list');
        }

        $targetAnnee = !empty($item['annee_code'])
            ? (string)$item['annee_code']
            : (!empty($item['inscription_annee_code']) ? (string)$item['inscription_annee_code'] : null);
        $anneeCode = $this->resolveTargetAcademicYear($targetAnnee);
        if (empty($anneeCode)) {
            $this->error("Accès non autorisé aux absences de cette année académique.");
            return;
        }

        $etablissement = $this->getEtablissementConfig();
        $context = $this->model->getStudentAbsenceContext(
            (string)($item['inscription_code'] ?? ''),
            $anneeCode,
            null
        );

        $editeurNom = Auth::fullName();
        $anneeLibelle = !empty($item['libelle_annee']) ? (string)$item['libelle_annee'] : $this->getActiveAnneeLibelle();

        $data = [
            'etablissement' => $etablissement,
            'etudiant' => $item,
            'annee_libelle' => $anneeLibelle,
            'editeur_nom' => $editeurNom,
            'historique' => $context['history'],
            'stats' => $context['stats']
        ];

        $html = PdfService::renderTemplate('releve_absences_etudiant.php', $data);

        $nomComplet = trim((string)($item['nom_etudiant'] ?? ''));
        $this->logAction(
            AuditLogger::ACTION_PRINT,
            'absences',
            (string)($item['inscription_code'] ?? $item['id_absence']),
            "Impression de l'historique des absences de " . ($nomComplet !== '' ? $nomComplet : ($item['code_etudiant'] ?? '')),
            [
                'inscription_code' => $item['inscription_code'] ?? '',
                'annee_code' => $anneeCode,
                'nb_absences' => count($context['history'])
            ]
        );

        $cleanNom = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nomComplet !== '' ? $nomComplet : ($item['matricule_etudiant'] ?? 'Etudiant'));
        $sigle = trim((string)($etablissement['sigle_etablissement'] ?? ''));
        $filename = 'Historique_Absences_' . $cleanNom . '_' . date('Ymd_His') . '.pdf';

        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Historique des Absences - ' . $nomComplet . ($sigle !== '' ? ' - ' . $sigle : ''),
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 8,
            'margin_bottom' => 10
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ABSENCES);
        $userCode = Auth::code();
        $anneeCode = $this->resolveTargetAcademicYear($_POST['annee_code'] ?? null);
        if (empty($anneeCode) || !$this->academicGuard()->canUserWriteYear($anneeCode)) {
            $this->error("Opération refusée : vous n'avez pas l'autorisation d'enregistrer des absences sur cette année académique.");
            return;
        }
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

        $semestreCode = trim((string)($data['semestre_code'] ?? ''));
        if ($semestreCode === '') {
            $semestreCode = $this->model->resolveSemestreCodeForDate($anneeCode, $data['date_absence'] ?? null) ?: '';
        }
        $data['semestre_code'] = $semestreCode !== '' ? $semestreCode : null;

        $fkChecks = [
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'inscription_code' => $inscCode
        ];
        if ($semestreCode !== '') {
            $fkChecks['semestre_code'] = $semestreCode;
        }
        $this->validateForeignKeys($fkChecks);

        if (empty($data['code_absence'])) {
            $data['code_absence'] = $this->validator->generateCode(TABLES::ABSENCES, 'code_absence', 'ABS-', 8);
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
            $this->logAction(
                AuditLogger::ACTION_CREATE,
                'absences',
                $data['code_absence'],
                "Enregistrement d'une absence pour {$inscCode} le " . ($data['date_absence'] ?? date('d/m/Y')),
                $filteredData,
                $etabCode
            );
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

        if (isset($data['semestre_code'])) {
            $semestreCode = trim((string)$data['semestre_code']);
            if ($semestreCode !== '') {
                $this->validateForeignKeys(['semestre_code' => $semestreCode]);
                $data['semestre_code'] = $semestreCode;
            } else {
                $data['semestre_code'] = null;
            }
        }

        if (isset($data['motif_absence']) && !isset($data['motif'])) {
            $data['motif'] = $data['motif_absence'];
        }

        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $this->logAction(
                AuditLogger::ACTION_UPDATE,
                'absences',
                (string)$id,
                "Modification de l'absence #{$id}",
                $filteredData
            );
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
            if (!$item) { redirect('absence/list'); }
            $encryptedId = $this->validator->crypter($item['id_absence']);
            $encryptedEtudiantId = !empty($item['id_etudiant'])
                ? $this->validator->crypter($item['id_etudiant'])
                : (!empty($item['code_etudiant']) ? $this->validator->crypter($item['code_etudiant']) : '');
        } catch (Exception $e) {
            redirect('absence/list');
        }

        $context = $this->model->getStudentAbsenceContext(
            (string)($item['inscription_code'] ?? ''),
            !empty($item['annee_code']) ? (string)$item['annee_code'] : ($item['inscription_annee_code'] ?? null),
            !empty($item['semestre_code']) ? (string)$item['semestre_code'] : null
        );

        $history = [];
        foreach ($context['history'] as $h) {
            $h['encrypted_id'] = $this->validator->crypter($h['id_absence']);
            $history[] = $h;
        }

        $this->loadView('../views/absences/details.php', [
            'item' => $item,
            'encryptedId' => $encryptedId,
            'encryptedEtudiantId' => $encryptedEtudiantId,
            'studentStats' => $context['stats'],
            'studentHistory' => $history
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ABSENCES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getByIdWithDetails($id);
            if (!$item) { redirect('absence/list'); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            redirect('absence/list');
        }
        $deps = $this->model->getFormDataDependencies($item['annee_code'] ?? null);
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
                $this->logAction(
                    AuditLogger::ACTION_UPDATE,
                    'absences',
                    (string)$id,
                    "Changement du statut de justification de l'absence #{$id} : {$newStat}",
                    ['id_absence' => $id, 'justifiee' => $newStat]
                );
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
        $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
        if (empty($anneeCode)) {
            $this->renderNotFound("Accès refusé : aucune année académique active autorisée.");
            return;
        }
        $classes = (new ModelClasse())->getAll($anneeCode);
        $matieres = (new ModelMatiere())->getAll();
        $semestres = (new ModelSemestre())->getAll($anneeCode);
        usort($semestres, static fn($a, $b) => ((int)($a['id_semestre'] ?? 0)) <=> ((int)($b['id_semestre'] ?? 0)));

        $activeSemestreCode = '';
        foreach ($semestres as $sem) {
            if (($sem['statut_semestre'] ?? '') === STATUTS::ACTIF) {
                $activeSemestreCode = (string)$sem['code_semestre'];
                break;
            }
        }
        if ($activeSemestreCode === '' && !empty($semestres)) {
            $activeSemestreCode = (string)($semestres[0]['code_semestre'] ?? '');
        }
        
        $selectedClasseCode = $_GET['classe_code'] ?? '';
        $selectedDate = $_GET['date_absence'] ?? date('Y-m-d');
        $selectedMatiereCode = $_GET['matiere_code'] ?? '';
        $selectedSemestreCode = $_GET['semestre_code'] ?? $activeSemestreCode;

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
            'semestres' => $semestres,
            'selectedClasseCode' => $selectedClasseCode,
            'selectedDate' => $selectedDate,
            'selectedMatiereCode' => $selectedMatiereCode,
            'selectedSemestreCode' => $selectedSemestreCode,
            'etudiants' => $etudiants,
            'existingAbsences' => $existingAbsences
        ]);
    }

    public function saveBatch()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ABSENCES);

        $csrf = $this->post('csrf_token') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!Validator::validateCsrfToken($csrf)) {
            Auth::logout();
            if ($this->isAjax()) {
                $this->json([
                    'status' => 0,
                    'session_expired' => true,
                    'redirect' => url('user/connexion', ['expired' => 1]),
                    'message' => 'Session expirée. Veuillez vous reconnecter.'
                ], 401);
                return;
            }
            redirect('user/connexion', ['expired' => 1]);
        }

        $classeCode = $this->post('classe_code');
        $dateAbsence = $this->post('date_absence');
        $matiereCode = $this->post('matiere_code') ?: null;
        $semestreCode = trim((string)($this->post('semestre_code') ?? '')) ?: null;
        $dureeHeures = (float)($this->post('duree_heures') ?? 2);
        $absencesData = $_POST['absences'] ?? [];

        $userCode = Auth::code();
        $anneeCode = $this->resolveTargetAcademicYear($this->post('annee_code'));
        if (empty($anneeCode) || !$this->academicGuard()->canUserWriteYear($anneeCode)) {
            $this->error("Opération refusée : vous n'avez pas l'autorisation d'enregistrer des absences sur cette année académique.");
            return;
        }
        $etabCode = $this->getActiveEtablissementCode();

        $fkChecks = [
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'classe_code' => $classeCode
        ];
        if (!empty($semestreCode)) {
            $fkChecks['semestre_code'] = $semestreCode;
        }
        $this->validateForeignKeys($fkChecks);

        try {
            $this->model->saveBatchAbsences(
                $classeCode,
                $dateAbsence,
                $matiereCode,
                $dureeHeures,
                $absencesData,
                $anneeCode,
                $etabCode,
                $semestreCode
            );
            $nbAbs = is_array($absencesData) ? count($absencesData) : 0;
            $this->logAction(
                AuditLogger::ACTION_UPDATE,
                'absences',
                $classeCode,
                "Saisie groupée des absences pour la classe {$classeCode} au {$dateAbsence} ({$nbAbs} lignes traitées)",
                [
                    'classe_code' => $classeCode,
                    'date_absence' => $dateAbsence,
                    'matiere_code' => $matiereCode,
                    'semestre_code' => $semestreCode,
                    'duree_heures' => $dureeHeures,
                    'nb_lignes' => $nbAbs
                ],
                $etabCode
            );
            $this->success('Registre des absences de la classe mis à jour avec succès !', ['reload' => true]);
        } catch (Exception $e) {
            error_log("AbsenceController::saveBatch error: " . $e->getMessage());
            $this->error('Erreur lors de la sauvegarde : ' . $e->getMessage());
        }
    }
}
