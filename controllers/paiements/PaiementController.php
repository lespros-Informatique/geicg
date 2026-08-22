<?php

class PaiementController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelPaiement();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/paiements/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_paiement'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function getStudentFinancialSummary()
    {
        $this->requireAuth();
        $inscriptionCode = $_GET['inscription_code'] ?? ($_POST['inscription_code'] ?? '');
        $etudiantCode = $_GET['etudiant_code'] ?? ($_POST['etudiant_code'] ?? '');

        $db = $this->model->getCon();

        if (!empty($inscriptionCode)) {
            $stmt = $db->prepare("
                SELECT i.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.code_etudiant, c.libelle_classe
                FROM inscriptions i
                LEFT JOIN etudiants e ON i.etudiant_code = e.code_etudiant
                LEFT JOIN classes c ON i.classe_code = c.code_classe
                WHERE i.code_inscription = ?
                LIMIT 1
            ");
            $stmt->execute([$inscriptionCode]);
            $ins = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (!empty($etudiantCode)) {
            $stmt = $db->prepare("
                SELECT i.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.code_etudiant, c.libelle_classe
                FROM etudiants e
                LEFT JOIN inscriptions i ON i.etudiant_code = e.code_etudiant
                LEFT JOIN classes c ON i.classe_code = c.code_classe
                WHERE e.code_etudiant = ? OR e.matricule_etudiant = ?
                ORDER BY i.id_inscription DESC
                LIMIT 1
            ");
            $stmt->execute([$etudiantCode, $etudiantCode]);
            $ins = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $this->json(['status' => 0, 'message' => 'Code inscription ou matricule manquant']);
            return;
        }

        if (!$ins) {
            $this->json(['status' => 0, 'message' => 'Aucun dossier d\'inscription trouvé pour cet étudiant.']);
            return;
        }

        $codeInscription = $ins['code_inscription'] ?? '';
        $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);

        $stmtPay = $db->prepare("SELECT SUM(montant_paiement) FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
        $stmtPay->execute([$codeInscription]);
        $totalPaye = (float)($stmtPay->fetchColumn() ?: 0);

        $soldeRestant = max(0, $scolariteDue - $totalPaye);

        $statutReglement = 'Non Réglé';
        $badgeClass = 'badge-danger';
        if ($totalPaye >= $scolariteDue && $scolariteDue > 0) {
            $statutReglement = 'Scolarité Totalement Soldée';
            $badgeClass = 'badge-success';
        } elseif ($totalPaye > 0) {
            $statutReglement = 'Acompte Payé / Solde Débiteurs';
            $badgeClass = 'badge-warning';
        }

        $nomComplet = trim(($ins['nom_etudiant'] ?? '') . ' ' . ($ins['prenom_etudiant'] ?? ''));

        $this->json([
            'status' => 1,
            'data' => [
                'code_inscription' => $codeInscription,
                'code_etudiant' => $ins['code_etudiant'] ?? '',
                'matricule' => $ins['matricule_etudiant'] ?? '-',
                'nom_complet' => $nomComplet,
                'classe' => $ins['libelle_classe'] ?? 'Classe non définie',
                'scolarite_due' => $scolariteDue,
                'scolarite_due_fmt' => number_format($scolariteDue, 0, ',', ' ') . ' FCFA',
                'total_paye' => $totalPaye,
                'total_paye_fmt' => number_format($totalPaye, 0, ',', ' ') . ' FCFA',
                'solde_restant' => $soldeRestant,
                'solde_restant_fmt' => number_format($soldeRestant, 0, ',', ' ') . ' FCFA',
                'statut_reglement' => $statutReglement,
                'badge_class' => $badgeClass
            ]
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $_SESSION['annee_active_code'] ?? '0GklBk07waYoLB6pHwY';
        $etabCode = '5454544456';
        $data = $_POST;
        unset($data['csrf_token']);
        if (empty($data['code_paiement'])) {
            $data['code_paiement'] = $this->validator->generateCode('paiements', 'code_paiement', 'PAI-', 8);
        }
        $data['statut_paiement'] = $data['statut_paiement'] ?? 'actif';
        $data['date_paiement'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE paiements")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_paiement');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE paiements")->fetchAll(PDO::FETCH_COLUMN);
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
            if (!$item) { header('Location: ' . RACINE . 'paiement/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'paiement/list'); exit();
        }
        $this->loadView('../views/paiements/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'paiement/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'paiement/list'); exit();
        }
        $this->loadView('../views/paiements/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/paiements/edit.php', ['item' => []]);
    }
}
