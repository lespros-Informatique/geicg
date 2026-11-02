<?php

class ModelBulletin extends BaseModel
{
    protected string $table = TABLES::INSCRIPTIONS;
    protected string $primaryKey = 'id_inscription';
    protected ?string $statusField = 'statut_inscription';
    protected ?string $createdAtField = 'created_at_inscription';

    public function getActiveSemestres(?string $anneeCode = null): array
    {
        try {
            $sql = "SELECT * FROM " . TABLES::SEMESTRES;
            $params = [];
            if (!empty($anneeCode)) {
                $sql .= " WHERE (annee_code = ? OR annee_code IS NULL OR annee_code = '')";
                $params[] = $anneeCode;
            }
            $sql .= " ORDER BY id_semestre ASC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelBulletin::getActiveSemestres error: " . $e->getMessage());
            return [];
        }
    }

    public function getInscriptionsWithNotes(
        ?string $anneeCode = null,
        ?string $niveauCode = null,
        ?string $classeCode = null,
        ?string $semestreCode = null,
        ?array $teacherClasses = null,
        ?string $statutNotes = null
    ): array {
        try {
            $subParams = [];
            $subWhere = [
                "n.statut_note = '" . STATUTS::ACTIF . "'",
                "(comp.statut_composition IS NULL OR LOWER(TRIM(comp.statut_composition)) != '" . STATUTS::ANNULE . "')"
            ];

            if (!empty($semestreCode)) {
                $subWhere[] = "comp.semestre_code = ?";
                $subParams[] = $semestreCode;
            }
            if (!empty($anneeCode)) {
                $subWhere[] = "(comp.annee_code = ? OR comp.annee_code IS NULL OR comp.annee_code = '')";
                $subParams[] = $anneeCode;
            }

            $subWhereSql = implode(" AND ", $subWhere);

            $sql = "SELECT i.*, 
                           CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) AS etudiant_nom,
                           e.matricule_etudiant,
                           e.sexe_etudiant,
                           e.photo_etudiant,
                           cl.libelle_classe AS classe_nom,
                           cl.code_classe,
                           n.libelle_niveau AS niveau_nom,
                           f.libelle_filiere AS filiere_nom,
                           a.libelle_annee AS annee_nom,
                           COALESCE(n_cnt.nb_notes, 0) AS nb_notes,
                           n_cnt.moyenne_semestre
                    FROM " . TABLES::INSCRIPTIONS . " i
                    INNER JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
                    LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
                    LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
                    LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
                    LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
                    LEFT JOIN (
                        SELECT 
                            n.inscription_code, 
                            COUNT(DISTINCT n.id_note) AS nb_notes,
                            ROUND(SUM(n.valeur_note * COALESCE(comp.coefficient, 1.00)) / NULLIF(SUM(COALESCE(comp.coefficient, 1.00)), 0), 2) AS moyenne_semestre
                        FROM " . TABLES::NOTES . " n
                        INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
                        WHERE {$subWhereSql}
                        GROUP BY n.inscription_code
                    ) n_cnt ON n_cnt.inscription_code = i.code_inscription";

            $conditions = [
                "(i.statut_inscription != '" . STATUTS::ANNULE . "' OR i.statut_inscription IS NULL)"
            ];
            $params = $subParams;

            if (!empty($anneeCode)) {
                $conditions[] = "(i.annee_code = ? OR i.annee_code IS NULL OR i.annee_code = '')";
                $params[] = $anneeCode;
            }
            if (!empty($niveauCode)) {
                $conditions[] = "cl.niveau_code = ?";
                $params[] = $niveauCode;
            }
            if (!empty($classeCode)) {
                $conditions[] = "i.classe_code = ?";
                $params[] = $classeCode;
            }
            if (!empty($teacherClasses)) {
                $inClasses = implode(',', array_fill(0, count($teacherClasses), '?'));
                $conditions[] = "i.classe_code IN ($inClasses)";
                foreach ($teacherClasses as $tc) {
                    $params[] = $tc;
                }
            }
            if (!empty($statutNotes)) {
                if ($statutNotes === 'avec_notes') {
                    $conditions[] = "COALESCE(n_cnt.nb_notes, 0) > 0";
                } elseif ($statutNotes === 'sans_notes') {
                    $conditions[] = "COALESCE(n_cnt.nb_notes, 0) = 0";
                }
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC, e.prenom_etudiant ASC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelBulletin::getInscriptionsWithNotes error: " . $e->getMessage());
            return [];
        }
    }

    public function calculateBulletinData(int $id, ?string $selectedSemestreCode = null): ?array
    {
        $db = $this->getCon();
        $stmt = $db->prepare("SELECT * FROM " . VIEWS::INSCRIPTIONS_COMPLETES . " WHERE id_inscription = ? LIMIT 1");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            return null;
        }

        $semestres = $this->getActiveSemestres();

        if (empty($selectedSemestreCode)) {
            $stmtCheck = $db->prepare("
                SELECT comp.semestre_code FROM " . TABLES::NOTES . " n 
                INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
                WHERE n.inscription_code = ? AND n.statut_note = '" . STATUTS::ACTIF . "' 
                LIMIT 1
            ");
            $stmtCheck->execute([$item['code_inscription']]);
            $foundSem = $stmtCheck->fetchColumn();
            $selectedSemestreCode = $foundSem ?: ($semestres[0]['code_semestre'] ?? '');
        }

        // Requête ultra-allégée via la vue optimisée VIEWS::BULLETIN_NOTES_DETAILS
        $sqlNotes = "
            SELECT *
            FROM " . VIEWS::BULLETIN_NOTES_DETAILS . "
            WHERE inscription_code = ? AND semestre_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
              AND (statut_composition IS NULL OR LOWER(TRIM(statut_composition)) != '" . STATUTS::ANNULE . "')
            ORDER BY CASE WHEN categorie_matiere = '" . CATEGORIES_MATIERES::GENERALE . "' THEN 1 ELSE 2 END ASC, libelle_matiere ASC, type_evaluation_code ASC
        ";
        $stmtNotes = $db->prepare($sqlNotes);
        $stmtNotes->execute([$item['code_inscription'], $selectedSemestreCode]);
        $rawNotes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

        $matieresNotes = [];
        foreach ($rawNotes as $n) {
            $matCode = $n['matiere_code'] ?: 'MAT_INCONNUE';
            $coefComp = (float)($n['coefficient_composition'] ?? 1);
            if ($coefComp <= 0) $coefComp = 1.0;
            $valNote = (float)$n['valeur_note'];

            if (!isset($matieresNotes[$matCode])) {
                $matieresNotes[$matCode] = [
                    'matiere_code' => $matCode,
                    'libelle' => $n['libelle_matiere'] ?: 'Matière sans nom',
                    'coefficient' => (float)($n['coefficient_maquette'] ?? 1),
                    'categorie_matiere' => strtoupper($n['categorie_matiere'] ?? CATEGORIES_MATIERES::GENERALE),
                    'prof' => trim($n['nom_enseignant'] ?? ($n['enseignant'] ?? '')) ?: 'Non assigné',
                    'enseignant' => trim($n['enseignant'] ?? ($n['nom_enseignant'] ?? '')) ?: 'Non assigné',
                    'evaluations' => [],
                    'sum_notes_pond' => 0.0,
                    'sum_coef_comp' => 0.0
                ];
            }
            $matieresNotes[$matCode]['evaluations'][] = [
                'type' => $n['type_evaluation_code'],
                'note' => $valNote,
                'coefficient' => $coefComp,
                'observations' => $n['observations'] ?? ''
            ];
            $matieresNotes[$matCode]['sum_notes_pond'] += ($valNote * $coefComp);
            $matieresNotes[$matCode]['sum_coef_comp'] += $coefComp;
        }

        $totalPoints = 0;
        $totalCoefficients = 0;
        foreach ($matieresNotes as $k => &$m) {
            // 1. Moyenne de la matière pondérée par les coefficients de chaque épreuve/composition
            $moyenneMatiere = $m['sum_coef_comp'] > 0 ? ($m['sum_notes_pond'] / $m['sum_coef_comp']) : 0;
            $m['moyenne'] = round($moyenneMatiere, 2);

            // 2. Points de la matière = Moyenne matière x Coefficient de la Maquette Pédagogique
            $m['points'] = round($moyenneMatiere * $m['coefficient'], 2);

            if ($m['moyenne'] >= 16) $m['appreciation'] = 'Très Bien';
            elseif ($m['moyenne'] >= 14) $m['appreciation'] = 'Bien';
            elseif ($m['moyenne'] >= 12) $m['appreciation'] = 'Assez Bien';
            elseif ($m['moyenne'] >= 10) $m['appreciation'] = 'Passable';
            elseif ($m['moyenne'] >= 8)  $m['appreciation'] = 'Insuffisant';
            else $m['appreciation'] = 'Très Insuffisant';

            $m['rang_matiere'] = '-';
            $totalPoints += $m['points'];
            $totalCoefficients += $m['coefficient'];
        }
        unset($m);

        $moyenneGenerale = $totalCoefficients > 0 ? round($totalPoints / $totalCoefficients, 2) : 0;

        $mention = 'Non évalué';
        $decision = 'En attente';
        if ($totalCoefficients > 0) {
            if ($moyenneGenerale >= 16) {
                $mention = 'Très Bien';
                $decision = 'Félicitations du Conseil de Faculté / Jury';
            } elseif ($moyenneGenerale >= 14) {
                $mention = 'Bien';
                $decision = 'Tableau d\'Honneur';
            } elseif ($moyenneGenerale >= 12) {
                $mention = 'Assez Bien';
                $decision = 'Encouragements';
            } elseif ($moyenneGenerale >= 10) {
                $mention = 'Passable';
                $decision = 'Admis(e) au semestre';
            } else {
                $mention = 'Ajourné(e)';
                $decision = 'Non validé / Session de rattrapage';
            }
        }

        $sexeEleve = strtoupper($item['sexe_etudiant'] ?? 'M');
        $rang = 1;
        $rangFr = '1er';
        $rangGeneralesFr = '1er';
        $rangProFr = '1er';
        $totalElevesClasse = 1;
        $moyenneMinClasse = $moyenneGenerale;
        $moyenneMaxClasse = $moyenneGenerale;
        $moyenneMoyClasse = $moyenneGenerale;

        if (!empty($item['classe_code']) && !empty($selectedSemestreCode)) {
            // Requête classe allégée via VIEWS::BULLETIN_NOTES_DETAILS
            $sqlClassmates = "
                SELECT inscription_code AS code_inscription,
                       matiere_code,
                       categorie_matiere,
                       valeur_note,
                       coefficient_composition,
                       coefficient_maquette
                FROM " . VIEWS::BULLETIN_NOTES_DETAILS . "
                WHERE classe_code = ? AND semestre_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
                  AND (statut_composition IS NULL OR LOWER(TRIM(statut_composition)) != '" . STATUTS::ANNULE . "')
            ";
            $stmtClass = $db->prepare($sqlClassmates);
            $stmtClass->execute([$item['classe_code'], $selectedSemestreCode]);
            $classNotes = $stmtClass->fetchAll(PDO::FETCH_ASSOC);

            // 1. Agrégation par étudiant & par matière (Pondérée par composition)
            $etudiantsNotesByMatiere = []; // [insc][matCode] = ['sum_notes_pond' => 0, 'sum_coef_comp' => 0, 'coefficient_maquette' => X, 'categorie' => Y]
            foreach ($classNotes as $cn) {
                $insc = $cn['code_inscription'];
                $mat = $cn['matiere_code'] ?: 'MAT_INCONNUE';
                $coefComp = (float)($cn['coefficient_composition'] ?? 1);
                if ($coefComp <= 0) $coefComp = 1.0;
                $valNote = (float)$cn['valeur_note'];

                if (!isset($etudiantsNotesByMatiere[$insc])) {
                    $etudiantsNotesByMatiere[$insc] = [];
                }
                if (!isset($etudiantsNotesByMatiere[$insc][$mat])) {
                    $etudiantsNotesByMatiere[$insc][$mat] = [
                        'sum_notes_pond' => 0.0,
                        'sum_coef_comp' => 0.0,
                        'coefficient_maquette' => (float)$cn['coefficient_maquette'],
                        'categorie' => strtoupper($cn['categorie_matiere'] ?? CATEGORIES_MATIERES::GENERALE)
                    ];
                }
                $etudiantsNotesByMatiere[$insc][$mat]['sum_notes_pond'] += ($valNote * $coefComp);
                $etudiantsNotesByMatiere[$insc][$mat]['sum_coef_comp'] += $coefComp;
            }

            // 2. Moyennes globales et par catégorie pour chaque étudiant
            $classeMoyennesGlobales = [];
            $classeMoyennesGenerales = [];
            $classeMoyennesPro = [];
            $classeMoyennesParMatiere = []; // [matCode][insc] = moy

            foreach ($etudiantsNotesByMatiere as $insc => $mats) {
                $ptsGlob = 0; $coefGlob = 0;
                $ptsGen = 0;  $coefGen = 0;
                $ptsPro = 0;  $coefPro = 0;

                foreach ($mats as $matCode => $info) {
                    $cMaquette = $info['coefficient_maquette'];
                    $mVal = $info['sum_coef_comp'] > 0 ? ($info['sum_notes_pond'] / $info['sum_coef_comp']) : 0;

                    $ptsGlob += ($mVal * $cMaquette);
                    $coefGlob += $cMaquette;

                    if ($info['categorie'] === CATEGORIES_MATIERES::GENERALE) {
                        $ptsGen += ($mVal * $cMaquette);
                        $coefGen += $cMaquette;
                    } else {
                        $ptsPro += ($mVal * $cMaquette);
                        $coefPro += $cMaquette;
                    }

                    if (!isset($classeMoyennesParMatiere[$matCode])) {
                        $classeMoyennesParMatiere[$matCode] = [];
                    }
                    $classeMoyennesParMatiere[$matCode][$insc] = round($mVal, 2);
                }

                if ($coefGlob > 0) $classeMoyennesGlobales[$insc] = round($ptsGlob / $coefGlob, 2);
                if ($coefGen > 0)  $classeMoyennesGenerales[$insc] = round($ptsGen / $coefGen, 2);
                if ($coefPro > 0)  $classeMoyennesPro[$insc] = round($ptsPro / $coefPro, 2);
            }

            // 3. Calcul du rang général
            if (!empty($classeMoyennesGlobales)) {
                arsort($classeMoyennesGlobales);
                $totalElevesClasse = count($classeMoyennesGlobales);
                $moyenneMaxClasse = max($classeMoyennesGlobales);
                $moyenneMinClasse = min($classeMoyennesGlobales);
                $moyenneMoyClasse = round(array_sum($classeMoyennesGlobales) / $totalElevesClasse, 2);

                $pos = 1;
                foreach ($classeMoyennesGlobales as $inscCode => $moyVal) {
                    if ($inscCode === $item['code_inscription']) {
                        $rang = $pos;
                        $rangFr = ($pos === 1) ? (($sexeEleve === 'F') ? '1ère' : '1er') : ($pos . 'ème');
                        break;
                    }
                    $pos++;
                }
            }

            // 4. Calcul du rang dans les matières générales
            if (!empty($classeMoyennesGenerales)) {
                arsort($classeMoyennesGenerales);
                $pos = 1;
                foreach ($classeMoyennesGenerales as $inscCode => $moyVal) {
                    if ($inscCode === $item['code_inscription']) {
                        $rangGeneralesFr = ($pos === 1) ? (($sexeEleve === 'F') ? '1ère' : '1er') : ($pos . 'ème');
                        break;
                    }
                    $pos++;
                }
            }

            // 5. Calcul du rang dans les matières professionnelles
            if (!empty($classeMoyennesPro)) {
                arsort($classeMoyennesPro);
                $pos = 1;
                foreach ($classeMoyennesPro as $inscCode => $moyVal) {
                    if ($inscCode === $item['code_inscription']) {
                        $rangProFr = ($pos === 1) ? (($sexeEleve === 'F') ? '1ère' : '1er') : ($pos . 'ème');
                        break;
                    }
                    $pos++;
                }
            }

            // 6. Calcul du rang pour chaque matière individuelle
            foreach ($matieresNotes as $mCode => &$mInfo) {
                if (isset($classeMoyennesParMatiere[$mCode])) {
                    arsort($classeMoyennesParMatiere[$mCode]);
                    $pos = 1;
                    foreach ($classeMoyennesParMatiere[$mCode] as $inscCode => $mVal) {
                        if ($inscCode === $item['code_inscription']) {
                            $mInfo['rang_matiere'] = ($pos === 1) ? (($sexeEleve === 'F') ? '1ère' : '1er') : ($pos . 'ème');
                            break;
                        }
                        $pos++;
                    }
                }
            }
            unset($mInfo);
        }

        // Séparation bimanuelle : MATIÈRES GÉNÉRALES vs MATIÈRES PROFESSIONNELLES
        $matieresGenerales = [];
        $matieresPro = [];
        $totalPointsGenerales = 0; $totalCoefficientsGenerales = 0;
        $totalPointsPro = 0;       $totalCoefficientsPro = 0;

        foreach ($matieresNotes as $mCode => $m) {
            if ($m['categorie_matiere'] === CATEGORIES_MATIERES::PROFESSIONNELLE) {
                $matieresPro[] = $m;
                $totalPointsPro += $m['points'];
                $totalCoefficientsPro += $m['coefficient'];
            } else {
                $matieresGenerales[] = $m;
                $totalPointsGenerales += $m['points'];
                $totalCoefficientsGenerales += $m['coefficient'];
            }
        }

        $moyenneGenerales = $totalCoefficientsGenerales > 0 ? round($totalPointsGenerales / $totalCoefficientsGenerales, 2) : 0;
        $moyennePro = $totalCoefficientsPro > 0 ? round($totalPointsPro / $totalCoefficientsPro, 2) : 0;

        $apprGen = ($moyenneGenerales >= 16) ? 'Très Bien' : (($moyenneGenerales >= 14) ? 'Bien' : (($moyenneGenerales >= 12) ? 'Assez Bien' : (($moyenneGenerales >= 10) ? 'Passable' : (($moyenneGenerales >= 8) ? 'Insuffisant' : 'Très Insuffisant'))));
        $apprPro = ($moyennePro >= 16) ? 'Très Bien' : (($moyennePro >= 14) ? 'Bien' : (($moyennePro >= 12) ? 'Assez Bien' : (($moyennePro >= 10) ? 'Passable' : (($moyennePro >= 8) ? 'Insuffisant' : 'Très Insuffisant'))));

        // Calcul dynamique des absences de l'étudiant
        $stmtAbs = $db->prepare("
            SELECT COALESCE(SUM(duree_heures), 0) AS total_abs,
                   COALESCE(SUM(CASE WHEN justifiee = 'non' THEN duree_heures ELSE 0 END), 0) AS abs_non_justifiees
            FROM " . TABLES::ABSENCES . "
            WHERE inscription_code = ?
        ");
        $stmtAbs->execute([$item['code_inscription']]);
        $absData = $stmtAbs->fetch(PDO::FETCH_ASSOC);
        $totalAbsHeures = (float)($absData['total_abs'] ?? 0);
        $absNonJustHeures = (float)($absData['abs_non_justifiees'] ?? 0);
        $heuresAbsences = $totalAbsHeures > 0 ? ($totalAbsHeures . ' H' . ($absNonJustHeures > 0 ? ' (dont ' . $absNonJustHeures . ' H non justifiées)' : '')) : '0 H';

        // Récupération des moyennes multi-semestres et examens pour le récapitulatif annuel
        $moySem1 = null;
        $moySem2 = null;
        $moyExam = null;

        $stmtMultiSem = $db->prepare("
            SELECT comp.semestre_code, s.libelle_semestre,
                   ROUND(SUM(n.valeur_note * COALESCE(comp.coefficient, 1.00)) / NULLIF(SUM(COALESCE(comp.coefficient, 1.00)), 0), 2) AS moy_sem
            FROM " . TABLES::NOTES . " n
            INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
            LEFT JOIN " . TABLES::SEMESTRES . " s ON s.code_semestre = comp.semestre_code
            WHERE n.inscription_code = ? AND n.statut_note = '" . STATUTS::ACTIF . "'
              AND (comp.statut_composition IS NULL OR LOWER(TRIM(comp.statut_composition)) != '" . STATUTS::ANNULE . "')
            GROUP BY comp.semestre_code, s.libelle_semestre
            ORDER BY s.id_semestre ASC
        ");
        $stmtMultiSem->execute([$item['code_inscription']]);
        $multiSemRows = $stmtMultiSem->fetchAll(PDO::FETCH_ASSOC);

        foreach ($multiSemRows as $idx => $sr) {
            $semName = mb_strtolower($sr['libelle_semestre'] ?? '');
            if (str_contains($semName, '1') || $idx === 0) {
                if ($moySem1 === null) $moySem1 = (float)$sr['moy_sem'];
            }
            if (str_contains($semName, '2') || $idx === 1) {
                if ($moySem2 === null) $moySem2 = (float)$sr['moy_sem'];
            }
        }

        foreach ($semestres as $idx => $s) {
            if ($s['code_semestre'] === $selectedSemestreCode) {
                $sLib = mb_strtolower($s['libelle_semestre'] ?? '');
                if (str_contains($sLib, '1') || $idx === 0) {
                    $moySem1 = $moyenneGenerale;
                } elseif (str_contains($sLib, '2') || $idx === 1) {
                    $moySem2 = $moyenneGenerale;
                }
            }
        }

        $stmtExam = $db->prepare("
            SELECT ROUND(SUM(n.valeur_note * COALESCE(comp.coefficient, 1.00)) / NULLIF(SUM(COALESCE(comp.coefficient, 1.00)), 0), 2) AS moy_exam
            FROM " . TABLES::NOTES . " n
            INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
            WHERE n.inscription_code = ? AND comp.type_composition = 'EXAMEN' AND n.statut_note = '" . STATUTS::ACTIF . "'
              AND (comp.statut_composition IS NULL OR LOWER(TRIM(comp.statut_composition)) != '" . STATUTS::ANNULE . "')
        ");
        $stmtExam->execute([$item['code_inscription']]);
        $examVal = $stmtExam->fetchColumn();
        if ($examVal !== false && $examVal !== null) {
            $moyExam = (float)$examVal;
        }

        return [
            'item' => $item,
            'semestres' => $semestres,
            'selectedSemestreCode' => $selectedSemestreCode,
            'matieresNotes' => $matieresNotes,
            'totalPoints' => $totalPoints,
            'totalCoefficients' => $totalCoefficients,
            'moyenneGenerale' => $moyenneGenerale,
            'mention' => $mention,
            'decision' => $decision,
            'rang' => $rang,
            'rangFr' => $rangFr,
            'matieresGenerales' => $matieresGenerales,
            'matieresPro' => $matieresPro,
            'totalPointsGenerales' => $totalPointsGenerales,
            'totalCoefficientsGenerales' => $totalCoefficientsGenerales,
            'moyenneGenerales' => $moyenneGenerales,
            'rangGeneralesFr' => $rangGeneralesFr,
            'appreciationGenerales' => $apprGen,
            'totalPointsPro' => $totalPointsPro,
            'totalCoefficientsPro' => $totalCoefficientsPro,
            'moyennePro' => $moyennePro,
            'rangProFr' => $rangProFr,
            'appreciationPro' => $apprPro,
            'totalElevesClasse' => $totalElevesClasse,
            'moyenneMinClasse' => $moyenneMinClasse,
            'moyenneMaxClasse' => $moyenneMaxClasse,
            'moyenneMoyClasse' => $moyenneMoyClasse,
            'heuresAbsences' => $heuresAbsences,
            'moy_semestre1' => $moySem1,
            'moy_semestre2' => $moySem2,
            'moy_examen' => $moyExam
        ];
    }

    public function calculatePvClasseData(string $selectedClasseCode, string $selectedSemestreCode, ?string $anneeCode = null): array
    {
        $db = $this->getCon();
        $anneeItem = (new ModelAnnee())->getByCode($anneeCode);
        $anneeLibelle = $anneeItem['libelle_annee'] ?? $anneeCode;

        $classeInfo = null;
        $matieres = [];
        $pvRows = [];
        $statsClasse = [
            'totalEleves' => 0,
            'moyenneGeneraleClasse' => 0,
            'moyenneMax' => 0,
            'moyenneMin' => 0,
            'tauxAdmis' => 0
        ];

        if (empty($selectedClasseCode)) {
            return [
                'classeInfo' => null,
                'matieres' => [],
                'pvRows' => [],
                'statsClasse' => $statsClasse,
                'anneeLibelle' => $anneeLibelle
            ];
        }

        // 1. Informations de la classe
        $stmtC = $db->prepare("
            SELECT cl.*, f.libelle_filiere, n.libelle_niveau
            FROM " . TABLES::CLASSES . " cl
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
            WHERE cl.code_classe = ?
        ");
        $stmtC->execute([$selectedClasseCode]);
        $classeInfo = $stmtC->fetch(PDO::FETCH_ASSOC);

        $filiereCode = $classeInfo['filiere_code'] ?? '';
        $niveauCode = $classeInfo['niveau_code'] ?? '';

        // 2. Matières assignées / évaluées pour cette classe via VIEWS::BULLETIN_NOTES_DETAILS
        $stmtM = $db->prepare("
            SELECT DISTINCT matiere_code AS code_matiere, 
                   libelle_matiere, 
                   coefficient_maquette AS coefficient,
                   categorie_matiere
            FROM " . VIEWS::BULLETIN_NOTES_DETAILS . "
            WHERE classe_code = ? AND semestre_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
            ORDER BY CASE WHEN categorie_matiere = '" . CATEGORIES_MATIERES::GENERALE . "' THEN 1 ELSE 2 END ASC, libelle_matiere ASC
        ");
        $stmtM->execute([$selectedClasseCode, $selectedSemestreCode]);
        $matieres = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        // Fallback 1: Si aucune note n'est encore saisie, charger directement la maquette pédagogique via VIEWS::MAQUETTES_COMPLETES
        if (empty($matieres) && !empty($filiereCode) && !empty($niveauCode)) {
            $stmtMaq = $db->prepare("
                SELECT matiere_code AS code_matiere, 
                       libelle_matiere, 
                       COALESCE(coefficient, 1.00) AS coefficient,
                       COALESCE(categorie_matiere, '" . CATEGORIES_MATIERES::GENERALE . "') AS categorie_matiere
                FROM " . VIEWS::MAQUETTES_COMPLETES . "
                WHERE filiere_code = ? AND niveau_code = ? 
                  AND (annee_code = ? OR annee_code = '' OR annee_code IS NULL)
                  AND (statut_maquette = '" . STATUTS::ACTIF . "' OR statut_maquette IS NULL)
                ORDER BY CASE WHEN categorie_matiere = '" . CATEGORIES_MATIERES::GENERALE . "' THEN 1 ELSE 2 END ASC, libelle_matiere ASC
            ");
            $stmtMaq->execute([$filiereCode, $niveauCode, $anneeCode]);
            $matieres = $stmtMaq->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fallback 2: Matières actives globales
        if (empty($matieres)) {
            $matieres = $db->query("
                SELECT code_matiere, libelle_matiere, 1.00 as coefficient, '" . CATEGORIES_MATIERES::GENERALE . "' as categorie_matiere
                FROM " . TABLES::MATIERES . " 
                WHERE statut_matiere = '" . STATUTS::ACTIF . "' 
                ORDER BY libelle_matiere ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        }

        // 3. Étudiants inscrits dans cette classe pour l'année active
        $stmtE = $db->prepare("
            SELECT i.code_inscription, e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.sexe_etudiant
            FROM " . TABLES::INSCRIPTIONS . " i
            INNER JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
            WHERE i.classe_code = ? AND (i.annee_code = ? OR ? IS NULL OR ? = '') 
              AND (i.statut_inscription != '" . STATUTS::ANNULE . "' OR i.statut_inscription IS NULL)
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
        ");
        $stmtE->execute([$selectedClasseCode, $anneeCode, $anneeCode, $anneeCode]);
        $etudiants = $stmtE->fetchAll(PDO::FETCH_ASSOC);

        // 4. Batch query allégée via VIEWS::BULLETIN_NOTES_DETAILS
        $stmtAllNotes = $db->prepare("
            SELECT inscription_code, 
                   matiere_code, 
                   valeur_note, 
                   coefficient_composition
            FROM " . VIEWS::BULLETIN_NOTES_DETAILS . "
            WHERE classe_code = ? AND semestre_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
              AND (statut_composition IS NULL OR LOWER(TRIM(statut_composition)) != '" . STATUTS::ANNULE . "')
        ");
        $stmtAllNotes->execute([$selectedClasseCode, $selectedSemestreCode]);
        $allNotesRows = $stmtAllNotes->fetchAll(PDO::FETCH_ASSOC);

        $notesByInscAndMat = []; // [insc][matCode] = ['sum_pond' => X, 'sum_coef' => Y]
        foreach ($allNotesRows as $nr) {
            $insc = $nr['inscription_code'];
            $mat = $nr['matiere_code'];
            $cComp = (float)$nr['coefficient_composition'];
            if ($cComp <= 0) $cComp = 1.0;
            $vNote = (float)$nr['valeur_note'];

            if (!isset($notesByInscAndMat[$insc])) {
                $notesByInscAndMat[$insc] = [];
            }
            if (!isset($notesByInscAndMat[$insc][$mat])) {
                $notesByInscAndMat[$insc][$mat] = ['sum_pond' => 0.0, 'sum_coef' => 0.0];
            }
            $notesByInscAndMat[$insc][$mat]['sum_pond'] += ($vNote * $cComp);
            $notesByInscAndMat[$insc][$mat]['sum_coef'] += $cComp;
        }

        $tempPvData = [];
        $nbAdmis = 0;

        foreach ($etudiants as $e) {
            $inscCode = $e['code_inscription'];
            $notesMatiere = [];
            $totalPointsEtud = 0;
            $totalCoefEtud = 0;

            foreach ($matieres as $m) {
                $matCode = $m['code_matiere'];
                $coefMaquette = (float)$m['coefficient']; // COEFFICIENT MAQUETTE !

                if (isset($notesByInscAndMat[$inscCode][$matCode]) && $notesByInscAndMat[$inscCode][$matCode]['sum_coef'] > 0) {
                    $moyMat = round($notesByInscAndMat[$inscCode][$matCode]['sum_pond'] / $notesByInscAndMat[$inscCode][$matCode]['sum_coef'], 2);
                    $pts = round($moyMat * $coefMaquette, 2);
                    $totalPointsEtud += $pts;
                    $totalCoefEtud += $coefMaquette;
                    $notesMatiere[$matCode] = $moyMat;
                } else {
                    $notesMatiere[$matCode] = null;
                }
            }

            $moyenneGen = $totalCoefEtud > 0 ? round($totalPointsEtud / $totalCoefEtud, 2) : 0;
            
            $decision = 'Non éval.';
            if ($totalCoefEtud > 0) {
                if ($moyenneGen >= 10) {
                    $decision = 'Admis(e)';
                    $nbAdmis++;
                } else {
                    $decision = 'Ajourné(e)';
                }
            }

            $tempPvData[] = [
                'inscription_code' => $inscCode,
                'matricule' => $e['matricule_etudiant'],
                'nom_prenom' => $e['nom_etudiant'] . ' ' . $e['prenom_etudiant'],
                'sexe' => $e['sexe_etudiant'],
                'notes_matieres' => $notesMatiere,
                'total_points' => $totalPointsEtud,
                'total_coef' => $totalCoefEtud,
                'moyenne_generale' => $moyenneGen,
                'decision' => $decision,
                'rang' => 1
            ];
        }

        usort($tempPvData, function($a, $b) {
            return $b['moyenne_generale'] <=> $a['moyenne_generale'];
        });

        $rank = 1;
        $allMoyennes = [];
        foreach ($tempPvData as &$row) {
            $row['rang'] = $rank++;
            if ($row['total_coef'] > 0) {
                $allMoyennes[] = $row['moyenne_generale'];
            }
        }
        unset($row);

        $pvRows = $tempPvData;

        if (!empty($allMoyennes)) {
            $statsClasse['totalEleves'] = count($allMoyennes);
            $statsClasse['moyenneMax'] = max($allMoyennes);
            $statsClasse['moyenneMin'] = min($allMoyennes);
            $statsClasse['moyenneGeneraleClasse'] = round(array_sum($allMoyennes) / count($allMoyennes), 2);
            $statsClasse['tauxAdmis'] = round(($nbAdmis / count($allMoyennes)) * 100, 1);
        }

        return [
            'classeInfo' => $classeInfo,
            'matieres' => $matieres,
            'pvRows' => $pvRows,
            'statsClasse' => $statsClasse,
            'anneeLibelle' => $anneeLibelle
        ];
    }
}

