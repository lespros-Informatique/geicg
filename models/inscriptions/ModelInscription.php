<?php

class ModelInscription extends BaseModel
{
    protected string $table = 'inscriptions';
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
                WHERE annee_code = ? AND statut_inscription != 'annule'
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
                    WHERE i.annee_code = ? AND i.statut_inscription != 'annule'
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
                  AND i.statut_inscription != 'annule'
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
                SELECT ins.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant, e.sexe_etudiant, e.date_naissance_etudiant,
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       a.libelle_annee
                FROM " . TABLES::INSCRIPTIONS . " ins
                LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = ins.etudiant_code
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ins.classe_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = ins.annee_code
                WHERE ins.id_inscription = ? OR ins.code_inscription = ?
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
                if (($p['statut_paiement'] ?? '') !== 'annule') {
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
}
