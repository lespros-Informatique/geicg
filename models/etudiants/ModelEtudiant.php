<?php

class ModelEtudiant extends BaseModel
{
    protected string $table = TABLES::ETUDIANTS;
    protected string $primaryKey = 'id_etudiant';
    protected ?string $statusField = 'statut_etudiant';
    protected ?string $createdAtField = 'created_at_etudiant';

    /**
     * Génère automatiquement le matricule de l'étudiant
     * Règle : 1ère lettre Nom + 1ère lettre Prénom (Majuscule) - Ordre ID / GEB / 2 1ères lettres Filière + 2 derniers chiffres Année
     * Exemple : MN-123/GEB/ID26 (XX-123/GEB/AA26)
     */
    public function generateMatricule(string $nom, string $prenom, ?string $classeCode = null, ?string $anneeCode = null): string
    {
        $pdo = $this->getCon();

        // 1. Première lettre du Nom et Première lettre du Prénom en Majuscule
        $nomClean = trim($nom);
        $prenomClean = trim($prenom);
        $lettreNom = !empty($nomClean) ? strtoupper(mb_substr($nomClean, 0, 1, 'UTF-8')) : 'X';
        $lettrePrenom = !empty($prenomClean) ? strtoupper(mb_substr($prenomClean, 0, 1, 'UTF-8')) : 'X';
        $initiales = $lettreNom . $lettrePrenom;

        // 2. Ordre / Incrément last ID
        $lastId = (int)$pdo->query("SELECT MAX(id_etudiant) FROM " . TABLES::ETUDIANTS . "")->fetchColumn() ?: 0;
        $nextOrder = $lastId + 1;

        // 3. Sigle Établissement (dynamique depuis la base)
        $stmtEtabSigle = $pdo->query("SELECT code_etablissement, libelle_etablissement FROM " . TABLES::ETABLISSEMENTS . " ORDER BY id_etablissement ASC LIMIT 1");
        $rowEtabSigle = $stmtEtabSigle ? $stmtEtabSigle->fetch(PDO::FETCH_ASSOC) : null;
        $sigleEtab = 'GEB';
        if (!empty($rowEtabSigle['code_etablissement']) && !is_numeric($rowEtabSigle['code_etablissement']) && strlen(trim($rowEtabSigle['code_etablissement'])) <= 6) {
            $sigleEtab = strtoupper(trim(str_replace('ETA-', '', $rowEtabSigle['code_etablissement'])));
        } elseif (!empty($rowEtabSigle['libelle_etablissement'])) {
            $cleanLib = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $rowEtabSigle['libelle_etablissement']), 0, 3));
            if (!empty($cleanLib)) {
                $sigleEtab = $cleanLib;
            }
        }

        // 4. Deux premières lettres de la Filière en Majuscule
        $filiereCodeLetters = 'GE';
        if (!empty($classeCode)) {
            $stmtCls = $pdo->prepare("
                SELECT f.code_filiere, f.libelle_filiere 
                FROM " . TABLES::CLASSES . " cl 
                JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code 
                WHERE cl.code_classe = ? LIMIT 1
            ");
            $stmtCls->execute([$classeCode]);
            $filRow = $stmtCls->fetch(PDO::FETCH_ASSOC);
            if ($filRow) {
                $codeFil = str_replace('FIL-', '', $filRow['code_filiere'] ?? '');
                if (!empty($codeFil)) {
                    $filiereCodeLetters = strtoupper(substr($codeFil, 0, 2));
                } else {
                    $filiereCodeLetters = strtoupper(substr($filRow['libelle_filiere'] ?? 'GE', 0, 2));
                }
            }
        }

        // 5. Deux derniers chiffres de l'année (ex: 2026 -> 26, 2025-2026 -> 26)
        $anneeSuffix = date('y');
        if (!empty($anneeCode)) {
            $stmtAnn = $pdo->prepare("SELECT libelle_annee, date_debut_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
            $stmtAnn->execute([$anneeCode]);
            $annRow = $stmtAnn->fetch(PDO::FETCH_ASSOC);
            if ($annRow) {
                if (!empty($annRow['libelle_annee']) && preg_match('/(\d{4})/', $annRow['libelle_annee'], $m)) {
                    $anneeSuffix = substr($m[1], -2);
                } elseif (!empty($annRow['date_debut_annee'])) {
                    $anneeSuffix = date('y', strtotime($annRow['date_debut_annee']));
                }
            }
        }

        // Format: XX-123/GEB/AA26
        $matricule = sprintf('%s-%d/%s/%s%s', $initiales, $nextOrder, $sigleEtab, $filiereCodeLetters, $anneeSuffix);

        // Vérifier l'unicité
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM " . TABLES::ETUDIANTS . " WHERE matricule_etudiant = ?");
        $stmtCheck->execute([$matricule]);
        if ((int)$stmtCheck->fetchColumn() > 0) {
            $matricule = sprintf('%s-%d%02d/%s/%s%s', $initiales, $nextOrder, rand(1, 99), $sigleEtab, $filiereCodeLetters, $anneeSuffix);
        }

        return $matricule;
    }

    /**
     * Récupère la liste des étudiants avec leurs inscriptions annuelles et filtres dynamiques
     */
    public function getFilteredRegistry(array $filters = []): array
    {
        $pdo = $this->getCon();
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['annee_code']) && $filters['annee_code'] !== 'ALL') {
            $where[] = "i.annee_code = ?";
            $params[] = $filters['annee_code'];
        }

        if (!empty($filters['niveau_code']) && $filters['niveau_code'] !== 'ALL') {
            $where[] = "cl.niveau_code = ?";
            $params[] = $filters['niveau_code'];
        }

        if (!empty($filters['filiere_code']) && $filters['filiere_code'] !== 'ALL') {
            $where[] = "cl.filiere_code = ?";
            $params[] = $filters['filiere_code'];
        }

        if (!empty($filters['classe_code']) && $filters['classe_code'] !== 'ALL') {
            $where[] = "i.classe_code = ?";
            $params[] = $filters['classe_code'];
        }

        if (!empty($filters['statut_etudiant']) && $filters['statut_etudiant'] !== 'ALL') {
            $where[] = "e.statut_etudiant = ?";
            $params[] = $filters['statut_etudiant'];
        }

        if (!empty($filters['affectation_etat']) && $filters['affectation_etat'] !== 'ALL') {
            $val = $filters['affectation_etat'];
            if ($val === 'affecte') {
                $where[] = "(i.affectation_etat = 'affecte' OR i.affectation_etat = 'oui')";
            } else {
                $where[] = "(i.affectation_etat = 'non_affecte' OR i.affectation_etat = 'non' OR i.affectation_etat IS NULL)";
            }
        }

        if (!empty($filters['canal_inscription']) && $filters['canal_inscription'] !== 'ALL') {
            $where[] = "i.canal_inscription = ?";
            $params[] = $filters['canal_inscription'];
        }

        if (!empty($filters['statut_inscription'])) {
            if ($filters['statut_inscription'] === 'ACTIFS') {
                $where[] = "i.statut_inscription != '" . STATUTS::ANNULE . "'";
            } elseif ($filters['statut_inscription'] !== 'ALL') {
                $where[] = "i.statut_inscription = ?";
                $params[] = $filters['statut_inscription'];
            }
        } else {
            // Par défaut : aucune inscription annulée sauf si explicitement demandé
            $where[] = "i.statut_inscription != '" . STATUTS::ANNULE . "'";
        }

        $whereClause = implode(" AND ", $where);

        $sql = "
            SELECT 
                e.id_etudiant,
                e.code_etudiant,
                e.matricule_etudiant,
                e.matricule_menet,
                e.matricule_mesrs,
                e.nom_etudiant,
                e.prenom_etudiant,
                e.sexe_etudiant,
                e.telephone_etudiant,
                e.email_etudiant,
                e.photo_etudiant,
                e.statut_etudiant,
                e.created_at_etudiant,
                i.affectation_etat,
                i.id_inscription,
                i.code_inscription,
                i.canal_inscription,
                i.statut_inscription,
                i.montant_scolarite_inscription,
                i.created_at_inscription,
                (SELECT COUNT(*) FROM " . TABLES::PAIEMENTS . " p WHERE p.inscription_code = i.code_inscription AND p.statut_paiement != '" . STATUTS::ANNULE . "') AS total_paiements_count,
                a.code_annee,
                a.libelle_annee,
                cl.code_classe,
                cl.libelle_classe,
                f.code_filiere,
                f.libelle_filiere,
                f.slug_filiere,
                n.code_niveau,
                n.libelle_niveau,
                n.slug_niveau
            FROM " . TABLES::ETUDIANTS . " e
            JOIN " . TABLES::INSCRIPTIONS . " i ON i.etudiant_code = e.code_etudiant
            JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
            JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
            JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
            JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
            WHERE {$whereClause}
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC, i.id_inscription DESC
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getFilteredRegistry error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les dépendances pour les filtres de la liste des étudiants
     */
    public function getListFilterDependencies(): array
    {
        $db = $this->getCon();
        try {
            $annees = $db->query("SELECT * FROM " . TABLES::ANNEES . " ORDER BY (CASE WHEN statut_annee = '" . STATUTS::ACTIF . "' THEN 1 ELSE 2 END), date_debut_annee DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $niveaux = $db->query("SELECT * FROM " . TABLES::NIVEAUX . " WHERE statut_niveau = '" . STATUTS::ACTIF . "' ORDER BY libelle_niveau ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $filieres = $db->query("SELECT * FROM " . TABLES::FILIERES . " WHERE statut_filiere = '" . STATUTS::ACTIF . "' ORDER BY libelle_filiere ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $classes = $db->query("SELECT * FROM " . TABLES::CLASSES . " WHERE statut_classe = '" . STATUTS::ACTIF . "' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            return [
                'annees' => $annees,
                'niveaux' => $niveaux,
                'filieres' => $filieres,
                'classes' => $classes
            ];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getListFilterDependencies error: " . $e->getMessage());
            return ['annees' => [], 'niveaux' => [], 'filieres' => [], 'classes' => []];
        }
    }

    /**
     * Récupère les données d'inscription et de classes pour le formulaire de changement de classe
     */
    public function getChangerClasseData(string $etudiantCode, string $anneeActive): array
    {
        $db = $this->getCon();
        try {
            $stmtIns = $db->prepare("
                SELECT i.*, cl.libelle_classe, f.libelle_filiere, n.libelle_niveau
                FROM " . TABLES::INSCRIPTIONS . " i
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                WHERE i.etudiant_code = ? AND i.statut_inscription != '" . STATUTS::ANNULE . "'
                ORDER BY (i.annee_code = ?) DESC, i.id_inscription DESC
                LIMIT 1
            ");
            $stmtIns->execute([$etudiantCode, $anneeActive]);
            $inscription = $stmtIns->fetch(PDO::FETCH_ASSOC) ?: [];

            $stmtCl = $db->query("
                SELECT code_classe, libelle_classe, filiere_code, niveau_code, annee_code
                FROM " . TABLES::CLASSES . " 
                WHERE statut_classe = '" . STATUTS::ACTIF . "'
                ORDER BY libelle_classe ASC
            ");
            $classes = $stmtCl ? $stmtCl->fetchAll(PDO::FETCH_ASSOC) : [];

            $stmtAnn = $db->query("SELECT * FROM " . TABLES::ANNEES . " ORDER BY id_annee DESC");
            $annees = $stmtAnn ? $stmtAnn->fetchAll(PDO::FETCH_ASSOC) : [];

            return [
                'inscription' => $inscription,
                'classes' => $classes ?: [],
                'annees' => $annees ?: []
            ];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getChangerClasseData error: " . $e->getMessage());
            return ['inscription' => [], 'classes' => [], 'annees' => []];
        }
    }

    /**
     * Effectue le changement de classe, de régime et de scolarité pour une inscription
     */
    public function changerClasseInscription(string $codeInscription, string $codeEtudiant, string $nouvelleClasse, string $affectationEtat, string $anneeCode, ?float $montantScolarite = null, ?string $userCode = null, ?string $etabCode = null): array
    {
        $db = $this->getCon();
        try {
            $stmtCl = $db->prepare("SELECT code_classe, libelle_classe, filiere_code, niveau_code FROM " . TABLES::CLASSES . " WHERE code_classe = ? AND statut_classe = '" . STATUTS::ACTIF . "' LIMIT 1");
            $stmtCl->execute([$nouvelleClasse]);
            $classeRow = $stmtCl->fetch(PDO::FETCH_ASSOC);

            if (!$classeRow) {
                return ['success' => false, 'message' => "La classe sélectionnée est introuvable ou inactive."];
            }

            $inscriptionRow = null;
            if (!empty($codeInscription)) {
                $stmtIns = $db->prepare("
                    SELECT i.*, cl.filiere_code as ancienne_filiere_code, cl.niveau_code as ancien_niveau_code, cl.libelle_classe as ancien_libelle_classe
                    FROM " . TABLES::INSCRIPTIONS . " i
                    LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
                    WHERE i.code_inscription = ? AND i.statut_inscription != '" . STATUTS::ANNULE . "' 
                    LIMIT 1
                ");
                $stmtIns->execute([$codeInscription]);
                $inscriptionRow = $stmtIns->fetch(PDO::FETCH_ASSOC);
            }

            if (!$inscriptionRow && !empty($codeEtudiant)) {
                $stmtIns = $db->prepare("
                    SELECT i.*, cl.filiere_code as ancienne_filiere_code, cl.niveau_code as ancien_niveau_code, cl.libelle_classe as ancien_libelle_classe
                    FROM " . TABLES::INSCRIPTIONS . " i
                    LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
                    WHERE i.etudiant_code = ? AND i.statut_inscription != '" . STATUTS::ANNULE . "' 
                    ORDER BY (i.annee_code = ?) DESC, i.id_inscription DESC 
                    LIMIT 1
                ");
                $stmtIns->execute([$codeEtudiant, $anneeCode]);
                $inscriptionRow = $stmtIns->fetch(PDO::FETCH_ASSOC);
            }

            if (!$inscriptionRow) {
                return ['success' => false, 'message' => "Aucun dossier d'inscription actif n'a été trouvé pour cet étudiant."];
            }

            if ($montantScolarite === null || $montantScolarite <= 0) {
                $regimeScol = ($affectationEtat === 'oui') ? 'affecte' : 'non_affecte';
                $stmtSco = $db->prepare("SELECT montant_scolarite FROM " . TABLES::SCOLARITES . " WHERE filiere_code = ? AND niveau_code = ? AND annee_code = ? AND affectation_etat = ? AND statut_scolarite = '" . STATUTS::ACTIF . "' ORDER BY id_scolarite DESC LIMIT 1");
                $stmtSco->execute([$classeRow['filiere_code'], $classeRow['niveau_code'], $anneeCode, $regimeScol]);
                $sco = $stmtSco->fetch(PDO::FETCH_ASSOC);
                if ($sco) {
                    $montantScolarite = (float)$sco['montant_scolarite'];
                } else {
                    $montantScolarite = (float)($inscriptionRow['montant_scolarite_inscription'] ?? 0);
                }
            }

            // Gestion de l'annulation des versements antérieurs si la classe change et que le montant est différent
            $classeChangee = ($inscriptionRow['classe_code'] !== $nouvelleClasse);
            $ancienMontant = (float)($inscriptionRow['montant_scolarite_inscription'] ?? 0);
            $montantDifferent = (abs($ancienMontant - (float)$montantScolarite) > 0.01);
            $nbPaiementsAnnules = 0;
            $totalMontantAnnule = 0.0;

            if ($classeChangee && $montantDifferent && !empty($inscriptionRow['code_inscription'])) {
                // Recherche des versements de scolarité actifs de l'ancienne classe
                $stmtPaiements = $db->prepare("
                    SELECT id_paiement, code_paiement, montant_paiement, observations
                    FROM " . TABLES::PAIEMENTS . "
                    WHERE inscription_code = ? 
                      AND statut_paiement != '" . STATUTS::ANNULE . "'
                      AND (categorie_paiement = 'SCOLARITE' OR categorie_paiement IS NULL OR categorie_paiement = '')
                ");
                $stmtPaiements->execute([$inscriptionRow['code_inscription']]);
                $oldPayments = $stmtPaiements->fetchAll(PDO::FETCH_ASSOC) ?: [];

                if (!empty($oldPayments)) {
                    $motifAnnulation = sprintf(
                        " [ANNULATION le %s : Changement de classe de %s (%s FCFA) vers %s (%s FCFA) avec tarification différente]",
                        date('d/m/Y H:i'),
                        $inscriptionRow['classe_code'] ?? 'ancienne',
                        number_format($ancienMontant, 0, ',', ' '),
                        $nouvelleClasse,
                        number_format((float)$montantScolarite, 0, ',', ' ')
                    );

                    $stmtCancelPay = $db->prepare("
                        UPDATE " . TABLES::PAIEMENTS . "
                        SET statut_paiement = '" . STATUTS::ANNULE . "',
                            observations = CONCAT(COALESCE(observations, ''), ?)
                        WHERE id_paiement = ?
                    ");

                    foreach ($oldPayments as $op) {
                        $stmtCancelPay->execute([$motifAnnulation, $op['id_paiement']]);
                        $nbPaiementsAnnules++;
                        $totalMontantAnnule += (float)($op['montant_paiement'] ?? 0);
                    }
                }
            }

            // Option A : Gestion de l'inactivation des notes si la filière ou le niveau d'études change (réorientation)
            $ancienneFiliere = $inscriptionRow['ancienne_filiere_code'] ?? '';
            $ancienNiveau = $inscriptionRow['ancien_niveau_code'] ?? '';
            $nouvelleFiliere = $classeRow['filiere_code'] ?? '';
            $nouveauNiveau = $classeRow['niveau_code'] ?? '';
            $isReorientation = ($classeChangee && (!empty($ancienneFiliere) && $ancienneFiliere !== $nouvelleFiliere || !empty($ancienNiveau) && $ancienNiveau !== $nouveauNiveau));
            $nbNotesDesactivees = 0;

            if ($isReorientation && !empty($inscriptionRow['code_inscription'])) {
                $stmtCountNotes = $db->prepare("
                    SELECT COUNT(*) 
                    FROM " . TABLES::NOTES . " 
                    WHERE inscription_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
                ");
                $stmtCountNotes->execute([$inscriptionRow['code_inscription']]);
                $nbNotesDesactivees = (int)$stmtCountNotes->fetchColumn();

                if ($nbNotesDesactivees > 0) {
                    $motifNote = sprintf(
                        " [NOTE INACTIVE le %s : Réorientation de %s vers %s]",
                        date('d/m/Y H:i'),
                        $inscriptionRow['ancien_libelle_classe'] ?? ($inscriptionRow['classe_code'] ?? 'ancienne classe'),
                        $classeRow['libelle_classe'] ?? $nouvelleClasse
                    );

                    $stmtUpdateNotes = $db->prepare("
                        UPDATE " . TABLES::NOTES . "
                        SET statut_note = 'inactif',
                            observations = CONCAT(COALESCE(observations, ''), ?),
                            updated_at_note = NOW()
                        WHERE inscription_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
                    ");
                    $stmtUpdateNotes->execute([$motifNote, $inscriptionRow['code_inscription']]);
                }
            }

            // Option 1 : Synchronisation intelligente des kits / accessoires scolaires
            $nbKitsDesactives = 0;
            $nbKitsAjoutes = 0;
            $nbKitsReactives = 0;

            if ($classeChangee && !empty($inscriptionRow['code_inscription'])) {
                try {
                    // 1. Type de filière de la nouvelle classe (INDUSTRIELLE / TERTIAIRE)
                    $nouveauTypeFiliere = '';
                    if (!empty($classeRow['filiere_code'])) {
                        $stmtTypeFil = $db->prepare("SELECT type_filiere FROM " . TABLES::FILIERES . " WHERE code_filiere = ? LIMIT 1");
                        $stmtTypeFil->execute([$classeRow['filiere_code']]);
                        $nouveauTypeFiliere = strtoupper(trim((string)$stmtTypeFil->fetchColumn()));
                    }

                    // 2. Kits éligibles pour la nouvelle classe
                    $sqlEligible = "
                        SELECT DISTINCT a.code_accessoire, a.libelle_accessoire
                        FROM " . TABLES::ACCESSOIRES . " a
                        LEFT JOIN " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . " anf ON anf.accessoire_code = a.code_accessoire
                        WHERE a.statut_accessoire = '" . STATUTS::ACTIF . "'
                          AND (anf.type_filiere_cible = 'TOUT' OR anf.type_filiere_cible IS NULL OR anf.type_filiere_cible = ''" . (!empty($nouveauTypeFiliere) ? " OR anf.type_filiere_cible = ?" : "") . ")
                          AND (anf.niveau_code IS NULL OR anf.niveau_code = '' OR anf.niveau_code = 'TOUT'" . (!empty($classeRow['niveau_code']) ? " OR anf.niveau_code = ?" : "") . ")
                    ";
                    $paramsEligible = [];
                    if (!empty($nouveauTypeFiliere)) $paramsEligible[] = $nouveauTypeFiliere;
                    if (!empty($classeRow['niveau_code'])) $paramsEligible[] = $classeRow['niveau_code'];

                    $stmtEligible = $db->prepare($sqlEligible);
                    $stmtEligible->execute($paramsEligible);
                    $eligibleKits = $stmtEligible->fetchAll(PDO::FETCH_ASSOC) ?: [];
                    $eligibleCodes = array_column($eligibleKits, 'code_accessoire');

                    // 3. Kits actuels de cette inscription
                    $stmtCurrentKits = $db->prepare("
                        SELECT id_accessoire_inscription, code_accessoire_inscription, accessoire_code, etat_retrait, statut_accessoire_inscription
                        FROM " . TABLES::ACCESSOIRE_INSCRIPTION . "
                        WHERE inscription_code = ?
                    ");
                    $stmtCurrentKits->execute([$inscriptionRow['code_inscription']]);
                    $currentKits = $stmtCurrentKits->fetchAll(PDO::FETCH_ASSOC) ?: [];

                    $currentMapByCode = [];
                    foreach ($currentKits as $ck) {
                        $currentMapByCode[$ck['accessoire_code']] = $ck;
                    }

                    // 4. Désactiver les kits non retirés ne correspondant plus
                    $stmtDeactivateKit = $db->prepare("
                        UPDATE " . TABLES::ACCESSOIRE_INSCRIPTION . "
                        SET statut_accessoire_inscription = 'inactif'
                        WHERE id_accessoire_inscription = ?
                    ");

                    foreach ($currentKits as $ck) {
                        // Kits déjà retirés en main propre : strictement conservés
                        if ($ck['etat_retrait'] === 'retire') {
                            continue;
                        }

                        // Kits en attente non éligibles à la nouvelle classe : désactivation
                        if (!in_array($ck['accessoire_code'], $eligibleCodes) && ($ck['statut_accessoire_inscription'] ?? '') === STATUTS::ACTIF) {
                            $stmtDeactivateKit->execute([$ck['id_accessoire_inscription']]);
                            $nbKitsDesactives++;
                        }
                    }

                    // 5. Nouveaux kits éligibles : ajout ou réactivation
                    $stmtReactivateKit = $db->prepare("
                        UPDATE " . TABLES::ACCESSOIRE_INSCRIPTION . "
                        SET statut_accessoire_inscription = '" . STATUTS::ACTIF . "'
                        WHERE id_accessoire_inscription = ?
                    ");

                    $stmtInsertKit = $db->prepare("
                        INSERT INTO " . TABLES::ACCESSOIRE_INSCRIPTION . "
                        (code_accessoire_inscription, inscription_code, accessoire_code, annee_code, statut_accessoire_inscription, etat_retrait, user_code, etablissement_code, created_at_accessoire_inscription)
                        VALUES (?, ?, ?, ?, 'actif', 'en_attente', ?, ?, NOW())
                    ");

                    $targetAnnee = !empty($anneeCode) ? $anneeCode : ($inscriptionRow['annee_code'] ?? '');
                    $targetEtab = !empty($etabCode) ? $etabCode : ($inscriptionRow['etablissement_code'] ?? null);
                    $targetUser = !empty($userCode) ? $userCode : ($inscriptionRow['user_code'] ?? null);

                    foreach ($eligibleCodes as $elCode) {
                        if (isset($currentMapByCode[$elCode])) {
                            $existing = $currentMapByCode[$elCode];
                            if ($existing['statut_accessoire_inscription'] !== STATUTS::ACTIF) {
                                $stmtReactivateKit->execute([$existing['id_accessoire_inscription']]);
                                $nbKitsReactives++;
                            }
                        } else {
                            do {
                                $newAccCode = 'ACI-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                                $stmtChkAcc = $db->prepare("SELECT COUNT(*) FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " WHERE code_accessoire_inscription = ?");
                                $stmtChkAcc->execute([$newAccCode]);
                            } while ((int)$stmtChkAcc->fetchColumn() > 0);

                            $stmtInsertKit->execute([
                                $newAccCode,
                                $inscriptionRow['code_inscription'],
                                $elCode,
                                $targetAnnee,
                                $targetUser,
                                $targetEtab
                            ]);
                            $nbKitsAjoutes++;
                        }
                    }
                } catch (Exception $eAcc) {
                    error_log("ModelEtudiant::changerClasseInscription sync kits error: " . $eAcc->getMessage());
                }
            }

            // Si des paiements ont été annulés, réajuster le statut d'inscription s'il était soldé
            $nouveauStatutInscription = ($nbPaiementsAnnules > 0 && ($inscriptionRow['statut_inscription'] ?? '') === STATUTS::SOLDE) 
                ? STATUTS::VALIDE 
                : ($inscriptionRow['statut_inscription'] ?? STATUTS::VALIDE);

            $stmtUpdate = $db->prepare("
                UPDATE " . TABLES::INSCRIPTIONS . " 
                SET classe_code = ?,
                    affectation_etat = ?,
                    montant_scolarite_inscription = ?,
                    statut_inscription = ?,
                    annee_code = COALESCE(NULLIF(?, ''), annee_code),
                    updated_at_inscription = NOW()
                WHERE id_inscription = ?
            ");
            $ok = $stmtUpdate->execute([
                $nouvelleClasse,
                $affectationEtat,
                $montantScolarite,
                $nouveauStatutInscription,
                $anneeCode,
                $inscriptionRow['id_inscription']
            ]);

            if ($ok) {
                $msg = "La classe et le régime de l'étudiant ont été modifiés avec succès vers " . $classeRow['libelle_classe'] . " !";
                if ($nbPaiementsAnnules > 0) {
                    $msg .= " Le montant de scolarité ayant changé (" . number_format($ancienMontant, 0, ',', ' ') . " à " . number_format((float)$montantScolarite, 0, ',', ' ') . " FCFA), " . $nbPaiementsAnnules . " versement(s) antérieur(s) (" . number_format($totalMontantAnnule, 0, ',', ' ') . " FCFA) ont été annulés pour régularisation sur le nouvel échéancier.";
                }
                if ($nbNotesDesactivees > 0) {
                    $msg .= " La filière ou le niveau ayant changé, " . $nbNotesDesactivees . " note(s) de l'ancienne filière ont été archivées (inactives) pour initialiser la nouvelle maquette pédagogique.";
                }
                if ($nbKitsDesactives > 0 || $nbKitsAjoutes > 0 || $nbKitsReactives > 0) {
                    $kitDetails = [];
                    if ($nbKitsDesactives > 0) $kitDetails[] = "{$nbKitsDesactives} kit(s) non retiré(s) désactivé(s)";
                    if ($nbKitsAjoutes > 0) $kitDetails[] = "{$nbKitsAjoutes} nouveau(x) kit(s) attribué(s) en attente";
                    if ($nbKitsReactives > 0) $kitDetails[] = "{$nbKitsReactives} kit(s) réactivé(s)";
                    $msg .= " Dotation kits actualisée : " . implode(', ', $kitDetails) . " (les kits déjà retirés en main propre restent conservés).";
                }
                return [
                    'success' => true,
                    'message' => $msg,
                    'classe_libelle' => $classeRow['libelle_classe'],
                    'paiements_annules_count' => $nbPaiementsAnnules,
                    'paiements_annules_montant' => $totalMontantAnnule,
                    'notes_desactivees_count' => $nbNotesDesactivees,
                    'kits_desactives_count' => $nbKitsDesactives,
                    'kits_ajoutes_count' => $nbKitsAjoutes,
                    'kits_reactives_count' => $nbKitsReactives
                ];
            }
            return ['success' => false, 'message' => "Erreur lors de la mise à jour de la classe de l'étudiant."];
        } catch (Exception $e) {
            error_log("ModelEtudiant::changerClasseInscription error: " . $e->getMessage());
            return ['success' => false, 'message' => "Erreur lors de la mise à jour de la classe de l'étudiant."];
        }
    }

    /**
     * Récupère l'ensemble complet des détails d'un étudiant (parent, inscription, paiements, absences, dossier)
     */
    public function getStudentFullDetails(string $etudiantCode): array
    {
        $db = $this->getCon();

        // 1. Parent info
        $parent = [];
        try {
            $stmtPar = $db->prepare("SELECT * FROM " . TABLES::PARENTS . " WHERE etudiant_code = ? LIMIT 1");
            $stmtPar->execute([$etudiantCode]);
            $parent = $stmtPar->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getStudentFullDetails parent: " . $e->getMessage());
        }

        // 2. Inscription active & Classe
        $inscription = [];
        try {
            $stmtIns = $db->prepare("
                SELECT ins.*, cl.libelle_classe, f.libelle_filiere, n.libelle_niveau, a.libelle_annee
                FROM " . TABLES::INSCRIPTIONS . " ins
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ins.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = ins.annee_code
                WHERE ins.etudiant_code = ? AND ins.statut_inscription != '" . STATUTS::ANNULE . "'
                ORDER BY ins.id_inscription DESC LIMIT 1
            ");
            $stmtIns->execute([$etudiantCode]);
            $inscription = $stmtIns->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getStudentFullDetails inscription: " . $e->getMessage());
        }

        // 2.bis Établissement
        $etablissement = [];
        try {
            $etablissement = $db->query("SELECT * FROM " . TABLES::ETABLISSEMENTS . " LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getStudentFullDetails etablissement: " . $e->getMessage());
        }

        // 3. Paiements effectués
        $paiements = [];
        try {
            $stmtPaiements = $db->prepare("
                SELECT p.*, a.libelle_annee, ins.code_inscription
                FROM " . TABLES::PAIEMENTS . " p
                JOIN " . TABLES::INSCRIPTIONS . " ins ON ins.code_inscription = p.inscription_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = p.annee_code
                WHERE ins.etudiant_code = ?
                ORDER BY p.date_paiement DESC
            ");
            $stmtPaiements->execute([$etudiantCode]);
            $paiements = $stmtPaiements->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getStudentFullDetails paiements: " . $e->getMessage());
        }

        // 4. Statistiques financières calculées dynamiquement (Séparation stricte Scolarité / Frais Annexes - Règle 4)
        $scolariteTotale = (float)($inscription['montant_scolarite_inscription'] ?? 0);
        $totalPaye = 0.0;
        $activeInscrCode = $inscription['code_inscription'] ?? '';
        foreach ($paiements as $p) {
            $cat = strtoupper($p['categorie_paiement'] ?? '');
            $tranche = strtoupper($p['tranche_code'] ?? '');
            $type = strtolower($p['type_paiement'] ?? '');
            $isFraisAnnexe = ($cat === 'FRAIS_ANNEXES' || $tranche === 'FRAIS_ANNEXES' || strpos($type, 'annexe') !== false);

            if (($p['statut_paiement'] ?? '') !== STATUTS::ANNULE && !$isFraisAnnexe) {
                // Imputer uniquement sur l'inscription active affichée
                if (empty($activeInscrCode) || ($p['inscription_code'] ?? '') === $activeInscrCode) {
                    $totalPaye += (float)($p['montant_paiement'] ?? ($p['montant_paye'] ?? 0));
                }
            }
        }
        $soldeRestant = max(0, $scolariteTotale - $totalPaye);

        // 5. Absences
        $absences = [];
        try {
            $stmtAbs = $db->prepare("
                SELECT abs.*, m.libelle_matiere
                FROM " . TABLES::ABSENCES . " abs
                JOIN " . TABLES::INSCRIPTIONS . " ins ON ins.code_inscription = abs.inscription_code
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = abs.matiere_code
                WHERE ins.etudiant_code = ?
                ORDER BY abs.date_absence DESC
            ");
            $stmtAbs->execute([$etudiantCode]);
            $absences = $stmtAbs->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getStudentFullDetails absences: " . $e->getMessage());
        }

        // 6. Dossier / Pièces physiques
        $dossierPieces = [];
        try {
            $stmtDossier = $db->prepare("
                SELECT pf.libelle_piece, pf.description_piece, COALESCE(de.statut_depot, 'en_attente') AS statut_depot, de.date_depot, de.observations, de.id_dossier_etudiant
                FROM " . TABLES::PIECES_FOURNIR . " pf
                LEFT JOIN " . TABLES::DOSSIER_ETUDIANT . " de ON de.piece_code = pf.code_piece_fournir AND de.etudiant_code = ?
                WHERE pf.statut_piece = '" . STATUTS::ACTIF . "'
                ORDER BY pf.id_piece_fournir ASC
            ");
            $stmtDossier->execute([$etudiantCode]);
            $dossierPieces = $stmtDossier->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getStudentFullDetails dossierPieces: " . $e->getMessage());
        }

        return [
            'parent' => $parent,
            'inscription' => $inscription,
            'etablissement' => $etablissement,
            'paiements' => $paiements,
            'totalPaye' => $totalPaye,
            'soldeRestant' => $soldeRestant,
            'scolariteTotale' => $scolariteTotale,
            'absences' => $absences,
            'dossierPieces' => $dossierPieces
        ];
    }

    /**
     * Récupère l'ensemble des données requises pour la génération de la Fiche de réinscription & Bilan académique
     *
     * @param string|int $studentIdentifier ID, code ou matricule de l'étudiant, ou ID/code d'inscription
     * @param string|int|null $inscriptionIdentifier ID ou code d'inscription optionnel
     * @return array|null
     */
    public function getStudentFicheData($studentIdentifier, $inscriptionIdentifier = null): ?array
    {
        $db = $this->getCon();
        $inscription = null;
        $etudiant = null;

        // 1. Si un identifiant d'inscription explicite est fourni
        if (!empty($inscriptionIdentifier)) {
            $stmtIns = $db->prepare("
                SELECT ins.*, cl.libelle_classe, cl.filiere_code, cl.niveau_code,
                       f.libelle_filiere, n.libelle_niveau, a.libelle_annee, a.code_annee as annee_code_reel
                FROM " . TABLES::INSCRIPTIONS . " ins
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ins.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = ins.annee_code
                WHERE (ins.id_inscription = ? OR ins.code_inscription = ?)
                  AND ins.statut_inscription != '" . STATUTS::ANNULE . "'
                LIMIT 1
            ");
            $stmtIns->execute([
                is_numeric($inscriptionIdentifier) ? (int)$inscriptionIdentifier : 0,
                (string)$inscriptionIdentifier
            ]);
            $inscription = $stmtIns->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        // 2. Si non trouvé et $studentIdentifier correspond à une inscription
        if (!$inscription && !empty($studentIdentifier)) {
            $stmtTryIns = $db->prepare("
                SELECT ins.*, cl.libelle_classe, cl.filiere_code, cl.niveau_code,
                       f.libelle_filiere, n.libelle_niveau, a.libelle_annee, a.code_annee as annee_code_reel
                FROM " . TABLES::INSCRIPTIONS . " ins
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ins.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = ins.annee_code
                WHERE (ins.id_inscription = ? OR ins.code_inscription = ?)
                  AND ins.statut_inscription != '" . STATUTS::ANNULE . "'
                LIMIT 1
            ");
            $stmtTryIns->execute([
                is_numeric($studentIdentifier) ? (int)$studentIdentifier : 0,
                (string)$studentIdentifier
            ]);
            $tryIns = $stmtTryIns->fetch(PDO::FETCH_ASSOC);
            if ($tryIns) {
                $inscription = $tryIns;
                $studentIdentifier = $tryIns['etudiant_code'];
            }
        }

        // 3. Récupération du dossier étudiant
        $stmtEtu = $db->prepare("
            SELECT * FROM " . TABLES::ETUDIANTS . "
            WHERE id_etudiant = ? OR code_etudiant = ? OR matricule_etudiant = ?
            LIMIT 1
        ");
        $stmtEtu->execute([
            is_numeric($studentIdentifier) ? (int)$studentIdentifier : 0,
            (string)$studentIdentifier,
            (string)$studentIdentifier
        ]);
        $etudiant = $stmtEtu->fetch(PDO::FETCH_ASSOC);

        if (!$etudiant) {
            return null;
        }

        // 4. Si pas encore d'inscription associée, chercher la dernière inscription active de l'étudiant
        if (!$inscription) {
            $stmtLatestIns = $db->prepare("
                SELECT ins.*, cl.libelle_classe, cl.filiere_code, cl.niveau_code,
                       f.libelle_filiere, n.libelle_niveau, a.libelle_annee, a.code_annee as annee_code_reel
                FROM " . TABLES::INSCRIPTIONS . " ins
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ins.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = ins.annee_code
                WHERE (ins.etudiant_code = ? OR ins.etudiant_code = ?)
                  AND ins.statut_inscription != '" . STATUTS::ANNULE . "'
                ORDER BY ins.id_inscription DESC
                LIMIT 1
            ");
            $stmtLatestIns->execute([$etudiant['code_etudiant'], $etudiant['matricule_etudiant']]);
            $inscription = $stmtLatestIns->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        // 5. Coordonnées parents / tuteurs
        $stmtPar = $db->prepare("
            SELECT * FROM " . TABLES::PARENTS . "
            WHERE etudiant_code = ? OR etudiant_code = ?
            LIMIT 1
        ");
        $stmtPar->execute([$etudiant['code_etudiant'], $etudiant['matricule_etudiant']]);
        $parent = $stmtPar->fetch(PDO::FETCH_ASSOC) ?: [];

        // 6. Situation financière de l'ancienne classe (N-1)
        $totalScolariteN1 = (float)($inscription['montant_scolarite_inscription'] ?? 0);
        $totalPayeN1 = 0;
        if (!empty($inscription['code_inscription'])) {
            try {
                $stmtPay = $db->prepare("
                    SELECT SUM(montant_paiement) as total_paye
                    FROM " . TABLES::PAIEMENTS . "
                    WHERE inscription_code = ? AND statut_paiement != '" . STATUTS::ANNULE . "'
                ");
                $stmtPay->execute([$inscription['code_inscription']]);
                $rowPay = $stmtPay->fetch(PDO::FETCH_ASSOC);
                $totalPayeN1 = (float)($rowPay['total_paye'] ?? 0);
            } catch (Exception $e) {
                error_log("ModelEtudiant::getStudentFicheData paiements: " . $e->getMessage());
            }
        }
        $soldeN1 = max(0, $totalScolariteN1 - $totalPayeN1);

        // 7. Bilan Académique & Décision du Jury / Conseil
        $moyenneN1 = null;
        $mentionN1 = 'Non évalué';
        $decisionN1 = 'Autorisé(e) à se réinscrire';
        $decisionBadge = 'info'; // success | warning | danger | info

        if (!empty($inscription['id_inscription'])) {
            try {
                require_once __DIR__ . '/../bulletin/ModelBulletin.php';
                $modelBulletin = new ModelBulletin();
                $bulletinData = $modelBulletin->calculateBulletinData((int)$inscription['id_inscription']);
                if ($bulletinData && isset($bulletinData['moyenneGenerale'])) {
                    $mg = (float)$bulletinData['moyenneGenerale'];
                    if ($mg > 0 || !empty($bulletinData['notesDetails'])) {
                        $moyenneN1 = $mg;
                        $mentionN1 = !empty($bulletinData['mention']) ? $bulletinData['mention'] : ($mg >= 10 ? 'Passable' : 'Ajourné(e)');
                        if ($mg >= 10) {
                            $decisionN1 = 'Admis(e) en classe supérieure';
                            $decisionBadge = 'success';
                        } else {
                            $decisionN1 = 'Ajourné(e) / Redoublement';
                            $decisionBadge = 'warning';
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("ModelEtudiant::getStudentFicheData bulletin: " . $e->getMessage());
            }
        }

        // 8. Calcul de la classe cible / Orientation pour la réinscription
        $anneeActiveLibelle = '';
        $anneeActiveCode = '';
        try {
            $stmtActAnn = $db->query("SELECT code_annee, libelle_annee FROM " . TABLES::ANNEES . " WHERE statut_annee = '" . STATUTS::ACTIF . "' ORDER BY id_annee DESC LIMIT 1");
            $actAnn = $stmtActAnn ? $stmtActAnn->fetch(PDO::FETCH_ASSOC) : null;
            if ($actAnn) {
                $anneeActiveCode = $actAnn['code_annee'];
                $anneeActiveLibelle = $actAnn['libelle_annee'];
            }
        } catch (Exception $e) {}

        $classeCibleLibelle = $inscription['libelle_classe'] ?? '-';
        $filiereCibleLibelle = $inscription['libelle_filiere'] ?? '-';
        $niveauCibleLibelle = $inscription['libelle_niveau'] ?? '-';
        $scolariteCible = $totalScolariteN1;

        if ($decisionBadge === 'success' || $decisionBadge === 'info') {
            if (!empty($inscription['filiere_code']) && !empty($inscription['niveau_code'])) {
                try {
                    $stmtNiv = $db->prepare("SELECT id_niveau FROM " . TABLES::NIVEAUX . " WHERE code_niveau = ? LIMIT 1");
                    $stmtNiv->execute([$inscription['niveau_code']]);
                    $currNiv = $stmtNiv->fetch(PDO::FETCH_ASSOC);
                    if ($currNiv) {
                        $nextNivId = (int)$currNiv['id_niveau'] + 1;
                        $stmtNextCl = $db->prepare("
                            SELECT c.code_classe, c.libelle_classe, f.libelle_filiere, n.libelle_niveau
                            FROM " . TABLES::CLASSES . " c
                            JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
                            JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
                            WHERE c.filiere_code = ? 
                              AND n.id_niveau = ?
                              AND c.statut_classe = '" . STATUTS::ACTIF . "'
                            ORDER BY c.id_classe ASC
                            LIMIT 1
                        ");
                        $stmtNextCl->execute([$inscription['filiere_code'], $nextNivId]);
                        $nextCl = $stmtNextCl->fetch(PDO::FETCH_ASSOC);
                        if ($nextCl) {
                            $classeCibleLibelle = $nextCl['libelle_classe'];
                            $filiereCibleLibelle = $nextCl['libelle_filiere'] ?? $filiereCibleLibelle;
                            $niveauCibleLibelle = $nextCl['libelle_niveau'] ?? $niveauCibleLibelle;

                            $stmtTarif = $db->prepare("
                                SELECT montant_scolarite FROM " . TABLES::SCOLARITES . "
                                WHERE classe_code = ? AND statut_scolarite = '" . STATUTS::ACTIF . "'
                                LIMIT 1
                            ");
                            $stmtTarif->execute([$nextCl['code_classe']]);
                            $scolTarif = $stmtTarif->fetchColumn();
                            if ($scolTarif !== false && (float)$scolTarif > 0) {
                                $scolariteCible = (float)$scolTarif;
                            }
                        }
                    }
                } catch (Exception $e) {}
            }
        }

        // 9. Contrôle des pièces physiques du dossier
        $piecePhotos = false;
        $pieceExtrait = false;
        $pieceCni = false;
        $pieceDiplome = false;
        $pieceBulletins = false;
        $pieceAffectation = false;

        try {
            $stmtDossier = $db->prepare("
                SELECT pf.libelle_piece, COALESCE(de.statut_depot, 'en_attente') AS statut_depot
                FROM " . TABLES::PIECES_FOURNIR . " pf
                LEFT JOIN " . TABLES::DOSSIER_ETUDIANT . " de ON de.piece_code = pf.code_piece_fournir 
                     AND (de.etudiant_code = ? OR de.etudiant_code = ?)
                WHERE pf.statut_piece = '" . STATUTS::ACTIF . "'
                ORDER BY pf.id_piece_fournir ASC
            ");
            $stmtDossier->execute([$etudiant['code_etudiant'], $etudiant['matricule_etudiant']]);
            $dossierRows = $stmtDossier->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($dossierRows as $dr) {
                $lib = mb_strtolower($dr['libelle_piece'] ?? '', 'UTF-8');
                $isDep = ($dr['statut_depot'] === 'depose');
                if (strpos($lib, 'photo') !== false) $piecePhotos = $isDep;
                elseif (strpos($lib, 'extrait') !== false || strpos($lib, 'naissance') !== false) $pieceExtrait = $isDep;
                elseif (strpos($lib, 'cni') !== false || strpos($lib, 'identit') !== false) $pieceCni = $isDep;
                elseif (strpos($lib, 'diplome') !== false || strpos($lib, 'collante') !== false || strpos($lib, 'bac') !== false) $pieceDiplome = $isDep;
                elseif (strpos($lib, 'bulletin') !== false) $pieceBulletins = $isDep;
                elseif (strpos($lib, 'affect') !== false) $pieceAffectation = $isDep;
            }
        } catch (Exception $e) {
            error_log("ModelEtudiant::getStudentFicheData dossier: " . $e->getMessage());
        }

        $nomComplet = trim(($etudiant['nom_etudiant'] ?? '') . ' ' . ($etudiant['prenom_etudiant'] ?? ''));
        $sexeRaw = strtoupper($etudiant['sexe_etudiant'] ?? '');
        $sexeLibelle = ($sexeRaw === 'F' || strpos($sexeRaw, 'FEM') !== false) ? 'Féminin' : 'Masculin';
        $statutAff = (!empty($inscription['affectation_etat']) && in_array(strtolower($inscription['affectation_etat']), ['oui', 'affecte'])) ? 'Affecté (État)' : 'Non Affecté (Privé)';

        return [
            'etudiant' => $etudiant,
            'inscription' => $inscription,
            'parent' => $parent,
            'viewData' => [
                // Identité
                'matricule_etudiant' => !empty($etudiant['matricule_etudiant']) ? $etudiant['matricule_etudiant'] : '-',
                'code_inscription' => !empty($inscription['code_inscription']) ? $inscription['code_inscription'] : '-',
                'num_gpeicg' => !empty($etudiant['matricule_etudiant']) ? $etudiant['matricule_etudiant'] : (!empty($etudiant['code_etudiant']) ? $etudiant['code_etudiant'] : '-'),
                'nom_prenom_etudiant' => $nomComplet,
                'date_naissance' => !empty($etudiant['date_naissance_etudiant']) ? date('d/m/Y', strtotime($etudiant['date_naissance_etudiant'])) : '-',
                'lieu_naissance' => $etudiant['lieu_naissance_etudiant'] ?? '-',
                'sexe_etudiant' => $sexeLibelle,
                'nationalite' => $etudiant['nationalite_etudiant'] ?? 'Ivoirienne',
                'statut_affectation' => $statutAff,
                'num_piece_identite' => $etudiant['numero_cni'] ?? '-',
                'contact_etudiant' => $etudiant['telephone_etudiant'] ?? '-',
                'email_etudiant' => $etudiant['email_etudiant'] ?? '-',
                'adresse_etudiant' => $etudiant['lieu_residence_etudiant'] ?? '-',
                'photo_etudiant' => !empty($etudiant['photo_etudiant']) ? $etudiant['photo_etudiant'] : ($inscription['photo_inscription'] ?? ''),

                // Récap Ancienne Classe (N-1)
                'annee_precedente' => $inscription['libelle_annee'] ?? '-',
                'classe_precedente' => $inscription['libelle_classe'] ?? '-',
                'filiere_precedente' => $inscription['libelle_filiere'] ?? '-',
                'niveau_precedent' => $inscription['libelle_niveau'] ?? '-',
                'est_redoublant' => (!empty($inscription['est_redoublant']) && $inscription['est_redoublant'] == 1) ? 'OUI' : 'NON',

                // Résultats & Décision de passage
                'moyenne_n1' => $moyenneN1 !== null ? number_format($moyenneN1, 2, ',', ' ') . ' / 20' : 'Non renseignée',
                'mention_n1' => $mentionN1,
                'decision_n1' => $decisionN1,
                'decision_badge' => $decisionBadge,

                // Orientation & Nouvelle classe (Réinscription)
                'annee_reinscription' => !empty($anneeActiveLibelle) ? $anneeActiveLibelle : date('Y') . '-' . (date('Y') + 1),
                'classe_cible' => $classeCibleLibelle,
                'filiere_cible' => $filiereCibleLibelle,
                'niveau_cible' => $niveauCibleLibelle,
                'scolarite_cible' => $scolariteCible,

                // Bilan financier N-1
                'total_scolarite_n1' => $totalScolariteN1,
                'total_paye_n1' => $totalPayeN1,
                'solde_n1' => $soldeN1,

                // Coordonnées parents / tuteur
                'nom_pere' => $parent['nom_pere'] ?? '-',
                'contact_pere' => $parent['telephone_pere'] ?? '-',
                'nom_mere' => $parent['nom_mere'] ?? '-',
                'contact_mere' => $parent['telephone_mere'] ?? '-',
                'nom_tuteur' => $parent['nom_tuteur'] ?? '-',
                'contact_tuteur' => $parent['telephone_tuteur'] ?? '-',
                'profession_tuteur' => $parent['profession_tuteur'] ?? '-',
                'adresse_tuteur' => $parent['parent_habitation'] ?? '-',

                // Pièces du dossier
                'piece_photos' => $piecePhotos,
                'piece_extrait' => $pieceExtrait,
                'piece_cni' => $pieceCni,
                'piece_diplome' => $pieceDiplome,
                'piece_bulletins' => $pieceBulletins,
                'piece_affectation' => $pieceAffectation
            ]
        ];
    }

    /**
     * Récupère les intitulés lisibles des filtres pour l'export PDF
     */
    public function getFiltresLabels(array $filters): array
    {
        $db = $this->getCon();
        $filtresLabels = [
            'filiere' => 'Toutes les filières',
            'niveau' => 'Tous les niveaux',
            'classe' => 'Toutes les classes',
            'regime' => 'Tous les régimes'
        ];

        try {
            if (!empty($filters['filiere_code']) && $filters['filiere_code'] !== 'ALL') {
                $stmt = $db->prepare("SELECT libelle_filiere FROM " . TABLES::FILIERES . " WHERE code_filiere = ? LIMIT 1");
                $stmt->execute([$filters['filiere_code']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) $filtresLabels['filiere'] = $row['libelle_filiere'];
            }

            if (!empty($filters['niveau_code']) && $filters['niveau_code'] !== 'ALL') {
                $stmt = $db->prepare("SELECT libelle_niveau FROM " . TABLES::NIVEAUX . " WHERE code_niveau = ? LIMIT 1");
                $stmt->execute([$filters['niveau_code']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) $filtresLabels['niveau'] = $row['libelle_niveau'];
            }

            if (!empty($filters['classe_code']) && $filters['classe_code'] !== 'ALL') {
                $stmt = $db->prepare("
                    SELECT c.*, f.libelle_filiere, n.libelle_niveau 
                    FROM " . TABLES::CLASSES . " c 
                    LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code 
                    LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code 
                    WHERE c.code_classe = ? LIMIT 1
                ");
                $stmt->execute([$filters['classe_code']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $filtresLabels['classe'] = $row['libelle_classe'];
                    if (empty($filters['filiere_code']) || $filters['filiere_code'] === 'ALL') {
                        $filtresLabels['filiere'] = $row['libelle_filiere'] ?? '-';
                    }
                    if (empty($filters['niveau_code']) || $filters['niveau_code'] === 'ALL') {
                        $filtresLabels['niveau'] = $row['libelle_niveau'] ?? '-';
                    }
                }
            }

            if (!empty($filters['statut_inscription'])) {
                if ($filters['statut_inscription'] === 'ACTIFS') {
                    $filtresLabels['statut_inscription'] = 'Inscriptions Actives';
                } elseif ($filters['statut_inscription'] === 'ALL') {
                    $filtresLabels['statut_inscription'] = 'Tous les statuts';
                } elseif ($filters['statut_inscription'] === 'annule') {
                    $filtresLabels['statut_inscription'] = 'Annulées uniquement';
                } elseif ($filters['statut_inscription'] === 'valide') {
                    $filtresLabels['statut_inscription'] = 'Validées';
                } elseif ($filters['statut_inscription'] === 'solde') {
                    $filtresLabels['statut_inscription'] = 'Soldées';
                } elseif ($filters['statut_inscription'] === 'en_attente') {
                    $filtresLabels['statut_inscription'] = 'En attente';
                }
            }
        } catch (Exception $e) {
            error_log("ModelEtudiant::getFiltresLabels error: " . $e->getMessage());
        }

        return $filtresLabels;
    }

    /**
     * Annule une inscription académique
     */
    public function annulerInscription(int $idInscription, string $codeInscription): array
    {
        $db = $this->getCon();
        try {
            $inscription = null;
            if ($idInscription > 0) {
                $stmt = $db->prepare("SELECT * FROM " . TABLES::INSCRIPTIONS . " WHERE id_inscription = ? LIMIT 1");
                $stmt->execute([$idInscription]);
                $inscription = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif (!empty($codeInscription)) {
                $stmt = $db->prepare("SELECT * FROM " . TABLES::INSCRIPTIONS . " WHERE code_inscription = ? LIMIT 1");
                $stmt->execute([$codeInscription]);
                $inscription = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if (!$inscription) {
                return ['success' => false, 'message' => "Dossier d'inscription introuvable."];
            }

            if ($inscription['statut_inscription'] === STATUTS::ANNULE) {
                return ['success' => false, 'message' => "Cette inscription est déjà annulée."];
            }

            $db->beginTransaction();

            $codeIns = $inscription['code_inscription'] ?? $codeInscription;

            // 1. Annulation de l'inscription
            $stmtUp = $db->prepare("UPDATE " . TABLES::INSCRIPTIONS . " SET statut_inscription = '" . STATUTS::ANNULE . "', updated_at_inscription = NOW() WHERE id_inscription = ?");
            $res = $stmtUp->execute([$inscription['id_inscription']]);

            if (!$res) {
                $db->rollBack();
                return ['success' => false, 'message' => "Erreur lors de l'annulation de l'inscription."];
            }

            // 2. Annulation automatique des paiements associés
            $nbPaiementsAnnules = 0;
            $totalMontantAnnule = 0.0;
            if (!empty($codeIns)) {
                $stmtPaiements = $db->prepare("
                    SELECT id_paiement, code_paiement, montant_paiement, observations 
                    FROM " . TABLES::PAIEMENTS . " 
                    WHERE inscription_code = ? AND statut_paiement != '" . STATUTS::ANNULE . "'
                ");
                $stmtPaiements->execute([$codeIns]);
                $activePayments = $stmtPaiements->fetchAll(PDO::FETCH_ASSOC) ?: [];

                if (!empty($activePayments)) {
                    $motifPay = sprintf(" [ANNULATION le %s : Annulation automatique suite à l'annulation du dossier d'inscription %s]", date('d/m/Y H:i'), $codeIns);
                    $stmtCancelPay = $db->prepare("
                        UPDATE " . TABLES::PAIEMENTS . "
                        SET statut_paiement = '" . STATUTS::ANNULE . "',
                            observations = CONCAT(COALESCE(observations, ''), ?)
                        WHERE id_paiement = ?
                    ");
                    foreach ($activePayments as $p) {
                        $stmtCancelPay->execute([$motifPay, $p['id_paiement']]);
                        $nbPaiementsAnnules++;
                        $totalMontantAnnule += (float)($p['montant_paiement'] ?? 0);
                    }
                }
            }

            // 3. Annulation automatique des kits / accessoires associés
            $nbKitsAnnules = 0;
            if (!empty($codeIns)) {
                $stmtCountKits = $db->prepare("
                    SELECT COUNT(*) 
                    FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " 
                    WHERE inscription_code = ? AND statut_accessoire_inscription != 'inactif'
                ");
                $stmtCountKits->execute([$codeIns]);
                $nbKitsAnnules = (int)$stmtCountKits->fetchColumn();

                if ($nbKitsAnnules > 0) {
                    $stmtCancelKits = $db->prepare("
                        UPDATE " . TABLES::ACCESSOIRE_INSCRIPTION . "
                        SET statut_accessoire_inscription = 'inactif'
                        WHERE inscription_code = ? AND statut_accessoire_inscription != 'inactif'
                    ");
                    $stmtCancelKits->execute([$codeIns]);
                }
            }

            $db->commit();

            $msg = "L'inscription a été annulée avec succès.";
            $details = [];
            if ($nbPaiementsAnnules > 0) {
                $details[] = $nbPaiementsAnnules . " paiement(s) associé(s) (" . number_format($totalMontantAnnule, 0, ',', ' ') . " FCFA) automatiquement annulé(s)";
            }
            if ($nbKitsAnnules > 0) {
                $details[] = $nbKitsAnnules . " kit(s) scolaire(s) désactivé(s)";
            }
            if (!empty($details)) {
                $msg .= " " . implode(' et ', $details) . ".";
            }

            return [
                'success' => true,
                'message' => $msg,
                'inscription' => $inscription,
                'paiements_annules_count' => $nbPaiementsAnnules,
                'paiements_annules_montant' => $totalMontantAnnule,
                'kits_annules_count' => $nbKitsAnnules
            ];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("ModelEtudiant::annulerInscription error: " . $e->getMessage());
            return ['success' => false, 'message' => "Erreur lors de l'annulation de l'inscription: " . $e->getMessage()];
        }
    }

    /**
     * Enregistre un étudiant complet (étudiant, parents, inscription, accessoires, dossier)
     */
    public function createFullStudent(array $data, string $userCode, string $etabCode, string $anneeCode, object $validator): array
    {
        $db = $this->getCon();
        $nomEtudiant = trim($data['nom_etudiant'] ?? '');
        $prenomEtudiant = trim($data['prenom_etudiant'] ?? '');
        $telephoneEtudiant = $data['telephone_etudiant'] ?? '';

        if (empty($nomEtudiant) || empty($prenomEtudiant) || empty($telephoneEtudiant)) {
            return ['success' => false, 'message' => "Veuillez renseigner le nom, prénoms et téléphone de l'étudiant."];
        }

        $matriculeEtudiant = trim($data['matricule_etudiant'] ?? '');
        if (empty($matriculeEtudiant)) {
            $matriculeEtudiant = $this->generateMatricule($nomEtudiant, $prenomEtudiant, $data['classe_code'] ?? null, $anneeCode);
        }

        $matriculeMenet = !empty($data['matricule_menet']) ? trim($data['matricule_menet']) : null;
        $matriculeMesrs = !empty($data['matricule_mesrs']) ? trim($data['matricule_mesrs']) : null;

        $codeEtudiant = $validator->generateCode(TABLES::ETUDIANTS, 'code_etudiant', 'ETU-', 8);

        try {
            $db->beginTransaction();

            // 1. Insert Student into `etudiants`
            $fkErrEtu = ForeignKeyValidator::validate($db, TABLES::ETUDIANTS, [
                'etablissement_code' => $etabCode
            ]);
            if ($fkErrEtu !== null) {
                $db->rollBack();
                return ['success' => false, 'message' => $fkErrEtu];
            }

            $stmtEtu = $db->prepare("
                INSERT INTO " . TABLES::ETUDIANTS . " 
                (code_etudiant, matricule_etudiant, matricule_menet, matricule_mesrs, nom_etudiant, prenom_etudiant, sexe_etudiant, date_naissance_etudiant, lieu_naissance_etudiant, nationalite_etudiant, telephone_etudiant, email_etudiant, lieu_residence_etudiant, user_code, etablissement_code, statut_etudiant, created_at_etudiant)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtEtu->execute([
                $codeEtudiant,
                $matriculeEtudiant,
                $matriculeMenet,
                $matriculeMesrs,
                $nomEtudiant,
                $prenomEtudiant,
                $data['sexe_etudiant'] ?? 'M',
                !empty($data['date_naissance_etudiant']) ? $data['date_naissance_etudiant'] : (!empty($data['date_naissance']) ? $data['date_naissance'] : null),
                !empty($data['lieu_naissance_etudiant']) ? $data['lieu_naissance_etudiant'] : (!empty($data['lieu_naissance']) ? $data['lieu_naissance'] : null),
                !empty($data['nationalite_etudiant']) ? $data['nationalite_etudiant'] : (!empty($data['nationalite']) ? $data['nationalite'] : 'Ivoirienne'),
                $telephoneEtudiant,
                !empty($data['email_etudiant']) ? $data['email_etudiant'] : null,
                !empty($data['lieu_residence_etudiant']) ? $data['lieu_residence_etudiant'] : (!empty($data['adresse_etudiant']) ? $data['adresse_etudiant'] : null),
                $userCode,
                $etabCode
            ]);

            // 2. Insert Parent into `parents` if parent info provided
            if (!empty($data['nom_pere']) || !empty($data['nom_mere']) || !empty($data['nom_tuteur'])) {
                $codeParent = $validator->generateCode(TABLES::PARENTS, 'code_parent', 'PAR-', 8);
                $fkErrPar = ForeignKeyValidator::validate($db, TABLES::PARENTS, [
                    'etudiant_code' => $codeEtudiant,
                    'etablissement_code' => $etabCode
                ]);
                if ($fkErrPar !== null) {
                    $db->rollBack();
                    return ['success' => false, 'message' => $fkErrPar];
                }

                $stmtPar = $db->prepare("
                    INSERT INTO " . TABLES::PARENTS . " 
                    (code_parent, etudiant_code, nom_pere, telephone_pere, profession_pere, nom_mere, telephone_mere, profession_mere, nom_tuteur, telephone_tuteur, profession_tuteur, user_code, etablissement_code, created_at_parent)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmtPar->execute([
                    $codeParent,
                    $codeEtudiant,
                    !empty($data['nom_pere']) ? $data['nom_pere'] : null,
                    !empty($data['telephone_pere']) ? $data['telephone_pere'] : null,
                    !empty($data['profession_pere']) ? $data['profession_pere'] : null,
                    !empty($data['nom_mere']) ? $data['nom_mere'] : null,
                    !empty($data['telephone_mere']) ? $data['telephone_mere'] : null,
                    !empty($data['profession_mere']) ? $data['profession_mere'] : null,
                    !empty($data['nom_tuteur']) ? $data['nom_tuteur'] : null,
                    !empty($data['telephone_tuteur']) ? $data['telephone_tuteur'] : null,
                    !empty($data['profession_tuteur']) ? $data['profession_tuteur'] : null,
                    $userCode,
                    $etabCode
                ]);
            }

            // 3. Insert Academic Inscription into `inscriptions` if classe_code provided
            $codeInscription = null;
            if (!empty($data['classe_code'])) {
                $affectationEtatVal = (!empty($data['affectation_etat']) && in_array($data['affectation_etat'], ['affecte', 'oui'])) ? 'oui' : 'non';
                $regimeScol = ($affectationEtatVal === 'oui') ? 'affecte' : 'non_affecte';
                $montantScolarite = (float)($data['montant_scolarite_inscription'] ?? 0);
                if ($montantScolarite <= 0) {
                    $stmtCl = $db->prepare("SELECT * FROM " . TABLES::CLASSES . " WHERE code_classe = ? LIMIT 1");
                    $stmtCl->execute([$data['classe_code']]);
                    $cl = $stmtCl->fetch(PDO::FETCH_ASSOC);
                    if ($cl) {
                        $stmtSco = $db->prepare("SELECT montant_scolarite FROM " . TABLES::SCOLARITES . " WHERE filiere_code = ? AND niveau_code = ? AND annee_code = ? AND affectation_etat = ? AND statut_scolarite = '" . STATUTS::ACTIF . "' ORDER BY id_scolarite DESC LIMIT 1");
                        $stmtSco->execute([$cl['filiere_code'], $cl['niveau_code'], $anneeCode, $regimeScol]);
                        $sco = $stmtSco->fetch(PDO::FETCH_ASSOC);
                        if (!$sco) {
                            $stmtSco2 = $db->prepare("SELECT montant_scolarite FROM " . TABLES::SCOLARITES . " WHERE filiere_code = ? AND niveau_code = ? ORDER BY id_scolarite DESC LIMIT 1");
                            $stmtSco2->execute([$cl['filiere_code'], $cl['niveau_code']]);
                            $sco = $stmtSco2->fetch(PDO::FETCH_ASSOC);
                        }
                        if ($sco) {
                            $montantScolarite = (float)$sco['montant_scolarite'];
                        }
                    }
                }
                $codeInscription = $validator->generateCode(TABLES::INSCRIPTIONS, 'code_inscription', 'INS-', 8);
                
                $fkErrIns = ForeignKeyValidator::validate($db, TABLES::INSCRIPTIONS, [
                    'etudiant_code' => $codeEtudiant,
                    'classe_code' => $data['classe_code'],
                    'annee_code' => $anneeCode,
                    'etablissement_code' => $etabCode
                ]);
                if ($fkErrIns !== null) {
                    $db->rollBack();
                    return ['success' => false, 'message' => $fkErrIns];
                }

                $stmtIns = $db->prepare("
                    INSERT INTO " . TABLES::INSCRIPTIONS . "
                    (code_inscription, etudiant_code, classe_code, montant_scolarite_inscription, user_code, annee_code, etablissement_code, statut_inscription, affectation_etat, created_at_inscription)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'valide', ?, NOW())
                ");
                $stmtIns->execute([
                    $codeInscription,
                    $codeEtudiant,
                    $data['classe_code'],
                    $montantScolarite,
                    $userCode,
                    $anneeCode,
                    $etabCode,
                    $affectationEtatVal
                ]);
            }

            // 4. Insert Accessoires / Kits into `accessoire_inscription`
            if (!empty($data['accessoires']) && is_array($data['accessoires'])) {
                if (empty($codeInscription)) {
                    $db->rollBack();
                    return ['success' => false, 'message' => "Opération interrompue : Une classe et une inscription sont obligatoires pour attribuer des accessoires."];
                }
                $stmtAcc = $db->prepare("
                    INSERT INTO " . TABLES::ACCESSOIRE_INSCRIPTION . "
                    (code_accessoire_inscription, inscription_code, accessoire_code, annee_code, statut_accessoire_inscription, etat_retrait, user_code, etablissement_code, created_at_accessoire_inscription)
                    VALUES (?, ?, ?, ?, 'actif', 'en_attente', ?, ?, NOW())
                ");
                foreach ($data['accessoires'] as $accCode) {
                    if (empty($accCode)) continue;
                    
                    $fkErrAcc = ForeignKeyValidator::validate($db, TABLES::ACCESSOIRE_INSCRIPTION, [
                        'inscription_code' => $codeInscription,
                        'accessoire_code' => $accCode,
                        'annee_code' => $anneeCode,
                        'etablissement_code' => $etabCode
                    ]);
                    if ($fkErrAcc !== null) {
                        $db->rollBack();
                        return ['success' => false, 'message' => $fkErrAcc];
                    }

                    $codeAccIns = $validator->generateCode(TABLES::ACCESSOIRE_INSCRIPTION, 'code_accessoire_inscription', 'ACI-', 8);
                    $stmtAcc->execute([
                        $codeAccIns,
                        $codeInscription,
                        $accCode,
                        $anneeCode,
                        $userCode,
                        $etabCode
                    ]);
                }
            }

            // 5. Insert Documents / Checklist into `dossier_etudiant`
            $piecesFournies = !empty($data['pieces_fournies']) && is_array($data['pieces_fournies']) ? $data['pieces_fournies'] : [];
            $allPieces = $db->query("SELECT code_piece_fournir FROM " . TABLES::PIECES_FOURNIR . " WHERE statut_piece = '" . STATUTS::ACTIF . "'")->fetchAll(PDO::FETCH_COLUMN) ?: [];

            if (!empty($allPieces)) {
                if (empty($codeInscription)) {
                    $db->rollBack();
                    return ['success' => false, 'message' => "Opération interrompue : Une inscription valide est nécessaire pour initialiser le dossier étudiant."];
                }
                $stmtDos = $db->prepare("
                    INSERT INTO " . TABLES::DOSSIER_ETUDIANT . "
                    (code_dossier_etudiant, inscription_code, etudiant_code, piece_code, statut_depot, date_depot, observations, user_code, etablissement_code, created_at_dossier_etudiant)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                $piecesReqMap = [];
                try {
                    $pfcList = $db->query("SELECT piece_code, nombre_exemplaires FROM " . TABLES::PIECES_FOURNIR_CYCLES . " WHERE statut_piece_cycle = '" . STATUTS::ACTIF . "'")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
                    $piecesReqMap = $pfcList;
                } catch (Exception $e) {}

                foreach ($allPieces as $pCode) {
                    $isDepose = in_array($pCode, $piecesFournies);
                    $nbEx = !empty($piecesReqMap[$pCode]) ? max(1, (int)$piecesReqMap[$pCode]) : 1;
                    $obsText = $isDepose 
                        ? ("Pièce déposée ({$nbEx} exemplaire" . ($nbEx > 1 ? "s" : "") . ") lors de l'inscription initiale")
                        : ("En attente de transmission ({$nbEx} exemplaire" . ($nbEx > 1 ? "s requis" : " requis") . ")");

                    $fkErrDos = ForeignKeyValidator::validate($db, TABLES::DOSSIER_ETUDIANT, [
                        'inscription_code' => $codeInscription,
                        'etudiant_code' => $codeEtudiant,
                        'piece_code' => $pCode,
                        'etablissement_code' => $etabCode
                    ]);
                    if ($fkErrDos !== null) {
                        $db->rollBack();
                        return ['success' => false, 'message' => $fkErrDos];
                    }

                    $codeDos = $validator->generateCode(TABLES::DOSSIER_ETUDIANT, 'code_dossier_etudiant', 'DOS-', 8);
                    $stmtDos->execute([
                        $codeDos,
                        $codeInscription,
                        $codeEtudiant,
                        $pCode,
                        $isDepose ? 'depose' : 'en_attente',
                        $isDepose ? date('Y-m-d H:i:s') : null,
                        $obsText,
                        $userCode,
                        $etabCode
                    ]);
                }
            }

            $db->commit();
            return ['success' => true, 'message' => "Dossier d'inscription étudiant créé avec succès !"];

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("ModelEtudiant::createFullStudent error: " . $e->getMessage());
            return ['success' => false, 'message' => "Erreur lors de la création du dossier: " . $e->getMessage()];
        }
    }
}
