<?php

class ModelNote extends BaseModel
{
    protected string $table = TABLES::NOTES;
    protected string $primaryKey = 'id_note';
    protected ?string $statusField = 'statut_note';
    protected ?string $createdAtField = 'created_at_note';

    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null): array
    {
        return $this->getSessionsByClasseCompo($anneeCode, $niveauCode, $classeCode);
    }

    public function getSessionsByClasseCompo(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null, ?string $typeComposition = null, ?string $matiereCode = null, ?string $semestreCode = null, ?string $compositionCode = null, ?array $teacherCodes = null): array
    {
        $db = $this->getCon();
        $sql = "
            SELECT 
                comp.id_composition,
                comp.code_composition,
                comp.libelle_composition,
                comp.type_composition,
                comp.statut_composition,
                comp.semestre_code,
                comp.date_composition,
                comp.created_at_composition,
                s.libelle_semestre,
                s.statut_semestre,
                triplets.matiere_code,
                m.libelle_matiere,
                COALESCE(em.coefficient, comp.coefficient, 1.00) AS coefficient,
                COALESCE(comp.annee_code, ?) AS annee_code,
                cl.code_classe,
                cl.libelle_classe,
                cl.niveau_code,
                cl.filiere_code,
                n.libelle_niveau,
                f.libelle_filiere,
                (
                    SELECT COUNT(DISTINCT ins_c.code_inscription) 
                    FROM " . TABLES::INSCRIPTIONS . " ins_c 
                    WHERE ins_c.classe_code = cl.code_classe 
                      AND (ins_c.statut_inscription != '" . STATUTS::ANNULE . "' OR ins_c.statut_inscription IS NULL)
                      AND (ins_c.annee_code = comp.annee_code OR comp.annee_code IS NULL OR comp.annee_code = '' OR ins_c.annee_code = ?)
                ) AS total_etudiants,
                COUNT(DISTINCT n_entry.id_note) AS nb_notes,
                ROUND(AVG(n_entry.valeur_note), 2) AS moyenne_classe,
                MIN(n_entry.valeur_note) AS note_min,
                MAX(n_entry.valeur_note) AS note_max,
                MAX(n_entry.created_at_note) AS date_enregistrement_note,
                COALESCE(MAX(n_entry.created_at_note), comp.date_composition, comp.created_at_composition) AS date_enregistrement
            FROM (
                -- Source 1: composition_matieres (liaison stricte par cible niveau/filiere)
                SELECT 
                    cm.composition_code,
                    cl.code_classe,
                    cm.matiere_code
                FROM " . TABLES::COMPOSITION_MATIERES . " cm
                INNER JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON cnf.code_composition_niveau_filiere = cm.composition_niveau_filiere_code
                INNER JOIN " . TABLES::CLASSES . " cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
                WHERE cl.code_classe IS NOT NULL AND cm.matiere_code IS NOT NULL AND cm.matiere_code != ''

                UNION

                -- Source 2: Direct classe_code & matiere_code sur compositions
                SELECT 
                    comp.code_composition,
                    comp.classe_code,
                    comp.matiere_code
                FROM " . TABLES::COMPOSITIONS . " comp
                WHERE comp.classe_code IS NOT NULL AND comp.classe_code != ''
                  AND comp.matiere_code IS NOT NULL AND comp.matiere_code != ''

                UNION

                -- Source 3: Via composition_niveau_filiere avec matiere_code sur compositions
                SELECT 
                    cnf.composition_code,
                    cl.code_classe,
                    comp.matiere_code
                FROM " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf
                INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = cnf.composition_code
                INNER JOIN " . TABLES::CLASSES . " cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
                WHERE comp.matiere_code IS NOT NULL AND comp.matiere_code != ''

                UNION

                -- Source 4: Notes existantes
                SELECT 
                    n.composition_code,
                    ins.classe_code,
                    comp.matiere_code
                FROM " . TABLES::NOTES . " n
                INNER JOIN " . TABLES::INSCRIPTIONS . " ins ON ins.code_inscription = n.inscription_code
                INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
                WHERE n.statut_note = '" . STATUTS::ACTIF . "' AND comp.matiere_code IS NOT NULL AND comp.matiere_code != ''

                UNION

                -- Source 5: Matières de la classe via enseignant_matiere si compo sans matière définie
                SELECT 
                    base_pairs.code_composition,
                    base_pairs.classe_code,
                    em.matiere_code
                FROM (
                    SELECT code_composition, classe_code FROM " . TABLES::COMPOSITIONS . " WHERE classe_code IS NOT NULL AND classe_code != ''
                    UNION
                    SELECT cnf.composition_code, cl2.code_classe 
                    FROM " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf
                    INNER JOIN " . TABLES::CLASSES . " cl2 ON (cl2.niveau_code = cnf.niveau_code AND cl2.filiere_code = cnf.filiere_code)
                ) AS base_pairs
                INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = base_pairs.code_composition
                INNER JOIN " . TABLES::ENSEIGNANT_MATIERE . " em ON em.classe_code = base_pairs.classe_code
                WHERE (comp.matiere_code IS NULL OR comp.matiere_code = '')
                  AND comp.type_composition != 'EXAMEN'
                  AND NOT EXISTS (SELECT 1 FROM " . TABLES::COMPOSITION_MATIERES . " cm2 WHERE cm2.composition_code = comp.code_composition)
            ) AS triplets
            INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = triplets.composition_code
            INNER JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = triplets.code_classe
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = triplets.matiere_code
            LEFT JOIN " . TABLES::SEMESTRES . " s ON s.code_semestre = comp.semestre_code
            LEFT JOIN " . TABLES::ENSEIGNANT_MATIERE . " em ON (em.matiere_code = triplets.matiere_code AND em.classe_code = triplets.code_classe)
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
            LEFT JOIN " . TABLES::INSCRIPTIONS . " i ON (i.classe_code = cl.code_classe AND (i.annee_code = comp.annee_code OR comp.annee_code IS NULL OR comp.annee_code = '' OR i.annee_code = ?))
            LEFT JOIN " . TABLES::NOTES . " n_entry ON (n_entry.composition_code = comp.code_composition AND n_entry.inscription_code = i.code_inscription AND n_entry.statut_note = '" . STATUTS::ACTIF . "')
        ";

        $conditions = [];
        $params = [$anneeCode, $anneeCode, $anneeCode];

        // Exclusion des évaluations annulées
        $conditions[] = "(comp.statut_composition IS NULL OR LOWER(TRIM(comp.statut_composition)) != '" . STATUTS::ANNULE . "')";

        if (!empty($anneeCode)) {
            $conditions[] = "(comp.annee_code = ? OR comp.annee_code IS NULL OR comp.annee_code = '')";
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "cl.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "cl.code_classe = ?";
            $params[] = $classeCode;
        }
        if (!empty($typeComposition)) {
            $conditions[] = "comp.type_composition = ?";
            $params[] = $typeComposition;
        }
        if (!empty($matiereCode)) {
            $conditions[] = "triplets.matiere_code = ?";
            $params[] = $matiereCode;
        }
        if (!empty($semestreCode)) {
            $conditions[] = "comp.semestre_code = ?";
            $params[] = $semestreCode;
        }
        if (!empty($compositionCode)) {
            $conditions[] = "comp.code_composition = ?";
            $params[] = $compositionCode;
        }
        if (!empty($teacherCodes)) {
            $inCodes = implode(',', array_fill(0, count($teacherCodes), '?'));
            $conditions[] = "(
                em.enseignant_code IN ($inCodes)
                OR comp.user_code IN ($inCodes)
                OR EXISTS (
                    SELECT 1 FROM " . TABLES::ENSEIGNANT_MATIERE . " em_flt
                    WHERE em_flt.classe_code = triplets.code_classe
                      AND em_flt.matiere_code = triplets.matiere_code
                      AND em_flt.enseignant_code IN ($inCodes)
                      AND (em_flt.statut_enseignant_matiere = '" . STATUTS::ACTIF . "' OR em_flt.statut_enseignant_matiere IS NULL)
                )
                OR EXISTS (
                    SELECT 1 FROM " . TABLES::EMPLOIS_TEMPS . " edt_flt
                    WHERE edt_flt.classe_code = triplets.code_classe
                      AND edt_flt.matiere_code = triplets.matiere_code
                      AND edt_flt.enseignant_code IN ($inCodes)
                )
            )";
            foreach ($teacherCodes as $tc) { $params[] = $tc; }
            foreach ($teacherCodes as $tc) { $params[] = $tc; }
            foreach ($teacherCodes as $tc) { $params[] = $tc; }
            foreach ($teacherCodes as $tc) { $params[] = $tc; }
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " GROUP BY comp.code_composition, cl.code_classe, triplets.matiere_code ORDER BY comp.id_composition DESC, cl.libelle_classe ASC, m.libelle_matiere ASC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelNote::getSessionsByClasseCompo error: " . $e->getMessage());
            return [];
        }
    }

    public function getClassesForTeacher(array $teacherCodes, ?string $anneeCode = null): array
    {
        if (empty($teacherCodes)) {
            return [];
        }
        $db = $this->getCon();
        $inCodes = implode(',', array_fill(0, count($teacherCodes), '?'));
        $sql = "
            SELECT DISTINCT cl.code_classe, cl.libelle_classe, cl.niveau_code, cl.filiere_code
            FROM " . TABLES::CLASSES . " cl
            WHERE (
                EXISTS (
                    SELECT 1 FROM " . TABLES::ENSEIGNANT_MATIERE . " em
                    WHERE em.classe_code = cl.code_classe
                      AND em.enseignant_code IN ($inCodes)
                      AND (em.statut_enseignant_matiere = '" . STATUTS::ACTIF . "' OR em.statut_enseignant_matiere IS NULL)
                      AND (em.annee_code = ? OR ? IS NULL OR ? = '')
                )
                OR EXISTS (
                    SELECT 1 FROM " . TABLES::EMPLOIS_TEMPS . " edt
                    WHERE edt.classe_code = cl.code_classe
                      AND edt.enseignant_code IN ($inCodes)
                      AND (edt.annee_code = ? OR ? IS NULL OR ? = '')
                )
            )
            ORDER BY cl.libelle_classe ASC
        ";
        $params = array_merge($teacherCodes, [$anneeCode, $anneeCode, $anneeCode], $teacherCodes, [$anneeCode, $anneeCode, $anneeCode]);
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelNote::getClassesForTeacher error: " . $e->getMessage());
            return [];
        }
    }

    public function getMatieresForTeacher(array $teacherCodes, ?string $anneeCode = null): array
    {
        if (empty($teacherCodes)) {
            return [];
        }
        $db = $this->getCon();
        $inCodes = implode(',', array_fill(0, count($teacherCodes), '?'));
        $sql = "
            SELECT DISTINCT m.code_matiere, m.libelle_matiere
            FROM " . TABLES::MATIERES . " m
            WHERE (
                EXISTS (
                    SELECT 1 FROM " . TABLES::ENSEIGNANT_MATIERE . " em
                    WHERE em.matiere_code = m.code_matiere
                      AND em.enseignant_code IN ($inCodes)
                      AND (em.statut_enseignant_matiere = '" . STATUTS::ACTIF . "' OR em.statut_enseignant_matiere IS NULL)
                      AND (em.annee_code = ? OR ? IS NULL OR ? = '')
                )
                OR EXISTS (
                    SELECT 1 FROM " . TABLES::EMPLOIS_TEMPS . " edt
                    WHERE edt.matiere_code = m.code_matiere
                      AND edt.enseignant_code IN ($inCodes)
                      AND (edt.annee_code = ? OR ? IS NULL OR ? = '')
                )
            )
            ORDER BY m.libelle_matiere ASC
        ";
        $params = array_merge($teacherCodes, [$anneeCode, $anneeCode, $anneeCode], $teacherCodes, [$anneeCode, $anneeCode, $anneeCode]);
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelNote::getMatieresForTeacher error: " . $e->getMessage());
            return [];
        }
    }

    public function getDetailsClasseCompo(string $classeCode, string $compositionCode, ?string $matiereCode = null, ?string $anneeCode = null): ?array
    {
        $db = $this->getCon();
        try {
            $stmtInfo = $db->prepare("
                SELECT comp.*, s.libelle_semestre, s.statut_semestre, m.libelle_matiere, cl.libelle_classe, n.libelle_niveau, f.libelle_filiere,
                       COALESCE(em.coefficient, comp.coefficient, 1.00) AS coefficient
                FROM " . TABLES::COMPOSITIONS . " comp
                LEFT JOIN " . TABLES::SEMESTRES . " s ON s.code_semestre = comp.semestre_code
                LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = COALESCE(?, comp.matiere_code)
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ?
                LEFT JOIN " . TABLES::ENSEIGNANT_MATIERE . " em ON (em.matiere_code = COALESCE(?, comp.matiere_code) AND em.classe_code = ?)
                LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                WHERE comp.code_composition = ?
            ");
            $stmtInfo->execute([$matiereCode, $classeCode, $matiereCode, $classeCode, $compositionCode]);
            $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
            if (!$info) return null;

            if (!empty($matiereCode)) {
                $info['matiere_code'] = $matiereCode;
            }

            $stmtStudents = $db->prepare("
                SELECT 
                    i.code_inscription,
                    e.code_etudiant,
                    e.matricule_etudiant,
                    CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) AS nom_etudiant,
                    n.id_note,
                    n.valeur_note,
                    n.observations,
                    n.created_at_note,
                    n.statut_note
                FROM " . TABLES::INSCRIPTIONS . " i
                INNER JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
                LEFT JOIN " . TABLES::NOTES . " n ON (n.composition_code = ? AND n.inscription_code = i.code_inscription AND n.statut_note = '" . STATUTS::ACTIF . "')
                WHERE i.classe_code = ? 
                  AND (i.statut_inscription != '" . STATUTS::ANNULE . "' OR i.statut_inscription IS NULL)
                  AND (i.annee_code = ? OR ? IS NULL OR ? = '')
                ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
            ");
            $stmtStudents->execute([$compositionCode, $classeCode, $anneeCode, $anneeCode, $anneeCode]);
            $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $notesVals = [];
            $admisCount = 0;
            foreach ($students as &$s) {
                if ($s['valeur_note'] !== null && $s['valeur_note'] !== '') {
                    $v = (float)$s['valeur_note'];
                    $notesVals[] = $v;
                    if ($v >= 10) $admisCount++;
                    
                    if ($v >= 16) $s['mention'] = 'Très Bien';
                    elseif ($v >= 14) $s['mention'] = 'Bien';
                    elseif ($v >= 12) $s['mention'] = 'Assez Bien';
                    elseif ($v >= 10) $s['mention'] = 'Passable';
                    elseif ($v >= 8)  $s['mention'] = 'Insuffisant';
                    else $s['mention'] = 'Très Insuffisant';
                } else {
                    $s['mention'] = 'Non noté';
                }
            }
            unset($s);

            $nbNotes = count($notesVals);
            $moyenne = $nbNotes > 0 ? round(array_sum($notesVals) / $nbNotes, 2) : null;
            $noteMin = $nbNotes > 0 ? min($notesVals) : null;
            $noteMax = $nbNotes > 0 ? max($notesVals) : null;
            $tauxReussite = $nbNotes > 0 ? round(($admisCount / $nbNotes) * 100, 1) : 0;

            // Calcul des rangs avec gestion des ex-æquo
            $notesMap = [];
            foreach ($students as $st) {
                if ($st['valeur_note'] !== null && $st['valeur_note'] !== '') {
                    $notesMap[$st['code_inscription']] = (float)$st['valeur_note'];
                }
            }
            arsort($notesMap);

            $noteCounts = [];
            foreach ($notesMap as $v) {
                $k = (string)round($v, 2);
                $noteCounts[$k] = ($noteCounts[$k] ?? 0) + 1;
            }

            $ranksMap = [];
            $pos = 1;
            $prevVal = null;
            $currentRankDisplay = '';
            foreach ($notesMap as $inscCode => $v) {
                $k = (string)round($v, 2);
                $isEx = ($noteCounts[$k] > 1);
                if ($prevVal === null || round($v, 2) !== round($prevVal, 2)) {
                    $rankNum = $pos;
                    $suffix = ($rankNum === 1) ? 'er' : 'e';
                    $currentRankDisplay = $rankNum . '<sup>' . $suffix . '</sup>' . ($isEx ? ' ex' : '');
                    $prevVal = $v;
                }
                $ranksMap[$inscCode] = [
                    'rank_num' => $pos,
                    'display' => $currentRankDisplay
                ];
                $pos++;
            }

            foreach ($students as &$s) {
                if (isset($ranksMap[$s['code_inscription']])) {
                    $s['rang'] = $ranksMap[$s['code_inscription']]['display'];
                    $s['rang_num'] = $ranksMap[$s['code_inscription']]['rank_num'];
                } else {
                    $s['rang'] = '-';
                    $s['rang_num'] = 999999;
                }
            }
            unset($s);

            return [
                'info' => $info,
                'students' => $students,
                'stats' => [
                    'total_etudiants' => count($students),
                    'nb_notes' => $nbNotes,
                    'moyenne_classe' => $moyenne,
                    'note_min' => $noteMin,
                    'note_max' => $noteMax,
                    'admis_count' => $admisCount,
                    'taux_reussite' => $tauxReussite
                ]
            ];
        } catch (Exception $e) {
            error_log("ModelNote::getDetailsClasseCompo error: " . $e->getMessage());
            return null;
        }
    }

    public function getFormDataDependencies(?string $anneeCode = null): array
    {
        require_once __DIR__ . '/../niveaux/ModelNiveau.php';
        require_once __DIR__ . '/../classes/ModelClasse.php';
        require_once __DIR__ . '/../matieres/ModelMatiere.php';
        require_once __DIR__ . '/../semestres/ModelSemestre.php';

        $niveaux = (new ModelNiveau())->getAll();
        $classes = (new ModelClasse())->getAll();
        $matieres = (new ModelMatiere())->getAll();
        $semestres = (new ModelSemestre())->getAll($anneeCode);

        $sql = "
            SELECT i.code_inscription, i.classe_code,
                   CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) AS etudiant_nom,
                   e.matricule_etudiant,
                   cl.libelle_classe,
                   cl.niveau_code
            FROM " . TABLES::INSCRIPTIONS . " i
            LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
            ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC
        ";
        $inscriptionsDetails = $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'niveaux' => $niveaux,
            'classes' => $classes,
            'matieres' => $matieres,
            'semestres' => $semestres,
            'inscriptionsDetails' => $inscriptionsDetails
        ];
    }

    public function getByIdWithDetails($id): ?array
    {
        $stmt = $this->getCon()->prepare("
            SELECT vn.*,
                   vn.etu_nom AS nom_etudiant,
                   vn.etu_prenom AS prenom_etudiant,
                   vn.composition_classe_code AS classe_code,
                   COALESCE(em.coefficient, vn.coefficient, 1.0) AS coef_cours
            FROM " . VIEWS::NOTES_COMPLETES . " vn
            LEFT JOIN " . TABLES::ENSEIGNANT_MATIERE . " em ON (em.classe_code = vn.code_classe AND em.matiere_code = vn.matiere_code)
            WHERE vn.id_note = ? OR vn.code_note = ?
            LIMIT 1
        ");
        $stmt->execute([$id, (string)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMatieresForClasse(string $classeCode, string $anneeCode, string $compositionCode = ''): array
    {
        if (empty($classeCode)) return [];
        $db = $this->getCon();

        $dedup = function(array $list): array {
            $unique = [];
            foreach ($list as $item) {
                $c = $item['code_matiere'] ?? '';
                if (!empty($c) && !isset($unique[$c])) {
                    $unique[$c] = $item;
                }
            }
            return array_values($unique);
        };

        // 1. Si une composition est spécifiée
        if (!empty($compositionCode)) {
            $stmtCompMat = $db->prepare("
                SELECT DISTINCT m.code_matiere, m.libelle_matiere, 
                       COALESCE(MAX(em.coefficient), MAX(comp.coefficient), 1.00) AS coefficient,
                       TRIM(GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ')) AS prof_nom,
                       1 AS is_assigned
                FROM " . TABLES::COMPOSITION_MATIERES . " cm
                INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = cm.composition_code
                INNER JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON cnf.code_composition_niveau_filiere = cm.composition_niveau_filiere_code
                INNER JOIN " . TABLES::CLASSES . " cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
                INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = cm.matiere_code
                LEFT JOIN " . TABLES::ENSEIGNANT_MATIERE . " em ON (em.matiere_code = m.code_matiere AND em.classe_code = cl.code_classe)
                LEFT JOIN " . TABLES::EMPLOIS_TEMPS . " edt ON (edt.classe_code = cl.code_classe AND edt.matiere_code = m.code_matiere)
                LEFT JOIN " . TABLES::USERS . " u ON (u.code_user = em.enseignant_code OR u.code_user = edt.enseignant_code)
                WHERE cm.composition_code = ? AND cl.code_classe = ?
                GROUP BY m.code_matiere, m.libelle_matiere
                ORDER BY m.libelle_matiere ASC
            ");
            $stmtCompMat->execute([$compositionCode, $classeCode]);
            $compItems = $stmtCompMat->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($compItems)) {
                return $dedup($compItems);
            }
        }

        // 2. Matières selon l'emploi du temps
        $stmtEdt = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   COALESCE(MAX(em.coefficient), 1.00) AS coefficient,
                   TRIM(GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ')) AS prof_nom,
                   1 AS is_assigned
            FROM " . TABLES::EMPLOIS_TEMPS . " edt
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
            LEFT JOIN " . TABLES::ENSEIGNANT_MATIERE . " em ON (em.classe_code = edt.classe_code AND em.matiere_code = edt.matiere_code)
            LEFT JOIN " . TABLES::USERS . " u ON (u.code_user = edt.enseignant_code OR u.code_user = em.enseignant_code)
            WHERE edt.classe_code = ? 
              AND (edt.annee_code = ? OR ? IS NULL OR ? = '')
              AND (edt.statut_emploi = '" . STATUTS::ACTIF . "' OR edt.statut_emploi IS NULL)
            GROUP BY m.code_matiere, m.libelle_matiere
            ORDER BY m.libelle_matiere ASC
        ");
            $stmtEdt->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        $edtItems = $stmtEdt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($edtItems)) {
            return $dedup($edtItems);
        }

        // 3. Matières affectées à la classe via enseignant_matiere
        $stmtEm = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   COALESCE(MAX(em.coefficient), 1.00) AS coefficient,
                   TRIM(GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ')) AS prof_nom,
                   1 AS is_assigned
            FROM " . TABLES::ENSEIGNANT_MATIERE . " em
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = em.matiere_code
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = em.enseignant_code
            WHERE em.classe_code = ?
              AND (em.annee_code = ? OR ? IS NULL OR ? = '')
              AND (em.statut_enseignant_matiere = '" . STATUTS::ACTIF . "' OR em.statut_enseignant_matiere IS NULL)
            GROUP BY m.code_matiere, m.libelle_matiere
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtEm->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        $emItems = $stmtEm->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($emItems)) {
            return $dedup($emItems);
        }

        // 4. Matières liées au Niveau & Filière (composition_niveau_filiere)
        $stmtCnf = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   COALESCE(MAX(comp.coefficient), 1.00) AS coefficient,
                   '' AS prof_nom,
                   1 AS is_assigned
            FROM " . TABLES::COMPOSITION_MATIERES . " cm
            INNER JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON cnf.code_composition_niveau_filiere = cm.composition_niveau_filiere_code
            INNER JOIN " . TABLES::CLASSES . " cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = cm.matiere_code
            INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = cm.composition_code
            WHERE cl.code_classe = ?
              AND (comp.annee_code = ? OR ? IS NULL OR ? = '')
            GROUP BY m.code_matiere, m.libelle_matiere
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtCnf->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        $cnfItems = $stmtCnf->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($cnfItems)) {
            return $dedup($cnfItems);
        }

        // 5. Repli : Catalogue des matières actives
        return $dedup($this->getActiveMatieresCatalogue());
    }

    public function getActiveMatieresCatalogue(): array
    {
        $stmtAll = $this->getCon()->query("
            SELECT code_matiere, libelle_matiere, 1.00 AS coefficient, '' AS prof_nom, 0 AS is_assigned
            FROM " . TABLES::MATIERES . " 
            WHERE statut_matiere = '" . STATUTS::ACTIF . "'
            ORDER BY libelle_matiere ASC
        ");
        return $stmtAll->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStudentsByClasse(string $classeCode, string $anneeCode): array
    {
        $db = $this->getCon();
        $stmt = $db->prepare("
            SELECT i.id_inscription, i.code_inscription, e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
            FROM " . TABLES::INSCRIPTIONS . " i
            INNER JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
            WHERE i.classe_code = ? AND (i.annee_code = ? OR ? IS NULL OR ? = '') AND (i.statut_inscription != '" . STATUTS::ANNULE . "' OR i.statut_inscription IS NULL)
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
        ");
        $stmt->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getNotesByComposition(string $compositionCode): array
    {
        $db = $this->getCon();
        $sqlNotes = "SELECT n.*, n.inscription_code 
                     FROM " . TABLES::NOTES . " n
                     WHERE n.composition_code = ? AND n.statut_note = '" . STATUTS::ACTIF . "'";
        $stmtNotes = $db->prepare($sqlNotes);
        $stmtNotes->execute([$compositionCode]);
        $rows = $stmtNotes->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $res = [];
        foreach ($rows as $r) {
            $res[$r['inscription_code']] = $r;
        }
        return $res;
    }

    public function getEtudiantCodeByInscription(string $inscriptionCode): ?string
    {
        $stmt = $this->getCon()->prepare("SELECT etudiant_code FROM " . TABLES::INSCRIPTIONS . " WHERE code_inscription = ? LIMIT 1");
        $stmt->execute([$inscriptionCode]);
        return $stmt->fetchColumn() ?: null;
    }

    public function createQuickComposition(
        string $typeEval,
        string $semestreCode,
        string $classeCode,
        string $matiereCode,
        float $coef = 1.00,
        ?string $libelleEval = null,
        string $anneeCode = '',
        string $etabCode = '',
        string $userCode = ''
    ): string {
        $db = $this->getCon();
        $validator = new Validator();
        $compositionCode = $validator->generateCode(TABLES::COMPOSITIONS, 'code_composition', 'CMP-', 8);
        $defaultLbl = ($typeEval === 'DEVOIR') ? 'Devoir Sur Table' : (($typeEval === 'INTERROGATION') ? 'Interrogation' : 'Évaluation');
        $finalLibelle = !empty($libelleEval) ? $libelleEval : $defaultLbl;
        $today = date('Y-m-d');

        $stmtNewComp = $db->prepare("
            INSERT INTO " . TABLES::COMPOSITIONS . " (
                code_composition, libelle_composition, type_composition, semestre_code, classe_code, matiere_code, coefficient,
                date_composition, date_fin_composition, statut_composition, annee_code,
                etablissement_code, user_code, created_at_composition
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'programme', ?, ?, ?, NOW())
        ");
        $stmtNewComp->execute([
            $compositionCode,
            $finalLibelle,
            $typeEval,
            $semestreCode,
            $classeCode,
            $matiereCode,
            $coef,
            $today,
            $today,
            $anneeCode,
            $etabCode,
            $userCode
        ]);

        if (!empty($classeCode)) {
            $stmtCl = $db->prepare("SELECT niveau_code, filiere_code FROM " . TABLES::CLASSES . " WHERE code_classe = ?");
            $stmtCl->execute([$classeCode]);
            $clRow = $stmtCl->fetch(PDO::FETCH_ASSOC);

            if ($clRow && !empty($clRow['niveau_code']) && !empty($clRow['filiere_code'])) {
                $codeCNF = $validator->generateCode(TABLES::COMPOSITION_NIVEAU_FILIERE, 'code_composition_niveau_filiere', 'CNF-', 8);
                $stmtInsCNF = $db->prepare("
                    INSERT IGNORE INTO " . TABLES::COMPOSITION_NIVEAU_FILIERE . " (code_composition_niveau_filiere, composition_code, niveau_code, filiere_code)
                    VALUES (?, ?, ?, ?)
                ");
                $stmtInsCNF->execute([$codeCNF, $compositionCode, $clRow['niveau_code'], $clRow['filiere_code']]);

                $stmtInsCM = $db->prepare("
                    INSERT IGNORE INTO " . TABLES::COMPOSITION_MATIERES . " (composition_code, composition_niveau_filiere_code, matiere_code)
                    VALUES (?, ?, ?)
                ");
                $stmtInsCM->execute([$compositionCode, $codeCNF, $matiereCode]);
            }
        }

        return $compositionCode;
    }

    public function updateQuickComposition(string $compositionCode, ?string $libelleEval, float $coef, ?string $classeCode, ?string $matiereCode): bool
    {
        $stmtUpdComp = $this->getCon()->prepare("
            UPDATE " . TABLES::COMPOSITIONS . " 
            SET libelle_composition = COALESCE(NULLIF(?, ''), libelle_composition),
                coefficient = ?,
                classe_code = COALESCE(classe_code, ?),
                matiere_code = COALESCE(matiere_code, ?),
                updated_at_composition = NOW()
            WHERE code_composition = ?
        ");
        return $stmtUpdComp->execute([$libelleEval, $coef, $classeCode, $matiereCode, $compositionCode]);
    }

    public function saveBatchNotes(
        string $classeCode,
        string $matiereCode,
        string $semestreCode,
        string $typeEval,
        ?string $compositionCode,
        string $libelleEval,
        float $coefEval,
        array $notesData,
        string $userCode,
        string $anneeCode,
        string $etabCode
    ): array {
        $db = $this->getCon();
        $validator = new Validator();
        $db->beginTransaction();

        try {
            if ($typeEval !== 'EXAMEN') {
                if (empty($compositionCode)) {
                    $compositionCode = $this->createQuickComposition(
                        $typeEval, $semestreCode, $classeCode, $matiereCode, $coefEval,
                        $libelleEval, $anneeCode, $etabCode, $userCode
                    );
                } else {
                    $this->updateQuickComposition($compositionCode, $libelleEval, $coefEval, $classeCode, $matiereCode);
                }
            }

            $savedCount = 0;
            $deletedCount = 0;

            // Pré-chargement en 1 seule requête des notes existantes pour cette composition
            $existingNotesMap = [];
            $stmtExistAll = $db->prepare("SELECT inscription_code, id_note FROM " . TABLES::NOTES . " WHERE composition_code = ?");
            $stmtExistAll->execute([$compositionCode]);
            foreach ($stmtExistAll->fetchAll(PDO::FETCH_ASSOC) as $enRow) {
                $existingNotesMap[$enRow['inscription_code']] = $enRow['id_note'];
            }

            // Pré-chargement en 1 seule requête des codes étudiants de la classe
            $etudiantsByInsc = [];
            if (!empty($classeCode)) {
                foreach ($this->getStudentsByClasse($classeCode, $anneeCode) as $st) {
                    $etudiantsByInsc[$st['code_inscription']] = $st['code_etudiant'];
                }
            }

            $stmtDel = $db->prepare("DELETE FROM " . TABLES::NOTES . " WHERE id_note = ?");
            $stmtUpd = $db->prepare("
                UPDATE " . TABLES::NOTES . " 
                SET valeur_note = ?, observations = ?, updated_at_note = NOW(), statut_note = '" . STATUTS::ACTIF . "'
                WHERE id_note = ?
            ");
            $stmtIns = $db->prepare("
                INSERT INTO " . TABLES::NOTES . " (code_note, composition_code, etudiant_code, inscription_code, valeur_note, observations, statut_note, user_code, etablissement_code, created_at_note)
                VALUES (?, ?, ?, ?, ?, ?, 'actif', ?, ?, ?)
            ");
            $nowStr = date('Y-m-d H:i:s');

            foreach ($notesData as $inscCode => $info) {
                $valNoteStr = trim($info['valeur_note'] ?? '');
                $obs = trim($info['observations'] ?? '');

                $existingId = $existingNotesMap[$inscCode] ?? false;

                if ($valNoteStr === '') {
                    if (!empty($obs) && stripos($obs, 'absent') !== false) {
                        $valNote = 0.00;
                    } else {
                        if ($existingId) {
                            $stmtDel->execute([$existingId]);
                            $deletedCount++;
                        }
                        continue;
                    }
                } else {
                    $valNote = (float)str_replace(',', '.', $valNoteStr);
                    if ($valNote < 0 || $valNote > 20) {
                        throw new Exception("La note doit être comprise entre 0 et 20.");
                    }
                }

                if ($existingId) {
                    $stmtUpd->execute([$valNote, $obs, $existingId]);
                } else {
                    $etudiantCode = trim($info['etudiant_code'] ?? '');
                    if (empty($etudiantCode)) {
                        $etudiantCode = $etudiantsByInsc[$inscCode]
                            ?? ($this->getEtudiantCodeByInscription($inscCode) ?: '');
                    }

                    $fkErr = ForeignKeyValidator::validate($db, TABLES::NOTES, [
                        'inscription_code' => $inscCode,
                        'composition_code' => $compositionCode,
                        'etudiant_code' => $etudiantCode,
                        'etablissement_code' => $etabCode
                    ]);
                    if ($fkErr !== null) {
                        throw new Exception($fkErr);
                    }

                    $codeNote = $validator->generateCode(TABLES::NOTES, 'code_note', 'NOT-', 8);
                    $stmtIns->execute([$codeNote, $compositionCode, $etudiantCode, $inscCode, $valNote, $obs, $userCode, $etabCode, $nowStr]);
                }
                $savedCount++;
            }

            $db->commit();
            return [
                'saved_count' => $savedCount,
                'deleted_count' => $deletedCount,
                'composition_code' => $compositionCode
            ];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
