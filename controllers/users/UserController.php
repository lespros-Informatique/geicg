<?php
class UserController extends BaseController
{
    use PressingAware;

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
        $users = $this->model->getAll();
        $pressingCode = $this->getCurrentPressingCode();

        if ($pressingCode !== null) {
            $pressingUserCodes = [];
            try {
                $sql = "SELECT user_code FROM " . TABLES::USERS_PRESSINGS . " WHERE pressing_code = ? AND statut_user_pressing = 'actif'";
                $stmt = $this->model->getCon()->prepare($sql);
                $stmt->execute([$pressingCode]);
                $pressingUserCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (Exception $e) {
                $pressingUserCodes = [];
            }

            $users = array_filter($users, function($u) use ($pressingUserCodes) {
                return in_array($u['code_user'], $pressingUserCodes, true);
            });
        }

        $data = [];

        foreach ($users as $u) {
            $idCrypte = $this->validator->crypter($u['id_user']);
            $role = $this->model->getUserRole($u['code_user']);
            $data[] = [
                'code' => $u['code_user'],
                'nom' => $u['nom_user'],
                'prenom' => $u['prenom_user'] ?? '',
                'telephone' => $u['telephone_user'] ?? '',
                'role' => $role ? $role['libelle_role'] : '-',
                'role_code' => $role ? ($role['code_role'] ?? '') : '',
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
        $notEmpty = Validator::validateRequiredFields(['nom' => $_POST['nom'] ?? '', 'telephone' => $_POST['telephone'] ?? '']);

        if ($notEmpty === true) {
            if (!Validator::validNumber($this->post('telephone'), 10)) {
                $this->error('Le numéro de téléphone doit contenir 10 chiffres!');
            } elseif ($this->validator->getByElement(TABLES::USERS, 'telephone_user', $this->post('telephone'))) {
                $this->error('Ce numéro de téléphone existe déjà!');
            } else {
                $code_user = $this->validator->generateCode(TABLES::USERS, 'code_user', 'US-', 6);
                $email = $this->post('email') ?: ($this->post('email_user') ?: null);
                $rawPassword = !empty($this->post('password')) ? $this->post('password') : '12345';
                $password = Validator::hashPassword($rawPassword);
                $statut = 'actif';

                $data = [
                    'code_user' => $code_user,
                    'nom_user' => $this->post('nom'),
                    'prenom_user' => $this->post('prenom') ?? '',
                    'telephone_user' => $this->post('telephone'),
                    'email_user' => $email,
                    'password_user' => $password,
                    'statut_user' => $statut,
                    'created_at_user' => date('Y-m-d H:i:s')
                ];

                if ($this->model->create($data)) {
                    $roleCode = $this->post('role_code') ?: 'ROLE-PRO';
                    $this->model->setUserRole($code_user, $roleCode);

                    $pressingCode = $this->getCurrentPressingCode();
                    if ($pressingCode) {
                        // Contrôle du nombre maximal d'utilisateurs selon le forfait B2B du pressing (100% dynamique depuis la BDD)
                        $stmtSub = $this->model->getCon()->prepare("
                            SELECT f.code_forfait, f.libelle_forfait, f.nb_comptes_max
                            FROM abonnements_pressings ab
                            JOIN forfaits f ON ab.forfait_code = f.code_forfait
                            WHERE ab.pressing_code = ? AND ab.statut_abonnement_pressing = 'actif'
                            ORDER BY ab.id_abonnement_pressing DESC LIMIT 1
                        ");
                        $stmtSub->execute([$pressingCode]);
                        $sub = $stmtSub->fetch(PDO::FETCH_ASSOC);

                        if ($sub) {
                            $maxUsersAllowed = (int)($sub['nb_comptes_max'] ?? 0);
                            if ($maxUsersAllowed > 0) {
                                $stmtUsersCount = $this->model->getCon()->prepare("
                                    SELECT COUNT(*) FROM " . TABLES::USERS_PRESSINGS . " WHERE pressing_code = ? AND statut_user_pressing = 'actif'
                                ");
                                $stmtUsersCount->execute([$pressingCode]);
                                $currentUsersCount = (int)$stmtUsersCount->fetchColumn();

                                if ($currentUsersCount >= $maxUsersAllowed) {
                                    $this->error("Votre forfait actuel (" . $sub['libelle_forfait'] . ") est limité à $maxUsersAllowed compte(s) utilisateur(s). Passez à la formule supérieure pour ajouter du personnel !");
                                    return;
                                }
                            }
                        }
                    }

                    if ($pressingCode && in_array($roleCode, ['ROLE-PRO', 'ROLE-GEST', 'ROLE-LIV'])) {
                        try {
                            $userPressingCode = $this->validator->generateCode(TABLES::USERS_PRESSINGS, 'code_user_pressing', 'USP-', 6);
                            $stmtCheckUp = $this->model->getCon()->prepare("SELECT id_user_pressing FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ? AND pressing_code = ? LIMIT 1");
                            $stmtCheckUp->execute([$code_user, $pressingCode]);
                            if (!$stmtCheckUp->fetch()) {
                                $stmtUp = $this->model->getCon()->prepare("
                                    INSERT INTO " . TABLES::USERS_PRESSINGS . " (code_user_pressing, user_code, pressing_code, role_code, statut_user_pressing, created_at_user_pressing)
                                    VALUES (?, ?, ?, ?, 'actif', NOW())
                                ");
                                $stmtUp->execute([$userPressingCode, $code_user, $pressingCode, $roleCode]);
                            }

                            if ($roleCode === 'ROLE-LIV') {
                                $codeLivreur = $this->validator->generateCode(TABLES::LIVREURS, 'code_livreur', 'LIV-', 6);
                                $stmtLiv = $this->model->getCon()->prepare("
                                    INSERT INTO " . TABLES::LIVREURS . " (code_livreur, pressing_code, user_code, nom_livreur, prenom_livreur, telephone_livreur, statut_livreur, created_at_livreur)
                                    VALUES (?, ?, ?, ?, ?, ?, 'actif', NOW())
                                ");
                                $stmtLiv->execute([$codeLivreur, $pressingCode, $code_user, $this->post('nom'), $this->post('prenom') ?? '', $this->post('telephone')]);
                            }
                        } catch (Exception $e) {
                            error_log("Erreur de liaison pressing dans UserController::add : " . $e->getMessage());
                        }
                    }

                    $this->success('Utilisateur ajouté avec succès ! (Mot de passe par défaut : 12345)');
                } else {
                    $this->error('Erreur lors de l\'ajout de l\'utilisateur');
                }
            }
        } else {
            $this->error('Veuillez renseigner tous les champs!');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['nom' => $_POST['nom'] ?? '', 'telephone' => $_POST['telephone'] ?? '', 'id_user' => $_POST['id_user'] ?? '']);

        if ($notEmpty === true) {
            if (!Validator::validNumber($this->post('telephone'), 10)) {
                $this->error('Le numéro de téléphone doit contenir 10 chiffres!');
            } elseif ($this->validator->_verif(TABLES::USERS, 'telephone_user', $this->post('telephone'), 'id_user', $this->post('id_user'))) {
                $this->error('Ce numéro de téléphone est déjà utilisé par un autre utilisateur!');
            } else {
                $actif = $this->post('actif');
                if ($actif === 'actif' || $actif === 1 || $actif === '1') {
                    $statut = 'actif';
                } else {
                    $statut = 'inactif';
                }
                $id = (int) $this->post('id_user');
                
                $data = [
                    'id_user' => $id,
                    'nom_user' => $this->post('nom'),
                    'prenom_user' => $this->post('prenom') ?? '',
                    'telephone_user' => $this->post('telephone'),
                    'email_user' => $this->post('email') ?: null,
                    'statut_user' => $statut,
                    'updated_at_user' => date('Y-m-d H:i:s')
                ];

                if ($this->model->update($data, $id)) {
                    $roleCode = $this->post('role_code');
                    if ($roleCode) {
                        $user = $this->model->getById($id);
                        if ($user) {
                            $this->model->setUserRole($user['code_user'], $roleCode);
                        }
                    }
                    $this->success('Utilisateur modifié avec succès!');
                } else {
                    $this->error('Erreur lors de la modification');
                }
            }
        } else {
            $this->error('Veuillez renseigner tous les champs!');
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
                $this->error('Erreur');
            }
        } else {
            $this->error('Utilisateur introuvable!');
        }
    }

    public function setRole()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if (!$this->isSuperAdmin()) {
            $this->error('Accès refusé', 403);
            return;
        }

        $id = (int) $this->post('id_user');
        $roleCode = $this->post('role_code');

        if (!$id || !$roleCode) {
            $this->error('Identifiant et rôle requis');
            return;
        }

        $user = $this->model->getById($id);
        if (!$user) {
            $this->error('Utilisateur introuvable');
            return;
        }

        $allowedRoles = [ROLES::SUPER_ADMIN, ROLES::PRESSING, ROLES::LIVREUR];
        if (!in_array($roleCode, $allowedRoles, true)) {
            $this->error('Rôle invalide');
            return;
        }

        if ($this->model->setUserRole($user['code_user'], $roleCode)) {
            $this->success('Rôle attribué avec succès');
        } else {
            $this->error('Erreur lors de l\'attribution du rôle');
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

    public function checkPhone()
    {
        $this->requireAuth();
        $phone = trim($_POST['telephone'] ?? ($_GET['telephone'] ?? ''));
        if (empty($phone)) {
            $this->error('Numéro de téléphone requis');
            return;
        }

        $exists = $this->validator->verif(TABLES::USERS, 'telephone_user', $phone);
        if ($exists) {
            $this->error('Ce numéro de téléphone est déjà attribué à un compte !');
        } else {
            $this->success('Numéro disponible');
        }
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/users/edit.php', ['user' => [], 'role' => []]);
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
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'user/list');
            exit();
        }

        $this->loadView('../views/users/edit.php', [
            'user' => $userProfile,
            'role' => $role
        ]);
    }

    public function profil()
    {
        $this->requireAuth();
        $auth = $_SESSION[USERS_AUTH] ?? null;
        $userId = $auth['id_user'] ?? 0;
        $editId = $this->validator->crypter($userId);
        $this->loadView('../views/users/profil.php', [
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
            $login = $this->post('login');
            $user = $this->validator->getByElement(TABLES::USERS, 'telephone_user', $login);
            if (!$user) {
                $user = $this->validator->getByElement(TABLES::USERS, 'email_user', $login);
            }

            if (isset($user) && !empty($user) && password_verify($this->post('password'), $user['password_user'] ?? '')) {
                if ($user['statut_user'] == STATUTS::USERS[0]) {
                    $roleCode = $this->model->getUserRole($user['code_user']);
                    $roleCode = $roleCode ? ($roleCode['code_role'] ?? '') : '';

                    Validator::saveSesion(USERS_AUTH, [
                        'id_user' => $user['id_user'],
                        'code_user' => $user['code_user'],
                        'nom' => $user['nom_user'],
                        'email' => $user['email_user'] ?? '',
                        'tel' => $user['telephone_user'] ?? '',
                        'role_code' => $roleCode
                    ]);

                    $this->success('Bienvenue sur Lavex Admin!');
                } else {
                    $this->error('Ce compte utilisateur est inactif'.$user['nom_user']);
                }
            } else {
                $this->error('Identifiants incorrects. Veuillez vérifier votre téléphone/email et mot de passe.');
            }
        } else {
            $this->error('Veuillez renseigner tous les champs!');
        }
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
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $user = null;
        if ($userId) {
            $user = $this->model->getById((int)$userId);
        }
        if (!$user && $userCode) {
            $user = $this->validator->getByElement(TABLES::USERS, 'code_user', $userCode);
            if ($user) {
                $userId = (int)$user['id_user'];
            }
        }

        if (!$user) {
            $this->error('Utilisateur introuvable !');
            return;
        }

        if (!password_verify($oldPassword, $user['password_user'] ?? '')) {
            $this->error('Votre mot de passe actuel est incorrect !');
            return;
        }

        $hash = Validator::hashPassword($newPassword);
        if ($this->model->updatePassword($hash, (int)$userId)) {
            $this->success('Votre mot de passe a été modifié avec succès !');
        } else {
            $this->error('Erreur lors de la mise à jour du mot de passe');
        }
    }
}