<?php

class PaiementController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelPaiement();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_PAIEMENTS');

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $aRow = $this->model->getAnneeByCode($getAnnee);
            if ($aRow) {
                Session::set('annee_active_code', $aRow['code_annee']);
                Session::set('annee_active_libelle', $aRow['libelle_annee']);
            }
        }

        $activeYear = $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;

        $annees = $this->getAccessibleAnnees();
        $deps = $this->model->getListDependencies($activeYear);

        $stats = $this->model->computeFinancialStats($activeYear, $niveauCode, $classeCode, $dateDebut, $dateFin, $activeYear);

        $activeSession = $deps['activeSession'] ?? null;
        $isCaisseOuverte = $deps['isCaisseOuverte'] ?? false;
        $encryptedSessionId = ($activeSession && !empty($activeSession['id_session'])) ? $this->validator->crypter($activeSession['id_session']) : '';

        $canRecord = $this->hasPermission(['RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        $canOpenCaisse = $this->hasPermission(['OUVERTURE_CAISSE', 'MANAGE_CAISSE', 'RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        $canCloseCaisse = $this->hasPermission(['CLOTURE_CAISSE', 'MANAGE_CAISSE', 'RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        $canCancelPaiement = $this->hasPermission('CANCEL_PAIEMENTS');

        $this->loadView('../views/paiements/list.php', [
            'stats' => $stats,
            'annees' => $annees,
            'niveaux' => $deps['niveaux'] ?? [],
            'classes' => $deps['classes'] ?? [],
            'selectedAnneeCode' => $activeYear,
            'inscriptions' => $deps['inscriptions'] ?? [],
            'isCaisseOuverte' => $isCaisseOuverte,
            'activeSession' => $activeSession,
            'encryptedSessionId' => $encryptedSessionId,
            'canRecord' => $canRecord,
            'canOpenCaisse' => $canOpenCaisse,
            'canCloseCaisse' => $canCloseCaisse,
            'canCancelPaiement' => $canCancelPaiement
        ]);
    }

    public function comptabiliteEtudiants()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_COMPTABILITE_ETUDIANTS', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS']);

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $aRow = $this->model->getAnneeByCode($getAnnee);
            if ($aRow) {
                Session::set('annee_active_code', $aRow['code_annee']);
                Session::set('annee_active_libelle', $aRow['libelle_annee']);
            }
        }

        $activeYear = $this->getActiveAnneeCode();
        $annees = $this->getAccessibleAnnees();
        $deps = $this->model->getListDependencies($activeYear);

        $activeSession = $deps['activeSession'] ?? null;
        $isCaisseOuverte = $deps['isCaisseOuverte'] ?? false;
        $encryptedSessionId = ($activeSession && !empty($activeSession['id_session'])) ? $this->validator->crypter($activeSession['id_session']) : '';

        $canRecord = $this->hasPermission(['RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        $canOpenCaisse = $this->hasPermission(['OUVERTURE_CAISSE', 'MANAGE_CAISSE', 'RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        $canCloseCaisse = $this->hasPermission(['CLOTURE_CAISSE', 'MANAGE_CAISSE', 'RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS']);

        $this->loadView('../views/paiements/comptabilite_etudiants.php', [
            'annees' => $annees,
            'niveaux' => $deps['niveaux'] ?? [],
            'classes' => $deps['classes'] ?? [],
            'selectedAnneeCode' => $activeYear,
            'inscriptions' => $deps['inscriptions'] ?? [],
            'isCaisseOuverte' => $isCaisseOuverte,
            'activeSession' => $activeSession,
            'encryptedSessionId' => $encryptedSessionId,
            'canRecord' => $canRecord,
            'canOpenCaisse' => $canOpenCaisse,
            'canCloseCaisse' => $canCloseCaisse
        ]);
    }

    public function apiComptabiliteEtudiants()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_COMPTABILITE_ETUDIANTS', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS']);

        $anneeCode = $_GET['annee_code'] ?? $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $regime = $_GET['regime'] ?? 'ALL';
        $statutPaiement = $_GET['statut_paiement'] ?? 'ALL';

        $res = $this->model->getComptabiliteEtudiantsData($anneeCode, $niveauCode, $classeCode, $regime, $statutPaiement, $this->validator);
        $this->json($res);
    }

    public function apiStats()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_PAIEMENTS');

        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code') ?? null;
        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;

        $stats = $this->model->computeFinancialStats($anneeCode, $niveauCode, $classeCode, $dateDebut, $dateFin, $this->getActiveAnneeCode());
        $this->json(['status' => 1, 'stats' => $stats]);
    }

    public function computeFinancialStats(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        return $this->model->computeFinancialStats($anneeCode, $niveauCode, $classeCode, $dateDebut, $dateFin, $this->getActiveAnneeCode());
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_PAIEMENTS');

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $aRow = $this->model->getAnneeByCode($getAnnee);
            if ($aRow) {
                Session::set('annee_active_code', $aRow['code_annee']);
                Session::set('annee_active_libelle', $aRow['libelle_annee']);
            }
        }

        $anneeCode = $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;
        $canalPaiement = $_GET['canal_paiement'] ?? null;

        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode, $dateDebut, $dateFin, $canalPaiement);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_paiement'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function getStudentFinancialSummary()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_PAIEMENTS');

        $inscriptionCode = $_GET['inscription_code'] ?? ($_POST['inscription_code'] ?? '');
        $etudiantCode = $_GET['etudiant_code'] ?? ($_POST['etudiant_code'] ?? '');
        $anneeParam = $_GET['annee_code'] ?? ($_POST['annee_code'] ?? '');
        $activeYear = !empty($anneeParam) ? trim($anneeParam) : $this->getActiveAnneeCode();

        $lookupKey = !empty($inscriptionCode) ? $inscriptionCode : $etudiantCode;
        $result = $this->model->getStudentFinancialSummaryData($lookupKey, $activeYear, $this->validator);
        $this->json($result);
    }

    public function getNextUnpaidTranche(string $inscriptionCode): ?array
    {
        return $this->model->getNextUnpaidTranche($inscriptionCode, $this->getActiveAnneeCode());
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('RECORD_PAIEMENTS');

        $userCode = Auth::code() ?: 'SYS';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $data = $_POST;
        unset($data['csrf_token']);

        $res = $this->model->processEnregistrementPaiement($data, $userCode, $anneeCode, $etabCode, $this->validator);
        if ($res['success']) {
            $this->success($res['message'], $res['data'] ?? []);
        } else {
            $this->error($res['message']);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('RECORD_PAIEMENTS');
        $id = (int)$this->post('id_paiement');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $filteredData = $this->model->filterData($data);
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
        $this->requirePermission('CANCEL_PAIEMENTS');
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = ['en_attente', 'confirme', 'annule', 'rembourse', 'echoue'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'statut_paiement');
            } else {
                $success = $this->model->toggleStatus($id);
            }
            if ($success) {
                $this->success('Statut du paiement mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Paiement introuvable');
        }
    }

    public function annuler()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('CANCEL_PAIEMENTS');

        $id = (int)($this->post('id_paiement') ?: $this->post('id'));
        $codePaiement = trim($this->post('code_paiement') ?? '');
        $motif = trim($this->post('motif') ?? '');

        $userCode = Auth::code() ?: 'SYS';
        $userName = trim(Auth::user('prenom') . ' ' . Auth::user('nom'));

        $res = $this->model->annulerVersement($id, $codePaiement, $motif, $userCode, $userName);
        if ($res['success']) {
            $this->success($res['message'], $res['data'] ?? []);
        } else {
            $this->error($res['message']);
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_PAIEMENTS');
        try {
            $id = $details;
            if (!empty($details)) {
                $decrypted = $this->validator->decrypter($details);
                if (!empty($decrypted) && $decrypted !== 0 && $decrypted !== '0') {
                    $id = $decrypted;
                }
            }

            $item = $this->model->getById($id);
            if (!$item && !empty($details)) {
                $item = $this->model->getById($details);
            }

            if (!$item) { 
                $this->renderNotFound("Le paiement demandé est introuvable.");
                return;
            }

            $actualId = (int)($item['id_paiement'] ?? 0);
            $calcData = $this->model->getDetailsPaiementData($actualId, $item);
            $encryptedId = $actualId > 0 ? $this->validator->crypter($actualId) : $details;
        } catch (Exception $e) {
            error_log("PaiementController::details error: " . $e->getMessage());
            $this->renderNotFound("Le paiement demandé est introuvable.");
            return;
        }

        $this->loadView('../views/paiements/details.php', [
            'item' => $calcData['item'] ?? $item, 
            'totalPayeCumul' => $calcData['totalPayeCumul'] ?? 0,
            'totalScolariteCumul' => $calcData['totalScolariteCumul'] ?? 0,
            'totalFraisAnnexesCumul' => $calcData['totalFraisAnnexesCumul'] ?? 0,
            'firstGroupScolarite' => $calcData['firstGroupScolarite'] ?? 0,
            'firstGroupFraisAnnexes' => $calcData['firstGroupFraisAnnexes'] ?? 0,
            'isFirstPayment' => $calcData['isFirstPayment'] ?? false,
            'soldeRestant' => $calcData['soldeRestant'] ?? 0,
            'scolarite' => $calcData['scolarite'] ?? 0,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        header('Location: ' . RACINE . 'paiement/list');
        exit();
    }

    public function formulaire()
    {
        $inscrCode = $_GET['inscription_code'] ?? ($_GET['code'] ?? '');
        $etuCode = $_GET['etudiant_code'] ?? '';
        $query = '?action=encaissement';
        if (!empty($inscrCode)) $query .= '&inscription_code=' . urlencode($inscrCode);
        if (!empty($etuCode)) $query .= '&etudiant_code=' . urlencode($etuCode);
        header('Location: ' . RACINE . 'paiement/list' . $query);
        exit();
    }

    public function imprimerPdf($details = null)
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_PAIEMENTS', 'VIEW_ETUDIANTS', 'VIEW_INSCRIPTIONS']);
        require_once __DIR__ . '/../../core/PdfService.php';

        if (empty($details) && !empty($_GET['id'])) {
            $details = $_GET['id'];
        }

        if (empty($details)) {
            $this->renderNotFound("Règlement non spécifié.");
            return;
        }

        $id = $details;
        if (!empty($details)) {
            $decrypted = $this->validator->decrypter($details);
            if (!empty($decrypted)) {
                $id = $decrypted;
            }
        }

        $data = $this->model->getRecuVersementData((string)$id);
        if (!$data) {
            $this->renderNotFound("Le reçu de versement demandé est introuvable.");
            return;
        }

        $templateName = 'recu_versement.php';
        $html = PdfService::renderTemplate($templateName, $data);

        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $numRecuCode = $data['numero_recu'] ?? 'REC';
        $filename = 'Recu_Versement_' . $numRecuCode . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Reçu de Versement N° ' . $numRecuCode,
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8
        ]);
    }

    public function imprimerComptabiliteEtudiants()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_COMPTABILITE_ETUDIANTS', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        require_once __DIR__ . '/../../core/PdfService.php';

        $anneeCode = $_GET['annee_code'] ?? $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $regime = $_GET['regime'] ?? 'ALL';
        $statutPaiement = $_GET['statut_paiement'] ?? 'ALL';

        $currentUserName = trim(Auth::user('nom') . ' ' . Auth::user('prenom'));
        $dataView = $this->model->getPrintComptabiliteEtudiantsData($anneeCode, $niveauCode, $classeCode, $regime, $statutPaiement, $currentUserName);

        $html = PdfService::renderTemplate('comptabilite_etudiants.php', $dataView);

        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $filename = 'Etat_Comptabilite_Etudiants_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'État de Synthèse Comptable des Étudiants',
            'margin_left' => 6,
            'margin_right' => 6,
            'margin_top' => 6,
            'margin_bottom' => 6
        ]);
    }

    public function imprimerRegistreEncaissements()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_PAIEMENTS', 'VIEW_COMPTABILITE_ETUDIANTS', 'MANAGE_PAIEMENTS']);
        require_once __DIR__ . '/../../core/PdfService.php';

        $anneeCode = $_GET['annee_code'] ?? '';
        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $canalPaiement = $_GET['canal_paiement'] ?? 'ALL';
        $modePaiement = $_GET['mode_paiement'] ?? 'ALL';
        $categoriePaiement = $_GET['categorie_paiement'] ?? 'ALL';
        $dateDebut = $_GET['date_debut'] ?? '';
        $dateFin = $_GET['date_fin'] ?? '';

        $currentUserName = trim(Auth::user('nom') . ' ' . Auth::user('prenom'));
        $dataView = $this->model->getPrintRegistreEncaissementsData($anneeCode, $niveauCode, $classeCode, $canalPaiement, $modePaiement, $categoriePaiement, $dateDebut, $dateFin, $currentUserName);

        $html = PdfService::renderTemplate('registre_encaissements.php', $dataView);

        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $filename = 'Registre_Encaissements_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'Registre des Encaissements - GEICG',
            'margin_left' => 6,
            'margin_right' => 6,
            'margin_top' => 6,
            'margin_bottom' => 6
        ]);
    }

    public function imprimerBilanFinancier($details = null)
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_COMPTABILITE_ETUDIANTS', 'VIEW_PAIEMENTS', 'VIEW_INSCRIPTIONS', 'VIEW_ETUDIANTS']);
        require_once __DIR__ . '/../../core/PdfService.php';

        if (empty($details) && !empty($_GET['id'])) {
            $details = $_GET['id'];
        }

        if (empty($details)) {
            $this->renderNotFound("Inscription / Étudiant non spécifié.");
            return;
        }

        $id = $details;
        if (!empty($details)) {
            $decrypted = $this->validator->decrypter($details);
            if (!empty($decrypted)) {
                $id = $decrypted;
            }
        }

        $activeAnneeCode = $this->getActiveAnneeCode();
        $currentUserName = trim(Auth::user('nom') . ' ' . Auth::user('prenom'));
        $dataView = $this->model->getPrintBilanFinancierData((string)$id, $activeAnneeCode, $currentUserName);

        if (!$dataView) {
            $this->renderNotFound("Le dossier financier de l'étudiant est introuvable.");
            return;
        }

        $html = PdfService::renderTemplate('fiche_versement_etudiant.php', $dataView);

        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $matricule = $dataView['matricule_etudiant'] ?? 'ETUDIANT';
        $nomComplet = $dataView['nom_prenom_etudiant'] ?? '';
        $filename = 'Bilan_Financier_' . $matricule . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Bilan Financier de l\'Étudiant - ' . $nomComplet,
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8
        ]);
    }
}
