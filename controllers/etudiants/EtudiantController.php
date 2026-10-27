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
        $this->requirePermission('VIEW_ETUDIANTS');
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
        $this->requirePermission('MANAGE_ETUDIANTS');
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
        $this->requirePermission(['EDIT_ETUDIANT', 'MANAGE_ETUDIANTS']);
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
        $this->requirePermission('MANAGE_ETUDIANTS');
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
        $this->requirePermission(['CHANGER_CLASSE_ETUDIANT', PERMISSIONS::MANAGE_ETUDIANTS]);

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
        $this->requirePermission(['CHANGER_CLASSE_ETUDIANT', PERMISSIONS::MANAGE_ETUDIANTS]);

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
        $this->requirePermission(['EDIT_ETUDIANT', 'MANAGE_ETUDIANTS']);
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
        $this->requirePermission('MANAGE_ETUDIANTS');
        $this->loadView('../views/etudiants/edit.php', ['item' => []]);
    }

    public function wizard()
    {
        $this->requireAuth();
        $this->requirePermission('MANAGE_ETUDIANTS');
        $this->loadView('../views/etudiants/wizard.php');
    }

    public function addWizard()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_ETUDIANTS');

        $db = $this->model->getCon();
        $userCode = Auth::code();
        $data = $_POST;
        unset($data['csrf_token']);
        $this->cleanPhoneFields($data);

        $anneeCode = !empty($data['annee_code']) ? trim($data['annee_code']) : $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $nomEtudiant = trim($data['nom_etudiant'] ?? '');
        $prenomEtudiant = trim($data['prenom_etudiant'] ?? '');
        $telephoneEtudiant = $data['telephone_etudiant'] ?? '';

        if (empty($nomEtudiant) || empty($prenomEtudiant) || empty($telephoneEtudiant)) {
            $this->error('Veuillez renseigner le nom, prénoms et téléphone de l\'étudiant.');
            return;
        }

        $matriculeEtudiant = trim($data['matricule_etudiant'] ?? '');
        if (empty($matriculeEtudiant)) {
            $matriculeEtudiant = $this->model->generateMatricule($nomEtudiant, $prenomEtudiant, $data['classe_code'] ?? null, $anneeCode);
        }

        $matriculeMenet = !empty($data['matricule_menet']) ? trim($data['matricule_menet']) : null;
        $matriculeMesrs = !empty($data['matricule_mesrs']) ? trim($data['matricule_mesrs']) : null;

        $codeEtudiant = $this->validator->generateCode('etudiants', 'code_etudiant', 'ETU-', 8);

        try {
            $db->beginTransaction();

            // 1. Insert Student into `etudiants`
            $fkErrEtu = ForeignKeyValidator::validate($db, 'etudiants', [
                'etablissement_code' => $etabCode
            ]);
            if ($fkErrEtu !== null) {
                $db->rollBack();
                $this->error($fkErrEtu);
                return;
            }

            $stmtEtu = $db->prepare("
                INSERT INTO etudiants 
                (code_etudiant, matricule_etudiant, matricule_menet, matricule_mesrs, nom_etudiant, prenom_etudiant, sexe_etudiant, date_naissance_etudiant, lieu_naissance_etudiant, nationalite_etudiant, telephone_etudiant, email_etudiant, lieu_residence_etudiant, user_code, etablissement_code, statut_etudiant, created_at_etudiant)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtEtu->execute([
                $codeEtudiant,
                $matriculeEtudiant,
                $matriculeMenet,
                $matriculeMesrs,
                $nomEtudiant,
                $prenomEtudiant,
                $data['sexe_etudiant'] ?? 'M',
                !empty($data['date_naissance_etudiant']) ? $data['date_naissance_etudiant'] : (!empty($data['date_naissance']) ? $data['date_naissance'] : null),
                !empty($data['lieu_naissance_etudiant']) ? $data['lieu_naissance_etudiant'] : (!empty($data['lieu_naissance']) ? $data['lieu_naissance'] : null),
                !empty($data['nationalite_etudiant']) ? $data['nationalite_etudiant'] : (!empty($data['nationalite']) ? $data['nationalite'] : 'Ivoirienne'),
                $telephoneEtudiant,
                !empty($data['email_etudiant']) ? $data['email_etudiant'] : null,
                !empty($data['lieu_residence_etudiant']) ? $data['lieu_residence_etudiant'] : (!empty($data['adresse_etudiant']) ? $data['adresse_etudiant'] : null),
                $userCode,
                $etabCode
            ]);

            // 2. Insert Parent into `parents` if parent info provided
            if (!empty($data['nom_pere']) || !empty($data['nom_mere']) || !empty($data['nom_tuteur'])) {
                $codeParent = $this->validator->generateCode('parents', 'code_parent', 'PAR-', 8);
                $fkErrPar = ForeignKeyValidator::validate($db, 'parents', [
                    'etudiant_code' => $codeEtudiant,
                    'etablissement_code' => $etabCode
                ]);
                if ($fkErrPar !== null) {
                    $db->rollBack();
                    $this->error($fkErrPar);
                    return;
                }

                $stmtPar = $db->prepare("
                    INSERT INTO parents 
                    (code_parent, etudiant_code, nom_pere, telephone_pere, profession_pere, nom_mere, telephone_mere, profession_mere, nom_tuteur, telephone_tuteur, profession_tuteur, user_code, etablissement_code, created_at_parent)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmtPar->execute([
                    $codeParent,
                    $codeEtudiant,
                    !empty($data['nom_pere']) ? $data['nom_pere'] : null,
                    !empty($data['telephone_pere']) ? $data['telephone_pere'] : null,
                    !empty($data['profession_pere']) ? $data['profession_pere'] : null,
                    !empty($data['nom_mere']) ? $data['nom_mere'] : null,
                    !empty($data['telephone_mere']) ? $data['telephone_mere'] : null,
                    !empty($data['profession_mere']) ? $data['profession_mere'] : null,
                    !empty($data['nom_tuteur']) ? $data['nom_tuteur'] : null,
                    !empty($data['telephone_tuteur']) ? $data['telephone_tuteur'] : null,
                    !empty($data['profession_tuteur']) ? $data['profession_tuteur'] : null,
                    $userCode,
                    $etabCode
                ]);
            }

            // 3. Insert Academic Inscription into `inscriptions` if classe_code provided
            if (!empty($data['classe_code'])) {
                $affectationEtatVal = (!empty($data['affectation_etat']) && in_array($data['affectation_etat'], ['affecte', 'oui'])) ? 'oui' : 'non';
                $regimeScol = ($affectationEtatVal === 'oui') ? 'affecte' : 'non_affecte';
                $montantScolarite = (float)($data['montant_scolarite_inscription'] ?? 0);
                if ($montantScolarite <= 0) {
                    $stmtCl = $db->prepare("SELECT * FROM classes WHERE code_classe = ? LIMIT 1");
                    $stmtCl->execute([$data['classe_code']]);
                    $cl = $stmtCl->fetch(PDO::FETCH_ASSOC);
                    if ($cl) {
                        $stmtSco = $db->prepare("SELECT montant_scolarite FROM scolarites WHERE filiere_code = ? AND niveau_code = ? AND annee_code = ? AND affectation_etat = ? AND statut_scolarite = 'actif' ORDER BY id_scolarite DESC LIMIT 1");
                        $stmtSco->execute([$cl['filiere_code'], $cl['niveau_code'], $anneeCode, $regimeScol]);
                        $sco = $stmtSco->fetch(PDO::FETCH_ASSOC);
                        if (!$sco) {
                            $stmtSco2 = $db->prepare("SELECT montant_scolarite FROM scolarites WHERE filiere_code = ? AND niveau_code = ? ORDER BY id_scolarite DESC LIMIT 1");
                            $stmtSco2->execute([$cl['filiere_code'], $cl['niveau_code']]);
                            $sco = $stmtSco2->fetch(PDO::FETCH_ASSOC);
                        }
                        if ($sco) {
                            $montantScolarite = (float)$sco['montant_scolarite'];
                        }
                    }
                }
                $codeInscription = $this->validator->generateCode('inscriptions', 'code_inscription', 'INS-', 8);
                
                $fkErrIns = ForeignKeyValidator::validate($db, 'inscriptions', [
                    'etudiant_code' => $codeEtudiant,
                    'classe_code' => $data['classe_code'],
                    'annee_code' => $anneeCode,
                    'etablissement_code' => $etabCode
                ]);
                if ($fkErrIns !== null) {
                    $db->rollBack();
                    $this->error($fkErrIns);
                    return;
                }

                $stmtIns = $db->prepare("
                    INSERT INTO inscriptions
                    (code_inscription, etudiant_code, classe_code, montant_scolarite_inscription, user_code, annee_code, etablissement_code, statut_inscription, affectation_etat, created_at_inscription)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'valide', ?, NOW())
                ");
                $stmtIns->execute([
                    $codeInscription,
                    $codeEtudiant,
                    $data['classe_code'],
                    $montantScolarite,
                    $userCode,
                    $anneeCode,
                    $etabCode,
                    $affectationEtatVal
                ]);
            }

            // 4. Insert Accessoires / Kits into `accessoire_inscription`
            if (!empty($data['accessoires']) && is_array($data['accessoires'])) {
                if (empty($codeInscription)) {
                    $db->rollBack();
                    $this->error("Opération interrompue : Une classe et une inscription sont obligatoires pour attribuer des accessoires.");
                    return;
                }
                $stmtAcc = $db->prepare("
                    INSERT INTO accessoire_inscription
                    (code_accessoire_inscription, inscription_code, accessoire_code, annee_code, statut_accessoire_inscription, etat_retrait, user_code, etablissement_code, created_at_accessoire_inscription)
                    VALUES (?, ?, ?, ?, 'actif', 'en_attente', ?, ?, NOW())
                ");
                foreach ($data['accessoires'] as $accCode) {
                    if (empty($accCode)) continue;
                    
                    $fkErrAcc = ForeignKeyValidator::validate($db, 'accessoire_inscription', [
                        'inscription_code' => $codeInscription,
                        'accessoire_code' => $accCode,
                        'annee_code' => $anneeCode,
                        'etablissement_code' => $etabCode
                    ]);
                    if ($fkErrAcc !== null) {
                        $db->rollBack();
                        $this->error($fkErrAcc);
                        return;
                    }

                    $codeAccIns = $this->validator->generateCode('accessoire_inscription', 'code_accessoire_inscription', 'ACI-', 8);
                    $stmtAcc->execute([
                        $codeAccIns,
                        $codeInscription,
                        $accCode,
                        $anneeCode,
                        $userCode,
                        $etabCode
                    ]);
                }
            }

            // 5. Insert Documents / Checklist into `dossier_etudiant`
            $piecesFournies = !empty($data['pieces_fournies']) && is_array($data['pieces_fournies']) ? $data['pieces_fournies'] : [];
            $allPieces = $db->query("SELECT code_piece_fournir FROM pieces_fournir WHERE statut_piece = 'actif'")->fetchAll(PDO::FETCH_COLUMN) ?: [];

            if (!empty($allPieces)) {
                if (empty($codeInscription)) {
                    $db->rollBack();
                    $this->error("Opération interrompue : Une inscription valide est nécessaire pour initialiser le dossier étudiant.");
                    return;
                }
                $stmtDos = $db->prepare("
                    INSERT INTO dossier_etudiant
                    (code_dossier_etudiant, inscription_code, etudiant_code, piece_code, statut_depot, date_depot, observations, user_code, etablissement_code, created_at_dossier_etudiant)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                $piecesReqMap = [];
                try {
                    $pfcList = $db->query("SELECT piece_code, nombre_exemplaires FROM piece_fournir_cycle WHERE statut_piece_cycle = 'actif'")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
                    $piecesReqMap = $pfcList;
                } catch (Exception $e) {}

                foreach ($allPieces as $pCode) {
                    $isDepose = in_array($pCode, $piecesFournies);
                    $nbEx = !empty($piecesReqMap[$pCode]) ? max(1, (int)$piecesReqMap[$pCode]) : 1;
                    $obsText = $isDepose 
                        ? ("Pièce déposée ({$nbEx} exemplaire" . ($nbEx > 1 ? "s" : "") . ") lors de l'inscription initiale")
                        : ("En attente de transmission ({$nbEx} exemplaire" . ($nbEx > 1 ? "s requis" : " requis") . ")");

                    $fkErrDos = ForeignKeyValidator::validate($db, 'dossier_etudiant', [
                        'inscription_code' => $codeInscription,
                        'etudiant_code' => $codeEtudiant,
                        'piece_code' => $pCode,
                        'etablissement_code' => $etabCode
                    ]);
                    if ($fkErrDos !== null) {
                        $db->rollBack();
                        $this->error($fkErrDos);
                        return;
                    }

                    $codeDos = $this->validator->generateCode('dossier_etudiant', 'code_dossier_etudiant', 'DOS-', 8);
                    $stmtDos->execute([
                        $codeDos,
                        $codeInscription,
                        $codeEtudiant,
                        $pCode,
                        $isDepose ? 'depose' : 'en_attente',
                        $isDepose ? date('Y-m-d H:i:s') : null,
                        $obsText,
                        $userCode,
                        $etabCode
                    ]);
                }
            }

            $db->commit();
            $this->success('Dossier d\'inscription étudiant créé avec succès !', ['redirect' => RACINE . 'etudiant/list']);

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->error('Erreur lors de la création du dossier: ' . $e->getMessage());
        }
    }

    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_ETUDIANTS', 'MANAGE_ETUDIANTS']);

        require_once __DIR__ . '/../../core/PdfService.php';

        $db = $this->model->getCon();

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
            $stmtA = $db->prepare("SELECT libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$anneeCode]);
            $aName = $stmtA->fetchColumn();
            if ($aName) $anneeLibelle = $aName;
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
        $authSession = $_SESSION[USERS_AUTH] ?? [];
        $editeurNom = trim(($authSession['prenom_user'] ?? '') . ' ' . ($authSession['nom_user'] ?? ''));
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
        $this->requirePermission(['ANNULER_INSCRIPTION', PERMISSIONS::MANAGE_INSCRIPTIONS]);

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