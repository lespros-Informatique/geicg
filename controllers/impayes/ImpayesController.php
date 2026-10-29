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

        $canSendRelance = $this->hasPermission([PERMISSIONS::SEND_RELANCES, PERMISSIONS::MANAGE_IMPAYES]);
        $canRecordPayment = $this->hasPermission([PERMISSIONS::RECORD_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);

        $this->loadView('../views/impayes/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode,
            'canSendRelance' => $canSendRelance,
            'canRecordPayment' => $canRecordPayment
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_IMPAYES, PERMISSIONS::MANAGE_IMPAYES, PERMISSIONS::VIEW_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);

        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
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

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::SEND_RELANCES, PERMISSIONS::MANAGE_IMPAYES]);

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['code_relance'])) {
            $data['code_relance'] = $this->validator->generateCode(TABLES::IMPAYES, 'code_relance', 'REL-', 8);
        }
        $data['statut_relance'] = 'envoye';
        $data['created_at_relance'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['annee_code'] = $anneeCode;
        $data['etablissement_code'] = $etabCode;

        $filteredData = $this->model->filterData($data);

        if ($this->model->create($filteredData)) {
            $this->success('Relance d\'impayé enregistrée et journalisée avec succès!');
        } else {
            $this->error('Erreur lors de l\'enregistrement de la relance');
        }
    }

    public function addGrouped()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::SEND_RELANCES, PERMISSIONS::MANAGE_IMPAYES]);

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
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
            $codeRelance = $this->validator->generateCode(TABLES::IMPAYES, 'code_relance', 'REL-', 8);
            
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

        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
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

        // Paiements via modèle
        $totalPaye = $this->model->getTotalPayeByInscription($ins['code_inscription']);

        $modelFA = new ModelFraisAnnexe();
        $typeFiliere = $ins['type_filiere'] ?? 'TERTIAIRE';
        $fraisAnnexesDus = (float)$modelFA->getMontantByTypeFiliere($typeFiliere, $ins['annee_code'], $ins['niveau_code'], 'inscription');

        // Scolarité due via modèle
        $scolariteDue = $this->model->getScolariteDue($ins['filiere_code'], $ins['niveau_code'], $ins['annee_code']);
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

        $logoSrc = $this->toBase64([
            'public/assets/images/logo/logo_eicg.jpg',
            'assets/images/logo/logo_eicg.jpg',
            'public/uploads/logos/logo_1787358264.jpg'
        ]);

        $photoSrc = $this->toBase64([
            $ins['photo_etudiant'] ?? '',
            $ins['photo_inscription'] ?? '',
            'public/assets/images/placeholders/etudiant.png',
            'assets/images/placeholders/etudiant.png'
        ]);

        $dataView = [
            'annee_libelle' => $ins['libelle_annee'] ?? date('Y') . '-' . (date('Y') + 1),
            'code_rappel' => 'RAP-' . strtoupper(substr(md5($ins['code_inscription']), 0, 8)),
            'code_barre_val' => 'RAP-' . strtoupper(substr(md5($ins['code_inscription']), 0, 8)),
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
            'delai_rigueur' => date('d/m/Y', strtotime('+7 days')),
            'caissier_nom' => $caissierNom,
            'ref_caiss' => 'REF-' . date('YmdHis'),
            'date_impression' => date('d/m/Y H:i:s')
        ];

        $html = PdfService::renderTemplate('rappel_scolarite.php', $dataView);
        $filename = 'Rappel_Scolarite_' . ($ins['matricule_etudiant'] ?? $ins['code_inscription']) . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Rappel de Scolarité - ' . $dataView['nom_prenom_etudiant']
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

        $logoSrc = $this->toBase64([
            'public/assets/images/logo/logo_eicg.jpg',
            'assets/images/logo/logo_eicg.jpg',
            'public/uploads/logos/logo_1787358264.jpg'
        ]);

        $htmlPages = [];

        foreach ($inscriptions as $ins) {
            $totalPaye = $this->model->getTotalPayeByInscription($ins['code_inscription']);

            $typeFiliere = $ins['type_filiere'] ?? 'TERTIAIRE';
            $fraisAnnexesDus = (float)$modelFA->getMontantByTypeFiliere($typeFiliere, $ins['annee_code'], $ins['niveau_code'], 'inscription');

            $scolariteDue = $this->model->getScolariteDue($ins['filiere_code'], $ins['niveau_code'], $ins['annee_code']);
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
                'public/assets/images/placeholders/etudiant.png',
                'assets/images/placeholders/etudiant.png'
            ]);

            $dataView = [
                'annee_libelle' => $ins['libelle_annee'] ?? date('Y') . '-' . (date('Y') + 1),
                'code_rappel' => 'RAP-' . strtoupper(substr(md5($ins['code_inscription']), 0, 8)),
                'code_barre_val' => 'RAP-' . strtoupper(substr(md5($ins['code_inscription']), 0, 8)),
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
                'delai_rigueur' => date('d/m/Y', strtotime('+7 days')),
                'caissier_nom' => $caissierNom,
                'ref_caiss' => 'REF-' . date('YmdHis'),
                'date_impression' => date('d/m/Y H:i:s')
            ];

            $htmlPages[] = PdfService::renderTemplate('rappel_scolarite.php', $dataView);
        }

        $fullHtml = implode('<div style="page-break-after: always;"></div>', $htmlPages);
        $filename = 'Rappels_Scolarite_Masse_' . date('Ymd_His') . '.pdf';
        PdfService::generate($fullHtml, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Rappels de Scolarité en Masse (' . count($inscriptions) . ')'
        ]);
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

