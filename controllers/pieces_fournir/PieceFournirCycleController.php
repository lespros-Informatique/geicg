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
        $this->requirePermission(['MANAGE_PIECES', 'VIEW_PIECES', 'CONFIG_ACADEMIQUE', 'MANAGE_INSCRIPTIONS']);
        $cycles = (new ModelCycle())->getAll();
        $niveaux = (new ModelNiveau())->getActifs();
        
        $selectedCycleCode = $_GET['cycle_code'] ?? null;
        $selectedNiveauCode = $_GET['niveau_code'] ?? null;
        $summary = $this->model->getSummaryCounts($selectedCycleCode, $selectedNiveauCode);

        $this->loadView('../views/piece_fournir_cycle/list.php', [
            'summary' => $summary,
            'cycles' => $cycles,
            'niveaux' => $niveaux,
            'selectedCycleCode' => $selectedCycleCode,
            'selectedNiveauCode' => $selectedNiveauCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_PIECES', 'VIEW_PIECES', 'CONFIG_ACADEMIQUE', 'MANAGE_INSCRIPTIONS']);
        $cycleCode = isset($_GET['cycle_code']) ? trim($_GET['cycle_code']) : null;
        $niveauCode = isset($_GET['niveau_code']) ? trim($_GET['niveau_code']) : null;
        $items = $this->model->getAll($cycleCode, $niveauCode);
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

    public function apiStats()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_PIECES', 'VIEW_PIECES', 'CONFIG_ACADEMIQUE', 'MANAGE_INSCRIPTIONS']);
        $cycleCode = isset($_GET['cycle_code']) ? trim($_GET['cycle_code']) : null;
        $niveauCode = isset($_GET['niveau_code']) ? trim($_GET['niveau_code']) : null;
        $summary = $this->model->getSummaryCounts($cycleCode, $niveauCode);
        $this->json(['status' => 1, 'data' => $summary]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_PIECES', 'CONFIG_ACADEMIQUE']);
        $cycles = (new ModelCycle())->getAll();
        $niveaux = (new ModelNiveau())->getActifs();
        $pieces = (new ModelPieceFournir())->getActifs();

        $this->loadView('../views/piece_fournir_cycle/edit.php', [
            'cycles' => $cycles,
            'niveaux' => $niveaux,
            'pieces' => $pieces
        ]);
    }

    public function edition($idParam)
    {
        $this->requireAuth();
        $this->requirePermission(['MANAGE_PIECES', 'CONFIG_ACADEMIQUE']);
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
        $niveaux = (new ModelNiveau())->getActifs();
        $pieces = (new ModelPieceFournir())->getActifs();

        $this->loadView('../views/piece_fournir_cycle/edit.php', [
            'item' => $item,
            'cycles' => $cycles,
            'niveaux' => $niveaux,
            'pieces' => $pieces
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['MANAGE_PIECES', 'CONFIG_ACADEMIQUE']);
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        $cycleCode = trim($data['cycle_code'] ?? '');
        $niveauCode = trim($data['niveau_code'] ?? '');

        if (empty($cycleCode)) {
            $this->error("Veuillez sélectionner le cycle académique.");
            return;
        }

        if (empty($niveauCode)) {
            $this->error("Veuillez sélectionner le niveau d'étude.");
            return;
        }

        $existingPieces = $this->model->getAssignedPieceCodes($cycleCode, $niveauCode);

        // Ajout multiple par lot
        if (isset($data['items']) && is_array($data['items'])) {
            $insertedCount = 0;
            $duplicateCount = 0;
            $seenInRequest = [];

            foreach ($data['items'] as $item) {
                $pieceCode = trim($item['piece_code'] ?? '');
                if (empty($pieceCode)) continue;

                // Vérifier si la pièce existe déjà pour ce cycle/niveau dans la BD ou dans la même requête
                if (in_array($pieceCode, $existingPieces) || in_array($pieceCode, $seenInRequest)) {
                    $duplicateCount++;
                    continue;
                }

                $seenInRequest[] = $pieceCode;
                $nbEx = max(1, (int)($item['nombre_exemplaires'] ?? 1));
                $nature = in_array($item['nature_document'] ?? '', ['photocopie_simple', 'photocopie_legalisee', 'original', 'numerique', 'aucun']) ? $item['nature_document'] : 'photocopie_simple';
                $code = $this->validator->generateCode('piece_fournir_cycle', 'code_piece_cycle', 'PFC-', 8);

                $saveData = [
                    'code_piece_cycle' => $code,
                    'cycle_code' => $cycleCode,
                    'niveau_code' => $niveauCode,
                    'piece_code' => $pieceCode,
                    'nombre_exemplaires' => $nbEx,
                    'nature_document' => $nature,
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
                    $this->error("Toutes les pièces sélectionnées existent déjà dans le dossier de ce cycle pour ce niveau.");
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

        if ($this->model->existsForCycle($cycleCode, $pieceCode, $niveauCode)) {
            $this->error("Cette pièce est déjà enregistrée dans le dossier de ce cycle pour ce niveau.");
            return;
        }

        $nbEx = max(1, (int)($data['nombre_exemplaires'] ?? 1));
        $nature = in_array($data['nature_document'] ?? '', ['photocopie_simple', 'photocopie_legalisee', 'original', 'numerique', 'aucun']) ? $data['nature_document'] : 'photocopie_simple';
        $code = $this->validator->generateCode('piece_fournir_cycle', 'code_piece_cycle', 'PFC-', 8);

        $saveData = [
            'code_piece_cycle' => $code,
            'cycle_code' => $cycleCode,
            'niveau_code' => $niveauCode,
            'piece_code' => $pieceCode,
            'nombre_exemplaires' => $nbEx,
            'nature_document' => $nature,
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
        $this->requirePermission(['MANAGE_PIECES', 'CONFIG_ACADEMIQUE']);
        $data = $_POST;
        unset($data['csrf_token']);

        $id = (int)($data['id_piece_cycle'] ?? 0);
        if ($id <= 0) {
            $this->error("Identifiant invalide.");
            return;
        }

        $cycleCode = trim($data['cycle_code'] ?? '');
        $pieceCode = trim($data['piece_code'] ?? '');
        $niveauCode = trim($data['niveau_code'] ?? '');

        if (empty($cycleCode) || empty($pieceCode) || empty($niveauCode)) {
            $this->error("Le cycle, le niveau d'étude et la pièce sont obligatoires.");
            return;
        }

        if ($this->model->existsForCycle($cycleCode, $pieceCode, $niveauCode, $id)) {
            $this->error("Cette pièce est déjà configurée dans le dossier de ce cycle pour ce niveau.");
            return;
        }

        $nbEx = max(1, (int)($data['nombre_exemplaires'] ?? 1));
        $nature = in_array($data['nature_document'] ?? '', ['photocopie_simple', 'photocopie_legalisee', 'original', 'numerique', 'aucun']) ? $data['nature_document'] : 'photocopie_simple';
        $updateData = [
            'cycle_code' => $cycleCode,
            'niveau_code' => $niveauCode,
            'piece_code' => $pieceCode,
            'nombre_exemplaires' => $nbEx,
            'nature_document' => $nature,
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
        $this->requirePermission(['MANAGE_PIECES', 'CONFIG_ACADEMIQUE']);
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
        $this->requirePermission(['MANAGE_PIECES', 'CONFIG_ACADEMIQUE']);
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
        $this->requirePermission(['MANAGE_PIECES', 'VIEW_PIECES', 'CONFIG_ACADEMIQUE', 'MANAGE_INSCRIPTIONS']);
        $cycleCode = trim($_GET['cycle_code'] ?? ($_POST['cycle_code'] ?? ''));
        $niveauCode = trim($_GET['niveau_code'] ?? ($_POST['niveau_code'] ?? ''));
        $niveauParam = !empty($niveauCode) ? $niveauCode : null;
        $items = $this->model->getByCycle($cycleCode, $niveauParam);
        $assignedCodes = $this->model->getAssignedPieceCodes($cycleCode, $niveauParam);
        $niveaux = !empty($cycleCode) ? (new ModelNiveau())->getByCycle($cycleCode) : [];
        $this->json([
            'status' => 1,
            'data' => $items,
            'assignedCodes' => $assignedCodes,
            'niveaux' => $niveaux
        ]);
    }
}
