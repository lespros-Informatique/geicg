<?php

class SessionCaisseController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelSessionCaisse();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::OUVERTURE_CAISSE, PERMISSIONS::CLOTURE_CAISSE]);
        $today = date('Y-m-d');
        
        $annees = $this->getAccessibleAnnees();
        
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

        $stats = $this->model->getStatistics($selectedAnneeCode);
        $totalSessions = $stats['totalSessions'];
        $totalOuvertes = $stats['totalOuvertes'];
        $totalCloturees = $stats['totalCloturees'];
        $totalCumulSessions = $stats['totalCumulSessions'];

        // Active session for today
        $activeSession = $this->model->getActiveSession($today);

        // Latest session for reference
        $latestSession = $this->model->getLatestSession();
        
        // Today's live collection totals from paiements
        $dailyTotals = $this->model->getDailyFinancialTotals($today);

        $this->loadView('../views/sessions_caisse/list.php', [
            'totalSessions' => $totalSessions,
            'totalOuvertes' => $totalOuvertes,
            'totalCloturees' => $totalCloturees,
            'totalCumulSessions' => $totalCumulSessions,
            'activeSession' => $activeSession,
            'latestSession' => $latestSession,
            'dailyTotals' => $dailyTotals,
            'annees' => $annees,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::OUVERTURE_CAISSE, PERMISSIONS::CLOTURE_CAISSE]);
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $items = $this->model->getAll($anneeCode);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_session'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::OUVERTURE_CAISSE]);
        $this->loadView('../views/sessions_caisse/edit.php', [
            'item' => [
                'date_session' => date('Y-m-d'),
                'fond_initial' => 0,
                'observations_ouverture' => ''
            ]
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::OUVERTURE_CAISSE]);
        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode
        ]);

        $data = $_POST;
        unset($data['csrf_token']);

        $dateSession = $data['date_session'] ?? date('Y-m-d');

        // Vérifier si une session est déjà OUVERTE pour cette date
        $active = $this->model->getActiveSession($dateSession);
        if ($active) {
            $this->error('Une session de caisse est déjà en cours (OUVERTE) pour le ' . date('d/m/Y', strtotime($dateSession)));
            return;
        }

        if (empty($data['code_session'])) {
            $data['code_session'] = $this->validator->generateCode(TABLES::SESSIONS_CAISSE, 'code_session', 'SES-', 8);
        }
        $data['heure_ouverture'] = date('H:i:s');
        $data['statut_session'] = 'ouverte';
        $data['fond_initial'] = (float)($data['fond_initial'] ?? 0);
        $data['user_code'] = $userCode;
        $data['annee_code'] = $anneeCode;
        $data['etablissement_code'] = $etabCode;

        $filteredData = $this->model->filterData($data);

        $id = $this->model->create($filteredData);
        if ($id) {
            $this->success("La session de caisse {$data['code_session']} a été ouverte avec succès.", RACINE . 'session_caisse/list');
        } else {
            $this->error("Erreur lors de l'ouverture de la session de caisse.");
        }
    }

    public function cloturer($idParam = null)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::CLOTURE_CAISSE]);
        $id = $idParam ? $this->validator->decrypter($idParam) : null;
        if (!$id && is_numeric($idParam)) {
            $id = $idParam;
        }

        $session = null;
        if ($id) {
            $session = $this->model->getById($id);
        } else {
            $session = $this->model->getActiveSession(date('Y-m-d'));
        }

        if (!$session) {
            $this->error("Aucune session de caisse active à clôturer.", RACINE . 'session_caisse/list');
            return;
        }

        // Totaux collectés en direct pour cette session
        $financials = $this->model->getDailyFinancialTotals($session['date_session'], $session['code_session'] ?? null);

        $this->loadView('../views/sessions_caisse/cloturer.php', [
            'session' => $session,
            'financials' => $financials
        ]);
    }

    public function saveCloture()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::CLOTURE_CAISSE]);
        $userCode = Auth::code();
        $data = $_POST;
        unset($data['csrf_token']);

        $idSession = $data['id_session'] ?? null;
        if (!$idSession) {
            $this->error("Identifiant de session manquant.");
            return;
        }

        $session = $this->model->getById($idSession);
        if (!$session) {
            $this->error("Session introuvable.");
            return;
        }

        $fondInitial = (float)($session['fond_initial'] ?? 0);
        $totalEspeces = (float)($data['total_especes'] ?? 0);
        $totalMobile = (float)($data['total_mobile_money'] ?? 0);
        $totalCheque = (float)($data['total_cheque_virement'] ?? 0);
        
        $totalGeneral = $totalEspeces + $totalMobile + $totalCheque;
        $soldeAttendu = $fondInitial + $totalGeneral;
        $montantPhysiqueCompte = (float)($data['montant_physique_compte'] ?? $soldeAttendu);
        $ecartCaisse = $montantPhysiqueCompte - $soldeAttendu;

        $updateData = [
            'heure_cloture' => date('H:i:s'),
            'total_especes' => $totalEspeces,
            'total_mobile_money' => $totalMobile,
            'total_cheque_virement' => $totalCheque,
            'total_general' => $totalGeneral,
            'solde_attendu' => $soldeAttendu,
            'ecart_caisse' => $ecartCaisse,
            'observations_cloture' => $data['observations_cloture'] ?? '',
            'statut_session' => 'cloturee',
            'user_validation' => $userCode
        ];

        $res = $this->model->update($updateData, $idSession);
        if ($res) {
            $this->success("Arrêté et clôture de la session {$session['code_session']} enregistrés avec succès.", RACINE . 'session_caisse/details/' . $this->validator->crypter($idSession));
        } else {
            $this->error("Erreur lors de l'enregistrement de la clôture.");
        }
    }

    public function details($idParam)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::OUVERTURE_CAISSE, PERMISSIONS::CLOTURE_CAISSE]);
        $id = $this->validator->decrypter($idParam);
        if (!$id && is_numeric($idParam)) {
            $id = $idParam;
        }

        $item = $this->model->getById($id);
        if (!$item) {
            $this->error('Session de caisse introuvable.', RACINE . 'session_caisse/list');
            return;
        }

        // Paiements réalisés au cours de la session (TOUTES LES TRANSACTIONS de la session, y compris annulées)
        $paiements = $this->model->getSessionTransactions($item['code_session'] ?? '', $item['date_session']);

        $totalValide = 0;
        $totalAnnule = 0;
        $nbValides = 0;
        $nbAnnules = 0;
        $totalEspeces = 0;
        $totalMobile = 0;
        $totalCheque = 0;
        $totalScolarite = 0;
        $totalFraisAnnexes = 0;

        foreach ($paiements as $p) {
            $m = (float)($p['montant_paiement'] ?? 0);
            $mode = strtolower($p['mode_paiement'] ?? '');
            $cat = strtoupper($p['categorie_paiement'] ?? '');
            $stat = strtolower($p['statut_paiement'] ?? 'confirme');

            if ($stat === 'annule') {
                $totalAnnule += $m;
                $nbAnnules++;
            } else {
                $totalValide += $m;
                $nbValides++;
                if ($mode === 'espece' || $mode === 'especes') {
                    $totalEspeces += $m;
                } elseif (in_array($mode, ['mobile_money', 'mobile', 'wave', 'orange', 'mtn', 'moov'])) {
                    $totalMobile += $m;
                } else {
                    $totalCheque += $m;
                }

                if ($cat === 'FRAIS_ANNEXES') {
                    $totalFraisAnnexes += $m;
                } else {
                    $totalScolarite += $m;
                }
            }
        }

        $fondInitial = (float)($item['fond_initial'] ?? 0);
        $soldeAttendu = $fondInitial + $totalValide;
        $ecart = (float)($item['ecart_caisse'] ?? 0);

        $kpis = [
            'fond_initial' => $fondInitial,
            'total_valide' => $totalValide,
            'total_annule' => $totalAnnule,
            'nb_valides' => $nbValides,
            'nb_annules' => $nbAnnules,
            'nb_total' => count($paiements),
            'total_especes' => $totalEspeces,
            'total_mobile' => $totalMobile,
            'total_cheque' => $totalCheque,
            'total_scolarite' => $totalScolarite,
            'total_frais_annexes' => $totalFraisAnnexes,
            'solde_attendu' => $soldeAttendu,
            'ecart_caisse' => $ecart
        ];

        $this->loadView('../views/sessions_caisse/details.php', [
            'item' => $item,
            'paiements' => $paiements,
            'kpis' => $kpis
        ]);
    }

    public function apiTransactions($idParam)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::OUVERTURE_CAISSE, PERMISSIONS::CLOTURE_CAISSE]);
        $id = $this->validator->decrypter($idParam);
        if (!$id && is_numeric($idParam)) {
            $id = $idParam;
        }

        $session = $this->model->getById($id);
        if (!$session) {
            $this->json(['status' => 0, 'message' => 'Session de caisse introuvable.', 'data' => []]);
            return;
        }

        $paiements = $this->model->getSessionTransactions($session['code_session'] ?? '', $session['date_session']);

        $totalValide = 0;
        $totalAnnule = 0;
        $nbValides = 0;
        $nbAnnules = 0;
        $totalScolarite = 0;
        $totalFraisAnnexes = 0;
        $totalEspeces = 0;
        $totalMobile = 0;

        foreach ($paiements as &$p) {
            $m = (float)($p['montant_paiement'] ?? 0);
            $mode = strtolower($p['mode_paiement'] ?? '');
            $cat = strtoupper($p['categorie_paiement'] ?? '');
            $stat = strtolower($p['statut_paiement'] ?? 'confirme');

            if ($stat === 'annule') {
                $totalAnnule += $m;
                $nbAnnules++;
            } else {
                $totalValide += $m;
                $nbValides++;
                if ($cat === 'FRAIS_ANNEXES') {
                    $totalFraisAnnexes += $m;
                } else {
                    $totalScolarite += $m;
                }

                if ($mode === 'espece' || $mode === 'especes') {
                    $totalEspeces += $m;
                } else {
                    $totalMobile += $m;
                }
            }

            $p['formatted_montant'] = number_format($m, 0, ',', ' ') . ' FCFA';
            $p['formatted_date'] = date('d/m/Y H:i', strtotime($p['date_paiement']));
            $p['etudiant_complet'] = trim(($p['nom_etudiant'] ?? '') . ' ' . ($p['prenom_etudiant'] ?? ''));
            if (empty($p['etudiant_complet'])) {
                $p['etudiant_complet'] = 'Étudiant non renseigné';
            }
        }
        unset($p);

        $this->json([
            'status' => 1,
            'session' => [
                'id_session' => $session['id_session'],
                'code_session' => $session['code_session'],
                'date_session' => date('d/m/Y', strtotime($session['date_session'])),
                'caissier_nom' => $session['caissier_nom'] ?? ($session['user_code'] ?? 'Caissier'),
                'fond_initial' => (float)($session['fond_initial'] ?? 0),
                'statut_session' => $session['statut_session'] ?? 'ouverte',
                'total_valide' => $totalValide,
                'total_annule' => $totalAnnule,
                'nb_valides' => $nbValides,
                'nb_annules' => $nbAnnules,
                'total_scolarite' => $totalScolarite,
                'total_frais_annexes' => $totalFraisAnnexes,
                'total_especes' => $totalEspeces,
                'total_mobile' => $totalMobile,
                'solde_attendu' => (float)($session['fond_initial'] ?? 0) + $totalValide,
                'ecart_caisse' => (float)($session['ecart_caisse'] ?? 0)
            ],
            'data' => $paiements
        ]);
    }

    public function edition($idParam)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_CAISSE);
        $id = $this->validator->decrypter($idParam);
        if (!$id && is_numeric($idParam)) {
            $id = $idParam;
        }

        $item = $this->model->getById($id);
        if (!$item) {
            $this->error('Session de caisse introuvable.', RACINE . 'session_caisse/list');
            return;
        }

        $this->loadView('../views/sessions_caisse/edit.php', [
            'item' => $item
        ]);
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_CAISSE);
        $data = $_POST;
        unset($data['csrf_token']);

        $id = $data['id_session'] ?? null;
        if (!$id) {
            $this->error("Identifiant de session manquant.");
            return;
        }

        $filteredData = $this->model->filterData($data);

        $res = $this->model->update($filteredData, $id);
        if ($res) {
            $this->success("Session de caisse mise à jour.", RACINE . 'session_caisse/list');
        } else {
            $this->error("Erreur lors de la mise à jour.");
        }
    }

    public function changer()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_CAISSE);
        $id = $_POST['id'] ?? null;
        $statut = $_POST['statut'] ?? null;

        if (!$id || !$statut) {
            $this->json(['status' => 0, 'message' => 'Paramètres invalides']);
            return;
        }

        $validStatuts = ['ouverte', 'cloturee', 'valide', 'rejete'];
        if (!in_array($statut, $validStatuts)) {
            $this->json(['status' => 0, 'message' => 'Statut invalide']);
            return;
        }

        $update = ['statut_session' => $statut];
        if ($statut === 'valide') {
            $update['user_validation'] = Auth::code();
        }

        $res = $this->model->update($update, $id);
        if ($res) {
            $this->json(['status' => 1, 'message' => 'Statut de session mis à jour avec succès']);
        } else {
            $this->json(['status' => 0, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    public function getDailyTotals()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_CAISSE, PERMISSIONS::OUVERTURE_CAISSE, PERMISSIONS::CLOTURE_CAISSE]);
        $date = $_GET['date'] ?? ($_POST['date'] ?? date('Y-m-d'));
        $data = $this->model->getDailyFinancialTotals($date);
        $this->json($data);
    }
}
