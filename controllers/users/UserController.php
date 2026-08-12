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
        $users = $this->model->getAll();
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
                $email = $this->post('email') ?: null;
                $password = !empty($this->post('password')) ? Validator::hashPassword($this->post('password')) : null;
                $statut = $this->post('actif') ?: 'actif';

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
                    $roleCode = $this->post('role_code') ?: 'ROLE-ADMIN';
                    $this->model->setUserRole($code_user, $roleCode);
                    $this->success('Utilisateur ajouté avec succès!');
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
                    'nom_user' => $this->post('nom'),
                    'prenom_user' => $this->post('prenom') ?? '',
                    'telephone_user' => $this->post('telephone'),
                    'email_user' => $this->post('email') ?: null,
                    'statut_user' => $statut,
                    'updated_at_user' => date('Y-m-d H:i:s')
                ];

                if ($this->model->update($data)) {
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

    public function decon()
    {
        $this->requireAuth();
        $this->unsetSession();
        $this->loadView('../views/users/connexion.php');
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
                    Validator::saveSesion(USERS_AUTH, [
                        'id_user' => $user['id_user'],
                        'code_user' => $user['code_user'],
                        'nom' => $user['nom_user'],
                        'email' => $user['email_user'] ?? '',
                        'tel' => $user['telephone_user'] ?? ''
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
        $this->requirePost();
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields($_POST);

        if ($notEmpty === true) {
            $user = $this->model->getById($_SESSION[USERS_AUTH]['id_user'] ?? 0);
            if (isset($user) && !empty($user) && password_verify($this->post('password'), $user['password_user'])) {
                $mdp = Validator::hashPassword($this->post('newPassword'));
                if ($this->model->updatePassword($mdp, $user['id_user'])) {
                    $this->success('Mot de passe modifié avec succès!');
                } else {
                    $this->error('Erreur lors de la modification du mot de passe!');
                }
            } else {
                $this->error('Ancien mot de passe incorrect!');
            }
        } else {
            $this->error('Veuillez renseigner tous les champs!');
        }
    }
}