<?php

class SemestreController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelSemestre();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_SEMESTRES);
        $annees = $this->getAccessibleAnnees();
        
        if (isset($_GET['annee_code']) && !empty($_GET['annee_code'])) {
            $selectedAnneeCode = trim($_GET['annee_code']);
            foreach ($annees as $a) {
                if ($a['code_annee'] === $selectedAnneeCode) {
                    Session::set('annee_active_code', $a['code_annee']);
                    Session::set('annee_active_libelle', $a['libelle_annee']);
                    break;
                }
            }
        } else {
            $selectedAnneeCode = Session::get('annee_active_code');
        }

        $this->loadView('../views/semestres/list.php', [
            'annees' => $annees,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_SEMESTRES);
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code');
        $items = $this->model->getAll($anneeCode);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_semestre'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function normalizeLibelle(string $raw): ?string
    {
        $trim = trim($raw);
        $upper = strtoupper($trim);
        if ($upper === 'SEMESTRE 1' || $upper === 'SEMESTRE1' || $upper === 'S1') {
            return 'Semestre 1';
        }
        if ($upper === 'SEMESTRE 2' || $upper === 'SEMESTRE2' || $upper === 'S2') {
            return 'Semestre 2';
        }
        return null;
    }

    private function validateDatesSemestre(string $dateDebut, string $dateFin, string $anneeCode = ''): ?string
    {
        if (empty($dateDebut) || empty($dateFin)) {
            return "Les dates de début et de fin du semestre sont obligatoires.";
        }

        $timeDebut = strtotime($dateDebut);
        $timeFin = strtotime($dateFin);

        if (!$timeDebut || !$timeFin) {
            return "Les dates saisies pour le semestre ne sont pas valides.";
        }

        if ($timeFin <= $timeDebut) {
            $debutFormatted = date('d/m/Y', $timeDebut);
            $finFormatted = date('d/m/Y', $timeFin);
            return "Incohérence des dates du semestre : La date de fin ($finFormatted) doit être strictement postérieure à la date de début ($debutFormatted).";
        }

        if (!empty($anneeCode)) {
            $annee = $this->model->getAnneeByCode($anneeCode);

            if ($annee) {
                if (!empty($annee['date_debut_annee'])) {
                    $anneeDebut = strtotime($annee['date_debut_annee']);
                    if ($timeDebut < $anneeDebut) {
                        return "La date de début du semestre (" . date('d/m/Y', $timeDebut) . ") ne peut pas être antérieure à la date de début de l'année académique " . $annee['libelle_annee'] . " (" . date('d/m/Y', $anneeDebut) . ").";
                    }
                }
                if (!empty($annee['date_fin_annee'])) {
                    $anneeFin = strtotime($annee['date_fin_annee']);
                    if ($timeFin > $anneeFin) {
                        return "La date de fin du semestre (" . date('d/m/Y', $timeFin) . ") ne peut pas dépasser la date de fin de l'année académique " . $annee['libelle_annee'] . " (" . date('d/m/Y', $anneeFin) . ").";
                    }
                }
            }
        }

        return null;
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_SEMESTRES);
        $data = $_POST;
        unset($data['csrf_token']);

        // Contrôle strict du libellé : uniquement Semestre 1 ou Semestre 2
        $libelle = $this->normalizeLibelle($data['libelle_semestre'] ?? '');
        if (!$libelle) {
            $this->error('Veuillez sélectionner un semestre valide (Semestre 1 ou Semestre 2).');
            return;
        }
        $data['libelle_semestre'] = $libelle;

        $userCode = Auth::code();
        $anneeCode = !empty($data['annee_code']) ? $data['annee_code'] : ($this->getActiveAnneeCode());
        $etabCode = $this->getActiveEtablissementCode();

        if (empty($anneeCode)) {
            $this->error('Veuillez sélectionner une année académique.');
            return;
        }

        // Validation de cohérence des dates du semestre
        $dateErr = $this->validateDatesSemestre($data['date_debut_semestre'] ?? '', $data['date_fin_semestre'] ?? '', $anneeCode);
        if ($dateErr) {
            $this->error($dateErr);
            return;
        }

        // Vérification que la date de fin de l'année sélectionnée n'est pas encore passée
        $targetAnnee = $this->model->getAnneeByCode($anneeCode);
        if ($targetAnnee && !empty($targetAnnee['date_fin_annee']) && $targetAnnee['date_fin_annee'] < date('Y-m-d')) {
            $dateFinFr = date('d/m/Y', strtotime($targetAnnee['date_fin_annee']));
            $this->error("Impossible de créer un semestre sur l'année académique {$targetAnnee['libelle_annee']} car sa date de fin est déjà échue ($dateFinFr).");
            return;
        }

        // Contrôle d'unicité : un seul Semestre 1 et un seul Semestre 2 par année académique
        if ($this->model->checkSemestreExists($libelle, $anneeCode)) {
            $this->error("Le $libelle est déjà enregistré pour l'année académique sélectionnée.");
            return;
        }

        if (empty($data['code_semestre'])) {
            $data['code_semestre'] = $this->validator->generateCode(TABLES::SEMESTRES, 'code_semestre', 'SEM-', 8);
        }
        $data['statut_semestre'] = (!empty($data['statut_semestre']) && in_array($data['statut_semestre'], ['planifie', 'cloture'], true)) ? $data['statut_semestre'] : 'planifie';
        $data['created_at_semestre'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        $filteredData = $this->model->filterData($data);
        if ($this->model->create($filteredData)) {
            $this->success('Semestre créé avec succès!');
        } else {
            $this->error('Erreur lors de la création du semestre.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_SEMESTRES);
        $id = (int)$this->post('id_semestre');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        
        $current = $this->model->getById($id);
        if (!$current) { $this->error('Semestre introuvable'); return; }

        if (($current['statut_semestre'] ?? '') !== 'planifie') {
            $this->error('Seul un semestre en préparation (planifié) peut être modifié.');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        // Contrôle strict du libellé : uniquement Semestre 1 ou Semestre 2
        $libelle = $this->normalizeLibelle($data['libelle_semestre'] ?? '');
        if (!$libelle) {
            $this->error('Veuillez sélectionner un semestre valide (Semestre 1 ou Semestre 2).');
            return;
        }
        $data['libelle_semestre'] = $libelle;

        $anneeCode = !empty($data['annee_code']) ? $data['annee_code'] : ($current['annee_code'] ?? '');
        if (empty($anneeCode)) {
            $this->error('Veuillez sélectionner une année académique.');
            return;
        }

        // Validation de cohérence des dates du semestre
        $dDebut = $data['date_debut_semestre'] ?? ($current['date_debut_semestre'] ?? '');
        $dFin = $data['date_fin_semestre'] ?? ($current['date_fin_semestre'] ?? '');
        $dateErr = $this->validateDatesSemestre($dDebut, $dFin, $anneeCode);
        if ($dateErr) {
            $this->error($dateErr);
            return;
        }

        // Contrôle d'unicité par année académique (en excluant l'enregistrement en cours d'édition)
        if ($this->model->checkSemestreExists($libelle, $anneeCode, $id)) {
            $this->error("Le $libelle est déjà enregistré pour cette année académique.");
            return;
        }

        $filteredData = $this->model->filterData($data);
        if ($this->model->update($filteredData, $id)) {
            $this->success('Semestre modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification du semestre.');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_SEMESTRES);
        $id = (int)$this->post('id');
        $item = $id ? $this->model->getById($id) : null;
        if ($item) {
            $currentStatus = $item['statut_semestre'] ?? 'planifie';
            $errorMsg = '';

            if ($currentStatus !== 'actif') {
                if (!$this->model->canActivate($item, $errorMsg)) {
                    $this->error($errorMsg);
                    return;
                }
            } else {
                if (!$this->model->canClose($item, $errorMsg)) {
                    $this->error($errorMsg);
                    return;
                }
            }

            if ($this->model->toggleStatus($id)) {
                $msg = ($currentStatus === 'actif') ? 'Semestre clôturé avec succès !' : 'Semestre activé avec succès !';
                $this->success($msg, ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Semestre introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_SEMESTRES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getSemestreWithAnnee($id);
            if (!$item) { header('Location: ' . RACINE . 'semestre/list'); exit(); }

            $semestreCode = $item['code_semestre'];
            $stats = $this->model->getStatsForSemestre($semestreCode);
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("SemestreController::details error: " . $e->getMessage());
            $this->renderNotFound("Le semestre demandé est introuvable.");
            return;
        }
        $this->loadView('../views/semestres/details.php', [
            'item' => $item, 
            'stats' => $stats,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_SEMESTRES);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || ($item['statut_semestre'] ?? '') === STATUTS::ACTIF) { 
                header('Location: ' . RACINE . 'semestre/list'); 
                exit(); 
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'semestre/list'); exit();
        }
        $this->loadView('../views/semestres/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::MANAGE_SEMESTRES);
        $this->loadView('../views/semestres/edit.php', ['item' => []]);
    }

    /**
     * Vérifie si un semestre existe déjà pour l'année académique sélectionnée (pour validation dynamique AJAX)
     */
    public function checkExists()
    {
        $this->requireAuth();
        $anneeCode = trim($_REQUEST['annee_code'] ?? '');
        $libelleRaw = trim($_REQUEST['libelle_semestre'] ?? '');
        $idExclude = (int)($_REQUEST['id_semestre'] ?? 0);

        $libelle = $this->normalizeLibelle($libelleRaw);
        if (empty($anneeCode) || empty($libelle)) {
            $this->json(['exists' => false]);
            return;
        }

        $exists = $this->model->checkSemestreExists($libelle, $anneeCode, $idExclude);

        $this->json([
            'exists' => $exists,
            'message' => $exists ? "Erreur : Le {$libelle} est déjà enregistré pour l'année académique sélectionnée." : ""
        ]);
    }
}