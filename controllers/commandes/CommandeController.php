<?php

class CommandeController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelCommande();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/commandes/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();

        if ($pressingCode !== null) {
            $commandes = $this->model->getByPressing($pressingCode);
        } else {
            $commandes = $this->model->getAll();
        }

        $data = [];

        foreach ($commandes as $c) {
            $idCrypte = $this->validator->crypter($c['id_commande']);
            $data[] = [
                'code' => $c['code_commande'],
                'client' => $c['client_code'] ?? 'N/A',
                'client_nom' => $c['client_code'] ?? 'N/A',
                'user' => $c['user_code'] ?? 'N/A',
                'date' => $c['date_livraison_commande'] ?? '',
                'montant' => $c['montant_total_commande'] ?? 0,
                'statut' => $c['statut_commande'],
                'id' => $c['id_commande'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['client_code' => $_POST['client_code'] ?? '']);

        if ($notEmpty === true) {
            if ($this->validator->getByElement(TABLES::COMMANDES, 'code_commande', $this->post('code'))) {
                $this->error('Ce code commande existe déjà!');
            } else {
                $code = $this->post('code') ?: $this->validator->generateCode(TABLES::COMMANDES, 'code_commande', 'CMD-', 6);
                $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';

                $data = [
                    'code_commande' => $code,
                    'pressing_code' => $this->getCurrentPressingCode() ?: $this->post('pressing_code'),
                    'client_code' => $this->post('client_code'),
                    'user_code' => $userCode,
                    'remise_commande' => $this->post('remise_commande') ?: 0,
                    'frais_collecte_commande' => $this->post('frais_collecte_commande') ?: 0,
                    'frais_livraison_commande' => $this->post('frais_livraison_commande') ?: 0,
                    'montant_total_commande' => $this->post('montant_total_commande') ?: 0,
                    'observation_commande' => $this->post('observation_commande') ?? '',
                    'date_livraison_commande' => $this->post('date_livraison_commande') ?: null,
                    'statut_commande' => 'actif',
                    'statut_suivi_commande' => 'creee',
                    'created_at_commande' => date('Y-m-d H:i:s')
                ];

                if ($this->model->create($data)) {
                    $this->success('Commande ajoutée avec succès!', ['commande_code' => $code]);
                } else {
                    $this->error('Erreur lors de l\'ajout');
                }
            }
        } else {
            $this->error('Veuillez renseigner tous les champs!');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['client_code' => $_POST['client_code'] ?? '', 'id_commande' => $_POST['id_commande'] ?? '']);

        if ($notEmpty === true) {
            $statut = in_array($this->post('statut_commande'), ['actif', 'inactif']) ? $this->post('statut_commande') : 'actif';
            $id = (int) $this->post('id_commande');

            $data = [
                'client_code' => $this->post('client_code'),
                'user_code' => $_SESSION[USERS_AUTH]['code_user'] ?? '',
                'remise_commande' => $this->post('remise_commande') ?: 0,
                'frais_collecte_commande' => $this->post('frais_collecte_commande') ?: 0,
                'frais_livraison_commande' => $this->post('frais_livraison_commande') ?: 0,
                'montant_total_commande' => $this->post('montant_total_commande') ?: 0,
                'observation_commande' => $this->post('observation_commande') ?? '',
                'date_livraison_commande' => $this->post('date_livraison_commande') ?: null,
                'statut_commande' => $statut,
                'updated_at_commande' => date('Y-m-d H:i:s')
            ];

            if ($this->model->update($data)) {
                $this->success('Commande modifiée avec succès!');
            } else {
                $this->error('Erreur lors de la modification');
            }
        } else {
            $this->error('Veuillez renseigner tous les champs!');
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
            $this->error('Commande introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $item = null;
        $id = null;

        // 1. Décryptage ID
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getWithDetails($id);
        } catch (Exception $e) {
            $item = null;
        }

        // 2. Recherche par code commande direct
        if (!$item) {
            $item = $this->model->getByCodeWithDetails($details);
            if ($item) {
                $id = $item['id_commande'];
            }
        }

        // 3. Recherche par ID direct
        if (!$item && is_numeric($details)) {
            $item = $this->model->getWithDetails((int)$details);
            if ($item) {
                $id = $item['id_commande'];
            }
        }

        if (!$item) {
            header('Location: ' . RACINE . 'commande/list');
            exit();
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');
        $encryptedId = $this->validator->crypter($id);

        $ligneModel = new ModelCommandeDetail();
        $lignes = $ligneModel->getByCommande($item['code_commande']);

        // Livreurs disponibles pour assignation
        $livreurModel = new ModelLivreur();
        $livreurs = $livreurModel->getByStatus('actif');

        // Missions rattachées à cette commande
        $missions = [];
        try {
            $stmtM = $this->model->getCon()->prepare("
                SELECT m.*, l.nom_livreur, l.telephone_livreur 
                FROM " . TABLES::MISSIONS . " m
                LEFT JOIN " . TABLES::LIVREURS . " l ON m.livreur_code = l.code_livreur
                WHERE m.commande_code = ?
                ORDER BY m.id_mission DESC
            ");
            $stmtM->execute([$item['code_commande']]);
            $missions = $stmtM->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $missions = [];
        }

        $this->loadView('../views/commandes/details.php', [
            'order' => $item,
            'encryptedId' => $encryptedId,
            'lignes' => $lignes,
            'livreurs' => $livreurs,
            'missions' => $missions
        ]);
    }

    public function accepter()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');

        if (!$code) {
            $this->error('Code commande requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'acceptee', updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        if ($stmt->execute([$code])) {
            // Notification au client
            NotificationService::notifyOrderAccepted(
                $item['client_code'],
                $code,
                $item['libelle_pressing'] ?? 'Le pressing'
            );
            $this->success('Commande acceptée avec succès !', ['reload' => true]);
        } else {
            $this->error('Erreur lors de l\'acceptation');
        }
    }

    public function refuser()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');
        $motif = trim($this->post('motif', ''));

        if (!$code) {
            $this->error('Code commande requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'refusee', updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        if ($stmt->execute([$code])) {
            // Notification au client
            NotificationService::notifyOrderRejected(
                $item['client_code'],
                $code,
                $item['libelle_pressing'] ?? 'Le pressing',
                $motif ?: null
            );
            $this->success('Commande refusée', ['reload' => true]);
        } else {
            $this->error('Erreur lors du refus');
        }
    }

    public function saisirDevisColis()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');
        $montant = (float) $this->post('montant_total');

        if (!$code || $montant <= 0) {
            $this->error('Code commande et montant valide requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET montant_total_commande = ?, statut_suivi_commande = 'prix_a_valider', updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        if ($stmt->execute([$montant, $code])) {
            // Notification au client pour validation du devis
            NotificationService::notifyColisPriceToConfirm(
                $item['client_code'],
                $code,
                $montant,
                $item['libelle_pressing'] ?? 'Le pressing'
            );
            $this->success('Devis enregistré ! Le client a été notifié pour confirmation.', ['reload' => true]);
        } else {
            $this->error('Erreur lors de l\'enregistrement du devis');
        }
    }

    public function lancerTraitement()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');

        if (!$code) {
            $this->error('Code commande requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'en_traitement', updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        if ($stmt->execute([$code])) {
            // Notification au client
            NotificationService::notifyProcessingStarted($item['client_code'], $code);
            $this->success('Traitement du linge démarré !', ['reload' => true]);
        } else {
            $this->error('Erreur lors de la mise à jour');
        }
    }

    public function marquerPrete()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');

        if (!$code) {
            $this->error('Code commande requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'prete', updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        if ($stmt->execute([$code])) {
            // Notification au client
            NotificationService::notifyOrderReady($item['client_code'], $code);
            $this->success('Commande marquée comme prête !', ['reload' => true]);
        } else {
            $this->error('Erreur lors de la mise à jour');
        }
    }

    public function assignerLivreur()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');
        $livreurCode = $this->post('livreur_code');
        $typeMission = $this->post('type_mission') ?: 'livraison'; // 'collecte' ou 'livraison'

        if (!$code || !$livreurCode) {
            $this->error('Commande et livreur requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');

        // Récupérer le nom du livreur
        $livreurModel = new ModelLivreur();
        $livreur = $this->validator->getByElement(TABLES::LIVREURS, 'code_livreur', $livreurCode);
        $nomLivreur = $livreur ? ($livreur['nom_livreur'] ?? 'Le livreur') : 'Le livreur';

        // Créer la mission
        $missionModel = new ModelMission();
        $missionCode = $this->validator->generateCode(TABLES::MISSIONS, 'code_mission', 'MIS-', 6);
        $missionData = [
            'code_mission' => $missionCode,
            'commande_code' => $code,
            'livreur_code' => $livreurCode,
            'type_mission' => $typeMission,
            'adresse_mission' => $item['adresse_client'] ?? '',
            'statut_mission' => 'en_attente',
            'created_at_mission' => date('Y-m-d H:i:s')
        ];
        $missionModel->create($missionData);

        // Mettre à jour le statut de suivi de la commande
        $nouveauStatut = ($typeMission === 'collecte') ? 'collecte_programmee' : 'en_livraison';
        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = ?, updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute([$nouveauStatut, $code]);

        // Notifications
        if ($typeMission === 'collecte') {
            NotificationService::notifyDriverAssigned($item['client_code'], $code, $nomLivreur);
        } else {
            NotificationService::notifyDeliveryEnRoute($item['client_code'], $code, $nomLivreur);
        }

        $this->success("Livreur {$nomLivreur} assigné avec succès !", ['reload' => true]);
    }

    public function transition()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int) $this->post('id_commande');
        $next = $this->post('statut_suivi_commande');

        if (!$id || !$next) {
            $this->error('Identifiant et statut requis');
            return;
        }

        $allowed = array_keys(STATUTS::SUIVI_COMMANDES);

        if (!in_array($next, $allowed, true)) {
            $this->error('Statut de suivi non reconnu');
            return;
        }

        $item = $this->model->getWithDetails($id);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = ?, updated_at_commande = NOW() WHERE id_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);

        if ($stmt->execute([$next, $id])) {
            // Déclenchement automatique de la notification selon le statut
            $clientCode = $item['client_code'] ?? '';
            $orderCode = $item['code_commande'] ?? '';
            $pressingName = $item['libelle_pressing'] ?? 'Le pressing';

            switch ($next) {
                case 'acceptee':
                    NotificationService::notifyOrderAccepted($clientCode, $orderCode, $pressingName);
                    break;
                case 'refusee':
                    NotificationService::notifyOrderRejected($clientCode, $orderCode, $pressingName);
                    break;
                case 'collectee':
                    NotificationService::notifyCollectionCompleted($clientCode, $orderCode);
                    break;
                case 'recue_pressing':
                    NotificationService::notifyReceivedAtPressing($clientCode, $orderCode, $pressingName);
                    break;
                case 'en_traitement':
                    NotificationService::notifyProcessingStarted($clientCode, $orderCode);
                    break;
                case 'prete':
                    NotificationService::notifyOrderReady($clientCode, $orderCode);
                    break;
                case 'en_livraison':
                    NotificationService::notifyDeliveryEnRoute($clientCode, $orderCode);
                    break;
                case 'livree':
                    NotificationService::notifyOrderDelivered($clientCode, $orderCode);
                    break;
            }

            $this->success("Statut de la commande mis à jour vers {$next}", ['reload' => true]);
        } else {
            $this->error('Erreur lors de la mise à jour');
        }
    }
}