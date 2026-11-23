<?php

class UserController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelUser();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_USERS);
        $this->loadView('../views/users/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_USERS);
        $users = $this->model->getUsersWithRolesAndFonctions();

        $data = [];
        foreach ($users as $u) {
            $idCrypte = $this->validator->crypter($u['id_user']);
            $roleNames = !empty($u['roles_libelles']) ? explode('||', $u['roles_libelles']) : [];
            $roleCodes = !empty($u['roles_codes']) ? explode(',', $u['roles_codes']) : [];
            $data[] = [
                'code' => $u['code_user'],
                'nom' => $u['nom_user'],
                'prenom' => $u['prenom_user'] ?? '',
                'email' => $u['email_user'] ?? '',
                'telephone' => $u['telephone_user'] ?? '',
                'fonction' => $u['libelle_fonction'] ?? '-',
                'role' => !empty($roleNames) ? implode(', ', $roleNames) : 'Non attribué',
                'roles_list' => $roleNames,
                'role_code' => !empty($roleCodes) ? $roleCodes[0] : '',
                'roles_codes' => $roleCodes,
                'statut' => $u['statut_user'],
                'id' => $u['id_user'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_USERS);

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $telephone = Validator::cleanPhone($_POST['telephone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $postedRoles = $_POST['roles'] ?? ($_POST['role_code'] ?? []);
        if (!is_array($postedRoles)) {
            $postedRoles = [$postedRoles];
        }
        $postedRoles = array_values(array_unique(array_filter(array_map('trim', $postedRoles))));
        if (empty($postedRoles)) {
            $postedRoles = [$this->model->getDefaultRoleCode()];
        }

        $fonctionCode = $_POST['fonction_code'] ?? null;
        $rawPassword = !empty($_POST['password']) ? trim($_POST['password']) : ('USR' . rand(100000, 999999));

        if (empty($nom)) {
            $this->error('Le nom de l\'utilisateur est obligatoire !');
            return;
        }

        if (!empty($email) && $this->model->existsEmail($email)) {
            $this->error('Cette adresse email est déjà attribuée à un compte !');
            return;
        }

        if (!empty($telephone) && $this->model->existsTelephone($telephone)) {
            $this->error('Ce numéro de téléphone est déjà attribué à un compte !');
            return;
        }

        $code_user = $this->validator->generateCode(TABLES::USERS, 'code_user', 'USR-', 8);
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);
        $etabCode = $this->getActiveEtablissementCode();

        $data = [
            'code_user' => $code_user,
            'nom_user' => $nom,
            'prenom_user' => $prenom,
            'telephone_user' => $telephone ?: null,
            'email_user' => $email ?: null,
            'sexe_user' => $_POST['sexe_user'] ?? 'M',
            'password_user' => $password,
            'fonction_code' => $fonctionCode,
            'etablissement_code' => $etabCode,
            'statut_user' => STATUTS::ACTIF,
            'created_at_user' => date('Y-m-d H:i:s')
        ];

        $filteredData = $this->model->filterData($data);

        if ($this->model->create($filteredData)) {
            $rolePerms = $_POST['role_perms'] ?? [];
            $rolesData = [];
            foreach ($postedRoles as $r) {
                if (is_string($r) && !empty($r)) {
                    $rolesData[$r] = [
                        'create' => isset($rolePerms[$r]['create']) ? 1 : (isset($_POST['create_permission']) ? 1 : 1),
                        'edit'   => isset($rolePerms[$r]['edit']) ? 1 : (isset($_POST['edit_permission']) ? 1 : 1),
                        'show'   => isset($rolePerms[$r]['show']) ? 1 : (isset($_POST['show_permission']) ? 1 : 1),
                        'delete' => isset($rolePerms[$r]['delete']) ? 1 : (isset($_POST['delete_permission']) ? 1 : 0),
                    ];
                }
            }

            $this->model->syncUserRoles($code_user, $rolesData);

            if (!empty($_POST['annee_acces']) && is_array($_POST['annee_acces'])) {
                $this->model->saveUserAnneeAcces($code_user, $_POST['annee_acces']);
            }

            $nomComplet = trim($nom . ' ' . $prenom);
            $this->logAction(
                AuditLogger::ACTION_CREATE,
                'user',
                $code_user,
                "Création du compte utilisateur {$nomComplet} ({$code_user})",
                [
                    'code_user' => $code_user,
                    'nom_complet' => $nomComplet,
                    'email' => $email,
                    'telephone' => $telephone,
                    'roles' => $postedRoles,
                    'fonction_code' => $fonctionCode
                ],
                $etabCode
            );

            // Envoi automatique de l'e-mail de bienvenue si email présent
            if (!empty($email)) {
                $nomComplet = trim($nom . ' ' . $prenom);
                $libelleFonction = (!empty($fonctionCode) ? $this->model->getFonctionLibelle($fonctionCode) : null) ?: 'Collaborateur / Utilisateur';

                MailerService::sendTemplate(
                    $email,
                    'Vos Identifiants d\'Accès - GROUPE EICG',
                    'welcome_credentials',
                    [
                        'userNom'      => $nomComplet,
                        'userEmail'    => $email,
                        'userPassword' => $rawPassword,
                        'userFonction' => $libelleFonction,
                        'loginUrl'     => url('user/connexion')
                    ]
                );
            }

            $this->success("Utilisateur créé avec succès !", [
                'redirect' => url('user/list')
            ]);
        } else {
            $this->error('Erreur lors de la création de l\'utilisateur.');
        }
    }

    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            redirect('user/list');
        }

        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_USERS);
        $id = (int)$this->post('id_user');
        if (!$id) { $this->error('Identifiant invalide', url('user/list')); return; }

        $user = $this->model->getById($id);
        if (!$user) { $this->error('Utilisateur introuvable', url('user/list')); return; }

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $telephone = Validator::cleanPhone($_POST['telephone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        // Multi-rôles : supporte tableau roles[] ou role_code simple
        $postedRoles = $_POST['roles'] ?? ($_POST['role_code'] ?? []);
        if (!is_array($postedRoles)) {
            $postedRoles = !empty($postedRoles) ? [$postedRoles] : [];
        }
        $postedRoles = array_values(array_unique(array_filter(array_map('trim', $postedRoles))));

        $fonctionCode = $_POST['fonction_code'] ?? null;
        $statut = ($_POST['actif'] ?? '') === '0' || ($_POST['actif'] ?? '') === 'inactif' ? STATUTS::INACTIF : STATUTS::ACTIF;

        if (empty($nom)) {
            $this->error('Le nom est obligatoire !');
            return;
        }

        if (!empty($email) && $this->model->existsEmail($email, $id)) {
            $this->error('Cette adresse email est déjà utilisée par un autre compte.');
            return;
        }

        if (!empty($telephone) && $this->model->existsTelephone($telephone, $id)) {
            $this->error('Ce numéro de téléphone est déjà utilisé par un autre compte.');
            return;
        }

        $data = [
            'id_user' => $id,
            'nom_user' => $nom,
            'prenom_user' => $prenom,
            'telephone_user' => $telephone ?: null,
            'email_user' => $email ?: null,
            'sexe_user' => $_POST['sexe_user'] ?? 'M',
            'fonction_code' => $fonctionCode,
            'statut_user' => $statut,
            'updated_at_user' => date('Y-m-d H:i:s')
        ];

        if (!empty($_POST['password'])) {
            $data['password_user'] = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        }

        $filteredData = $this->model->filterData($data);

        if ($this->model->update($filteredData, $id)) {
            if (!empty($postedRoles)) {
                $rolePerms = $_POST['role_perms'] ?? [];
                $rolesData = [];
                foreach ($postedRoles as $r) {
                    if (is_string($r) && !empty($r)) {
                        $rolesData[$r] = [
                            'create' => isset($rolePerms[$r]['create']) ? 1 : 0,
                            'edit'   => isset($rolePerms[$r]['edit']) ? 1 : 0,
                            'show'   => isset($rolePerms[$r]['show']) ? 1 : 0,
                            'delete' => isset($rolePerms[$r]['delete']) ? 1 : 0,
                        ];
                    }
                }

                $this->model->syncUserRoles($user['code_user'], $rolesData);
            }

            if (isset($_POST['annee_acces']) && is_array($_POST['annee_acces'])) {
                $this->model->saveUserAnneeAcces($user['code_user'], $_POST['annee_acces']);
            }

            $nomComplet = trim($nom . ' ' . $prenom);
            $this->logAction(
                AuditLogger::ACTION_UPDATE,
                'user',
                $user['code_user'] ?? (string)$id,
                "Modification des informations du compte utilisateur {$nomComplet} (" . ($user['code_user'] ?? $id) . ")",
                [
                    'id_user' => $id,
                    'code_user' => $user['code_user'] ?? null,
                    'nom_complet' => $nomComplet,
                    'roles' => $postedRoles,
                    'fonction_code' => $fonctionCode,
                    'statut' => $statut
                ]
            );

            $this->success('Utilisateur et permissions par rôle mis à jour avec succès !', url('user/list'));
        } else {
            $this->error('Erreur lors de la modification de l\'utilisateur.');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_USERS);
        $id = $this->post('id') ?: $this->post('id_user');
        if (isset($id) && ($usr = $this->model->getById($id))) {
            if ($this->model->toggleStatus($id)) {
                $this->logAction(
                    AuditLogger::ACTION_UPDATE,
                    'user',
                    $usr['code_user'] ?? (string)$id,
                    "Basculement de statut (actif/inactif) du compte utilisateur #{$id}",
                    ['id_user' => $id, 'code_user' => $usr['code_user'] ?? null]
                );
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur lors du changement de statut.');
            }
        } else {
            $this->error('Utilisateur introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_USERS);
        try {
            $userProfile = null;
            if (is_numeric($details)) {
                $userProfile = $this->model->getUserDetailsById((int)$details);
            }
            if (!$userProfile) {
                $decrypted = $this->validator->decrypter($details);
                if (is_numeric($decrypted) && (int)$decrypted > 0) {
                    $userProfile = $this->model->getUserDetailsById((int)$decrypted);
                } elseif (is_string($decrypted) && $decrypted !== '') {
                    $byCode = $this->model->getByCode($decrypted, 'code_user');
                    if (!empty($byCode['id_user'])) {
                        $userProfile = $this->model->getUserDetailsById((int)$byCode['id_user']);
                    }
                }
            }
            if (!$userProfile && is_string($details) && $details !== '') {
                $byCode = $this->model->getByCode($details, 'code_user');
                if (!empty($byCode['id_user'])) {
                    $userProfile = $this->model->getUserDetailsById((int)$byCode['id_user']);
                }
            }
            if (!$userProfile) {
                redirect('user/list');
                return;
            }

            $userId = (int)$userProfile['id_user'];
            $userCode = (string)($userProfile['code_user'] ?? '');

            $userRoles = $this->model->getUserRoles($userCode);
            foreach ($userRoles as &$ur) {
                $ur['encrypted_role_id'] = !empty($ur['role_id'])
                    ? $this->validator->crypter((int)$ur['role_id'])
                    : '';
            }
            unset($ur);

            $primaryRole = !empty($userRoles) ? $userRoles[0] : null;
            $permissionsDetailed = $this->model->getUserPermissionsDetailed($userCode);

            $anneesAcces = $this->model->getUserAnneeAccesDetails($userCode);
            foreach ($anneesAcces as &$aa) {
                $aa['encrypted_annee_id'] = !empty($aa['id_annee'])
                    ? $this->validator->crypter((int)$aa['id_annee'])
                    : '';
            }
            unset($aa);

            $enseignantProfile = $this->model->getEnseignantLink($userCode);
            $encryptedEnseignantId = !empty($enseignantProfile['id_enseignant'])
                ? $this->validator->crypter((int)$enseignantProfile['id_enseignant'])
                : '';

            $canViewLogs = Auth::can(PERMISSIONS::VIEW_LOGS) || Auth::isSuperAdmin();
            $recentLogs = $canViewLogs ? $this->model->getUserRecentLogs($userCode, 15) : [];
            $totalLogsCount = $this->model->getUserLogsCount($userCode);

            $etablissement = $this->getEtablissementConfig();
            $encryptedId = $this->validator->crypter($userId);
            $isSelf = ((int)Auth::id() === $userId);
        } catch (Exception $e) {
            error_log("UserController::details error: " . $e->getMessage());
            redirect('user/list');
            return;
        }

        $this->loadView('../views/users/details.php', [
            'user'                  => $userProfile,
            'role'                  => $primaryRole,
            'userRoles'             => $userRoles,
            'permissionsDetailed'   => $permissionsDetailed,
            'anneesAcces'           => $anneesAcces,
            'enseignantProfile'     => $enseignantProfile,
            'encryptedEnseignantId' => $encryptedEnseignantId,
            'recentLogs'            => $recentLogs,
            'totalLogsCount'        => $totalLogsCount,
            'etablissement'         => $etablissement,
            'encryptedId'           => $encryptedId,
            'isSelf'                => $isSelf
        ]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_USERS);
        $roles = (new ModelRole())->getAll();
        $fonctions = (new ModelFonction())->getAll();
        $allAnneesList = (new ModelAnnee())->getAll();
        $this->loadView('../views/users/edit.php', [
            'user' => [],
            'role' => [],
            'userRoles' => [],
            'userRoleCodes' => [],
            'roles' => $roles,
            'fonctions' => $fonctions,
            'allAnneesList' => $allAnneesList,
            'userAnneeAcces' => []
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_USERS);
        try {
            $decryptedId = $this->validator->decrypter($details);
            $userProfile = $this->model->getById($decryptedId);

            if (!$userProfile) {
                redirect('user/list');
            }
            $userRoles = $this->model->getUserRoles($userProfile['code_user']);
            $userRoleCodes = $this->model->getUserRoleCodes($userProfile['code_user']);
            $primaryRole = !empty($userRoles) ? $userRoles[0] : null;
            $roles = (new ModelRole())->getAll();
            $fonctions = (new ModelFonction())->getAll();
            $allAnneesList = (new ModelAnnee())->getAll();
            $userAnneeAcces = $this->model->getUserAnneeAcces($userProfile['code_user']);
        } catch (Exception $e) {
            redirect('user/list');
        }

        $this->loadView('../views/users/edit.php', [
            'user' => $userProfile,
            'role' => $primaryRole,
            'userRoles' => $userRoles,
            'userRoleCodes' => $userRoleCodes,
            'roles' => $roles,
            'fonctions' => $fonctions,
            'allAnneesList' => $allAnneesList,
            'userAnneeAcces' => $userAnneeAcces
        ]);
    }

    public function profil()
    {
        $this->requireAuth();
        $userId = Auth::id() ?: 0;
        $editId = $this->validator->crypter($userId);
        $userProfile = $this->model->getById((int)$userId);
        $userRoles = $this->model->getUserRoles($userProfile['code_user'] ?? '');
        $primaryRole = !empty($userRoles) ? $userRoles[0] : null;
        $this->loadView('../views/users/profil.php', [
            'user' => $userProfile,
            'role' => $primaryRole,
            'userRoles' => $userRoles,
            'editId' => $editId
        ]);
    }

    public function connexion()
    {
        $this->requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->loadView('../views/users/connexion.php');
            return;
        }

        $this->requirePost(false);

        // 1. Vérification stricte par IP dès l'entrée (5 échecs max / 15 min) :
        //    empêche tout contournement en changeant d'identifiant après verrouillage.
        $this->enforceRateLimit('login_ip', 5, 900, null, false, 'Trop de tentatives de connexion depuis cette adresse. Veuillez réessayer dans {wait}.');

        $notEmpty = Validator::validateRequiredFields($_POST);

        if ($notEmpty === true) {
            $login = trim($this->post('login'));
            $password = $this->post('password');
            $loginNorm = mb_strtolower($login);

            // 2. Vérification par couple IP + identifiant saisi (5 échecs max / 15 min)
            $this->enforceRateLimit('login', 5, 900, $loginNorm, false, 'Trop de tentatives de connexion pour cet identifiant. Veuillez réessayer dans {wait}.');

            // Recherche utilisateur via ModelUser
            $user = $this->model->findUserByLogin($login);
            $userCode = ($user && !empty($user['code_user'])) ? (string)$user['code_user'] : null;

            // 3. Vérification canonique par compte utilisateur (indépendante de l'alias email/tél/matricule et de l'IP)
            //    Exécutée AVANT password_verify() : même avec le bon mot de passe, un compte verrouillé reste bloqué.
            if ($userCode !== null) {
                $this->enforceRateLimit('login_account', 5, 900, $userCode, false, 'Trop de tentatives de connexion pour ce compte. Veuillez réessayer dans {wait}.', false);
            }

            if ($user && password_verify($password, $user['password_user'] ?? '')) {
                if ($user['statut_user'] === STATUTS::ACTIF) {
                    // Réinitialisation des compteurs Rate Limit après succès authentifié
                    $this->clearRateLimit('login_ip', null);
                    $this->clearRateLimit('login', $loginNorm);
                    if ($userCode !== null) {
                        $this->clearRateLimit('login_account', $userCode, false);
                    }

                    $userRoles = $this->model->getUserRoles($user['code_user']);
                    $roleCodes = !empty($userRoles) ? array_column($userRoles, 'role_code') : [ROLES::USER];
                    $roleCode = !empty($roleCodes) ? $roleCodes[0] : ROLES::USER;

                    // Profil enseignant actif éventuel
                    $enseignantProfile = $this->model->getEnseignantProfile($user['code_user']);

                    // Permissions CRUD cumulées
                    $createPerm = 0; $editPerm = 0; $showPerm = 0; $deletePerm = 0;
                    if (!empty($userRoles)) {
                        foreach ($userRoles as $ur) {
                            if (!empty($ur['create_permission'])) $createPerm = 1;
                            if (!empty($ur['edit_permission']))   $editPerm = 1;
                            if (!empty($ur['show_permission']))   $showPerm = 1;
                            if (!empty($ur['delete_permission'])) $deletePerm = 1;
                        }
                    } else {
                        $createPerm = 1; $editPerm = 1; $showPerm = 1; $deletePerm = 0;
                    }

                    // Récupérer toutes les permissions métier des rôles
                    $allPermissions = $this->model->getPermissionsByRoles($roleCodes);

                    $sessionData = [
                        'id_user' => $user['id_user'],
                        'code_user' => $user['code_user'],
                        'nom' => $user['nom_user'],
                        'nom_user' => $user['nom_user'],
                        'prenom' => $user['prenom_user'] ?? '',
                        'prenom_user' => $user['prenom_user'] ?? '',
                        'email' => $user['email_user'] ?? '',
                        'email_user' => $user['email_user'] ?? '',
                        'tel' => $user['telephone_user'] ?? '',
                        'role_code' => $roleCode,
                        'roles' => $roleCodes,
                        'roles_details' => $userRoles,
                        'is_enseignant' => !empty($enseignantProfile) || in_array(ROLES::ENSEIGNANT, $roleCodes, true),
                        'code_enseignant' => $enseignantProfile['code_enseignant'] ?? $user['code_user'],
                        'grade_enseignant' => $enseignantProfile['grade_enseignant'] ?? null,
                        'type_contrat' => $enseignantProfile['type_contrat'] ?? null,
                        'permissions' => [
                            'create' => $createPerm,
                            'edit' => $editPerm,
                            'show' => $showPerm,
                            'delete' => $deletePerm
                        ]
                    ];

                    Auth::login($sessionData, $roleCodes, $allPermissions);
                    Session::set('roles', $roleCodes);

                    // Journalisation de la connexion
                    AuditLogger::logLogin(
                        (int)$user['id_user'],
                        $user['code_user'] ?? '',
                        trim(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? '')),
                        $user['email_user'] ?? $login
                    );

                    // Année académique active
                    $anneeActive = $this->model->getActiveAnneeAcademique();
                    if ($anneeActive) {
                        Session::set('annee_active_code', $anneeActive['code_annee']);
                        Session::set('annee_active_libelle', $anneeActive['libelle_annee']);
                    } else {
                        Session::set('annee_active_code', '');
                        Session::set('annee_active_libelle', 'Aucune');
                    }

                    $welcomeMsg = !empty($enseignantProfile) 
                        ? 'Bienvenue Professeur ' . htmlspecialchars($user['nom_user'] . ' ' . ($user['prenom_user'] ?? '')) . ' !'
                        : 'Connexion réussie ! Bienvenue sur GEICG.';

                    $this->success($welcomeMsg, ['redirect' => url()]);
                    return;
                } else {
                    $this->hitRateLimit('login_ip', 5, 900, null);
                    $this->hitRateLimit('login', 5, 900, $loginNorm);
                    if ($userCode !== null) {
                        $this->hitRateLimit('login_account', 5, 900, $userCode, false);
                    }
                    $this->error('Ce compte utilisateur est inactif ou suspendu. Veuillez contacter l\'administrateur.');
                    return;
                }
            }

            $hitIp = $this->hitRateLimit('login_ip', 5, 900, null);
            $hitLogin = $this->hitRateLimit('login', 5, 900, $loginNorm);
            $hitAccount = ($userCode !== null)
                ? $this->hitRateLimit('login_account', 5, 900, $userCode, false)
                : null;

            $remaining = min(
                (int)($hitIp['remaining'] ?? 5),
                (int)($hitLogin['remaining'] ?? 5),
                $hitAccount !== null ? (int)($hitAccount['remaining'] ?? 5) : 5
            );
            $retryAfter = max(
                (int)($hitIp['retry_after'] ?? 0),
                (int)($hitLogin['retry_after'] ?? 0),
                $hitAccount !== null ? (int)($hitAccount['retry_after'] ?? 0) : 0
            );

            if ($remaining <= 0) {
                $waitSec = $retryAfter > 0 ? $retryAfter : 900;
                $waitTxt = RateLimiter::formatRemainingTime($waitSec);
                AuditLogger::log(
                    AuditLogger::ACTION_RATE_LIMIT,
                    'security_rate_limit',
                    'login',
                    "Verrouillage Rate Limit activé après 5 échecs de connexion pour IP " . RateLimiter::getClientIp(),
                    ['action' => 'login', 'ip' => RateLimiter::getClientIp(), 'retry_after' => $waitSec]
                );
                $this->error("Identifiant ou mot de passe incorrect. Trop d'échecs consécutifs : accès temporairement bloqué pour {$waitTxt}.");
                return;
            }
            $this->error("Identifiant ou mot de passe incorrect. ({$remaining} tentative" . ($remaining > 1 ? 's' : '') . " restante" . ($remaining > 1 ? 's' : '') . ")");
            return;
        } else {
            $this->error('Veuillez renseigner tous les champs !');
        }
    }

    public function decon()
    {
        AuditLogger::logLogout();
        Auth::logout();
        redirect('user/connexion');
    }

    /**
     * Demande de réinitialisation de mot de passe (Mot de passe oublié)
     */
    public function motDePasseOublie()
    {
        $this->requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->loadView('../views/users/forgot_password.php', [
                'etablissement' => $this->getEtablissementConfig()
            ]);
            return;
        }

        $this->requirePost(true);

        $identifiant = trim((string)$this->post('login'));
        if ($identifiant === '') {
            $this->error('Veuillez saisir votre adresse e-mail ou votre numéro de téléphone.');
            return;
        }

        // Protection Rate Limiting anti-spam SMTP (3 demandes max / 15 min par IP et par identifiant)
        $this->enforceRateLimit('forgot_password_ip', 3, 900, null, true, 'Trop de demandes de réinitialisation. Veuillez patienter {wait} avant de réessayer.');
        $this->enforceRateLimit('forgot_password_id', 3, 900, mb_strtolower($identifiant), true, 'Une demande de réinitialisation a déjà été envoyée plusieurs fois pour cet identifiant. Veuillez patienter {wait}.');

        $genericSuccessMsg = "Si un compte actif est associé à cet identifiant, un e-mail contenant les instructions de réinitialisation vient d'être envoyé.";
        $user = $this->model->findUserByLogin($identifiant);

        if ($user && ($user['statut_user'] ?? '') === STATUTS::ACTIF && !empty($user['email_user'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $rawToken = $this->model->createPasswordResetToken(
                (string)$user['code_user'],
                (string)$user['email_user'],
                60,
                $ipAddress
            );

            if ($rawToken) {
                $etabCfg = $this->getEtablissementConfig();
                $sigleEtab = !empty($etabCfg['sigle_etablissement'])
                    ? $etabCfg['sigle_etablissement']
                    : (!empty($etabCfg['nom_etablissement']) ? $etabCfg['nom_etablissement'] : 'GEICG');

                $resetUrl = url('user/reset-password/' . $rawToken);
                $nomComplet = trim(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? ''));

                $mailRes = MailerService::sendTemplate(
                    (string)$user['email_user'],
                    'Réinitialisation de votre mot de passe - ' . $sigleEtab,
                    'reset_password',
                    [
                        'userNom'       => $nomComplet ?: 'Utilisateur',
                        'userEmail'     => (string)$user['email_user'],
                        'resetUrl'      => $resetUrl,
                        'ttlMinutes'    => 60,
                        'etablissement' => $etabCfg
                    ]
                );

                AuditLogger::log(
                    AuditLogger::ACTION_UPDATE,
                    'auth',
                    (string)$user['code_user'],
                    "Demande de réinitialisation du mot de passe pour {$user['email_user']}",
                    ['email' => $user['email_user'], 'mail_sent' => !empty($mailRes['status'])],
                    (int)$user['id_user'],
                    (string)$user['code_user'],
                    $nomComplet
                );

                if (empty($mailRes['status'])) {
                    error_log("UserController::motDePasseOublie mail error: " . ($mailRes['message'] ?? 'Erreur SMTP'));
                    $this->error("Impossible d'envoyer l'e-mail de réinitialisation pour le moment. Veuillez contacter l'administrateur.");
                    return;
                }
            }
        }

        $this->success($genericSuccessMsg);
    }

    /**
     * Réinitialisation effective du mot de passe via le lien envoyé par e-mail
     */
    public function reinitialiserMotDePasse($tokenParam = null)
    {
        $this->requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $token = trim((string)($tokenParam ?? ($_GET['token'] ?? '')));
            $resetRow = ($token !== '') ? $this->model->findValidPasswordReset($token) : null;

            $this->loadView('../views/users/reset_password.php', [
                'token'         => $token,
                'isValidToken'  => !empty($resetRow),
                'resetUserName' => $resetRow ? trim(($resetRow['nom_user'] ?? '') . ' ' . ($resetRow['prenom_user'] ?? '')) : '',
                'etablissement' => $this->getEtablissementConfig()
            ]);
            return;
        }

        $this->requirePost(true);

        // Protection Rate Limiting anti-brute-force sur la réinitialisation (5 tentatives / 15 min par IP)
        $this->enforceRateLimit('reset_password', 5, 900, null, true, 'Trop de tentatives de réinitialisation. Veuillez réessayer dans {wait}.');

        $token = trim((string)($this->post('token') ?: ($tokenParam ?? '')));
        $newPassword = (string)$this->post('new_password');
        $confirmPassword = (string)$this->post('confirm_password');

        if ($token === '') {
            $this->error('Lien de réinitialisation invalide ou manquant.');
            return;
        }

        if ($newPassword === '' || $confirmPassword === '') {
            $this->error('Veuillez saisir et confirmer votre nouveau mot de passe.');
            return;
        }

        if (strlen($newPassword) < 6) {
            $this->error('Le nouveau mot de passe doit contenir au moins 6 caractères.');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->error('La confirmation du mot de passe ne correspond pas.');
            return;
        }

        $resetRow = $this->model->findValidPasswordReset($token);
        if (!$resetRow) {
            $this->error('Ce lien de réinitialisation est invalide, déjà utilisé ou a expiré. Veuillez effectuer une nouvelle demande.');
            return;
        }

        $userId = (int)$resetRow['id_user'];
        $userCode = (string)$resetRow['user_code'];
        $nomComplet = trim(($resetRow['nom_user'] ?? '') . ' ' . ($resetRow['prenom_user'] ?? ''));

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($this->model->updatePassword($hash, $userId)) {
            $this->model->markPasswordResetUsed((int)$resetRow['id_reset'], $userCode);
            $this->clearRateLimit('reset_password', null);
            $this->clearRateLimit('login_ip', null);
            $this->clearRateLimit('login', mb_strtolower((string)($resetRow['email_user'] ?? '')));
            $this->clearRateLimit('login_account', $userCode, false);

            AuditLogger::log(
                AuditLogger::ACTION_UPDATE,
                'auth',
                $userCode,
                "Mot de passe réinitialisé avec succès via le lien de récupération ({$resetRow['email_user']})",
                ['id_user' => $userId, 'email' => $resetRow['email_user']],
                $userId,
                $userCode,
                $nomComplet
            );

            $this->success('Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter.', [
                'redirect' => url('user/connexion')
            ]);
        } else {
            $this->error('Une erreur technique est survenue lors de la mise à jour du mot de passe.');
        }
    }

    public function logout()
    {
        $this->decon();
    }

    public function editPassword()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->loadView('../views/users/editPassword.php', [
                'user' => Auth::user() ?: []
            ]);
            return;
        }

        $this->requirePost(false);

        $oldPassword = $this->post('old_password') ?: $this->post('password');
        $newPassword = $this->post('new_password') ?: $this->post('newPassword');
        $confirmPassword = $this->post('confirm_password') ?: $this->post('confirmPassword');

        if (empty($oldPassword) || empty($newPassword)) {
            $this->error('Veuillez renseigner l\'ancien et le nouveau mot de passe !');
            return;
        }

        if (!empty($confirmPassword) && $newPassword !== $confirmPassword) {
            $this->error('La confirmation du nouveau mot de passe ne correspond pas !');
            return;
        }

        if (strlen($newPassword) < 4) {
            $this->error('Le nouveau mot de passe doit contenir au moins 4 caractères !');
            return;
        }

        $userId = Auth::id() ?: 0;
        $user = $this->model->getById((int)$userId);

        if (!$user) {
            $this->error('Utilisateur introuvable !');
            return;
        }

        // Protection Rate Limiting sur la modification du mot de passe (5 échecs / 15 min par utilisateur)
        $this->enforceRateLimit('edit_password', 5, 900, (string)$userId, false, 'Trop de tentatives de modification de mot de passe. Veuillez réessayer dans {wait}.');

        if (!password_verify($oldPassword, $user['password_user'] ?? '')) {
            $this->hitRateLimit('edit_password', 5, 900, (string)$userId);
            $this->error('Votre mot de passe actuel est incorrect !');
            return;
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($this->model->updatePassword($hash, (int)$userId)) {
            $this->clearRateLimit('edit_password', (string)$userId);
            $this->logAction(
                AuditLogger::ACTION_UPDATE,
                'user',
                $user['code_user'] ?? (string)$userId,
                "Modification du mot de passe de l'utilisateur " . trim(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? '')),
                ['id_user' => $userId, 'code_user' => $user['code_user'] ?? null]
            );
            $this->success('Votre mot de passe a été modifié avec succès !');
        } else {
            $this->error('Erreur lors de la mise à jour du mot de passe.');
        }
    }

    /**
     * Génère et imprime la fiche officielle d'un utilisateur en PDF
     */
    public function imprimerFichePdf($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_USERS);
        require_once __DIR__ . '/../../core/PdfService.php';

        try {
            $userId = $this->validator->decrypter($details);
            if (!$userId || !is_numeric($userId)) {
                $userId = is_numeric($details) ? (int)$details : 0;
            }

            $userProfile = $this->model->getById((int)$userId);
            if (!$userProfile) {
                redirect('user/list');
            }

            $userRoles = $this->model->getUserRoles($userProfile['code_user'] ?? '');
            $primaryRole = !empty($userRoles) ? $userRoles[0] : null;

            $dataView = [
                'user' => $userProfile,
                'role' => $primaryRole,
                'userRoles' => $userRoles,
                'etablissement' => $this->getEtablissementConfig()
            ];

            $html = PdfService::renderTemplate('fiche_utilisateur.php', $dataView);
            if (isset($_GET['html'])) {
                echo $html;
                return;
            }

            $nomClean = preg_replace('/[^A-Za-z0-9_\-]/', '_', trim(($userProfile['nom_user'] ?? '') . '_' . ($userProfile['prenom_user'] ?? '')));
            $filename = 'Fiche_Utilisateur_' . ($nomClean ?: 'Compte') . '_' . date('Ymd_His') . '.pdf';

            // Journalisation de l'impression
            $this->logAction(
                AuditLogger::ACTION_PRINT,
                'user',
                $userProfile['code_user'] ?? (string)$userId,
                "Impression de la fiche de l'utilisateur " . trim(($userProfile['nom_user'] ?? '') . ' ' . ($userProfile['prenom_user'] ?? ''))
            );

            PdfService::generate($html, $filename, [
                'orientation' => 'P',
                'format' => 'A4',
                'title' => 'Fiche Utilisateur - ' . trim(($userProfile['nom_user'] ?? '') . ' ' . ($userProfile['prenom_user'] ?? '')),
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_top' => 8,
                'margin_bottom' => 8
            ]);
        } catch (Exception $e) {
            error_log("UserController::imprimerFichePdf error: " . $e->getMessage());
            redirect('user/list');
        }
    }
}