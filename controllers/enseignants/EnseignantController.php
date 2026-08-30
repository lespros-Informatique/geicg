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
        $this->loadView('../views/enseignants/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
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
        $currentUserCode = $_SESSION[USERS_AUTH]['code_user'] ?? '5wBEh2OfI00frxk8ITPf';
        $etabCode = '5454544456';
        $data = $_POST;
        unset($data['csrf_token']);

        $db = $this->model->getCon();
        $mode = trim($data['mode_creation'] ?? 'nouveau'); // 'nouveau' ou 'existant'

        // 1. Cas : Rattachement d'un employé / utilisateur existant
        if ($mode === 'existant' && !empty($data['user_code_existant'])) {
            $codeUser = trim($data['user_code_existant']);
            
            // Vérifier si cet utilisateur est déjà enseignant
            $stmtCheck = $db->prepare("SELECT id_enseignant FROM enseignants WHERE code_enseignant = ?");
            $stmtCheck->execute([$codeUser]);
            if ($stmtCheck->fetch()) {
                $this->error('Cet utilisateur est déjà enregistré dans le corps enseignant.');
                return;
            }

            // Vérifier que l'utilisateur existe bien dans `users`
            $stmtUser = $db->prepare("SELECT * FROM users WHERE code_user = ?");
            $stmtUser->execute([$codeUser]);
            $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
            if (!$userRow) {
                $this->error('Utilisateur introuvable.');
                return;
            }

            // Assigner le rôle ROLE_ENSEIGNANT dans user_roles s'il ne l'a pas déjà
            $stmtRole = $db->prepare("
                INSERT IGNORE INTO user_roles (user_code, role_code, create_permission, edit_permission, show_permission, delete_permission)
                VALUES (?, 'ROLE_ENSEIGNANT', 1, 1, 1, 0)
            ");
            $stmtRole->execute([$codeUser]);

            // Créer la fiche dans enseignants
            $stmtEns = $db->prepare("
                INSERT INTO enseignants (
                    code_enseignant, grade_enseignant, type_contrat, numero_autorisation,
                    etablissement_code, user_code, statut_enseignant, created_at_enseignant
                ) VALUES (?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $success = $stmtEns->execute([
                $codeUser,
                $data['grade_enseignant'] ?? 'Enseignant',
                $data['type_contrat'] ?? 'permanent',
                !empty($data['numero_autorisation']) ? trim($data['numero_autorisation']) : null,
                $etabCode,
                $currentUserCode
            ]);

            if ($success) {
                $this->success("L'employé <strong>{$userRow['nom_user']} {$userRow['prenom_user']}</strong> a été configuré comme enseignant avec succès !");
            } else {
                $this->error("Erreur lors de la configuration du profil enseignant.");
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

        if (!empty($email)) {
            $stmtCheckEmail = $db->prepare("SELECT id_user FROM users WHERE email_user = ?");
            $stmtCheckEmail->execute([$email]);
            if ($stmtCheckEmail->fetch()) {
                $this->error('Cette adresse email est déjà utilisée par un compte utilisateur.');
                return;
            }
        }

        $codeUser = $this->validator->generateCode('users', 'code_user', 'ENS-', 8);
        $matricule = 'MAT-' . $codeUser;
        $rawPassword = !empty($data['password_user']) ? trim($data['password_user']) : ('ENS' . rand(100000, 999999));
        $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

        $db->beginTransaction();
        try {
            // Créer le compte utilisateur dans `users`
            $stmtUser = $db->prepare("
                INSERT INTO users (
                    code_user, matricule_user, nom_user, prenom_user, email_user, telephone_user,
                    sexe_user, password_user, etablissement_code, statut_user, created_at_user
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtUser->execute([
                $codeUser,
                $matricule,
                $nom,
                $prenom,
                $email ?: ($codeUser . '@geicg.ci'),
                $telephone ?: null,
                $sexe,
                $hashedPassword,
                $etabCode
            ]);

            // Assigner le rôle ROLE_ENSEIGNANT
            $stmtRole = $db->prepare("
                INSERT INTO user_roles (user_code, role_code, create_permission, edit_permission, show_permission, delete_permission)
                VALUES (?, 'ROLE_ENSEIGNANT', 1, 1, 1, 0)
            ");
            $stmtRole->execute([$codeUser]);

            // Créer la fiche enseignant
            $stmtEns = $db->prepare("
                INSERT INTO enseignants (
                    code_enseignant, grade_enseignant, type_contrat, numero_autorisation,
                    etablissement_code, user_code, statut_enseignant, created_at_enseignant
                ) VALUES (?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtEns->execute([
                $codeUser,
                $data['grade_enseignant'] ?? 'Enseignant',
                $data['type_contrat'] ?? 'permanent',
                !empty($data['numero_autorisation']) ? trim($data['numero_autorisation']) : null,
                $etabCode,
                $currentUserCode
            ]);

            $db->commit();
            $idDisplay = $email ?: ($telephone ?: $codeUser);
            $this->success("Enseignant et compte utilisateur créés avec succès ! Identifiant : <strong>{$idDisplay}</strong> | Mot de passe : <strong style='color:#15803D;'>{$rawPassword}</strong>", ['password' => $rawPassword]);
        } catch (Exception $e) {
            $db->rollBack();
            error_log("EnseignantController add error: " . $e->getMessage());
            $this->error("Erreur lors de la création de l'enseignant : " . $e->getMessage());
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
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

        $db = $this->model->getCon();

        if (!empty($email)) {
            $stmtCheck = $db->prepare("SELECT id_user FROM users WHERE email_user = ? AND code_user != ?");
            $stmtCheck->execute([$email, $item['code_enseignant']]);
            if ($stmtCheck->fetch()) {
                $this->error('Cette adresse email est déjà enregistrée pour un autre utilisateur.');
                return;
            }
        }

        $db->beginTransaction();
        try {
            // 1. Mettre à jour la table USERS
            $sqlUser = "
                UPDATE users 
                SET nom_user = ?, prenom_user = ?, email_user = ?, telephone_user = ?, sexe_user = ?, updated_at_user = NOW()";
            $paramsUser = [
                $nom,
                $prenom,
                $email ?: null,
                $telephone ?: null,
                $sexe
            ];

            if (!empty($data['password_user']) || !empty($data['password_enseignant'])) {
                $rawPwd = !empty($data['password_user']) ? $data['password_user'] : $data['password_enseignant'];
                $sqlUser .= ", password_user = ?";
                $paramsUser[] = password_hash(trim($rawPwd), PASSWORD_DEFAULT);
            }

            $sqlUser .= " WHERE code_user = ?";
            $paramsUser[] = $item['code_enseignant'];

            $stmtU = $db->prepare($sqlUser);
            $stmtU->execute($paramsUser);

            // 2. Mettre à jour la table ENSEIGNANTS
            $sqlEns = "
                UPDATE enseignants 
                SET grade_enseignant = ?, type_contrat = ?, numero_autorisation = ?, 
                    statut_enseignant = ?, updated_at_enseignant = NOW()
                WHERE id_enseignant = ?
            ";
            $stmtE = $db->prepare($sqlEns);
            $stmtE->execute([
                $data['grade_enseignant'] ?? 'Enseignant',
                $data['type_contrat'] ?? 'permanent',
                !empty($data['numero_autorisation']) ? trim($data['numero_autorisation']) : null,
                $data['statut_enseignant'] ?? 'actif',
                $id
            ]);

            $db->commit();
            $this->success('Profil enseignant et compte utilisateur mis à jour avec succès !');
        } catch (Exception $e) {
            $db->rollBack();
            error_log("EnseignantController edit error: " . $e->getMessage());
            $this->error('Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
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
        try {
            $id = $this->validator->decrypter($details);
            if (!$id || !is_numeric($id)) $id = is_numeric($details) ? (int)$details : 0;

            $item = $this->model->getById((int)$id);
            if (!$item) { header('Location: ' . RACINE . 'enseignant/list'); exit(); }

            // Récupérer les cours / matières affectés
            $stmtCours = $this->model->getCon()->prepare("
                SELECT em.*, m.libelle_matiere, cl.libelle_classe
                FROM enseignant_matiere em
                LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
                LEFT JOIN classes cl ON cl.code_classe = em.classe_code
                WHERE em.enseignant_code = ?
                ORDER BY cl.libelle_classe ASC, m.libelle_matiere ASC
            ");
            $stmtCours->execute([$item['code_enseignant']]);
            $cours = $stmtCours->fetchAll(PDO::FETCH_ASSOC) ?: [];

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
        $availableUsers = $this->model->getUsersAvailableForTeacher();
        $this->loadView('../views/enseignants/edit.php', [
            'item' => [],
            'availableUsers' => $availableUsers
        ]);
    }
}