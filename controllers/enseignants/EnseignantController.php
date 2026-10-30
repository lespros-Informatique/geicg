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
        $this->loadView('../views/enseignants/list.php');
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
        $this->json(['data' => $data]);
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

        $codeUser = $this->validator->generateCode('users', 'code_user', 'ENS-', 8);
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