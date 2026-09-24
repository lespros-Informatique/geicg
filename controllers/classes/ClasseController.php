<?php

class ClasseController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelClasse();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_CLASSES');
        $db = $this->model->getCon();

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }

        $activeYear = $this->getActiveAnneeCode();
        $annees = $db->query("SELECT code_annee, libelle_annee, statut_annee FROM annees ORDER BY id_annee DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Récupérer la liste des combinaisons pivots Cycle - Filière - Niveau
        $parcoursPivots = $db->query("
            SELECT fc.*, 
                   f.libelle_filiere, f.slug_filiere,
                   c.libelle_cycle, c.slug_cycle,
                   n.libelle_niveau, n.slug_niveau
            FROM filiere_cycles fc
            LEFT JOIN filieres f ON f.code_filiere = fc.filiere_code
            LEFT JOIN cycles c ON c.code_cycle = fc.cycle_code
            LEFT JOIN niveaux n ON n.code_niveau = fc.niveau_code
            WHERE fc.statut_filiere_cycle = 'actif'
            ORDER BY c.libelle_cycle ASC, f.libelle_filiere ASC, n.libelle_niveau ASC
        ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->loadView('../views/classes/list.php', [
            'annees' => $annees,
            'selectedAnneeCode' => $activeYear,
            'parcoursPivots' => $parcoursPivots
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_CLASSES');
        $db = $this->model->getCon();

        if (!empty($_GET['annee_code'])) {
            $getAnnee = trim($_GET['annee_code']);
            $stmtA = $db->prepare("SELECT code_annee, libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$getAnnee]);
            $aRow = $stmtA->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $_SESSION['annee_active_code'] = $aRow['code_annee'];
                $_SESSION['annee_active_libelle'] = $aRow['libelle_annee'];
            }
        }

        $anneeCode = $this->getActiveAnneeCode();

        $sql = "SELECT c.*, 
                       f.libelle_filiere, f.slug_filiere,
                       n.libelle_niveau, n.slug_niveau,
                       cy.libelle_cycle, cy.slug_cycle,
                       a.libelle_annee
                FROM classes c
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                LEFT JOIN cycles cy ON cy.code_cycle = c.cycle_code
                LEFT JOIN annees a ON a.code_annee = c.annee_code
                WHERE (c.annee_code = ? OR ? = '')
                ORDER BY c.id_classe DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$anneeCode, $anneeCode]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_classe'];
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
        $this->requirePermission('MANAGE_CLASSES');
        $data = $_POST;
        unset($data['csrf_token']);

        // Déduction automatique de cycle_code depuis filiere_cycles si non fourni
        if (empty($data['cycle_code']) && !empty($data['filiere_code'])) {
            $stmtCy = $this->model->getCon()->prepare("
                SELECT cycle_code FROM filiere_cycles 
                WHERE filiere_code = ? AND (niveau_code = ? OR niveau_code IS NULL OR niveau_code = '') 
                LIMIT 1
            ");
            $stmtCy->execute([$data['filiere_code'], $data['niveau_code'] ?? '']);
            $cyCode = $stmtCy->fetchColumn();
            if ($cyCode) {
                $data['cycle_code'] = $cyCode;
            }
        }

        // Génération automatique intelligente de secours si le champ libelle_classe est vide
        if (empty($data['libelle_classe']) && !empty($data['filiere_code']) && !empty($data['niveau_code'])) {
            $stmtF = $this->model->getCon()->prepare("SELECT libelle_filiere FROM filieres WHERE code_filiere = ?");
            $stmtF->execute([$data['filiere_code']]);
            $fName = $stmtF->fetchColumn();

            $stmtN = $this->model->getCon()->prepare("SELECT libelle_niveau FROM niveaux WHERE code_niveau = ?");
            $stmtN->execute([$data['niveau_code']]);
            $nName = $stmtN->fetchColumn();

            if ($fName && $nName) {
                $data['libelle_classe'] = trim($fName) . ' - ' . trim($nName);
            }
        }

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = !empty($data['annee_code']) ? trim($data['annee_code']) : $this->getActiveAnneeCode();

        if (empty($anneeCode)) {
            $this->error("Impossible de créer la classe : Aucune année académique n'a été spécifiée ou n'est active. Veuillez sélectionner ou créer une année académique.");
            return;
        }

        if (!empty($data['libelle_classe'])) {
            if (!$this->checkUniquePair('classes', [
                'libelle_classe' => $data['libelle_classe'],
                'annee_code' => $anneeCode
            ], 'nom de classe', 'id_classe', null)) return;
        }

        $etabCode = $this->getActiveEtablissementCode();
        if (empty($data['code_classe'])) {
            $data['code_classe'] = $this->validator->generateCode('classes', 'code_classe', 'CLA-', 8);
        }
        $data['statut_classe'] = (!empty($data['statut_classe']) && in_array($data['statut_classe'], ['actif', 'inactif'], true)) ? $data['statut_classe'] : 'actif';
        $data['created_at_classe'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE classes")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Classe créée avec succès!');
        } else {
            $err = $this->model->getLastError() ?: 'Erreur lors de la création de la classe.';
            $this->error($err);
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_CLASSES');
        $id = (int)$this->post('id_classe');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        // Déduction automatique de cycle_code depuis filiere_cycles si non fourni
        if (empty($data['cycle_code']) && !empty($data['filiere_code'])) {
            $stmtCy = $this->model->getCon()->prepare("
                SELECT cycle_code FROM filiere_cycles 
                WHERE filiere_code = ? AND (niveau_code = ? OR niveau_code IS NULL OR niveau_code = '') 
                LIMIT 1
            ");
            $stmtCy->execute([$data['filiere_code'], $data['niveau_code'] ?? '']);
            $cyCode = $stmtCy->fetchColumn();
            if ($cyCode) {
                $data['cycle_code'] = $cyCode;
            }
        }

        // Génération automatique intelligente de secours si le champ libelle_classe est vide
        if (empty($data['libelle_classe']) && !empty($data['filiere_code']) && !empty($data['niveau_code'])) {
            $stmtF = $this->model->getCon()->prepare("SELECT libelle_filiere FROM filieres WHERE code_filiere = ?");
            $stmtF->execute([$data['filiere_code']]);
            $fName = $stmtF->fetchColumn();

            $stmtN = $this->model->getCon()->prepare("SELECT libelle_niveau FROM niveaux WHERE code_niveau = ?");
            $stmtN->execute([$data['niveau_code']]);
            $nName = $stmtN->fetchColumn();

            if ($fName && $nName) {
                $data['libelle_classe'] = trim($fName) . ' - ' . trim($nName);
            }
        }

        $anneeCode = !empty($data['annee_code']) ? trim($data['annee_code']) : null;
        if (!$anneeCode) {
            $existing = $this->model->getById($id);
            $anneeCode = $existing['annee_code'] ?? $this->getActiveAnneeCode();
        }

        if (!empty($data['libelle_classe'])) {
            if (!$this->checkUniquePair('classes', [
                'libelle_classe' => $data['libelle_classe'],
                'annee_code' => $anneeCode
            ], 'nom de classe', 'id_classe', $id)) return;
        }

        if (isset($data['statut_classe'])) {
            $data['statut_classe'] = in_array($data['statut_classe'], ['actif', 'inactif'], true) ? $data['statut_classe'] : 'actif';
        }
        $cols = $this->model->getCon()->query("DESCRIBE classes")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('annee_code', $cols) && !empty($anneeCode)) {
            $data['annee_code'] = $anneeCode;
        }
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Classe modifiée avec succès!');
        } else {
            $err = $this->model->getLastError() ?: 'Erreur lors de la modification de la classe.';
            $this->error($err);
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_CLASSES');
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
        $this->requirePermission('VIEW_CLASSES');
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT c.*, 
                       f.libelle_filiere,
                       n.libelle_niveau,
                       a.libelle_annee
                FROM classes c
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                LEFT JOIN annees a ON a.code_annee = c.annee_code
                WHERE c.id_classe = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { 
                $this->renderNotFound("La classe demandée est introuvable.");
                return;
            }

            $classeCode = $item['code_classe'];

            // Liste des étudiants inscrits dans cette classe
            $stmtEtu = $this->model->getCon()->prepare("
                SELECT e.*, ins.created_at_inscription, ins.statut_inscription, ins.code_inscription
                FROM etudiants e
                INNER JOIN inscriptions ins ON ins.etudiant_code = e.code_etudiant
                WHERE ins.classe_code = ? AND (ins.statut_inscription != 'annule' OR ins.statut_inscription IS NULL)
                ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
            ");
            $stmtEtu->execute([$classeCode]);
            $etudiants = $stmtEtu->fetchAll(PDO::FETCH_ASSOC);

            // Liste des matières & enseignants assignés
            $stmtMat = $this->model->getCon()->prepare("
                SELECT em.*, m.libelle_matiere, m.code_matiere,
                       u.nom_user as nom_prof,
                       u.prenom_user as prenom_prof,
                       e.grade_enseignant
                FROM enseignant_matiere em
                LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
                LEFT JOIN enseignants e ON e.code_enseignant = em.enseignant_code
                LEFT JOIN users u ON u.code_user = em.enseignant_code
                WHERE em.classe_code = ?
                ORDER BY m.libelle_matiere ASC
            ");
            $stmtMat->execute([$classeCode]);
            $matieres = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("ClasseController::details error: " . $e->getMessage());
            $this->renderNotFound("La classe demandée est introuvable.");
            return;
        }
        $this->loadView('../views/classes/details.php', [
            'item' => $item, 
            'etudiants' => $etudiants,
            'matieres' => $matieres,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->requirePermission('MANAGE_CLASSES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'classe/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'classe/list'); exit();
        }
        $this->loadView('../views/classes/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission('MANAGE_CLASSES');
        $this->loadView('../views/classes/edit.php', ['item' => []]);
    }

    public function getClassesWithScolarite()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_CLASSES', 'MANAGE_ETUDIANTS', 'VIEW_INSCRIPTIONS', 'MANAGE_INSCRIPTIONS']);

        $anneeCode = trim($_GET['annee_code'] ?? ($_POST['annee_code'] ?? ''));
        $affectationEtat = trim($_GET['affectation_etat'] ?? ($_POST['affectation_etat'] ?? ''));
        if ($affectationEtat === 'oui') $affectationEtat = 'affecte';
        if ($affectationEtat === 'non') $affectationEtat = 'non_affecte';

        if (empty($anneeCode)) {
            $anneeCode = $this->getActiveAnneeCode();
        }

        $classes = $this->model->getClassesWithScolarite($anneeCode, $affectationEtat);

        $this->json([
            'status' => 1,
            'data' => $classes,
            'annee_code' => $anneeCode,
            'total' => count($classes)
        ]);
    }

    public function reconduire()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_CLASSES');

        $anneeSourceCode = trim($_POST['annee_source_code'] ?? '');
        $anneeCibleCode = trim($_POST['annee_cible_code'] ?? '');

        if (empty($anneeSourceCode) || empty($anneeCibleCode)) {
            $this->error("Veuillez sélectionner l'année source et l'année cible.");
            return;
        }

        if ($anneeSourceCode === $anneeCibleCode) {
            $this->error("L'année source et l'année cible doivent être différentes.");
            return;
        }

        $etabCode = $this->getActiveEtablissementCode();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';

        $res = $this->model->reconduireClassesAnnee($anneeSourceCode, $anneeCibleCode, $etabCode, $userCode);

        if ($res['success']) {
            $this->success($res['message'], ['reload' => true, 'count' => $res['count'], 'skipped' => $res['skipped']]);
        } else {
            $this->error($res['message']);
        }
    }

    /**
     * Impression du Répertoire des Classes via mPDF
     */
    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission(['PRINT_CLASSES', 'VIEW_CLASSES', 'PRINT_OFFRE_ACADEMIQUE']);

        require_once __DIR__ . '/../../core/PdfService.php';

        $db = $this->model->getCon();
        $anneeCode = $this->getActiveAnneeCode();
        $anneeLibelle = $this->getActiveAnneeLibelle();

        $sql = "SELECT c.*, 
                       f.libelle_filiere, f.slug_filiere,
                       n.libelle_niveau, n.slug_niveau,
                       cy.libelle_cycle, cy.slug_cycle,
                       a.libelle_annee
                FROM classes c
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                LEFT JOIN cycles cy ON cy.code_cycle = c.cycle_code
                LEFT JOIN annees a ON a.code_annee = c.annee_code
                WHERE (c.annee_code = ? OR ? = '')
                ORDER BY cy.libelle_cycle ASC, f.libelle_filiere ASC, n.libelle_niveau ASC, c.libelle_classe ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$anneeCode, $anneeCode]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $etablissement = $this->getEtablissementConfig();

        $authSession = $_SESSION[USERS_AUTH] ?? [];
        $editeurNom = trim(($authSession['prenom_user'] ?? '') . ' ' . ($authSession['nom_user'] ?? ''));
        if (empty($editeurNom)) {
            $editeurNom = 'Direction des Études & Scolarité';
        }

        $data = [
            'etablissement' => $etablissement,
            'annee_libelle' => $anneeLibelle,
            'editeur_nom' => $editeurNom,
            'classes' => $classes
        ];

        $html = PdfService::renderTemplate('repertoire_classes.php', $data);
        $filename = 'Repertoire_Classes_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Répertoire des Classes - ' . ($etablissement['libelle_etablissement'] ?? 'GEICG'),
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 12
        ]);
    }
}