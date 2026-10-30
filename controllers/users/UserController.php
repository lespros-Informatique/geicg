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
                        'loginUrl'     => RACINE . 'user/connexion'
                    ]
                );
            }

            $idDisplay = $email ?: ($telephone ?: $nom);
            $this->success("Utilisateur créé avec succès ! Identifiant : <strong>{$idDisplay}</strong> | Mot de passe généré : <strong style='color:#15803D;'>{$rawPassword}</strong>", [
                'redirect' => RACINE . 'user/list',
                'password' => $rawPassword
            ]);
        } else {
            $this->error('Erreur lors de la création de l\'utilisateur.');
        }
    }

    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Location: ' . RACINE . 'user/list');
            exit;
        }

        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_USERS);
        $id = (int)$this->post('id_user');
        if (!$id) { $this->error('Identifiant invalide', RACINE . 'user/list'); return; }

        $user = $this->model->getById($id);
        if (!$user) { $this->error('Utilisateur introuvable', RACINE . 'user/list'); return; }

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

            $this->success('Utilisateur et permissions par rôle mis à jour avec succès !', RACINE . 'user/list');
        } else {
            $this->error('Erreur lors de la modification de l\'utilisateur.');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_USERS);
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
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
            $userId = $this->validator->decrypter($details);
            $userProfile = $this->model->getById($userId);
            if (!$userProfile) {
                header('Location: ' . RACINE . 'user/list');
                exit();
            }
            $userRoles = $this->model->getUserRoles($userProfile['code_user']);
            $primaryRole = !empty($userRoles) ? $userRoles[0] : null;
            $encryptedId = $this->validator->crypter($userId);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'user/list');
            exit();
        }

        $this->loadView('../views/users/details.php', [
            'user' => $userProfile,
            'role' => $primaryRole,
            'userRoles' => $userRoles,
            'encryptedId' => $encryptedId
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
                header('Location: ' . RACINE . 'user/list');
                exit();
            }
            $userRoles = $this->model->getUserRoles($userProfile['code_user']);
            $userRoleCodes = $this->model->getUserRoleCodes($userProfile['code_user']);
            $primaryRole = !empty($userRoles) ? $userRoles[0] : null;
            $roles = (new ModelRole())->getAll();
            $fonctions = (new ModelFonction())->getAll();
            $allAnneesList = (new ModelAnnee())->getAll();
            $userAnneeAcces = $this->model->getUserAnneeAcces($userProfile['code_user']);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'user/list');
            exit();
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
        $notEmpty = Validator::validateRequiredFields($_POST);

        if ($notEmpty === true) {
            $login = trim($this->post('login'));
            $password = $this->post('password');

            // Recherche utilisateur via ModelUser
            $user = $this->model->findUserByLogin($login);

            if ($user && password_verify($password, $user['password_user'] ?? '')) {
                if ($user['statut_user'] === STATUTS::ACTIF) {
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
                        'prenom' => $user['prenom_user'] ?? '',
                        'email' => $user['email_user'] ?? '',
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

                    $this->success($welcomeMsg, ['redirect' => RACINE]);
                    return;
                } else {
                    $this->error('Ce compte utilisateur est inactif ou suspendu. Veuillez contacter l\'administrateur.');
                    return;
                }
            }

            $this->error('Identifiant ou mot de passe incorrect.');
            return;
        } else {
            $this->error('Veuillez renseigner tous les champs !');
        }
    }

    public function decon()
    {
        Auth::logout();
        header('Location: ' . RACINE . 'user/connexion');
        exit();
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

        if (!password_verify($oldPassword, $user['password_user'] ?? '')) {
            $this->error('Votre mot de passe actuel est incorrect !');
            return;
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($this->model->updatePassword($hash, (int)$userId)) {
            $this->success('Votre mot de passe a été modifié avec succès !');
        } else {
            $this->error('Erreur lors de la mise à jour du mot de passe.');
        }
    }
}