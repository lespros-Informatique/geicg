<?php

class NotificationController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelNotification();
    }

    public function list()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();
        $stats = $this->model->getStats($pressingCode);

        // Récupérer la liste des clients pour le formulaire d'envoi
        $clientModel = new ModelClient();
        $clients = $clientModel->getAll();

        $this->loadView('../views/notifications/list.php', [
            'stats' => $stats,
            'clients' => $clients
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();
        $items = $this->model->getAllWithClient($pressingCode);
        $data = [];

        foreach ($items as $i) {
            $idCrypte = $this->validator->crypter($i['id_notification']);
            $isLu = ((int)$i['lu_notification'] === 1);
            $clientName = $i['nom_client'] ?: ($i['client_code'] ?: 'Tous les clients (Global)');

            $data[] = [
                'id' => (int)$i['id_notification'],
                'editId' => $idCrypte,
                'code' => $i['code_notification'],
                'type' => $i['type_notification'],
                'titre' => $i['titre_notification'],
                'message' => $i['message_notification'],
                'reference' => $i['reference_code'] ?? '',
                'client_code' => $i['client_code'] ?? '',
                'client_nom' => $clientName,
                'client_telephone' => $i['telephone_client'] ?? '',
                'lu' => $isLu ? 1 : 0,
                'statut' => $i['statut_notification'] ?? 'envoyee',
                'created_at' => $i['created_at_notification'] ? date('d/m/Y H:i', strtotime($i['created_at_notification'])) : '-'
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $titre = trim($this->post('titre_notification'));
        $message = trim($this->post('message_notification'));
        $type = $this->post('type_notification') ?: 'alerte';
        $clientCode = $this->post('client_code') ?: null;

        if (empty($titre) || empty($message)) {
            $this->error('Veuillez renseigner le titre et le message de la notification !');
            return;
        }

        try {
            if ($clientCode === 'ALL' || empty($clientCode)) {
                // Envoi à tous les clients
                $clientModel = new ModelClient();
                $allClients = $clientModel->getAll();
                $count = 0;
                foreach ($allClients as $cl) {
                    if (!empty($cl['code_client'])) {
                        NotificationService::notifyClient(
                            $cl['code_client'],
                            $type,
                            $titre,
                            $message,
                            null,
                            ['broadcast' => true]
                        );
                        $count++;
                    }
                }
                $this->success("Notification diffusée avec succès à {$count} client(s) !");
            } else {
                // Envoi ciblé
                $code = NotificationService::notifyClient(
                    $clientCode,
                    $type,
                    $titre,
                    $message,
                    null,
                    ['direct' => true]
                );

                if ($code) {
                    $this->success('Notification envoyée avec succès au client !');
                } else {
                    $this->error('Erreur lors de l\'envoi de la notification');
                }
            }
        } catch (Exception $e) {
            $this->error('Erreur technique : ' . $e->getMessage());
        }
    }

    public function marquerLu()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');

        if ($id && $this->model->markAsRead($id)) {
            $this->success('Notification marquée comme lue !');
        } else {
            $this->error('Impossible de mettre à jour la notification');
        }
    }

    public function marquerToutLu()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if ($this->model->markAllAsRead()) {
            $this->success('Toutes les notifications ont été marquées comme lues !');
        } else {
            $this->error('Erreur lors de la mise à jour');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');

        if ($id && $this->model->toggleRead($id)) {
            $this->success('Statut de lecture mis à jour !', ['reload' => true]);
        } else {
            $this->error('Notification introuvable');
        }
    }

    public function delete()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');

        if ($id && $this->model->deleteNotification($id)) {
            $this->success('Notification supprimée avec succès !');
        } else {
            $this->error('Erreur lors de la suppression');
        }
    }

    public function stats()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();
        $stats = $this->model->getStats($pressingCode);
        $this->json($stats);
    }
}
