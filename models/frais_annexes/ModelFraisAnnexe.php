<?php

class ModelFraisAnnexe extends BaseModel
{
    protected string $table = TABLES::FRAIS_ANNEXES;
    protected string $primaryKey = 'id_frais_annexe';
    protected ?string $statusField = 'statut_frais_annexe';
    protected ?string $createdAtField = 'created_at_frais_annexe';

    public function getAll(?string $anneeCode = null, ?string $typeFiliere = null, ?string $niveauCode = null, ?string $categorie = null): array
    {
        $sql = "
            SELECT fa.*, a.libelle_annee, n.libelle_niveau, n.slug_niveau
            FROM " . TABLES::FRAIS_ANNEXES . " fa
            LEFT JOIN " . TABLES::ANNEES . " a ON fa.annee_code = a.code_annee
            LEFT JOIN " . TABLES::NIVEAUX . " n ON fa.niveau_code = n.code_niveau
        ";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "fa.annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($typeFiliere)) {
            $conditions[] = "(fa.type_filiere = ? OR fa.type_filiere = 'TOUT')";
            $params[] = $typeFiliere;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "(fa.niveau_code = ? OR n.slug_niveau = ? OR fa.niveau_code IS NULL OR fa.niveau_code = '' OR fa.niveau_code = 'TOUT')";
            $params[] = $niveauCode;
            $params[] = $niveauCode;
        }
        if (!empty($categorie)) {
            $conditions[] = "fa.categorie_frais_annexe = ?";
            $params[] = $categorie;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY fa.id_frais_annexe DESC ";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById($id): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT fa.*, a.libelle_annee, n.libelle_niveau, n.slug_niveau
            FROM " . TABLES::FRAIS_ANNEXES . " fa
            LEFT JOIN " . TABLES::ANNEES . " a ON fa.annee_code = a.code_annee
            LEFT JOIN " . TABLES::NIVEAUX . " n ON fa.niveau_code = n.code_niveau
            WHERE fa.id_frais_annexe = ? OR fa.code_frais_annexe = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    private static array $montantCache = [];
    private static ?array $preloadedActiveRows = null;

    /**
     * Pré-charge en 1 seule requête tous les tarifs actifs de frais annexes afin d'éviter
     * toute requête SQL en boucle lors des calculs d'impayés ou d'éditions de masse.
     */
    public function preloadMontants(?string $anneeCode = null): void
    {
        try {
            $sql = "
                SELECT id_frais_annexe, type_filiere, annee_code, niveau_code, categorie_frais_annexe, montant_frais_annexe
                FROM " . TABLES::FRAIS_ANNEXES . "
                WHERE statut_frais_annexe = '" . STATUTS::ACTIF . "'
            ";
            $params = [];
            if (!empty($anneeCode) && $anneeCode !== 'ALL') {
                $sql .= " AND annee_code = ?";
                $params[] = $anneeCode;
            }
            $sql .= " ORDER BY id_frais_annexe DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (empty($anneeCode) || $anneeCode === 'ALL') {
                self::$preloadedActiveRows = $rows;
            } else {
                // Conserver les lignes déjà préchargées pour d'autres années si existantes
                if (self::$preloadedActiveRows === null) {
                    self::$preloadedActiveRows = [];
                }
                self::$preloadedActiveRows = array_values(array_filter(
                    self::$preloadedActiveRows,
                    fn($r) => ($r['annee_code'] ?? '') !== $anneeCode
                ));
                self::$preloadedActiveRows = array_merge(self::$preloadedActiveRows, $rows);
            }
        } catch (Exception $e) {
            error_log("ModelFraisAnnexe::preloadMontants error: " . $e->getMessage());
        }
    }

