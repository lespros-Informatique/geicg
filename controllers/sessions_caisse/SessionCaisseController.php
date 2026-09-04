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
        $today = date('Y-m-d');
        $anneeCode = $_SESSION['annee_active_code'] ?? null;

        $db = $this->model->getCon();
        $totalSessions = (int)($db->query("SELECT COUNT(*) FROM sessions_caisse")->fetchColumn() ?: 0);
        $totalOuvertes = (int)($db->query("SELECT COUNT(*) FROM sessions_caisse WHERE statut_session = 'ouverte'")->fetchColumn() ?: 0);
        $totalCloturees = (int)($db->query("SELECT COUNT(*) FROM sessions_caisse WHERE statut_session IN ('cloturee', 'valide')")->fetchColumn() ?: 0);

        // Active session for today
        $activeSession = $this->model->getActiveSession($today);
        
        // Today's live collection totals from paiements
        $dailyTotals = $this->model->getDailyFinancialTotals($today);

        $this->loadView('../views/sessions_caisse/list.php', [
            'totalSessions' => $totalSessions,
            'totalOuvertes' => $totalOuvertes,
            'totalCloturees' => $totalCloturees,
            'activeSession' => $activeSession,
            'dailyTotals' => $dailyTotals
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
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
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
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
            $data['code_session'] = $this->validator->generateCode('sessions_caisse', 'code_session', 'SES-', 8);
        }
        $data['heure_ouverture'] = date('H:i:s');
        $data['statut_session'] = 'ouverte';
        $data['fond_initial'] = (float)($data['fond_initial'] ?? 0);
        $data['user_code'] = $userCode;
        $data['annee_code'] = $anneeCode;
        $data['etablissement_code'] = $etabCode;

        $cols = $this->model->getCon()->query("DESCRIBE sessions_caisse")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

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
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
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
        $id = $this->validator->decrypter($idParam);
        if (!$id && is_numeric($idParam)) {
            $id = $idParam;
        }

        $item = $this->model->getById($id);
        if (!$item) {
            $this->error('Session de caisse introuvable.', RACINE . 'session_caisse/list');
            return;
        }

        // Paiements réalisés au cours de la session
        $db = $this->model->getCon();
        $stmtP = $db->prepare("
            SELECT p.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, c.libelle_classe, ts.libelle_tranche
            FROM paiements p
            LEFT JOIN inscriptions i ON i.code_inscription = p.inscription_code
            LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN classes c ON c.code_classe = i.classe_code
            LEFT JOIN tranches_scolarite ts ON ts.code_tranche = p.tranche_code
            WHERE (p.session_caisse_code = ? OR (p.session_caisse_code IS NULL AND DATE(p.date_paiement) = ?))
              AND p.statut_paiement != 'annule'
            ORDER BY p.id_paiement DESC
        ");
        $stmtP->execute([$item['code_session'] ?? '', $item['date_session']]);
        $paiements = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/sessions_caisse/details.php', [
            'item' => $item,
            'paiements' => $paiements
        ]);
    }

    public function edition($idParam)
    {
        $this->requireAuth();
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
        $data = $_POST;
        unset($data['csrf_token']);

        $id = $data['id_session'] ?? null;
        if (!$id) {
            $this->error("Identifiant de session manquant.");
            return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE sessions_caisse")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

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
            $update['user_validation'] = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        }

        $res = $this->model->update($id, $update);
        if ($res) {
            $this->json(['status' => 1, 'message' => 'Statut de session mis à jour avec succès']);
        } else {
            $this->json(['status' => 0, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    public function getDailyTotals()
    {
        $this->requireAuth();
        $date = $_GET['date'] ?? ($_POST['date'] ?? date('Y-m-d'));
        $data = $this->model->getDailyFinancialTotals($date);
        $this->json($data);
    }
}
