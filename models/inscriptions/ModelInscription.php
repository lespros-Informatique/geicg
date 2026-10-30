<?php

class ModelInscription extends BaseModel
{
    protected string $table = TABLES::INSCRIPTIONS;
    protected string $primaryKey = 'id_inscription';
    protected ?string $statusField = 'statut_inscription';
    protected ?string $createdAtField = 'created_at_inscription';

    /**
     * Récupère la liste des étudiants candidats à la réinscription (non encore inscrits sur l'année active)
     */
    public function getReinscriptionCandidates(string $activeAnneeCode, string $filterAnnee, string $filterFiliere = '', string $filterNiveau = '', string $filterClasse = ''): array
    {
        $db = $this->getCon();
        try {
            // 1. Tous les étudiants
            $students = $db->query("
                SELECT id_etudiant, code_etudiant, matricule_etudiant, nom_etudiant, prenom_etudiant, sexe_etudiant, telephone_etudiant, email_etudiant, photo_etudiant, statut_etudiant
                FROM " . TABLES::ETUDIANTS . "
                ORDER BY id_etudiant DESC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // 2. Déjà inscrits sur l'année active
            $stmtCur = $db->prepare("
                SELECT DISTINCT etudiant_code
                FROM " . TABLES::INSCRIPTIONS . "
                WHERE annee_code = ? AND statut_inscription != '" . STATUTS::ANNULE . "'
            ");
            $stmtCur->execute([$activeAnneeCode]);
            $curCodes = $stmtCur->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $curSet = [];
            foreach ($curCodes as $c) {
                $curSet[trim($c)] = true;
            }

            // 3. Dernière inscription passée selon le filtre d'année
            $priorMap = [];
            if (!empty($filterAnnee) && $filterAnnee !== 'ALL') {
                $stmtPrior = $db->prepare("
                    SELECT i.*, c.libelle_classe as classe_prev, c.filiere_code as filiere_prev_code, c.niveau_code as niveau_prev_code,
                           f.libelle_filiere as filiere_prev, n.libelle_niveau as niveau_prev, a.libelle_annee as annee_prev
                    FROM " . TABLES::INSCRIPTIONS . " i
                    LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
                    LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
                    LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
                    LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
                    WHERE i.annee_code = ? AND i.statut_inscription != '" . STATUTS::ANNULE . "'
                    ORDER BY i.id_inscription DESC
                ");
                $stmtPrior->execute([$filterAnnee]);
            } else {
                $stmtPrior = $db->prepare("
                    SELECT i.*, c.libelle_classe as classe_prev, c.filiere_code as filiere_prev_code, c.niveau_code as niveau_prev_code,
                           f.libelle_filiere as filiere_prev, n.libelle_niveau as niveau_prev, a.libelle_annee as annee_prev
                    FROM " . TABLES::INSCRIPTIONS . " i
                    LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
                    LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
                    LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
                    LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
                    WHERE i.annee_code != ? AND i.statut_inscription != '" . STATUTS::ANNULE . "'
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

            $result = [];
            foreach ($students as $etu) {
                $code = $etu['code_etudiant'];
                $mat = $etu['matricule_etudiant'] ?? '';

                if (isset($curSet[$code]) || (!empty($mat) && isset($curSet[$mat]))) {
                    continue;
                }

                $prev = $priorMap[$code] ?? ($priorMap[$mat] ?? null);
                if (!$prev) {
                    continue;
                }

                $refFiliere = $prev['filiere_prev_code'] ?? '';
                $refNiveau = $prev['niveau_prev_code'] ?? '';
                $refClasse = $prev['classe_code'] ?? '';

                if (!empty($filterFiliere) && $filterFiliere !== 'ALL' && $refFiliere !== $filterFiliere) {
                    continue;
                }
                if (!empty($filterNiveau) && $filterNiveau !== 'ALL' && $refNiveau !== $filterNiveau) {
                    continue;
                }
                if (!empty($filterClasse) && $filterClasse !== 'ALL' && $refClasse !== $filterClasse) {
                    continue;
                }

                $result[] = [
                    'etudiant' => $etu,
                    'prev' => $prev
                ];
            }

            return $result;
        } catch (Exception $e) {
            error_log("ModelInscription::getReinscriptionCandidates error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les données complètes de réinscription pour un étudiant
     */
    public function getStudentReinscriptionInfo(string $etudiantCode, string $anneeActive): ?array
    {
        $db = $this->getCon();
        try {
            // 1. Étudiant & Parent
            $stmt = $db->prepare("
                SELECT e.*, 
                       p.nom_pere, p.telephone_pere, p.profession_pere,
                       p.nom_mere, p.telephone_mere, p.profession_mere,
                       p.nom_tuteur, p.telephone_tuteur
                FROM " . TABLES::ETUDIANTS . " e
                LEFT JOIN " . TABLES::PARENTS . " p ON (p.etudiant_code = e.code_etudiant OR p.etudiant_code = e.matricule_etudiant)
                WHERE e.code_etudiant = ? OR e.matricule_etudiant = ? OR e.id_etudiant = ?
                LIMIT 1
            ");
            $stmt->execute([$etudiantCode, $etudiantCode, is_numeric($etudiantCode) ? (int)$etudiantCode : 0]);
            $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$etudiant) {
                return null;
            }

            // Inscription active courante si elle existe
            $stmtThisYear = $db->prepare("
                SELECT i.*, c.libelle_classe, a.libelle_annee 
                FROM " . TABLES::INSCRIPTIONS . " i
                LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
                WHERE (i.etudiant_code = ? OR i.etudiant_code = ?)
                  AND i.annee_code = ?
                  AND i.statut_inscription != '" . STATUTS::ANNULE . "'
                LIMIT 1
            ");
            $stmtThisYear->execute([$etudiant['code_etudiant'], $etudiant['matricule_etudiant'], $anneeActive]);
            $alreadyThisYear = $stmtThisYear->fetch(PDO::FETCH_ASSOC) ?: null;

            // Inscription précédente
            $stmtPrev = $db->prepare("
                SELECT i.*, 
                       c.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       a.libelle_annee
                FROM " . TABLES::INSCRIPTIONS . " i
                LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
                LEFT JOIN " . TABLES::ANNEES . " a ON (a.code_annee = i.annee_code OR a.id_annee = i.annee_code)
                WHERE i.etudiant_code = ?
                ORDER BY i.id_inscription DESC
                LIMIT 1
            ");
            $stmtPrev->execute([$etudiant['code_etudiant']]);
            $lastInscription = $stmtPrev->fetch(PDO::FETCH_ASSOC) ?: null;

            return [
                'etudiant' => $etudiant,
                'already_this_year' => $alreadyThisYear,
                'last_inscription' => $lastInscription
            ];
        } catch (Exception $e) {
            error_log("ModelInscription::getStudentReinscriptionInfo error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les détails d'une inscription avec l'étudiant, la classe et ses paiements
     */
    public function getInscriptionFullDetails($idOrCode): ?array
    {
        $db = $this->getCon();
        try {
            $stmt = $db->prepare("
                SELECT * FROM " . VIEWS::INSCRIPTIONS_COMPLETES . "
                WHERE id_inscription = ? OR code_inscription = ?
                LIMIT 1
            ");
            $numericId = is_numeric($idOrCode) ? (int)$idOrCode : 0;
            $stmt->execute([$numericId, (string)$idOrCode]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                return null;
            }

            $stmtP = $db->prepare("
                SELECT * FROM " . TABLES::PAIEMENTS . " 
                WHERE inscription_code = ?
                ORDER BY date_paiement DESC
            ");
            $stmtP->execute([$item['code_inscription']]);
            $paiements = $stmtP->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $scolarite = (float)($item['montant_scolarite_inscription'] ?? 0);
            $totalPaye = 0;
            foreach ($paiements as $p) {
                if (($p['statut_paiement'] ?? '') !== STATUTS::ANNULE) {
                    $totalPaye += (float)($p['montant_paiement'] ?? 0);
                }
            }
            $soldeRestant = max(0, $scolarite - $totalPaye);

            return [
                'item' => $item,
                'paiements' => $paiements,
                'totalPaye' => $totalPaye,
                'soldeRestant' => $soldeRestant,
                'scolarite' => $scolarite
            ];
        } catch (Exception $e) {
            error_log("ModelInscription::getInscriptionFullDetails error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère la liste des inscriptions filtrée pour la gestion des photos
     */
    public function getInscriptionsSansPhoto(string $anneeCode = '', string $filiereCode = '', string $classeCode = '', string $filterPhoto = 'sans_photo'): array
    {
        $db = $this->getCon();
        try {
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
                FROM " . TABLES::INSCRIPTIONS . " i
                JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
                JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelInscription::getInscriptionsSansPhoto error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les métadonnées d'une inscription requises pour le téléversement de photo
     */
    public function getInscriptionForPhotoUpload($idOrCode): ?array
    {
        $db = $this->getCon();
        try {
            $stmtInfo = $db->prepare("
                SELECT 
                    i.id_inscription,
                    i.code_inscription,
                    i.etudiant_code,
                    i.annee_code,
                    i.photo_inscription,
                    e.matricule_etudiant,
                    e.code_etudiant,
                    e.photo_etudiant,
                    a.libelle_annee
                FROM " . TABLES::INSCRIPTIONS . " i
                LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
                WHERE " . (is_numeric($idOrCode) && (int)$idOrCode > 0 ? "i.id_inscription = ?" : "i.code_inscription = ?") . "
                LIMIT 1
            ");
            $stmtInfo->execute([$idOrCode]);
            return $stmtInfo->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelInscription::getInscriptionForPhotoUpload error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Met à jour la photo d'inscription et synchronise avec la fiche étudiant
     */
    public function updatePhoto(int $idInscription, string $relativePath, string $codeEtu = '', string $matEtu = ''): bool
    {
        $db = $this->getCon();
        try {
            $stmt = $db->prepare("UPDATE " . TABLES::INSCRIPTIONS . " SET photo_inscription = ?, updated_at_inscription = NOW() WHERE id_inscription = ?");
            $ok = $stmt->execute([$relativePath, $idInscription]);

            if (!empty($codeEtu) || !empty($matEtu)) {
                $db->prepare("UPDATE " . TABLES::ETUDIANTS . " SET photo_etudiant = ?, updated_at_etudiant = NOW() WHERE code_etudiant = ? OR (matricule_etudiant = ? AND matricule_etudiant != '') OR code_etudiant = ?")
                   ->execute([$relativePath, $codeEtu, $matEtu, $codeEtu]);
            }
            return (bool)$ok;
        } catch (Exception $e) {
            error_log("ModelInscription::updatePhoto error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les dépendances pour le formulaire de réinscription
     */
    public function getReinscriptionFormDependencies(string $activeAnneeCode): array
    {
        $db = $this->getCon();
        $stmtCloture = $db->query("SELECT id_annee, code_annee, libelle_annee, statut_annee FROM " . TABLES::ANNEES . " WHERE statut_annee = 'cloture' ORDER BY id_annee DESC");
        $anneesCloturees = $stmtCloture ? $stmtCloture->fetchAll(PDO::FETCH_ASSOC) : [];
        if (empty($anneesCloturees)) {
            $stmtFallback = $db->prepare("SELECT id_annee, code_annee, libelle_annee, statut_annee FROM " . TABLES::ANNEES . " WHERE code_annee != ? ORDER BY id_annee DESC");
            $stmtFallback->execute([$activeAnneeCode]);
            $anneesCloturees = $stmtFallback->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        $filieres = $db->query("SELECT code_filiere, libelle_filiere FROM " . TABLES::FILIERES . " WHERE statut_filiere = '" . STATUTS::ACTIF . "' ORDER BY libelle_filiere ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $niveaux = $db->query("SELECT code_niveau, libelle_niveau FROM " . TABLES::NIVEAUX . " WHERE statut_niveau = '" . STATUTS::ACTIF . "' ORDER BY id_niveau ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $classes = $db->query("SELECT code_classe, libelle_classe, cycle_code, filiere_code, niveau_code, annee_code FROM " . TABLES::CLASSES . " WHERE statut_classe = '" . STATUTS::ACTIF . "' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'anneesCloturees' => $anneesCloturees,
            'filieres' => $filieres,
            'niveaux' => $niveaux,
            'classes' => $classes
        ];
    }

    /**
     * Récupère les dépendances pour la prise de vue
     */
    public function getPriseDeVueDependencies(): array
    {
        $db = $this->getCon();
        $filieres = $db->query("SELECT code_filiere, libelle_filiere FROM " . TABLES::FILIERES . " WHERE statut_filiere = '" . STATUTS::ACTIF . "' ORDER BY libelle_filiere ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $classes = $db->query("SELECT code_classe, libelle_classe, filiere_code FROM " . TABLES::CLASSES . " WHERE statut_classe = '" . STATUTS::ACTIF . "' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return [
            'filieres' => $filieres,
            'classes' => $classes
        ];
    }

    /**
     * Récupère le profil complet d'un étudiant pour la réinscription
     */
    public function getStudentProfileForReinscription(string $etudiantCode, string $anneeActive): ?array
    {
        $db = $this->getCon();
        $stmt = $db->prepare("
            SELECT e.*, 
                   p.nom_pere, p.telephone_pere, p.profession_pere,
                   p.nom_mere, p.telephone_mere, p.profession_mere,
                   p.nom_tuteur, p.telephone_tuteur
            FROM " . TABLES::ETUDIANTS . " e
            LEFT JOIN " . TABLES::PARENTS . " p ON (p.etudiant_code = e.code_etudiant OR p.etudiant_code = e.matricule_etudiant)
            WHERE e.code_etudiant = ? OR e.matricule_etudiant = ? OR e.id_etudiant = ?
            LIMIT 1
        ");
        $stmt->execute([$etudiantCode, $etudiantCode, is_numeric($etudiantCode) ? (int)$etudiantCode : 0]);
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$etudiant) {
            return null;
        }

        $stmtThisYear = $db->prepare("
            SELECT i.*, c.libelle_classe, a.libelle_annee 
            FROM " . TABLES::INSCRIPTIONS . " i
            LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
            LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
            WHERE (i.etudiant_code = ? OR i.etudiant_code = ?)
              AND i.annee_code = ?
              AND i.statut_inscription != '" . STATUTS::ANNULE . "'
            LIMIT 1
        ");
        $stmtThisYear->execute([$etudiant['code_etudiant'], $etudiant['matricule_etudiant'], $anneeActive]);
        $alreadyThisYear = $stmtThisYear->fetch(PDO::FETCH_ASSOC);

        $stmtPrev = $db->prepare("
            SELECT i.*, 
                   c.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                   a.libelle_annee
            FROM " . TABLES::INSCRIPTIONS . " i
            LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
            LEFT JOIN " . TABLES::ANNEES . " a ON (a.code_annee = i.annee_code OR a.id_annee = i.annee_code)
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
            
            $stmtP = $db->prepare("
                SELECT SUM(montant_paiement) as total_paye 
                FROM " . TABLES::PAIEMENTS . " 
                WHERE inscription_code = ? AND statut_paiement != '" . STATUTS::ANNULE . "'
            ");
            $stmtP->execute([$prevIns['code_inscription']]);
            $rowP = $stmtP->fetch(PDO::FETCH_ASSOC);
            $prevPaye = (float)($rowP['total_paye'] ?? 0);
            $prevSolde = max(0, $prevDue - $prevPaye);

            if (!empty($prevIns['filiere_code']) && !empty($prevIns['niveau_code'])) {
                $stmtNiv = $db->prepare("SELECT id_niveau FROM " . TABLES::NIVEAUX . " WHERE code_niveau = ? LIMIT 1");
                $stmtNiv->execute([$prevIns['niveau_code']]);
                $currNiv = $stmtNiv->fetch(PDO::FETCH_ASSOC);
                if ($currNiv) {
                    $nextNivId = (int)$currNiv['id_niveau'] + 1;
                    $stmtNextCl = $db->prepare("
                        SELECT c.code_classe, c.libelle_classe 
                        FROM " . TABLES::CLASSES . " c
                        JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
                        WHERE c.filiere_code = ? 
                          AND n.id_niveau = ?
                          AND (c.annee_code = ? OR ? = '')
                          AND c.statut_classe = '" . STATUTS::ACTIF . "'
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

        $parentNom = $etudiant['nom_tuteur'] ?: ($etudiant['nom_pere'] ?: ($etudiant['nom_mere'] ?: 'Non renseigné'));
        $parentTel = $etudiant['telephone_tuteur'] ?: ($etudiant['telephone_pere'] ?: ($etudiant['telephone_mere'] ?: 'Non renseigné'));
        $parentRole = $etudiant['nom_tuteur'] ? 'Tuteur' : ($etudiant['nom_pere'] ? 'Père' : ($etudiant['nom_mere'] ? 'Mère' : 'Parent'));
        $parentProf = $etudiant['profession_pere'] ?: ($etudiant['profession_mere'] ?: 'Non renseignée');

        $stmtPai = $db->prepare("
            SELECT p.code_paiement, p.montant_paiement, p.mode_paiement, p.date_paiement, p.reference_paiement, p.statut_paiement, a.libelle_annee
            FROM " . TABLES::PAIEMENTS . " p
            LEFT JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = p.inscription_code
            LEFT JOIN " . TABLES::ANNEES . " a ON (a.code_annee = i.annee_code OR a.id_annee = i.annee_code)
            WHERE (i.etudiant_code = ? OR i.etudiant_code = ?)
              AND p.statut_paiement != '" . STATUTS::ANNULE . "'
            ORDER BY p.date_paiement DESC, p.id_paiement DESC
        ");
        $stmtPai->execute([$etudiant['code_etudiant'], $etudiant['matricule_etudiant'] ?? '']);
        $historyPayments = $stmtPai->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmtAcc = $db->prepare("
            SELECT a.code_accessoire, a.libelle_accessoire, COALESCE(ai.statut_accessoire_inscription, 'actif') as statut
            FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " ai
            JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = ai.inscription_code
            JOIN " . TABLES::ACCESSOIRES . " a ON a.code_accessoire = ai.accessoire_code
            WHERE i.etudiant_code = ?
            ORDER BY (CASE WHEN ai.annee_code = ? THEN 1 ELSE 2 END), ai.id_accessoire_inscription DESC
        ");
        $stmtAcc->execute([$etudiantCode, $anneeActive]);
        $accessoiresEtudiant = $stmtAcc->fetchAll(PDO::FETCH_ASSOC);
        if (empty($accessoiresEtudiant)) {
            $stmtAllAcc = $db->query("SELECT code_accessoire, libelle_accessoire, 'disponible' as statut FROM " . TABLES::ACCESSOIRES . " WHERE statut_accessoire = '" . STATUTS::ACTIF . "'");
            $accessoiresEtudiant = $stmtAllAcc ? $stmtAllAcc->fetchAll(PDO::FETCH_ASSOC) : [];
        }

        return [
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
            'history_payments' => $historyPayments,
            'accessoires_etudiant' => $accessoiresEtudiant
        ];
    }

    /**
     * Récupère le tarif et l'échéancier d'une classe
     */
    public function getClassTarifs(string $classeCode, string $affectationEtat, ?string $anneeCodeReq = null): ?array
    {
        $db = $this->getCon();
        $stmtCl = $db->prepare("
            SELECT c.*, f.libelle_filiere, f.type_filiere, n.libelle_niveau, a.libelle_annee 
            FROM " . TABLES::CLASSES . " c
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = c.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = c.niveau_code
            LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = c.annee_code
            WHERE c.code_classe = ? 
            LIMIT 1
        ");
        $stmtCl->execute([$classeCode]);
        $classe = $stmtCl->fetch(PDO::FETCH_ASSOC);
        if (!$classe) return null;

        $filiereCode = $classe['filiere_code'] ?? '';
        $typeFiliere = $classe['type_filiere'] ?? 'INDUSTRIELLE';
        $niveauCode = $classe['niveau_code'] ?? '';
        $classAnneeCode = $classe['annee_code'] ?? '';
        $activeAnneeCode = !empty($anneeCodeReq) ? $anneeCodeReq : $classAnneeCode;

        $stmtSco = $db->prepare("
            SELECT * FROM " . TABLES::SCOLARITES . " 
            WHERE filiere_code = ? 
              AND (annee_code = ? OR ? = '')
              AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
              AND affectation_etat = ?
              AND statut_scolarite = '" . STATUTS::ACTIF . "'
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
        if (!$sco) return null;

        $montantScolarite = (float)$sco['montant_scolarite'];
        $affectationEtatFinal = $sco['affectation_etat'];
        $codeScolarite = $sco['code_scolarite'];

        $tranches = [];
        if (!empty($codeScolarite)) {
            $stmtTr = $db->prepare("
                SELECT * FROM " . TABLES::TRANCHES_SCOLARITE . " 
                WHERE scolarite_code = ? 
                  AND statut_tranche = '" . STATUTS::ACTIF . "'
                ORDER BY id_tranche ASC
            ");
            $stmtTr->execute([$codeScolarite]);
            $tranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC);
        }

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

        require_once __DIR__ . '/../frais_annexes/ModelFraisAnnexe.php';
        $modelFraisAnnexe = new ModelFraisAnnexe();
        $totalFraisAnnexes = $modelFraisAnnexe->getMontantByTypeFiliere($typeFiliere, $activeAnneeCode, $niveauCode, 'inscription');
        $fraisAnnexeDetails = $modelFraisAnnexe->getFraisAnnexeDetails($typeFiliere, $activeAnneeCode, $niveauCode, 'inscription');

        return [
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
        ];
    }

    /**
     * Crée une inscription complète avec nouvel étudiant et parent
     */
    public function processFullInscription(array $data, string $userCode, string $etabCode, string $anneeCode, object $validator): array
    {
        $db = $this->getCon();
        $nomEtudiant = trim($data['nom_etudiant'] ?? '');
        $prenomEtudiant = trim($data['prenom_etudiant'] ?? '');
        $telephoneEtudiant = trim($data['telephone_etudiant'] ?? '');

        if (empty($nomEtudiant) || empty($prenomEtudiant)) {
            return ['success' => false, 'message' => "Veuillez renseigner le nom et les prénoms de l'étudiant."];
        }

        if (empty($data['classe_code'])) {
            return ['success' => false, 'message' => "Veuillez choisir la classe d'affectation."];
        }

        $matriculeEtudiant = trim($data['matricule_etudiant'] ?? '');
        if (empty($matriculeEtudiant)) {
            $matriculeEtudiant = $validator->generateCode(TABLES::ETUDIANTS, 'matricule_etudiant', 'ETU-' . date('Y') . '-', 4);
        }

        $codeEtudiant = $validator->generateCode(TABLES::ETUDIANTS, 'code_etudiant', 'ETU-', 8);

        try {
            $db->beginTransaction();

            // A. Création Étudiant
            $fkErrEtu = ForeignKeyValidator::validate($db, TABLES::ETUDIANTS, [
                'etablissement_code' => $etabCode
            ]);
            if ($fkErrEtu !== null) {
                $db->rollBack();
                return ['success' => false, 'message' => $fkErrEtu];
            }

            $stmtEtu = $db->prepare("
                INSERT INTO " . TABLES::ETUDIANTS . " 
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

            // B. Création Parent
            if (!empty($data['nom_tuteur']) || !empty($data['nom_pere']) || !empty($data['nom_mere']) || !empty($data['telephone_tuteur']) || !empty($data['telephone_pere'])) {
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
            return ['success' => true, 'message' => "Dossier d'inscription créé avec succès pour l'étudiant $nomEtudiant $prenomEtudiant !"];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("ModelInscription::processFullInscription error: " . $e->getMessage());
            return ['success' => false, 'message' => "Erreur lors de l'enregistrement du dossier complet : " . $e->getMessage()];
        }
    }

    /**
     * Réalise la réinscription d'un étudiant existant
     */
    public function processReinscription(array $data, string $userCode, string $etabCode, string $anneeCode, object $validator, bool $hasDerogationPermission, ?string $agentName = null): array
    {
        $db = $this->getCon();
        if (empty($data['etudiant_code'])) {
            return ['success' => false, 'message' => "Veuillez sélectionner un étudiant."];
        }
        if (empty($data['classe_code'])) {
            return ['success' => false, 'message' => "Veuillez choisir la classe d'affectation."];
        }

        // Anti-doublon
        $stmtCheck = $db->prepare("
            SELECT i.id_inscription, i.code_inscription, c.libelle_classe, a.libelle_annee 
            FROM " . TABLES::INSCRIPTIONS . " i
            LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
            LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
            WHERE (i.etudiant_code = ? OR i.etudiant_code = (SELECT matricule_etudiant FROM " . TABLES::ETUDIANTS . " WHERE code_etudiant = ? LIMIT 1))
              AND i.annee_code = ?
              AND i.statut_inscription != '" . STATUTS::ANNULE . "'
            LIMIT 1
        ");
        $stmtCheck->execute([$data['etudiant_code'], $data['etudiant_code'], $anneeCode]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return ['success' => false, 'message' => "Cet étudiant est déjà inscrit/réinscrit pour l'année académique " . ($existing['libelle_annee'] ?? 'en cours') . " dans la classe " . ($existing['libelle_classe'] ?? '') . " [Réf: " . $existing['code_inscription'] . "]. Une double réinscription pour la même session est impossible."];
        }

        // Quitus N-1
        $stmtPrev = $db->prepare("
            SELECT i.code_inscription, i.montant_scolarite_inscription, a.libelle_annee
            FROM " . TABLES::INSCRIPTIONS . " i
            LEFT JOIN " . TABLES::ANNEES . " a ON (a.code_annee = i.annee_code OR a.id_annee = i.annee_code)
            WHERE (i.etudiant_code = ? OR i.etudiant_code = (SELECT matricule_etudiant FROM " . TABLES::ETUDIANTS . " WHERE code_etudiant = ? LIMIT 1))
              AND i.annee_code != ?
              AND i.statut_inscription != '" . STATUTS::ANNULE . "'
            ORDER BY i.id_inscription DESC
            LIMIT 1
        ");
        $stmtPrev->execute([$data['etudiant_code'], $data['etudiant_code'], $anneeCode]);
        $prevIns = $stmtPrev->fetch(PDO::FETCH_ASSOC);
        $prevSolde = 0;
        if ($prevIns) {
            $prevDue = (float)($prevIns['montant_scolarite_inscription'] ?? 0);
            $stmtP = $db->prepare("SELECT SUM(montant_paiement) as total_paye FROM " . TABLES::PAIEMENTS . " WHERE inscription_code = ? AND statut_paiement != '" . STATUTS::ANNULE . "'");
            $stmtP->execute([$prevIns['code_inscription']]);
            $prevPaye = (float)($stmtP->fetch(PDO::FETCH_ASSOC)['total_paye'] ?? 0);
            $prevSolde = max(0, $prevDue - $prevPaye);
        }

        $derogationAcceptee = !empty($data['derogation_arriere']) && in_array($data['derogation_arriere'], ['1', 'oui', 'true']);
        if ($derogationAcceptee && !$hasDerogationPermission) {
            return ['success' => false, 'message' => "Accès refusé : Vous ne possédez pas le privilège [MANAGE_DEROGATION_INSCRIPTION] requis pour accorder une dérogation administrative."];
        }
        if ($prevSolde > 0 && !$derogationAcceptee) {
            return ['success' => false, 'message' => "L'étudiant présente un reliquat impayé de " . number_format($prevSolde, 0, ',', ' ') . " FCFA sur la session précédente (" . ($prevIns['libelle_annee'] ?? 'antérieure') . "). La réinscription requiert un quitus financier au Bureau des Versements ou une dérogation administrative formelle."];
        }

        // Mise à jour coordonnées si modifiées
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
            $sqlUpEtu = "UPDATE " . TABLES::ETUDIANTS . " SET " . implode(', ', $updateEtu) . " WHERE code_etudiant = ? OR matricule_etudiant = ?";
            $db->prepare($sqlUpEtu)->execute($paramsEtu);
        }

        $affectationEtat = (!empty($data['affectation_etat']) && in_array($data['affectation_etat'], ['affecte', 'oui'])) ? 'affecte' : 'non_affecte';
        $data['affectation_etat'] = ($affectationEtat === 'affecte') ? 'oui' : 'non';

        $stmtCl = $db->prepare("SELECT libelle_classe, filiere_code, niveau_code, annee_code FROM " . TABLES::CLASSES . " WHERE code_classe = ? LIMIT 1");
        $stmtCl->execute([$data['classe_code']]);
        $cl = $stmtCl->fetch(PDO::FETCH_ASSOC);

        $officialScolarite = 0;
        $codeScolarite = '';
        if ($cl) {
            $stmtSco = $db->prepare("
                SELECT code_scolarite, montant_scolarite FROM " . TABLES::SCOLARITES . " 
                WHERE filiere_code = ? 
                  AND (annee_code = ? OR ? = '')
                  AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
                  AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
                  AND statut_scolarite = '" . STATUTS::ACTIF . "'
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
                return ['success' => false, 'message' => "Réinscription impossible : Aucun tarif de scolarité actif n'est configuré pour la classe [$classeLib] sous le régime $regimeTxt pour cette session académique. Veuillez d'abord paramétrer la scolarité dans le module Finance."];
            }
        }

        $tranche1Amount = $officialScolarite;
        $tranche1Libelle = 'Scolarité / Droit de réinscription';
        if (!empty($codeScolarite)) {
            $stmtTr = $db->prepare("
                SELECT libelle_tranche, montant_tranche, date_limite 
                FROM " . TABLES::TRANCHES_SCOLARITE . " 
                WHERE scolarite_code = ? 
                  AND statut_tranche = '" . STATUTS::ACTIF . "'
                ORDER BY id_tranche ASC
            ");
            $stmtTr->execute([$codeScolarite]);
            $tranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (!empty($tranches)) {
                $tranche1Amount = (float)$tranches[0]['montant_tranche'];
                $tranche1Libelle = $tranches[0]['libelle_tranche'];
            }
        }

        $data['montant_scolarite_inscription'] = $officialScolarite;
        $data['date_inscription'] = date('Y-m-d');

        if (empty($data['code_inscription'])) {
            $data['code_inscription'] = $validator->generateCode(TABLES::INSCRIPTIONS, 'code_inscription', 'INS-', 8);
        }
        $codeInscription = $data['code_inscription'];
        $data['statut_inscription'] = $data['statut_inscription'] ?? 'valide';
        $data['created_at_inscription'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;
        
        $filteredData = $this->filterData($data);
        if ($this->create($filteredData)) {
            $stmtE = $db->prepare("SELECT nom_etudiant, prenom_etudiant, matricule_etudiant, telephone_etudiant FROM " . TABLES::ETUDIANTS . " WHERE code_etudiant = ? OR matricule_etudiant = ? LIMIT 1");
            $stmtE->execute([$data['etudiant_code'], $data['etudiant_code']]);
            $etuRow = $stmtE->fetch(PDO::FETCH_ASSOC);

            $stmtA = $db->prepare("SELECT libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
            $stmtA->execute([$anneeCode]);
            $anneeLib = $stmtA->fetch(PDO::FETCH_ASSOC)['libelle_annee'] ?? '';

            return [
                'success' => true,
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
                    'agent_inscription' => $agentName ?: 'Bureau Inscription'
                ]
            ];
        } else {
            return ['success' => false, 'message' => "Erreur lors de l'enregistrement de la réinscription"];
        }
    }

    /**
     * Récupère le tarif de scolarité pour une classe et un statut d'affectation
     */
    public function getScolariteAmountForClasse(string $classeCode, string $affectationEtat): ?float
    {
        $db = $this->getCon();
        $stmtCl = $db->prepare("SELECT filiere_code, niveau_code, annee_code FROM " . TABLES::CLASSES . " WHERE code_classe = ? LIMIT 1");
        $stmtCl->execute([$classeCode]);
        $cl = $stmtCl->fetch(PDO::FETCH_ASSOC);

        if (!$cl) return null;

        $stmtSco = $db->prepare("
            SELECT montant_scolarite FROM " . TABLES::SCOLARITES . " 
            WHERE filiere_code = ? 
              AND (annee_code = ? OR ? = '')
              AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL)
              AND (affectation_etat = ? OR affectation_etat = '' OR affectation_etat IS NULL)
              AND statut_scolarite = '" . STATUTS::ACTIF . "'
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
        return $scol ? (float)$scol['montant_scolarite'] : null;
    }

    /**
     * Prépare toutes les données du reçu d'inscription pour PDF/HTML
     */
    public function getRecuInscriptionData(string $param): ?array
    {
        $db = $this->getCon();
        $id = $param;

        $stmtIns = $db->prepare("
            SELECT * FROM " . VIEWS::INSCRIPTIONS_COMPLETES . "
            WHERE id_inscription = ? OR code_inscription = ? OR code_etudiant = ? OR matricule_etudiant = ? OR id_etudiant = ?
            LIMIT 1
        ");
        $stmtIns->execute([$id, $id, $id, $id, $id]);
        $ins = $stmtIns->fetch(PDO::FETCH_ASSOC);

        if (!$ins) {
            return null;
        }

        $stmtAllPai = $db->prepare("
            SELECT p.*, u.nom_user, u.prenom_user
            FROM " . TABLES::PAIEMENTS . " p
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = p.user_code
            WHERE p.inscription_code = ? AND p.statut_paiement != '" . STATUTS::ANNULE . "'
            ORDER BY p.id_paiement ASC
        ");
        $stmtAllPai->execute([$ins['code_inscription']]);
        $allPaiements = $stmtAllPai->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($allPaiements)) {
            return ['error' => "Impossible de générer le reçu d'inscription : Aucun règlement n'a été enregistré pour cet étudiant."];
        }

        $lastPaiement = end($allPaiements);
        $lastDate = $lastPaiement['date_paiement'] ?? '';
        $opPayments = array_filter($allPaiements, function($p) use ($lastDate) {
            if (empty($lastDate)) return true;
            return ($p['date_paiement'] === $lastDate) || (abs(strtotime($p['date_paiement'] ?? '') - strtotime($lastDate)) <= 10);
        });

        $faOpJour = 0;
        $scolOpJour = 0;
        foreach ($opPayments as $pOp) {
            $catOp = strtoupper(trim($pOp['categorie_paiement'] ?? ''));
            $trancheOp = strtoupper(trim($pOp['tranche_code'] ?? ''));
            if ($catOp === 'FRAIS_ANNEXES' || $trancheOp === 'FRAIS_ANNEXES') {
                $faOpJour += (float)$pOp['montant_paiement'];
            } else {
                $scolOpJour += (float)$pOp['montant_paiement'];
            }
        }

        $droitOpJour = $faOpJour + $scolOpJour;
        $scolariteOpJour = $scolOpJour;
        $fraisAnnexesOpJour = $faOpJour;

        $opDuJourMontant = $faOpJour + $scolOpJour;
        if ($opDuJourMontant <= 0) {
            $opDuJourMontant = (float)($lastPaiement['montant_paiement'] ?? 0);
            $droitOpJour = $opDuJourMontant;
            $scolariteOpJour = $opDuJourMontant;
        }

        $fraisAnnexesVerse = 0;
        $scolariteVerse = 0;
        foreach ($allPaiements as $p) {
            $cat = strtoupper(trim($p['categorie_paiement'] ?? ''));
            $tranche = strtoupper(trim($p['tranche_code'] ?? ''));
            if ($cat === 'FRAIS_ANNEXES' || $tranche === 'FRAIS_ANNEXES') {
                $fraisAnnexesVerse += (float)$p['montant_paiement'];
            } else {
                $scolariteVerse += (float)$p['montant_paiement'];
            }
        }
        $totalVerse = $scolariteVerse; // Règle comptable 4.2 : Total versé scolarité strict

        $scolariteTotPayer = (float)($ins['montant_scolarite_inscription'] ?? 0);
        $scolariteTotVerse = $scolariteVerse;
        $scolariteReste = max(0, $scolariteTotPayer - $scolariteTotVerse);

        $caissierNom = trim(($lastPaiement['prenom_user'] ?? '') . ' ' . ($lastPaiement['nom_user'] ?? ''));
        if (empty($caissierNom)) {
            $caissierNom = "La Caisse";
        }

        $photoSrc = null;
        $photoRel = !empty($ins['photo_etudiant']) ? $ins['photo_etudiant'] : ($ins['photo_inscription'] ?? '');
        if (!empty($photoRel)) {
            $cleanPath = ltrim($photoRel, '/');
            if (strpos($cleanPath, 'public/') === 0) {
                $photoPath = __DIR__ . '/../../' . $cleanPath;
            } else {
                $photoPath = __DIR__ . '/../../public/' . $cleanPath;
            }
            if (file_exists($photoPath) && is_file($photoPath)) {
                $ext = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
                $mimeType = ($ext === 'png') ? 'png' : (($ext === 'webp') ? 'webp' : 'jpeg');
                $photoSrc = 'data:image/' . $mimeType . ';base64,' . base64_encode(file_get_contents($photoPath));
            }
        }
        if (empty($photoSrc)) {
            $placeholderPath = __DIR__ . '/../../public/assets/images/placeholders/etudiant.png';
            if (file_exists($placeholderPath) && is_file($placeholderPath)) {
                $photoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($placeholderPath));
            }
        }

        $filiereCode = !empty($ins['filiere_code']) ? $ins['filiere_code'] : ($ins['code_filiere'] ?? '');
        $niveauCode = !empty($ins['niveau_code']) ? $ins['niveau_code'] : ($ins['code_niveau'] ?? '');
        $anneeCode = !empty($ins['annee_code']) ? $ins['annee_code'] : '';

        $premiereTrancheMontant = 0;
        if (!empty($filiereCode) && !empty($niveauCode) && !empty($anneeCode)) {
            $stmtTranche = $db->prepare("
                SELECT montant_tranche FROM " . TABLES::TRANCHES_SCOLARITE . "
                WHERE filiere_code = ? AND niveau_code = ? AND annee_code = ? AND statut_tranche = '" . STATUTS::ACTIF . "'
                ORDER BY id_tranche ASC LIMIT 1
            ");
            $stmtTranche->execute([$filiereCode, $niveauCode, $anneeCode]);
            $trancheRow = $stmtTranche->fetch(PDO::FETCH_ASSOC);
            if ($trancheRow) {
                $premiereTrancheMontant = (float)$trancheRow['montant_tranche'];
            }
        }

        $typeFiliere = !empty($ins['type_filiere']) ? $ins['type_filiere'] : 'TOUT';
        if (empty($ins['type_filiere']) && !empty($filiereCode)) {
            $stmtFil = $db->prepare("SELECT type_filiere FROM " . TABLES::FILIERES . " WHERE code_filiere = ? LIMIT 1");
            $stmtFil->execute([$filiereCode]);
            $filRow = $stmtFil->fetch(PDO::FETCH_ASSOC);
            if ($filRow && !empty($filRow['type_filiere'])) {
                $typeFiliere = $filRow['type_filiere'];
            }
        }
        require_once __DIR__ . '/../frais_annexes/ModelFraisAnnexe.php';
        $modelFA = new ModelFraisAnnexe();
        $fraisAnnexesInscription = (float)$modelFA->getMontantByTypeFiliere($typeFiliere, $anneeCode, $niveauCode, 'inscription');

        $droitTotPayer = $premiereTrancheMontant + $fraisAnnexesInscription;
        $droitTotVerse = $fraisAnnexesVerse + min($scolariteVerse, $premiereTrancheMontant);

        $fraisAnnexesTotPayer = $fraisAnnexesInscription;
        $fraisAnnexesTotVerse = $fraisAnnexesVerse;
        $fraisAnnexesReste = max(0, $fraisAnnexesTotPayer - $fraisAnnexesTotVerse);

        $scolariteTotal = $scolariteTotPayer;
        $resteAPayer = $scolariteReste;

        $filiereNiveauSlug = (!empty($ins['slug_filiere']) ? $ins['slug_filiere'] : 'GEC') . '_' . (!empty($ins['slug_niveau']) ? $ins['slug_niveau'] : '2A');
        if (!empty($ins['libelle_classe'])) {
            $filiereNiveauSlug = str_replace(' ', '_', $ins['libelle_classe']);
        }

        $numRecuCode = !empty($lastPaiement['recu_numero_paiement']) ? $lastPaiement['recu_numero_paiement'] : (!empty($lastPaiement['code_paiement']) ? $lastPaiement['code_paiement'] : ('GE-' . sprintf('%08d', $ins['id_inscription'])));

        return [
            'item' => $ins,
            'inscription' => $ins,
            'paiement' => $lastPaiement,
            'allPaiements' => $allPaiements,
            'annee_universitaire' => $ins['libelle_annee'] ?? '',
            'numero_recu' => $numRecuCode,
            'matricule' => !empty($ins['matricule_etudiant']) ? $ins['matricule_etudiant'] : ($ins['matricule_mesrs'] ?? ''),
            'nom_prenoms' => trim(strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ($ins['prenom_etudiant'] ?? '')),
            'filiere_niveau' => $filiereNiveauSlug,
            'statut_etudiant' => ($ins['affectation_etat'] === 'oui' || $ins['affectation_etat'] === 'affecte') ? 'AFFECTE' : 'NON AFFECTE',
            'type_operation' => $lastPaiement['type_paiement'] ?? 'SCOLARITE',
            'montant_operation' => $opDuJourMontant,
            'montant_operation_formatted' => number_format($opDuJourMontant, 0, ',', ' ') . 'CFA',
            'montant_en_lettres' => PdfService::numberToWordsFrench($opDuJourMontant),
            'scolarite_total' => $scolariteTotal,
            'total_verse' => $totalVerse,
            'reste_a_payer' => $resteAPayer,
            'caissier_nom' => $caissierNom,
            'photo_src' => $photoSrc,
            'date_operation' => !empty($lastPaiement['date_paiement']) ? date('d/m/Y H:i:s', strtotime($lastPaiement['date_paiement'])) : date('d/m/Y H:i:s'),
            'date_impression' => date('d/m/Y H:i:s'),
            'code_barre_val' => ($ins['code_inscription'] ?? '') . ',' . ($lastPaiement['code_paiement'] ?? ''),
            'premiere_tranche' => $premiereTrancheMontant,
            'frais_annexes_inscription' => $fraisAnnexesInscription,
            'droit_tot_payer' => $droitTotPayer,
            'droit_op_jour' => $droitOpJour,
            'droit_tot_verse' => $droitTotVerse,
            'frais_annexes_tot_payer' => $fraisAnnexesTotPayer,
            'frais_annexes_op_jour' => $fraisAnnexesOpJour,
            'frais_annexes_tot_verse' => $fraisAnnexesTotVerse,
            'frais_annexes_reste' => $fraisAnnexesReste,
            'scolarite_op_jour' => $scolariteOpJour,
            'scolarite_tot_payer' => $scolariteTotPayer,
            'scolarite_tot_verse' => $scolariteTotVerse,
            'scolarite_reste' => $scolariteReste
        ];
    }
}
