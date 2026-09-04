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
        $db = $this->model->getCon();
        $activeYear = $this->getActiveAnneeCode();

        // 1. Statistiques des Inscriptions et de la Scolarité Globale Attendue
        $stmtIns = $db->prepare("
            SELECT 
                COUNT(DISTINCT i.id_inscription) as total_inscrits,
                COUNT(DISTINCT i.etudiant_code) as total_etudiants_inscrits,
                COALESCE(SUM(
                    CASE 
                        WHEN i.montant_scolarite_inscription IS NOT NULL AND i.montant_scolarite_inscription > 0 THEN i.montant_scolarite_inscription
                        WHEN s.montant_scolarite IS NOT NULL AND s.montant_scolarite > 0 THEN s.montant_scolarite
                        ELSE 0
                    END
                ), 0) as total_scolarite_attendue
            FROM inscriptions i
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            LEFT JOIN scolarites s ON (
                s.filiere_code = c.filiere_code 
                AND (s.niveau_code = c.niveau_code OR s.niveau_code IS NULL OR s.niveau_code = '')
                AND (s.annee_code = i.annee_code OR s.annee_code IS NULL OR s.annee_code = '')
                AND (s.affectation_etat = i.affectation_etat OR s.affectation_etat IS NULL OR s.affectation_etat = '')
                AND s.statut_scolarite = 'actif'
            )
            WHERE (i.annee_code = ? OR ? = '') AND i.statut_inscription != 'annule'
        ");
        $stmtIns->execute([$activeYear, $activeYear]);
        $insStats = $stmtIns->fetch(PDO::FETCH_ASSOC) ?: [
            'total_inscrits' => 0,
            'total_etudiants_inscrits' => 0,
            'total_scolarite_attendue' => 0
        ];

        // 2. Statistiques des Paiements et Encaissements
        $stmtPay = $db->prepare("
            SELECT 
                COUNT(*) as total_operations,
                COALESCE(SUM(montant_paiement), 0) as total_encaisse,
                COALESCE(SUM(CASE WHEN DATE(date_paiement) = CURDATE() THEN montant_paiement ELSE 0 END), 0) as encaisse_aujourdhui,
                COALESCE(SUM(CASE WHEN YEAR(date_paiement) = YEAR(CURDATE()) AND MONTH(date_paiement) = MONTH(CURDATE()) THEN montant_paiement ELSE 0 END), 0) as encaisse_mois,
                COUNT(DISTINCT p.inscription_code) as total_eleves_payeurs
            FROM paiements p
            WHERE p.statut_paiement != 'annule'
        ");
        $stmtPay->execute();
        $payStats = $stmtPay->fetch(PDO::FETCH_ASSOC) ?: [
            'total_operations' => 0,
            'total_encaisse' => 0,
            'encaisse_aujourdhui' => 0,
            'encaisse_mois' => 0,
            'total_eleves_payeurs' => 0
        ];

        $totalScolarite = (float)$insStats['total_scolarite_attendue'];
        $totalEncaisse = (float)$payStats['total_encaisse'];
        $montantEnAttente = max(0, $totalScolarite - $totalEncaisse);
        $tauxRecouvrement = ($totalScolarite > 0) ? round(($totalEncaisse / $totalScolarite) * 100, 1) : 0;

        $stats = array_merge($payStats, [
            'total_inscrits' => (int)$insStats['total_inscrits'],
            'total_scolarite_attendue' => $totalScolarite,
            'montant_en_attente' => $montantEnAttente,
            'taux_recouvrement' => $tauxRecouvrement
        ]);

        $this->loadView('../views/paiements/list.php', [
            'stats' => $stats
        ]);
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

        $activeYear = $this->getActiveAnneeCode();

        if (!empty($inscriptionCode)) {
            $stmt = $db->prepare("
                SELECT i.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.code_etudiant, e.photo_etudiant,
                       c.libelle_classe, c.filiere_code, c.niveau_code,
                       f.libelle_filiere, n.libelle_niveau, a.libelle_annee
                FROM inscriptions i
                LEFT JOIN etudiants e ON i.etudiant_code = e.code_etudiant
                LEFT JOIN classes c ON i.classe_code = c.code_classe
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code
                WHERE i.code_inscription = ? OR i.id_inscription = ?
                LIMIT 1
            ");
            $stmt->execute([$inscriptionCode, is_numeric($inscriptionCode) ? (int)$inscriptionCode : 0]);
            $ins = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (!empty($etudiantCode)) {
            $stmt = $db->prepare("
                SELECT i.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.code_etudiant, e.photo_etudiant,
                       c.libelle_classe, c.filiere_code, c.niveau_code,
                       f.libelle_filiere, n.libelle_niveau, a.libelle_annee
                FROM etudiants e
                LEFT JOIN inscriptions i ON i.etudiant_code = e.code_etudiant AND (i.annee_code = ? OR i.annee_code IS NULL) AND (i.statut_inscription != 'annule')
                LEFT JOIN classes c ON i.classe_code = c.code_classe
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code
                WHERE e.code_etudiant = ? OR e.matricule_etudiant = ?
                ORDER BY (CASE WHEN i.annee_code = ? THEN 1 ELSE 2 END), i.id_inscription DESC
                LIMIT 1
            ");
            $stmt->execute([$activeYear, $etudiantCode, $etudiantCode, $activeYear]);
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
        $filiereCode = $ins['filiere_code'] ?? '';
        $niveauCode = $ins['niveau_code'] ?? '';
        $anneeCode = !empty($ins['annee_code']) ? $ins['annee_code'] : $activeYear;
        
        $rawAff = strtolower(trim($ins['affectation_etat'] ?? ''));
        $isAffecte = ($rawAff === 'oui' || $rawAff === 'affecte' || $rawAff === '1');
        $affEtat = $isAffecte ? 'affecte' : 'non_affecte';

        // 1. Recherche du tarif officiel de scolarité pour l'année, la filière / niveau et le régime
        $stmtSco = $db->prepare("
            SELECT * FROM scolarites 
            WHERE filiere_code = ? 
              AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
              AND (annee_code = ? OR annee_code = ? OR annee_code = '' OR annee_code IS NULL)
              AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
              AND statut_scolarite = 'actif'
            ORDER BY (CASE WHEN annee_code = ? THEN 1 WHEN annee_code = ? THEN 2 ELSE 3 END),
                     (CASE WHEN affectation_etat = ? THEN 1 ELSE 2 END),
                     id_scolarite DESC
            LIMIT 1
        ");
        $stmtSco->execute([$filiereCode, $niveauCode, $activeYear, $anneeCode, $affEtat, $activeYear, $anneeCode, $affEtat]);
        $scoGrid = $stmtSco->fetch(PDO::FETCH_ASSOC);

        if (!$scoGrid && !empty($filiereCode)) {
            $stmtScoFallback = $db->prepare("
                SELECT * FROM scolarites 
                WHERE filiere_code = ? AND statut_scolarite = 'actif'
                ORDER BY (CASE WHEN (annee_code = ? OR annee_code = ?) THEN 1 ELSE 2 END), id_scolarite DESC
                LIMIT 1
            ");
            $stmtScoFallback->execute([$filiereCode, $activeYear, $anneeCode]);
            $scoGrid = $stmtScoFallback->fetch(PDO::FETCH_ASSOC);
        }

        $codeScolarite = $scoGrid['code_scolarite'] ?? '';
        if ($scoGrid && (float)$scoGrid['montant_scolarite'] > 0) {
            $scolariteDue = (float)$scoGrid['montant_scolarite'];
        } else {
            $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
        }

        // Récupération de tous les paiements existants pour cette inscription
        $stmtPay = $db->prepare("SELECT * FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule' ORDER BY date_paiement ASC, id_paiement ASC");
        $stmtPay->execute([$codeInscription]);
        $allPayments = $stmtPay->fetchAll(PDO::FETCH_ASSOC);

        $totalPaye = 0;
        foreach ($allPayments as $p) {
            $totalPaye += (float)$p['montant_paiement'];
        }

        $soldeRestant = max(0, $scolariteDue - $totalPaye);

        $statutReglement = 'Non Réglé';
        $badgeClass = 'badge-danger';
        if ($totalPaye >= $scolariteDue && $scolariteDue > 0) {
            $statutReglement = 'Scolarité Totalement Soldée';
            $badgeClass = 'badge-success';
        } elseif ($totalPaye > 0) {
            $statutReglement = 'Acompte Payé / Solde Débiteur';
            $badgeClass = 'badge-warning';
        }

        // 2. Récupération des tranches actives UNIQUEMENT pour l'année académique de l'inscription
        $stmtTr = $db->prepare("
            SELECT t.*, s.montant_scolarite as scolarite_globale
            FROM tranches_scolarite t
            LEFT JOIN scolarites s ON s.code_scolarite = t.scolarite_code
            WHERE t.statut_tranche = 'actif'
              AND (t.annee_code = ? OR s.annee_code = ?)
              AND (
                (t.scolarite_code != '' AND t.scolarite_code = ?)
                OR (t.scolarite_code = '' AND t.filiere_code = ? AND t.niveau_code = ?)
                OR (s.filiere_code = ? AND s.niveau_code = ?)
              )
            ORDER BY t.date_limite ASC, t.id_tranche ASC
        ");
        $stmtTr->execute([$anneeCode, $anneeCode, $codeScolarite, $filiereCode, $niveauCode, $filiereCode, $niveauCode]);
        $dbTranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC);

        // Calcul des paiements par tranche
        $tranchesList = [];
        $unassignedPayments = 0;

        // Somme des paiements explicitement attribués par code_tranche
        $paymentsByTranche = [];
        foreach ($allPayments as $p) {
            $tCode = $p['tranche_code'] ?? '';
            if (!empty($tCode)) {
                $paymentsByTranche[$tCode] = ($paymentsByTranche[$tCode] ?? 0) + (float)$p['montant_paiement'];
            } else {
                $unassignedPayments += (float)$p['montant_paiement'];
            }
        }

        $suggestedTrancheCode = null;

        if (!empty($dbTranches)) {
            foreach ($dbTranches as $tr) {
                $tCode = $tr['code_tranche'];
                $montantTranche = (float)$tr['montant_tranche'];
                $dejaPaye = $paymentsByTranche[$tCode] ?? 0;

                // Si paiements antérieurs sans tranche_code, allocation séquentielle
                if ($unassignedPayments > 0 && $dejaPaye < $montantTranche) {
                    $needed = $montantTranche - $dejaPaye;
                    $allocated = min($unassignedPayments, $needed);
                    $dejaPaye += $allocated;
                    $unassignedPayments -= $allocated;
                }

                $resteAPayer = max(0, $montantTranche - $dejaPaye);
                $isSoldee = ($resteAPayer <= 0);
                $isPartiel = ($dejaPaye > 0 && $resteAPayer > 0);

                if ($isSoldee) {
                    $statutCode = 'soldee';
                    $statutLibelle = 'Payée (Soldée)';
                    $badgeBg = '#DCFCE7';
                    $badgeColor = '#15803D';
                } elseif ($isPartiel) {
                    $statutCode = 'partiel';
                    $statutLibelle = 'Partielle (Reste : ' . number_format($resteAPayer, 0, ',', ' ') . ' F)';
                    $badgeBg = '#FEF3C7';
                    $badgeColor = '#B45309';
                } else {
                    $statutCode = 'a_payer';
                    $statutLibelle = 'À Payer';
                    $badgeBg = '#EFF6FF';
                    $badgeColor = '#1E3A5F';
                }

                if (!$isSoldee && $suggestedTrancheCode === null) {
                    $suggestedTrancheCode = $tCode;
                }

                $tranchesList[] = [
                    'id_tranche' => $tr['id_tranche'],
                    'code_tranche' => $tCode,
                    'libelle_tranche' => $tr['libelle_tranche'],
                    'montant_tranche' => $montantTranche,
                    'montant_tranche_fmt' => number_format($montantTranche, 0, ',', ' ') . ' FCFA',
                    'date_limite' => $tr['date_limite'],
                    'date_limite_fmt' => !empty($tr['date_limite']) ? date('d/m/Y', strtotime($tr['date_limite'])) : 'Non définie',
                    'deja_paye' => $dejaPaye,
                    'deja_paye_fmt' => number_format($dejaPaye, 0, ',', ' ') . ' FCFA',
                    'reste_a_payer' => $resteAPayer,
                    'reste_a_payer_fmt' => number_format($resteAPayer, 0, ',', ' ') . ' FCFA',
                    'is_soldee' => $isSoldee,
                    'statut_code' => $statutCode,
                    'statut_libelle' => $statutLibelle,
                    'badge_bg' => $badgeBg,
                    'badge_color' => $badgeColor
                ];
            }
        } else {
            // Pas de tranches distinctes configurées pour cette filière/niveau
            $isSoldee = ($soldeRestant <= 0);
            $tranchesList[] = [
                'id_tranche' => 0,
                'code_tranche' => 'SCOLARITE_GLOBALE',
                'libelle_tranche' => 'Scolarité Complète',
                'montant_tranche' => $scolariteDue,
                'montant_tranche_fmt' => number_format($scolariteDue, 0, ',', ' ') . ' FCFA',
                'date_limite' => '',
                'date_limite_fmt' => 'Annuelle',
                'deja_paye' => $totalPaye,
                'deja_paye_fmt' => number_format($totalPaye, 0, ',', ' ') . ' FCFA',
                'reste_a_payer' => $soldeRestant,
                'reste_a_payer_fmt' => number_format($soldeRestant, 0, ',', ' ') . ' FCFA',
                'is_soldee' => $isSoldee,
                'statut_code' => $isSoldee ? 'soldee' : ($totalPaye > 0 ? 'partiel' : 'a_payer'),
                'statut_libelle' => $isSoldee ? 'Payée (Soldée)' : 'À Payer',
                'badge_bg' => $isSoldee ? '#DCFCE7' : '#EFF6FF',
                'badge_color' => $isSoldee ? '#15803D' : '#1E3A5F'
            ];
            $suggestedTrancheCode = 'SCOLARITE_GLOBALE';
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
                'filiere' => $ins['libelle_filiere'] ?? '-',
                'niveau' => $ins['libelle_niveau'] ?? '-',
                'annee' => $ins['libelle_annee'] ?? '-',
                'scolarite_due' => $scolariteDue,
                'scolarite_due_fmt' => number_format($scolariteDue, 0, ',', ' ') . ' FCFA',
                'total_paye' => $totalPaye,
                'total_paye_fmt' => number_format($totalPaye, 0, ',', ' ') . ' FCFA',
                'solde_restant' => $soldeRestant,
                'solde_restant_fmt' => number_format($soldeRestant, 0, ',', ' ') . ' FCFA',
                'statut_reglement' => $statutReglement,
                'badge_class' => $badgeClass,
                'tranches' => $tranchesList,
                'suggested_tranche_code' => $suggestedTrancheCode
            ]
        ]);
    }

    public function getNextUnpaidTranche(string $inscriptionCode): ?array
    {
        $db = $this->model->getCon();
        $stmtIns = $db->prepare("
            SELECT i.*, c.filiere_code, c.niveau_code
            FROM inscriptions i
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            WHERE i.code_inscription = ? OR i.id_inscription = ?
            LIMIT 1
        ");
        $stmtIns->execute([$inscriptionCode, is_numeric($inscriptionCode) ? (int)$inscriptionCode : 0]);
        $ins = $stmtIns->fetch(PDO::FETCH_ASSOC);
        if (!$ins) return null;

        $filiereCode = $ins['filiere_code'] ?? '';
        $niveauCode = $ins['niveau_code'] ?? '';
        $anneeCode = !empty($ins['annee_code']) ? $ins['annee_code'] : $this->getActiveAnneeCode();

        $rawAff = strtolower(trim($ins['affectation_etat'] ?? ''));
        $isAffecte = ($rawAff === 'oui' || $rawAff === 'affecte' || $rawAff === '1');
        $affEtat = $isAffecte ? 'affecte' : 'non_affecte';

        $stmtSco = $db->prepare("
            SELECT code_scolarite FROM scolarites 
            WHERE filiere_code = ? 
              AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
              AND (annee_code = ? OR annee_code = '' OR annee_code IS NULL)
              AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
              AND statut_scolarite = 'actif'
            ORDER BY id_scolarite DESC LIMIT 1
        ");
        $stmtSco->execute([$filiereCode, $niveauCode, $anneeCode, $affEtat]);
        $scoGrid = $stmtSco->fetch(PDO::FETCH_ASSOC);
        $codeScolarite = $scoGrid['code_scolarite'] ?? '';

        $stmtTr = $db->prepare("
            SELECT t.*
            FROM tranches_scolarite t
            LEFT JOIN scolarites s ON s.code_scolarite = t.scolarite_code
            WHERE t.statut_tranche = 'actif'
              AND (
                (t.scolarite_code != '' AND t.scolarite_code = ?)
                OR (t.scolarite_code = '' AND t.filiere_code = ? AND t.niveau_code = ?)
                OR (s.filiere_code = ? AND s.niveau_code = ?)
              )
            ORDER BY t.date_limite ASC, t.id_tranche ASC
        ");
        $stmtTr->execute([$codeScolarite, $filiereCode, $niveauCode, $filiereCode, $niveauCode]);
        $dbTranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC);

        if (empty($dbTranches)) return null;

        $stmtPay = $db->prepare("SELECT * FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
        $stmtPay->execute([$inscriptionCode]);
        $allPayments = $stmtPay->fetchAll(PDO::FETCH_ASSOC);

        $paymentsByTranche = [];
        $unassignedPayments = 0;
        foreach ($allPayments as $p) {
            $tCode = $p['tranche_code'] ?? '';
            if (!empty($tCode)) {
                $paymentsByTranche[$tCode] = ($paymentsByTranche[$tCode] ?? 0) + (float)$p['montant_paiement'];
            } else {
                $unassignedPayments += (float)$p['montant_paiement'];
            }
        }

        foreach ($dbTranches as $tr) {
            $tCode = $tr['code_tranche'];
            $montantTranche = (float)$tr['montant_tranche'];
            $dejaPaye = $paymentsByTranche[$tCode] ?? 0;

            if ($unassignedPayments > 0 && $dejaPaye < $montantTranche) {
                $needed = $montantTranche - $dejaPaye;
                $allocated = min($unassignedPayments, $needed);
                $dejaPaye += $allocated;
                $unassignedPayments -= $allocated;
            }

            $resteAPayer = max(0, $montantTranche - $dejaPaye);
            if ($resteAPayer > 0) {
                return [
                    'code_tranche' => $tCode,
                    'libelle_tranche' => $tr['libelle_tranche'],
                    'montant_tranche' => $montantTranche,
                    'deja_paye' => $dejaPaye,
                    'reste_a_payer' => $resteAPayer
                ];
            }
        }

        return null;
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
        $today = date('Y-m-d');
        $mode = strtolower($data['mode_paiement'] ?? 'espece');

        // Vérification de la session de caisse pour les encaissements en espèces
        if ($mode === 'especes' || $mode === 'espece' || $mode === 'cash' || empty($mode)) {
            $stmtSession = $db->prepare("SELECT * FROM sessions_caisse WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
            $stmtSession->execute([$today]);
            $sess = $stmtSession->fetch(PDO::FETCH_ASSOC);

            if (!$sess || $sess['statut_session'] !== 'ouverte') {
                if ($sess && in_array($sess['statut_session'], ['cloturee', 'valide'])) {
                    $this->error("Encaissement impossible : La session de caisse du jour a déjà été CLÔTURÉE (Réf: {$sess['code_session']}). Aucun nouvel encaissement en espèces ne peut être enregistré.");
                } else {
                    $this->error("Encaissement impossible : Aucune session de caisse n'est OUVERTE pour aujourd'hui. Veuillez ouvrir la session de caisse avant d'encaisser.");
                }
                return;
            }
        }

        $inscriptionCode = trim($data['inscription_code'] ?? '');
        if (empty($inscriptionCode)) {
            $this->error("Veuillez sélectionner un dossier d'inscription valide.");
            return;
        }

        $montantPaiement = (float)($data['montant_paiement'] ?? 0);
        if ($montantPaiement <= 0) {
            $this->error("Le montant du versement doit être supérieur à 0 FCFA.");
            return;
        }

        $trancheCode = trim($data['tranche_code'] ?? '');
        if (empty($trancheCode)) {
            $this->error("Veuillez obligatoirement sélectionner la tranche correspondante à ce versement.");
            return;
        }

        // Contrôle backend strict de l'ordre chronologique des tranches (Prochaine tranche impayée obligatoire)
        if ($trancheCode !== 'SCOLARITE_GLOBALE') {
            $nextUnpaid = $this->getNextUnpaidTranche($inscriptionCode);
            if ($nextUnpaid && $nextUnpaid['code_tranche'] !== $trancheCode) {
                $this->error("Encaissement refusé : Vous devez obligatoirement solder la tranche impayée en cours « {$nextUnpaid['libelle_tranche']} » avant de pouvoir enregistrer un versement pour une tranche ultérieure.");
                return;
            }

            $stmtTr = $db->prepare("SELECT * FROM tranches_scolarite WHERE code_tranche = ? LIMIT 1");
            $stmtTr->execute([$trancheCode]);
            $tranche = $stmtTr->fetch(PDO::FETCH_ASSOC);
            if (!$tranche) {
                $this->error("La tranche sélectionnée est introuvable.");
                return;
            }

            $stmtPayTr = $db->prepare("SELECT SUM(montant_paiement) FROM paiements WHERE inscription_code = ? AND tranche_code = ? AND statut_paiement != 'annule'");
            $stmtPayTr->execute([$inscriptionCode, $trancheCode]);
            $dejaPayeTr = (float)($stmtPayTr->fetchColumn() ?: 0);
            $montantMaxTr = (float)$tranche['montant_tranche'];
            $resteAutorise = max(0, $montantMaxTr - $dejaPayeTr);

            if ($resteAutorise <= 0) {
                $this->error("Paiement impossible : La tranche « {$tranche['libelle_tranche']} » a déjà été intégralement soldée (" . number_format($montantMaxTr, 0, ',', ' ') . " FCFA déjà payé). Veuillez sélectionner une autre tranche impayée.");
                return;
            }

            if ($montantPaiement > $resteAutorise) {
                $resteFmt = number_format($resteAutorise, 0, ',', ' ');
                $montantSaisiFmt = number_format($montantPaiement, 0, ',', ' ');
                $this->error("Le montant saisi ($montantSaisiFmt FCFA) dépasse le solde restant dû pour cette tranche ($resteFmt FCFA). Veuillez saisir un montant inférieur ou égal à $resteFmt FCFA.");
                return;
            }
        }

        if (empty($data['code_paiement'])) {
            $data['code_paiement'] = $this->validator->generateCode('paiements', 'code_paiement', 'PAI-', 8);
        }
        $data['statut_paiement'] = $data['statut_paiement'] ?? 'confirme';
        $data['date_paiement'] = date('Y-m-d H:i:s');

        // Rattachement automatique à la session de caisse ouverte
        $todayDate = date('Y-m-d');
        $stmtActiveSes = $db->prepare("SELECT code_session FROM sessions_caisse WHERE date_session = ? AND statut_session = 'ouverte' ORDER BY id_session DESC LIMIT 1");
        $stmtActiveSes->execute([$todayDate]);
        $activeSessionCode = $stmtActiveSes->fetchColumn();

        if ($activeSessionCode) {
            $data['session_caisse_code'] = $activeSessionCode;
        } else {
            // S'il n'y a pas de session ouverte, chercher la session la plus récente du jour
            $stmtAnySes = $db->prepare("SELECT code_session FROM sessions_caisse WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
            $stmtAnySes->execute([$todayDate]);
            $anySessionCode = $stmtAnySes->fetchColumn();
            if ($anySessionCode) {
                $data['session_caisse_code'] = $anySessionCode;
            }
        }

        $cols = $this->model->getCon()->query("DESCRIBE paiements")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        if (in_array('tranche_code', $cols)) $data['tranche_code'] = $trancheCode;

        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            // Mettre à jour le statut d'inscription si la totalité de la scolarité est soldée
            $stmtIns = $db->prepare("SELECT * FROM inscriptions WHERE code_inscription = ? LIMIT 1");
            $stmtIns->execute([$inscriptionCode]);
            $ins = $stmtIns->fetch(PDO::FETCH_ASSOC);
            if ($ins) {
                $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
                $stmtTot = $db->prepare("SELECT SUM(montant_paiement) FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
                $stmtTot->execute([$inscriptionCode]);
                $totalPayeCumul = (float)($stmtTot->fetchColumn() ?: 0);

                if ($totalPayeCumul >= $scolariteDue && $scolariteDue > 0) {
                    $db->prepare("UPDATE inscriptions SET statut_inscription = 'solde' WHERE code_inscription = ?")->execute([$inscriptionCode]);
                }
            }

            $this->success('Règlement de caisse enregistré avec succès!', ['reload' => true]);
        } else {
            $this->error('Erreur lors de l\'enregistrement du paiement');
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
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = ['en_attente', 'confirme', 'annule', 'rembourse', 'echoue'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'statut_paiement');
            } else {
                $success = $this->model->toggleStatus($id);
            }
            if ($success) {
                $this->success('Statut du paiement mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Paiement introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = is_numeric($details) ? (int)$details : $this->validator->decrypter($details);
            if (!$id && is_numeric($details)) {
                $id = (int)$details;
            }
            $item = $this->model->getById($id);
            if (!$item) { 
                $this->renderNotFound("Le paiement demandé est introuvable.");
                return;
            }

            // Calcul du cumul payé par l'étudiant pour cette inscription
            $inscriptionCode = $item['inscription_code'] ?? '';
            $stmtCumul = $this->model->getCon()->prepare("
                SELECT COALESCE(SUM(montant_paiement), 0) FROM paiements 
                WHERE inscription_code = ? AND statut_paiement != 'annule'
            ");
            $stmtCumul->execute([$inscriptionCode]);
            $totalPayeCumul = (float)$stmtCumul->fetchColumn();

            $scolarite = (float)($item['montant_scolarite_inscription'] ?? 0);
            $soldeRestant = max(0, $scolarite - $totalPayeCumul);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("PaiementController::details error: " . $e->getMessage());
            $this->renderNotFound("Le paiement demandé est introuvable.");
            return;
        }
        $this->loadView('../views/paiements/details.php', [
            'item' => $item, 
            'totalPayeCumul' => $totalPayeCumul,
            'soldeRestant' => $soldeRestant,
            'scolarite' => $scolarite,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = is_numeric($details) ? (int)$details : $this->validator->decrypter($details);
            if (!$id && is_numeric($details)) {
                $id = (int)$details;
            }
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
