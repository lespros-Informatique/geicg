<?php

class ImpayesController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelImpayes();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_IMPAYES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);

        $annees = $this->getAccessibleAnnees();
        $selectedAnneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);

        if ($selectedAnneeCode === null) {
            $this->loadView('../views/impayes/list.php', [
                'annees' => $annees,
                'niveaux' => [],
                'classes' => [],
                'selectedAnneeCode' => '',
                'canSendRelance' => false,
                'canRecordPayment' => false
            ]);
            return;
        }

        $niveaux = (new ModelNiveau())->getAll();
        $classes = (new ModelClasse())->getAll($selectedAnneeCode);

        $canSendRelance = $this->hasPermission([PERMISSIONS::SEND_RELANCES, PERMISSIONS::MANAGE_IMPAYES]) && $this->canWriteAcademicYear($selectedAnneeCode);
        $canRecordPayment = $this->hasPermission([PERMISSIONS::RECORD_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]) && $this->canWriteAcademicYear($selectedAnneeCode);

        $modelSessionCaisse = new ModelSessionCaisse();
        $activeSession = $modelSessionCaisse->getActiveSession();
        $isCaisseOuverte = !empty($activeSession);

        $this->loadView('../views/impayes/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode,
            'canSendRelance' => $canSendRelance,
            'canRecordPayment' => $canRecordPayment,
            'isCaisseOuverte' => $isCaisseOuverte
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_IMPAYES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);

        $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
        if ($anneeCode === null) {
            $this->json([
                'status' => 1,
                'success' => true,
                'data' => [],
                'kpis' => []
            ]);
            return;
        }

        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $severite = $_GET['severite'] ?? 'ALL';

        $result = $this->model->getOverdueStudentsDetailed($anneeCode, $niveauCode, $classeCode, $severite);
        $rawList = $result['list'] ?? [];
        $data = [];

        foreach ($rawList as $item) {
            $id = $item['id_inscription'];
            $idCrypte = $this->validator->crypter($id);
            $codeCrypte = $this->validator->crypter($item['code_inscription']);

            $data[] = array_merge($item, [
                'encrypted_id' => $idCrypte,
                'encrypted_code' => $codeCrypte
            ]);
        }

        $this->json([
            'status' => 1,
            'success' => true,
            'data' => $data,
            'kpis' => $result['kpis'] ?? []
        ]);
    }

    public function apiHistorique()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_IMPAYES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);

        $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
        if ($anneeCode === null) {
            $this->json([
                'status' => 1,
                'success' => true,
                'data' => []
            ]);
            return;
        }

        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $canal = $_GET['canal_relance'] ?? 'ALL';

        $rows = $this->model->getHistoriqueRelances($anneeCode, $niveauCode, $classeCode, $canal);
        $data = [];
        foreach ($rows as $row) {
            $data[] = array_merge($row, [
                'encrypted_id' => $this->validator->crypter($row['id_relance']),
                'encrypted_inscription_id' => !empty($row['id_inscription']) ? $this->validator->crypter($row['id_inscription']) : '',
                'nom_complet' => trim(strtoupper($row['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($row['prenom_etudiant'] ?? '')))
            ]);
        }

        $this->json([
            'status' => 1,
            'success' => true,
            'data' => $data
        ]);
    }

    public function details($param)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_IMPAYES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);

        try {
            $id = $this->validator->decrypter($param);
        } catch (Exception $e) {
            $id = $param;
        }

        $item = $this->model->getRelanceDetailsByIdOrCode($id);
        if (!$item && (string)$id !== (string)$param) {
            $item = $this->model->getRelanceDetailsByIdOrCode($param);
        }
        if (!$item) {
            $this->renderNotFound("Fiche de relance introuvable.");
            return;
        }

        if (!empty($item['annee_code']) && !$this->canAccessAcademicYear($item['annee_code'])) {
            $this->renderNotFound("Accès non autorisé à l'historique de cette année académique.");
            return;
        }

        $encryptedInscriptionId = !empty($item['id_inscription']) ? $this->validator->crypter($item['id_inscription']) : '';

        $this->loadView('../views/impayes/details.php', [
            'item' => $item,
            'encryptedInscriptionId' => $encryptedInscriptionId
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::SEND_RELANCES, PERMISSIONS::MANAGE_IMPAYES]);

        $userCode = Auth::code();
        $anneeCode = $this->resolveTargetAcademicYear($_POST['annee_code'] ?? null);
        if ($anneeCode === null || !$this->canWriteAcademicYear($anneeCode)) {
            $this->error("Action d'enregistrement de relance non autorisée sur cette année académique.");
            return;
        }

        $etabCode = $this->getActiveEtablissementCode();
        $etab = $this->getEtablissementConfig(true);

        $data = $_POST;
        unset($data['csrf_token']);

        $allowedCanaux = ['sms', 'whatsapp', 'email', 'appel', 'recu_pdf'];
        $canal = strtolower(trim((string)($data['canal_relance'] ?? 'sms')));
        if (!in_array($canal, $allowedCanaux, true)) {
            $canal = 'sms';
        }
        $data['canal_relance'] = $canal;

        $allowedNiveaux = ['rappel_amiable', 'relance_ferme', 'mise_en_demeure'];
        $niveau = strtolower(trim((string)($data['niveau_relance'] ?? 'rappel_amiable')));
        if (!in_array($niveau, $allowedNiveaux, true)) {
            $niveau = 'rappel_amiable';
        }
        $data['niveau_relance'] = $niveau;

        $destinataire = trim((string)($data['telephone_destinataire'] ?? ''));
        $messageRelance = trim((string)($data['message_relance'] ?? ''));
        if ($messageRelance === '') {
            $this->error("Le message de relance ne peut pas être vide.");
            return;
        }

        if ($canal === 'email' && !filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
            $this->error("Veuillez saisir une adresse e-mail valide pour le canal Email.");
            return;
        }

        $prefix = ($canal === 'recu_pdf') ? 'RAP-' : 'REL-';
        if (empty($data['code_relance'])) {
            $data['code_relance'] = $this->validator->generateCode(TABLES::RELANCES_IMPAYES, 'code_relance', $prefix, 8);
        }

        $ins = !empty($data['inscription_code'])
            ? $this->model->getInscriptionDetailsForRappel($data['inscription_code'])
            : null;
        if ($ins && !empty($ins['annee_code'])) {
            if (!$this->canWriteAcademicYear($ins['annee_code'])) {
                $this->error("Action d'enregistrement de relance non autorisée sur l'année de cette inscription.");
                return;
            }
            $anneeCode = $ins['annee_code'];
        }

        $statutRelance = 'envoye';
        $emailNotice = '';
        $emailSent = null;

        // Envoi SMTP réel pour le canal Email (avec option de pièce jointe PDF du rappel)
        if ($canal === 'email' && !empty($_POST['send_email_now'])) {
            require_once __DIR__ . '/../../core/MailerService.php';
            $attachments = [];
            $tmpPdfPath = null;

            if (!empty($_POST['attach_pdf']) && $ins) {
                try {
                    require_once __DIR__ . '/../../core/PdfService.php';
                    $delaiRigueur = $this->resolveDelaiRigueurScolarite($etab);
                    $dataViewPdf = $this->buildRappelPdfDataView($ins, $etab, $data['code_relance'], $delaiRigueur);
                    $htmlPdf = PdfService::renderTemplate('rappel_scolarite.php', $dataViewPdf);
                    $tmpPdfPath = rtrim(sys_get_temp_dir(), '/\\') . '/rappel_' . preg_replace('/[^A-Za-z0-9_-]/', '', $data['code_relance']) . '_' . uniqid() . '.pdf';
                    PdfService::generate($htmlPdf, $tmpPdfPath, [
                        'orientation' => 'P',
                        'format' => 'A4',
                        'title' => 'Rappel de Scolarité - ' . ($dataViewPdf['nom_prenom_etudiant'] ?? ''),
                        'watermark_logo' => true,
                        'watermark_alpha' => 0.05,
                        'destination' => \Mpdf\Output\Destination::FILE
                    ]);
                    if (file_exists($tmpPdfPath)) {
                        $attachments[] = [
                            'path' => $tmpPdfPath,
                            'name' => 'Rappel_Scolarite_' . ($ins['matricule_etudiant'] ?? $data['code_relance']) . '.pdf'
                        ];
                    }
                } catch (Throwable $e) {
                    error_log("ImpayesController::add PDF attachment error: " . $e->getMessage());
                }
            }

            $niveauLabels = [
                'rappel_amiable' => 'Rappel Amiable de Scolarité',
                'relance_ferme' => 'Relance Ferme de Scolarité',
                'mise_en_demeure' => 'Mise en Demeure — Scolarité'
            ];
            $etabTitle = trim((string)($etab['sigle_etablissement'] ?? ($etab['nom_etablissement'] ?? '')));
            $subject = ($etabTitle !== '' ? $etabTitle . ' — ' : '') . ($niveauLabels[$niveau] ?? 'Rappel de Scolarité');
            $htmlBody = $this->buildRelanceEmailHtml($etab, $ins, $messageRelance, (float)($data['montant_impaye'] ?? 0), $data['code_relance']);

            $mailRes = MailerService::send($destinataire, $subject, $htmlBody, $messageRelance, $attachments);
            if ($tmpPdfPath && file_exists($tmpPdfPath)) {
                @unlink($tmpPdfPath);
            }

            $emailSent = !empty($mailRes['status']);
            if (!$emailSent) {
                $statutRelance = 'en_attente';
                $emailNotice = " (Relance journalisée en attente : " . ($mailRes['message'] ?? "échec SMTP") . ")";
            } else {
                $emailNotice = " et e-mail transmis à {$destinataire}";
            }
        }

        $data['telephone_destinataire'] = $destinataire;
        $data['message_relance'] = $messageRelance;
        $data['statut_relance'] = $statutRelance;
        $data['created_at_relance'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['annee_code'] = $anneeCode;
        $data['etablissement_code'] = $etabCode;

        $filteredData = $this->model->filterData($data);

        if ($this->model->create($filteredData)) {
            $this->logAction(
                AuditLogger::ACTION_CREATE,
                'relances_impayes',
                $data['code_relance'],
                "Relance d'impayé enregistrée ({$canal} / {$niveau}) pour " . ($data['etudiant_code'] ?? ''),
                [
                    'code_relance' => $data['code_relance'],
                    'canal_relance' => $canal,
                    'niveau_relance' => $niveau,
                    'montant_impaye' => (float)($data['montant_impaye'] ?? 0),
                    'statut_relance' => $statutRelance
                ]
            );

            $responsePayload = [
                'code_relance' => $data['code_relance'],
                'canal_relance' => $canal,
                'statut_relance' => $statutRelance,
                'email_sent' => $emailSent
            ];

            if ($canal === 'recu_pdf' && $ins && !empty($ins['id_inscription'])) {
                $encInsId = $this->validator->crypter($ins['id_inscription']);
                $responsePayload['pdf_url'] = url('impayes/imprimerRappelPdf/' . $encInsId) . '?reprint=1&code_relance=' . urlencode($data['code_relance']);
            } elseif ($canal === 'whatsapp') {
                $digits = preg_replace('/\D+/', '', $destinataire);
                if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
                    $digits = '225' . $digits;
                }
                if ($digits !== '') {
                    $responsePayload['whatsapp_url'] = 'https://wa.me/' . $digits . '?text=' . rawurlencode($messageRelance);
                }
            }

            $this->success("Relance d'impayé enregistrée ({$data['code_relance']}){$emailNotice} !", $responsePayload);
        } else {
            $this->error("Erreur lors de l'enregistrement de la relance.");
        }
    }

    public function addGrouped()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::SEND_RELANCES, PERMISSIONS::MANAGE_IMPAYES]);

        $userCode = Auth::code();
        $anneeCode = $this->resolveTargetAcademicYear($_POST['annee_code'] ?? null);
        if ($anneeCode === null || !$this->canWriteAcademicYear($anneeCode)) {
            $this->error("Action de relance groupée non autorisée sur cette année académique.");
            return;
        }

        $etabCode = $this->getActiveEtablissementCode();

        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true);
        if (!is_array($items) || empty($items)) {
            $this->error('Aucun étudiant sélectionné pour la relance.');
            return;
        }

        $niveauRelance = $_POST['niveau_relance'] ?? 'rappel_amiable';
        $canalRelance = $_POST['canal_relance'] ?? 'sms';
        $messageTemplate = $_POST['message_relance'] ?? '';

        $savedCount = 0;
        foreach ($items as $item) {
            $codeRelance = $this->validator->generateCode(TABLES::RELANCES_IMPAYES, 'code_relance', 'REL-', 8);
            
            $msg = $messageTemplate;
            $msg = str_replace('{NOM_ETUDIANT}', $item['student_name'] ?? '', $msg);
            $msg = str_replace('{MONTANT_ECHU}', number_format((float)($item['montant_echu'] ?? 0), 0, ',', ' ') . ' FCFA', $msg);
            $msg = str_replace('{CLASSE}', $item['classe'] ?? '', $msg);

            $data = [
                'code_relance' => $codeRelance,
                'etudiant_code' => $item['etudiant_code'] ?? '',
                'inscription_code' => $item['inscription_code'] ?? '',
                'niveau_relance' => $niveauRelance,
                'canal_relance' => $canalRelance,
                'montant_impaye' => (float)($item['montant_echu'] ?? 0),
                'telephone_destinataire' => $item['phone'] ?? '',
                'message_relance' => $msg,
                'statut_relance' => 'envoye',
                'user_code' => $userCode,
                'annee_code' => $anneeCode,
                'etablissement_code' => $etabCode,
                'created_at_relance' => date('Y-m-d H:i:s')
            ];

            $filteredData = $this->model->filterData($data);
            if ($this->model->create($filteredData)) {
                $savedCount++;
            }
        }

        if ($savedCount > 0) {
            $this->logAction(
                AuditLogger::ACTION_CREATE,
                'relances_impayes',
                'GROUPE-' . date('YmdHis'),
                "Relance groupée enregistrée ({$canalRelance}) pour {$savedCount} étudiant(s)",
                ['canal_relance' => $canalRelance, 'niveau_relance' => $niveauRelance, 'count' => $savedCount]
            );
            $this->success("Relance groupée transmise et journalisée avec succès pour {$savedCount} destinataire(s)!");
        } else {
            $this->error("Erreur lors de l'enregistrement de la relance groupée");
        }
    }

    public function imprimerListeImpayes()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_IMPAYES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);
        require_once __DIR__ . '/../../core/PdfService.php';

        $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);
        if ($anneeCode === null) {
            $this->error("Accès non autorisé aux impayés de cette année académique.");
            return;
        }

        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $severite = $_GET['severite'] ?? 'ALL';

        $result = $this->model->getOverdueStudentsDetailed($anneeCode, $niveauCode, $classeCode, $severite);

        $anneeLibelle = 'Toutes';
        if (!empty($anneeCode)) {
            $anneeLibelle = $this->model->getAnneeLibelle($anneeCode);
        }

        $niveauLibelle = 'Tous';
        if (!empty($niveauCode) && $niveauCode !== 'ALL') {
            $niveauLibelle = $this->model->getNiveauLibelle($niveauCode);
        }

        $classeLibelle = 'Toutes';
        if (!empty($classeCode) && $classeCode !== 'ALL') {
            $classeLibelle = $this->model->getClasseLibelle($classeCode);
        }

        $severiteLibelle = 'Tous les retards';
        if ($severite === 'leger') $severiteLibelle = 'Retard Léger (1-15j)';
        elseif ($severite === 'modere') $severiteLibelle = 'Retard Modéré (16-30j)';
        elseif ($severite === 'critique') $severiteLibelle = 'Retard Critique (>30j)';

        $dataView = [
            'etablissement' => $this->getEtablissementConfig(),
            'list' => $result['list'] ?? [],
            'kpis' => $result['kpis'] ?? [],
            'annee_libelle' => $anneeLibelle,
            'niveau_libelle' => $niveauLibelle,
            'classe_libelle' => $classeLibelle,
            'severite_libelle' => $severiteLibelle
        ];

        $html = PdfService::renderTemplate('liste_impayes.php', $dataView);
        $filename = 'Liste_Impayes_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'Liste des Impayés & Retards de Versements'
        ]);
    }

    public function imprimerRappelPdf($param)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_IMPAYES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);
        require_once __DIR__ . '/../../core/PdfService.php';
        require_once __DIR__ . '/../../models/frais_annexes/ModelFraisAnnexe.php';

        try {
            $id = $this->validator->decrypter($param);
        } catch (Exception $e) {
            $id = $param;
        }

        $ins = $this->model->getInscriptionDetailsForRappel($id);
        if (!$ins) {
            $this->error('Inscription introuvable pour ce rappel.');
            return;
        }

        if (!empty($ins['annee_code']) && !$this->canAccessAcademicYear($ins['annee_code'])) {
            $this->error("Accès non autorisé aux informations financières de cette année académique.");
            return;
        }

        // Paiements via modèle
        $totalPaye = $this->model->getTotalPayeByInscription($ins['code_inscription']);

        $modelFA = new ModelFraisAnnexe();
        $typeFiliere = $ins['type_filiere'] ?? 'TERTIAIRE';
        $fraisAnnexesDus = (float)$modelFA->getMontantByTypeFiliere($typeFiliere, $ins['annee_code'], $ins['niveau_code'], 'inscription');

        // Scolarité due via modèle (avec affectation_etat)
        $scolariteDue = $this->model->getScolariteDue($ins['filiere_code'], $ins['niveau_code'], $ins['annee_code'], $ins['affectation_etat'] ?? null);
        if ($scolariteDue === null) {
            $scolariteDue = (float)$ins['montant_scolarite_inscription'];
        }

        $totalAttendu = $scolariteDue;
        $restePayer = max(0, $totalAttendu - $totalPaye);
        $isAffecte = ($ins['affectation_etat'] === 'affecte' || $ins['affectation_etat'] === 'oui');

        $nomParent = '-';
        $contactParent = $ins['telephone_etudiant'] ?? '-';
        if (!empty($ins['nom_tuteur'])) {
            $nomParent = trim($ins['nom_tuteur']) . ' (Tuteur)';
            if (!empty($ins['telephone_tuteur'])) $contactParent = $ins['telephone_tuteur'];
        } elseif (!empty($ins['nom_pere'])) {
            $nomParent = trim($ins['nom_pere']) . ' (Père)';
            if (!empty($ins['telephone_pere'])) $contactParent = $ins['telephone_pere'];
        } elseif (!empty($ins['nom_mere'])) {
            $nomParent = trim($ins['nom_mere']) . ' (Mère)';
            if (!empty($ins['telephone_mere'])) $contactParent = $ins['telephone_mere'];
        }

        $user = Auth::user();
        $userNom = $user['nom_user'] ?? 'La Caissière';
        $userPrenom = $user['prenom_user'] ?? '';
        $caissierNom = trim($userNom . ' ' . $userPrenom);

        $etab = $this->getEtablissementConfig(true);
        $cfgLogo = !empty($etab['logo_etablissement']) ? ltrim($etab['logo_etablissement'], '/') : '';
        $logoSrc = $this->toBase64(array_filter([
            $cfgLogo ? 'public/' . $cfgLogo : '',
            $cfgLogo
        ]));

        $photoSrc = $this->toBase64([
            $ins['photo_etudiant'] ?? '',
            $ins['photo_inscription'] ?? '',
            placeholderPath('etudiant')
        ]);

        $delaiRigueur = $this->resolveDelaiRigueurScolarite($etab);
        $isReprint = !empty($_GET['reprint']);
        $codeRappel = '';
        if ($isReprint && !empty($_GET['code_relance'])) {
            $codeRappel = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$_GET['code_relance']);
        } elseif (!$isReprint) {
            $codeRappel = $this->recordRappelPdfRelance($ins, $restePayer, $contactParent, $delaiRigueur);
        }
        if ($codeRappel === '') {
            $codeRappel = 'RAP-' . strtoupper(substr(md5($ins['code_inscription']), 0, 8));
        }

        $dataView = [
            'etablissement' => $etab,
            'annee_libelle' => $ins['libelle_annee'] ?? '-',
            'code_rappel' => $codeRappel,
            'code_barre_val' => $codeRappel,
            'code_inscription' => $ins['code_inscription'],
            'statut_affectation' => $isAffecte ? 'AFFECTE' : 'NON AFFECTE',
            'matricule_etudiant' => $ins['matricule_etudiant'] ?? 'N/A',
            'filiere_niveau' => (!empty($ins['slug_filiere']) ? $ins['slug_filiere'] : ($ins['libelle_filiere'] ?? '')) . ' - ' . (!empty($ins['slug_niveau']) ? $ins['slug_niveau'] : ($ins['libelle_niveau'] ?? '')),
            'nom_prenom_etudiant' => strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
            'contact_etudiant' => $contactParent,
            'photo_etudiant' => $ins['photo_etudiant'] ?? $ins['photo_inscription'] ?? '',
            'photo_src' => $photoSrc,
            'logo_src' => $logoSrc,
            'total_scolarite' => $totalAttendu,
            'montant_paye' => $totalPaye,
            'reste_payer' => $restePayer,
            'montant_exigible_du' => $restePayer,
            'montant_exigible_lettres' => PdfService::numberToWordsFrench($restePayer),
            'delai_rigueur' => $delaiRigueur,
            'caissier_nom' => $caissierNom,
            'ref_caiss' => 'REF-' . date('YmdHis'),
            'date_impression' => date('d/m/Y H:i:s')
        ];

        $this->logAction(
            AuditLogger::ACTION_PRINT,
            'relances_impayes',
            $codeRappel,
            ($isReprint ? 'Réimpression' : 'Impression') . " du reçu de rappel de scolarité PDF ({$codeRappel}) pour " . $dataView['nom_prenom_etudiant'],
            [
                'code_relance' => $codeRappel,
                'code_inscription' => $ins['code_inscription'],
                'montant_impaye' => $restePayer,
                'delai_rigueur' => $delaiRigueur,
                'reprint' => $isReprint
            ]
        );

        $html = PdfService::renderTemplate('rappel_scolarite.php', $dataView);
        $filename = 'Rappel_Scolarite_' . ($ins['matricule_etudiant'] ?? $ins['code_inscription']) . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Rappel de Scolarité - ' . $dataView['nom_prenom_etudiant'],
            'watermark_logo' => true,
            'watermark_alpha' => 0.05
        ]);
    }

    public function imprimerRappelsMasse()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_IMPAYES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);
        require_once __DIR__ . '/../../core/PdfService.php';
        require_once __DIR__ . '/../../models/frais_annexes/ModelFraisAnnexe.php';

        $rawIds = $_GET['ids'] ?? '';
        if (empty($rawIds)) {
            $this->error('Aucun étudiant sélectionné pour l\'impression en masse.');
            return;
        }

        $ids = array_filter(array_map('trim', explode(',', $rawIds)));
        if (empty($ids)) {
            $this->error('Aucun identifiant valide fourni.');
            return;
        }

        $inscriptions = $this->model->getInscriptionsDetailsForRappelsMasse($ids);
        if (empty($inscriptions)) {
            $this->error('Aucune inscription trouvée pour ces identifiants.');
            return;
        }

        $modelFA = new ModelFraisAnnexe();
        $user = Auth::user();
        $userNom = $user['nom_user'] ?? 'La Caissière';
        $userPrenom = $user['prenom_user'] ?? '';
        $caissierNom = trim($userNom . ' ' . $userPrenom);

        $etab = $this->getEtablissementConfig(true);
        $cfgLogo = !empty($etab['logo_etablissement']) ? ltrim($etab['logo_etablissement'], '/') : '';
        $logoSrc = $this->toBase64(array_filter([
            $cfgLogo ? 'public/' . $cfgLogo : '',
            $cfgLogo
        ]));

        $htmlPages = [];
        $recordedCodes = [];
        $inscCodesMasse = array_column($inscriptions, 'code_inscription');
        $totauxPayesMap = $this->model->getTotalPayeByInscriptions($inscCodesMasse);
        $modelFA->preloadMontants();
        $this->model->preloadScolaritesDues();
        $delaiRigueur = $this->resolveDelaiRigueurScolarite($etab);

        foreach ($inscriptions as $ins) {
            if (!empty($ins['annee_code']) && !$this->canAccessAcademicYear($ins['annee_code'])) {
                continue;
            }
            $totalPaye = $totauxPayesMap[$ins['code_inscription']] ?? 0.0;

            $typeFiliere = $ins['type_filiere'] ?? 'TERTIAIRE';
            $fraisAnnexesDus = (float)$modelFA->getMontantByTypeFiliere($typeFiliere, $ins['annee_code'], $ins['niveau_code'], 'inscription');

            $scolariteDue = $this->model->getScolariteDue($ins['filiere_code'], $ins['niveau_code'], $ins['annee_code'], $ins['affectation_etat'] ?? null);
            if ($scolariteDue === null) {
                $scolariteDue = (float)$ins['montant_scolarite_inscription'];
            }

            $totalAttendu = $scolariteDue;
            $restePayer = max(0, $totalAttendu - $totalPaye);
            $isAffecte = ($ins['affectation_etat'] === 'affecte' || $ins['affectation_etat'] === 'oui');

            $nomParent = '-';
            $contactParent = $ins['telephone_etudiant'] ?? '-';
            if (!empty($ins['nom_tuteur'])) {
                $nomParent = trim($ins['nom_tuteur']) . ' (Tuteur)';
                if (!empty($ins['telephone_tuteur'])) $contactParent = $ins['telephone_tuteur'];
            } elseif (!empty($ins['nom_pere'])) {
                $nomParent = trim($ins['nom_pere']) . ' (Père)';
                if (!empty($ins['telephone_pere'])) $contactParent = $ins['telephone_pere'];
            } elseif (!empty($ins['nom_mere'])) {
                $nomParent = trim($ins['nom_mere']) . ' (Mère)';
                if (!empty($ins['telephone_mere'])) $contactParent = $ins['telephone_mere'];
            }

            $photoSrc = $this->toBase64([
                $ins['photo_etudiant'] ?? '',
                $ins['photo_inscription'] ?? '',
                placeholderPath('etudiant')
            ]);

            $codeRappel = $this->recordRappelPdfRelance($ins, $restePayer, $contactParent, $delaiRigueur);
            $recordedCodes[] = $codeRappel;

            $dataView = [
                'etablissement' => $etab,
                'annee_libelle' => $ins['libelle_annee'] ?? '-',
                'code_rappel' => $codeRappel,
                'code_barre_val' => $codeRappel,
                'code_inscription' => $ins['code_inscription'],
                'statut_affectation' => $isAffecte ? 'AFFECTE' : 'NON AFFECTE',
                'matricule_etudiant' => $ins['matricule_etudiant'] ?? 'N/A',
                'filiere_niveau' => (!empty($ins['slug_filiere']) ? $ins['slug_filiere'] : ($ins['libelle_filiere'] ?? '')) . ' - ' . (!empty($ins['slug_niveau']) ? $ins['slug_niveau'] : ($ins['libelle_niveau'] ?? '')),
                'nom_prenom_etudiant' => strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
                'contact_etudiant' => $contactParent,
                'photo_etudiant' => $ins['photo_etudiant'] ?? $ins['photo_inscription'] ?? '',
                'photo_src' => $photoSrc,
                'logo_src' => $logoSrc,
                'total_scolarite' => $totalAttendu,
                'montant_paye' => $totalPaye,
                'reste_payer' => $restePayer,
                'montant_exigible_du' => $restePayer,
                'montant_exigible_lettres' => PdfService::numberToWordsFrench($restePayer),
                'delai_rigueur' => $delaiRigueur,
                'caissier_nom' => $caissierNom,
                'ref_caiss' => 'REF-' . date('YmdHis'),
                'date_impression' => date('d/m/Y H:i:s')
            ];

            $htmlPages[] = PdfService::renderTemplate('rappel_scolarite.php', $dataView);
        }

        if (!empty($recordedCodes)) {
            $this->logAction(
                AuditLogger::ACTION_PRINT,
                'relances_impayes',
                'MASSE-' . date('YmdHis'),
                "Impression en masse de " . count($recordedCodes) . " reçu(s) de rappel de scolarité PDF",
                ['count' => count($recordedCodes), 'codes_relances' => $recordedCodes, 'delai_rigueur' => $delaiRigueur]
            );
        }

        $fullHtml = implode('<div style="page-break-after: always;"></div>', $htmlPages);
        $filename = 'Rappels_Scolarite_Masse_' . date('Ymd_His') . '.pdf';
        PdfService::generate($fullHtml, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Rappels de Scolarité en Masse (' . count($inscriptions) . ')',
            'watermark_logo' => true,
            'watermark_alpha' => 0.05
        ]);
    }

    /**
     * Construit le tableau de données complet pour le gabarit PDF rappel_scolarite.php
     */
    private function buildRappelPdfDataView(array $ins, array $etab, string $codeRappel, string $delaiRigueur): array
    {
        $totalPaye = $this->model->getTotalPayeByInscription($ins['code_inscription']);
        $scolariteDue = $this->model->getScolariteDue($ins['filiere_code'], $ins['niveau_code'], $ins['annee_code'], $ins['affectation_etat'] ?? null);
        if ($scolariteDue === null) {
            $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
        }
        $restePayer = max(0, $scolariteDue - $totalPaye);
        $isAffecte = (($ins['affectation_etat'] ?? '') === 'affecte' || ($ins['affectation_etat'] ?? '') === 'oui');

        $contactParent = $ins['telephone_etudiant'] ?? '-';
        if (!empty($ins['nom_tuteur']) && !empty($ins['telephone_tuteur'])) {
            $contactParent = $ins['telephone_tuteur'];
        } elseif (!empty($ins['nom_pere']) && !empty($ins['telephone_pere'])) {
            $contactParent = $ins['telephone_pere'];
        } elseif (!empty($ins['nom_mere']) && !empty($ins['telephone_mere'])) {
            $contactParent = $ins['telephone_mere'];
        }

        $user = Auth::user();
        $caissierNom = trim(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? ''));

        $cfgLogo = !empty($etab['logo_etablissement']) ? ltrim($etab['logo_etablissement'], '/') : '';
        $logoSrc = $this->toBase64(array_filter([
            $cfgLogo ? 'public/' . $cfgLogo : '',
            $cfgLogo
        ]));
        $photoSrc = $this->toBase64([
            $ins['photo_etudiant'] ?? '',
            $ins['photo_inscription'] ?? '',
            placeholderPath('etudiant')
        ]);

        return [
            'etablissement' => $etab,
            'annee_libelle' => $ins['libelle_annee'] ?? '-',
            'code_rappel' => $codeRappel,
            'code_barre_val' => $codeRappel,
            'code_inscription' => $ins['code_inscription'],
            'statut_affectation' => $isAffecte ? 'AFFECTE' : 'NON AFFECTE',
            'matricule_etudiant' => $ins['matricule_etudiant'] ?? '-',
            'filiere_niveau' => (!empty($ins['slug_filiere']) ? $ins['slug_filiere'] : ($ins['libelle_filiere'] ?? '')) . ' - ' . (!empty($ins['slug_niveau']) ? $ins['slug_niveau'] : ($ins['libelle_niveau'] ?? '')),
            'nom_prenom_etudiant' => strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
            'contact_etudiant' => $contactParent,
            'photo_etudiant' => $ins['photo_etudiant'] ?? $ins['photo_inscription'] ?? '',
            'photo_src' => $photoSrc,
            'logo_src' => $logoSrc,
            'total_scolarite' => $scolariteDue,
            'montant_paye' => $totalPaye,
            'reste_payer' => $restePayer,
            'montant_exigible_du' => $restePayer,
            'montant_exigible_lettres' => PdfService::numberToWordsFrench($restePayer),
            'delai_rigueur' => $delaiRigueur,
            'caissier_nom' => $caissierNom !== '' ? $caissierNom : '-',
            'ref_caiss' => ($etab['prefixe_reference'] ?? 'REF-') . date('YmdHis'),
            'date_impression' => date('d/m/Y H:i:s')
        ];
    }

    /**
     * Construit le corps HTML de l'e-mail de relance à partir de configurations (Règle 9)
     */
    private function buildRelanceEmailHtml(array $etab, ?array $ins, string $message, float $montant, string $codeRelance): string
    {
        $nomEtab = htmlspecialchars((string)($etab['nom_etablissement'] ?? ($etab['sigle_etablissement'] ?? '')));
        $contacts = htmlspecialchars((string)($etab['contacts_formatted'] ?? ''));
        $adresse = htmlspecialchars((string)($etab['adresse_etablissement'] ?? ''));
        $piedPage = htmlspecialchars((string)($etab['texte_pied_page'] ?? ''));
        $nomEtudiant = $ins ? htmlspecialchars(trim(strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')))) : '';
        $matricule = $ins ? htmlspecialchars((string)($ins['matricule_etudiant'] ?? '')) : '';
        $classe = $ins ? htmlspecialchars((string)($ins['libelle_classe'] ?? '')) : '';
        $montantFmt = number_format($montant, 0, ',', ' ') . ' FCFA';
        $delai = htmlspecialchars($this->resolveDelaiRigueurScolarite($etab));
        $msgHtml = nl2br(htmlspecialchars($message));

        return '<div style="font-family: Arial, Helvetica, sans-serif; max-width: 620px; margin: 0 auto; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden; color: #0F172A;">'
            . '<div style="background: #1E3A5F; color: #FFFFFF; padding: 18px 24px;">'
            . '<div style="font-size: 16px; font-weight: bold;">' . $nomEtab . '</div>'
            . '<div style="font-size: 12px; opacity: 0.85; margin-top: 4px;">Avis de Relance de Scolarité — Réf. ' . htmlspecialchars($codeRelance) . '</div>'
            . '</div>'
            . '<div style="padding: 22px 24px; font-size: 14px; line-height: 1.6;">'
            . ($nomEtudiant !== '' ? '<p style="margin: 0 0 12px 0; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px 14px;">'
                . '<strong>Étudiant :</strong> ' . $nomEtudiant . ($matricule !== '' ? ' (' . $matricule . ')' : '') . '<br>'
                . ($classe !== '' ? '<strong>Classe :</strong> ' . $classe . '<br>' : '')
                . '<strong>Montant exigible :</strong> <span style="color: #DC2626; font-weight: bold;">' . $montantFmt . '</span>'
                . ($delai !== '-' ? '<br><strong>Délai de rigueur :</strong> <strong>' . $delai . '</strong>' : '')
                . '</p>' : '')
            . '<p style="margin: 0 0 16px 0;">' . $msgHtml . '</p>'
            . '<hr style="border: none; border-top: 1px solid #E2E8F0; margin: 18px 0;">'
            . '<div style="font-size: 12px; color: #64748B;">'
            . ($adresse !== '' ? $adresse . '<br>' : '')
            . ($contacts !== '' ? 'Contacts : ' . $contacts . '<br>' : '')
            . ($piedPage !== '' ? '<em>' . $piedPage . '</em>' : '')
            . '</div></div></div>';
    }

    /**
     * Enregistre dans relances_impayes l'émission d'un reçu / avis de rappel de scolarité PDF
     */
    private function recordRappelPdfRelance(array $ins, float $restePayer, string $contactParent, string $delaiRigueur): string
    {
        $codeRelance = $this->validator->generateCode(TABLES::RELANCES_IMPAYES, 'code_relance', 'RAP-', 8);
        $anneeCode = (string)($ins['annee_code'] ?? $this->getActiveAnneeCode());
        $etudiantCode = (string)($ins['etudiant_code'] ?? '');
        $userCode = (string)(Auth::code() ?? '');
        $etabCode = (string)($ins['etablissement_code'] ?? $this->getActiveEtablissementCode());

        if ($etudiantCode === '' || $userCode === '' || $anneeCode === '' || $etabCode === '') {
            return $codeRelance;
        }
        if (!$this->canWriteAcademicYear($anneeCode)) {
            return $codeRelance;
        }

        $telephoneDest = ($contactParent !== '' && $contactParent !== '-') ? $contactParent : ($ins['telephone_etudiant'] ?? '');
        $message = "Reçu de rappel de scolarité PDF émis (Réf: {$codeRelance}) — Solde restant : "
            . number_format($restePayer, 0, ',', ' ') . " FCFA"
            . ($delaiRigueur !== '-' ? " — Délai de rigueur : {$delaiRigueur}" : '') . ".";

        $payload = [
            'code_relance' => $codeRelance,
            'etudiant_code' => $etudiantCode,
            'inscription_code' => $ins['code_inscription'] ?? null,
            'niveau_relance' => 'rappel_amiable',
            'canal_relance' => 'recu_pdf',
            'montant_impaye' => $restePayer,
            'telephone_destinataire' => $telephoneDest,
            'message_relance' => $message,
            'statut_relance' => 'envoye',
            'user_code' => $userCode,
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'created_at_relance' => date('Y-m-d H:i:s')
        ];

        $filtered = $this->model->filterData($payload);
        $this->model->create($filtered);
        return $codeRelance;
    }

    /**
     * Calcule dynamiquement la date limite de rigueur à partir de configurations.delai_rigueur_scolarite (en jours)
     */
    private function resolveDelaiRigueurScolarite(array $etab): string
    {
        if (!isset($etab['delai_rigueur_scolarite']) || $etab['delai_rigueur_scolarite'] === '' || $etab['delai_rigueur_scolarite'] === null) {
            return '-';
        }
        $nbJours = (int)$etab['delai_rigueur_scolarite'];
        if ($nbJours < 0) {
            return '-';
        }
        return date('d/m/Y', strtotime('+' . $nbJours . ' days'));
    }

    private function toBase64(array $paths): string
    {
        foreach ($paths as $p) {
            if (empty($p)) continue;
            $pClean = ltrim($p, '/');
            $candidates = [
                $pClean,
                __DIR__ . '/../../' . $pClean,
                __DIR__ . '/../../public/' . $pClean
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand) && is_file($cand)) {
                    $ext = pathinfo($cand, PATHINFO_EXTENSION);
                    $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/jpeg');
                    return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($cand));
                }
            }
        }
        return '';
    }
}