    public function getMontantByTypeFiliere(string $typeFiliere, string $anneeCode, ?string $niveauCode = null, ?string $categorie = null): float
    {
        $cacheKey = "{$typeFiliere}|{$anneeCode}|" . ($niveauCode ?? '') . "|" . ($categorie ?? '');
        if (array_key_exists($cacheKey, self::$montantCache)) {
            return self::$montantCache[$cacheKey];
        }

        // Résolution 100% en mémoire (0 requête SQL) si preloadMontants() a été appelé
        if (self::$preloadedActiveRows !== null) {
            $bestPriority = PHP_INT_MAX;
            $bestId = -1;
            $bestMontant = null;

            foreach (self::$preloadedActiveRows as $r) {
                if (($r['annee_code'] ?? '') !== $anneeCode) continue;
                $rType = (string)($r['type_filiere'] ?? '');
                if ($rType !== $typeFiliere && $rType !== 'TOUT') continue;
                if (!empty($categorie) && ($r['categorie_frais_annexe'] ?? '') !== $categorie) continue;

                $rNiv = (string)($r['niveau_code'] ?? '');
                $nivPrio = 2;
                if (!empty($niveauCode)) {
                    if ($rNiv !== $niveauCode && $rNiv !== '') continue;
                    $nivPrio = ($rNiv === $niveauCode) ? 1 : 2;
                }
                $typePrio = ($rType === $typeFiliere) ? 1 : 2;
                $priority = ($nivPrio * 10) + $typePrio;
                $rowId = (int)($r['id_frais_annexe'] ?? 0);

                if ($priority < $bestPriority || ($priority === $bestPriority && $rowId > $bestId)) {
                    $bestPriority = $priority;
                    $bestId = $rowId;
                    $bestMontant = (float)$r['montant_frais_annexe'];
                }
            }

            $montant = $bestMontant !== null ? $bestMontant : 0.0;
            self::$montantCache[$cacheKey] = $montant;
            return $montant;
        }

        $sql = "
            SELECT montant_frais_annexe 
            FROM " . TABLES::FRAIS_ANNEXES . " 
            WHERE statut_frais_annexe = '" . STATUTS::ACTIF . "'
              AND (type_filiere = ? OR type_filiere = 'TOUT')
              AND annee_code = ?
        ";
        $params = [$typeFiliere, $anneeCode];

        if (!empty($categorie)) {
            $sql .= " AND categorie_frais_annexe = ? ";
            $params[] = $categorie;
        }

        if (!empty($niveauCode)) {
            $sql .= " AND (niveau_code = ? OR niveau_code IS NULL OR niveau_code = '') ";
            $params[] = $niveauCode;
            $sql .= " ORDER BY 
                (CASE WHEN niveau_code = ? THEN 1 ELSE 2 END), 
                (CASE WHEN type_filiere = ? THEN 1 ELSE 2 END), 
                id_frais_annexe DESC ";
            $params[] = $niveauCode;
            $params[] = $typeFiliere;
        } else {
            $sql .= " ORDER BY (CASE WHEN type_filiere = ? THEN 1 ELSE 2 END), id_frais_annexe DESC ";
            $params[] = $typeFiliere;
        }

        $sql .= " LIMIT 1 ";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $montant = $row ? (float)$row['montant_frais_annexe'] : 0.0;
        self::$montantCache[$cacheKey] = $montant;
        return $montant;
    }

