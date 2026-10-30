<?php

class EtudiantController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEtudiant();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ETUDIANTS);
        $deps = $this->model->getListFilterDependencies();
        $anneeActive = $this->getActiveAnneeCode();

        $this->loadView('../views/etudiants/list.php', [
            'annees' => $deps['annees'],
            'niveaux' => $deps['niveaux'],
            'filieres' => $deps['filieres'],
            'classes' => $deps['classes'],
            'anneeActive' => $anneeActive
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ETUDIANTS);
        $anneeCode = $_GET['annee_code'] ?? ($_POST['annee_code'] ?? '');
        if ($anneeCode === '' || $anneeCode === null) {
            $anneeCode = $this->getActiveAnneeCode();
        }

        $filters = [
            'annee_code' => $anneeCode,
            'niveau_code' => $_GET['niveau_code'] ?? ($_POST['niveau_code'] ?? ''),
            'filiere_code' => $_GET['filiere_code'] ?? ($_POST['filiere_code'] ?? ''),
            'classe_code' => $_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''),
            'statut_etudiant' => $_GET['statut_etudiant'] ?? ($_POST['statut_etudiant'] ?? ''),
            'affectation_etat' => $_GET['affectation_etat'] ?? ($_POST['affectation_etat'] ?? ''),
            'canal_inscription' => $_GET['canal_inscription'] ?? ($_POST['canal_inscription'] ?? ''),
            'statut_inscription' => $_GET['statut_inscription'] ?? ($_POST['statut_inscription'] ?? '')
        ];

        $etabCfg = $this->getEtablissementConfig();
        $useSlugFiliere = !empty($etabCfg['use_slug_filiere']);
        $useSlugNiveau = !empty($etabCfg['use_slug_niveau']);
        $useSlugCycle = !empty($etabCfg['use_slug_cycle']);

        $items = $this->model->getFilteredRegistry($filters);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_etudiant'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'use_slug_filiere' => $useSlugFiliere,
                'use_slug_niveau' => $useSlugNiveau,
                'use_slug_cycle' => $useSlugCycle
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETUDIANTS);
        $data = $_POST;
        unset($data['csrf_token']);
        $this->cleanPhoneFields($data);

        // Aliases de compatibilité des champs du formulaire
        if (isset($data['date_naissance']) && !isset($data['date_naissance_etudiant'])) {
            $data['date_naissance_etudiant'] = $data['date_naissance'];
        }
        if (isset($data['lieu_naissance']) && !isset($data['lieu_naissance_etudiant'])) {
            $data['lieu_naissance_etudiant'] = $data['lieu_naissance'];
        }
        if (isset($data['nationalite']) && !isset($data['nationalite_etudiant'])) {
            $data['nationalite_etudiant'] = $data['nationalite'];
        }
        if (isset($data['adresse_etudiant']) && !isset($data['lieu_residence_etudiant'])) {
            $data['lieu_residence_etudiant'] = $data['adresse_etudiant'];
        }

        if (!empty($data['matricule_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'matricule_etudiant', $data['matricule_etudiant'], 'Matricule etudiant')) return;
        }
        if (!empty($data['email_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'email_etudiant', $data['email_etudiant'], 'Email etudiant')) return;
        }
        if (!empty($data['telephone_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'telephone_etudiant', $data['telephone_etudiant'], 'Telephone etudiant')) return;
        }

        $userCode = Auth::code();
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $nomEtudiant = trim($data['nom_etudiant'] ?? '');
        $prenomEtudiant = trim($data['prenom_etudiant'] ?? '');

        if (empty($data['matricule_etudiant'])) {
            $data['matricule_etudiant'] = $this->model->generateMatricule($nomEtudiant, $prenomEtudiant, $data['classe_code'] ?? null, $anneeCode);
        }
        if (empty($data['code_etudiant'])) {
            $data['code_etudiant'] = $this->validator->generateCode(TABLES::ETUDIANTS, 'code_etudiant', 'ETU-', 8);
        }
        $data['statut_etudiant'] = $data['statut_etudiant'] ?? STATUTS::ACTIF;
        $data['created_at_etudiant'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $this->success('Item créé avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::EDIT_ETUDIANT, PERMISSIONS::MANAGE_ETUDIANTS]);
        $id = (int)$this->post('id_etudiant');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $this->cleanPhoneFields($data);

        // Aliases de compatibilité des champs du formulaire
        if (isset($data['date_naissance']) && !isset($data['date_naissance_etudiant'])) {
            $data['date_naissance_etudiant'] = $data['date_naissance'];
        }
        if (isset($data['lieu_naissance']) && !isset($data['lieu_naissance_etudiant'])) {
            $data['lieu_naissance_etudiant'] = $data['lieu_naissance'];
        }
        if (isset($data['nationalite']) && !isset($data['nationalite_etudiant'])) {
            $data['nationalite_etudiant'] = $data['nationalite'];
        }
        if (isset($data['adresse_etudiant']) && !isset($data['lieu_residence_etudiant'])) {
            $data['lieu_residence_etudiant'] = $data['adresse_etudiant'];
        }

        if (!empty($data['matricule_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'matricule_etudiant', $data['matricule_etudiant'], 'Matricule etudiant', 'id_etudiant', $id)) return;
        }
        if (!empty($data['email_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'email_etudiant', $data['email_etudiant'], 'Email etudiant', 'id_etudiant', $id)) return;
        }
        if (!empty($data['telephone_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'telephone_etudiant', $data['telephone_etudiant'], 'Telephone etudiant', 'id_etudiant', $id)) return;
        }

        $filteredData = $this->model->filterData($data);
        $encryptedId = $this->validator->crypter($id);
        $redirectUrl = RACINE . 'etudiant/details/' . $encryptedId;

        if ($this->model->update($filteredData, $id)) {
            if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                Session::flash('flash_success', "Fiche de l'étudiant modifiée avec succès !");
                header('Location: ' . $redirectUrl);
                exit;
            }
            $this->success("Fiche de l'étudiant modifiée avec succès !", [
                'redirect' => $redirectUrl,
                'encryptedId' => $encryptedId
            ]);
        } else {
            $err = $this->model->getLastError() ?: 'Erreur lors de la modification de la fiche étudiant.';
            if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                Session::flash('flash_error', $err);
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? $redirectUrl));
                exit;
            }
            $this->error($err);
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETUDIANTS);
        $id = $this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Item introuvable');
        }
    }

    public function changerClassePage($details)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CHANGER_CLASSE_ETUDIANT, PERMISSIONS::MANAGE_ETUDIANTS]);

        try {
            $id = $this->validator->decrypter($details);
            if (empty($id)) {
                $id = $details;
            }

            $item = $this->model->getById($id);
            if (!$item) {
                Session::flash('flash_error', "Étudiant introuvable.");
                header('Location: ' . RACINE . 'etudiant/list');
                exit;
            }

            $anneeActive = $this->getActiveAnneeCode();
            $data = $this->model->getChangerClasseData($item['code_etudiant'], $anneeActive);
            $encryptedId = $this->validator->crypter($item['id_etudiant']);

            $this->loadView('../views/etudiants/changer_classe.php', [
                'item' => $item,
                'inscription' => $data['inscription'],
                'classes' => $data['classes'],
                'annees' => $data['annees'],
                'encryptedId' => $encryptedId,
                'activeAnneeCode' => !empty($data['inscription']['annee_code']) ? $data['inscription']['annee_code'] : $anneeActive
            ]);

        } catch (Exception $e) {
            Session::flash('flash_error', "Erreur : " . $e->getMessage());
            header('Location: ' . RACINE . 'etudiant/list');
            exit;
        }
    }

    public function changerClasse()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CHANGER_CLASSE_ETUDIANT, PERMISSIONS::MANAGE_ETUDIANTS]);

        $codeInscription = trim($_POST['code_inscription'] ?? '');
        $codeEtudiant = trim($_POST['code_etudiant'] ?? '');
        $nouvelleClasse = trim($_POST['classe_code'] ?? ($_POST['nouvelle_classe_code'] ?? ''));
        $affectationInput = trim($_POST['affectation_etat'] ?? 'non_affecte');
        $affectationEtat = ($affectationInput === 'affecte' || $affectationInput === 'oui') ? 'oui' : 'non';
        $anneeCode = trim($_POST['annee_code'] ?? '');
        $montantScolarite = isset($_POST['montant_scolarite_inscription']) ? (float)$_POST['montant_scolarite_inscription'] : null;

        if (empty($nouvelleClasse)) {
            $this->error("Veuillez sélectionner une classe valide.");
            return;
        }

        $res = $this->model->changerClasseInscription(
            $codeInscription,
            $codeEtudiant,
            $nouvelleClasse,
            $affectationEtat,
            $anneeCode ?: $this->getActiveAnneeCode(),
            $montantScolarite
        );

        if ($res['success']) {
            $this->success($res['message'], ['redirect' => RACINE . 'etudiant/list']);
        } else {
            $this->error($res['message']);
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ETUDIANTS);
        try {
            $id = $this->validator->decrypter($details);
            if (empty($id)) {
                $id = $details;
            }
            $item = $this->model->getById($id);
            if (!$item && is_string($id)) {
                $item = $this->model->getByCode($id);
            }
            if (!$item && is_string($details)) {
                $item = $this->model->getByCode($details);
            }

            if (!$item) {
                $this->renderNotFound("Le dossier étudiant demandé est introuvable.");
                return;
            }

            $etudiantCode = $item['code_etudiant'];
            $fullDetails = $this->model->getStudentFullDetails($etudiantCode);
            $encryptedId = $this->validator->crypter($id);

            $this->loadView('../views/etudiants/details.php', [
                'item' => $item, 
                'parent' => $fullDetails['parent'],
                'inscription' => $fullDetails['inscription'],
                'etablissement' => $fullDetails['etablissement'],
                'dossierPieces' => $fullDetails['dossierPieces'],
                'paiements' => $fullDetails['paiements'],
                'totalPaye' => $fullDetails['totalPaye'],
                'soldeRestant' => $fullDetails['soldeRestant'],
                'scolariteTotale' => $fullDetails['scolariteTotale'],
                'absences' => $fullDetails['absences'],
                'encryptedId' => $encryptedId
            ]);
        } catch (Exception $e) {
            error_log("EtudiantController::details error: " . $e->getMessage());
            $this->renderNotFound("Le dossier étudiant demandé est introuvable ou une erreur est survenue.");
        }
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::EDIT_ETUDIANT, PERMISSIONS::MANAGE_ETUDIANTS]);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'etudiant/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'etudiant/list'); exit();
        }
        $this->loadView('../views/etudiants/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETUDIANTS);
        $this->loadView('../views/etudiants/edit.php', ['item' => []]);
    }

    public function wizard()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETUDIANTS);
        $this->loadView('../views/etudiants/wizard.php');
    }

    public function addWizard()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ETUDIANTS);

        $userCode = Auth::code();
        $data = $_POST;
        unset($data['csrf_token']);
        $this->cleanPhoneFields($data);

        $anneeCode = !empty($data['annee_code']) ? trim($data['annee_code']) : $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $res = $this->model->createFullStudent($data, $userCode, $etabCode, $anneeCode, $this->validator);
        if ($res['success']) {
            $this->success($res['message'], ['redirect' => RACINE . 'etudiant/list']);
        } else {
            $this->error($res['message']);
        }
    }

    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_ETUDIANTS, PERMISSIONS::MANAGE_ETUDIANTS]);

        require_once __DIR__ . '/../../core/PdfService.php';

        $anneeCode = $_GET['annee_code'] ?? ($_POST['annee_code'] ?? '');
        if ($anneeCode === '' || $anneeCode === null || $anneeCode === 'ALL') {
            $anneeCode = $this->getActiveAnneeCode();
        }

        $filters = [
            'annee_code' => $anneeCode,
            'niveau_code' => $_GET['niveau_code'] ?? ($_POST['niveau_code'] ?? ''),
            'filiere_code' => $_GET['filiere_code'] ?? ($_POST['filiere_code'] ?? ''),
            'classe_code' => $_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''),
            'statut_etudiant' => $_GET['statut_etudiant'] ?? ($_POST['statut_etudiant'] ?? ''),
            'affectation_etat' => $_GET['affectation_etat'] ?? ($_POST['affectation_etat'] ?? '')
        ];

        $etudiants = $this->model->getFilteredRegistry($filters);

        // Tri par ordre alphabétique (Nom et Prénoms)
        usort($etudiants, function ($a, $b) {
            $nomA = mb_strtolower(trim(($a['nom_etudiant'] ?? '') . ' ' . ($a['prenom_etudiant'] ?? '')), 'UTF-8');
            $nomB = mb_strtolower(trim(($b['nom_etudiant'] ?? '') . ' ' . ($b['prenom_etudiant'] ?? '')), 'UTF-8');
            return strnatcmp($nomA, $nomB);
        });

        $filtresLabels = $this->model->getFiltresLabels($filters);
        if (!empty($filters['affectation_etat']) && $filters['affectation_etat'] !== 'ALL') {
            $filtresLabels['regime'] = ($filters['affectation_etat'] === 'affecte') ? 'Affectés (État)' : 'Non Affectés (Privé)';
        }

        // Année académique libellé
        $anneeLibelle = $this->getActiveAnneeLibelle();
        if (!empty($anneeCode)) {
            $anneeItem = (new ModelAnnee())->getByCode($anneeCode);
            if (!empty($anneeItem['libelle_annee'])) {
                $anneeLibelle = $anneeItem['libelle_annee'];
            }
        }

        // KPIs de synthèse
        $total = count($etudiants);
        $affectes = 0;
        $nonAffectes = 0;
        $hommes = 0;
        $femmes = 0;

        foreach ($etudiants as $e) {
            $aff = $e['affectation_etat'] ?? '';
            if ($aff === 'affecte' || $aff === 'oui') {
                $affectes++;
            } else {
                $nonAffectes++;
            }

            $sexe = strtoupper(trim($e['sexe_etudiant'] ?? ''));
            if (strpos($sexe, 'F') !== false) {
                $femmes++;
            } else {
                $hommes++;
            }
        }

        $etablissement = $this->getEtablissementConfig();
        $currentUser = Auth::user();
        $editeurNom = trim(($currentUser['prenom_user'] ?? '') . ' ' . ($currentUser['nom_user'] ?? ''));
        if (empty($editeurNom)) {
            $editeurNom = 'Service Inscription & Scolarité';
        }

        $isClassList = (!empty($filters['classe_code']) && $filters['classe_code'] !== 'ALL');

        $data = [
            'etablissement' => $etablissement,
            'annee_libelle' => $anneeLibelle,
            'editeur_nom' => $editeurNom,
            'etudiants' => $etudiants,
            'filtres_labels' => $filtresLabels,
            'classe_libelle' => $filtresLabels['classe'] ?? '',
            'filiere_libelle' => $filtresLabels['filiere'] ?? '',
            'niveau_libelle' => $filtresLabels['niveau'] ?? '',
            'kpis' => [
                'total' => $total,
                'affectes' => $affectes,
                'non_affectes' => $nonAffectes,
                'hommes' => $hommes,
                'femmes' => $femmes
            ]
        ];

        $template = $isClassList ? 'liste_classe.php' : 'liste_etudiants.php';
        $orientation = $isClassList ? 'P' : 'L';
        $docTitle = $isClassList ? ('Liste_Classe_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $filtresLabels['classe'] ?? 'Classe')) : 'Liste_Etudiants';

        $html = PdfService::renderTemplate($template, $data);

        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $filename = $docTitle . '_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => $orientation,
            'format' => 'A4',
            'title' => ($isClassList ? 'Liste de Classe & Émargement - ' : 'Registre Nominatif des Étudiants - ') . ($etablissement['nom_etablissement'] ?? 'GROUPE EICG'),
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8
        ]);
    }

    public function annulerInscription()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::ANNULER_INSCRIPTION, PERMISSIONS::MANAGE_INSCRIPTIONS]);

        $idInscription = (int)$this->post('id_inscription');
        $codeInscription = trim($this->post('code_inscription') ?? '');

        $res = $this->model->annulerInscription($idInscription, $codeInscription);

        if ($res['success']) {
            $this->success($res['message'], [
                'reload' => true,
                'id_inscription' => $res['inscription']['id_inscription'] ?? $idInscription,
                'code_inscription' => $res['inscription']['code_inscription'] ?? $codeInscription
            ]);
        } else {
            $this->error($res['message']);
        }
    }
}