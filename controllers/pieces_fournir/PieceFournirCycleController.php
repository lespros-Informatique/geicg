<?php

class PieceFournirCycleController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelPieceFournirCycle();
    }

    public function list()
    {
        $this->requireAuth();
        $summary = $this->model->getSummaryCounts();
        $cycles = (new ModelCycle())->getAll();
        $this->loadView('../views/piece_fournir_cycle/list.php', [
            'summary' => $summary,
            'cycles' => $cycles
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_piece_cycle'];
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
        $cycles = (new ModelCycle())->getAll();
        $pieces = (new ModelPieceFournir())->getActifs();
        $annees = (new ModelAnnee())->getAll();

        $this->loadView('../views/piece_fournir_cycle/edit.php', [
            'cycles' => $cycles,
            'pieces' => $pieces,
            'annees' => $annees
        ]);
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
            $this->error("Pièce de dossier introuvable.");
            return;
        }

        $cycles = (new ModelCycle())->getAll();
        $pieces = (new ModelPieceFournir())->getActifs();
        $annees = (new ModelAnnee())->getAll();

        $this->loadView('../views/piece_fournir_cycle/edit.php', [
            'item' => $item,
            'cycles' => $cycles,
            'pieces' => $pieces,
            'annees' => $annees
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = '5454544456';
        $data = $_POST;
        unset($data['csrf_token']);

        $cycleCode = trim($data['cycle_code'] ?? '');
        $anneePost = trim($data['annee_code'] ?? '') ?: $anneeCode;

        if (empty($cycleCode)) {
            $this->error("Veuillez sélectionner le cycle académique.");
            return;
        }

        $existingPieces = $this->model->getAssignedPieceCodes($cycleCode);

        // Ajout multiple par lot
        if (isset($data['items']) && is_array($data['items'])) {
            $insertedCount = 0;
            $duplicateCount = 0;
            $seenInRequest = [];

            foreach ($data['items'] as $item) {
                $pieceCode = trim($item['piece_code'] ?? '');
                if (empty($pieceCode)) continue;

                // Vérifier si la pièce existe déjà pour ce cycle dans la BD ou dans la même requête
                if (in_array($pieceCode, $existingPieces) || in_array($pieceCode, $seenInRequest)) {
                    $duplicateCount++;
                    continue;
                }

                $seenInRequest[] = $pieceCode;
                $nbEx = max(1, (int)($item['nombre_exemplaires'] ?? 1));
                $nature = in_array($item['nature_document'] ?? '', ['photocopie_simple', 'photocopie_legalisee', 'original', 'numerique']) ? $item['nature_document'] : 'photocopie_simple';
                $exigence = in_array($item['est_obligatoire'] ?? '', ['obligatoire', 'facultatif', 'complementaire']) ? $item['est_obligatoire'] : 'obligatoire';

                $code = $this->validator->generateCode('piece_fournir_cycle', 'code_piece_cycle', 'PFC-', 8);

                $saveData = [
                    'code_piece_cycle' => $code,
                    'cycle_code' => $cycleCode,
                    'piece_code' => $pieceCode,
                    'nombre_exemplaires' => $nbEx,
                    'nature_document' => $nature,
                    'est_obligatoire' => $exigence,
                    'annee_code' => $anneePost ?: null,
                    'etablissement_code' => $etabCode,
                    'user_code' => $userCode,
                    'statut_piece_cycle' => 'actif'
                ];

                if ($this->model->create($saveData)) {
                    $insertedCount++;
                    $existingPieces[] = $pieceCode;
                }
            }

            if ($insertedCount > 0) {
                if ($duplicateCount > 0) {
                    $_SESSION['flash_success'] = "$insertedCount nouvelle(s) pièce(s) enregistrée(s). $duplicateCount pièce(s) déjà existante(s) ont été ignorées.";
                } else {
                    $_SESSION['flash_success'] = "$insertedCount pièce(s) assignée(s) au dossier du cycle avec succès !";
                }
                header('Location: ' . RACINE . 'piece_fournir_cycle/list');
                exit();
            } else {
                if ($duplicateCount > 0) {
                    $this->error("Toutes les pièces sélectionnées existent déjà dans le dossier de ce cycle.");
                } else {
                    $this->error("Aucune pièce valide sélectionnée.");
                }
            }
            return;
        }

        // Ajout unitaire
        $pieceCode = trim($data['piece_code'] ?? '');
        if (empty($pieceCode)) {
            $this->error("Veuillez sélectionner une pièce à fournir.");
            return;
        }

        if ($this->model->existsForCycle($cycleCode, $pieceCode)) {
            $this->error("Cette pièce est déjà enregistrée dans le dossier de ce cycle.");
            return;
        }

        $nbEx = max(1, (int)($data['nombre_exemplaires'] ?? 1));
        $nature = in_array($data['nature_document'] ?? '', ['photocopie_simple', 'photocopie_legalisee', 'original', 'numerique']) ? $data['nature_document'] : 'photocopie_simple';
        $exigence = in_array($data['est_obligatoire'] ?? '', ['obligatoire', 'facultatif', 'complementaire']) ? $data['est_obligatoire'] : 'obligatoire';

        $code = $this->validator->generateCode('piece_fournir_cycle', 'code_piece_cycle', 'PFC-', 8);

        $saveData = [
            'code_piece_cycle' => $code,
            'cycle_code' => $cycleCode,
            'piece_code' => $pieceCode,
            'nombre_exemplaires' => $nbEx,
            'nature_document' => $nature,
            'est_obligatoire' => $exigence,
            'annee_code' => $anneePost ?: null,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'statut_piece_cycle' => $data['statut_piece_cycle'] ?? 'actif'
        ];

        if ($this->model->create($saveData)) {
            $_SESSION['flash_success'] = "Pièce assignée au dossier du cycle avec succès !";
            header('Location: ' . RACINE . 'piece_fournir_cycle/list');
            exit();
        } else {
            $this->error("Erreur lors de l'enregistrement de la pièce.");
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $data = $_POST;
        unset($data['csrf_token']);

        $id = (int)($data['id_piece_cycle'] ?? 0);
        if ($id <= 0) {
            $this->error("Identifiant invalide.");
            return;
        }

        $cycleCode = trim($data['cycle_code'] ?? '');
        $pieceCode = trim($data['piece_code'] ?? '');

        if (empty($cycleCode) || empty($pieceCode)) {
            $this->error("Le cycle et la pièce sont obligatoires.");
            return;
        }

        if ($this->model->existsForCycle($cycleCode, $pieceCode, $id)) {
            $this->error("Cette pièce est déjà configurée dans le dossier de ce cycle.");
            return;
        }

        $nbEx = max(1, (int)($data['nombre_exemplaires'] ?? 1));
        $nature = in_array($data['nature_document'] ?? '', ['photocopie_simple', 'photocopie_legalisee', 'original', 'numerique']) ? $data['nature_document'] : 'photocopie_simple';
        $exigence = in_array($data['est_obligatoire'] ?? '', ['obligatoire', 'facultatif', 'complementaire']) ? $data['est_obligatoire'] : 'obligatoire';

        $updateData = [
            'cycle_code' => $cycleCode,
            'piece_code' => $pieceCode,
            'nombre_exemplaires' => $nbEx,
            'nature_document' => $nature,
            'est_obligatoire' => $exigence,
            'statut_piece_cycle' => $data['statut_piece_cycle'] ?? 'actif'
        ];

        if ($this->model->update($updateData, $id)) {
            $_SESSION['flash_success'] = "Pièce du cycle mise à jour avec succès !";
            header('Location: ' . RACINE . 'piece_fournir_cycle/list');
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

        $res = $this->model->update(['statut_piece_cycle' => $statut], (int)$idDecrypte);
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
            $_SESSION['flash_success'] = "Pièce retirée du dossier de ce cycle.";
            header('Location: ' . RACINE . 'piece_fournir_cycle/list');
            exit();
        } else {
            $this->error("Impossible de retirer cette pièce.");
        }
    }

    public function getByCycleApi()
    {
        $this->requireAuth();
        $cycleCode = trim($_GET['cycle_code'] ?? ($_POST['cycle_code'] ?? ''));
        $items = $this->model->getByCycle($cycleCode);
        $assignedCodes = $this->model->getAssignedPieceCodes($cycleCode);
        $this->json([
            'status' => 1,
            'data' => $items,
            'assignedCodes' => $assignedCodes
        ]);
    }
}
