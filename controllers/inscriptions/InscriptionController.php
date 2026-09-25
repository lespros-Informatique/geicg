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
        $this->requirePermission('VIEW_INSCRIPTIONS');
        $db = $this->model->getCon();

        $activeAnneeCode = $this->getActiveAnneeCode();

        // Récupérer la liste des années académiques clôturées pour la réinscription
        $stmtCloture = $db->query("SELECT id_annee, code_annee, libelle_annee, statut_annee FROM annees WHERE statut_annee = 'cloture' ORDER BY id_annee DESC");
        $annees = $stmtCloture ? ($stmtCloture->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

        if (empty($annees)) {
            $stmtFallback = $db->prepare("SELECT id_annee, code_annee, libelle_annee, statut_annee FROM annees WHERE code_annee != ? ORDER BY id_annee DESC");
            $stmtFallback->execute([$activeAnneeCode]);
            $annees = $stmtFallback->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        // Année sélectionnée par défaut (la plus récente des années clôturées)
        $selectedAnneeCode = !empty($_GET['annee_code']) ? trim($_GET['annee_code']) : ($annees[0]['code_annee'] ?? '');

        $filieres = $db->query("SELECT code_filiere, libelle_filiere FROM filieres WHERE statut_filiere = 'actif' ORDER BY libelle_filiere ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $niveaux = $db->query("SELECT code_niveau, libelle_niveau FROM niveaux WHERE statut_niveau = 'actif' ORDER BY id_niveau ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $classes = $db->query("SELECT code_classe, libelle_classe, cycle_code, filiere_code, niveau_code, annee_code FROM classes WHERE statut_classe = 'actif' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->loadView('../views/inscriptions/list.php', [
            'filieres' => $filieres,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'annees' => $annees,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    protected function getActiveAnneeCode(): string
    {
        if (!empty($_SESSION['annee_active_code'])) {
            return $_SESSION['annee_active_code'];
        }
        $db = $this->model->getCon();
        $stmt = $db->query("SELECT code_annee, libelle_annee FROM annees WHERE statut_annee = 'actif' ORDER BY id_annee DESC LIMIT 1");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        if (!$row) {
            $stmtFallback = $db->query("SELECT code_annee, libelle_annee FROM annees ORDER BY id_annee DESC LIMIT 1");
            $row = $stmtFallback ? $stmtFallback->fetch(PDO::FETCH_ASSOC) : null;
        }
        if ($row) {
            $_SESSION['annee_active_code'] = $row['code_annee'];
            $_SESSION['annee_active_libelle'] = $row['libelle_annee'];
            return $row['code_annee'];
        }
        return '';
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission('VIEW_INSCRIPTIONS');
        $db = $this->model->getCon();

        $activeAnneeCode = $this->getActiveAnneeCode();
        $filterAnnee = trim($_GET['annee_code'] ?? '');
        $filterFiliere = trim($_GET['filiere_code'] ?? '');
        $filterNiveau = trim($_GET['niveau_code'] ?? '');
        $filterClasse = trim($_GET['classe_code'] ?? '');

        if (empty($filterAnnee) || $filterAnnee === 'ALL') {
            $this->json(['data' => []]);
            return;
        }

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
        $stmtCur->execute([$activeAnneeCode]);
        $curCodes = $stmtCur->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $curSet = [];
        foreach ($curCodes as $c) {
            $curSet[trim($c)] = true;
        }

        // 3. Récupération de la dernière inscription passée selon le filtre d'année sélectionné (id_annee < active)
        $priorMap = [];
        if (!empty($filterAnnee) && $filterAnnee !== 'ALL') {
            $stmtPrior = $db->prepare("
                SELECT i.*, c.libelle_classe as classe_prev, c.filiere_code as filiere_prev_code, c.niveau_code as niveau_prev_code,
                       f.libelle_filiere as filiere_prev, n.libelle_niveau as niveau_prev, a.libelle_annee as annee_prev
                FROM inscriptions i
                LEFT JOIN classes c ON c.code_classe = i.classe_code
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code
                WHERE i.annee_code = ? AND i.statut_inscription != 'annule'
                ORDER BY i.id_inscription DESC
            ");
            $stmtPrior->execute([$filterAnnee]);
        } else {
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
            $stmtPrior->execute([$activeAnneeCode]);
        }

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
            if (!$prev) {
                continue;
            }

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
        $this->requirePermission('VIEW_INSCRIPTIONS');
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

        $anneeCodeParam = trim($_GET['annee_code'] ?? ($_POST['annee_code'] ?? ''));
        $anneeActive = !empty($anneeCodeParam) ? $anneeCodeParam : $this->getActiveAnneeCode();
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
        $suggestedNextClassCode = '';
        $suggestedNextClassLibelle = '';

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

            // Détection automatique de la classe suivante N+1 (Smart Class Progression)
            if (!empty($prevIns['filiere_code']) && !empty($prevIns['niveau_code'])) {
                $stmtNiv = $db->prepare("SELECT id_niveau FROM niveaux WHERE code_niveau = ? LIMIT 1");
                $stmtNiv->execute([$prevIns['niveau_code']]);
                $currNiv = $stmtNiv->fetch(PDO::FETCH_ASSOC);
                if ($currNiv) {
                    $nextNivId = (int)$currNiv['id_niveau'] + 1;
                    $stmtNextCl = $db->prepare("
                        SELECT c.code_classe, c.libelle_classe 
                        FROM classes c
                        JOIN niveaux n ON n.code_niveau = c.niveau_code
                        WHERE c.filiere_code = ? 
                          AND n.id_niveau = ?
                          AND (c.annee_code = ? OR ? = '')
                          AND c.statut_classe = 'actif'
                        ORDER BY c.id_classe ASC
                        LIMIT 1
                    ");
                    $stmtNextCl->execute([$prevIns['filiere_code'], $nextNivId, $anneeActive, $anneeActive]);
                    $nextCl = $stmtNextCl->fetch(PDO::FETCH_ASSOC);
                    if ($nextCl) {
                        $suggestedNextClassCode = $nextCl['code_classe'];
                        $suggestedNextClassLibelle = $nextCl['libelle_classe'];
                    }
                }
            }
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
                'suggested_next_class_code' => $suggestedNextClassCode,
                'suggested_next_class_libelle' => $suggestedNextClassLibelle,
                'prev_affectation_etat' => (($prevIns['affectation_etat'] ?? '') === 'affecte' || ($prevIns['affectation_etat'] ?? '') === 'oui') ? 'affecte' : 'non_affecte',
                'prev_regime' => (($prevIns['affectation_etat'] ?? '') === 'affecte' || ($prevIns['affectation_etat'] ?? '') === 'oui') ? 'Affecté (État)' : 'Non Affecté (Privé)',
                'prev_scolarite' => $prevDue,
                'prev_paye' => $prevPaye,
                'prev_solde' => $prevSolde,
                'quitus_status' => ($prevSolde <= 0) ? 'ok' : 'impaye',
                'statut_etudiant' => $etudiant['statut_etudiant'] ?? 'actif',
                'photo_url' => !empty($prevIns['photo_inscription']) ? RACINE . ltrim($prevIns['photo_inscription'], '/') : (!empty($etudiant['photo_etudiant']) ? RACINE . ltrim($etudiant['photo_etudiant'], '/') : ''),
                'is_already_registered_this_year' => !empty($alreadyThisYear),
                'already_registered_classe' => $alreadyThisYear['libelle_classe'] ?? '',
                'already_registered_code' => $alreadyThisYear['code_inscription'] ?? '',
                'already_registered_annee' => $alreadyThisYear['libelle_annee'] ?? '',
                'history_payments' => (function() use ($db, $etudiant) {
                    $stmtP = $db->prepare("
                        SELECT p.code_paiement, p.montant_paiement, p.mode_paiement, p.date_paiement, p.reference_paiement, p.statut_paiement, a.libelle_annee
                        FROM paiements p
                        LEFT JOIN inscriptions i ON i.code_inscription = p.inscription_code
                        LEFT JOIN annees a ON (a.code_annee = i.annee_code OR a.id_annee = i.annee_code)
                        WHERE (i.etudiant_code = ? OR i.etudiant_code = ?)
                          AND p.statut_paiement != 'annule'
                        ORDER BY p.date_paiement DESC, p.id_paiement DESC
                    ");
                    $stmtP->execute([$etudiant['code_etudiant'], $etudiant['matricule_etudiant'] ?? '']);
                    return $stmtP->fetchAll(PDO::FETCH_ASSOC) ?: [];
                })(),
                'accessoires_etudiant' => (function() use ($db, $etudiantCode, $anneeActive) {
                    $stmt = $db->prepare("
                        SELECT a.code_accessoire, a.libelle_accessoire, COALESCE(ai.statut_accessoire_inscription, 'actif') as statut
                        FROM accessoire_inscription ai
                        JOIN inscriptions i ON i.code_inscription = ai.inscription_code
                        JOIN accessoires a ON a.code_accessoire = ai.accessoire_code
                        WHERE i.etudiant_code = ?
                        ORDER BY (CASE WHEN ai.annee_code = ? THEN 1 ELSE 2 END), ai.id_accessoire_inscription DESC
                    ");
                    $stmt->execute([$etudiantCode, $anneeActive]);
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
        $this->requirePermission(['VIEW_INSCRIPTIONS', 'MANAGE_ETUDIANTS', 'VIEW_CLASSES', 'MANAGE_INSCRIPTIONS']);
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        $anneeCodeReq = trim($_GET['annee_code'] ?? ($_POST['annee_code'] ?? ''));
        $affectationEtat = trim($_GET['affectation_etat'] ?? ($_POST['affectation_etat'] ?? 'non_affecte'));
        if ($affectationEtat === 'oui') $affectationEtat = 'affecte';
        if ($affectationEtat === 'non') $affectationEtat = 'non_affecte';
        if ($affectationEtat !== 'affecte') $affectationEtat = 'non_affecte';

        if (empty($classeCode)) {
            $this->json(['status' => 0, 'message' => 'Classe non spécifiée']);
            return;
        }

        $db = $this->model->getCon();

        // Récupérer la classe avec ses libellés filière, type de filière, niveau et année
        $stmtCl = $db->prepare("
            SELECT c.*, f.libelle_filiere, f.type_filiere, n.libelle_niveau, a.libelle_annee 
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
        $typeFiliere = $classe['type_filiere'] ?? 'INDUSTRIELLE';
        $niveauCode = $classe['niveau_code'] ?? '';
        $classAnneeCode = $classe['annee_code'] ?? '';
        $activeAnneeCode = !empty($anneeCodeReq) ? $anneeCodeReq : ($classAnneeCode ?: $this->getActiveAnneeCode());

        // 1. Trouver le tarif de scolarité actif STRICTEMENT pour cette classe, l'année active et ce statut d'affectation
        $stmtSco = $db->prepare("
            SELECT * FROM scolarites 
            WHERE filiere_code = ? 
              AND (annee_code = ? OR ? = '')
              AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
              AND affectation_etat = ?
              AND statut_scolarite = 'actif'
            ORDER BY 
              (CASE WHEN annee_code = ? THEN 1 ELSE 2 END),
              (CASE WHEN niveau_code = ? THEN 1 ELSE 2 END),
              id_scolarite DESC
            LIMIT 1
        ");
        $stmtSco->execute([
            $filiereCode,
            $activeAnneeCode, $activeAnneeCode,
            $niveauCode,
            $affectationEtat,
            $activeAnneeCode,
            $niveauCode
        ]);
        $sco = $stmtSco->fetch(PDO::FETCH_ASSOC);

        if (!$sco) {
            $this->json([
                'status' => 0,
                'message' => 'Aucun tarif de scolarité actif n\'a été trouvé pour le régime sélectionné (' . $affectationEtat . ') pour cette classe.'
            ]);
            return;
        }

        $montantScolarite = (float)$sco['montant_scolarite'];
        $affectationEtatFinal = $sco['affectation_etat'];
        $codeScolarite = $sco['code_scolarite'];

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

        // 3. Récupérer le tarif des Frais Annexes depuis la table dédiée `frais_annexes` par Type de Filière et Niveau (Filtre strict sur la catégorie 'inscription')
        $modelFraisAnnexe = new ModelFraisAnnexe();
        $totalFraisAnnexes = $modelFraisAnnexe->getMontantByTypeFiliere($typeFiliere, $activeAnneeCode, $niveauCode, 'inscription');
        $fraisAnnexeDetails = $modelFraisAnnexe->getFraisAnnexeDetails($typeFiliere, $activeAnneeCode, $niveauCode, 'inscription');

        $this->json([
            'status' => 1,
            'data' => [
                'classe_code' => $classe['code_classe'],
                'libelle_classe' => $classe['libelle_classe'],
                'filiere_code' => $filiereCode,
                'libelle_filiere' => $classe['libelle_filiere'] ?? '',
                'type_filiere' => $typeFiliere,
                'libelle_type_filiere' => ($typeFiliere === 'INDUSTRIELLE') ? 'Filière Industrielle' : (($typeFiliere === 'TERTIAIRE') ? 'Filière Tertiaire' : 'Toutes Filières'),
                'niveau_code' => $niveauCode,
                'libelle_niveau' => $classe['libelle_niveau'] ?? '',
                'annee_code' => $activeAnneeCode,
                'libelle_annee' => $classe['libelle_annee'] ?? '',
                'affectation_etat' => $affectationEtatFinal,
                'montant_scolarite' => $montantScolarite,
                'montant_scolarite_formate' => number_format($montantScolarite, 0, ',', ' ') . ' FCFA',
                'frais_inscription' => $fraisInscription,
                'frais_inscription_formate' => number_format($fraisInscription, 0, ',', ' ') . ' FCFA',
                'libelle_premiere_tranche' => $libellePremiereTranche,
                'date_limite_tranche' => $dateLimiteTranche,
                'nombre_tranches' => count($tranches),
                'tranches' => $tranches,
                'total_frais_annexes' => $totalFraisAnnexes,
                'total_frais_annexes_formate' => number_format($totalFraisAnnexes, 0, ',', ' ') . ' FCFA',
                'libelle_frais_annexe' => $fraisAnnexeDetails['libelle_frais_annexe'] ?? '',
                'code_frais_annexe' => $fraisAnnexeDetails['code_frais_annexe'] ?? '',
                'categorie_frais_annexe' => $fraisAnnexeDetails['categorie_frais_annexe'] ?? 'inscription',
                'libelle_categorie_frais_annexe' => 'Inscription',
                'total_frais_inscription' => $fraisInscription + $totalFraisAnnexes,
                'total_frais_inscription_formate' => number_format($fraisInscription + $totalFraisAnnexes, 0, ',', ' ') . ' FCFA'
            ]
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_INSCRIPTIONS');
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $etabCode = $this->getActiveEtablissementCode();
        $data = $_POST;
        unset($data['csrf_token']);
        $anneeCode = !empty($data['annee_code']) ? trim($data['annee_code']) : $this->getActiveAnneeCode();

        // Contrôle préalable des clés étrangères globales de la requête d'inscription
        $this->validateForeignKeys([
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'classe_code' => $data['classe_code'] ?? ''
        ]);

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
                $fkErrEtu = ForeignKeyValidator::validate($db, 'etudiants', [
                    'etablissement_code' => $etabCode
                ]);
                if ($fkErrEtu !== null) {
                    $db->rollBack();
                    $this->error($fkErrEtu);
                    return;
                }

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
                    $fkErrPar = ForeignKeyValidator::validate($db, 'parents', [
                        'etudiant_code' => $codeEtudiant,
                        'etablissement_code' => $etabCode
                    ]);
                    if ($fkErrPar !== null) {
                        $db->rollBack();
                        $this->error($fkErrPar);
                        return;
                    }

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
                $fkErrIns = ForeignKeyValidator::validate($db, 'inscriptions', [
                    'etudiant_code' => $codeEtudiant,
                    'classe_code' => $data['classe_code'],
                    'annee_code' => $anneeCode,
                    'etablissement_code' => $etabCode
                ]);
                if ($fkErrIns !== null) {
                    $db->rollBack();
                    $this->error($fkErrIns);
                    return;
                }

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

        // Contrôle Quitus Financier N-1 (Séparation des services : contrôle préalable de scolarité)
        $stmtPrev = $db->prepare("
            SELECT i.code_inscription, i.montant_scolarite_inscription, a.libelle_annee
            FROM inscriptions i
            LEFT JOIN annees a ON (a.code_annee = i.annee_code OR a.id_annee = i.annee_code)
            WHERE (i.etudiant_code = ? OR i.etudiant_code = (SELECT matricule_etudiant FROM etudiants WHERE code_etudiant = ? LIMIT 1))
              AND i.annee_code != ?
              AND i.statut_inscription != 'annule'
            ORDER BY i.id_inscription DESC
            LIMIT 1
        ");
        $stmtPrev->execute([$data['etudiant_code'], $data['etudiant_code'], $anneeCode]);
        $prevIns = $stmtPrev->fetch(PDO::FETCH_ASSOC);
        $prevSolde = 0;
        if ($prevIns) {
            $prevDue = (float)($prevIns['montant_scolarite_inscription'] ?? 0);
            $stmtP = $db->prepare("SELECT SUM(montant_paiement) as total_paye FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
            $stmtP->execute([$prevIns['code_inscription']]);
            $prevPaye = (float)($stmtP->fetch(PDO::FETCH_ASSOC)['total_paye'] ?? 0);
            $prevSolde = max(0, $prevDue - $prevPaye);
        }

        $derogationAcceptee = !empty($data['derogation_arriere']) && in_array($data['derogation_arriere'], ['1', 'oui', 'true']);
        if ($derogationAcceptee && !$this->hasPermission('MANAGE_DEROGATION_INSCRIPTION')) {
            $this->error("Accès refusé : Vous ne possédez pas le privilège [MANAGE_DEROGATION_INSCRIPTION] requis pour accorder une dérogation administrative.");
            return;
        }
        if ($prevSolde > 0 && !$derogationAcceptee) {
            $this->error("L'étudiant présente un reliquat impayé de " . number_format($prevSolde, 0, ',', ' ') . " FCFA sur la session précédente (" . ($prevIns['libelle_annee'] ?? 'antérieure') . "). La réinscription requiert un quitus financier au Bureau des Versements ou une dérogation administrative formelle.");
            return;
        }

        // Mise à jour rapide des coordonnées si modifiées lors du guichet de réinscription
        $updateEtu = [];
        $paramsEtu = [];
        if (!empty($data['telephone_etudiant'])) {
            $updateEtu[] = "telephone_etudiant = ?";
            $paramsEtu[] = trim($data['telephone_etudiant']);
        }
        if (isset($data['email_etudiant']) && trim($data['email_etudiant']) !== '') {
            $updateEtu[] = "email_etudiant = ?";
            $paramsEtu[] = trim($data['email_etudiant']);
        }
        if (!empty($data['lieu_residence_etudiant'])) {
            $updateEtu[] = "lieu_residence_etudiant = ?";
            $paramsEtu[] = trim($data['lieu_residence_etudiant']);
        }
        if (!empty($updateEtu)) {
            $paramsEtu[] = $data['etudiant_code'];
            $paramsEtu[] = $data['etudiant_code'];
            $sqlUpEtu = "UPDATE etudiants SET " . implode(', ', $updateEtu) . " WHERE code_etudiant = ? OR matricule_etudiant = ?";
            $db->prepare($sqlUpEtu)->execute($paramsEtu);
        }

        // Récupération sécurisée du barème  côté backend
        $affectationEtat = (!empty($data['affectation_etat']) && in_array($data['affectation_etat'], ['affecte', 'oui'])) ? 'affecte' : 'non_affecte';
        $data['affectation_etat'] = ($affectationEtat === 'affecte') ? 'oui' : 'non';

        $stmtCl = $db->prepare("SELECT libelle_classe, filiere_code, niveau_code, annee_code FROM classes WHERE code_classe = ? LIMIT 1");
        $stmtCl->execute([$data['classe_code']]);
        $cl = $stmtCl->fetch(PDO::FETCH_ASSOC);

        $officialScolarite = 0;
        $officialScolarite = 0;
        $codeScolarite = '';
        if ($cl) {
            $stmtSco = $db->prepare("
                SELECT code_scolarite, montant_scolarite FROM scolarites 
                WHERE filiere_code = ? 
                  AND (annee_code = ? OR ? = '')
                  AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
                  AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
                  AND statut_scolarite = 'actif'
                ORDER BY 
                  (CASE WHEN annee_code = ? THEN 1 ELSE 2 END),
                  (CASE WHEN niveau_code = ? THEN 1 ELSE 2 END),
                  (CASE WHEN affectation_etat = ? THEN 1 ELSE 2 END), 
                  id_scolarite DESC
                LIMIT 1
            ");
            $stmtSco->execute([
                $cl['filiere_code'],
                $cl['annee_code'], $cl['annee_code'],
                $cl['niveau_code'],
                $affectationEtat,
                $cl['annee_code'],
                $cl['niveau_code'],
                $affectationEtat
            ]);
            $scol = $stmtSco->fetch(PDO::FETCH_ASSOC);
            if ($scol) {
                $officialScolarite = (float)$scol['montant_scolarite'];
                $codeScolarite = $scol['code_scolarite'] ?? '';
            } else {
                $regimeTxt = ($affectationEtat === 'affecte') ? 'Affecté (État)' : 'Non Affecté (Privé)';
                $classeLib = $cl['libelle_classe'] ?? 'sélectionnée';
                $this->error("Réinscription impossible : Aucun tarif de scolarité actif n'est configuré pour la classe [$classeLib] sous le régime $regimeTxt pour cette session académique. Veuillez d'abord paramétrer la scolarité dans le module Finance.");
                return;
            }
        }

        // Récupération de la première tranche (Droit de réinscription exigible à la caisse)
        $tranche1Amount = $officialScolarite;
        $tranche1Libelle = 'Scolarité / Droit de réinscription';
        if (!empty($codeScolarite)) {
            $stmtTr = $db->prepare("
                SELECT libelle_tranche, montant_tranche, date_limite 
                FROM tranches_scolarite 
                WHERE scolarite_code = ? 
                  AND statut_tranche = 'actif'
                ORDER BY id_tranche ASC
            ");
            $stmtTr->execute([$codeScolarite]);
            $tranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (!empty($tranches)) {
                $tranche1Amount = (float)$tranches[0]['montant_tranche'];
                $tranche1Libelle = $tranches[0]['libelle_tranche'];
            }
        }

        // Forcer le montant  et la date d'inscription côté backend pour garantir l'intégrité
        $data['montant_scolarite_inscription'] = $officialScolarite;
        $data['date_inscription'] = date('Y-m-d');

        if (empty($data['code_inscription'])) {
            $data['code_inscription'] = $this->validator->generateCode('inscriptions', 'code_inscription', 'INS-', 8);
        }
        $codeInscription = $data['code_inscription'];
        $data['statut_inscription'] = $data['statut_inscription'] ?? 'valide';
        $data['created_at_inscription'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            // Récupérer les informations de l'étudiant pour la fiche navette
            $stmtE = $db->prepare("SELECT nom_etudiant, prenom_etudiant, matricule_etudiant, telephone_etudiant FROM etudiants WHERE code_etudiant = ? OR matricule_etudiant = ? LIMIT 1");
            $stmtE->execute([$data['etudiant_code'], $data['etudiant_code']]);
            $etuRow = $stmtE->fetch(PDO::FETCH_ASSOC);

            // Récupérer libellé année académique
            $stmtA = $db->prepare("SELECT libelle_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$anneeCode]);
            $anneeLib = $stmtA->fetch(PDO::FETCH_ASSOC)['libelle_annee'] ?? ($_SESSION['annee_active_libelle'] ?? '');

            $this->json([
                'status' => 1,
                'message' => 'Réinscription enregistrée avec succès au Bureau d\'Inscription !',
                'voucher_data' => [
                    'code_inscription' => $codeInscription,
                    'matricule_etudiant' => $etuRow['matricule_etudiant'] ?? '-',
                    'nom_complet' => trim(($etuRow['nom_etudiant'] ?? '') . ' ' . ($etuRow['prenom_etudiant'] ?? '')),
                    'telephone_etudiant' => $etuRow['telephone_etudiant'] ?? '',
                    'classe_libelle' => $cl['libelle_classe'] ?? '',
                    'regime' => ($affectationEtat === 'affecte') ? 'Affecté (État)' : 'Non Affecté (Privé)',
                    'annee_libelle' => $anneeLib,
                    'scolarite_totale' => $officialScolarite,
                    'tranche1_montant' => $tranche1Amount,
                    'tranche1_libelle' => $tranche1Libelle,
                    'arrieres_n1' => $prevSolde,
                    'date_inscription' => date('d/m/Y H:i'),
                    'agent_inscription' => trim(($_SESSION[USERS_AUTH]['prenom_user'] ?? '') . ' ' . ($_SESSION[USERS_AUTH]['nom_user'] ?? ''))
                ]
            ]);
        } else {
            $this->error("Erreur lors de l'enregistrement de la réinscription");
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission('MANAGE_INSCRIPTIONS');
        $id = (int)$this->post('id_inscription');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        $db = $this->model->getCon();

        // Récupération sécurisée du barème  côté backend si la classe ou le statut change
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
                      AND (annee_code = ? OR ? = '')
                      AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
                      AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
                      AND statut_scolarite = 'actif'
                    ORDER BY 
                      (CASE WHEN annee_code = ? THEN 1 ELSE 2 END),
                      (CASE WHEN niveau_code = ? THEN 1 ELSE 2 END),
                      (CASE WHEN affectation_etat = ? THEN 1 ELSE 2 END), 
                      id_scolarite DESC
                    LIMIT 1
                ");
                $stmtSco->execute([
                    $cl['filiere_code'],
                    $cl['annee_code'], $cl['annee_code'],
                    $cl['niveau_code'],
                    $affectationEtat,
                    $cl['annee_code'],
                    $cl['niveau_code'],
                    $affectationEtat
                ]);
                $scol = $stmtSco->fetch(PDO::FETCH_ASSOC);
                if ($scol) {
                    $data['montant_scolarite_inscription'] = (float)$scol['montant_scolarite'];
                } else {
                    $regimeTxt = ($affectationEtat === 'affecte') ? 'Affecté (État)' : 'Non Affecté (Privé)';
                    $this->error("Modification impossible : Aucun tarif de scolarité actif n'est configuré pour cette classe sous le régime $regimeTxt.");
                    return;
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
        $this->requirePermission('MANAGE_INSCRIPTIONS');
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
        $this->requirePermission('VIEW_INSCRIPTIONS');
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
                WHERE ins.id_inscription = ? OR ins.code_inscription = ?
            ");
            $stmt->execute([is_numeric($id) ? (int)$id : 0, $details]);
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
        $this->requirePermission('MANAGE_INSCRIPTIONS');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'inscription/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'inscription/list'); exit();
        }
        $canDerogate = $this->hasPermission('MANAGE_DEROGATION_INSCRIPTION');
        $this->loadView('../views/inscriptions/edit.php', ['item' => $item, 'encryptedId' => $encryptedId, 'canDerogate' => $canDerogate]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->requirePermission('MANAGE_INSCRIPTIONS');
        $canDerogate = $this->hasPermission('MANAGE_DEROGATION_INSCRIPTION');
        $this->loadView('../views/inscriptions/edit.php', ['item' => [], 'canDerogate' => $canDerogate]);
    }

    public function sansPhoto()
    {
        $this->priseDeVue();
    }

    public function priseDeVue()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_INSCRIPTIONS_SANS_PHOTO', 'MANAGE_INSCRIPTIONS_SANS_PHOTO', 'MANAGE_INSCRIPTIONS']);
        $db = $this->model->getCon();

        $activeAnneeCode = $this->getActiveAnneeCode();
        $annees = $this->getAccessibleAnnees();
        $filieres = $db->query("SELECT code_filiere, libelle_filiere FROM filieres WHERE statut_filiere = 'actif' ORDER BY libelle_filiere ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $classes = $db->query("SELECT code_classe, libelle_classe, filiere_code FROM classes WHERE statut_classe = 'actif' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->loadView('../views/inscriptions/prise_de_vue.php', [
            'annees' => $annees,
            'filieres' => $filieres,
            'classes' => $classes,
            'selectedAnneeCode' => $activeAnneeCode
        ]);
    }

    public function imprimerFiche($param = null)
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_INSCRIPTIONS', 'MANAGE_INSCRIPTIONS']);
        require_once __DIR__ . '/../../core/PdfService.php';

        $db = $this->model->getCon();
        $data = [];

        if (!empty($param)) {
            $id = $this->validator->decrypter($param);
            if (empty($id)) {
                $id = $param;
            }

            // Récupérer l'inscription avec les jointures étudiant et classe
            $stmtIns = $db->prepare("
                SELECT i.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.matricule_mesrs, 
                       e.date_naissance_etudiant, e.lieu_naissance_etudiant, e.nationalite_etudiant, e.sexe_etudiant,
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau, a.libelle_annee
                FROM inscriptions i
                LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = i.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code
                WHERE i.id_inscription = ? OR i.code_inscription = ? OR e.code_etudiant = ? OR e.matricule_etudiant = ? OR e.id_etudiant = ?
                LIMIT 1
            ");
            $stmtIns->execute([$id, $id, $id, $id, $id]);
            $ins = $stmtIns->fetch(PDO::FETCH_ASSOC);

            if ($ins) {
                // Dernier paiement validé pour l'inscription
                $stmtPai = $db->prepare("
                    SELECT code_paiement, montant_paiement, created_at_paiement
                    FROM paiements 
                    WHERE inscription_code = ? AND statut_paiement = 'valide'
                    ORDER BY id_paiement DESC LIMIT 1
                ");
                $stmtPai->execute([$ins['code_inscription']]);
                $pai = $stmtPai->fetch(PDO::FETCH_ASSOC) ?: [];

                $data = [
                    'item' => $ins,
                    'inscription' => $ins,
                    'paiement' => $pai,
                    'annee_universitaire' => $ins['libelle_annee'] ?? '2022 - 2023',
                    'matricule_mesrs' => !empty($ins['matricule_mesrs']) ? $ins['matricule_mesrs'] : ($ins['matricule_etudiant'] ?? ''),
                    'nom' => $ins['nom_etudiant'] ?? '',
                    'prenoms' => $ins['prenom_etudiant'] ?? '',
                    'date_lieu_naissance' => (!empty($ins['date_naissance_etudiant']) ? date('d-m-Y', strtotime($ins['date_naissance_etudiant'])) : '') . (!empty($ins['lieu_naissance_etudiant']) ? ' à ' . $ins['lieu_naissance_etudiant'] : ''),
                    'nationalite' => $ins['nationalite_etudiant'] ?? 'IVOIRIENNE',
                    'filiere' => $ins['libelle_filiere'] ?? '',
                    'niveau' => $ins['libelle_niveau'] ?? '',
                    'specialite' => $ins['libelle_classe'] ?? '',
                    'code_paiement' => $pai['code_paiement'] ?? ('INS-' . ($ins['code_inscription'] ?? '001')),
                    'montant_paiement' => isset($pai['montant_paiement']) ? number_format((float)$pai['montant_paiement'], 0, ',', '.') . ' F' : number_format((float)($ins['montant_scolarite_inscription'] ?? 60000), 0, ',', '.') . ' F',
                    'date_paiement' => !empty($pai['created_at_paiement']) ? date('d-m-Y', strtotime($pai['created_at_paiement'])) : date('d-m-Y')
                ];
            }
        }

        $html = PdfService::renderTemplate('fiche_inscription.php', $data);

        if (!empty($_GET['pdf'])) {
            $filename = 'Fiche_Inscription_' . ($data['matricule_mesrs'] ?? 'UVCI') . '.pdf';
            PdfService::generate($html, $filename, ['orientation' => 'P']);
        } else {
            echo $html;
        }
    }

    public function apiSansPhoto()
    {
        $this->apiPriseDeVue();
    }

    public function apiPriseDeVue()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_INSCRIPTIONS_SANS_PHOTO', 'MANAGE_INSCRIPTIONS_SANS_PHOTO', 'MANAGE_INSCRIPTIONS']);

        $anneeCode = $_GET['annee_code'] ?? $this->getActiveAnneeCode();
        $filiereCode = $_GET['filiere_code'] ?? '';
        $classeCode = $_GET['classe_code'] ?? '';
        $filterPhoto = $_GET['filter_photo'] ?? 'all';

        try {
            $db = $this->model->getCon();
            $sql = "
                SELECT 
                    i.id_inscription,
                    i.code_inscription,
                    i.created_at_inscription,
                    i.statut_inscription,
                    i.photo_inscription,
                    e.code_etudiant,
                    e.matricule_etudiant,
                    e.nom_etudiant,
                    e.prenom_etudiant,
                    e.photo_etudiant,
                    e.telephone_etudiant,
                    cl.code_classe,
                    cl.libelle_classe,
                    f.code_filiere,
                    f.libelle_filiere,
                    a.libelle_annee
                FROM inscriptions i
                JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                JOIN classes cl ON cl.code_classe = i.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code
                WHERE 1=1
            ";

            $params = [];
            if (!empty($anneeCode)) {
                $sql .= " AND i.annee_code = ?";
                $params[] = $anneeCode;
            }
            if (!empty($filiereCode)) {
                $sql .= " AND cl.filiere_code = ?";
                $params[] = $filiereCode;
            }
            if (!empty($classeCode)) {
                $sql .= " AND i.classe_code = ?";
                $params[] = $classeCode;
            }

            if ($filterPhoto === 'sans_photo') {
                $sql .= " AND (i.photo_inscription IS NULL OR TRIM(i.photo_inscription) = '')";
            } elseif ($filterPhoto === 'avec_photo') {
                $sql .= " AND i.photo_inscription IS NOT NULL AND TRIM(i.photo_inscription) != ''";
            }

            $sql .= " GROUP BY i.code_inscription ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $data = [];
            foreach ($rows as $r) {
                $id = $r['id_inscription'];
                $idCrypte = $this->validator->crypter($id);
                $hasPhoto = (!empty($r['photo_inscription']) || !empty($r['photo_etudiant']));
                $photoPath = !empty($r['photo_inscription']) ? $r['photo_inscription'] : ($r['photo_etudiant'] ?? '');
                $data[] = array_merge($r, [
                    'id' => $id,
                    'editId' => $idCrypte,
                    'nom_complet' => trim($r['nom_etudiant'] . ' ' . $r['prenom_etudiant']),
                    'has_photo' => $hasPhoto,
                    'photo_path' => $photoPath
                ]);
            }
            $this->json(['data' => $data]);
        } catch (Exception $e) {
            error_log("InscriptionController::apiPriseDeVue error: " . $e->getMessage());
            $this->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    public function uploadPhoto()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['MANAGE_INSCRIPTIONS_SANS_PHOTO', 'MANAGE_INSCRIPTIONS']);

        $idInscription = (int)($_POST['id_inscription'] ?? 0);
        $codeInscription = trim($_POST['code_inscription'] ?? '');
        $webcamData = trim($_POST['photo_webcam_data'] ?? '');

        if (!$idInscription && empty($codeInscription)) {
            $this->error("Identifiant d'inscription valide requis.");
            return;
        }

        $db = $this->model->getCon();
        $stmtInfo = $db->prepare("
            SELECT 
                i.id_inscription,
                i.code_inscription,
                i.annee_code,
                i.photo_inscription,
                e.matricule_etudiant,
                e.code_etudiant,
                a.libelle_annee
            FROM inscriptions i
            JOIN etudiants e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN annees a ON a.code_annee = i.annee_code
            WHERE " . ($idInscription ? "i.id_inscription = ?" : "i.code_inscription = ?") . "
            LIMIT 1
        ");
        $stmtInfo->execute([$idInscription ?: $codeInscription]);
        $inscInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if (!$inscInfo) {
            $this->error("Inscription introuvable.");
            return;
        }

        // Dossier classé par année académique
        $anneeLabel = !empty($inscInfo['libelle_annee']) ? $inscInfo['libelle_annee'] : ($inscInfo['annee_code'] ?: 'defaut');
        $safeAnneeFolder = preg_replace('/[^a-zA-Z0-9_-]/', '_', $anneeLabel);
        $uploadDir = __DIR__ . '/../../public/uploads/photos_inscriptions/' . $safeAnneeFolder . '/';

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
            @chmod($uploadDir, 0777);
        }

        // Nom du fichier = Matricule de l'étudiant
        $matricule = !empty($inscInfo['matricule_etudiant']) ? trim($inscInfo['matricule_etudiant']) : '';
        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $matricule);
        if (empty($safeFilename)) {
            $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $inscInfo['code_inscription'] ?: ('INS_' . $inscInfo['id_inscription']));
        }

        // Supprimer l'ancienne photo si elle existe sur le disque
        $oldPhotoPath = !empty($inscInfo['photo_inscription']) ? trim($inscInfo['photo_inscription']) : '';
        if (!empty($oldPhotoPath)) {
            $oldFullPath = __DIR__ . '/../../' . ltrim($oldPhotoPath, '/');
            if (file_exists($oldFullPath) && is_file($oldFullPath)) {
                @unlink($oldFullPath);
            }
        }

        $relativePath = null;

        if (!empty($webcamData)) {
            if (preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $webcamData, $type)) {
                $data = substr($webcamData, strpos($webcamData, ',') + 1);
                $ext = strtolower($type[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $data = base64_decode($data);
                if ($data === false) {
                    $this->error("Données de photo webcam invalides.");
                    return;
                }
                $filename = $safeFilename . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (file_put_contents($targetPath, $data) !== false) {
                    $relativePath = 'public/uploads/photos_inscriptions/' . $safeAnneeFolder . '/' . $filename;
                }
            } else {
                $this->error("Format de capture webcam invalide.");
                return;
            }
        } elseif (isset($_FILES['photo_inscription']) && $_FILES['photo_inscription']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo_inscription'];
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                $this->error("La photo dépasse la taille maximale autorisée de 5 Mo.");
                return;
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['png', 'jpg', 'jpeg', 'webp'];
            if (!in_array($ext, $allowedExts, true)) {
                $this->error("Format d'image non autorisé. Formats acceptés : PNG, JPG, JPEG, WEBP.");
                return;
            }
            $filename = $safeFilename . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $relativePath = 'public/uploads/photos_inscriptions/' . $safeAnneeFolder . '/' . $filename;
            }
        } else {
            $this->error("Veuillez sélectionner un fichier image ou capturer une photo depuis la webcam.");
            return;
        }

        if ($relativePath) {
            $stmt = $db->prepare("UPDATE inscriptions SET photo_inscription = ?, updated_at_inscription = NOW() WHERE id_inscription = ?");
            $ok = $stmt->execute([$relativePath, $inscInfo['id_inscription']]);

            if ($ok) {
                $this->success("La photo d'inscription a été enregistrée avec succès !", [
                    'photo_url' => RACINE . $relativePath
                ]);
            } else {
                $this->error("Erreur lors de la mise à jour de la photo dans la base de données.");
            }
        } else {
            $this->error("Erreur lors de la sauvegarde du fichier photo.");
        }
    }
}
