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
        $this->loadView('../views/paiements/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $paiements = $this->model->getAll();
        $data = [];

        foreach ($paiements as $p) {
            $idCrypte = $this->validator->crypter($p['id_paiement']);
            $data[] = [
                'code' => $p['code_paiement'],
                'nom' => $p['ligne_commande_code'] ?? 'N/A',
                'montant' => $p['montant_paiement'],
                'mode' => $p['mode_paiement'],
                'statut' => $p['statut_paiement'],
                'id' => $p['id_paiement'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function clients()
    {
        $this->requireAuth();
        $campagneCode = $_SESSION[CAMPAIGN_SESSION]['code_campagne'] ?? '';
        if (!$campagneCode) {
            $this->json(['status' => 0, 'message' => 'Aucune campagne active'], 400);
            return;
        }
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $role = $_SESSION[USERS_AUTH]['role_user'] ?? 'commercial';
        $isAdmin = in_array($role, ['admin', 'superadmin']);

        $commandeModel = new ModelCommande();
        $clients = $commandeModel->getClientsByCampaignAndUser($campagneCode, $userCode, $isAdmin);
        $this->json(['status' => 1, 'data' => $clients]);
    }

    public function kits($clientCode)
    {
        $this->requireAuth();
        $campagneCode = $_SESSION[CAMPAIGN_SESSION]['code_campagne'] ?? '';
        if (!$campagneCode) {
            $this->json(['status' => 0, 'message' => 'Aucune campagne active'], 400);
            return;
        }
        $ligneModel = new ModelCommandeDetail();
        $lignes = $ligneModel->getByCommande($clientCode);
        $this->json(['status' => 1, 'data' => $lignes]);
    }

    public function calendar($ligneCode)
    {
        $this->requireAuth();
        $campagneCode = $_SESSION[CAMPAIGN_SESSION]['code_campagne'] ?? '';
        if (!$campagneCode) {
            $this->json(['status' => 0, 'message' => 'Aucune campagne active'], 400);
            return;
        }
        $campagneModel = new ModelCampagne();
        $campagne = $campagneModel->getByCode($campagneCode);
        if (!$campagne) {
            $this->json(['status' => 0, 'message' => 'Campagne introuvable'], 404);
            return;
        }

        $ligneModel = new ModelCommandeDetail();
        $ligne = $ligneModel->getByElement('code_commande_detail', $ligneCode);
        if (!$ligne) {
            $this->json(['status' => 0, 'message' => 'Ligne de commande introuvable'], 404);
            return;
        }

        $paiementModel = new ModelPaiement();
        $paiements = $paiementModel->getByLigneCode($ligneCode);
        $paiementsByDate = [];
        foreach ($paiements as $p) {
            $date = date('Y-m-d', strtotime($p['created_at_paiement']));
            $paiementsByDate[$date] = $p;
        }

        $debut = new DateTime($campagne['date_debut_campagne']);
        $fin = new DateTime($campagne['date_fin_campagne']);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($debut, $interval, $fin->modify('+1 day'));

        $days = [];
        $jourNum = 1;
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $days[] = [
                'date' => $dateStr,
                'jour_num' => $jourNum,
                'has_payment' => isset($paiementsByDate[$dateStr]),
                'payment' => $paiementsByDate[$dateStr] ?? null
            ];
            $jourNum++;
        }

        $this->json([
            'status' => 1,
            'campagne' => $campagne,
            'ligne' => $ligne,
            'days' => $days
        ]);
    }

    public function pay()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields([
            'commande_code' => $_POST['commande_code'] ?? '',
            'montant_paiement' => $_POST['montant_paiement'] ?? ''
        ]);

        if ($notEmpty === true) {
            $code = $this->post('code') ?: $this->validator->generateCode(TABLES::PAIEMENTS, 'code_paiement', 'PAY-', 6);
            $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
            $mode = in_array($this->post('mode_paiement'), ['especes', 'orange_money', 'mtn_money', 'wave']) ? $this->post('mode_paiement') : 'especes';
            $datePaiement = $this->post('date_paiement');
            $createdAt = $datePaiement
                ? date('Y-m-d H:i:s', strtotime($datePaiement . ' ' . date('H:i:s')))
                : date('Y-m-d H:i:s');
            $data = [
                'code_paiement' => $code,
                'commande_code' => $this->post('commande_code'),
                'montant_paiement' => $this->post('montant_paiement'),
                'mode_paiement' => $mode,
                'user_code' => $userCode,
                'statut_paiement' => 'valide',
                'created_at_paiement' => $createdAt
            ];

            if ($this->model->create($data)) {
                $this->success('Paiement enregistré avec succès!', ['paiement_code' => $code]);
            } else {
                $this->error('Erreur lors de l\'enregistrement du paiement');
            }
        } else {
            $this->error('Champs requis manquants');
        }
    }

    public function sessions()
    {
        $this->requireAuth();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        try {
            $sql = "SELECT * FROM session_caisses WHERE user_code = ? AND statut_session = 'ouverte' ORDER BY date_ouverture DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$userCode]);
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $options = [];
            foreach ($sessions as $s) {
                $options[$s['code_session']] = $s['code_session'] . ' (' . date('d/m/Y H:i', strtotime($s['date_ouverture'])) . ')';
            }
            $this->json(['options' => $options]);
        } catch (Exception $e) {
            error_log('[PaiementController::sessions] ' . $e->getMessage());
            $this->json(['options' => []]);
        }
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields([
            'commande_code' => $_POST['commande_code'] ?? '',
            'montant_paiement' => $_POST['montant_paiement'] ?? ''
        ]);

        if ($notEmpty === true) {
            $code = $this->post('code') ?: $this->validator->generateCode(TABLES::PAIEMENTS, 'code_paiement', 'PAY-', 6);
            $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
            $mode = in_array($this->post('mode_paiement'), ['especes', 'orange_money', 'mtn_money', 'wave']) ? $this->post('mode_paiement') : 'especes';
            $datePaiement = $this->post('date_paiement');
            $createdAt = $datePaiement
                ? date('Y-m-d H:i:s', strtotime($datePaiement . ' ' . date('H:i:s')))
                : date('Y-m-d H:i:s');
            $data = [
                'code_paiement' => $code,
                'commande_code' => $this->post('commande_code'),
                'montant_paiement' => $this->post('montant_paiement'),
                'mode_paiement' => $mode,
                'user_code' => $userCode,
                'statut_paiement' => 'valide',
                'created_at_paiement' => $createdAt
            ];

            if ($this->model->create($data)) {
                $this->success('Paiement ajouté!');
            } else {
                $this->error('Erreur ajout');
            }
        } else {
            $this->error('Champs requis!');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields([
            'commande_code' => $_POST['commande_code'] ?? '',
            'montant_paiement' => $_POST['montant_paiement'] ?? '',
            'id_paiement' => $_POST['id_paiement'] ?? ''
        ]);

        if ($notEmpty === true) {
            $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
            $mode = in_array($this->post('mode_paiement'), ['especes', 'orange_money', 'mtn_money', 'wave']) ? $this->post('mode_paiement') : 'especes';
            $statut = in_array($this->post('statut_paiement'), ['valide', 'annule', 'en_attente']) ? $this->post('statut_paiement') : 'valide';
            $id = (int) $this->post('id_paiement');

            $data = [
                'commande_code' => $this->post('commande_code'),
                'montant_paiement' => $this->post('montant_paiement'),
                'mode_paiement' => $mode,
                'user_code' => $userCode,
                'statut_paiement' => $statut,
                'updated_at_paiement' => date('Y-m-d H:i:s')
            ];

            if ($this->model->update($data)) {
                $this->success('Paiement modifié!');
            } else {
                $this->error('Erreur modification');
            }
        } else {
            $this->error('Champs requis!');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Paiement introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $paiementId = $this->validator->decrypter($details);
            $paiementProfile = $this->model->getById($paiementId);
            if (!$paiementProfile) {
                header('Location: ' . RACINE . 'paiement/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($paiementId);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'paiement/list');
            exit();
        }

        $this->loadView('../views/paiements/details.php', ['paiement' => $paiementProfile, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $decryptedId = $this->validator->decrypter($details);
            $paiement = $this->model->getById($decryptedId);

            if (!$paiement) {
                header('Location: ' . RACINE . 'paiement/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'paiement/list');
            exit();
        }

        $this->loadView('../views/paiements/edit.php', ['paiement' => $paiement]);
    }
}