<?php

class PieceFournirController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelPieceFournir();
    }

    public function list()
    {
        $this->requireAuth();
        $summary = $this->model->getSummaryCounts();
        $this->loadView('../views/pieces_fournir/list.php', [
            'summary' => $summary
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_piece_fournir'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/pieces_fournir/edit.php', []);
    }

    public function edition($idParam)
    {
        $this->requireAuth();
        $id = $this->validator->decrypter($idParam);
        if (!$id || !is_numeric($id)) {
            $id = is_numeric($idParam) ? (int)$idParam : 0;
        }

        $item = $this->model->getById((int)$id);
        if (!$item) {
            $this->error("Pièce à fournir introuvable.");
            return;
        }

        $this->loadView('../views/pieces_fournir/edit.php', [
            'item' => $item
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $etabCode = '5454544456';
        $data = $_POST;
        unset($data['csrf_token']);

        // Ajout par lot
        if (isset($data['pieces']) && is_array($data['pieces'])) {
            $inserted = 0;
            $duplicates = 0;
            $seenInBatch = [];

            foreach ($data['pieces'] as $p) {
                $libelle = trim($p['libelle'] ?? '');
                if (empty($libelle)) continue;

                $libelleLower = mb_strtolower($libelle);

                // Vérifier si le libellé est un doublon dans le formulaire ou existe déjà en BD
                if (in_array($libelleLower, $seenInBatch) || $this->model->existsByLibelle($libelle)) {
                    $duplicates++;
                    continue;
                }

                $seenInBatch[] = $libelleLower;

                $code = $this->validator->generateCode('pieces_fournir', 'code_piece_fournir', 'DOC-', 8);
                $save = [
                    'code_piece_fournir' => $code,
                    'libelle_piece' => $libelle,
                    'description_piece' => trim($p['description'] ?? ''),
                    'etablissement_code' => $etabCode,
                    'user_code' => $userCode,
                    'statut_piece' => 'actif'
                ];
                if ($this->model->create($save)) {
                    $inserted++;
                }
            }

            if ($inserted > 0) {
                if ($duplicates > 0) {
                    $_SESSION['flash_success'] = "$inserted pièce(s) ajoutée(s) au répertoire avec succès. $duplicates document(s) existant déjà ont été ignoré(s).";
                } else {
                    $_SESSION['flash_success'] = "$inserted pièce(s) ajoutée(s) au répertoire avec succès !";
                }
                header('Location: ' . RACINE . 'piece_fournir/list');
                exit();
            } else {
                if ($duplicates > 0) {
                    $this->error("Le(s) document(s) saisi(s) existe(nt) déjà dans le répertoire des pièces.");
                } else {
                    $this->error("Aucune pièce valide à enregistrer.");
                }
            }
            return;
        }

        // Ajout unitaire
        $libelle = trim($data['libelle_piece'] ?? '');
        if (empty($libelle)) {
            $this->error("L'intitulé de la pièce à fournir est obligatoire.");
            return;
        }

        if ($this->model->existsByLibelle($libelle)) {
            $this->error("La pièce « $libelle » existe déjà dans le répertoire.");
            return;
        }

        $code = $this->validator->generateCode('pieces_fournir', 'code_piece_fournir', 'DOC-', 8);
        $saveData = [
            'code_piece_fournir' => $code,
            'libelle_piece' => $libelle,
            'description_piece' => trim($data['description_piece'] ?? ''),
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'statut_piece' => $data['statut_piece'] ?? 'actif'
        ];

        if ($this->model->create($saveData)) {
            $_SESSION['flash_success'] = "Pièce « $libelle » enregistrée avec succès !";
            header('Location: ' . RACINE . 'piece_fournir/list');
            exit();
        } else {
            $this->error("Erreur lors de l'enregistrement.");
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $data = $_POST;
        unset($data['csrf_token']);

        $id = (int)($data['id_piece_fournir'] ?? 0);
        if ($id <= 0) {
            $this->error("Identifiant invalide.");
            return;
        }

        $libelle = trim($data['libelle_piece'] ?? '');
        if (empty($libelle)) {
            $this->error("L'intitulé de la pièce est obligatoire.");
            return;
        }

        if ($this->model->existsByLibelle($libelle, $id)) {
            $this->error("Une autre pièce portant l'intitulé « $libelle » existe déjà dans le répertoire.");
            return;
        }

        $updateData = [
            'libelle_piece' => $libelle,
            'description_piece' => trim($data['description_piece'] ?? ''),
            'statut_piece' => $data['statut_piece'] ?? 'actif'
        ];

        if ($this->model->update($updateData, $id)) {
            $_SESSION['flash_success'] = "Pièce à fournir mise à jour avec succès !";
            header('Location: ' . RACINE . 'piece_fournir/list');
            exit();
        } else {
            $this->error("Erreur lors de la mise à jour.");
        }
    }

    public function changer()
    {
        $this->requireAuth();
        $id = $_POST['id'] ?? null;
        $statut = $_POST['statut'] ?? null;

        if (!$id || !$statut) {
            $this->json(['status' => 0, 'message' => 'Paramètres invalides']);
            return;
        }

        $idDecrypte = $this->validator->decrypter($id);
        if (!$idDecrypte || !is_numeric($idDecrypte)) {
            $idDecrypte = is_numeric($id) ? (int)$id : 0;
        }

        $res = $this->model->update(['statut_piece' => $statut], (int)$idDecrypte);
        if ($res) {
            $this->json(['status' => 1, 'message' => 'Statut mis à jour avec succès']);
        } else {
            $this->json(['status' => 0, 'message' => 'Erreur lors de la mise à jour du statut']);
        }
    }

    public function supprimer($idParam)
    {
        $this->requireAuth();
        $id = $this->validator->decrypter($idParam);
        if (!$id || !is_numeric($id)) {
            $id = is_numeric($idParam) ? (int)$idParam : 0;
        }

        if ($this->model->delete((int)$id)) {
            $_SESSION['flash_success'] = "Pièce supprimée du répertoire.";
            header('Location: ' . RACINE . 'piece_fournir/list');
            exit();
        } else {
            $this->error("Impossible de supprimer cette pièce.");
        }
    }
}
