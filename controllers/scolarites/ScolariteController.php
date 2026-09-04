<?php

class ScolariteController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelScolarite();
    }

    public function list()
    {
        $this->requireAuth();
        $db = $this->model->getCon();

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }

        $activeYear = $this->getActiveAnneeCode();
        $annees = $db->query("SELECT code_annee, libelle_annee, statut_annee FROM annees ORDER BY id_annee DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmtTot = $db->prepare("SELECT COUNT(*) FROM scolarites WHERE (annee_code = ? OR annee_code IS NULL OR annee_code = '' OR ? = '')");
        $stmtTot->execute([$activeYear, $activeYear]);
        $totalScolarites = (int)$stmtTot->fetchColumn();

        $stmtAff = $db->prepare("SELECT COUNT(*) FROM scolarites WHERE affectation_etat = 'affecte' AND (annee_code = ? OR annee_code IS NULL OR annee_code = '' OR ? = '')");
        $stmtAff->execute([$activeYear, $activeYear]);
        $totalAffectes = (int)$stmtAff->fetchColumn();

        $stmtNonAff = $db->prepare("SELECT COUNT(*) FROM scolarites WHERE (affectation_etat = 'non_affecte' OR affectation_etat IS NULL OR affectation_etat = '') AND (annee_code = ? OR annee_code IS NULL OR annee_code = '' OR ? = '')");
        $stmtNonAff->execute([$activeYear, $activeYear]);
        $totalNonAffectes = (int)$stmtNonAff->fetchColumn();

        $stmtTr = $db->prepare("SELECT COUNT(*) FROM tranches_scolarite WHERE (annee_code = ? OR annee_code IS NULL OR annee_code = '' OR ? = '')");
        $stmtTr->execute([$activeYear, $activeYear]);
        $totalTranches = (int)$stmtTr->fetchColumn();

        $niveaux = (new ModelNiveau())->getAll();
        $classes = (new ModelClasse())->getAll();

        $this->loadView('../views/scolarites/list.php', [
            'totalScolarites' => $totalScolarites,
            'totalAffectes' => $totalAffectes,
            'totalNonAffectes' => $totalNonAffectes,
            'totalTranches' => $totalTranches,
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $activeYear
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $db = $this->model->getCon();
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }

        $anneeCode = $this->getActiveAnneeCode();
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;
        $items = $this->model->getAll($anneeCode, $niveauCode, $classeCode);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_scolarite'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function validateTranchesScolarite(float $montantScolarite, array $tranches, string $anneeCode = ''): ?string
    {
        if (empty($tranches)) {
            return null;
        }

        $totalTranches = 0;
        $previousDate = null;
        $previousTrancheName = '';

        $anneeStart = null;
        $anneeEnd = null;
        $anneeLibelle = '';
        if (!empty($anneeCode)) {
            $stmtAnnee = $this->model->getCon()->prepare("SELECT * FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtAnnee->execute([$anneeCode]);
            $annee = $stmtAnnee->fetch(PDO::FETCH_ASSOC);
            if ($annee) {
                $anneeLibelle = $annee['libelle_annee'] ?? '';
                if (!empty($annee['date_debut_annee'])) $anneeStart = strtotime($annee['date_debut_annee']);
                if (!empty($annee['date_fin_annee'])) $anneeEnd = strtotime($annee['date_fin_annee']);
            }
        }

        $trancheIndex = 0;
        foreach ($tranches as $tData) {
            $libelle = trim($tData['libelle_tranche'] ?? '');
            $montant = (float)($tData['montant_tranche'] ?? 0);
            $dateLimite = trim($tData['date_limite'] ?? '');

            if (empty($libelle) && $montant <= 0 && empty($dateLimite)) {
                continue;
            }

            $trancheIndex++;
            $trancheName = !empty($libelle) ? $libelle : "Tranche $trancheIndex";

            if ($montant <= 0) {
                return "Erreur sur \"$trancheName\" : Le montant de la tranche doit être strictement supérieur à 0 FCFA.";
            }

            $totalTranches += $montant;

            if (empty($dateLimite)) {
                return "Erreur sur \"$trancheName\" : La date limite de règlement est obligatoire.";
            }

            $timeLimite = strtotime($dateLimite);
            if (!$timeLimite) {
                return "Erreur sur \"$trancheName\" : La date limite saisie n'est pas valide.";
            }

            if ($previousDate !== null && $timeLimite <= $previousDate) {
                $dateCurrentFr = date('d/m/Y', $timeLimite);
                $datePrevFr = date('d/m/Y', $previousDate);
                return "Incohérence des dates d'échéancier : La date de \"$trancheName\" ($dateCurrentFr) doit être strictement postérieure à la date de \"$previousTrancheName\" ($datePrevFr).";
            }

            if ($anneeStart && $timeLimite < $anneeStart) {
                return "Erreur sur \"$trancheName\" : La date limite (" . date('d/m/Y', $timeLimite) . ") ne peut pas être antérieure au début de l'année académique $anneeLibelle (" . date('d/m/Y', $anneeStart) . ").";
            }
            if ($anneeEnd && $timeLimite > $anneeEnd) {
                return "Erreur sur \"$trancheName\" : La date limite (" . date('d/m/Y', $timeLimite) . ") ne peut pas dépasser la fin de l'année académique $anneeLibelle (" . date('d/m/Y', $anneeEnd) . ").";
            }

            $previousDate = $timeLimite;
            $previousTrancheName = $trancheName;
        }

        if ($montantScolarite > 0 && $totalTranches > $montantScolarite) {
            $depassement = $totalTranches - $montantScolarite;
            $totFormatted = number_format($totalTranches, 0, ',', ' ') . ' FCFA';
            $scoFormatted = number_format($montantScolarite, 0, ',', ' ') . ' FCFA';
            $depFormatted = number_format($depassement, 0, ',', ' ') . ' FCFA';
            return "Incohérence sur le montant : Le cumul des tranches ($totFormatted) dépasse le montant annuel de la scolarité ($scoFormatted) de $depFormatted.";
        }

        return null;
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = !empty($_POST['annee_code']) ? trim($_POST['annee_code']) : ($this->getActiveAnneeCode());
        
        $filiereCodes = $_POST['filiere_codes'] ?? ($_POST['filiere_code'] ?? []);
        if (!is_array($filiereCodes)) {
            $filiereCodes = array_filter([trim($filiereCodes)]);
        }
        
        $niveauCodes = $_POST['niveau_codes'] ?? ($_POST['niveau_code'] ?? []);
        if (!is_array($niveauCodes)) {
            $niveauCodes = array_filter([trim($niveauCodes)]);
        }

        $affectationEtat = trim($_POST['affectation_etat'] ?? 'affecte');
        $montantScolarite = (float)($_POST['montant_scolarite'] ?? 0);
        $etabCode = $this->getActiveEtablissementCode();

        if (empty($filiereCodes)) {
            $this->error("Veuillez sélectionner au moins une filière rattachée.");
            return;
        }
        if (empty($niveauCodes)) {
            $this->error("Veuillez sélectionner au moins un niveau d'études.");
            return;
        }

        // Validation des tranches
        $trancheErr = $this->validateTranchesScolarite($montantScolarite, $_POST['tranches'] ?? [], $anneeCode);
        if ($trancheErr) {
            $this->error($trancheErr);
            return;
        }

        $db = $this->model->getCon();
        $stmtCheck = $db->prepare("
            SELECT id_scolarite FROM scolarites 
            WHERE annee_code = ? AND filiere_code = ? AND niveau_code = ? AND affectation_etat = ?
        ");

        $cols = $db->query("DESCRIBE scolarites")->fetchAll(PDO::FETCH_COLUMN);
        $trancheModel = new ModelTranche();
        $trancheCols = $db->query("DESCRIBE tranches_scolarite")->fetchAll(PDO::FETCH_COLUMN);

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($filiereCodes as $fCode) {
            $fCode = trim($fCode);
            if (empty($fCode)) continue;

            foreach ($niveauCodes as $nCode) {
                $nCode = trim($nCode);
                if (empty($nCode)) continue;

                // Contrôle d'unicité pour chaque combinaison
                $stmtCheck->execute([$anneeCode, $fCode, $nCode, $affectationEtat]);
                if ($stmtCheck->fetch()) {
                    $skippedCount++;
                    continue;
                }

                $codeScolarite = $this->validator->generateCode('scolarites', 'code_scolarite', 'SCO-', 8);

                $data = $_POST;
                unset($data['csrf_token']);
                unset($data['tranches']);
                unset($data['deleted_tranches_ids']);
                unset($data['filiere_codes']);
                unset($data['niveau_codes']);

                $data['code_scolarite'] = $codeScolarite;
                $data['annee_code'] = $anneeCode;
                $data['filiere_code'] = $fCode;
                $data['niveau_code'] = $nCode;
                $data['affectation_etat'] = $affectationEtat;
                $data['montant_scolarite'] = $montantScolarite;
                $data['statut_scolarite'] = $data['statut_scolarite'] ?? 'actif';
                $data['created_at_scolarite'] = date('Y-m-d H:i:s');

                if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
                if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;

                $filteredData = array_intersect_key($data, array_flip($cols));

                if ($this->model->create($filteredData)) {
                    $createdCount++;

                    // Duplication de l'échéancier des tranches pour chaque grille de scolarité
                    if (!empty($_POST['tranches']) && is_array($_POST['tranches'])) {
                        foreach ($_POST['tranches'] as $tData) {
                            $libelle = trim($tData['libelle_tranche'] ?? '');
                            $montant = (float)($tData['montant_tranche'] ?? 0);
                            if (empty($libelle) && $montant <= 0) {
                                continue;
                            }
                            if (empty($libelle)) {
                                $libelle = 'Tranche';
                            }
                            $dateLimite = !empty($tData['date_limite']) ? $tData['date_limite'] : date('Y-m-d');
                            $codeTranche = $this->validator->generateCode('tranches_scolarite', 'code_tranche', 'TRA-', 8);

                            $newTranche = [
                                'code_tranche' => $codeTranche,
                                'libelle_tranche' => $libelle,
                                'montant_tranche' => $montant,
                                'date_limite' => $dateLimite,
                                'scolarite_code' => $codeScolarite,
                                'filiere_code' => $fCode,
                                'niveau_code' => $nCode,
                                'annee_code' => $anneeCode,
                                'etablissement_code' => $etabCode,
                                'statut_tranche' => $tData['statut_tranche'] ?? 'actif',
                                'created_at_tranche' => date('Y-m-d H:i:s')
                            ];
                            if (in_array('user_code', $trancheCols)) {
                                $newTranche['user_code'] = $userCode;
                            }
                            $filteredTranche = array_intersect_key($newTranche, array_flip($trancheCols));
                            $trancheModel->create($filteredTranche);
                        }
                    }
                }
            }
        }

        if ($createdCount === 0) {
            if ($skippedCount > 0) {
                $this->error("Toutes les combinaisons (Filière x Niveau) sélectionnées existent déjà pour ce régime d'affectation.");
            } else {
                $this->error("Erreur lors de la création du tarif de scolarité.");
            }
            return;
        }

        $msg = "$createdCount grille(s) de tarif de scolarité créée(s) avec succès avec leurs échéanciers !";
        if ($skippedCount > 0) {
            $msg .= " ($skippedCount grille(s) déjà existante(s) ignorée(s)).";
        }
        $this->success($msg);
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_scolarite');
        if (!$id) { $this->error('Identifiant invalide'); return; }

        $item = $this->model->getById($id);
        if (!$item) { $this->error('Scolarité introuvable'); return; }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = !empty($_POST['annee_code']) ? trim($_POST['annee_code']) : ($item['annee_code'] ?? '');
        $filiereCode = !empty($_POST['filiere_code']) ? trim($_POST['filiere_code']) : ($item['filiere_code'] ?? '');
        $niveauCode = !empty($_POST['niveau_code']) ? trim($_POST['niveau_code']) : ($item['niveau_code'] ?? '');
        $affectationEtat = trim($_POST['affectation_etat'] ?? ($item['affectation_etat'] ?? 'affecte'));
        $montantScolarite = (float)($_POST['montant_scolarite'] ?? ($item['montant_scolarite'] ?? 0));
        $codeScolarite = $item['code_scolarite'];
        $etabCode = $this->getActiveEtablissementCode();

        // Contrôle d'unicité de la grille tarifaire (Année, Filière, Niveau, Régime)
        $stmtCheck = $this->model->getCon()->prepare("
            SELECT id_scolarite FROM scolarites 
            WHERE annee_code = ? AND filiere_code = ? AND niveau_code = ? AND affectation_etat = ? AND id_scolarite != ?
        ");
        $stmtCheck->execute([$anneeCode, $filiereCode, $niveauCode, $affectationEtat, $id]);
        if ($stmtCheck->fetch()) {
            $regimeName = ($affectationEtat === 'affecte') ? "Affecté (de l'État)" : "Non Affecté (Privé)";
            $this->error("Un tarif de scolarité pour le régime $regimeName existe déjà pour cette année, filière et niveau.");
            return;
        }

        // Validation des tranches
        $trancheErr = $this->validateTranchesScolarite($montantScolarite, $_POST['tranches'] ?? [], $anneeCode);
        if ($trancheErr) {
            $this->error($trancheErr);
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);
        unset($data['tranches']);
        unset($data['deleted_tranches_ids']);

        if (!empty($_POST['annee_code'])) {
            $data['annee_code'] = trim($_POST['annee_code']);
        }
        $cols = $this->model->getCon()->query("DESCRIBE scolarites")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->update($filteredData, $id)) {
            $db = $this->model->getCon();
            $trancheModel = new ModelTranche();
            $trancheCols = $db->query("DESCRIBE tranches_scolarite")->fetchAll(PDO::FETCH_COLUMN);

            // Gestion de la suppression des tranches retirées du panier
            if (!empty($_POST['deleted_tranches_ids'])) {
                $deletedIds = explode(',', $_POST['deleted_tranches_ids']);
                foreach ($deletedIds as $delId) {
                    $delId = (int)trim($delId);
                    if ($delId > 0) {
                        $trancheModel->delete($delId);
                    }
                }
            }

            // Enregistrement / mise à jour des tranches
            if (!empty($_POST['tranches']) && is_array($_POST['tranches'])) {
                foreach ($_POST['tranches'] as $tData) {
                    $libelle = trim($tData['libelle_tranche'] ?? '');
                    $montant = (float)($tData['montant_tranche'] ?? 0);
                    if (empty($libelle) && $montant <= 0) {
                        continue;
                    }
                    if (empty($libelle)) {
                        $libelle = 'Tranche';
                    }
                    $dateLimite = !empty($tData['date_limite']) ? $tData['date_limite'] : date('Y-m-d');
                    $trancheId = !empty($tData['id_tranche']) ? (int)$tData['id_tranche'] : null;

                    if ($trancheId && $trancheId > 0) {
                        // Mise à jour de la tranche existante
                        $updateTranche = [
                            'libelle_tranche' => $libelle,
                            'montant_tranche' => $montant,
                            'date_limite' => $dateLimite,
                            'scolarite_code' => $codeScolarite,
                            'filiere_code' => $filiereCode,
                            'niveau_code' => $niveauCode,
                            'annee_code' => $anneeCode,
                            'statut_tranche' => $tData['statut_tranche'] ?? 'actif'
                        ];
                        $filteredTranche = array_intersect_key($updateTranche, array_flip($trancheCols));
                        $trancheModel->update($filteredTranche, $trancheId);
                    } else {
                        // Création d'une nouvelle tranche
                        $codeTranche = $this->validator->generateCode('tranches_scolarite', 'code_tranche', 'TRA-', 8);
                        $newTranche = [
                            'code_tranche' => $codeTranche,
                            'libelle_tranche' => $libelle,
                            'montant_tranche' => $montant,
                            'date_limite' => $dateLimite,
                            'scolarite_code' => $codeScolarite,
                            'filiere_code' => $filiereCode,
                            'niveau_code' => $niveauCode,
                            'annee_code' => $anneeCode,
                            'etablissement_code' => $etabCode,
                            'statut_tranche' => $tData['statut_tranche'] ?? 'actif',
                            'created_at_tranche' => date('Y-m-d H:i:s')
                        ];
                        if (in_array('user_code', $trancheCols)) {
                            $newTranche['user_code'] = $userCode;
                        }
                        $filteredTranche = array_intersect_key($newTranche, array_flip($trancheCols));
                        $trancheModel->create($filteredTranche);
                    }
                }
            }

            $this->success('Tarif de scolarité et tranches mis à jour avec succès !');
        } else {
            $this->error('Erreur lors de la modification de la scolarité.');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
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

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT s.*, 
                       f.libelle_filiere, 
                       n.libelle_niveau, 
                       a.libelle_annee
                FROM scolarites s
                LEFT JOIN filieres f ON f.code_filiere = s.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = s.niveau_code
                LEFT JOIN annees a ON a.code_annee = s.annee_code
                WHERE s.id_scolarite = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { 
                $this->renderNotFound("La grille de scolarité demandée est introuvable.");
                return;
            }

            // Tranches / Échéancier de cette scolarité
            $stmtTranches = $this->model->getCon()->prepare("
                SELECT * FROM tranches_scolarite 
                WHERE scolarite_code = ? OR ((scolarite_code IS NULL OR scolarite_code = '') AND filiere_code = ? AND niveau_code = ?)
                ORDER BY date_limite ASC, id_tranche ASC
            ");
            $stmtTranches->execute([$item['code_scolarite'], $item['filiere_code'], $item['niveau_code']]);
            $tranches = $stmtTranches->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("ScolariteController::details error: " . $e->getMessage());
            $this->renderNotFound("La grille de scolarité demandée est introuvable.");
            return;
        }
        $this->loadView('../views/scolarites/details.php', [
            'item' => $item, 
            'tranches' => $tranches,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'scolarite/list'); exit(); }
            
            // Récupérer les tranches existantes associées à cette scolarité
            $stmtTranches = $this->model->getCon()->prepare("
                SELECT * FROM tranches_scolarite 
                WHERE scolarite_code = ? OR ((scolarite_code IS NULL OR scolarite_code = '') AND filiere_code = ? AND niveau_code = ? AND annee_code = ?)
                ORDER BY date_limite ASC, id_tranche ASC
            ");
            $stmtTranches->execute([$item['code_scolarite'], $item['filiere_code'], $item['niveau_code'], $item['annee_code']]);
            $tranches = $stmtTranches->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'scolarite/list'); exit();
        }
        $this->loadView('../views/scolarites/edit.php', [
            'item' => $item, 
            'tranches' => $tranches ?? [], 
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/scolarites/edit.php', [
            'item' => [], 
            'tranches' => []
        ]);
    }

    /**
     * Vérifie l'existence des combinaisons (Filière x Niveau) sélectionnées pour validation dynamique AJAX
     */
    public function checkExists()
    {
        $this->requireAuth();
        $anneeCode = !empty($_REQUEST['annee_code']) ? trim($_REQUEST['annee_code']) : ($this->getActiveAnneeCode());
        $affectationEtat = trim($_REQUEST['affectation_etat'] ?? 'affecte');
        $idExclude = (int)($_REQUEST['id_scolarite'] ?? 0);

        $filiereCodes = $_REQUEST['filiere_codes'] ?? ($_REQUEST['filiere_code'] ?? []);
        if (!is_array($filiereCodes)) {
            $filiereCodes = array_filter([trim($filiereCodes)]);
        }

        $niveauCodes = $_REQUEST['niveau_codes'] ?? ($_REQUEST['niveau_code'] ?? []);
        if (!is_array($niveauCodes)) {
            $niveauCodes = array_filter([trim($niveauCodes)]);
        }

        if (empty($anneeCode) || empty($filiereCodes) || empty($niveauCodes)) {
            $this->json(['existing' => [], 'total_combos' => 0, 'existing_count' => 0, 'all_exist' => false, 'message' => '']);
            return;
        }

        $db = $this->model->getCon();
        $existing = [];
        $totalCombos = count($filiereCodes) * count($niveauCodes);

        $sql = "SELECT s.id_scolarite, f.libelle_filiere, n.libelle_niveau 
                FROM scolarites s
                LEFT JOIN filieres f ON f.code_filiere = s.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = s.niveau_code
                WHERE s.annee_code = ? AND s.filiere_code = ? AND s.niveau_code = ? AND s.affectation_etat = ?";
        if ($idExclude > 0) {
            $sql .= " AND s.id_scolarite != ?";
        }

        $stmt = $db->prepare($sql);

        foreach ($filiereCodes as $fCode) {
            $fCode = trim($fCode);
            if (empty($fCode)) continue;

            foreach ($niveauCodes as $nCode) {
                $nCode = trim($nCode);
                if (empty($nCode)) continue;

                $queryParams = [$anneeCode, $fCode, $nCode, $affectationEtat];
                if ($idExclude > 0) {
                    $queryParams[] = $idExclude;
                }

                $stmt->execute($queryParams);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $fName = !empty($row['libelle_filiere']) ? $row['libelle_filiere'] : $fCode;
                    $nName = !empty($row['libelle_niveau']) ? $row['libelle_niveau'] : $nCode;
                    $existing[] = "$fName - $nName";
                }
            }
        }

        $existingCount = count($existing);
        $allExist = ($existingCount > 0 && $existingCount === $totalCombos);
        $regimeName = ($affectationEtat === 'affecte') ? "Affecté (de l'État)" : "Non Affecté (Privé)";

        $msg = '';
        if ($allExist) {
            $msg = "Doublon détecté : Toutes les combinaisons sélectionnées (" . implode(', ', $existing) . ") existent déjà en base pour le régime $regimeName.";
        } elseif ($existingCount > 0) {
            $msg = "Info : $existingCount grille(s) sur $totalCombos sélectionnée(s) existe(nt) déjà en base (" . implode(', ', $existing) . "). Seules les nouvelles grilles seront créées.";
        }

        $this->json([
            'existing' => $existing,
            'existing_count' => $existingCount,
            'total_combos' => $totalCombos,
            'all_exist' => $allExist,
            'message' => $msg
        ]);
    }
}
