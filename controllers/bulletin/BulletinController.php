<?php

class BulletinController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelBulletin();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::VIEW_NOTES]);
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

        $teacherCtx = $this->getTeacherContext();
        $isTeacherOnly = $teacherCtx['isTeacherOnly'];
        $teacherCodes = $teacherCtx['teacherCodes'];

        if (empty($selectedAnneeCode)) {
            $classes = [];
            $semestres = [];
        } else {
            if ($isTeacherOnly && !empty($teacherCodes)) {
                require_once __DIR__ . '/../notes/NoteController.php';
                $modelNote = new ModelNote();
                $classes = $modelNote->getClassesForTeacher($teacherCodes, $selectedAnneeCode);
            } else {
                $classes = (new ModelClasse())->getAll();
            }

            $semestres = (new ModelSemestre())->getAll($selectedAnneeCode);
            if (empty($semestres)) {
                $semestres = $this->model->getActiveSemestres($selectedAnneeCode);
            }
        }

        $canGenerateBulletins = Auth::can([PERMISSIONS::GENERATE_BULLETINS, PERMISSIONS::CONFIG_ACADEMIQUE]) && !$isTeacherOnly;
        $canViewBulletins = Auth::can(PERMISSIONS::VIEW_BULLETINS) || $isTeacherOnly;

        $this->loadView('../views/bulletin/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'semestres' => $semestres,
            'selectedAnneeCode' => $selectedAnneeCode,
            'isTeacherOnly' => $isTeacherOnly,
            'canGenerateBulletins' => $canGenerateBulletins,
            'canViewBulletins' => $canViewBulletins
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
        $niveauCode = !empty($_GET['niveau_code']) ? trim($_GET['niveau_code']) : null;
        $classeCode = !empty($_GET['classe_code']) ? trim($_GET['classe_code']) : null;
        $semestreCode = !empty($_GET['semestre_code']) ? trim($_GET['semestre_code']) : null;
        $statutNotes = !empty($_GET['statut_notes']) ? trim($_GET['statut_notes']) : null;

        // Arrêt immédiat si aucune classe sélectionnée ou aucune année académique autorisée
        if (empty($anneeCode) || empty($classeCode)) {
            $this->json(['data' => []]);
            return;
        }

        $teacherCtx = $this->getTeacherContext();
        $teacherClasses = null;
        if ($teacherCtx['isTeacherOnly'] && !empty($teacherCtx['teacherCodes'])) {
            require_once __DIR__ . '/../../models/notes/ModelNote.php';
            $clsList = (new ModelNote())->getClassesForTeacher($teacherCtx['teacherCodes'], $anneeCode);
            $teacherClasses = array_column($clsList, 'code_classe');
            if (empty($teacherClasses)) {
                $teacherClasses = ['__NO_ACCESS__'];
            }
        }

        $items = $this->model->getInscriptionsWithNotes(
            $anneeCode,
            $niveauCode,
            $classeCode,
            $semestreCode,
            $teacherClasses,
            $statutNotes
        );

        $data = [];
        foreach ($items as $i) {
            $id = $i['id_inscription'];
            $idCrypte = $this->validator->crypter($id);
            $moy = $i['moyenne_semestre'] !== null ? (float)$i['moyenne_semestre'] : null;
            $decision = 'En attente';
            if ($moy !== null) {
                if ($moy >= 10) {
                    $decision = 'Admis(e)';
                } else {
                    $decision = 'Ajourné(e)';
                }
            }

            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'moyenne_num' => $moy,
                'decision' => $decision,
                'semestre_code_selected' => $semestreCode
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function calculateBulletinData(int $id, ?string $selectedSemestreCode = null): ?array
    {
        $calc = $this->model->calculateBulletinData($id, $selectedSemestreCode);
        if (!$calc) {
            return null;
        }
        $calc['encryptedId'] = $this->validator->crypter($id);
        return $calc;
    }

    /**
     * Prépare le tableau des variables pour la vue bulletin_notes.php
     */
    private function buildBulletinViewData(array $calc): array
    {
        $item = $calc['item'];
        $semestreLibelle = $calc['selectedSemestreCode'] ?? '';
        if (!empty($calc['semestres'])) {
            foreach ($calc['semestres'] as $s) {
                if ($s['code_semestre'] === ($calc['selectedSemestreCode'] ?? '')) {
                    $semestreLibelle = $s['libelle_semestre'];
                    break;
                }
            }
        }

        $nomComplet = trim(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? ''));
        $config = $this->getEtablissementConfig();

        return [
            'annee_libelle' => $item['libelle_annee'] ?? '-',
            'photo_etudiant' => $item['photo_etudiant'] ?? '',
            'nom_prenom_etudiant' => $nomComplet ?: '-',
            'matricule_etudiant' => $item['matricule_etudiant'] ?? '-',
            'matricule_mesrs' => !empty($item['matricule_mesrs']) ? $item['matricule_mesrs'] : (!empty($item['matricule_menet']) ? $item['matricule_menet'] : ($item['matricule_etudiant'] ?? '-')),
            'date_naissance' => !empty($item['date_naissance_etudiant']) ? date('d/m/Y', strtotime($item['date_naissance_etudiant'])) : '-',
            'lieu_naissance' => $item['lieu_naissance_etudiant'] ?? '-',
            'sexe_etudiant' => strtoupper($item['sexe_etudiant'] ?? '-'),
            'filiere_libelle' => $item['libelle_filiere'] ?? '-',
            'niveau_libelle' => $item['libelle_niveau'] ?? '-',
            'classe_libelle' => $item['libelle_classe'] ?? '-',
            'classe_code_or_ref' => !empty($item['code_classe']) ? $item['code_classe'] : ($item['libelle_classe'] ?? '-'),
            'effectif_classe' => $calc['totalElevesClasse'] ?? '-',
            'statut_affectation' => (!empty($item['affectation_etat']) && strtolower(trim($item['affectation_etat'])) === 'oui') ? 'AFFECTE' : 'NON AFFECTE',
            'redoublant' => (!empty($item['redoublant']) && strtolower(trim($item['redoublant'])) === 'oui') ? 'OUI' : 'NON',
            'intitule_periode' => strtoupper($semestreLibelle ?: 'SEMESTRE'),
            'matieres_notes' => $calc['matieresNotes'] ?? [],
            'matieresGenerales' => $calc['matieresGenerales'] ?? [],
            'matieresPro' => $calc['matieresPro'] ?? [],
            'totalPointsGenerales' => $calc['totalPointsGenerales'] ?? 0,
            'totalCoefficientsGenerales' => $calc['totalCoefficientsGenerales'] ?? 0,
            'moyenneGenerales' => $calc['moyenneGenerales'] ?? 0,
            'rangGeneralesFr' => $calc['rangGeneralesFr'] ?? '-',
            'appreciationGenerales' => $calc['appreciationGenerales'] ?? '-',
            'totalPointsPro' => $calc['totalPointsPro'] ?? 0,
            'totalCoefficientsPro' => $calc['totalCoefficientsPro'] ?? 0,
            'moyennePro' => $calc['moyennePro'] ?? 0,
            'rangProFr' => $calc['rangProFr'] ?? '-',
            'appreciationPro' => $calc['appreciationPro'] ?? '-',
            'totalPoints' => $calc['totalPoints'] ?? 0,
            'totalCoefficients' => $calc['totalCoefficients'] ?? 0,
            'moyenneGenerale' => $calc['moyenneGenerale'] ?? 0,
            'mention' => $calc['mention'] ?? '-',
            'decision' => $calc['decision'] ?? '-',
            'rang' => $calc['rang'] ?? '-',
            'rangFr' => $calc['rangFr'] ?? (($calc['rang'] ?? '-') !== '-' ? ($calc['rang'] . 'ème') : '-'),
            'totalElevesClasse' => $calc['totalElevesClasse'] ?? '-',
            'moyenneMinClasse' => $calc['moyenneMinClasse'] ?? 0,
            'moyenneMaxClasse' => $calc['moyenneMaxClasse'] ?? 0,
            'moyenneMoyClasse' => $calc['moyenneMoyClasse'] ?? 0,
            'heures_absences' => $calc['heuresAbsences'] ?? '0 H',
            'moy_semestre1' => $calc['moy_semestre1'] ?? null,
            'moy_semestre2' => $calc['moy_semestre2'] ?? null,
            'moy_examen' => $calc['moy_examen'] ?? null,
            'etablissement' => $config,
            'directeur_etudes' => ($config['nom_directeur_etudes'] ?? 'Dr COULIBALY Sonan Hamed')
        ];
    }


    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        try {
            $id = $this->validator->decrypter($details);
            $selectedSemestreCode = $_GET['semestre'] ?? '';
            $calc = $this->calculateBulletinData($id, $selectedSemestreCode);
            if (!$calc) {
                $this->renderNotFound("Le bulletin demandé est introuvable.");
                return;
            }
            $this->loadView('../views/bulletin/details.php', $calc);
        } catch (Exception $e) {
            error_log("BulletinController::details error: " . $e->getMessage());
            $this->renderNotFound("Le bulletin demandé est introuvable.");
            return;
        }
    }

    public function imprimerBulletinPdf($details)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::GENERATE_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        require_once __DIR__ . '/../../core/PdfService.php';

        try {
            $id = $this->validator->decrypter($details);
            $selectedSemestreCode = $_GET['semestre'] ?? '';
            $calc = $this->calculateBulletinData($id, $selectedSemestreCode);
            if (!$calc) {
                $this->renderNotFound("Le bulletin demandé est introuvable.");
                return;
            }

            $item = $calc['item'];
            $dataView = $this->buildBulletinViewData($calc);
            $nomComplet = $dataView['nom_prenom_etudiant'];
            $matricule = $item['matricule_etudiant'] ?? 'Etudiant';

            $this->logAction(
                AuditLogger::ACTION_PRINT,
                'bulletins',
                $matricule,
                "Impression du bulletin de notes de {$nomComplet} ({$matricule}) pour {$dataView['intitule_periode']}",
                ['matricule' => $matricule, 'nom_etudiant' => $nomComplet, 'semestre' => $selectedSemestreCode, 'moyenne' => $dataView['moyenneGenerale'] ?? null]
            );

            $html = PdfService::renderTemplate('bulletin_notes.php', $dataView);
            if (isset($_GET['html'])) {
                echo $html;
                return;
            }

            $filename = 'Bulletin_' . ($item['matricule_etudiant'] ?? 'Etudiant') . '_' . date('Ymd_His') . '.pdf';
            PdfService::generate($html, $filename, [
                'orientation' => 'P',
                'format' => 'A4',
                'title' => 'Bulletin de Notes - ' . $nomComplet,
                'margin_left' => 7,
                'margin_right' => 7,
                'margin_top' => 6,
                'margin_bottom' => 6
            ]);
        } catch (Exception $e) {
            error_log("BulletinController::imprimerBulletinPdf error: " . $e->getMessage());
            $this->renderNotFound("Erreur lors de la génération du bulletin PDF.");
        }
    }

    /**
     * Imprime en un seul fichier PDF tous les bulletins des étudiants de la classe sélectionnée
     */
    public function imprimerClasseBulletinsPdf()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::GENERATE_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        require_once __DIR__ . '/../../core/PdfService.php';

        try {
            ini_set('memory_limit', '512M');
            set_time_limit(180);

            $classeCode = !empty($_GET['classe_code']) ? trim($_GET['classe_code']) : null;
            $semestreCode = !empty($_GET['semestre_code']) ? trim($_GET['semestre_code']) : null;
            $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
            $statutNotes = !empty($_GET['statut_notes']) ? trim($_GET['statut_notes']) : null;

            if (empty($anneeCode)) {
                $this->renderNotFound("Accès refusé : aucune année académique active autorisée.");
                return;
            }

            if (empty($classeCode)) {
                $this->renderNotFound("Veuillez sélectionner une classe pour imprimer les bulletins de notes.");
                return;
            }

            $teacherCtx = $this->getTeacherContext();
            $teacherClasses = null;
            if ($teacherCtx['isTeacherOnly'] && !empty($teacherCtx['teacherCodes'])) {
                require_once __DIR__ . '/../../models/notes/ModelNote.php';
                $clsList = (new ModelNote())->getClassesForTeacher($teacherCtx['teacherCodes'], $anneeCode);
                $teacherClasses = array_column($clsList, 'code_classe');
                if (!in_array($classeCode, $teacherClasses)) {
                    $this->renderNotFound("Vous n'avez pas l'autorisation d'accéder aux bulletins de cette classe.");
                    return;
                }
            }

            $items = $this->model->getInscriptionsWithNotes(
                $anneeCode,
                null,
                $classeCode,
                $semestreCode,
                $teacherClasses,
                $statutNotes
            );

            if (empty($items)) {
                $this->renderNotFound("Aucun étudiant inscrit trouvé dans cette classe pour la période sélectionnée.");
                return;
            }

            $bulletins = [];
            foreach ($items as $item) {
                $id = (int)$item['id_inscription'];
                $calc = $this->calculateBulletinData($id, $semestreCode);
                if ($calc) {
                    $bulletins[] = $this->buildBulletinViewData($calc);
                }
            }

            if (empty($bulletins)) {
                $this->renderNotFound("Aucune donnée de bulletin n'a pu être générée pour cette classe.");
                return;
            }

            $fullHtml = '';
            $total = count($bulletins);
            foreach ($bulletins as $idx => $bData) {
                if ($idx > 0) {
                    $fullHtml .= '<pagebreak />';
                }
                $bData['is_batch'] = true;
                $bData['include_styles'] = ($idx === 0);
                $bData['is_last_in_batch'] = ($idx === $total - 1);
                $fullHtml .= PdfService::renderTemplate('bulletin_notes.php', $bData);
            }

            if (isset($_GET['html'])) {
                echo $fullHtml;
                return;
            }

            $classeLibelle = $bulletins[0]['classe_libelle'] ?? $classeCode;
            $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $classeLibelle);
            $filename = 'Bulletins_Classe_' . $cleanName . '_' . date('Ymd_His') . '.pdf';

            $this->logAction(
                AuditLogger::ACTION_PRINT,
                'bulletins',
                $classeCode,
                "Impression groupée des bulletins pour la classe {$classeLibelle} ({$total} étudiants)",
                ['classe_code' => $classeCode, 'semestre_code' => $semestreCode, 'nb_bulletins' => $total]
            );

            PdfService::generate($fullHtml, $filename, [
                'orientation' => 'P',
                'format' => 'A4',
                'title' => 'Bulletins de Notes - Classe ' . $classeLibelle,
                'margin_left' => 7,
                'margin_right' => 7,
                'margin_top' => 6,
                'margin_bottom' => 6
            ]);
        } catch (Exception $e) {
            error_log("BulletinController::imprimerClasseBulletinsPdf error: " . $e->getMessage());
            $this->renderNotFound("Erreur lors de la génération des bulletins de la classe en PDF : " . $e->getMessage());
        }
    }

    public function formulaire()
    {
        $this->requireAuth();
        header('Location: ' . RACINE . 'bulletin/list');
        exit();
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->details($details);
    }

    private function calculatePvClasseData(string $selectedClasseCode, string $selectedSemestreCode): array
    {
        return $this->model->calculatePvClasseData($selectedClasseCode, $selectedSemestreCode, $this->getActiveAnneeCode());
    }

    public function pvClasse($param = null)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::VIEW_NOTES]);

        $classes = (new ModelClasse())->getAll();
        $semestres = $this->model->getActiveSemestres();

        $selectedClasseCode = $_GET['classe_code'] ?? '';
        if (empty($selectedClasseCode) && !empty($param)) {
            try {
                $selectedClasseCode = $this->validator->decrypter($param);
            } catch (Exception $e) {
                $selectedClasseCode = $param;
            }
        }
        if (empty($selectedClasseCode) && !empty($classes)) {
            $selectedClasseCode = $classes[0]['code_classe'] ?? '';
        }

        $selectedSemestreCode = $_GET['semestre_code'] ?? ($semestres[0]['code_semestre'] ?? '');

        $pvData = $this->calculatePvClasseData($selectedClasseCode, $selectedSemestreCode);
        $encryptedClasseCode = !empty($selectedClasseCode) ? $this->validator->crypter($selectedClasseCode) : '';

        $this->loadView('../views/bulletin/pv_classe.php', array_merge($pvData, [
            'classes' => $classes,
            'semestres' => $semestres,
            'selectedClasseCode' => $selectedClasseCode,
            'selectedSemestreCode' => $selectedSemestreCode,
            'encryptedClasseCode' => $encryptedClasseCode
        ]));
    }

    public function imprimerPvPdf($param = null)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::GENERATE_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        require_once __DIR__ . '/../../core/PdfService.php';

        $selectedClasseCode = $_GET['classe_code'] ?? '';
        if (empty($selectedClasseCode) && !empty($param)) {
            try {
                $selectedClasseCode = $this->validator->decrypter($param);
            } catch (Exception $e) {
                $selectedClasseCode = $param;
            }
        }

        $semestres = $this->model->getActiveSemestres();
        $selectedSemestreCode = $_GET['semestre'] ?? ($_GET['semestre_code'] ?? ($semestres[0]['code_semestre'] ?? ''));

        $selectedSemestreLibelle = $selectedSemestreCode;
        foreach ($semestres as $s) {
            if ($s['code_semestre'] === $selectedSemestreCode) {
                $selectedSemestreLibelle = $s['libelle_semestre'];
                break;
            }
        }

        $pvData = $this->calculatePvClasseData($selectedClasseCode, $selectedSemestreCode);
        if (empty($pvData['classeInfo'])) {
            $this->renderNotFound("Classe introuvable pour la génération du PV.");
            return;
        }

        $dataView = array_merge($pvData, [
            'etablissement' => $this->getEtablissementConfig(),
            'selectedClasseCode' => $selectedClasseCode,
            'selectedSemestreCode' => $selectedSemestreCode,
            'selectedSemestreLibelle' => $selectedSemestreLibelle
        ]);

        $html = PdfService::renderTemplate('pv_classe_pdf.php', $dataView);

        $this->logAction(
            AuditLogger::ACTION_PRINT,
            'bulletins',
            $selectedClasseCode,
            "Impression du PV de délibération de la classe {$selectedClasseCode} ({$selectedSemestreLibelle})",
            ['classe_code' => $selectedClasseCode, 'semestre' => $selectedSemestreCode]
        );

        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $className = preg_replace('/[^A-Za-z0-9_\-]/', '_', $pvData['classeInfo']['libelle_classe'] ?? 'Classe');
        $filename = 'PV_Deliberation_' . $className . '_' . date('Ymd_His') . '.pdf';

        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'PV de Délibération - ' . ($pvData['classeInfo']['libelle_classe'] ?? ''),
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8
        ]);
    }

    public function imprimerRegistrePdf()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        require_once __DIR__ . '/../../core/PdfService.php';

        $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
        if (empty($anneeCode)) {
            $this->renderNotFound("Accès refusé : aucune année académique active autorisée.");
            return;
        }
        $niveauCode = !empty($_GET['niveau_code']) ? trim($_GET['niveau_code']) : null;
        $classeCode = !empty($_GET['classe_code']) ? trim($_GET['classe_code']) : null;
        $semestreCode = !empty($_GET['semestre_code']) ? trim($_GET['semestre_code']) : null;
        $statutNotes = !empty($_GET['statut_notes']) ? trim($_GET['statut_notes']) : null;

        // Vérification de la classe obligatoire pour l'impression du registre
        if (empty($classeCode)) {
            $this->renderNotFound("Veuillez sélectionner une classe pour imprimer le registre des bulletins.");
            return;
        }

        $teacherCtx = $this->getTeacherContext();
        $teacherClasses = null;
        if ($teacherCtx['isTeacherOnly'] && !empty($teacherCtx['teacherCodes'])) {
            require_once __DIR__ . '/../../models/notes/ModelNote.php';
            $clsList = (new ModelNote())->getClassesForTeacher($teacherCtx['teacherCodes'], $anneeCode);
            $teacherClasses = array_column($clsList, 'code_classe');
            if (empty($teacherClasses)) {
                $teacherClasses = ['__NO_ACCESS__'];
            }
        }

        $items = $this->model->getInscriptionsWithNotes(
            $anneeCode,
            $niveauCode,
            $classeCode,
            $semestreCode,
            $teacherClasses,
            $statutNotes
        );

        $anneeLibelle = $this->getActiveAnneeLibelle();
        if (!empty($anneeCode)) {
            $anneeItem = (new ModelAnnee())->getByCode($anneeCode);
            if (!empty($anneeItem['libelle_annee'])) {
                $anneeLibelle = $anneeItem['libelle_annee'];
            }
        }

        $etablissement = $this->getEtablissementConfig();
        $currentUser = Auth::user();
        $editeurNom = trim(($currentUser['prenom_user'] ?? '') . ' ' . ($currentUser['nom_user'] ?? ''));
        if (empty($editeurNom)) {
            $editeurNom = !empty($teacherCtx['isTeacherOnly']) ? 'Enseignant' : 'Direction des Études';
        }

        $filtresLabels = [
            'annee' => $anneeLibelle,
            'niveau' => 'Tous les niveaux',
            'classe' => 'Toutes les classes',
            'semestre' => 'Tous les semestres'
        ];
        if (!empty($niveauCode)) {
            $niv = (new ModelNiveau())->getByCode($niveauCode);
            if ($niv) $filtresLabels['niveau'] = $niv['libelle_niveau'] ?? $niveauCode;
        }
        if (!empty($classeCode)) {
            $cls = (new ModelClasse())->getByCode($classeCode);
            if ($cls) $filtresLabels['classe'] = $cls['libelle_classe'] ?? $classeCode;
        }
        if (!empty($semestreCode)) {
            $sem = (new ModelSemestre())->getByCode($semestreCode);
            if ($sem) $filtresLabels['semestre'] = $sem['libelle_semestre'] ?? $semestreCode;
        }

        $totalInscrits = count($items);
        $totalAvecNotes = 0;
        $totalAdmis = 0;
        $totalSansNotes = 0;
        $sommeMoyennes = 0;
        $nbMoyennes = 0;

        foreach ($items as $it) {
            $nb = (int)($it['nb_notes'] ?? 0);
            $moy = $it['moyenne_semestre'] !== null ? (float)$it['moyenne_semestre'] : null;
            if ($nb > 0) {
                $totalAvecNotes++;
            } else {
                $totalSansNotes++;
            }
            if ($moy !== null) {
                $sommeMoyennes += $moy;
                $nbMoyennes++;
                if ($moy >= 10) {
                    $totalAdmis++;
                }
            }
        }

        $moyenneGlobale = $nbMoyennes > 0 ? round($sommeMoyennes / $nbMoyennes, 2) : 0;
        $tauxAdmis = $nbMoyennes > 0 ? round(($totalAdmis / $nbMoyennes) * 100, 1) : 0;

        $data = [
            'etablissement' => $etablissement,
            'date_impression' => date('d/m/Y H:i'),
            'editeur_nom' => $editeurNom,
            'isTeacherOnly' => !empty($teacherCtx['isTeacherOnly']),
            'filtres_labels' => $filtresLabels,
            'etudiants' => $items,
            'kpis' => [
                'total_inscrits' => $totalInscrits,
                'total_avec_notes' => $totalAvecNotes,
                'total_sans_notes' => $totalSansNotes,
                'total_admis' => $totalAdmis,
                'moyenne_globale' => $moyenneGlobale,
                'taux_admis' => $tauxAdmis
            ]
        ];

        $html = PdfService::renderTemplate('registre_bulletins.php', $data);

        $this->logAction(
            AuditLogger::ACTION_PRINT,
            'bulletins',
            $classeCode,
            "Impression du registre des bulletins pour la classe {$classeCode} ({$filtresLabels['semestre']})",
            ['classe_code' => $classeCode, 'semestre_code' => $semestreCode, 'total_inscrits' => $totalInscrits]
        );

        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $filename = 'Registre_Bulletins_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'Registre des Bulletins - ' . ($etablissement['nom_etablissement'] ?? 'GEICG'),
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8
        ]);
    }
}
