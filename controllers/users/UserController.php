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
        $this->loadView('../views/users/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $sql = "SELECT u.*, 
                       r.libelle_role, 
                       r.code_role,
                       f.libelle_fonction
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_code = u.code_user
                LEFT JOIN roles r ON r.code_role = ur.role_code
                LEFT JOIN fonctions f ON f.code_fonction = u.fonction_code
                ORDER BY u.id_user DESC";
        $users = $this->model->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($users as $u) {
            $idCrypte = $this->validator->crypter($u['id_user']);
            $data[] = [
                'code' => $u['code_user'],
                'nom' => $u['nom_user'],
                'prenom' => $u['prenom_user'] ?? '',
                'email' => $u['email_user'] ?? '',
                'telephone' => $u['telephone_user'] ?? '',
                'fonction' => $u['libelle_fonction'] ?? '-',
                'role' => $u['libelle_role'] ?? 'Non attribué',
                'role_code' => $u['code_role'] ?? '',
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

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $telephone = Validator::cleanPhone($_POST['telephone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roleCode = $_POST['role_code'] ?? 'ROLE_SCOLARITE';
        $fonctionCode = $_POST['fonction_code'] ?? null;
        $rawPassword = !empty($_POST['password']) ? trim($_POST['password']) : '123456';

        if (empty($nom)) {
            $this->error('Le nom de l\'utilisateur est obligatoire !');
            return;
        }

        if (!empty($email)) {
            $stmtCheckEmail = $this->model->getCon()->prepare("SELECT id_user FROM users WHERE email_user = ?");
            $stmtCheckEmail->execute([$email]);
            if ($stmtCheckEmail->fetch()) {
                $this->error('Cette adresse email est déjà attribuée à un compte !');
                return;
            }
        }

        if (!empty($telephone)) {
            $stmtCheckTel = $this->model->getCon()->prepare("SELECT id_user FROM users WHERE telephone_user = ?");
            $stmtCheckTel->execute([$telephone]);
            if ($stmtCheckTel->fetch()) {
                $this->error('Ce numéro de téléphone est déjà attribué à un compte !');
                return;
            }
        }

        $code_user = $this->validator->generateCode('users', 'code_user', 'USR-', 8);
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);
        $etabCode = '5454544456';

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
            'statut_user' => 'actif',
            'created_at_user' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $createP = isset($_POST['create_permission']) ? 1 : 0;
            $editP = isset($_POST['edit_permission']) ? 1 : 0;
            $showP = isset($_POST['show_permission']) ? 1 : 1;
            $deleteP = isset($_POST['delete_permission']) ? 1 : 0;

            $this->model->setUserRole($code_user, $roleCode, $createP, $editP, $showP, $deleteP);
            $this->success('Utilisateur créé avec succès ! (Mot de passe initial : ' . $rawPassword . ')');
        } else {
            $this->error('Erreur lors de la création de l\'utilisateur.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_user');
        if (!$id) { $this->error('Identifiant invalide'); return; }

        $user = $this->model->getById($id);
        if (!$user) { $this->error('Utilisateur introuvable'); return; }

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $telephone = Validator::cleanPhone($_POST['telephone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roleCode = $_POST['role_code'] ?? '';
        $fonctionCode = $_POST['fonction_code'] ?? null;
        $statut = ($_POST['actif'] ?? '') === '0' || ($_POST['actif'] ?? '') === 'inactif' ? 'inactif' : 'actif';

        if (empty($nom)) {
            $this->error('Le nom est obligatoire !');
            return;
        }

        if (!empty($email)) {
            $stmtCheck = $this->model->getCon()->prepare("SELECT id_user FROM users WHERE email_user = ? AND id_user != ?");
            $stmtCheck->execute([$email, $id]);
            if ($stmtCheck->fetch()) {
                $this->error('Cette adresse email est déjà utilisée par un autre compte.');
                return;
            }
        }

        if (!empty($telephone)) {
            $stmtCheck = $this->model->getCon()->prepare("SELECT id_user FROM users WHERE telephone_user = ? AND id_user != ?");
            $stmtCheck->execute([$telephone, $id]);
            if ($stmtCheck->fetch()) {
                $this->error('Ce numéro de téléphone est déjà utilisé par un autre compte.');
                return;
            }
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

        if ($this->model->update($data, $id)) {
            if (!empty($roleCode)) {
                $createP = isset($_POST['create_permission']) ? 1 : 0;
                $editP = isset($_POST['edit_permission']) ? 1 : 0;
                $showP = isset($_POST['show_permission']) ? 1 : 1;
                $deleteP = isset($_POST['delete_permission']) ? 1 : 0;

                $this->model->setUserRole($user['code_user'], $roleCode, $createP, $editP, $showP, $deleteP);
            }
            $this->success('Utilisateur et permissions mis à jour avec succès !');
        } else {
            $this->error('Erreur lors de la modification de l\'utilisateur.');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
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
        try {
            $userId = $this->validator->decrypter($details);
            $userProfile = $this->model->getById($userId);
            if (!$userProfile) {
                header('Location: ' . RACINE . 'user/list');
                exit();
            }
            $role = $this->model->getUserRole($userProfile['code_user']);
            $encryptedId = $this->validator->crypter($userId);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'user/list');
            exit();
        }

        $this->loadView('../views/users/details.php', [
            'user' => $userProfile,
            'role' => $role,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $roles = (new ModelRole())->getAll();
        $fonctions = (new ModelFonction())->getAll();
        $this->loadView('../views/users/edit.php', [
            'user' => [],
            'role' => [],
            'roles' => $roles,
            'fonctions' => $fonctions
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $decryptedId = $this->validator->decrypter($details);
            $userProfile = $this->model->getById($decryptedId);

            if (!$userProfile) {
                header('Location: ' . RACINE . 'user/list');
                exit();
            }
            $role = $this->model->getUserRole($userProfile['code_user']);
            $roles = (new ModelRole())->getAll();
            $fonctions = (new ModelFonction())->getAll();
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'user/list');
            exit();
        }

        $this->loadView('../views/users/edit.php', [
            'user' => $userProfile,
            'role' => $role,
            'roles' => $roles,
            'fonctions' => $fonctions
        ]);
    }

    public function profil()
    {
        $this->requireAuth();
        $auth = $_SESSION[USERS_AUTH] ?? null;
        $userId = $auth['id_user'] ?? 0;
        $editId = $this->validator->crypter($userId);
        $userProfile = $this->model->getById((int)$userId);
        $role = $this->model->getUserRole($userProfile['code_user'] ?? '');
        $this->loadView('../views/users/profil.php', [
            'user' => $userProfile,
            'role' => $role,
            'editId' => $editId
        ]);
    }

    public function connexion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->loadView('../views/users/connexion.php');
            return;
        }

        $this->requirePost(false);
        $notEmpty = Validator::validateRequiredFields($_POST);

        if ($notEmpty === true) {
            $login = trim($this->post('login'));
            $password = $this->post('password');

            // 1. Recherche dans la table USERS
            $user = $this->validator->getByElement('users', 'telephone_user', $login);
            if (!$user) {
                $user = $this->validator->getByElement('users', 'email_user', $login);
            }

            if (isset($user) && !empty($user) && password_verify($password, $user['password_user'] ?? '')) {
                if ($user['statut_user'] === 'actif') {
                    $userRole = $this->model->getUserRole($user['code_user']);
                    $roleCode = $userRole ? ($userRole['role_code'] ?? 'ROLE_USER') : 'ROLE_USER';

                    // Vérifier si lié à un profil enseignant
                    $stmtEns = $this->model->getCon()->prepare("
                        SELECT * FROM enseignants WHERE user_code = ? AND statut_enseignant = 'actif' LIMIT 1
                    ");
                    $stmtEns->execute([$user['code_user']]);
                    $enseignantProfile = $stmtEns->fetch(PDO::FETCH_ASSOC);

                    $sessionData = [
                        'id_user' => $user['id_user'],
                        'code_user' => $user['code_user'],
                        'nom' => $user['nom_user'],
                        'prenom' => $user['prenom_user'] ?? '',
                        'email' => $user['email_user'] ?? '',
                        'tel' => $user['telephone_user'] ?? '',
                        'role_code' => $roleCode,
                        'is_enseignant' => !empty($enseignantProfile),
                        'code_enseignant' => $enseignantProfile['code_enseignant'] ?? null,
                        'grade_enseignant' => $enseignantProfile['grade_enseignant'] ?? null,
                        'type_contrat' => $enseignantProfile['type_contrat'] ?? null,
                        'permissions' => [
                            'create' => (int)($userRole['create_permission'] ?? 1),
                            'edit' => (int)($userRole['edit_permission'] ?? 1),
                            'show' => (int)($userRole['show_permission'] ?? 1),
                            'delete' => (int)($userRole['delete_permission'] ?? 0)
                        ]
                    ];

                    Validator::saveSesion(USERS_AUTH, $sessionData);
                    $welcomeMsg = !empty($enseignantProfile) 
                        ? 'Bienvenue Professeur ' . htmlspecialchars($user['nom_user']) . ' !'
                        : 'Connexion réussie ! Bienvenue sur GEICG.';

                    $this->success($welcomeMsg);
                    return;
                } else {
                    $this->error('Ce compte utilisateur est inactif ou suspendu. Veuillez contacter l\'administrateur.');
                    return;
                }
            }

            // 2. Si pas trouvé dans USERS, recherche directe dans la table ENSEIGNANTS
            $stmtEnsLogin = $this->model->getCon()->prepare("
                SELECT * FROM enseignants 
                WHERE (email_enseignant = ? OR telephone_enseignant = ?)
                LIMIT 1
            ");
            $stmtEnsLogin->execute([$login, $login]);
            $ens = $stmtEnsLogin->fetch(PDO::FETCH_ASSOC);

            if ($ens && !empty($ens['password_enseignant']) && password_verify($password, $ens['password_enseignant'])) {
                if ($ens['statut_enseignant'] === 'actif') {
                    $sessionData = [
                        'id_user' => $ens['id_enseignant'],
                        'code_user' => $ens['code_enseignant'],
                        'nom' => $ens['nom_enseignant'],
                        'prenom' => $ens['prenom_enseignant'] ?? '',
                        'email' => $ens['email_enseignant'] ?? '',
                        'tel' => $ens['telephone_enseignant'] ?? '',
                        'role_code' => 'ROLE_ENSEIGNANT',
                        'is_enseignant' => true,
                        'code_enseignant' => $ens['code_enseignant'],
                        'grade_enseignant' => $ens['grade_enseignant'] ?? 'Enseignant',
                        'type_contrat' => $ens['type_contrat'] ?? 'permanent',
                        'taux_horaire' => (float)($ens['taux_horaire'] ?? 0),
                        'permissions' => [
                            'create' => 1,
                            'edit' => 1,
                            'show' => 1,
                            'delete' => 0
                        ]
                    ];

                    Validator::saveSesion(USERS_AUTH, $sessionData);
                    $this->success('Bienvenue Professeur ' . htmlspecialchars($ens['nom_enseignant']) . ' !');
                    return;
                } else {
                    $this->error('Ce compte enseignant est inactif ou suspendu. Veuillez contacter l\'administration.');
                    return;
                }
            }

            $this->error('Identifiants incorrects. Veuillez vérifier votre adresse email / téléphone et mot de passe.');
        } else {
            $this->error('Veuillez renseigner tous les champs !');
        }
    }

    public function decon()
    {
        $this->unsetSession();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            @session_destroy();
        }
        $_SESSION = [];

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
                'user' => $_SESSION[USERS_AUTH] ?? []
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

        $userId = $_SESSION[USERS_AUTH]['id_user'] ?? 0;
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