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

        // Configuration institutionnelle (table configurations)
        $modelConfig = new ModelConfiguration();
        $configData = [
            'nom_etablissement' => $data['nom_etablissement'] ?? ($data['libelle_etablissement'] ?? ''),
            'sigle_etablissement' => $data['sigle_etablissement'] ?? 'GROUPE EICG',
            'slogan_etablissement' => $data['slogan_etablissement'] ?? '',
            'numero_autorisation' => $data['numero_autorisation'] ?? ($data['numero_autorisation_etablissement'] ?? ''),
            'ministere_tutelle' => $data['ministere_tutelle'] ?? '',
            'adresse_etablissement' => $data['adresse_etablissement'] ?? '',
            'boite_postale' => $data['boite_postale'] ?? '',
            'ville_etablissement' => $data['ville_etablissement'] ?? 'Bouaké',
            'pays_etablissement' => $data['pays_etablissement'] ?? 'Côte d\'Ivoire',
            'telephone_fixe' => $data['telephone_fixe'] ?? '',
            'telephone_mobile' => $data['telephone_mobile'] ?? ($data['telephone_etablissement'] ?? ''),
            'telephone_mobile2' => $data['telephone_mobile2'] ?? ($data['telephone_etablissement2'] ?? ''),
            'email_etablissement' => $data['email_etablissement'] ?? '',
            'site_web' => $data['site_web'] ?? '',
            'titre_direction_pedagogique' => $data['titre_direction_pedagogique'] ?? '',
            'titre_direction_generale' => $data['titre_direction_generale'] ?? '',
            'titre_service_finance' => $data['titre_service_finance'] ?? '',
            'mention_cachet_signature' => $data['mention_cachet_signature'] ?? '',
            'ville_signature' => $data['ville_signature'] ?? 'Bouaké',
            'texte_pied_page' => $data['texte_pied_page'] ?? '',
            'prefixe_reference' => $data['prefixe_reference'] ?? 'REF-',
        ];
        if (!empty($data['logo_etablissement'])) {
            $configData['logo_etablissement'] = $data['logo_etablissement'];
        }
        $modelConfig->saveConfig($configData);

        // Synchronisation des slugs et champs de base dans la table etablissements
        $data['use_slug_filiere'] = isset($data['use_slug_filiere']) ? (int)$data['use_slug_filiere'] : 0;
        $data['use_slug_cycle'] = isset($data['use_slug_cycle']) ? (int)$data['use_slug_cycle'] : 0;
        $data['use_slug_niveau'] = isset($data['use_slug_niveau']) ? (int)$data['use_slug_niveau'] : 0;
        if (!empty($configData['nom_etablissement'])) {
            $data['libelle_etablissement'] = $configData['nom_etablissement'];
        }
        if (!empty($configData['numero_autorisation'])) {
            $data['numero_autorisation_etablissement'] = $configData['numero_autorisation'];
        }
        if (!empty($configData['telephone_mobile'])) {
            $data['telephone_etablissement'] = $configData['telephone_mobile'];
        }
        if (!empty($configData['telephone_mobile2'])) {
            $data['telephone_etablissement2'] = $configData['telephone_mobile2'];
        }

        $filteredData = $this->model->filterData($data);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($id && $this->model->getById($id)) {
            $this->model->update($filteredData, $id);
        } else {
            $filteredData['code_etablissement'] = $this->validator->generateCode(TABLES::ETABLISSEMENTS, 'code_etablissement', 'ETA-', 8);
            $filteredData['statut_etablissement'] = STATUTS::ACTIF;
            $filteredData['created_at_etablissement'] = date('Y-m-d H:i:s');
            $this->model->create($filteredData);
        }

        Session::remove('etablissement_config');

        if ($isAjax) {
            $this->success('Configuration de l\'établissement mise à jour avec succès!');
        } else {
            Session::set('flash_success', 'Configuration de l\'établissement mise à jour avec succès!');
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
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Établissement introuvable');
        }
    }
}
