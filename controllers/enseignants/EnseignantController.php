<?php

class EnseignantController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEnseignant();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ENSEIGNANTS);
        $stats = $this->model->getStats();
        $annees = (new ModelAnnee())->getAll();
        $selectedAnneeCode = Session::get('annee_active_code');
        $allEnseignants = $this->model->getAll();
        $this->loadView('../views/enseignants/list.php', [
            'stats' => $stats,
            'annees' => $annees,
            'selectedAnneeCode' => $selectedAnneeCode,
            'allEnseignants' => $allEnseignants
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ENSEIGNANTS);
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_enseignant'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json([
            'data' => $data,
            'stats' => $this->model->getStats()
        ]);
    }

    public function apiProgrammeJour()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ENSEIGNANTS);

        $date = trim($_GET['date'] ?? ($_POST['date'] ?? date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $ensCode = trim($_GET['enseignant_code'] ?? ($_POST['enseignant_code'] ?? ''));
        $anneeCode = trim($_GET['annee_code'] ?? ($_POST['annee_code'] ?? ''));

        // Conversion Date -> Jour de la semaine en français
        $jours = [
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi',
            7 => 'dimanche'
        ];
        $joursFr = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche'
        ];
        $moisFr = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        $time = strtotime($date);
        $numJour = (int)date('N', $time);
        $jourDb = $jours[$numJour] ?? 'lundi';
        $jourFr = $joursFr[$numJour] ?? 'Lundi';
        $moisNum = (int)date('n', $time);
        $dateFormatee = $jourFr . ' ' . date('d', $time) . ' ' . ($moisFr[$moisNum] ?? '') . ' ' . date('Y', $time);
        $isToday = ($date === date('Y-m-d'));

        $sql = "
            SELECT edt.id_emploi,
                   edt.code_emploi,
                   edt.classe_code,
                   edt.matiere_code,
                   edt.enseignant_code,
                   edt.salle_code,
                   edt.annee_code,
                   edt.jour,
                   edt.heure_debut,
                   edt.heure_fin,
                   ROUND(TIME_TO_SEC(TIMEDIFF(edt.heure_fin, edt.heure_debut)) / 3600, 1) AS duree,
                   m.libelle_matiere,
                   cl.libelle_classe,
                   s.libelle_salle,
                   u.code_user,
                   u.nom_user AS nom_enseignant,
                   u.prenom_user AS prenom_enseignant,
                   u.telephone_user AS telephone_enseignant,
                   u.email_user AS email_enseignant,
                   u.photo_user,
                   e.type_contrat
            FROM " . TABLES::EMPLOIS_TEMPS . " edt
            LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = edt.classe_code
            LEFT JOIN " . TABLES::SALLES . " s ON s.code_salle = edt.salle_code
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = edt.enseignant_code
            LEFT JOIN " . TABLES::ENSEIGNANTS . " e ON e.code_enseignant = edt.enseignant_code
            WHERE LOWER(edt.jour) = ?
              AND (edt.statut_emploi = '" . STATUTS::ACTIF . "' OR edt.statut_emploi IS NULL)
        ";
        $params = [$jourDb];

        if (!empty($anneeCode)) {
            $sql .= " AND edt.annee_code = ? ";
            $params[] = $anneeCode;
        }

        if (!empty($ensCode)) {
            $sql .= " AND edt.enseignant_code = ? ";
            $params[] = $ensCode;
        }

        $sql .= " ORDER BY edt.heure_debut ASC, cl.libelle_classe ASC ";

        try {
            $stmt = (new ModelEmploi())->getCon()->prepare($sql);
            $stmt->execute($params);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $ex) {
            error_log("apiProgrammeJour error: " . $ex->getMessage());
            $items = [];
        }

        $totalHeures = 0;
        $profsSet = [];
        $classesSet = [];
        $sallesSet = [];
        foreach ($items as $item) {
            $totalHeures += (float)($item['duree'] ?? 0);
            if (!empty($item['enseignant_code'])) $profsSet[$item['enseignant_code']] = true;
            if (!empty($item['classe_code'])) $classesSet[$item['classe_code']] = true;
            if (!empty($item['salle_code'])) $sallesSet[$item['salle_code']] = true;
        }

        $this->json([
            'status' => 1,
            'date' => $date,
            'jour' => $jourDb,
            'jour_libelle' => $jourFr,
            'date_formatee' => $dateFormatee,
            'is_today' => $isToday,
            'stats' => [
                'total_cours' => count($items),
                'total_heures' => round($totalHeures, 1),
                'nb_profs' => count($profsSet),
                'nb_classes' => count($classesSet),
                'nb_salles' => count($sallesSet)
            ],
            'data' => $items
        ]);
    }

    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ENSEIGNANTS);

        require_once __DIR__ . '/../../core/PdfService.php';

        $anneeCode = trim($_GET['annee_code'] ?? '');
        $sexe = strtoupper(trim($_GET['sexe'] ?? ''));
        $typeContrat = strtolower(trim($_GET['type_contrat'] ?? ''));

        $allEnseignants = $this->model->getAll();
        $enseignants = [];

        foreach ($allEnseignants as $ens) {
            // Filtre par année
            if (!empty($anneeCode)) {
                $anneesCodes = explode(',', $ens['annees_codes'] ?? '');
                if (!in_array($anneeCode, $anneesCodes, true)) {
                    continue;
                }
            }

            // Filtre par genre
            if (!empty($sexe)) {
                $rowSexe = strtoupper(trim($ens['sexe_user'] ?? ($ens['sexe_enseignant'] ?? '')));
                if ($sexe === 'M' && $rowSexe !== 'M' && $rowSexe !== 'MASCULIN') {
                    continue;
                }
                if ($sexe === 'F' && $rowSexe !== 'F' && $rowSexe !== 'FEMININ') {
                    continue;
                }
            }

            // Filtre par type de contrat
            if (!empty($typeContrat)) {
                $rowContrat = strtolower(trim($ens['type_contrat'] ?? ''));
                if ($rowContrat !== $typeContrat) {
                    continue;
                }
            }

            $enseignants[] = $ens;
        }

        // Tri alphabétique (Nom, puis Prénoms)
        usort($enseignants, function ($a, $b) {
            $nomA = mb_strtolower(trim(($a['nom_enseignant'] ?? '') . ' ' . ($a['prenom_enseignant'] ?? '')), 'UTF-8');
            $nomB = mb_strtolower(trim(($b['nom_enseignant'] ?? '') . ' ' . ($b['prenom_enseignant'] ?? '')), 'UTF-8');
            return strnatcmp($nomA, $nomB);
        });

        $etablissement = $this->getEtablissementConfig();
        $anneeLibelle = $this->getActiveAnneeLibelle();
        if (!empty($anneeCode)) {
            $anneeItem = (new ModelAnnee())->getByCode($anneeCode);
            if (!empty($anneeItem['libelle_annee'])) {
                $anneeLibelle = $anneeItem['libelle_annee'];
            }
        }

        $editeurNom = Auth::fullName();
        if (empty($editeurNom)) {
            $editeurNom = 'Direction des Études & Scolarité';
        }

        // Statistiques de la sélection
        $statsFiltre = [
            'total' => count($enseignants),
            'hommes' => 0,
            'femmes' => 0,
            'permanents' => 0,
            'vacataires' => 0
        ];
        foreach ($enseignants as $ens) {
            $sx = strtoupper(trim($ens['sexe_user'] ?? ($ens['sexe_enseignant'] ?? '')));
            if ($sx === 'M' || $sx === 'MASCULIN') $statsFiltre['hommes']++;
            else if ($sx === 'F' || $sx === 'FEMININ') $statsFiltre['femmes']++;

            $tc = strtolower(trim($ens['type_contrat'] ?? ''));
            if ($tc === 'permanent') $statsFiltre['permanents']++;
            else if ($tc === 'vacataire' || $tc === 'prestataire') $statsFiltre['vacataires']++;
        }

        $data = [
            'etablissement' => $etablissement,
            'annee_libelle' => $anneeLibelle,
            'annee_code' => $anneeCode,
            'sexe_filtre' => $sexe,
            'contrat_filtre' => $typeContrat,
            'editeur_nom' => $editeurNom,
            'stats' => $statsFiltre,
            'enseignants' => $enseignants
        ];

        $html = PdfService::renderTemplate('repertoire_enseignants.php', $data);
        $filename = 'Repertoire_Enseignants_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Répertoire du Corps Enseignant - ' . ($etablissement['libelle_etablissement'] ?? 'GEICG'),
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 12
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ENSEIGNANTS);
        $currentUserCode = Auth::code();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        $mode = trim($data['mode_creation'] ?? 'nouveau'); // 'nouveau' ou 'existant'

        // 1. Cas : Rattachement d'un employé / utilisateur existant
        if ($mode === 'existant') {
            $codeUser = trim($data['user_code_existant'] ?? '');
            if (empty($codeUser)) {
                $this->error('Veuillez sélectionner un compte employé à rattacher comme enseignant.');
                return;
            }
            
            $errorMsg = null;
            $userRow = $this->model->attachExistingUser($codeUser, $etabCode, $currentUserCode, $data, $errorMsg);
            if ($userRow) {
                $this->success("L'employé <strong>{$userRow['nom_user']} {$userRow['prenom_user']}</strong> a été configuré comme enseignant avec succès !", [
                    'redirect' => RACINE . 'enseignant/list'
                ]);
            } else {
                $this->error($errorMsg ?: "Erreur lors de la configuration du profil enseignant.");
            }
            return;
        }

        // 2. Cas : Création complète d'un nouvel enseignant (User + Enseignant)
        $nom = trim($data['nom_user'] ?? ($data['nom_enseignant'] ?? ''));
        $prenom = trim($data['prenom_user'] ?? ($data['prenom_enseignant'] ?? ''));
        $email = trim($data['email_user'] ?? ($data['email_enseignant'] ?? ''));
        $telephone = Validator::cleanPhone($data['telephone_user'] ?? ($data['telephone_enseignant'] ?? ''));
        $sexe = ($data['sexe_user'] ?? ($data['sexe_enseignant'] ?? 'M')) === 'F' ? 'Féminin' : 'Masculin';

        if (empty($nom) || empty($prenom)) {
            $this->error('Le nom et le prénom sont obligatoires.');
            return;
        }

        if (!empty($email) && $this->model->isEmailTaken($email)) {
            $this->error('Cette adresse email est déjà utilisée par un compte utilisateur.');
            return;
        }

        $codeUser = $this->validator->generateCode(TABLES::USERS, 'code_user', 'ENS-', 8);
        $userData = [
            'code_user' => $codeUser,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone,
            'sexe' => $sexe,
            'password_raw' => !empty($data['password_user']) ? trim($data['password_user']) : null
        ];

        $ensData = [
            'grade_enseignant' => $data['grade_enseignant'] ?? 'Enseignant',
            'type_contrat' => $data['type_contrat'] ?? 'permanent',
            'numero_autorisation' => !empty($data['numero_autorisation']) ? trim($data['numero_autorisation']) : null
        ];

        $errorMsg = null;
        $rawPassword = $this->model->createNewTeacher($userData, $ensData, $etabCode, $currentUserCode, $errorMsg);

        if ($rawPassword !== null) {
            $idDisplay = $email ?: ($telephone ?: $codeUser);
            $this->success("Enseignant et compte utilisateur créés avec succès ! Identifiant : <strong>{$idDisplay}</strong> | Mot de passe : <strong style='color:#15803D;'>{$rawPassword}</strong>", [
                'password' => $rawPassword,
                'redirect' => RACINE . 'enseignant/list'
            ]);
        } else {
            $this->error($errorMsg ?: "Erreur lors de la création de l'enseignant.");
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ENSEIGNANTS);
        $id = (int)$this->post('id_enseignant');
        if (!$id) { $this->error('Identifiant invalide'); return; }

        $item = $this->model->getById($id);
        if (!$item) { $this->error('Enseignant introuvable'); return; }

        $data = $_POST;
        unset($data['csrf_token']);

        $nom = trim($data['nom_user'] ?? ($data['nom_enseignant'] ?? ''));
        $prenom = trim($data['prenom_user'] ?? ($data['prenom_enseignant'] ?? ''));
        $email = trim($data['email_user'] ?? ($data['email_enseignant'] ?? ''));
        $telephone = Validator::cleanPhone($data['telephone_user'] ?? ($data['telephone_enseignant'] ?? ''));
        $sexe = ($data['sexe_user'] ?? ($data['sexe_enseignant'] ?? 'M')) === 'F' ? 'Féminin' : 'Masculin';

        if (empty($nom) || empty($prenom)) {
            $this->error('Le nom et le prénom sont obligatoires.');
            return;
        }

        if (!empty($email) && $this->model->isEmailTaken($email, $item['code_enseignant'])) {
            $this->error('Cette adresse email est déjà enregistrée pour un autre utilisateur.');
            return;
        }

        $rawPwd = !empty($data['password_user']) ? $data['password_user'] : ($data['password_enseignant'] ?? null);
        $userData = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone,
            'sexe' => $sexe,
            'password_raw' => $rawPwd
        ];

        $ensData = [
            'grade_enseignant' => $data['grade_enseignant'] ?? 'Enseignant',
            'type_contrat' => $data['type_contrat'] ?? 'permanent',
            'numero_autorisation' => !empty($data['numero_autorisation']) ? trim($data['numero_autorisation']) : null,
            'statut_enseignant' => $data['statut_enseignant'] ?? 'actif'
        ];

        $errorMsg = null;
        if ($this->model->updateTeacherAndUser($id, $item['code_enseignant'], $userData, $ensData, $errorMsg)) {
            $this->success('Profil enseignant et compte utilisateur mis à jour avec succès !', [
                'redirect' => RACINE . 'enseignant/list'
            ]);
        } else {
            $this->error($errorMsg ?: 'Erreur lors de la modification.');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ENSEIGNANTS);
        $id = $this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès !', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Enseignant introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ENSEIGNANTS);
        try {
            $id = $this->validator->decrypter($details);
            if (!$id || !is_numeric($id)) $id = is_numeric($details) ? (int)$details : 0;

            $item = $this->model->getById((int)$id);
            if (!$item) { header('Location: ' . RACINE . 'enseignant/list'); exit(); }

            $cours = $this->model->getCoursForEnseignant($item['code_enseignant']);
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'enseignant/list'); exit();
        }
        $this->loadView('../views/enseignants/details.php', [
            'item' => $item, 
            'cours' => $cours,
            'encryptedId' => $encryptedId
        ]);
    }

    /**
     * Génère et imprime la fiche officielle d'un enseignant en PDF
     */
    public function imprimerFichePdf($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_ENSEIGNANTS);
        require_once __DIR__ . '/../../core/PdfService.php';

        try {
            $id = $this->validator->decrypter($details);
            if (!$id || !is_numeric($id)) {
                $id = is_numeric($details) ? (int)$details : 0;
            }

            $item = $this->model->getById((int)$id);
            if (!$item) {
                header('Location: ' . RACINE . 'enseignant/list');
                exit();
            }

            $cours = $this->model->getCoursForEnseignant($item['code_enseignant']);

            $dataView = [
                'item' => $item,
                'cours' => $cours,
                'etablissement' => $this->getEtablissementConfig()
            ];

            $html = PdfService::renderTemplate('fiche_enseignant.php', $dataView);
            if (isset($_GET['html'])) {
                echo $html;
                return;
            }

            $nomClean = preg_replace('/[^A-Za-z0-9_\-]/', '_', trim(($item['nom_enseignant'] ?? '') . '_' . ($item['prenom_enseignant'] ?? '')));
            $filename = 'Fiche_Enseignant_' . ($nomClean ?: 'Profil') . '_' . date('Ymd_His') . '.pdf';

            // Journalisation de l'impression
            $this->logAction(
                AuditLogger::ACTION_PRINT,
                'enseignant',
                $item['code_enseignant'] ?? (string)$id,
                "Impression de la fiche officielle de l'enseignant " . trim(($item['nom_enseignant'] ?? '') . ' ' . ($item['prenom_enseignant'] ?? ''))
            );

            PdfService::generate($html, $filename, [
                'orientation' => 'P',
                'format' => 'A4',
                'title' => 'Fiche Enseignant - ' . trim(($item['nom_enseignant'] ?? '') . ' ' . ($item['prenom_enseignant'] ?? '')),
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_top' => 8,
                'margin_bottom' => 8
            ]);
        } catch (Exception $e) {
            error_log("imprimerFichePdf error: " . $e->getMessage());
            header('Location: ' . RACINE . 'enseignant/list');
            exit();
        }
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ENSEIGNANTS);
        try {
            $id = $this->validator->decrypter($details);
            if (!$id || !is_numeric($id)) $id = is_numeric($details) ? (int)$details : 0;

            $item = $this->model->getById((int)$id);
            if (!$item) { header('Location: ' . RACINE . 'enseignant/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
            $availableUsers = $this->model->getUsersAvailableForTeacher();
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'enseignant/list'); exit();
        }
        $this->loadView('../views/enseignants/edit.php', [
            'item' => $item, 
            'availableUsers' => $availableUsers,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_ENSEIGNANTS);
        $availableUsers = $this->model->getUsersAvailableForTeacher();
        $this->loadView('../views/enseignants/edit.php', [
            'item' => [],
            'availableUsers' => $availableUsers
        ]);
    }
}