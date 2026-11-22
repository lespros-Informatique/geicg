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
                $classes = (new ModelClasse())->getAll($selectedAnneeCode);
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
        $matricule = !empty($_GET['matricule']) ? trim($_GET['matricule']) : null;

        // Arrêt immédiat si ni classe ni matricule sélectionné, ou aucune année académique autorisée
        if (empty($anneeCode) || (empty($classeCode) && empty($matricule))) {
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
            $statutNotes,
            $matricule
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
            $rowSemCode = !empty($i['code_semestre']) ? $i['code_semestre'] : $semestreCode;

            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'moyenne_num' => $moy,
                'decision' => $decision,
                'semestre_code_selected' => $rowSemCode
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
            'photo_etudiant' => !empty($item['photo_etudiant']) ? $item['photo_etudiant'] : ($item['photo_inscription'] ?? ''),
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
            'redoublant' => (!empty($calc['estRedoublant']) ? 'OUI' : 'NON'),
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
            'code_distinction_sanction' => $calc['code_distinction_sanction'] ?? 'AUCUN',
            'rang' => $calc['rang'] ?? '-',
            'rangFr' => $calc['rangFr'] ?? (($calc['rang'] ?? '-') !== '-' ? ($calc['rang'] . 'ème') : '-'),
            'totalElevesClasse' => $calc['totalElevesClasse'] ?? '-',
            'moyenneMinClasse' => $calc['moyenneMinClasse'] ?? 0,
            'moyenneMaxClasse' => $calc['moyenneMaxClasse'] ?? 0,
            'moyenneMoyClasse' => $calc['moyenneMoyClasse'] ?? 0,
            'est_premiere_annee' => !empty($calc['est_premiere_annee']),
            'est_deuxieme_semestre' => !empty($calc['est_deuxieme_semestre']),
            'moyenneRecap' => $calc['moyenneRecap'] ?? ($calc['moyenneGenerale'] ?? 0),
            'rangRecapFr' => $calc['rangRecapFr'] ?? ($calc['rangFr'] ?? '-'),
            'moyenneMinRecap' => $calc['moyenneMinRecap'] ?? ($calc['moyenneMinClasse'] ?? 0),
            'moyenneMaxRecap' => $calc['moyenneMaxRecap'] ?? ($calc['moyenneMaxClasse'] ?? 0),
            'moyenneMoyRecap' => $calc['moyenneMoyRecap'] ?? ($calc['moyenneMoyClasse'] ?? 0),
            'heures_absences' => $calc['heuresAbsences'] ?? '0 H',
            'moy_semestre1' => $calc['moy_semestre1'] ?? null,
            'moy_semestre2' => $calc['moy_semestre2'] ?? null,
            'moy_examen' => $calc['moy_examen'] ?? null,
            'etablissement' => $config,
            'directeur_etudes' => ($config['nom_directeur_etudes'] ?? '')
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
            $calc['etablissement'] = $this->getEtablissementConfig();
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
                'margin_left' => 6,
                'margin_right' => 6,
                'margin_top' => 6,
                'margin_bottom' => 6,
                'watermark_logo' => true
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

            $inscriptionIds = array_map(fn($it) => (int)$it['id_inscription'], $items);
            $calcsBatch = $this->model->calculateClasseBulletinsBatch($inscriptionIds, $classeCode, $anneeCode, $semestreCode);

            $bulletins = [];
            foreach ($calcsBatch as $calc) {
                $bulletins[] = $this->buildBulletinViewData($calc);
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
                'margin_left' => 6,
                'margin_right' => 6,
                'margin_top' => 6,
                'margin_bottom' => 6,
                'watermark_logo' => true
            ]);
        } catch (Exception $e) {
            error_log("BulletinController::imprimerClasseBulletinsPdf error: " . $e->getMessage());
            $this->renderNotFound("Erreur lors de la génération des bulletins de la classe en PDF : " . $e->getMessage());
        }
    }

    public function formulaire()
    {
        $this->requireAuth();
        redirect('bulletin/list');
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->details($details);
    }

    private function calculatePvClasseData(string $selectedClasseCode, string $selectedSemestreCode, ?string $anneeCode = null): array
    {
        return $this->model->calculatePvClasseData($selectedClasseCode, $selectedSemestreCode, $anneeCode ?: $this->getActiveAnneeCode());
    }

    /**
     * Charge les classes accessibles pour le PV (filtrées par année académique et par rôle enseignant)
     */
    private function getAccessibleClassesForPv(?string $anneeCode, array $teacherCtx): array
    {
        if (empty($anneeCode)) {
            return [];
        }

        if (!empty($teacherCtx['isTeacherOnly'])) {
            if (empty($teacherCtx['teacherCodes'])) {
                return [];
            }
            require_once __DIR__ . '/../../models/notes/ModelNote.php';
            return (new ModelNote())->getClassesForTeacher($teacherCtx['teacherCodes'], $anneeCode);
        }

        $allClasses = (new ModelClasse())->getAllWithDetails($anneeCode);
        return array_values(array_filter($allClasses, function ($c) {
            return empty($c['statut_classe']) || $c['statut_classe'] === STATUTS::ACTIF;
        }));
    }

    /**
     * Résout le contexte commun du PV de classe (année, classes autorisées, semestres, données PV)
     */
    private function resolvePvContext($param = null, bool $defaultFirstClass = true): ?array
    {
        $requestedAnnee = $_GET['annee_code'] ?? null;
        $anneeCode = $this->resolveTargetAcademicYear($requestedAnnee);
        if (empty($anneeCode)) {
            if (!$defaultFirstClass) {
                $this->renderNotFound("Accès refusé : aucune année académique active autorisée.");
                return null;
            }
            return [
                'anneeCode' => null,
                'classes' => [],
                'semestres' => [],
                'selectedClasseCode' => '',
                'selectedSemestreCode' => '',
                'selectedSemestreLibelle' => '-',
                'pvData' => $this->model->calculatePvClasseData('', '', null),
                'isTeacherOnly' => false
            ];
        }

        $teacherCtx = $this->getTeacherContext();
        $isTeacherOnly = !empty($teacherCtx['isTeacherOnly']);
        $classes = $this->getAccessibleClassesForPv($anneeCode, $teacherCtx);

        $selectedClasseCode = trim((string)($_GET['classe_code'] ?? ''));
        if (empty($selectedClasseCode) && !empty($param)) {
            try {
                $decrypted = $this->validator->decrypter($param);
                $selectedClasseCode = (!empty($decrypted) && $decrypted !== false) ? (string)$decrypted : (string)$param;
            } catch (Exception $e) {
                $selectedClasseCode = (string)$param;
            }
        }

        $allowedClassCodes = array_column($classes, 'code_classe');

        if (!empty($selectedClasseCode)) {
            if ($isTeacherOnly) {
                if (!in_array($selectedClasseCode, $allowedClassCodes, true)) {
                    $this->renderForbidden("Accès refusé : vous n'êtes pas autorisé à consulter le PV de cette classe.");
                    return null;
                }
            } elseif (!in_array($selectedClasseCode, $allowedClassCodes, true)) {
                // Si une classe d'une autre année est explicitement demandée sans annee_code dans l'URL, basculer si autorisé
                if (empty($requestedAnnee)) {
                    $clsRow = (new ModelClasse())->getClasseWithDetails($selectedClasseCode);
                    if (!empty($clsRow['annee_code']) && $this->canAccessAcademicYear($clsRow['annee_code'])) {
                        $anneeCode = $clsRow['annee_code'];
                        $classes = $this->getAccessibleClassesForPv($anneeCode, $teacherCtx);
                        $allowedClassCodes = array_column($classes, 'code_classe');
                    } elseif ($defaultFirstClass) {
                        $selectedClasseCode = $classes[0]['code_classe'] ?? '';
                    }
                } elseif ($defaultFirstClass) {
                    $selectedClasseCode = $classes[0]['code_classe'] ?? '';
                }
            }
        } elseif ($defaultFirstClass && !empty($classes)) {
            $selectedClasseCode = $classes[0]['code_classe'] ?? '';
        }

        $semestres = $this->model->getActiveSemestres($anneeCode);
        if (empty($semestres)) {
            $semestres = (new ModelSemestre())->getAll($anneeCode);
        }

        $selectedSemestreCode = trim((string)($_GET['semestre'] ?? ($_GET['semestre_code'] ?? '')));
        $semCodes = array_column($semestres, 'code_semestre');
        if (empty($selectedSemestreCode) || (!empty($semCodes) && !in_array($selectedSemestreCode, $semCodes, true))) {
            $selectedSemestreCode = $semestres[0]['code_semestre'] ?? '';
        }

        $selectedSemestreLibelle = '-';
        foreach ($semestres as $s) {
            if (($s['code_semestre'] ?? '') === $selectedSemestreCode) {
                $selectedSemestreLibelle = $s['libelle_semestre'] ?: $selectedSemestreCode;
                break;
            }
        }

        $pvData = $this->calculatePvClasseData($selectedClasseCode, $selectedSemestreCode, $anneeCode);

        return [
            'anneeCode' => $anneeCode,
            'classes' => $classes,
            'semestres' => $semestres,
            'selectedClasseCode' => $selectedClasseCode,
            'selectedSemestreCode' => $selectedSemestreCode,
            'selectedSemestreLibelle' => $selectedSemestreLibelle,
            'pvData' => $pvData,
            'isTeacherOnly' => $isTeacherOnly
        ];
    }

    public function pvClasse($param = null)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::VIEW_NOTES]);

        $ctx = $this->resolvePvContext($param, true);
        if ($ctx === null) {
            return;
        }

        $selectedClasseCode = $ctx['selectedClasseCode'];
        $selectedAnneeCode = $ctx['anneeCode'] ?? '';
        $encryptedClasseCode = !empty($selectedClasseCode) ? $this->validator->crypter($selectedClasseCode) : '';
        $canAccessDeliberations = Auth::can([PERMISSIONS::VIEW_DELIBERATIONS, PERMISSIONS::MANAGE_DELIBERATIONS]) && empty($ctx['isTeacherOnly']);

        $this->loadView('../views/bulletin/pv_classe.php', array_merge($ctx['pvData'], [
            'etablissement' => $this->getEtablissementConfig(),
            'classes' => $ctx['classes'],
            'semestres' => $ctx['semestres'],
            'selectedAnneeCode' => $selectedAnneeCode,
            'selectedClasseCode' => $selectedClasseCode,
            'selectedSemestreCode' => $ctx['selectedSemestreCode'],
            'selectedSemestreLibelle' => $ctx['selectedSemestreLibelle'],
            'encryptedClasseCode' => $encryptedClasseCode,
            'isTeacherOnly' => $ctx['isTeacherOnly'],
            'canAccessDeliberations' => $canAccessDeliberations
        ]));
    }

    public function imprimerPvPdf($param = null)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::GENERATE_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        require_once __DIR__ . '/../../core/PdfService.php';

        $ctx = $this->resolvePvContext($param, false);
        if ($ctx === null) {
            return;
        }

        $selectedClasseCode = $ctx['selectedClasseCode'];
        $selectedSemestreCode = $ctx['selectedSemestreCode'];
        $selectedSemestreLibelle = $ctx['selectedSemestreLibelle'];
        $pvData = $ctx['pvData'];

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
            ['classe_code' => $selectedClasseCode, 'semestre' => $selectedSemestreCode, 'annee_code' => $ctx['anneeCode']]
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
            'margin_bottom' => 8,
            'watermark_logo' => true,
            'watermark_alpha' => 0.05
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

    /**
     * Valide et fige officiellement la délibération annuelle de toute une classe
     */
    public function validerDeliberation()
    {
        $this->requireAuth();
        $this->requirePost(true);
        $this->requirePermission(PERMISSIONS::MANAGE_DELIBERATIONS);

        $teacherCtx = $this->getTeacherContext();
        if (!empty($teacherCtx['isTeacherOnly'])) {
            $this->error("Accès refusé : seuls les membres autorisés de la direction / du jury peuvent valider une délibération.");
            return;
        }

        $classeCode = trim((string)($_POST['classe_code'] ?? ''));
        $anneeCode = trim((string)($_POST['annee_code'] ?? ''));
        if ($anneeCode === '') {
            $anneeCode = (string)($this->resolveTargetAcademicYear(null) ?? '');
        }
        $preserveManual = !isset($_POST['preserve_manual']) || (string)$_POST['preserve_manual'] !== '0';

        if ($classeCode === '' || $anneeCode === '') {
            $this->error("Veuillez sélectionner une classe et une année académique valides.");
            return;
        }

        $this->requireAcademicYearWriteAccess($anneeCode);

        $delibModel = new ModelDeliberation();
        $res = $delibModel->delibererClasse(
            $classeCode,
            $anneeCode,
            (string)Auth::code(),
            (string)Session::get('etablissement_code', ''),
            $preserveManual
        );

        if (empty($res['success'])) {
            $this->error($res['message'] ?? "Impossible de valider la délibération.");
            return;
        }

        $this->logAction(
            AuditLogger::ACTION_UPDATE,
            'deliberations',
            $classeCode,
            "Validation officielle de la délibération annuelle pour la classe {$classeCode} ({$anneeCode}) : " . ($res['count_saved'] ?? 0) . " étudiant(s) figé(s).",
            [
                'classe_code' => $classeCode,
                'annee_code' => $anneeCode,
                'count_saved' => $res['count_saved'] ?? 0,
                'count_admis' => $res['count_admis'] ?? 0,
                'count_ajournes' => $res['count_ajournes'] ?? 0,
                'preserve_manual' => $preserveManual
            ]
        );

        $this->success($res['message'], [
            'summary' => $delibModel->getClasseDeliberationSummary($classeCode, $anneeCode)
        ]);
    }

    /**
     * Modifie manuellement la décision officielle du jury pour un étudiant (rachat, redoublement, exclusion, observation)
     */
    public function modifierDecisionJury()
    {
        $this->requireAuth();
        $this->requirePost(true);
        $this->requirePermission(PERMISSIONS::MANAGE_DELIBERATIONS);

        $teacherCtx = $this->getTeacherContext();
        if (!empty($teacherCtx['isTeacherOnly'])) {
            $this->error("Accès refusé : seuls les membres autorisés de la direction / du jury peuvent modifier une décision.");
            return;
        }

        $inscriptionCode = trim((string)($_POST['inscription_code'] ?? ''));
        $decisionFinale = trim((string)($_POST['decision_finale'] ?? ''));
        $libelleDecision = trim((string)($_POST['libelle_decision'] ?? ''));
        $estRachete = (!empty($_POST['est_rachete']) && (string)$_POST['est_rachete'] !== '0') ? 1 : 0;
        $observationJury = isset($_POST['observation_jury']) ? trim((string)$_POST['observation_jury']) : null;

        if ($inscriptionCode === '') {
            $this->error("Code d'inscription manquant.");
            return;
        }

        if (!in_array($decisionFinale, STATUTS::DECISIONS_DELIBERATION, true)) {
            $this->error("Décision du jury non valide.");
            return;
        }

        // Un rachat implique logiquement une décision 'admis'
        if ($decisionFinale !== 'admis') {
            $estRachete = 0;
        }

        $delibModel = new ModelDeliberation();
        $existing = $delibModel->getByInscription($inscriptionCode);
        if (!$existing) {
            $this->error("Veuillez d'abord valider la délibération annuelle de la classe avant d'ajuster une décision individuelle.");
            return;
        }

        $this->requireAcademicYearWriteAccess((string)$existing['annee_code']);

        $ok = $delibModel->updateDecisionIndividuelle(
            $inscriptionCode,
            $decisionFinale,
            $libelleDecision,
            $estRachete,
            $observationJury,
            (string)Auth::code()
        );

        if (!$ok) {
            $this->error("Erreur lors de l'enregistrement de la décision du jury.");
            return;
        }

        $this->logAction(
            AuditLogger::ACTION_UPDATE,
            'deliberations',
            $inscriptionCode,
            "Décision individuelle du jury mise à jour pour {$inscriptionCode} : {$decisionFinale}" . ($estRachete ? " (racheté)" : ""),
            [
                'inscription_code' => $inscriptionCode,
                'classe_code' => $existing['classe_code'],
                'annee_code' => $existing['annee_code'],
                'decision_finale' => $decisionFinale,
                'libelle_decision' => $libelleDecision,
                'est_rachete' => $estRachete,
                'observation_jury' => $observationJury
            ]
        );

        $this->success("Décision officielle du jury enregistrée avec succès.", [
            'deliberation' => $delibModel->getByInscription($inscriptionCode)
        ]);
    }

    /**
     * Réouvre la délibération annuelle d'une classe en statut 'provisoire' (avant clôture de l'année)
     */
    public function reouvrirDeliberation()
    {
        $this->requireAuth();
        $this->requirePost(true);
        $this->requirePermission(PERMISSIONS::MANAGE_DELIBERATIONS);

        $teacherCtx = $this->getTeacherContext();
        if (!empty($teacherCtx['isTeacherOnly'])) {
            $this->error("Accès refusé : seuls les membres autorisés de la direction / du jury peuvent rouvrir une délibération.");
            return;
        }

        $classeCode = trim((string)($_POST['classe_code'] ?? ''));
        $anneeCode = trim((string)($_POST['annee_code'] ?? ''));
        if ($anneeCode === '') {
            $anneeCode = (string)($this->resolveTargetAcademicYear(null) ?? '');
        }

        if ($classeCode === '' || $anneeCode === '') {
            $this->error("Classe et année académique requises.");
            return;
        }

        $this->requireAcademicYearWriteAccess($anneeCode);

        $delibModel = new ModelDeliberation();
        $ok = $delibModel->reouvrirClasse($classeCode, $anneeCode, (string)Auth::code());
        if (!$ok) {
            $this->error("Impossible de rouvrir la délibération de cette classe.");
            return;
        }

        $this->logAction(
            AuditLogger::ACTION_UPDATE,
            'deliberations',
            $classeCode,
            "Réouverture (statut provisoire) de la délibération annuelle pour la classe {$classeCode} ({$anneeCode}).",
            ['classe_code' => $classeCode, 'annee_code' => $anneeCode]
        );

        $this->success("Délibération repassée en statut provisoire. Les décisions figées ne sont plus actives tant que la classe n'est pas revalidée.");
    }

    /**
     * Espace dédié : Conseil de Classe & Délibération Annuelle (étude individuelle cas par cas)
     */
    public function deliberationConseil($param = null)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_DELIBERATIONS, PERMISSIONS::MANAGE_DELIBERATIONS]);

        $annees = $this->getAccessibleAnnees();
        $requestedAnnee = $_GET['annee_code'] ?? null;
        $selectedAnneeCode = $this->resolveTargetAcademicYear($requestedAnnee);

        $teacherCtx = $this->getTeacherContext();
        $isTeacherOnly = !empty($teacherCtx['isTeacherOnly']);

        $classes = !empty($selectedAnneeCode)
            ? $this->getAccessibleClassesForPv($selectedAnneeCode, $teacherCtx)
            : [];

        $selectedClasseCode = trim((string)($_GET['classe_code'] ?? ''));
        if ($selectedClasseCode === '' && !empty($param)) {
            try {
                $dec = $this->validator->decrypter($param);
                $selectedClasseCode = (!empty($dec) && $dec !== false) ? (string)$dec : (string)$param;
            } catch (Exception $e) {
                $selectedClasseCode = (string)$param;
            }
        }

        $allowedCodes = array_column($classes, 'code_classe');
        if ($selectedClasseCode !== '' && !in_array($selectedClasseCode, $allowedCodes, true)) {
            if ($isTeacherOnly) {
                $this->renderForbidden("Accès refusé : vous n'êtes pas autorisé à consulter la délibération de cette classe.");
                return;
            }
            if (empty($requestedAnnee)) {
                $clsRow = (new ModelClasse())->getClasseWithDetails($selectedClasseCode);
                if (!empty($clsRow['annee_code']) && $this->canAccessAcademicYear($clsRow['annee_code'])) {
                    $selectedAnneeCode = $clsRow['annee_code'];
                    $classes = $this->getAccessibleClassesForPv($selectedAnneeCode, $teacherCtx);
                    $allowedCodes = array_column($classes, 'code_classe');
                } else {
                    $selectedClasseCode = $classes[0]['code_classe'] ?? '';
                }
            } else {
                $selectedClasseCode = $classes[0]['code_classe'] ?? '';
            }
        } elseif ($selectedClasseCode === '' && !empty($classes)) {
            $selectedClasseCode = $classes[0]['code_classe'] ?? '';
        }

        $delibModel = new ModelDeliberation();
        $conseilData = (!empty($selectedClasseCode) && !empty($selectedAnneeCode))
            ? $delibModel->getConseilClasseData($selectedClasseCode, $selectedAnneeCode)
            : $delibModel->getConseilClasseData('', '');

        // Ajouter l'identifiant d'inscription chiffré pour ouvrir le bulletin détaillé d'un étudiant en 1 clic
        foreach ($conseilData['rows'] as &$r) {
            $r['encrypted_id_inscription'] = !empty($r['id_inscription'])
                ? $this->validator->crypter((string)$r['id_inscription'])
                : '';
        }
        unset($r);

        $encryptedClasseCode = !empty($selectedClasseCode) ? $this->validator->crypter($selectedClasseCode) : '';
        $canManageDeliberations = Auth::can(PERMISSIONS::MANAGE_DELIBERATIONS) && !$isTeacherOnly;
        $canWriteYear = !empty($selectedAnneeCode) && $this->canWriteAcademicYear($selectedAnneeCode);

        $this->loadView('../views/bulletin/deliberation_conseil.php', [
            'etablissement' => $this->getEtablissementConfig(),
            'annees' => $annees,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode ?? '',
            'selectedClasseCode' => $selectedClasseCode,
            'encryptedClasseCode' => $encryptedClasseCode,
            'classeInfo' => $conseilData['classeInfo'],
            'anneeLibelle' => $conseilData['anneeLibelle'],
            'estPremiereAnnee' => $conseilData['est_premiere_annee'],
            'lastSemestreCode' => $conseilData['lastSemestreCode'],
            'summary' => $conseilData['summary'],
            'rows' => $conseilData['rows'],
            'canManageDeliberations' => $canManageDeliberations,
            'canWriteYear' => $canWriteYear
        ]);
    }

    /**
     * Enregistre les décisions individuelles du Conseil (mode 'provisoire' = brouillon, mode 'valide' = clôture officielle)
     */
    public function enregistrerDeliberation()
    {
        $this->requireAuth();
        $this->requirePost(true);
        $this->requirePermission(PERMISSIONS::MANAGE_DELIBERATIONS);

        $teacherCtx = $this->getTeacherContext();
        if (!empty($teacherCtx['isTeacherOnly'])) {
            $this->error("Accès refusé : seuls les membres autorisés du conseil / de la direction peuvent enregistrer une délibération.");
            return;
        }

        $classeCode = trim((string)($_POST['classe_code'] ?? ''));
        $anneeCode = trim((string)($_POST['annee_code'] ?? ''));
        if ($anneeCode === '') {
            $anneeCode = (string)($this->resolveTargetAcademicYear(null) ?? '');
        }
        $mode = trim((string)($_POST['mode'] ?? 'provisoire'));
        if (!in_array($mode, ['provisoire', STATUTS::VALIDE], true)) {
            $mode = 'provisoire';
        }

        if ($classeCode === '' || $anneeCode === '') {
            $this->error("Classe et année académique requises.");
            return;
        }

        $this->requireAcademicYearWriteAccess($anneeCode);

        $rawDecisions = $_POST['decisions'] ?? '[]';
        $decisionsInput = is_string($rawDecisions) ? json_decode($rawDecisions, true) : $rawDecisions;
        if (!is_array($decisionsInput)) {
            $this->error("Format des décisions invalide.");
            return;
        }

        $delibModel = new ModelDeliberation();
        $res = $delibModel->saveSessionDeliberation(
            $classeCode,
            $anneeCode,
            $decisionsInput,
            $mode,
            (string)Auth::code(),
            (string)Session::get('etablissement_code', '')
        );

        if (empty($res['success'])) {
            $this->error($res['message'] ?? "Erreur lors de l'enregistrement de la délibération.");
            return;
        }

        $actionLabel = ($mode === STATUTS::VALIDE)
            ? "Clôture et validation officielle de la délibération du conseil"
            : "Enregistrement en brouillon de la délibération du conseil";

        $this->logAction(
            AuditLogger::ACTION_UPDATE,
            'deliberations',
            $classeCode,
            "{$actionLabel} pour la classe {$classeCode} ({$anneeCode})",
            [
                'classe_code' => $classeCode,
                'annee_code' => $anneeCode,
                'mode' => $mode,
                'counts' => $res['counts'] ?? []
            ]
        );

        $this->success($res['message'], [
            'statut_deliberation' => $res['statut_deliberation'],
            'counts' => $res['counts'] ?? [],
            'summary' => $delibModel->getClasseDeliberationSummary($classeCode, $anneeCode)
        ]);
    }

    /**
     * Génère le PV Officiel de Délibération Annuelle du Conseil en PDF (A4 Paysage)
     */
    public function imprimerDeliberationPdf($param = null)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_DELIBERATIONS, PERMISSIONS::MANAGE_DELIBERATIONS]);
        require_once __DIR__ . '/../../core/PdfService.php';

        $selectedAnneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
        if (empty($selectedAnneeCode)) {
            $this->renderNotFound("Accès refusé : aucune année académique active autorisée.");
            return;
        }

        $selectedClasseCode = trim((string)($_GET['classe_code'] ?? ''));
        if ($selectedClasseCode === '' && !empty($param)) {
            try {
                $dec = $this->validator->decrypter($param);
                $selectedClasseCode = (!empty($dec) && $dec !== false) ? (string)$dec : (string)$param;
            } catch (Exception $e) {
                $selectedClasseCode = (string)$param;
            }
        }

        $teacherCtx = $this->getTeacherContext();
        if (!empty($teacherCtx['isTeacherOnly'])) {
            $allowed = array_column($this->getAccessibleClassesForPv($selectedAnneeCode, $teacherCtx), 'code_classe');
            if (!in_array($selectedClasseCode, $allowed, true)) {
                $this->renderForbidden("Accès refusé à cette classe.");
                return;
            }
        }

        $delibModel = new ModelDeliberation();
        $conseilData = $delibModel->getConseilClasseData($selectedClasseCode, $selectedAnneeCode);
        if (empty($conseilData['classeInfo'])) {
            $this->renderNotFound("Classe introuvable pour l'impression du PV de délibération annuelle.");
            return;
        }

        $dataView = array_merge($conseilData, [
            'etablissement' => $this->getEtablissementConfig(),
            'selectedClasseCode' => $selectedClasseCode,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);

        $html = PdfService::renderTemplate('pv_deliberation_annuelle_pdf.php', $dataView);

        $this->logAction(
            AuditLogger::ACTION_PRINT,
            'deliberations',
            $selectedClasseCode,
            "Impression du PV Officiel de Délibération Annuelle pour la classe {$selectedClasseCode} ({$selectedAnneeCode})",
            ['classe_code' => $selectedClasseCode, 'annee_code' => $selectedAnneeCode]
        );

        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $className = preg_replace('/[^A-Za-z0-9_\-]/', '_', $conseilData['classeInfo']['libelle_classe'] ?? 'Classe');
        $filename = 'PV_Deliberation_Annuelle_' . $className . '_' . date('Ymd_His') . '.pdf';

        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'PV de Délibération Annuelle - ' . ($conseilData['classeInfo']['libelle_classe'] ?? ''),
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'watermark_logo' => true,
            'watermark_alpha' => 0.05
        ]);
    }
}
