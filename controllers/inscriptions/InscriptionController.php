<?php

class InscriptionController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelInscription();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_INSCRIPTIONS);

        $activeAnneeCode = $this->getActiveAnneeCode();
        $deps = $this->model->getReinscriptionFormDependencies($activeAnneeCode);
        $annees = $deps['anneesCloturees'];
        $selectedAnneeCode = !empty($_GET['annee_code']) ? trim($_GET['annee_code']) : ($annees[0]['code_annee'] ?? '');

        $this->loadView('../views/inscriptions/list.php', [
            'filieres' => $deps['filieres'],
            'niveaux' => $deps['niveaux'],
            'classes' => $deps['classes'],
            'annees' => $annees,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_INSCRIPTIONS);

        $activeAnneeCode = $this->getActiveAnneeCode();
        $filterAnnee = trim($_GET['annee_code'] ?? '');
        $filterFiliere = trim($_GET['filiere_code'] ?? '');
        $filterNiveau = trim($_GET['niveau_code'] ?? '');
        $filterClasse = trim($_GET['classe_code'] ?? '');

        if (empty($filterAnnee) || $filterAnnee === 'ALL') {
            $this->json(['data' => []]);
            return;
        }

        $candidates = $this->model->getReinscriptionCandidates($activeAnneeCode, $filterAnnee, $filterFiliere, $filterNiveau, $filterClasse);
        $data = [];
        foreach ($candidates as $cand) {
            $etu = $cand['etudiant'];
            $prev = $cand['prev'];

            $data[] = [
                'id_etudiant' => $etu['id_etudiant'],
                'code_etudiant' => $etu['code_etudiant'],
                'matricule_etudiant' => $etu['matricule_etudiant'] ?? '-',
                'nom_etudiant' => $etu['nom_etudiant'],
                'prenom_etudiant' => $etu['prenom_etudiant'],
                'nom_complet' => trim(($etu['nom_etudiant'] ?? '') . ' ' . ($etu['prenom_etudiant'] ?? '')),
                'sexe' => $etu['sexe_etudiant'] ?? 'M',
                'telephone' => $etu['telephone_etudiant'] ?? '-',
                'photo_etudiant' => $etu['photo_etudiant'] ?? '',

                // Cursus antérieur (N-1)
                'classe_precedente' => $prev['classe_prev'] ?? '',
                'filiere_precedente' => $prev['filiere_prev'] ?? '',
                'niveau_precedent' => $prev['niveau_prev'] ?? '',
                'annee_precedente' => $prev['annee_prev'] ?? ''
            ];
        }

        $this->json(['data' => $data]);
    }

    public function getStudentProfileSummary()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_INSCRIPTIONS);
        $etudiantCode = trim($_GET['etudiant_code'] ?? ($_POST['etudiant_code'] ?? ''));

        if (empty($etudiantCode)) {
            $this->json(['status' => 0, 'message' => 'Code étudiant requis']);
            return;
        }

        $anneeCodeParam = trim($_GET['annee_code'] ?? ($_POST['annee_code'] ?? ''));
        $anneeActive = !empty($anneeCodeParam) ? $anneeCodeParam : $this->getActiveAnneeCode();

        $profile = $this->model->getStudentProfileForReinscription($etudiantCode, $anneeActive);
        if (!$profile) {
            $this->json(['status' => 0, 'message' => 'Étudiant introuvable']);
            return;
        }

        $this->json([
            'status' => 1,
            'data' => $profile
        ]);
    }

    public function getTuitionByClass()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_INSCRIPTIONS, PERMISSIONS::MANAGE_ETUDIANTS, PERMISSIONS::VIEW_CLASSES, PERMISSIONS::MANAGE_INSCRIPTIONS]);
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        $anneeCodeReq = trim($_GET['annee_code'] ?? ($_POST['annee_code'] ?? ''));
        $affectationEtat = trim($_GET['affectation_etat'] ?? ($_POST['affectation_etat'] ?? 'non_affecte'));
        if ($affectationEtat === 'oui') $affectationEtat = 'affecte';
        if ($affectationEtat === 'non') $affectationEtat = 'non_affecte';
        if ($affectationEtat !== 'affecte') $affectationEtat = 'non_affecte';

        if (empty($classeCode)) {
            $this->json(['status' => 0, 'message' => 'Classe non spécifiée']);
            return;
        }

        $tarifs = $this->model->getClassTarifs($classeCode, $affectationEtat, $anneeCodeReq ?: $this->getActiveAnneeCode());
        if (!$tarifs) {
            $this->json([
                'status' => 0,
                'message' => 'Aucun tarif de scolarité actif n\'a été trouvé pour le régime sélectionné (' . $affectationEtat . ') pour cette classe.'
            ]);
            return;
        }

        $this->json([
            'status' => 1,
            'data' => $tarifs
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_INSCRIPTIONS);
        $userCode = Auth::code();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);
        $anneeCode = !empty($data['annee_code']) ? trim($data['annee_code']) : $this->getActiveAnneeCode();

        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'classe_code' => $data['classe_code'] ?? ''
        ]);

        $modeInscription = $data['mode_inscription'] ?? 'existant';

        // 1. CAS NOUVEAU DOSSIER ÉTUDIANT (Création Étudiant + Parent + Inscription)
        if ($modeInscription === 'nouveau' || (!empty($data['nom_etudiant']) && !empty($data['prenom_etudiant']))) {
            $res = $this->model->processFullInscription($data, $userCode, $etabCode, $anneeCode, $this->validator);
            if ($res['success']) {
                $this->success($res['message']);
            } else {
                $this->error($res['message']);
            }
            return;
        }

        // 2. CAS RÉINSCRIPTION ÉTUDIANT EXISTANT
        $currentUser = Auth::user();
        $agentName = trim(($currentUser['prenom_user'] ?? '') . ' ' . ($currentUser['nom_user'] ?? ''));
        $hasDerogationPermission = $this->hasPermission(PERMISSIONS::MANAGE_DEROGATION_INSCRIPTION);

        $res = $this->model->processReinscription($data, $userCode, $etabCode, $anneeCode, $this->validator, $hasDerogationPermission, $agentName);
        if ($res['success']) {
            $this->json([
                'status' => 1,
                'message' => $res['message'],
                'voucher_data' => $res['voucher_data'] ?? []
            ]);
        } else {
            $this->error($res['message']);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_INSCRIPTIONS);
        $id = (int)$this->post('id_inscription');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['classe_code'])) {
            $affectationEtat = (!empty($data['affectation_etat']) && in_array($data['affectation_etat'], ['affecte', 'oui'])) ? 'affecte' : 'non_affecte';
            $data['affectation_etat'] = ($affectationEtat === 'affecte') ? 'oui' : 'non';

            $scolAmount = $this->model->getScolariteAmountForClasse($data['classe_code'], $affectationEtat);
            if ($scolAmount !== null) {
                $data['montant_scolarite_inscription'] = $scolAmount;
            } else {
                $regimeTxt = ($affectationEtat === 'affecte') ? 'Affecté (État)' : 'Non Affecté (Privé)';
                $this->error("Modification impossible : Aucun tarif de scolarité actif n'est configuré pour cette classe sous le régime $regimeTxt.");
                return;
            }
        }

        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $this->success('Inscription modifiée avec succès !');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_INSCRIPTIONS);
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = STATUTS::INSCRIPTIONS;
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'statut_inscription');
            } else {
                $success = $this->model->toggleStatus($id);
            }
            if ($success) {
                $this->success('Statut de l\'inscription mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Inscription introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_INSCRIPTIONS);
        try {
            $id = $this->validator->decrypter($details);
            $fullDetails = $this->model->getInscriptionFullDetails($id ?: $details);
            if (!$fullDetails) { 
                $this->renderNotFound("L'inscription demandée est introuvable.");
                return;
            }

            $item = $fullDetails['item'];
            $paiements = $fullDetails['paiements'];
            $scolarite = $fullDetails['scolarite'];
            $totalPaye = $fullDetails['totalPaye'];
            $solde = $fullDetails['soldeRestant'];
            $encryptedId = $this->validator->crypter($item['id_inscription']);
        } catch (Exception $e) {
            error_log("InscriptionController::details error: " . $e->getMessage());
            $this->renderNotFound("L'inscription demandée est introuvable.");
            return;
        }
        $this->loadView('../views/inscriptions/details.php', [
            'item' => $item, 
            'paiements' => $paiements,
            'totalPaye' => $totalPaye,
            'solde' => $solde,
            'scolarite' => $scolarite,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_INSCRIPTIONS);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'inscription/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'inscription/list'); exit();
        }
        $canDerogate = $this->hasPermission(PERMISSIONS::MANAGE_DEROGATION_INSCRIPTION);
        $this->loadView('../views/inscriptions/edit.php', ['item' => $item, 'encryptedId' => $encryptedId, 'canDerogate' => $canDerogate]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_INSCRIPTIONS);
        $canDerogate = $this->hasPermission(PERMISSIONS::MANAGE_DEROGATION_INSCRIPTION);
        $this->loadView('../views/inscriptions/edit.php', ['item' => [], 'canDerogate' => $canDerogate]);
    }

    public function sansPhoto()
    {
        $this->priseDeVue();
    }

    public function priseDeVue()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_INSCRIPTIONS_SANS_PHOTO, PERMISSIONS::MANAGE_INSCRIPTIONS_SANS_PHOTO, PERMISSIONS::MANAGE_INSCRIPTIONS]);

        $activeAnneeCode = $this->getActiveAnneeCode();
        $annees = $this->getAccessibleAnnees();
        $deps = $this->model->getPriseDeVueDependencies();

        $this->loadView('../views/inscriptions/prise_de_vue.php', [
            'annees' => $annees,
            'filieres' => $deps['filieres'] ?? [],
            'classes' => $deps['classes'] ?? [],
            'selectedAnneeCode' => $activeAnneeCode
        ]);
    }

    public function imprimerFiche($param = null)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_ETUDIANTS, PERMISSIONS::MANAGE_ETUDIANTS, PERMISSIONS::VIEW_INSCRIPTIONS, PERMISSIONS::MANAGE_INSCRIPTIONS, PERMISSIONS::RECORD_PAIEMENTS, PERMISSIONS::MANAGE_PAIEMENTS]);
        require_once __DIR__ . '/../../core/PdfService.php';

        $data = [];

        if (!empty($param)) {
            $id = $this->validator->decrypter($param);
            if (empty($id)) {
                $id = $param;
            }

            $recuData = $this->model->getRecuInscriptionData((string)$id);
            if ($recuData === null || isset($recuData['error'])) {
                $msg = $recuData['error'] ?? "Impossible de générer le reçu d'inscription : Inscription introuvable.";
                Session::flash('flash_error', $msg);
                header('Location: ' . RACINE . 'etudiant/list');
                exit;
            }
            $data = $recuData;
        }

        $html = PdfService::renderTemplate('recu_inscription.php', $data);

        if (!empty($_GET['pdf'])) {
            $filename = 'Recu_Inscription_' . ($data['matricule'] ?? 'GEICG') . '.pdf';
            PdfService::generate($html, $filename, [
                'orientation' => 'P'
            ]);
        } else {
            echo $html;
        }
    }

    public function apiSansPhoto()
    {
        $this->apiPriseDeVue();
    }

    public function apiPriseDeVue()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_INSCRIPTIONS_SANS_PHOTO, PERMISSIONS::MANAGE_INSCRIPTIONS_SANS_PHOTO, PERMISSIONS::MANAGE_INSCRIPTIONS]);

        $anneeCode = $_GET['annee_code'] ?? $this->getActiveAnneeCode();
        $filiereCode = $_GET['filiere_code'] ?? '';
        $classeCode = $_GET['classe_code'] ?? '';
        $filterPhoto = $_GET['filter_photo'] ?? 'all';

        try {
            $rows = $this->model->getInscriptionsSansPhoto($anneeCode, $filiereCode, $classeCode, $filterPhoto);
            $data = [];
            foreach ($rows as $r) {
                $id = $r['id_inscription'];
                $idCrypte = $this->validator->crypter($id);
                $hasPhoto = (!empty($r['photo_inscription']) || !empty($r['photo_etudiant']));
                $photoPath = !empty($r['photo_inscription']) ? $r['photo_inscription'] : ($r['photo_etudiant'] ?? '');
                $data[] = array_merge($r, [
                    'id' => $id,
                    'editId' => $idCrypte,
                    'nom_complet' => trim($r['nom_etudiant'] . ' ' . $r['prenom_etudiant']),
                    'has_photo' => $hasPhoto,
                    'photo_path' => $photoPath
                ]);
            }
            $this->json(['data' => $data]);
        } catch (Exception $e) {
            error_log("InscriptionController::apiPriseDeVue error: " . $e->getMessage());
            $this->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    public function uploadPhoto()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::MANAGE_INSCRIPTIONS_SANS_PHOTO, PERMISSIONS::MANAGE_INSCRIPTIONS]);

        $idInscription = (int)($_POST['id_inscription'] ?? 0);
        $codeInscription = trim($_POST['code_inscription'] ?? '');
        $webcamData = trim($_POST['photo_webcam_data'] ?? '');

        if (!$idInscription && empty($codeInscription)) {
            $this->error("Identifiant d'inscription valide requis.");
            return;
        }

        $inscInfo = $this->model->getInscriptionForPhotoUpload($idInscription ?: $codeInscription);

        if (!$inscInfo) {
            $this->error("Inscription introuvable.");
            return;
        }

        // Dossier classé par année académique
        $anneeLabel = !empty($inscInfo['libelle_annee']) ? $inscInfo['libelle_annee'] : ($inscInfo['annee_code'] ?: 'defaut');
        $safeAnneeFolder = preg_replace('/[^a-zA-Z0-9_-]/', '_', $anneeLabel);
        $uploadDir = __DIR__ . '/../../public/uploads/photos_inscriptions/' . $safeAnneeFolder . '/';

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
            @chmod($uploadDir, 0777);
        }

        // Nom du fichier = Matricule de l'étudiant
        $matricule = !empty($inscInfo['matricule_etudiant']) ? trim($inscInfo['matricule_etudiant']) : '';
        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $matricule);
        if (empty($safeFilename)) {
            $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $inscInfo['code_inscription'] ?: ('INS_' . $inscInfo['id_inscription']));
        }

        // Supprimer l'ancienne photo si elle existe sur le disque
        $oldPhotoPath = !empty($inscInfo['photo_inscription']) ? trim($inscInfo['photo_inscription']) : (!empty($inscInfo['photo_etudiant']) ? trim($inscInfo['photo_etudiant']) : '');
        if (!empty($oldPhotoPath)) {
            $oldFullPath = __DIR__ . '/../../' . ltrim($oldPhotoPath, '/');
            if (file_exists($oldFullPath) && is_file($oldFullPath)) {
                @unlink($oldFullPath);
            }
        }

        $relativePath = null;

        if (!empty($webcamData)) {
            if (preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $webcamData, $type)) {
                $data = substr($webcamData, strpos($webcamData, ',') + 1);
                $ext = strtolower($type[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $data = base64_decode($data);
                if ($data === false) {
                    $this->error("Données de photo webcam invalides.");
                    return;
                }
                $filename = $safeFilename . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (file_put_contents($targetPath, $data) !== false) {
                    $relativePath = 'public/uploads/photos_inscriptions/' . $safeAnneeFolder . '/' . $filename;
                }
            } else {
                $this->error("Format de capture webcam invalide.");
                return;
            }
        } elseif (isset($_FILES['photo_inscription']) && $_FILES['photo_inscription']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo_inscription'];
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                $this->error("La photo dépasse la taille maximale autorisée de 5 Mo.");
                return;
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['png', 'jpg', 'jpeg', 'webp'];
            if (!in_array($ext, $allowedExts, true)) {
                $this->error("Format d'image non autorisé. Formats acceptés : PNG, JPG, JPEG, WEBP.");
                return;
            }
            $filename = $safeFilename . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $relativePath = 'public/uploads/photos_inscriptions/' . $safeAnneeFolder . '/' . $filename;
            }
        } else {
            $this->error("Veuillez sélectionner un fichier image ou capturer une photo depuis la webcam.");
            return;
        }

        if ($relativePath) {
            $codeEtu = !empty($inscInfo['code_etudiant']) ? $inscInfo['code_etudiant'] : ($inscInfo['etudiant_code'] ?? '');
            $matEtu = !empty($inscInfo['matricule_etudiant']) ? $inscInfo['matricule_etudiant'] : '';
            $ok = $this->model->updatePhoto((int)$inscInfo['id_inscription'], $relativePath, $codeEtu, $matEtu);

            if ($ok) {
                $this->success("La photo d'inscription a été enregistrée avec succès !", [
                    'photo_url' => RACINE . $relativePath
                ]);
            } else {
                $this->error("Erreur lors de la mise à jour de la photo dans la base de données.");
            }
        } else {
            $this->error("Erreur lors de la sauvegarde du fichier photo.");
        }
    }
}
