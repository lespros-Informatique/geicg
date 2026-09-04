<?php

class InscriptionController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelInscription();
    }

    public function list()
    {
        $this->requireAuth();
        $db = $this->model->getCon();
        $filieres = $db->query("SELECT code_filiere, libelle_filiere FROM filieres WHERE statut_filiere = 'actif' ORDER BY libelle_filiere ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $niveaux = $db->query("SELECT code_niveau, libelle_niveau FROM niveaux WHERE statut_niveau = 'actif' ORDER BY id_niveau ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $classes = $db->query("SELECT code_classe, libelle_classe, filiere_code, niveau_code, annee_code FROM classes WHERE statut_classe = 'actif' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $annees = $db->query("SELECT code_annee, libelle_annee FROM annees ORDER BY id_annee DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        $this->loadView('../views/inscriptions/list.php', [
            'filieres' => $filieres,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'annees' => $annees
        ]);
    }

    protected function getActiveAnneeCode(): string
    {
        if (!empty($_SESSION['annee_active_code'])) {
            return $_SESSION['annee_active_code'];
        }
        $db = $this->model->getCon();
        $stmt = $db->query("SELECT code_annee FROM annees WHERE statut_annee = 'actif' ORDER BY id_annee DESC LIMIT 1");
        $code = $stmt->fetchColumn();
        if ($code) {
            $_SESSION['annee_active_code'] = $code;
            return $code;
        }
        return '';
    }

    public function apiList()
    {
        $this->requireAuth();
        $anneeCode = $this->getActiveAnneeCode();
        $filterFiliere = trim($_GET['filiere_code'] ?? '');
        $filterNiveau = trim($_GET['niveau_code'] ?? '');
        $filterClasse = trim($_GET['classe_code'] ?? '');

        $db = $this->model->getCon();

        // 1. Récupérer tous les étudiants
        $students = $db->query("
            SELECT id_etudiant, code_etudiant, matricule_etudiant, nom_etudiant, prenom_etudiant, sexe_etudiant, telephone_etudiant, email_etudiant, photo_etudiant, statut_etudiant
            FROM etudiants
            ORDER BY id_etudiant DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 2. Inscriptions actives de l'année active (pour exclure ceux qui sont déjà inscrits/réinscrits)
        $stmtCur = $db->prepare("
            SELECT DISTINCT etudiant_code
            FROM inscriptions
            WHERE annee_code = ? AND statut_inscription != 'annule'
        ");
        $stmtCur->execute([$anneeCode]);
        $curCodes = $stmtCur->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $curSet = [];
        foreach ($curCodes as $c) {
            $curSet[trim($c)] = true;
        }

        // 3. Dernière inscription passée (N-1)
        $priorMap = [];
        $stmtPrior = $db->prepare("
            SELECT i.*, c.libelle_classe as classe_prev, c.filiere_code as filiere_prev_code, c.niveau_code as niveau_prev_code,
                   f.libelle_filiere as filiere_prev, n.libelle_niveau as niveau_prev, a.libelle_annee as annee_prev
            FROM inscriptions i
            LEFT JOIN classes c ON c.code_classe = i.classe_code
            LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
            LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
            LEFT JOIN annees a ON a.code_annee = i.annee_code
            WHERE i.annee_code != ? AND i.statut_inscription != 'annule'
            ORDER BY i.id_inscription DESC
        ");
        $stmtPrior->execute([$anneeCode]);
        while ($row = $stmtPrior->fetch(PDO::FETCH_ASSOC)) {
            $etuCode = $row['etudiant_code'];
            if (!isset($priorMap[$etuCode])) {
                $priorMap[$etuCode] = $row;
            }
        }

        $data = [];
        foreach ($students as $etu) {
            $code = $etu['code_etudiant'];
            $mat = $etu['matricule_etudiant'] ?? '';

            // Si l'étudiant est DÉJÀ inscrit pour cette année active, ON LE MASQUE STRICTEMENT DE LA LISTE !
            if (isset($curSet[$code]) || (!empty($mat) && isset($curSet[$mat]))) {
                continue;
            }

            $prev = $priorMap[$code] ?? ($priorMap[$mat] ?? null);

            // Détermination de la filière / niveau / classe N-1
            $refFiliere = $prev['filiere_prev_code'] ?? '';
            $refNiveau = $prev['niveau_prev_code'] ?? '';
            $refClasse = $prev['classe_code'] ?? '';

            // Application des filtres filière / niveau / classe
            if (!empty($filterFiliere) && $filterFiliere !== 'ALL' && $refFiliere !== $filterFiliere) {
                continue;
            }
            if (!empty($filterNiveau) && $filterNiveau !== 'ALL' && $refNiveau !== $filterNiveau) {
                continue;
            }
            if (!empty($filterClasse) && $filterClasse !== 'ALL' && $refClasse !== $filterClasse) {
                continue;
            }

            $data[] = [
                'id_etudiant' => $etu['id_etudiant'],
                'code_etudiant' => $code,
                'matricule_etudiant' => $etu['matricule_etudiant'] ?? '-',
                'nom_etudiant' => $etu['nom_etudiant'],
                'prenom_etudiant' => $etu['prenom_etudiant'],
                'nom_complet' => trim(($etu['nom_etudiant'] ?? '') . ' ' . ($etu['prenom_etudiant'] ?? '')),
                'sexe' => $etu['sexe_etudiant'] ?? 'M',
                'telephone' => $etu['telephone_etudiant'] ?? '-',
                'photo_etudiant' => $etu['photo_etudiant'] ?? '',

                // Cursus antérieur (N-1)
                'classe_precedente' => $prev['classe_prev'] ?? '',
                'filiere_precedente' => $prev['filiere_prev'] ?? '',
                'niveau_precedent' => $prev['niveau_prev'] ?? '',
                'annee_precedente' => $prev['annee_prev'] ?? ''
            ];
        }

        $this->json(['data' => $data]);
    }

    public function getStudentProfileSummary()
    {
        $this->requireAuth();
        $etudiantCode = trim($_GET['etudiant_code'] ?? ($_POST['etudiant_code'] ?? ''));

        if (empty($etudiantCode)) {
            $this->json(['status' => 0, 'message' => 'Code étudiant requis']);
            return;
        }

        $db = $this->model->getCon();

        // 1. Récupération des informations personnelles de l'étudiant et de ses parents
        $stmt = $db->prepare("
            SELECT e.*, 
                   p.nom_pere, p.telephone_pere, p.profession_pere,
                   p.nom_mere, p.telephone_mere, p.profession_mere,
                   p.nom_tuteur, p.telephone_tuteur
            FROM etudiants e
            LEFT JOIN parents p ON (p.etudiant_code = e.code_etudiant OR p.etudiant_code = e.matricule_etudiant)
            WHERE e.code_etudiant = ? OR e.matricule_etudiant = ? OR e.id_etudiant = ?
            LIMIT 1
        ");
        $stmt->execute([$etudiantCode, $etudiantCode, is_numeric($etudiantCode) ? (int)$etudiantCode : 0]);
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$etudiant) {
            $this->json(['status' => 0, 'message' => 'Étudiant introuvable']);
            return;
        }

        $anneeActive = $this->getActiveAnneeCode();
        // Vérifier si l'étudiant est déjà inscrit pour cette année active
        $stmtThisYear = $db->prepare("
            SELECT i.*, c.libelle_classe, a.libelle_annee 
            FROM inscriptions i
            LEFT JOIN classes c ON c.code_classe = i.classe_code
            LEFT JOIN annees a ON a.code_annee = i.annee_code
            WHERE (i.etudiant_code = ? OR i.etudiant_code = ?)
              AND i.annee_code = ?
              AND i.statut_inscription != 'annule'
            LIMIT 1
        ");
        $stmtThisYear->execute([$etudiant['code_etudiant'], $etudiant['matricule_etudiant'], $anneeActive]);
        $alreadyThisYear = $stmtThisYear->fetch(PDO::FETCH_ASSOC);

        // 2. Recherche complète de l'historique de l'inscription précédente (N-1)
        $stmtPrev = $db->prepare("
            SELECT i.*, 
                   c.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                   a.libelle_annee
            FROM inscriptions i
            LEFT JOIN classes c ON c.code_classe = i.classe_code
            LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
            LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
            LEFT JOIN annees a ON (a.code_annee = i.annee_code OR a.id_annee = i.annee_code)
            WHERE i.etudiant_code = ?
            ORDER BY i.id_inscription DESC
            LIMIT 1
        ");
        $stmtPrev->execute([$etudiant['code_etudiant']]);
        $prevIns = $stmtPrev->fetch(PDO::FETCH_ASSOC);

        $prevDue = 0;
        $prevPaye = 0;
        $prevSolde = 0;
        $hasHistory = false;

        if ($prevIns) {
            $hasHistory = true;
            $prevDue = (float)($prevIns['montant_scolarite_inscription'] ?? 0);
            
            // Calcul des paiements de l'inscription précédente
            $stmtP = $db->prepare("
                SELECT SUM(montant_paiement) as total_paye 
                FROM paiements 
                WHERE inscription_code = ? AND statut_paiement != 'annule'
            ");
            $stmtP->execute([$prevIns['code_inscription']]);
            $rowP = $stmtP->fetch(PDO::FETCH_ASSOC);
            $prevPaye = (float)($rowP['total_paye'] ?? 0);
            $prevSolde = max(0, $prevDue - $prevPaye);
        }

        $nomComplet = trim(($etudiant['nom_etudiant'] ?? '') . ' ' . ($etudiant['prenom_etudiant'] ?? ''));
        $dateNaissFmt = !empty($etudiant['date_naissance_etudiant']) ? date('d/m/Y', strtotime($etudiant['date_naissance_etudiant'])) : 'Non renseignée';

        // Identification du parent référent
        $parentNom = $etudiant['nom_tuteur'] ?: ($etudiant['nom_pere'] ?: ($etudiant['nom_mere'] ?: 'Non renseigné'));
        $parentTel = $etudiant['telephone_tuteur'] ?: ($etudiant['telephone_pere'] ?: ($etudiant['telephone_mere'] ?: 'Non renseigné'));
        $parentRole = $etudiant['nom_tuteur'] ? 'Tuteur' : ($etudiant['nom_pere'] ? 'Père' : ($etudiant['nom_mere'] ? 'Mère' : 'Parent'));
        $parentProf = $etudiant['profession_pere'] ?: ($etudiant['profession_mere'] ?: 'Non renseignée');

        $this->json([
            'status' => 1,
            'data' => [
                'code_etudiant' => $etudiant['code_etudiant'],
                'matricule' => $etudiant['matricule_etudiant'] ?? '-',
                'nom_famille' => $etudiant['nom_etudiant'] ?? '',
                'prenom' => $etudiant['prenom_etudiant'] ?? '',
                'nom_complet' => $nomComplet,
                'telephone' => $etudiant['telephone_etudiant'] ?? 'Non renseigné',
                'email' => $etudiant['email_etudiant'] ?? 'Non renseigné',
                'date_naissance' => $dateNaissFmt,
                'lieu_naissance' => $etudiant['lieu_naissance_etudiant'] ?? 'Non renseigné',
                'sexe' => $etudiant['sexe_etudiant'] ?? 'M',
                'nationalite' => $etudiant['nationalite_etudiant'] ?? 'Ivoirienne',
                'residence' => $etudiant['lieu_residence_etudiant'] ?? 'Non renseigné',
                'parent_nom' => $parentNom,
                'parent_tel' => $parentTel,
                'parent_role' => $parentRole,
                'parent_profession' => $parentProf,
                'nom_pere' => $etudiant['nom_pere'] ?? '',
                'telephone_pere' => $etudiant['telephone_pere'] ?? '',
                'nom_mere' => $etudiant['nom_mere'] ?? '',
                'telephone_mere' => $etudiant['telephone_mere'] ?? '',
                'nom_tuteur' => $etudiant['nom_tuteur'] ?? '',
                'telephone_tuteur' => $etudiant['telephone_tuteur'] ?? '',
                'has_history' => $hasHistory,
                'derniere_filiere' => $prevIns['libelle_filiere'] ?? 'Non définie',
                'dernier_niveau' => $prevIns['libelle_niveau'] ?? 'Non défini',
                'derniere_classe' => $prevIns['libelle_classe'] ?? 'Nouvel inscrit',
                'derniere_classe_code' => $prevIns['classe_code'] ?? '',
                'dernier_niveau_code' => $prevIns['niveau_code'] ?? '',
                'derniere_filiere_code' => $prevIns['filiere_code'] ?? '',
                'derniere_annee' => $prevIns['libelle_annee'] ?? '',
                'prev_affectation_etat' => (($prevIns['affectation_etat'] ?? '') === 'affecte' || ($prevIns['affectation_etat'] ?? '') === 'oui') ? 'affecte' : 'non_affecte',
                'prev_regime' => (($prevIns['affectation_etat'] ?? '') === 'affecte' || ($prevIns['affectation_etat'] ?? '') === 'oui') ? 'Affecté (État)' : 'Non Affecté (Privé)',
                'prev_scolarite' => $prevDue,
                'prev_paye' => $prevPaye,
                'prev_solde' => $prevSolde,
                'statut_etudiant' => $etudiant['statut_etudiant'] ?? 'actif',
                'is_already_registered_this_year' => !empty($alreadyThisYear),
                'already_registered_classe' => $alreadyThisYear['libelle_classe'] ?? '',
                'already_registered_code' => $alreadyThisYear['code_inscription'] ?? '',
                'already_registered_annee' => $alreadyThisYear['libelle_annee'] ?? '',
                'accessoires_etudiant' => (function() use ($db, $etudiantCode, $anneeCode) {
                    $stmt = $db->prepare("
                        SELECT a.code_accessoire, a.libelle_accessoire, COALESCE(ai.statut_accessoire_inscription, 'actif') as statut
                        FROM accessoire_inscription ai
                        JOIN inscriptions i ON i.code_inscription = ai.inscription_code
                        JOIN accessoires a ON a.code_accessoire = ai.accessoire_code
                        WHERE i.etudiant_code = ?
                        ORDER BY (CASE WHEN ai.annee_code = ? THEN 1 ELSE 2 END), ai.id_accessoire_inscription DESC
                    ");
                    $stmt->execute([$etudiantCode, $anneeCode]);
                    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($res)) {
                        // Récupérer les kits/accessoires actifs configurés dans l'établissement
                        $stmtAll = $db->query("SELECT code_accessoire, libelle_accessoire, 'disponible' as statut FROM accessoires WHERE statut_accessoire = 'actif'");
                        $res = $stmtAll ? $stmtAll->fetchAll(PDO::FETCH_ASSOC) : [];
                    }
                    return $res;
                })()
            ]
        ]);
    }

    public function getTuitionByClass()
    {
        $this->requireAuth();
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        $affectationEtat = trim($_GET['affectation_etat'] ?? ($_POST['affectation_etat'] ?? 'non_affecte'));
        if ($affectationEtat === 'oui') $affectationEtat = 'affecte';
        if ($affectationEtat === 'non') $affectationEtat = 'non_affecte';
        if ($affectationEtat !== 'affecte') $affectationEtat = 'non_affecte';

        if (empty($classeCode)) {
            $this->json(['status' => 0, 'message' => 'Classe non spécifiée']);
            return;
        }

        $db = $this->model->getCon();

        // Récupérer la classe avec ses libellés filière, niveau et année
        $stmtCl = $db->prepare("
            SELECT c.*, f.libelle_filiere, n.libelle_niveau, a.libelle_annee 
            FROM classes c
            LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
            LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
            LEFT JOIN annees a ON a.code_annee = c.annee_code
            WHERE c.code_classe = ? 
            LIMIT 1
        ");
        $stmtCl->execute([$classeCode]);
        $classe = $stmtCl->fetch(PDO::FETCH_ASSOC);

        if (!$classe) {
            $this->json(['status' => 0, 'message' => 'Classe introuvable']);
            return;
        }

        $filiereCode = $classe['filiere_code'] ?? '';
        $niveauCode = $classe['niveau_code'] ?? '';
        $classAnneeCode = $classe['annee_code'] ?? '';
        $activeAnneeCode = $this->getActiveAnneeCode();

        // 1. Trouver le tarif de scolarité actif pour cette classe, l'année active et ce statut d'affectation
        $stmtSco = $db->prepare("
            SELECT * FROM scolarites 
            WHERE filiere_code = ? 
              AND statut_scolarite = 'actif'
            ORDER BY 
              (CASE 
                WHEN (annee_code = ? OR annee_code = ? OR annee_code = '' OR annee_code IS NULL) 
                     AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL) 
                     AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL) THEN 1
                WHEN (annee_code = ? OR annee_code = ?) 
                     AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL) THEN 2
                WHEN (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL) THEN 3
                ELSE 4 END), 
              id_scolarite DESC
            LIMIT 1
        ");
        $stmtSco->execute([
            $filiereCode,
            $activeAnneeCode, $classAnneeCode, $niveauCode, $affectationEtat,
            $activeAnneeCode, $classAnneeCode, $affectationEtat,
            $affectationEtat
        ]);
        $sco = $stmtSco->fetch(PDO::FETCH_ASSOC);

        if (!$sco) {
            $stmtSco = $db->prepare("
                SELECT * FROM scolarites 
                WHERE filiere_code = ? AND statut_scolarite = 'actif'
                ORDER BY id_scolarite DESC
                LIMIT 1
            ");
            $stmtSco->execute([$filiereCode]);
            $sco = $stmtSco->fetch(PDO::FETCH_ASSOC);
        }

        $montantScolarite = $sco ? (float)$sco['montant_scolarite'] : 0;
        $affectationEtatFinal = $sco['affectation_etat'] ?? $affectationEtat;
        $codeScolarite = $sco['code_scolarite'] ?? '';

        // 2. Récupérer TOUTES les tranches de scolarité associées
        $tranches = [];
        if (!empty($codeScolarite)) {
            $stmtTr = $db->prepare("
                SELECT * FROM tranches_scolarite 
                WHERE scolarite_code = ? 
                  AND statut_tranche = 'actif'
                ORDER BY id_tranche ASC
            ");
            $stmtTr->execute([$codeScolarite]);
            $tranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC);
        }

        // Formater les tranches avec pourcentages et dates lisibles
        foreach ($tranches as &$tr) {
            $mt = (float)($tr['montant_tranche'] ?? 0);
            $tr['montant_tranche_num'] = $mt;
            $tr['montant_tranche_formate'] = number_format($mt, 0, ',', ' ') . ' FCFA';
            $tr['date_limite_formatee'] = !empty($tr['date_limite']) ? date('d/m/Y', strtotime($tr['date_limite'])) : 'Non définie';
            $tr['pourcentage'] = ($montantScolarite > 0) ? round(($mt / $montantScolarite) * 100) : 0;
        }
        unset($tr);

        $fraisInscription = 0;
        $libellePremiereTranche = '1ère tranche';
        $dateLimiteTranche = '';

        if (!empty($tranches)) {
            $firstTranche = $tranches[0];
            $fraisInscription = (float)$firstTranche['montant_tranche'];
            $libellePremiereTranche = $firstTranche['libelle_tranche'] ?: $libellePremiereTranche;
            $dateLimiteTranche = $firstTranche['date_limite_formatee'];
        }

        $this->json([
            'status' => 1,
            'data' => [
                'classe_code' => $classe['code_classe'],
                'libelle_classe' => $classe['libelle_classe'],
                'filiere_code' => $filiereCode,
                'libelle_filiere' => $classe['libelle_filiere'] ?? '',
                'niveau_code' => $niveauCode,
                'libelle_niveau' => $classe['libelle_niveau'] ?? '',
                'annee_code' => $anneeCode,
                'libelle_annee' => $classe['libelle_annee'] ?? '',
                'affectation_etat' => $affectationEtatFinal,
                'montant_scolarite' => $montantScolarite,
                'montant_scolarite_formate' => number_format($montantScolarite, 0, ',', ' ') . ' FCFA',
                'frais_inscription' => $fraisInscription,
                'frais_inscription_formate' => number_format($fraisInscription, 0, ',', ' ') . ' FCFA',
                'libelle_premiere_tranche' => $libellePremiereTranche,
                'date_limite_tranche' => $dateLimiteTranche,
                'nombre_tranches' => count($tranches),
                'tranches' => $tranches
            ]
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);

        $db = $this->model->getCon();
        $modeInscription = $data['mode_inscription'] ?? 'existant';

        // 1. CAS NOUVEAU DOSSIER ÉTUDIANT (Création Étudiant + Parent + Inscription)
        if ($modeInscription === 'nouveau' || (!empty($data['nom_etudiant']) && !empty($data['prenom_etudiant']))) {
            $nomEtudiant = trim($data['nom_etudiant'] ?? '');
            $prenomEtudiant = trim($data['prenom_etudiant'] ?? '');
            $telephoneEtudiant = trim($data['telephone_etudiant'] ?? '');

            if (empty($nomEtudiant) || empty($prenomEtudiant)) {
                $this->error("Veuillez renseigner le nom et les prénoms de l'étudiant.");
                return;
            }

            if (empty($data['classe_code'])) {
                $this->error("Veuillez choisir la classe d'affectation.");
                return;
            }

            $matriculeEtudiant = trim($data['matricule_etudiant'] ?? '');
            if (empty($matriculeEtudiant)) {
                $matriculeEtudiant = $this->validator->generateCode('etudiants', 'matricule_etudiant', 'ETU-' . date('Y') . '-', 4);
            }

            $codeEtudiant = $this->validator->generateCode('etudiants', 'code_etudiant', 'ETU-', 8);

            try {
                $db->beginTransaction();

                // A. Création Étudiant
                $stmtEtu = $db->prepare("
                    INSERT INTO etudiants 
                    (code_etudiant, matricule_etudiant, nom_etudiant, prenom_etudiant, sexe_etudiant, date_naissance_etudiant, lieu_naissance_etudiant, nationalite_etudiant, telephone_etudiant, email_etudiant, lieu_residence_etudiant, user_code, etablissement_code, statut_etudiant, created_at_etudiant)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
                ");
                $stmtEtu->execute([
                    $codeEtudiant,
                    $matriculeEtudiant,
                    $nomEtudiant,
                    $prenomEtudiant,
                    $data['sexe_etudiant'] ?? 'M',
                    !empty($data['date_naissance_etudiant']) ? $data['date_naissance_etudiant'] : null,
                    !empty($data['lieu_naissance_etudiant']) ? $data['lieu_naissance_etudiant'] : null,
                    !empty($data['nationalite_etudiant']) ? $data['nationalite_etudiant'] : 'Ivoirienne',
                    $telephoneEtudiant,
                    !empty($data['email_etudiant']) ? $data['email_etudiant'] : null,
                    !empty($data['lieu_residence_etudiant']) ? $data['lieu_residence_etudiant'] : null,
                    $userCode,
                    $etabCode
                ]);

                // B. Création Parent / Tuteur (si renseigné)
                if (!empty($data['nom_tuteur']) || !empty($data['nom_pere']) || !empty($data['nom_mere']) || !empty($data['telephone_tuteur']) || !empty($data['telephone_pere'])) {
                    $codeParent = $this->validator->generateCode('parents', 'code_parent', 'PAR-', 8);
                    $stmtPar = $db->prepare("
                        INSERT INTO parents 
                        (code_parent, etudiant_code, nom_pere, telephone_pere, profession_pere, nom_mere, telephone_mere, profession_mere, nom_tuteur, telephone_tuteur, user_code, etablissement_code, created_at_parent)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmtPar->execute([
                        $codeParent,
                        $codeEtudiant,
                        $data['nom_pere'] ?? null,
                        $data['telephone_pere'] ?? null,
                        $data['profession_pere'] ?? null,
                        $data['nom_mere'] ?? null,
                        $data['telephone_mere'] ?? null,
                        $data['profession_mere'] ?? null,
                        $data['nom_tuteur'] ?? null,
                        $data['telephone_tuteur'] ?? null,
                        $userCode,
                        $etabCode
                    ]);
                }

                // C. Création Inscription
                $codeInscription = $this->validator->generateCode('inscriptions', 'code_inscription', 'INS-', 8);
                $stmtIns = $db->prepare("
                    INSERT INTO inscriptions 
                    (code_inscription, etudiant_code, classe_code, montant_scolarite_inscription, annee_code, user_code, etablissement_code, statut_inscription, affectation_etat, created_at_inscription)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'valide', 'non', NOW())
                ");
                $stmtIns->execute([
                    $codeInscription,
                    $codeEtudiant,
                    $data['classe_code'],
                    (float)($data['montant_scolarite_inscription'] ?? 0),
                    $anneeCode,
                    $userCode,
                    $etabCode
                ]);

                $db->commit();
                $this->success("Dossier d'inscription créé avec succès pour l'étudiant $nomEtudiant $prenomEtudiant !");
                return;
            } catch (Exception $e) {
                $db->rollBack();
                $this->error("Erreur lors de l'enregistrement du dossier complet : " . $e->getMessage());
                return;
            }
        }

        // 2. CAS RÉINSCRIPTION ÉTUDIANT EXISTANT
        if (empty($data['etudiant_code'])) {
            $this->error("Veuillez sélectionner un étudiant.");
            return;
        }

        if (empty($data['classe_code'])) {
            $this->error("Veuillez choisir la classe d'affectation.");
            return;
        }

        // Vérification absolue anti-doublon : L'étudiant est-il déjà inscrit/réinscrit pour l'année active ?
        $stmtCheck = $db->prepare("
            SELECT i.id_inscription, i.code_inscription, c.libelle_classe, a.libelle_annee 
            FROM inscriptions i
            LEFT JOIN classes c ON c.code_classe = i.classe_code
            LEFT JOIN annees a ON a.code_annee = i.annee_code
            WHERE (i.etudiant_code = ? OR i.etudiant_code = (SELECT matricule_etudiant FROM etudiants WHERE code_etudiant = ? LIMIT 1))
              AND i.annee_code = ?
              AND i.statut_inscription != 'annule'
            LIMIT 1
        ");
        $stmtCheck->execute([$data['etudiant_code'], $data['etudiant_code'], $anneeCode]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $this->error("Cet étudiant est déjà inscrit/réinscrit pour l'année académique " . ($existing['libelle_annee'] ?? 'en cours') . " dans la classe " . ($existing['libelle_classe'] ?? '') . " [Réf: " . $existing['code_inscription'] . "]. Une double réinscription pour la même session est impossible.");
            return;
        }

        // Récupération sécurisée du barème officiel côté backend
        $affectationEtat = (!empty($data['affectation_etat']) && in_array($data['affectation_etat'], ['affecte', 'oui'])) ? 'affecte' : 'non_affecte';
        $data['affectation_etat'] = ($affectationEtat === 'affecte') ? 'oui' : 'non';

        $stmtCl = $db->prepare("SELECT filiere_code, niveau_code, annee_code FROM classes WHERE code_classe = ? LIMIT 1");
        $stmtCl->execute([$data['classe_code']]);
        $cl = $stmtCl->fetch(PDO::FETCH_ASSOC);

        $officialScolarite = 0;
        if ($cl) {
            $stmtSco = $db->prepare("
                SELECT montant_scolarite FROM scolarites 
                WHERE filiere_code = ? 
                  AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
                  AND (annee_code = ? OR annee_code = '' OR annee_code IS NULL)
                  AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
                  AND statut_scolarite = 'actif'
                ORDER BY 
                  (CASE WHEN annee_code = ? AND niveau_code = ? AND affectation_etat = ? THEN 1
                        WHEN niveau_code = ? AND affectation_etat = ? THEN 2
                        WHEN affectation_etat = ? THEN 3
                        ELSE 4 END), 
                  id_scolarite DESC
                LIMIT 1
            ");
            $stmtSco->execute([
                $cl['filiere_code'], $cl['niveau_code'], $cl['annee_code'], $affectationEtat,
                $cl['annee_code'], $cl['niveau_code'], $affectationEtat,
                $cl['niveau_code'], $affectationEtat,
                $affectationEtat
            ]);
            $scol = $stmtSco->fetch(PDO::FETCH_ASSOC);
            if ($scol) {
                $officialScolarite = (float)$scol['montant_scolarite'];
            }
        }

        // Forcer le montant officiel et la date d'inscription côté backend pour garantir l'intégrité
        $data['montant_scolarite_inscription'] = $officialScolarite;
        $data['date_inscription'] = date('Y-m-d');

        if (empty($data['code_inscription'])) {
            $data['code_inscription'] = $this->validator->generateCode('inscriptions', 'code_inscription', 'INS-', 8);
        }
        $data['statut_inscription'] = $data['statut_inscription'] ?? 'valide';
        $data['created_at_inscription'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Réinscription enregistrée avec succès !');
        } else {
            $this->error("Erreur lors de l'enregistrement de la réinscription");
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_inscription');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        $db = $this->model->getCon();

        // Récupération sécurisée du barème officiel côté backend si la classe ou le statut change
        if (!empty($data['classe_code'])) {
            $affectationEtat = (!empty($data['affectation_etat']) && in_array($data['affectation_etat'], ['affecte', 'oui'])) ? 'affecte' : 'non_affecte';
            $data['affectation_etat'] = ($affectationEtat === 'affecte') ? 'oui' : 'non';

            $stmtCl = $db->prepare("SELECT filiere_code, niveau_code, annee_code FROM classes WHERE code_classe = ? LIMIT 1");
            $stmtCl->execute([$data['classe_code']]);
            $cl = $stmtCl->fetch(PDO::FETCH_ASSOC);

            if ($cl) {
                $stmtSco = $db->prepare("
                    SELECT montant_scolarite FROM scolarites 
                    WHERE filiere_code = ? 
                      AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
                      AND (annee_code = ? OR annee_code = '' OR annee_code IS NULL)
                      AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
                      AND statut_scolarite = 'actif'
                    ORDER BY 
                      (CASE WHEN annee_code = ? AND niveau_code = ? AND affectation_etat = ? THEN 1
                            WHEN niveau_code = ? AND affectation_etat = ? THEN 2
                            WHEN affectation_etat = ? THEN 3
                            ELSE 4 END), 
                      id_scolarite DESC
                    LIMIT 1
                ");
                $stmtSco->execute([
                    $cl['filiere_code'], $cl['niveau_code'], $cl['annee_code'], $affectationEtat,
                    $cl['annee_code'], $cl['niveau_code'], $affectationEtat,
                    $cl['niveau_code'], $affectationEtat,
                    $affectationEtat
                ]);
                $scol = $stmtSco->fetch(PDO::FETCH_ASSOC);
                if ($scol) {
                    $data['montant_scolarite_inscription'] = (float)$scol['montant_scolarite'];
                }
            }
        }

        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Inscription modifiée avec succès !');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = ['valide', 'solde', 'annule'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'statut_inscription');
            } else {
                $success = $this->model->toggleStatus($id);
            }
            if ($success) {
                $this->success('Statut de l\'inscription mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Inscription introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT ins.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant, e.sexe_etudiant, e.date_naissance_etudiant,
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       a.libelle_annee
                FROM inscriptions ins
                LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN annees a ON a.code_annee = ins.annee_code
                WHERE ins.id_inscription = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { 
                $this->renderNotFound("L'inscription demandée est introuvable.");
                return;
            }

            // Paiements pour cette inscription
            $stmtP = $this->model->getCon()->prepare("
                SELECT * FROM paiements 
                WHERE inscription_code = ?
                ORDER BY date_paiement DESC
            ");
            $stmtP->execute([$item['code_inscription']]);
            $paiements = $stmtP->fetchAll(PDO::FETCH_ASSOC);

            $scolarite = (float)($item['montant_scolarite_inscription'] ?? 0);
            $totalPaye = 0;
            foreach ($paiements as $p) {
                if (($p['statut_paiement'] ?? '') !== 'annule') {
                    $totalPaye += (float)($p['montant_paiement'] ?? 0);
                }
            }
            $solde = max(0, $scolarite - $totalPaye);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("InscriptionController::details error: " . $e->getMessage());
            $this->renderNotFound("L'inscription demandée est introuvable.");
            return;
        }
        $this->loadView('../views/inscriptions/details.php', [
            'item' => $item, 
            'paiements' => $paiements,
            'totalPaye' => $totalPaye,
            'solde' => $solde,
            'scolarite' => $scolarite,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'inscription/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'inscription/list'); exit();
        }
        $this->loadView('../views/inscriptions/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/inscriptions/edit.php', ['item' => []]);
    }
}
