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
        $this->loadView('../views/etudiants/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_etudiant'];
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
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['matricule_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'matricule_etudiant', $data['matricule_etudiant'], 'Matricule etudiant')) return;
        }
        if (!empty($data['email_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'email_etudiant', $data['email_etudiant'], 'Email etudiant')) return;
        }
        if (!empty($data['telephone_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'telephone_etudiant', $data['telephone_etudiant'], 'Telephone etudiant')) return;
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $_SESSION['annee_active_code'] ?? '0GklBk07waYoLB6pHwY';
        $etabCode = '5454544456';
        if (empty($data['code_etudiant'])) {
            $data['code_etudiant'] = $this->validator->generateCode('etudiants', 'code_etudiant', 'ETU-', 8);
        }
        $data['statut_etudiant'] = $data['statut_etudiant'] ?? 'actif';
        $data['created_at_etudiant'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE etudiants")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
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
        $id = (int)$this->post('id_etudiant');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['matricule_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'matricule_etudiant', $data['matricule_etudiant'], 'Matricule etudiant', 'id_etudiant', $id)) return;
        }
        if (!empty($data['email_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'email_etudiant', $data['email_etudiant'], 'Email etudiant', 'id_etudiant', $id)) return;
        }
        if (!empty($data['telephone_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'telephone_etudiant', $data['telephone_etudiant'], 'Telephone etudiant', 'id_etudiant', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE etudiants")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
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
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'etudiant/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'etudiant/list'); exit();
        }
        $this->loadView('../views/etudiants/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
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
        $this->loadView('../views/etudiants/edit.php', ['item' => []]);
    }

    public function wizard()
    {
        $this->requireAuth();
        $this->loadView('../views/etudiants/wizard.php');
    }

    public function addWizard()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $db = $this->model->getCon();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $_SESSION['annee_active_code'] ?? '0GklBk07waYoLB6pHwY';
        $etabCode = '5454544456';

        $data = $_POST;
        unset($data['csrf_token']);

        $nomEtudiant = trim($data['nom_etudiant'] ?? '');
        $prenomEtudiant = trim($data['prenom_etudiant'] ?? '');
        $telephoneEtudiant = trim($data['telephone_etudiant'] ?? '');

        if (empty($nomEtudiant) || empty($prenomEtudiant) || empty($telephoneEtudiant)) {
            $this->error('Veuillez renseigner le nom, prénoms et téléphone de l\'étudiant.');
            return;
        }

        $matriculeEtudiant = trim($data['matricule_etudiant'] ?? '');
        if (empty($matriculeEtudiant)) {
            $matriculeEtudiant = $this->validator->generateCode('etudiants', 'matricule_etudiant', 'ETU-' . date('Y') . '-', 4);
        }

        $codeEtudiant = $this->validator->generateCode('etudiants', 'code_etudiant', 'ETU-', 8);

        try {
            $db->beginTransaction();

            // 1. Insert Student into `etudiants`
            $stmtEtu = $db->prepare("
                INSERT INTO etudiants 
                (code_etudiant, matricule_etudiant, nom_etudiant, prenom_etudiant, sexe_etudiant, date_naissance, lieu_naissance, nationalite, telephone_etudiant, email_etudiant, adresse_etudiant, user_code, annee_code, etablissement_code, statut_etudiant, created_at_etudiant)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtEtu->execute([
                $codeEtudiant,
                $matriculeEtudiant,
                $nomEtudiant,
                $prenomEtudiant,
                $data['sexe_etudiant'] ?? 'M',
                !empty($data['date_naissance']) ? $data['date_naissance'] : null,
                !empty($data['lieu_naissance']) ? $data['lieu_naissance'] : null,
                !empty($data['nationalite']) ? $data['nationalite'] : 'Ivoirienne',
                $telephoneEtudiant,
                !empty($data['email_etudiant']) ? $data['email_etudiant'] : null,
                !empty($data['adresse_etudiant']) ? $data['adresse_etudiant'] : null,
                $userCode,
                $anneeCode,
                $etabCode
            ]);

            // 2. Insert Parent into `parents` if parent info provided
            if (!empty($data['nom_pere']) || !empty($data['nom_mere']) || !empty($data['nom_tuteur'])) {
                $codeParent = $this->validator->generateCode('parents', 'code_parent', 'PAR-', 8);
                $stmtPar = $db->prepare("
                    INSERT INTO parents 
                    (code_parent, etudiant_code, nom_pere, telephone_pere, profession_pere, nom_mere, telephone_mere, nom_tuteur, telephone_tuteur, user_code, annee_code, etablissement_code, statut_parent, created_at_parent)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
                ");
                $stmtPar->execute([
                    $codeParent,
                    $codeEtudiant,
                    !empty($data['nom_pere']) ? $data['nom_pere'] : null,
                    !empty($data['telephone_pere']) ? $data['telephone_pere'] : null,
                    !empty($data['profession_pere']) ? $data['profession_pere'] : null,
                    !empty($data['nom_mere']) ? $data['nom_mere'] : null,
                    !empty($data['telephone_mere']) ? $data['telephone_mere'] : null,
                    !empty($data['nom_tuteur']) ? $data['nom_tuteur'] : null,
                    !empty($data['telephone_tuteur']) ? $data['telephone_tuteur'] : null,
                    $userCode,
                    $anneeCode,
                    $etabCode
                ]);
            }

            // 3. Insert Academic Inscription into `inscriptions` if classe_code provided
            if (!empty($data['classe_code'])) {
                $codeInscription = $this->validator->generateCode('inscriptions', 'code_inscription', 'INS-', 8);
                $stmtIns = $db->prepare("
                    INSERT INTO inscriptions
                    (code_inscription, etudiant_code, classe_code, montant_scolarite_inscription, remise_accordee, date_inscription, user_code, annee_code, etablissement_code, statut_inscription, created_at_inscription)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
                ");
                $stmtIns->execute([
                    $codeInscription,
                    $codeEtudiant,
                    $data['classe_code'],
                    (float)($data['montant_scolarite_inscription'] ?? 0),
                    (float)($data['remise_accordee'] ?? 0),
                    !empty($data['date_inscription']) ? $data['date_inscription'] : date('Y-m-d'),
                    $userCode,
                    $anneeCode,
                    $etabCode
                ]);
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
}