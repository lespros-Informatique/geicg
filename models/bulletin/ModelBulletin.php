<?php

class ModelBulletin extends BaseModel
{
    protected string $table = TABLES::INSCRIPTIONS;
    protected string $primaryKey = 'id_inscription';
    protected ?string $statusField = 'statut_inscription';
    protected ?string $createdAtField = 'created_at_inscription';

    public function getActiveSemestres(?string $anneeCode = null): array
    {
        if (empty($anneeCode)) {
            return [];
        }

        try {
            $sql = "SELECT * FROM " . TABLES::SEMESTRES . " WHERE annee_code = ? ORDER BY id_semestre ASC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$anneeCode]);
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
        ?string $statutNotes = null,
        ?string $matricule = null
    ): array {
        if (empty($anneeCode)) {
            return [];
        }

        try {
            $matricule = $matricule !== null ? trim($matricule) : '';
            $isMatriculeSearch = ($matricule !== '');

            if ($isMatriculeSearch) {
                // Recherche par matricule : lecture directe en 1 requête via VIEWS::MOYENNES_SEMESTRES_INSCRIPTIONS
                // (zéro boucle N+1, moyenne pondérée exacte par la maquette pédagogique)
                $semJoinCond = "s.annee_code = v.annee_code";
                $semJoinParams = [];
                if (!empty($semestreCode)) {
                    $semJoinCond .= " AND s.code_semestre = ?";
                    $semJoinParams[] = $semestreCode;
                }

                $sql = "SELECT v.*,
                               s.code_semestre,
                               s.libelle_semestre,
                               s.statut_semestre,
                               COALESCE(msi.nb_notes, 0) AS nb_notes,
                               msi.moyenne_semestre
                        FROM " . VIEWS::BULLETIN_INSCRIPTIONS_SYNTHESE . " v
                        LEFT JOIN " . TABLES::SEMESTRES . " s ON ({$semJoinCond})
                        LEFT JOIN " . VIEWS::MOYENNES_SEMESTRES_INSCRIPTIONS . " msi
                               ON msi.inscription_code = v.code_inscription
                              AND msi.annee_code = v.annee_code
                              AND (s.code_semestre IS NULL OR msi.semestre_code = s.code_semestre)";

                $conditions = [
                    "(v.statut_inscription != '" . STATUTS::ANNULE . "' OR v.statut_inscription IS NULL)",
                    "v.annee_code = ?",
                    "(v.matricule_etudiant LIKE ? OR v.matricule_mesrs LIKE ? OR v.matricule_menet LIKE ? OR v.code_etudiant LIKE ?)"
                ];
                $likeMat = '%' . $matricule . '%';
                $params = array_merge($semJoinParams, [$anneeCode, $likeMat, $likeMat, $likeMat, $likeMat]);

                if (!empty($teacherClasses)) {
                    $inClasses = implode(',', array_fill(0, count($teacherClasses), '?'));
                    $conditions[] = "v.classe_code IN ($inClasses)";
                    foreach ($teacherClasses as $tc) {
                        $params[] = $tc;
                    }
                }

                $sql .= " WHERE " . implode(" AND ", $conditions);
                $sql .= " ORDER BY v.etudiant_nom ASC, s.id_semestre ASC";

                $stmt = $this->getCon()->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            // Mode liste / filtre par classe : lecture directe via VIEWS::MOYENNES_SEMESTRES_INSCRIPTIONS
            $joinParams = [];
            if (!empty($semestreCode)) {
                $sql = "SELECT v.*,
                               COALESCE(n_cnt.nb_notes, 0) AS nb_notes,
                               n_cnt.moyenne_semestre
                        FROM " . VIEWS::BULLETIN_INSCRIPTIONS_SYNTHESE . " v
                        LEFT JOIN " . VIEWS::MOYENNES_SEMESTRES_INSCRIPTIONS . " n_cnt
                               ON n_cnt.inscription_code = v.code_inscription
                              AND n_cnt.annee_code = v.annee_code
                              AND n_cnt.semestre_code = ?";
                $joinParams[] = $semestreCode;
            } else {
                $sql = "SELECT v.*,
                               COALESCE(n_cnt.nb_notes, 0) AS nb_notes,
                               n_cnt.moyenne_semestre
                        FROM " . VIEWS::BULLETIN_INSCRIPTIONS_SYNTHESE . " v
                        LEFT JOIN (
                            SELECT inscription_code,
                                   SUM(nb_notes) AS nb_notes,
                                   ROUND(SUM(total_points) / NULLIF(SUM(total_coef), 0), 2) AS moyenne_semestre
                            FROM " . VIEWS::MOYENNES_SEMESTRES_INSCRIPTIONS . "
                            WHERE annee_code = ?
                            GROUP BY inscription_code
                        ) n_cnt ON n_cnt.inscription_code = v.code_inscription";
                $joinParams[] = $anneeCode;
            }

            $conditions = [
                "(v.statut_inscription != '" . STATUTS::ANNULE . "' OR v.statut_inscription IS NULL)",
                "v.annee_code = ?"
            ];
            $params = array_merge($joinParams, [$anneeCode]);
            if (!empty($niveauCode)) {
                $conditions[] = "v.niveau_code = ?";
                $params[] = $niveauCode;
            }
            if (!empty($classeCode)) {
                $conditions[] = "v.classe_code = ?";
                $params[] = $classeCode;
            }
            if (!empty($teacherClasses)) {
                $inClasses = implode(',', array_fill(0, count($teacherClasses), '?'));
                $conditions[] = "v.classe_code IN ($inClasses)";
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

            $sql .= " ORDER BY v.classe_nom ASC, v.etudiant_nom ASC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelBulletin::getInscriptionsWithNotes error: " . $e->getMessage());
            return [];
        }
    }

    public function isPremiereAnnee(?string $slugNiveau, ?string $libelleNiveau): bool
    {
        $slug = strtoupper(str_replace(' ', '', trim((string)$slugNiveau)));
        $lib = mb_strtoupper(trim((string)$libelleNiveau));
        if (in_array($slug, ['1A', 'L1', '1ERE', '1'], true)) {
            return true;
        }
        if (str_contains($lib, 'PREMIERE') || str_contains($lib, 'PREMIÈRE') || str_contains($lib, '1ERE') || str_contains($lib, '1ÈRE')) {
            return true;
        }
        return false;
    }

    public function isDeuxiemeSemestre(string $selectedSemestreCode, array $semestres): bool
    {
        foreach ($semestres as $idx => $s) {
            if (($s['code_semestre'] ?? '') === $selectedSemestreCode) {
                $sLib = mb_strtolower(trim($s['libelle_semestre'] ?? ''));
                if (str_contains($sLib, '2') || str_contains($sLib, 'deux') || str_contains($sLib, 'second') || $idx === 1) {
                    return true;
                }
                return false;
            }
        }
        return false;
    }

    public function calculateBulletinData(int $id, ?string $selectedSemestreCode = null, bool $useFrozenDeliberation = true): ?array
    {
        $db = $this->getCon();
        $stmt = $db->prepare("SELECT * FROM " . VIEWS::INSCRIPTIONS_COMPLETES . " WHERE id_inscription = ? AND (statut_inscription != '" . STATUTS::ANNULE . "' OR statut_inscription IS NULL) LIMIT 1");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            return null;
        }

        $semestres = $this->getActiveSemestres($item['annee_code'] ?? null);

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

        $estPremiereAnnee = $this->isPremiereAnnee($item['slug_niveau'] ?? '', $item['libelle_niveau'] ?? '');
        $estDeuxiemeSemestre = $this->isDeuxiemeSemestre((string)$selectedSemestreCode, $semestres);

        // Récupération des coefficients historiques (entiers) des semestres dans la table annees
        // Le coefficient de l'examen provient directement de la table compositions (comp.coefficient)
        $coefSem1 = 2;
        $coefSem2 = 2;
        $coefExam = 1;
        if (!empty($item['annee_code'])) {
            $stmtAnnee = $db->prepare("SELECT coef_semestre1, coef_semestre2 FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
            $stmtAnnee->execute([$item['annee_code']]);
            $anneeRow = $stmtAnnee->fetch(PDO::FETCH_ASSOC);
            if ($anneeRow) {
                $coefSem1 = max(1, (int)round((float)($anneeRow['coef_semestre1'] ?? 2)));
                $coefSem2 = max(1, (int)round((float)($anneeRow['coef_semestre2'] ?? 2)));
            }
        }

        // En 1ère année, les notes de type EXAMEN ne sont pas comptabilisées dans le calcul des moyennes de matières du semestre
        $examFilterSql = $estPremiereAnnee
            ? " AND UPPER(TRIM(COALESCE(type_evaluation_code, ''))) != 'EXAMEN'"
            : "";

        // Requête ultra-allégée via la vue optimisée VIEWS::BULLETIN_NOTES_DETAILS
        $sqlNotes = "
            SELECT *
            FROM " . VIEWS::BULLETIN_NOTES_DETAILS . "
            WHERE inscription_code = ? AND semestre_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
              AND (statut_composition IS NULL OR LOWER(TRIM(statut_composition)) != '" . STATUTS::ANNULE . "')
              {$examFilterSql}
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

        require_once __DIR__ . '/../configurations/ModelConfiguration.php';
        $modelConfig = new ModelConfiguration();
        $baremesBulletin = $modelConfig->getBaremesBulletin(true);

        $totalPoints = 0;
        $totalCoefficients = 0;
        foreach ($matieresNotes as $k => &$m) {
            // 1. Moyenne de la matière pondérée par les coefficients de chaque épreuve/composition
            $moyenneMatiere = $m['sum_coef_comp'] > 0 ? ($m['sum_notes_pond'] / $m['sum_coef_comp']) : 0;
            $m['moyenne'] = round($moyenneMatiere, 2);

            // 2. Points de la matière = Moyenne matière x Coefficient de la Maquette Pédagogique
            $m['points'] = round($moyenneMatiere * $m['coefficient'], 2);

            $evalMat = $modelConfig->evaluateNote((float)$m['moyenne'], $baremesBulletin);
            $m['appreciation'] = $evalMat['appreciation_matiere'];

            $m['rang_matiere'] = '-';
            $totalPoints += $m['points'];
            $totalCoefficients += $m['coefficient'];
        }
        unset($m);

        $moyenneGenerale = $totalCoefficients > 0 ? round($totalPoints / $totalCoefficients, 2) : 0;

        $mention = 'Non évalué';
        $decision = 'En attente';
        $codeDistinctionSanction = 'AUCUN';
        if ($totalCoefficients > 0) {
            $evalGen = $modelConfig->evaluateNote((float)$moyenneGenerale, $baremesBulletin);
            $mention = $evalGen['mention_bulletin'];
            $decision = $evalGen['appreciation_conseil'];
            $codeDistinctionSanction = $evalGen['code_distinction_sanction'];
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
                WHERE classe_code = ? AND semestre_code = ? AND annee_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
                  AND (statut_composition IS NULL OR LOWER(TRIM(statut_composition)) != '" . STATUTS::ANNULE . "')
                  {$examFilterSql}
            ";
            $stmtClass = $db->prepare($sqlClassmates);
            $stmtClass->execute([$item['classe_code'], $selectedSemestreCode, $item['annee_code'] ?? '']);
            $classNotes = $stmtClass->fetchAll(PDO::FETCH_ASSOC);

            // 1. Agrégation par étudiant & par matière (Pondérée par composition)
            $etudiantsNotesByMatiere = [];
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
            $classeMoyennesParMatiere = [];

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

            // 3. Calcul du rang général du semestre
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

        $apprGen = $totalCoefficientsGenerales > 0 ? ($modelConfig->evaluateNote((float)$moyenneGenerales, $baremesBulletin)['appreciation_matiere']) : '-';
        $apprPro = $totalCoefficientsPro > 0 ? ($modelConfig->evaluateNote((float)$moyennePro, $baremesBulletin)['appreciation_matiere']) : '-';

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

        // =====================================================================
        // CALCUL DU BLOC RÉCAPITULATIF (SEMESTRE 1 vs SEMESTRE 2 / ANNUEL)
        // =====================================================================
        $moySem1 = null;
        $moySem2 = null;
        $moyExam = null;

        $moyenneRecap = $moyenneGenerale;
        $rangRecap = $rang;
        $rangRecapFr = $rangFr;
        $moyenneMinRecap = $moyenneMinClasse;
        $moyenneMaxRecap = $moyenneMaxClasse;
        $moyenneMoyRecap = $moyenneMoyClasse;

        if (!$estDeuxiemeSemestre) {
            $moySem1 = $moyenneGenerale;
        } else {
            $moySem2 = $moyenneGenerale;
        }

        // Déterminer les codes du Semestre 1 et du Semestre 2 de l'année
        $sem1Code = null;
        $sem2Code = null;
        foreach ($semestres as $idx => $s) {
            $sLib = mb_strtolower(trim($s['libelle_semestre'] ?? ''));
            if ($sem1Code === null && (str_contains($sLib, '1') || str_contains($sLib, 'prem') || $idx === 0)) {
                $sem1Code = $s['code_semestre'];
            } elseif ($sem2Code === null && (str_contains($sLib, '2') || str_contains($sLib, 'deux') || str_contains($sLib, 'second') || $idx === 1)) {
                $sem2Code = $s['code_semestre'];
            }
        }

        if ($estDeuxiemeSemestre && !empty($item['classe_code'])) {
            // Lecture directe via la vue SQL optimisée VIEWS::SYNTHESE_MOYENNES_ANNUELLES
            // (calcule en 1 requête : Moy. S1, Moy. S2, Moy. Examen 1A, Moy. Générale Annuelle et coefficients)
            $stmtAnnual = $db->prepare("
                SELECT inscription_code,
                       coef_semestre1,
                       coef_semestre2,
                       coef_examen,
                       moyenne_s1,
                       moyenne_s2,
                       moyenne_examen,
                       moyenne_annuelle
                FROM " . VIEWS::SYNTHESE_MOYENNES_ANNUELLES . "
                WHERE classe_code = ? AND annee_code = ? AND has_notes = 1
            ");
            $stmtAnnual->execute([$item['classe_code'], $item['annee_code'] ?? '']);
            $annualRows = $stmtAnnual->fetchAll(PDO::FETCH_ASSOC);

            $classeMoyennesAnnuelles = [];

            foreach ($annualRows as $ar) {
                $insc = $ar['inscription_code'];
                $mS1 = $ar['moyenne_s1'] !== null ? round((float)$ar['moyenne_s1'], 2) : null;
                $mS2 = $ar['moyenne_s2'] !== null ? round((float)$ar['moyenne_s2'], 2) : null;
                $mEx = $ar['moyenne_examen'] !== null ? round((float)$ar['moyenne_examen'], 2) : null;
                $moyAnn = $ar['moyenne_annuelle'] !== null ? round((float)$ar['moyenne_annuelle'], 2) : 0.0;

                if ($estPremiereAnnee && !empty($ar['coef_examen'])) {
                    $coefExam = max(1, (int)$ar['coef_examen']);
                }

                if ($insc === $item['code_inscription']) {
                    $coefSem1 = max(1, (int)($ar['coef_semestre1'] ?? $coefSem1));
                    $coefSem2 = max(1, (int)($ar['coef_semestre2'] ?? $coefSem2));
                    $moySem1 = $mS1;
                    $moySem2 = $mS2 ?? $moyenneGenerale;
                    $moyExam = $mEx;
                }

                $classeMoyennesAnnuelles[$insc] = $moyAnn;
            }

            if (isset($classeMoyennesAnnuelles[$item['code_inscription']])) {
                $moyenneRecap = $classeMoyennesAnnuelles[$item['code_inscription']];
            }

            if (!empty($classeMoyennesAnnuelles)) {
                arsort($classeMoyennesAnnuelles);
                $nbAnn = count($classeMoyennesAnnuelles);
                $moyenneMaxRecap = max($classeMoyennesAnnuelles);
                $moyenneMinRecap = min($classeMoyennesAnnuelles);
                $moyenneMoyRecap = round(array_sum($classeMoyennesAnnuelles) / $nbAnn, 2);

                $pos = 1;
                foreach ($classeMoyennesAnnuelles as $inscCode => $valAnn) {
                    if ($inscCode === $item['code_inscription']) {
                        $rangRecap = $pos;
                        $rangRecapFr = ($pos === 1) ? (($sexeEleve === 'F') ? '1ère' : '1er') : ($pos . 'ème');
                        break;
                    }
                    $pos++;
                }
            }

            // Au 2ème semestre, les distinctions/sanctions et l'appréciation du conseil (S2) sont basées sur la Moyenne Générale Annuelle
            if ($totalCoefficients > 0) {
                $evalAnn = $modelConfig->evaluateNote((float)$moyenneRecap, $baremesBulletin);
                $decision = !empty($evalAnn['appreciation_conseil_s2'])
                    ? $evalAnn['appreciation_conseil_s2']
                    : $evalAnn['appreciation_conseil'];
                $codeDistinctionSanction = $evalAnn['code_distinction_sanction'];
            }

            // Si une délibération annuelle officielle (validée) existe, priorité absolue à la décision du jury
            $deliberationOfficielle = null;
            if ($useFrozenDeliberation && class_exists('ModelDeliberation') && !empty($item['code_inscription'])) {
                $deliberationOfficielle = (new ModelDeliberation())->getValidatedByInscription((string)$item['code_inscription']);
                if ($deliberationOfficielle) {
                    if (!empty($deliberationOfficielle['libelle_decision'])) {
                        $decision = (string)$deliberationOfficielle['libelle_decision'];
                    }
                    if (!empty($deliberationOfficielle['code_distinction_sanction'])) {
                        $codeDistinctionSanction = (string)$deliberationOfficielle['code_distinction_sanction'];
                    }
                }
            }
        }

        return [
            'item' => $item,
            'semestres' => $semestres,
            'selectedSemestreCode' => $selectedSemestreCode,
            'est_premiere_annee' => $estPremiereAnnee,
            'est_deuxieme_semestre' => $estDeuxiemeSemestre,
            'coef_semestre1' => $coefSem1,
            'coef_semestre2' => $coefSem2,
            'coef_examen' => $coefExam,
            'matieresNotes' => $matieresNotes,
            'totalPoints' => $totalPoints,
            'totalCoefficients' => $totalCoefficients,
            'moyenneGenerale' => $moyenneGenerale,
            'mention' => $mention,
            'decision' => $decision,
            'deliberation_officielle' => $deliberationOfficielle ?? null,
            'code_distinction_sanction' => $codeDistinctionSanction,
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
            'moyenneRecap' => $moyenneRecap,
            'rangRecap' => $rangRecap,
            'rangRecapFr' => $rangRecapFr,
            'moyenneMinRecap' => $moyenneMinRecap,
            'moyenneMaxRecap' => $moyenneMaxRecap,
            'moyenneMoyRecap' => $moyenneMoyRecap,
            'heuresAbsences' => $heuresAbsences,
            'moy_semestre1' => $moySem1,
            'moy_semestre2' => $moySem2,
            'moy_examen' => $moyExam
        ];
    }

    /**
     * Calcule en LOT tous les bulletins d'une classe en nombre CONSTANT de requêtes SQL (~6 à 8 requêtes au total,
     * contre ~8*N requêtes en boucle via calculateBulletinData).
     * Retourne exactement la même structure que calculateBulletinData() pour chaque étudiant, dans l'ordre des IDs fournis.
     */
    public function calculateClasseBulletinsBatch(
        array $inscriptionIds,
        string $classeCode,
        string $anneeCode,
        ?string $selectedSemestreCode = null,
        bool $useFrozenDeliberation = true
    ): array {
        $ids = array_values(array_unique(array_filter(array_map('intval', $inscriptionIds), fn($v) => $v > 0)));
        if (empty($ids) || trim($classeCode) === '' || trim($anneeCode) === '') {
            return [];
        }

        $db = $this->getCon();

        // 1. Charger toutes les inscriptions complètes en 1 requête
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmtIns = $db->prepare("
            SELECT *
            FROM " . VIEWS::INSCRIPTIONS_COMPLETES . "
            WHERE id_inscription IN ({$placeholders})
              AND (statut_inscription != '" . STATUTS::ANNULE . "' OR statut_inscription IS NULL)
        ");
        $stmtIns->execute($ids);
        $rawItems = $stmtIns->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (empty($rawItems)) {
            return [];
        }

        $itemsById = [];
        $inscCodes = [];
        foreach ($rawItems as $ri) {
            $itemsById[(int)$ri['id_inscription']] = $ri;
            $inscCodes[] = (string)$ri['code_inscription'];
        }
        // Conserver l'ordre exact demandé par le contrôleur
        $orderedItems = [];
        foreach ($ids as $id) {
            if (isset($itemsById[$id])) {
                $orderedItems[] = $itemsById[$id];
            }
        }
        if (empty($orderedItems)) {
            return [];
        }

        // 2. Semestres + coefficients de l'année + barèmes (3 requêtes uniques pour toute la classe)
        $semestres = $this->getActiveSemestres($anneeCode);
        if (empty($selectedSemestreCode)) {
            $selectedSemestreCode = $semestres[0]['code_semestre'] ?? '';
        }

        $firstItem = $orderedItems[0];
        $estPremiereAnnee = $this->isPremiereAnnee($firstItem['slug_niveau'] ?? '', $firstItem['libelle_niveau'] ?? '');
        $estDeuxiemeSemestre = $this->isDeuxiemeSemestre((string)$selectedSemestreCode, $semestres);

        $coefSem1 = 2;
        $coefSem2 = 2;
        $coefExam = 1;
        $stmtAnnee = $db->prepare("SELECT coef_semestre1, coef_semestre2 FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
        $stmtAnnee->execute([$anneeCode]);
        $anneeRow = $stmtAnnee->fetch(PDO::FETCH_ASSOC);
        if ($anneeRow) {
            $coefSem1 = max(1, (int)round((float)($anneeRow['coef_semestre1'] ?? 2)));
            $coefSem2 = max(1, (int)round((float)($anneeRow['coef_semestre2'] ?? 2)));
        }

        require_once __DIR__ . '/../configurations/ModelConfiguration.php';
        $modelConfig = new ModelConfiguration();
        $baremesBulletin = $modelConfig->getBaremesBulletin(true);

        // 3. Charger TOUTES les notes détaillées de la classe pour le semestre en 1 seule requête
        $examFilterSql = $estPremiereAnnee
            ? " AND UPPER(TRIM(COALESCE(type_evaluation_code, ''))) != 'EXAMEN'"
            : "";

        $classNotes = [];
        if (!empty($selectedSemestreCode)) {
            $stmtNotes = $db->prepare("
                SELECT *
                FROM " . VIEWS::BULLETIN_NOTES_DETAILS . "
                WHERE classe_code = ? AND semestre_code = ? AND annee_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
                  AND (statut_composition IS NULL OR LOWER(TRIM(statut_composition)) != '" . STATUTS::ANNULE . "')
                  {$examFilterSql}
                ORDER BY CASE WHEN categorie_matiere = '" . CATEGORIES_MATIERES::GENERALE . "' THEN 1 ELSE 2 END ASC, libelle_matiere ASC, type_evaluation_code ASC
            ");
            $stmtNotes->execute([$classeCode, $selectedSemestreCode, $anneeCode]);
            $classNotes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        // Regrouper par inscription et préparer les classements de la classe en mémoire (une seule passe !)
        $notesByInsc = [];
        $etudiantsNotesByMatiere = [];

        foreach ($classNotes as $n) {
            $insc = (string)$n['inscription_code'];
            $matCode = $n['matiere_code'] ?: 'MAT_INCONNUE';
            $coefComp = (float)($n['coefficient_composition'] ?? 1);
            if ($coefComp <= 0) $coefComp = 1.0;
            $valNote = (float)$n['valeur_note'];
            $coefMaq = (float)($n['coefficient_maquette'] ?? 1);
            $catMat = strtoupper($n['categorie_matiere'] ?? CATEGORIES_MATIERES::GENERALE);

            if (!isset($notesByInsc[$insc][$matCode])) {
                $notesByInsc[$insc][$matCode] = [
                    'matiere_code' => $matCode,
                    'libelle' => $n['libelle_matiere'] ?: 'Matière sans nom',
                    'coefficient' => $coefMaq,
                    'categorie_matiere' => $catMat,
                    'prof' => trim($n['nom_enseignant'] ?? ($n['enseignant'] ?? '')) ?: 'Non assigné',
                    'enseignant' => trim($n['enseignant'] ?? ($n['nom_enseignant'] ?? '')) ?: 'Non assigné',
                    'evaluations' => [],
                    'sum_notes_pond' => 0.0,
                    'sum_coef_comp' => 0.0
                ];
            }
            $notesByInsc[$insc][$matCode]['evaluations'][] = [
                'type' => $n['type_evaluation_code'],
                'note' => $valNote,
                'coefficient' => $coefComp,
                'observations' => $n['observations'] ?? ''
            ];
            $notesByInsc[$insc][$matCode]['sum_notes_pond'] += ($valNote * $coefComp);
            $notesByInsc[$insc][$matCode]['sum_coef_comp'] += $coefComp;

            if (!isset($etudiantsNotesByMatiere[$insc][$matCode])) {
                $etudiantsNotesByMatiere[$insc][$matCode] = [
                    'sum_notes_pond' => 0.0,
                    'sum_coef_comp' => 0.0,
                    'coefficient_maquette' => $coefMaq,
                    'categorie' => $catMat
                ];
            }
            $etudiantsNotesByMatiere[$insc][$matCode]['sum_notes_pond'] += ($valNote * $coefComp);
            $etudiantsNotesByMatiere[$insc][$matCode]['sum_coef_comp'] += $coefComp;
        }

        // Calculer une seule fois les classements de la classe (global, général, pro, et par matière)
        $classeMoyennesGlobales = [];
        $classeMoyennesGenerales = [];
        $classeMoyennesPro = [];
        $classeMoyennesParMatiere = [];

        foreach ($etudiantsNotesByMatiere as $insc => $mats) {
            $ptsGlob = 0.0; $coefGlob = 0.0;
            $ptsGen = 0.0;  $coefGen = 0.0;
            $ptsPro = 0.0;  $coefPro = 0.0;

            foreach ($mats as $matCode => $info) {
                $cMaquette = $info['coefficient_maquette'];
                $mVal = $info['sum_coef_comp'] > 0 ? ($info['sum_notes_pond'] / $info['sum_coef_comp']) : 0.0;

                $ptsGlob += ($mVal * $cMaquette);
                $coefGlob += $cMaquette;

                if ($info['categorie'] === CATEGORIES_MATIERES::GENERALE) {
                    $ptsGen += ($mVal * $cMaquette);
                    $coefGen += $cMaquette;
                } else {
                    $ptsPro += ($mVal * $cMaquette);
                    $coefPro += $cMaquette;
                }

                $classeMoyennesParMatiere[$matCode][$insc] = round($mVal, 2);
            }

            if ($coefGlob > 0) $classeMoyennesGlobales[$insc] = round($ptsGlob / $coefGlob, 2);
            if ($coefGen > 0)  $classeMoyennesGenerales[$insc] = round($ptsGen / $coefGen, 2);
            if ($coefPro > 0)  $classeMoyennesPro[$insc] = round($ptsPro / $coefPro, 2);
        }

        // Indexer les rangs en O(1)
        $rankGlobMap = [];
        $totalElevesClasseGlobal = 1;
        $moyMaxGlobal = null;
        $moyMinGlobal = null;
        $moyMoyGlobal = null;

        if (!empty($classeMoyennesGlobales)) {
            arsort($classeMoyennesGlobales);
            $totalElevesClasseGlobal = count($classeMoyennesGlobales);
            $moyMaxGlobal = max($classeMoyennesGlobales);
            $moyMinGlobal = min($classeMoyennesGlobales);
            $moyMoyGlobal = round(array_sum($classeMoyennesGlobales) / $totalElevesClasseGlobal, 2);
            $pos = 1;
            foreach ($classeMoyennesGlobales as $ic => $mv) {
                $rankGlobMap[$ic] = $pos++;
            }
        }

        $rankGenMap = [];
        if (!empty($classeMoyennesGenerales)) {
            arsort($classeMoyennesGenerales);
            $pos = 1;
            foreach ($classeMoyennesGenerales as $ic => $mv) {
                $rankGenMap[$ic] = $pos++;
            }
        }

        $rankProMap = [];
        if (!empty($classeMoyennesPro)) {
            arsort($classeMoyennesPro);
            $pos = 1;
            foreach ($classeMoyennesPro as $ic => $mv) {
                $rankProMap[$ic] = $pos++;
            }
        }

        $rankMatMap = [];
        foreach ($classeMoyennesParMatiere as $mCode => $mArr) {
            arsort($mArr);
            $pos = 1;
            foreach ($mArr as $ic => $mv) {
                $rankMatMap[$mCode][$ic] = $pos++;
            }
        }

        // 4. Charger les absences de tous les étudiants en 1 requête
        $absMap = [];
        $codePlaceholders = implode(',', array_fill(0, count($inscCodes), '?'));
        $stmtAbs = $db->prepare("
            SELECT inscription_code,
                   COALESCE(SUM(duree_heures), 0) AS total_abs,
                   COALESCE(SUM(CASE WHEN justifiee = 'non' THEN duree_heures ELSE 0 END), 0) AS abs_non_justifiees
            FROM " . TABLES::ABSENCES . "
            WHERE inscription_code IN ({$codePlaceholders})
            GROUP BY inscription_code
        ");
        $stmtAbs->execute(array_values($inscCodes));
        foreach ($stmtAbs->fetchAll(PDO::FETCH_ASSOC) ?: [] as $ab) {
            $absMap[$ab['inscription_code']] = [
                'total_abs' => (float)$ab['total_abs'],
                'abs_non_justifiees' => (float)$ab['abs_non_justifiees']
            ];
        }

        // 5. Si S2 : charger en 1 requête la synthèse annuelle + en 1 requête les délibérations validées
        $annualMap = [];
        $classeMoyennesAnnuelles = [];
        $rankAnnMap = [];
        $moyMinAnn = null;
        $moyMaxAnn = null;
        $moyMoyAnn = null;
        $delibsMap = [];

        if ($estDeuxiemeSemestre) {
            $stmtAnnual = $db->prepare("
                SELECT inscription_code,
                       coef_semestre1,
                       coef_semestre2,
                       coef_examen,
                       moyenne_s1,
                       moyenne_s2,
                       moyenne_examen,
                       moyenne_annuelle
                FROM " . VIEWS::SYNTHESE_MOYENNES_ANNUELLES . "
                WHERE classe_code = ? AND annee_code = ? AND has_notes = 1
            ");
            $stmtAnnual->execute([$classeCode, $anneeCode]);
            foreach ($stmtAnnual->fetchAll(PDO::FETCH_ASSOC) ?: [] as $ar) {
                $ic = (string)$ar['inscription_code'];
                $annualMap[$ic] = $ar;
                if ($estPremiereAnnee && !empty($ar['coef_examen'])) {
                    $coefExam = max(1, (int)$ar['coef_examen']);
                }
                $classeMoyennesAnnuelles[$ic] = $ar['moyenne_annuelle'] !== null ? round((float)$ar['moyenne_annuelle'], 2) : 0.0;
            }
            if (!empty($classeMoyennesAnnuelles)) {
                arsort($classeMoyennesAnnuelles);
                $nbAnn = count($classeMoyennesAnnuelles);
                $moyMaxAnn = max($classeMoyennesAnnuelles);
                $moyMinAnn = min($classeMoyennesAnnuelles);
                $moyMoyAnn = round(array_sum($classeMoyennesAnnuelles) / $nbAnn, 2);
                $pos = 1;
                foreach ($classeMoyennesAnnuelles as $ic => $valAnn) {
                    $rankAnnMap[$ic] = $pos++;
                }
            }

            if ($useFrozenDeliberation && class_exists('ModelDeliberation')) {
                $allDelibs = (new ModelDeliberation())->getByClasseAndAnnee($classeCode, $anneeCode);
                foreach ($allDelibs as $ic => $dRow) {
                    if (($dRow['statut_deliberation'] ?? '') === STATUTS::VALIDE) {
                        $delibsMap[$ic] = $dRow;
                    }
                }
            }
        }

        // 6. Assemblage final en mémoire (0 requête SQL dans cette boucle !)
        $formatRankFr = static function (int $pos, string $sexe): string {
            return ($pos === 1) ? (($sexe === 'F') ? '1ère' : '1er') : ($pos . 'ème');
        };

        $results = [];
        foreach ($orderedItems as $item) {
            $codeIns = (string)$item['code_inscription'];
            $sexeEleve = strtoupper($item['sexe_etudiant'] ?? 'M');
            $matieresNotes = $notesByInsc[$codeIns] ?? [];

            $totalPoints = 0.0;
            $totalCoefficients = 0.0;
            foreach ($matieresNotes as $mCode => &$m) {
                $moyMat = $m['sum_coef_comp'] > 0 ? ($m['sum_notes_pond'] / $m['sum_coef_comp']) : 0.0;
                $m['moyenne'] = round($moyMat, 2);
                $m['points'] = round($moyMat * $m['coefficient'], 2);
                $evalMat = $modelConfig->evaluateNote((float)$m['moyenne'], $baremesBulletin);
                $m['appreciation'] = $evalMat['appreciation_matiere'];
                $m['rang_matiere'] = isset($rankMatMap[$mCode][$codeIns])
                    ? $formatRankFr($rankMatMap[$mCode][$codeIns], $sexeEleve)
                    : '-';
                $totalPoints += $m['points'];
                $totalCoefficients += $m['coefficient'];
            }
            unset($m);

            $moyenneGenerale = $totalCoefficients > 0 ? round($totalPoints / $totalCoefficients, 2) : 0;

            $mention = 'Non évalué';
            $decision = 'En attente';
            $codeDistinctionSanction = 'AUCUN';
            if ($totalCoefficients > 0) {
                $evalGen = $modelConfig->evaluateNote((float)$moyenneGenerale, $baremesBulletin);
                $mention = $evalGen['mention_bulletin'];
                $decision = $evalGen['appreciation_conseil'];
                $codeDistinctionSanction = $evalGen['code_distinction_sanction'];
            }

            $rang = $rankGlobMap[$codeIns] ?? 1;
            $rangFr = isset($rankGlobMap[$codeIns]) ? $formatRankFr($rankGlobMap[$codeIns], $sexeEleve) : '1er';
            $rangGeneralesFr = isset($rankGenMap[$codeIns]) ? $formatRankFr($rankGenMap[$codeIns], $sexeEleve) : '1er';
            $rangProFr = isset($rankProMap[$codeIns]) ? $formatRankFr($rankProMap[$codeIns], $sexeEleve) : '1er';

            $totalElevesClasse = !empty($classeMoyennesGlobales) ? $totalElevesClasseGlobal : 1;
            $moyenneMinClasse = $moyMinGlobal !== null ? $moyMinGlobal : $moyenneGenerale;
            $moyenneMaxClasse = $moyMaxGlobal !== null ? $moyMaxGlobal : $moyenneGenerale;
            $moyenneMoyClasse = $moyMoyGlobal !== null ? $moyMoyGlobal : $moyenneGenerale;

            $matieresGenerales = [];
            $matieresPro = [];
            $totalPointsGenerales = 0.0; $totalCoefficientsGenerales = 0.0;
            $totalPointsPro = 0.0;       $totalCoefficientsPro = 0.0;
            foreach ($matieresNotes as $m) {
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
            $apprGen = $totalCoefficientsGenerales > 0 ? ($modelConfig->evaluateNote((float)$moyenneGenerales, $baremesBulletin)['appreciation_matiere']) : '-';
            $apprPro = $totalCoefficientsPro > 0 ? ($modelConfig->evaluateNote((float)$moyennePro, $baremesBulletin)['appreciation_matiere']) : '-';

            $totalAbsHeures = $absMap[$codeIns]['total_abs'] ?? 0.0;
            $absNonJustHeures = $absMap[$codeIns]['abs_non_justifiees'] ?? 0.0;
            $heuresAbsences = $totalAbsHeures > 0
                ? ($totalAbsHeures . ' H' . ($absNonJustHeures > 0 ? ' (dont ' . $absNonJustHeures . ' H non justifiées)' : ''))
                : '0 H';

            $moySem1 = !$estDeuxiemeSemestre ? $moyenneGenerale : null;
            $moySem2 = $estDeuxiemeSemestre ? $moyenneGenerale : null;
            $moyExam = null;
            $moyenneRecap = $moyenneGenerale;
            $rangRecap = $rang;
            $rangRecapFr = $rangFr;
            $moyenneMinRecap = $moyenneMinClasse;
            $moyenneMaxRecap = $moyenneMaxClasse;
            $moyenneMoyRecap = $moyenneMoyClasse;
            $deliberationOfficielle = null;
            $cSem1 = $coefSem1;
            $cSem2 = $coefSem2;

            if ($estDeuxiemeSemestre) {
                if (isset($annualMap[$codeIns])) {
                    $ar = $annualMap[$codeIns];
                    $cSem1 = max(1, (int)($ar['coef_semestre1'] ?? $coefSem1));
                    $cSem2 = max(1, (int)($ar['coef_semestre2'] ?? $coefSem2));
                    $moySem1 = $ar['moyenne_s1'] !== null ? round((float)$ar['moyenne_s1'], 2) : null;
                    $moySem2 = $ar['moyenne_s2'] !== null ? round((float)$ar['moyenne_s2'], 2) : $moyenneGenerale;
                    $moyExam = $ar['moyenne_examen'] !== null ? round((float)$ar['moyenne_examen'], 2) : null;
                    $moyenneRecap = $classeMoyennesAnnuelles[$codeIns];
                }
                if (!empty($classeMoyennesAnnuelles)) {
                    $moyenneMaxRecap = $moyMaxAnn;
                    $moyenneMinRecap = $moyMinAnn;
                    $moyenneMoyRecap = $moyMoyAnn;
                    if (isset($rankAnnMap[$codeIns])) {
                        $rangRecap = $rankAnnMap[$codeIns];
                        $rangRecapFr = $formatRankFr($rangRecap, $sexeEleve);
                    }
                }
                if ($totalCoefficients > 0) {
                    $evalAnn = $modelConfig->evaluateNote((float)$moyenneRecap, $baremesBulletin);
                    $decision = !empty($evalAnn['appreciation_conseil_s2'])
                        ? $evalAnn['appreciation_conseil_s2']
                        : $evalAnn['appreciation_conseil'];
                    $codeDistinctionSanction = $evalAnn['code_distinction_sanction'];
                }
                if ($useFrozenDeliberation && isset($delibsMap[$codeIns])) {
                    $deliberationOfficielle = $delibsMap[$codeIns];
                    if (!empty($deliberationOfficielle['libelle_decision'])) {
                        $decision = (string)$deliberationOfficielle['libelle_decision'];
                    }
                    if (!empty($deliberationOfficielle['code_distinction_sanction'])) {
                        $codeDistinctionSanction = (string)$deliberationOfficielle['code_distinction_sanction'];
                    }
                }
            }

            $results[] = [
                'item' => $item,
                'semestres' => $semestres,
                'selectedSemestreCode' => $selectedSemestreCode,
                'est_premiere_annee' => $estPremiereAnnee,
                'est_deuxieme_semestre' => $estDeuxiemeSemestre,
                'coef_semestre1' => $cSem1,
                'coef_semestre2' => $cSem2,
                'coef_examen' => $coefExam,
                'matieresNotes' => $matieresNotes,
                'totalPoints' => $totalPoints,
                'totalCoefficients' => $totalCoefficients,
                'moyenneGenerale' => $moyenneGenerale,
                'mention' => $mention,
                'decision' => $decision,
                'deliberation_officielle' => $deliberationOfficielle,
                'code_distinction_sanction' => $codeDistinctionSanction,
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
                'moyenneRecap' => $moyenneRecap,
                'rangRecap' => $rangRecap,
                'rangRecapFr' => $rangRecapFr,
                'moyenneMinRecap' => $moyenneMinRecap,
                'moyenneMaxRecap' => $moyenneMaxRecap,
                'moyenneMoyRecap' => $moyenneMoyRecap,
                'heuresAbsences' => $heuresAbsences,
                'moy_semestre1' => $moySem1,
                'moy_semestre2' => $moySem2,
                'moy_examen' => $moyExam
            ];
        }

        return $results;
    }

    public function calculatePvClasseData(string $selectedClasseCode, string $selectedSemestreCode, ?string $anneeCode = null): array
    {
        $db = $this->getCon();
        $anneeItem = !empty($anneeCode) ? (new ModelAnnee())->getByCode((string)$anneeCode) : [];
        $anneeLibelle = $anneeItem['libelle_annee'] ?? ($anneeCode ?: '-');

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
            SELECT cl.*, f.libelle_filiere, n.libelle_niveau, n.slug_niveau
            FROM " . TABLES::CLASSES . " cl
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = cl.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = cl.niveau_code
            WHERE cl.code_classe = ?
            LIMIT 1
        ");
        $stmtC->execute([$selectedClasseCode]);
        $classeInfo = $stmtC->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$classeInfo) {
            return [
                'classeInfo' => null,
                'matieres' => [],
                'pvRows' => [],
                'statsClasse' => $statsClasse,
                'anneeLibelle' => $anneeLibelle
            ];
        }

        if (empty($anneeCode) && !empty($classeInfo['annee_code'])) {
            $anneeCode = $classeInfo['annee_code'];
            $anneeItem = (new ModelAnnee())->getByCode((string)$anneeCode);
            $anneeLibelle = $anneeItem['libelle_annee'] ?? $anneeCode;
        }

        $filiereCode = $classeInfo['filiere_code'] ?? '';
        $niveauCode = $classeInfo['niveau_code'] ?? '';
        $estPremiereAnneePv = $this->isPremiereAnnee($classeInfo['slug_niveau'] ?? '', $classeInfo['libelle_niveau'] ?? '');
        $pvExamFilterSql = $estPremiereAnneePv
            ? " AND UPPER(TRIM(COALESCE(type_evaluation_code, ''))) != 'EXAMEN'"
            : "";

        // 2. Matières assignées / évaluées pour cette classe via VIEWS::BULLETIN_NOTES_DETAILS
        $matWhere = "classe_code = ? AND semestre_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
              AND (statut_composition IS NULL OR LOWER(TRIM(statut_composition)) != '" . STATUTS::ANNULE . "'){$pvExamFilterSql}";
        $matParams = [$selectedClasseCode, $selectedSemestreCode];
        if (!empty($anneeCode)) {
            $matWhere .= " AND annee_code = ?";
            $matParams[] = $anneeCode;
        }

        $stmtM = $db->prepare("
            SELECT DISTINCT matiere_code AS code_matiere, 
                   libelle_matiere, 
                   coefficient_maquette AS coefficient,
                   categorie_matiere
            FROM " . VIEWS::BULLETIN_NOTES_DETAILS . "
            WHERE {$matWhere}
            ORDER BY CASE WHEN categorie_matiere = '" . CATEGORIES_MATIERES::GENERALE . "' THEN 1 ELSE 2 END ASC, libelle_matiere ASC
        ");
        $stmtM->execute($matParams);
        $matieres = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        // Fallback 1: Si aucune note n'est encore saisie, charger directement la maquette pédagogique via VIEWS::MAQUETTES_COMPLETES
        if (empty($matieres) && !empty($filiereCode) && !empty($niveauCode)) {
            $maqWhere = "filiere_code = ? AND niveau_code = ?
                  AND (statut_maquette = '" . STATUTS::ACTIF . "' OR statut_maquette IS NULL)";
            $maqParams = [$filiereCode, $niveauCode];
            if (!empty($anneeCode)) {
                $maqWhere .= " AND (annee_code = ? OR annee_code IS NULL OR annee_code = '')";
                $maqParams[] = $anneeCode;
            }
            $stmtMaq = $db->prepare("
                SELECT matiere_code AS code_matiere, 
                       libelle_matiere, 
                       COALESCE(coefficient, 1.00) AS coefficient,
                       COALESCE(categorie_matiere, '" . CATEGORIES_MATIERES::GENERALE . "') AS categorie_matiere
                FROM " . VIEWS::MAQUETTES_COMPLETES . "
                WHERE {$maqWhere}
                ORDER BY CASE WHEN categorie_matiere = '" . CATEGORIES_MATIERES::GENERALE . "' THEN 1 ELSE 2 END ASC, libelle_matiere ASC
            ");
            $stmtMaq->execute($maqParams);
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
            SELECT code_inscription, etudiant_code, etudiant_nom, matricule_etudiant, sexe_etudiant
            FROM " . VIEWS::BULLETIN_INSCRIPTIONS_SYNTHESE . "
            WHERE classe_code = ? AND annee_code = ? 
              AND (statut_inscription != '" . STATUTS::ANNULE . "' OR statut_inscription IS NULL)
            ORDER BY etudiant_nom ASC
        ");
        $stmtE->execute([$selectedClasseCode, $anneeCode]);
        $etudiants = $stmtE->fetchAll(PDO::FETCH_ASSOC);

        // 4. Batch query allégée via VIEWS::BULLETIN_NOTES_DETAILS
        $notesWhere = "classe_code = ? AND semestre_code = ? AND statut_note = '" . STATUTS::ACTIF . "'
              AND (statut_composition IS NULL OR LOWER(TRIM(statut_composition)) != '" . STATUTS::ANNULE . "'){$pvExamFilterSql}";
        $notesParams = [$selectedClasseCode, $selectedSemestreCode];
        if (!empty($anneeCode)) {
            $notesWhere .= " AND annee_code = ?";
            $notesParams[] = $anneeCode;
        }

        $stmtAllNotes = $db->prepare("
            SELECT inscription_code, 
                   matiere_code, 
                   valeur_note, 
                   coefficient_composition
            FROM " . VIEWS::BULLETIN_NOTES_DETAILS . "
            WHERE {$notesWhere}
        ");
        $stmtAllNotes->execute($notesParams);
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
                'nom_prenom' => $e['etudiant_nom'],
                'sexe' => $e['sexe_etudiant'],
                'notes_matieres' => $notesMatiere,
                'total_points' => $totalPointsEtud,
                'total_coef' => $totalCoefEtud,
                'moyenne_generale' => $moyenneGen,
                'decision' => $decision,
                'rang' => null,
                'ex_aequo' => false
            ];
        }

        // Trier d'abord les étudiants évalués par moyenne décroissante, puis les non évalués par ordre alphabétique
        usort($tempPvData, function($a, $b) {
            $aEval = ($a['total_coef'] > 0) ? 1 : 0;
            $bEval = ($b['total_coef'] > 0) ? 1 : 0;
            if ($aEval !== $bEval) {
                return $bEval <=> $aEval;
            }
            if ($aEval === 1 && $b['moyenne_generale'] !== $a['moyenne_generale']) {
                return $b['moyenne_generale'] <=> $a['moyenne_generale'];
            }
            return strcmp((string)$a['nom_prenom'], (string)$b['nom_prenom']);
        });

        // Attribution des rangs avec gestion des ex-æquo uniquement pour les élèves évalués
        $evalIndex = 0;
        $currentRank = 0;
        $prevMoy = null;
        $moyCounts = [];
        $allMoyennes = [];

        foreach ($tempPvData as $rowCheck) {
            if ($rowCheck['total_coef'] > 0) {
                $keyMoy = number_format((float)$rowCheck['moyenne_generale'], 2, '.', '');
                $moyCounts[$keyMoy] = ($moyCounts[$keyMoy] ?? 0) + 1;
            }
        }

        foreach ($tempPvData as &$row) {
            if ($row['total_coef'] > 0) {
                $evalIndex++;
                $keyMoy = number_format((float)$row['moyenne_generale'], 2, '.', '');
                if ($prevMoy === null || $keyMoy !== $prevMoy) {
                    $currentRank = $evalIndex;
                    $prevMoy = $keyMoy;
                }
                $row['rang'] = $currentRank;
                $row['ex_aequo'] = (($moyCounts[$keyMoy] ?? 0) > 1);
                $allMoyennes[] = $row['moyenne_generale'];
            } else {
                $row['rang'] = null;
                $row['ex_aequo'] = false;
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

    /**
     * Retourne les moyennes d'examen par étudiant d'une classe depuis VIEWS::MOYENNES_EXAMEN_INSCRIPTIONS
     */
    public function getMoyennesExamenClasse(string $classeCode, string $anneeCode, ?string $semestreCode = null): array
    {
        if (trim($classeCode) === '' || trim($anneeCode) === '') {
            return [];
        }
        try {
            $sql = "
                SELECT *
                FROM " . VIEWS::MOYENNES_EXAMEN_INSCRIPTIONS . "
                WHERE classe_code = ? AND annee_code = ?
            ";
            $params = [$classeCode, $anneeCode];
            if (!empty($semestreCode)) {
                $sql .= " AND semestre_code = ?";
                $params[] = $semestreCode;
            }
            $sql .= " ORDER BY moyenne_examen DESC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelBulletin::getMoyennesExamenClasse error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retourne le détail par matière des notes d'examen d'un étudiant depuis VIEWS::MOYENNES_EXAMEN_MATIERES
     */
    public function getDetailsExamenInscription(string $inscriptionCode, ?string $semestreCode = null): array
    {
        if (trim($inscriptionCode) === '') {
            return [];
        }
        try {
            $sql = "
                SELECT *
                FROM " . VIEWS::MOYENNES_EXAMEN_MATIERES . "
                WHERE inscription_code = ?
            ";
            $params = [$inscriptionCode];
            if (!empty($semestreCode)) {
                $sql .= " AND semestre_code = ?";
                $params[] = $semestreCode;
            }
            $sql .= " ORDER BY CASE WHEN categorie_matiere = '" . CATEGORIES_MATIERES::GENERALE . "' THEN 1 ELSE 2 END ASC, libelle_matiere ASC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelBulletin::getDetailsExamenInscription error: " . $e->getMessage());
            return [];
        }
    }
}

