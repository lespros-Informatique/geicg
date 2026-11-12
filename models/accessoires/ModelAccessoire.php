<?php

class ModelAccessoire extends BaseModel
{
    protected string $table = TABLES::ACCESSOIRES;
    protected string $primaryKey = 'id_accessoire';
    protected ?string $statusField = 'statut_accessoire';
    protected ?string $createdAtField = 'created_at_accessoire';

    public function getById($id): array
    {
        if (empty($id)) {
            return [];
        }
        $sql = "
            SELECT a.*,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT anf.niveau_code ORDER BY n.id_niveau ASC SEPARATOR ','),
                       ''
                   ) AS niveaux_codes_csv,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT n.libelle_niveau ORDER BY n.id_niveau ASC SEPARATOR ', '),
                       ''
                   ) AS libelle_niveau,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT anf.filiere_code ORDER BY f.id_filiere ASC SEPARATOR ','),
                       ''
                   ) AS filieres_codes_csv,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT COALESCE(NULLIF(f.slug_filiere, ''), f.libelle_filiere) ORDER BY f.id_filiere ASC SEPARATOR ', '),
                       ''
                   ) AS libelle_filiere,
                   CASE
                       WHEN COUNT(anf.id_accessoire_niveau_filiere) > 0
                        AND SUM(CASE WHEN anf.filiere_code IS NULL OR anf.filiere_code = '' THEN 1 ELSE 0 END) = 0
                        AND COUNT(DISTINCT f.type_filiere) = 1
                       THEN MAX(f.type_filiere)
                       ELSE 'TOUT'
                   END AS type_filiere_cible
            FROM " . TABLES::ACCESSOIRES . " a
            LEFT JOIN " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . " anf ON anf.accessoire_code = a.code_accessoire
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = anf.niveau_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = anf.filiere_code
            WHERE a.id_accessoire = ?
            GROUP BY a.id_accessoire
            LIMIT 1
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return [];
            }
            $csv = trim((string)($row['niveaux_codes_csv'] ?? ''));
            $row['niveaux_codes'] = $csv !== '' ? array_values(array_filter(array_map('trim', explode(',', $csv)))) : [];
            $row['niveau_code'] = $csv;
            $filCsv = trim((string)($row['filieres_codes_csv'] ?? ''));
            $row['filieres_codes'] = $filCsv !== '' ? array_values(array_filter(array_map('trim', explode(',', $filCsv)))) : [];
            return $row;
        } catch (Exception $e) {
            error_log("Get accessoire by id: " . $e->getMessage());
            return [];
        }
    }

    public function getAll(?string $anneeCode = null): array
    {
        $sql = "
            SELECT a.*,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT anf.niveau_code ORDER BY n.id_niveau ASC SEPARATOR ','),
                       ''
                   ) AS niveaux_codes_csv,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT n.libelle_niveau ORDER BY n.id_niveau ASC SEPARATOR ', '),
                       ''
                   ) AS libelle_niveau,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT anf.filiere_code ORDER BY f.id_filiere ASC SEPARATOR ','),
                       ''
                   ) AS filieres_codes_csv,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT COALESCE(NULLIF(f.slug_filiere, ''), f.libelle_filiere) ORDER BY f.id_filiere ASC SEPARATOR ', '),
                       ''
                   ) AS libelle_filiere,
                   CASE
                       WHEN COUNT(anf.id_accessoire_niveau_filiere) > 0
                        AND SUM(CASE WHEN anf.filiere_code IS NULL OR anf.filiere_code = '' THEN 1 ELSE 0 END) = 0
                        AND COUNT(DISTINCT f.type_filiere) = 1
                       THEN MAX(f.type_filiere)
                       ELSE 'TOUT'
                   END AS type_filiere_cible
            FROM " . TABLES::ACCESSOIRES . " a
            LEFT JOIN " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . " anf ON anf.accessoire_code = a.code_accessoire
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = anf.niveau_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = anf.filiere_code
            GROUP BY a.id_accessoire
            ORDER BY a.id_accessoire DESC
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$row) {
                $csv = trim((string)($row['niveaux_codes_csv'] ?? ''));
                $codes = $csv !== '' ? array_values(array_filter(array_map('trim', explode(',', $csv)))) : [];
                $row['niveaux_codes'] = $codes;
                $row['niveau_code'] = $csv;
                $filCsv = trim((string)($row['filieres_codes_csv'] ?? ''));
                $row['filieres_codes'] = $filCsv !== '' ? array_values(array_filter(array_map('trim', explode(',', $filCsv)))) : [];
            }
            unset($row);
            return $rows;
        } catch (Exception $e) {
            error_log("Get all accessoires: " . $e->getMessage());
            return [];
        }
    }

    public function getActifs(): array
    {
        $sql = "
            SELECT a.*,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT anf.niveau_code ORDER BY n.id_niveau ASC SEPARATOR ','),
                       ''
                   ) AS niveaux_codes_csv,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT n.libelle_niveau ORDER BY n.id_niveau ASC SEPARATOR ', '),
                       ''
                   ) AS libelle_niveau,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT anf.filiere_code ORDER BY f.id_filiere ASC SEPARATOR ','),
                       ''
                   ) AS filieres_codes_csv,
                   COALESCE(
                       GROUP_CONCAT(DISTINCT COALESCE(NULLIF(f.slug_filiere, ''), f.libelle_filiere) ORDER BY f.id_filiere ASC SEPARATOR ', '),
                       ''
                   ) AS libelle_filiere,
                   CASE
                       WHEN COUNT(anf.id_accessoire_niveau_filiere) > 0
                        AND SUM(CASE WHEN anf.filiere_code IS NULL OR anf.filiere_code = '' THEN 1 ELSE 0 END) = 0
                        AND COUNT(DISTINCT f.type_filiere) = 1
                       THEN MAX(f.type_filiere)
                       ELSE 'TOUT'
                   END AS type_filiere_cible
            FROM " . TABLES::ACCESSOIRES . " a
            LEFT JOIN " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . " anf ON anf.accessoire_code = a.code_accessoire
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = anf.niveau_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = anf.filiere_code
            WHERE a.statut_accessoire = '" . STATUTS::ACTIF . "'
            GROUP BY a.id_accessoire
            ORDER BY a.libelle_accessoire ASC
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelAccessoire::getActifs error: " . $e->getMessage());
            return [];
        }
    }

    public function syncCibles(string $accessoireCode, string $typeFiliereCible, array $niveauCodes, ?string $etabCode = null, ?string $userCode = null): void
    {
        if (empty($accessoireCode)) {
            return;
        }
        $db = $this->getCon();
        $cleanNiveaux = array_values(array_unique(array_filter(array_map('trim', $niveauCodes), function ($v) {
            return $v !== '' && $v !== 'TOUT';
        })));

        try {
            $stmtDel = $db->prepare("DELETE FROM " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . " WHERE accessoire_code = ?");
            $stmtDel->execute([$accessoireCode]);

            $stmtIns = $db->prepare("
                INSERT INTO " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . "
                (code_accessoire_niveau_filiere, accessoire_code, filiere_code, niveau_code, etablissement_code, user_code, created_at_accessoire_niveau_filiere)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            if (empty($cleanNiveaux)) {
                $codeAnf = 'ANF-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                $stmtIns->execute([$codeAnf, $accessoireCode, null, null, $etabCode, $userCode]);
            } else {
                foreach ($cleanNiveaux as $nivCode) {
                    $codeAnf = 'ANF-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                    $stmtIns->execute([$codeAnf, $accessoireCode, null, $nivCode, $etabCode, $userCode]);
                }
            }
        } catch (Exception $e) {
            error_log("ModelAccessoire::syncCibles error: " . $e->getMessage());
        }
    }

    public function getFiliereCyclesMap(): array
    {
        $sql = "
            SELECT fc.filiere_code, fc.cycle_code, fc.niveau_code
            FROM " . TABLES::FILIERE_CYCLES . " fc
            WHERE fc.statut_filiere_cycle = '" . STATUTS::ACTIF . "' OR fc.statut_filiere_cycle IS NULL
        ";
        try {
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelAccessoire::getFiliereCyclesMap error: " . $e->getMessage());
            return [];
        }
    }

    public function getPivotDataByCycle(string $cycleCode): array
    {
        if (empty($cycleCode)) {
            return ['filieres' => [], 'niveaux' => [], 'pivots' => []];
        }
        $sql = "
            SELECT fc.code_filiere_cycle,
                   fc.cycle_code,
                   fc.filiere_code,
                   fc.niveau_code,
                   f.libelle_filiere,
                   f.slug_filiere,
                   f.type_filiere,
                   n.id_niveau,
                   n.libelle_niveau,
                   n.slug_niveau
            FROM " . TABLES::FILIERE_CYCLES . " fc
            INNER JOIN " . TABLES::FILIERES . " f ON f.code_filiere = fc.filiere_code
            INNER JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = fc.niveau_code
            WHERE fc.cycle_code = ?
              AND (fc.statut_filiere_cycle = '" . STATUTS::ACTIF . "' OR fc.statut_filiere_cycle IS NULL)
              AND (f.statut_filiere = '" . STATUTS::ACTIF . "' OR f.statut_filiere IS NULL)
              AND (n.statut_niveau = '" . STATUTS::ACTIF . "' OR n.statut_niveau IS NULL)
            ORDER BY f.libelle_filiere ASC, n.id_niveau ASC
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$cycleCode]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $filieresMap = [];
            $niveauxMap = [];
            $pivots = [];

            foreach ($rows as $r) {
                $fCode = $r['filiere_code'];
                $nCode = $r['niveau_code'];

                if (!isset($filieresMap[$fCode])) {
                    $filieresMap[$fCode] = [
                        'code_filiere' => $fCode,
                        'libelle_filiere' => $r['libelle_filiere'],
                        'slug_filiere' => $r['slug_filiere'],
                        'type_filiere' => $r['type_filiere']
                    ];
                }
                if (!isset($niveauxMap[$nCode])) {
                    $niveauxMap[$nCode] = [
                        'code_niveau' => $nCode,
                        'libelle_niveau' => $r['libelle_niveau'],
                        'slug_niveau' => $r['slug_niveau'],
                        'id_niveau' => (int)$r['id_niveau']
                    ];
                }
                $pivots[] = [
                    'filiere_code' => $fCode,
                    'niveau_code' => $nCode
                ];
            }

            $niveauxList = array_values($niveauxMap);
            usort($niveauxList, fn($a, $b) => ($a['id_niveau'] <=> $b['id_niveau']));

            return [
                'filieres' => array_values($filieresMap),
                'niveaux' => $niveauxList,
                'pivots' => $pivots
            ];
        } catch (Exception $e) {
            error_log("ModelAccessoire::getPivotDataByCycle error: " . $e->getMessage());
            return ['filieres' => [], 'niveaux' => [], 'pivots' => []];
        }
    }

    public function getCiblesList(?string $typeFiliere = null, ?string $niveauCode = null, ?string $filiereCode = null, ?string $cycleCode = null): array
    {
        $where = [];
        $params = [];

        if (!empty($cycleCode)) {
            $where[] = "(anf.filiere_code IS NULL OR anf.filiere_code = '' OR anf.filiere_code IN (SELECT fc.filiere_code FROM " . TABLES::FILIERE_CYCLES . " fc WHERE fc.cycle_code = ?))";
            $where[] = "(anf.niveau_code IS NULL OR anf.niveau_code = '' OR anf.niveau_code IN (SELECT fc2.niveau_code FROM " . TABLES::FILIERE_CYCLES . " fc2 WHERE fc2.cycle_code = ?))";
            $params[] = $cycleCode;
            $params[] = $cycleCode;
        }
        if (!empty($typeFiliere)) {
            if ($typeFiliere === 'TOUT') {
                $where[] = "(anf.filiere_code IS NULL OR anf.filiere_code = '')";
            } else {
                $where[] = "f.type_filiere = ?";
                $params[] = $typeFiliere;
            }
        }
        if (!empty($filiereCode)) {
            $where[] = "(anf.filiere_code = ? OR anf.filiere_code IS NULL OR anf.filiere_code = '')";
            $params[] = $filiereCode;
        }
        if (!empty($niveauCode)) {
            $where[] = "(anf.niveau_code = ? OR anf.niveau_code IS NULL OR anf.niveau_code = '')";
            $params[] = $niveauCode;
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT anf.*,
                   a.id_accessoire,
                   a.libelle_accessoire,
                   a.statut_accessoire,
                   f.libelle_filiere,
                   f.slug_filiere,
                   COALESCE(f.type_filiere, 'TOUT') AS type_filiere_cible,
                   n.libelle_niveau,
                   n.slug_niveau,
                   (SELECT fc.cycle_code FROM " . TABLES::FILIERE_CYCLES . " fc
                    WHERE (anf.filiere_code IS NULL OR fc.filiere_code = anf.filiere_code)
                      AND (anf.niveau_code IS NULL OR fc.niveau_code = anf.niveau_code)
                    LIMIT 1) AS cycle_code
            FROM " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . " anf
            JOIN " . TABLES::ACCESSOIRES . " a ON a.code_accessoire = anf.accessoire_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = anf.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = anf.niveau_code
            {$whereSql}
            ORDER BY anf.id_accessoire_niveau_filiere DESC
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelAccessoire::getCiblesList error: " . $e->getMessage());
            return [];
        }
    }

    public function saveCiblesBatch(array $accessoireCodes, string $typeFiliereCible, array $filiereCodes, array $niveauCodes, ?string $etabCode = null, ?string $userCode = null, ?int $idCibleToUpdate = null, ?string $cycleCode = null): int
    {
        $db = $this->getCon();
        $filiereType = in_array($typeFiliereCible, ['TOUT', 'INDUSTRIELLE', 'TERTIAIRE'], true) ? $typeFiliereCible : 'TOUT';
        $cleanAccs = array_values(array_unique(array_filter(array_map('trim', $accessoireCodes))));
        $cleanFilieres = array_values(array_unique(array_filter(array_map('trim', $filiereCodes), function ($v) {
            return $v !== '' && $v !== 'TOUT';
        })));
        $cleanNiveaux = array_values(array_unique(array_filter(array_map('trim', $niveauCodes), function ($v) {
            return $v !== '' && $v !== 'TOUT';
        })));

        if (empty($cleanAccs)) {
            return 0;
        }

        try {
            if ($idCibleToUpdate !== null && $idCibleToUpdate > 0) {
                $stmtDel = $db->prepare("DELETE FROM " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . " WHERE id_accessoire_niveau_filiere = ?");
                $stmtDel->execute([$idCibleToUpdate]);
            }

            if (!empty($cycleCode)) {
                if (empty($cleanFilieres)) {
                    $stmtFilCycle = $db->prepare("SELECT DISTINCT filiere_code FROM " . TABLES::FILIERE_CYCLES . " WHERE cycle_code = ? AND (statut_filiere_cycle = 'actif' OR statut_filiere_cycle IS NULL)");
                    $stmtFilCycle->execute([$cycleCode]);
                    $cleanFilieres = array_values(array_filter($stmtFilCycle->fetchAll(PDO::FETCH_COLUMN) ?: []));
                }
                if (empty($cleanNiveaux)) {
                    $stmtNivCycle = $db->prepare("SELECT DISTINCT niveau_code FROM " . TABLES::FILIERE_CYCLES . " WHERE cycle_code = ? AND (statut_filiere_cycle = 'actif' OR statut_filiere_cycle IS NULL)");
                    $stmtNivCycle->execute([$cycleCode]);
                    $cleanNiveaux = array_values(array_filter($stmtNivCycle->fetchAll(PDO::FETCH_COLUMN) ?: []));
                }
            } elseif (empty($cleanFilieres) && in_array($filiereType, ['INDUSTRIELLE', 'TERTIAIRE'], true)) {
                $stmtFilByType = $db->prepare("SELECT code_filiere FROM " . TABLES::FILIERES . " WHERE type_filiere = ? AND statut_filiere = 'actif'");
                $stmtFilByType->execute([$filiereType]);
                $cleanFilieres = $stmtFilByType->fetchAll(PDO::FETCH_COLUMN) ?: [];
            }

            $stmtCheck = $db->prepare("
                SELECT id_accessoire_niveau_filiere
                FROM " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . "
                WHERE accessoire_code = ?
                  AND ((filiere_code IS NULL AND ? IS NULL) OR filiere_code = ?)
                  AND ((niveau_code IS NULL AND ? IS NULL) OR niveau_code = ?)
                LIMIT 1
            ");

            $stmtIns = $db->prepare("
                INSERT INTO " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . "
                (code_accessoire_niveau_filiere, accessoire_code, filiere_code, niveau_code, etablissement_code, user_code, created_at_accessoire_niveau_filiere)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            $targetFilieres = empty($cleanFilieres) ? [null] : $cleanFilieres;
            $targetNiveaux = empty($cleanNiveaux) ? [null] : $cleanNiveaux;
            $inserted = 0;

            foreach ($cleanAccs as $accCode) {
                foreach ($targetFilieres as $filCode) {
                    foreach ($targetNiveaux as $nivCode) {
                        $stmtCheck->execute([$accCode, $filCode, $filCode, $nivCode, $nivCode]);
                        if ($stmtCheck->fetchColumn()) {
                            continue;
                        }
                        $codeAnf = 'ANF-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                        if ($stmtIns->execute([$codeAnf, $accCode, $filCode, $nivCode, $etabCode, $userCode])) {
                            $inserted++;
                        }
                    }
                }
            }

            return $inserted;
        } catch (Exception $e) {
            error_log("ModelAccessoire::saveCiblesBatch error: " . $e->getMessage());
            return 0;
        }
    }

    public function deleteCible(int $idCible): bool
    {
        try {
            $stmt = $this->getCon()->prepare("DELETE FROM " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . " WHERE id_accessoire_niveau_filiere = ?");
            return $stmt->execute([$idCible]);
        } catch (Exception $e) {
            error_log("ModelAccessoire::deleteCible error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAccessoire(int $idAccessoire): bool
    {
        try {
            $db = $this->getCon();
            $row = $this->getById($idAccessoire);
            if (!empty($row['code_accessoire'])) {
                $stmtAnf = $db->prepare("DELETE FROM " . TABLES::ACCESSOIRE_NIVEAU_FILIERE . " WHERE accessoire_code = ?");
                $stmtAnf->execute([$row['code_accessoire']]);
            }
            $stmt = $db->prepare("DELETE FROM " . TABLES::ACCESSOIRES . " WHERE id_accessoire = ?");
            return $stmt->execute([$idAccessoire]);
        } catch (Exception $e) {
            error_log("ModelAccessoire::deleteAccessoire error: " . $e->getMessage());
            return false;
        }
    }

    public function getDistributions(string $filter = 'all', ?string $anneeCode = null, ?string $classeCode = null): array
    {
        $db = $this->getCon();
        $where = "WHERE (ai.statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR ai.statut_accessoire_inscription IS NULL) AND (i.statut_inscription != '" . STATUTS::ANNULE . "' OR i.statut_inscription IS NULL)";
        $params = [];

        if (!empty($anneeCode)) {
            $where .= " AND ai.annee_code = ?";
            $params[] = $anneeCode;
        }

        if (!empty($classeCode)) {
            $where .= " AND i.classe_code = ?";
            $params[] = $classeCode;
        }

        $sql = "
            SELECT 
                   i.code_inscription,
                   e.code_etudiant,
                   COALESCE(e.matricule_etudiant, '-') as matricule_etudiant,
                   COALESCE(e.nom_etudiant, '') as nom_etudiant,
                   COALESCE(e.prenom_etudiant, '') as prenom_etudiant,
                   COALESCE(e.photo_etudiant, '') as photo_etudiant,
                   COALESCE(e.telephone_etudiant, '-') as telephone_etudiant,
                   CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) as nom_complet,
                   COALESCE(c.libelle_classe, 'Non assignée') as libelle_classe,
                   COUNT(ai.id_accessoire_inscription) as total_kits,
                   SUM(CASE WHEN ai.etat_retrait = 'retire' THEN 1 ELSE 0 END) as total_retires,
                   SUM(CASE WHEN ai.etat_retrait = 'en_attente' THEN 1 ELSE 0 END) as total_en_attente,
                   GROUP_CONCAT(
                       CONCAT(
                           ai.id_accessoire_inscription, ':::',
                           COALESCE(a.libelle_accessoire, 'Kit'), ':::',
                           ai.etat_retrait, ':::',
                           COALESCE(DATE_FORMAT(ai.date_retrait, '%d/%m/%Y %H:%i'), ''), ':::',
                           COALESCE(ai.accessoire_code, '')
                       ) ORDER BY ai.id_accessoire_inscription ASC SEPARATOR '|||'
                   ) as kits_details
            FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " ai
            LEFT JOIN " . TABLES::ACCESSOIRES . " a ON a.code_accessoire = ai.accessoire_code
            LEFT JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = ai.inscription_code
            LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
            {$where}
            GROUP BY i.code_inscription, e.code_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, e.photo_etudiant, e.telephone_etudiant, c.libelle_classe
        ";

        if ($filter === 'en_attente') {
            $sql .= " HAVING SUM(CASE WHEN ai.etat_retrait = 'en_attente' THEN 1 ELSE 0 END) > 0";
        } elseif ($filter === 'retire') {
            $sql .= " HAVING SUM(CASE WHEN ai.etat_retrait = 'en_attente' THEN 1 ELSE 0 END) = 0";
        }

        $sql .= " ORDER BY i.id_inscription DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as &$row) {
                $items = [];
                if (!empty($row['kits_details'])) {
                    $rawItems = explode('|||', $row['kits_details']);
                    foreach ($rawItems as $raw) {
                        $parts = explode(':::', $raw);
                        if (count($parts) >= 3) {
                            $items[] = [
                                'id_accessoire_inscription' => (int)($parts[0] ?? 0),
                                'libelle_accessoire' => $parts[1] ?? 'Kit',
                                'etat_retrait' => $parts[2] ?? 'en_attente',
                                'date_retrait_formatee' => $parts[3] ?? '',
                                'accessoire_code' => $parts[4] ?? ''
                            ];
                        }
                    }
                }
                $row['items'] = $items;
                $row['pourcentage'] = ($row['total_kits'] > 0) ? round(($row['total_retires'] / $row['total_kits']) * 100) : 0;
            }
            unset($row);

            return $rows;
        } catch (Exception $e) {
            error_log("ModelAccessoire::getDistributions error: " . $e->getMessage());
            return [];
        }
    }

    public function getBonRemiseData(string $inscriptionCode): array
    {
        $db = $this->getCon();
        $stmt = $db->prepare("
            SELECT 
                i.code_inscription, i.created_at_inscription AS date_inscription, i.statut_inscription,
                i.montant_scolarite_inscription, i.affectation_etat, i.annee_code,
                e.code_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, e.telephone_etudiant, e.photo_etudiant,
                cl.libelle_classe, cl.filiere_code,
                a.libelle_annee,
                et.libelle_etablissement AS nom_etablissement, et.logo_etablissement, et.telephone_etablissement, et.adresse_etablissement
            FROM " . TABLES::INSCRIPTIONS . " i
            JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
            JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
            LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
            LEFT JOIN " . TABLES::ETABLISSEMENTS . " et ON et.code_etablissement = i.etablissement_code
            WHERE i.code_inscription = ? AND i.statut_inscription != '" . STATUTS::ANNULE . "'
            LIMIT 1
        ");
        $stmt->execute([$inscriptionCode]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return [];
        }

        $stmtKits = $db->prepare("
            SELECT 
                ai.id_accessoire_inscription,
                ai.code_accessoire_inscription,
                ai.etat_retrait,
                ai.date_retrait,
                DATE_FORMAT(ai.date_retrait, '%d/%m/%Y %H:%i') as date_retrait_formatee,
                DATE_FORMAT(ai.created_at_accessoire_inscription, '%d/%m/%Y') as date_attribution_formatee,
                a.libelle_accessoire
            FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " ai
            JOIN " . TABLES::ACCESSOIRES . " a ON a.code_accessoire = ai.accessoire_code
            WHERE ai.inscription_code = ?
              AND (ai.statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR ai.statut_accessoire_inscription IS NULL)
            ORDER BY a.libelle_accessoire ASC
        ");
        $stmtKits->execute([$inscriptionCode]);
        $kits = $stmtKits->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $kitsRemis = [];
        $kitsEnAttente = [];

        foreach ($kits as $k) {
            if ($k['etat_retrait'] === 'retire') {
                $kitsRemis[] = $k;
            } else {
                $kitsEnAttente[] = $k;
            }
        }

        // Paiements associés à cette inscription
        $stmtP = $db->prepare("
            SELECT categorie_paiement, montant_paiement, type_paiement, code_paiement, date_paiement
            FROM " . TABLES::PAIEMENTS . "
            WHERE inscription_code = ? AND statut_paiement != '" . STATUTS::ANNULE . "'
            ORDER BY date_paiement DESC
        ");
        $stmtP->execute([$inscriptionCode]);
        $allPayments = $stmtP->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $fraisAnnexesPaye = 0.0;
        $dernierCodePaiementFrais = '';
        $dateDernierPaiementFrais = '';
        $scolaritePaye = 0.0;
        $dernierCodePaiementAutre = '';
        $dateDernierPaiementAutre = '';
        foreach ($allPayments as $p) {
            if ($p['categorie_paiement'] === 'FRAIS_ANNEXES') {
                $fraisAnnexesPaye += (float)$p['montant_paiement'];
                if (empty($dernierCodePaiementFrais)) {
                    $dernierCodePaiementFrais = $p['code_paiement'];
                    $dateDernierPaiementFrais = !empty($p['date_paiement']) ? date('d/m/Y', strtotime($p['date_paiement'])) : '';
                }
            } else {
                if (empty($dernierCodePaiementAutre)) {
                    $dernierCodePaiementAutre = $p['code_paiement'];
                    $dateDernierPaiementAutre = !empty($p['date_paiement']) ? date('d/m/Y', strtotime($p['date_paiement'])) : '';
                }
                if ($p['categorie_paiement'] === 'SCOLARITE') {
                    $scolaritePaye += (float)$p['montant_paiement'];
                }
            }
        }

        // Grille tarifaire des frais annexes correspondante
        $montantPayerTenue = 0.0;
        if (!empty($student['filiere_code'])) {
            $stmtFrais = $db->prepare("
                SELECT fa.montant_frais_annexe 
                FROM " . TABLES::FRAIS_ANNEXES . " fa
                JOIN " . TABLES::FILIERES . " fil ON fil.code_filiere = ?
                WHERE fa.annee_code = ? 
                  AND (fa.type_filiere = fil.type_filiere OR fa.type_filiere = 'TOUT')
                  AND fa.statut_frais_annexe = '" . STATUTS::ACTIF . "'
                ORDER BY (fa.type_filiere != 'TOUT') DESC
                LIMIT 1
            ");
            $stmtFrais->execute([$student['filiere_code'], $student['annee_code'] ?? '']);
            $faRow = $stmtFrais->fetch(PDO::FETCH_ASSOC);
            if ($faRow && (float)$faRow['montant_frais_annexe'] > 0) {
                $montantPayerTenue = (float)$faRow['montant_frais_annexe'];
            }
        }
        if ($montantPayerTenue <= 0) {
            $montantPayerTenue = $fraisAnnexesPaye;
        }

        $restePayerTenue = max(0, $montantPayerTenue - $fraisAnnexesPaye);
        $scolaritePayer = (float)($student['montant_scolarite_inscription'] ?? 0);
        $resteScolarite = max(0, $scolaritePayer - $scolaritePaye);

        // Détermination du code paiement officiel (JAMAIS le code d'inscription)
        $codePaiementFinal = $dernierCodePaiementFrais ?: $dernierCodePaiementAutre;
        if (empty($codePaiementFinal)) {
            require_once __DIR__ . '/../../core/Validator.php';
            $validator = new Validator();
            $codePaiementFinal = $validator->generateCode(TABLES::PAIEMENTS, 'code_paiement', 'REC-FA-', 6);
        }

        return [
            'student' => $student,
            'etudiant' => $student,
            'kits' => $kits,
            'kits_remis' => $kitsRemis,
            'kits_en_attente' => $kitsEnAttente,
            'total_kits' => count($kits),
            'total_remis' => count($kitsRemis),
            'total_en_attente' => count($kitsEnAttente),
            'pourcentage' => (count($kits) > 0) ? round((count($kitsRemis) / count($kits)) * 100) : 0,
            'finances' => [
                'code_paiement' => $codePaiementFinal,
                'date_operation' => $dateDernierPaiementFrais ?: ($dateDernierPaiementAutre ?: date('d/m/Y', strtotime($student['date_inscription'] ?? date('Y-m-d')))),
                'montant_payer_tenue' => $montantPayerTenue,
                'montant_paye_tenue' => $fraisAnnexesPaye,
                'reste_payer_tenue' => ($restePayerTenue <= 0 && $fraisAnnexesPaye > 0) ? 'Soldé' : ($restePayerTenue > 0 ? number_format($restePayerTenue, 0, ',', ' ') . ' CFA' : 'Soldé'),
                'scolarite_recap' => [
                    'montant_payer' => $scolaritePayer,
                    'montant_paye' => $scolaritePaye,
                    'reste_payer' => $resteScolarite,
                    'prochain_rdv' => ($resteScolarite <= 0) ? 'Soldé' : 'À fixer'
                ]
            ]
        ];
    }

    public function getStats(?string $anneeCode = null): array
    {
        $db = $this->getCon();
        $whereAnnee = "";
        $params = [];
        if (!empty($anneeCode)) {
            $whereAnnee = " AND ai.annee_code = ?";
            $params[] = $anneeCode;
        }

        $baseJoin = " FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " ai 
                      JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = ai.inscription_code 
                      WHERE (ai.statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR ai.statut_accessoire_inscription IS NULL) 
                        AND (i.statut_inscription != '" . STATUTS::ANNULE . "' OR i.statut_inscription IS NULL) ";

        $stmtTot = $db->prepare("SELECT COUNT(*)" . $baseJoin . $whereAnnee);
        $stmtTot->execute($params);
        $total = (int)($stmtTot->fetchColumn() ?: 0);

        $stmtAtt = $db->prepare("SELECT COUNT(*)" . $baseJoin . " AND ai.etat_retrait = 'en_attente'" . $whereAnnee);
        $stmtAtt->execute($params);
        $enAttente = (int)($stmtAtt->fetchColumn() ?: 0);

        $stmtRet = $db->prepare("SELECT COUNT(*)" . $baseJoin . " AND ai.etat_retrait = 'retire'" . $whereAnnee);
        $stmtRet->execute($params);
        $retire = (int)($stmtRet->fetchColumn() ?: 0);

        $totalTypes = (int)$db->query("SELECT COUNT(*) FROM " . TABLES::ACCESSOIRES . " WHERE statut_accessoire = '" . STATUTS::ACTIF . "'")->fetchColumn();
        $taux = $total > 0 ? round(($retire / $total) * 100) : 0;

        return [
            'total' => $total,
            'en_attente' => $enAttente,
            'retires' => $retire,
            'retire' => $retire,
            'taux_retrait' => $taux,
            'taux' => $taux,
            'total_types' => $totalTypes
        ];
    }

    public function getAccessoireInscription(int $id, string $code = ''): ?array
    {
        $db = $this->getCon();
        if ($id > 0) {
            $stmt = $db->prepare("SELECT * FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " WHERE id_accessoire_inscription = ? LIMIT 1");
            $stmt->execute([$id]);
        } else {
            $stmt = $db->prepare("SELECT * FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " WHERE code_accessoire_inscription = ? LIMIT 1");
            $stmt->execute([$code]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateEtatRetrait(int $id, string $newEtat, ?string $dateRetrait): bool
    {
        $stmt = $this->getCon()->prepare("
            UPDATE " . TABLES::ACCESSOIRE_INSCRIPTION . " 
            SET etat_retrait = ?, date_retrait = ? 
            WHERE id_accessoire_inscription = ?
        ");
        return $stmt->execute([$newEtat, $dateRetrait, $id]);
    }

    public function updateAllKitsForInscription(string $inscriptionCode, string $targetEtat, ?string $dateRetrait): bool
    {
        $stmt = $this->getCon()->prepare("
            UPDATE " . TABLES::ACCESSOIRE_INSCRIPTION . " 
            SET etat_retrait = ?, date_retrait = ? 
            WHERE inscription_code = ?
              AND (statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR statut_accessoire_inscription IS NULL)
        ");
        return $stmt->execute([$targetEtat, $dateRetrait, $inscriptionCode]);
    }

    public function getKitsByEtudiant(string $etudiantCode, ?string $anneeCode = null): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT ai.*, a.libelle_accessoire,
                   DATE_FORMAT(ai.date_retrait, '%d/%m/%Y %H:%i') as date_retrait_formatee,
                   DATE_FORMAT(ai.created_at_accessoire_inscription, '%d/%m/%Y') as date_attribution_formatee
            FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " ai
            JOIN " . TABLES::ACCESSOIRES . " a ON a.code_accessoire = ai.accessoire_code
            JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = ai.inscription_code
            WHERE i.etudiant_code = ?
              AND i.statut_inscription != '" . STATUTS::ANNULE . "'
              AND (ai.annee_code = ? OR ? = '')
              AND (ai.statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR ai.statut_accessoire_inscription IS NULL)
            ORDER BY ai.id_accessoire_inscription DESC
        ");
        $stmt->execute([$etudiantCode, $anneeCode ?: '', $anneeCode ?: '']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAllActiveKits(): array
    {
        return $this->getCon()->query("SELECT code_accessoire, libelle_accessoire FROM " . TABLES::ACCESSOIRES . " WHERE statut_accessoire = '" . STATUTS::ACTIF . "' ORDER BY libelle_accessoire ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getLatestInscriptionForStudent(string $etudiantCode): ?array
    {
        $stmt = $this->getCon()->prepare("SELECT code_inscription FROM " . TABLES::INSCRIPTIONS . " WHERE etudiant_code = ? AND statut_inscription != '" . STATUTS::ANNULE . "' ORDER BY id_inscription DESC LIMIT 1");
        $stmt->execute([$etudiantCode]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function checkKitAlreadyAttributed(string $etudiantCode, string $insCode, string $accCode, ?string $anneeCode = null): ?array
    {
        $stmt = $this->getCon()->prepare("
            SELECT ai.id_accessoire_inscription, a.libelle_accessoire 
            FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " ai
            LEFT JOIN " . TABLES::ACCESSOIRES . " a ON a.code_accessoire = ai.accessoire_code
            JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = ai.inscription_code
            WHERE (i.etudiant_code = ? OR ai.inscription_code = ?)
              AND i.statut_inscription != '" . STATUTS::ANNULE . "'
              AND ai.accessoire_code = ? 
              AND (ai.annee_code = ? OR ? = '')
              AND (ai.statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR ai.statut_accessoire_inscription IS NULL)
            LIMIT 1
        ");
        $stmt->execute([$etudiantCode, $insCode, $accCode, $anneeCode ?: '', $anneeCode ?: '']);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function insertAccessoireInscription(array $data): bool
    {
        $stmt = $this->getCon()->prepare("
            INSERT INTO " . TABLES::ACCESSOIRE_INSCRIPTION . "
            (code_accessoire_inscription, inscription_code, accessoire_code, annee_code, statut_accessoire_inscription, etat_retrait, date_retrait, user_code, etablissement_code, created_at_accessoire_inscription)
            VALUES (?, ?, ?, ?, 'actif', ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['code_accessoire_inscription'],
            $data['inscription_code'],
            $data['accessoire_code'],
            $data['annee_code'],
            $data['etat_retrait'] ?? 'en_attente',
            $data['date_retrait'] ?? null,
            $data['user_code'] ?? null,
            $data['etablissement_code'] ?? null
        ]);
    }
}