    public function getFraisAnnexeDetails(string $typeFiliere, string $anneeCode, ?string $niveauCode = null, ?string $categorie = null): ?array
    {
        $sql = "
            SELECT * 
            FROM " . TABLES::FRAIS_ANNEXES . " 
            WHERE statut_frais_annexe = '" . STATUTS::ACTIF . "'
              AND (type_filiere = ? OR type_filiere = 'TOUT')
              AND annee_code = ?
        ";
        $params = [$typeFiliere, $anneeCode];

        if (!empty($categorie)) {
            $sql .= " AND categorie_frais_annexe = ? ";
            $params[] = $categorie;
        }

        if (!empty($niveauCode)) {
            $sql .= " AND (niveau_code = ? OR niveau_code IS NULL OR niveau_code = '') ";
            $params[] = $niveauCode;
            $sql .= " ORDER BY 
                (CASE WHEN niveau_code = ? THEN 1 ELSE 2 END), 
                (CASE WHEN type_filiere = ? THEN 1 ELSE 2 END), 
                id_frais_annexe DESC ";
            $params[] = $niveauCode;
            $params[] = $typeFiliere;
        } else {
            $sql .= " ORDER BY (CASE WHEN type_filiere = ? THEN 1 ELSE 2 END), id_frais_annexe DESC ";
            $params[] = $typeFiliere;
        }

        $sql .= " LIMIT 1 ";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function checkDuplicate(string $anneeCode, string $typeFiliere, ?string $niveauCode, ?string $categorie = null, ?int $excludeId = null): ?array
    {
        // Contrôle d'unicité sur la combinaison (type_filiere x niveau_code x categorie_frais_annexe x annee_code)
        $sql = "SELECT * FROM " . TABLES::FRAIS_ANNEXES . " WHERE annee_code = ? AND type_filiere = ?";
        $params = [$anneeCode, $typeFiliere];
        if (empty($niveauCode) || $niveauCode === 'TOUT') {
            $sql .= " AND (niveau_code IS NULL OR niveau_code = '' OR niveau_code = 'TOUT')";
        } else {
            $sql .= " AND niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($categorie)) {
            $sql .= " AND categorie_frais_annexe = ?";
            $params[] = $categorie;
        }
        if ($excludeId) {
            $sql .= " AND id_frais_annexe != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->getCon()->prepare($sql . " LIMIT 1");
        $stmt->execute($params);
        $dup = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dup ?: null;
    }

    public function getAnneeByCode(string $code): ?array
    {
        $stmt = $this->getCon()->prepare("SELECT code_annee, libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function reconduireFraisAnnee(string $anneeSourceCode, string $anneeCibleCode, ?string $etabCode = null, ?string $userCode = null): array
    {
        $db = $this->getCon();
        $validator = new Validator();

        // 1. Récupérer tous les tarifs actifs de l'année source
        $stmt = $db->prepare("SELECT * FROM " . TABLES::FRAIS_ANNEXES . " WHERE annee_code = ? AND statut_frais_annexe = '" . STATUTS::ACTIF . "'");
        $stmt->execute([$anneeSourceCode]);
        $fraisSource = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($fraisSource)) {
            return [
                'success' => false,
                'message' => "Aucun tarif de frais annexes actif trouvé pour l'année source sélectionnée.",
                'count' => 0
            ];
        }

        $createdCount = 0;
        $skippedCount = 0;

        $stmtInsert = $db->prepare("
            INSERT INTO " . TABLES::FRAIS_ANNEXES . " 
            (code_frais_annexe, libelle_frais_annexe, montant_frais_annexe, type_filiere, niveau_code, categorie_frais_annexe, annee_code, etablissement_code, statut_frais_annexe, created_at_frais_annexe, user_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW(), ?)
        ");

        foreach ($fraisSource as $fa) {
            // Vérifier si le tarif existe déjà dans l'année de destination
            $duplicate = $this->checkDuplicate($anneeCibleCode, $fa['type_filiere'], $fa['niveau_code'], $fa['categorie_frais_annexe']);
            if ($duplicate) {
                $skippedCount++;
                continue;
            }

            // Générer un nouveau code unique
            $newCode = $validator->generateCode(TABLES::FRAIS_ANNEXES, 'code_frais_annexe', 'FRAIS-ANN-', 8);

            $stmtInsert->execute([
                $newCode,
                $fa['libelle_frais_annexe'],
                $fa['montant_frais_annexe'],
                $fa['type_filiere'],
                $fa['niveau_code'],
                $fa['categorie_frais_annexe'],
                $anneeCibleCode,
                $etabCode ?: $fa['etablissement_code'],
                $userCode ?: $fa['user_code']
            ]);

            $createdCount++;
        }

        return [
            'success' => true,
            'count' => $createdCount,
            'skipped' => $skippedCount,
            'message' => "Reconduction terminée : {$createdCount} tarif(s) de frais annexes reconduit(s), {$skippedCount} ignoré(s) (déjà existants)."
        ];
    }
}
