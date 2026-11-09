<?php

class EtablissementController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEtablissement();
    }

    private function getSingleItem()
    {
        $items = $this->model->getAll();
        $etab = !empty($items) ? $items[0] : [
            'id_etablissement' => 1,
            'libelle_etablissement' => 'Institut Supérieur GEICG',
            'numero_autorisation_etablissement' => '',
            'telephone_etablissement' => '',
            'telephone_etablissement2' => '',
            'email_etablissement' => '',
            'slogan_etablissement' => '',
            'adresse_etablissement' => '',
            'statut_etablissement' => 'actif'
        ];

        $modelConfig = new ModelConfiguration();
        $conf = $modelConfig->getConfig();

        return array_merge($etab, $conf ?: []);
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETABLISSEMENT);
        $item = $this->getSingleItem();
        $this->loadView('../views/etablissements/config.php', ['item' => $item]);
    }

    public function config()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETABLISSEMENT);
        $item = $this->getSingleItem();
        $this->loadView('../views/etablissements/config.php', ['item' => $item]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETABLISSEMENT);
        $item = $this->getSingleItem();
        $this->loadView('../views/etablissements/config.php', ['item' => $item]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETABLISSEMENT);
        $item = $this->getSingleItem();
        $this->loadView('../views/etablissements/config.php', ['item' => $item]);
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETABLISSEMENT);
        $item = $this->getSingleItem();
        $this->loadView('../views/etablissements/config.php', ['item' => $item]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETABLISSEMENT);
        $item = $this->getSingleItem();
        $id = $item['id_etablissement'] ?? 1;
        $idCrypte = $this->validator->crypter($id);
        $this->json(['data' => [array_merge($item, ['id' => $id, 'editId' => $idCrypte])]]);
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETABLISSEMENT);
        $id = (int)$this->post('id_etablissement');
        $data = $_POST;
        unset($data['csrf_token']);
        $this->cleanPhoneFields($data);

        if (!empty($_FILES['logo_file']['name']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            if (in_array($ext, $allowedExts)) {
                $filename = 'logo_' . time() . '.' . $ext;
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $targetFile)) {
                    $data['logo_etablissement'] = 'public/uploads/logos/' . $filename;
                }
            }
        }

        // Configuration institutionnelle (table configurations) : MAJ ciblée des champs soumis
        $modelConfig = new ModelConfiguration();
        $allowedConfigFields = [
            'nom_etablissement', 'sigle_etablissement', 'slogan_etablissement',
            'numero_autorisation', 'ministere_tutelle', 'adresse_etablissement',
            'boite_postale', 'ville_etablissement', 'pays_etablissement',
            'telephone_fixe', 'telephone_mobile', 'telephone_mobile2',
            'email_etablissement', 'site_web', 'titre_direction_pedagogique',
            'nom_directeur_etudes', 'titre_direction_generale', 'nom_directeur_general',
            'titre_service_finance', 'nom_responsable_finance', 'titre_service_scolarite',
            'nom_responsable_scolarite', 'nom_secretaire_general', 'mention_cachet_signature',
            'ville_signature', 'texte_pied_page', 'prefixe_reference', 'logo_etablissement'
        ];

        $configData = [];
        foreach ($allowedConfigFields as $field) {
            if (array_key_exists($field, $data)) {
                $configData[$field] = $data[$field];
            }
        }
        if (!empty($configData)) {
            $modelConfig->saveConfig($configData);
        }

        // Synchronisation des slugs et champs de base dans la table etablissements
        if (isset($data['use_slug_filiere']) || isset($data['use_slug_cycle']) || isset($data['use_slug_niveau']) || isset($data['section_parametres'])) {
            $data['use_slug_filiere'] = !empty($data['use_slug_filiere']) ? 1 : 0;
            $data['use_slug_cycle'] = !empty($data['use_slug_cycle']) ? 1 : 0;
            $data['use_slug_niveau'] = !empty($data['use_slug_niveau']) ? 1 : 0;
        }

        if (isset($configData['nom_etablissement'])) {
            $data['libelle_etablissement'] = $configData['nom_etablissement'];
        }
        if (isset($configData['numero_autorisation'])) {
            $data['numero_autorisation_etablissement'] = $configData['numero_autorisation'];
        }
        if (isset($configData['telephone_mobile'])) {
            $data['telephone_etablissement'] = $configData['telephone_mobile'];
        }
        if (isset($configData['telephone_mobile2'])) {
            $data['telephone_etablissement2'] = $configData['telephone_mobile2'];
        }

        $filteredData = $this->model->filterData($data);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($id && $this->model->getById($id)) {
            if (!empty($filteredData)) {
                $this->model->update($filteredData, $id);
            }
            $this->logAction(
                AuditLogger::ACTION_UPDATE,
                'etablissements',
                $id,
                "Mise à jour de la configuration de l'établissement: " . ($configData['nom_etablissement'] ?? ''),
                ['id_etablissement' => $id, 'fields' => array_keys($data)]
            );
        } else {
            $filteredData['code_etablissement'] = $this->validator->generateCode(TABLES::ETABLISSEMENTS, 'code_etablissement', 'ETA-', 8);
            $filteredData['statut_etablissement'] = STATUTS::ACTIF;
            $filteredData['created_at_etablissement'] = date('Y-m-d H:i:s');
            $newId = $this->model->create($filteredData);
            $this->logAction(
                AuditLogger::ACTION_CREATE,
                'etablissements',
                $newId,
                "Création de l'établissement: " . ($configData['nom_etablissement'] ?? ''),
                ['id_etablissement' => $newId, 'code' => $filteredData['code_etablissement']]
            );
        }

        Session::remove('etablissement_config');

        $sectionName = $this->post('section_name') ?: 'Configuration';
        $message = "{$sectionName} enregistrée avec succès !";
        $responseData = [
            'section' => $this->post('section_id') ?? ''
        ];
        if (!empty($data['logo_etablissement'])) {
            $responseData['logo_url'] = RACINE . ltrim($data['logo_etablissement'], '/');
        }

        if ($isAjax) {
            $this->success($message, $responseData);
        } else {
            Session::set('flash_success', $message);
            header('Location: ' . RACINE . 'etablissement/config');
            exit();
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $success = !empty($statut) ? $this->model->updateStatus($id, $statut, 'statut_etablissement') : $this->model->toggleStatus($id);
            if ($success) {
                $this->logAction(
                    AuditLogger::ACTION_UPDATE,
                    'etablissements',
                    $id,
                    "Changement du statut de l'établissement ID #{$id}",
                    ['id_etablissement' => $id, 'nouveau_statut' => $statut]
                );
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Établissement introuvable');
        }
    }
}
