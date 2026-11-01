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

        $sqlNotes = "
            SELECT n.*, 
                   m.libelle_matiere, 
                   COALESCE(comp.coefficient, 1.00) AS coefficient, 
                   COALESCE(comp.matiere_code, cm.matiere_code) AS matiere_code,
                   comp.type_composition AS type_evaluation_code,
                   CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS nom_enseignant
            FROM " . TABLES::NOTES . " n
            INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ?
            LEFT JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON (cnf.composition_code = comp.code_composition AND cnf.niveau_code = cl.niveau_code AND cnf.filiere_code = cl.filiere_code)
            LEFT JOIN " . TABLES::COMPOSITION_MATIERES . " cm ON (cm.composition_code = comp.code_composition AND cm.composition_niveau_filiere_code = cnf.code_composition_niveau_filiere)
            LEFT JOIN " . TABLES::MATIERES . " m ON m.code_matiere = COALESCE(comp.matiere_code, cm.matiere_code)
            LEFT JOIN " . TABLES::EMPLOIS_TEMPS . " edt ON (edt.classe_code = cl.code_classe AND edt.matiere_code = COALESCE(comp.matiere_code, cm.matiere_code))
            LEFT JOIN " . TABLES::USERS . " u ON (u.code_user = edt.enseignant_code OR u.code_user = comp.user_code)
            WHERE n.inscription_code = ? AND comp.semestre_code = ? AND n.statut_note = '" . STATUTS::ACTIF . "'
              AND (comp.statut_composition IS NULL OR LOWER(TRIM(comp.statut_composition)) != '" . STATUTS::ANNULE . "')
            ORDER BY m.libelle_matiere ASC, comp.type_composition ASC
        ";
        $stmtNotes = $db->prepare($sqlNotes);
        $stmtNotes->execute([$item['classe_code'], $item['code_inscription'], $selectedSemestreCode]);
        $rawNotes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

        $matieresNotes = [];
        foreach ($rawNotes as $n) {
            $matCode = $n['matiere_code'] ?: 'MAT_INCONNUE';
            if (!isset($matieresNotes[$matCode])) {
                $matieresNotes[$matCode] = [
                    'libelle' => $n['libelle_matiere'] ?: 'Matière sans nom',
                    'coefficient' => (float)($n['coefficient'] ?? 1),
                    'prof' => trim($n['nom_enseignant'] ?? '') ?: 'Non assigné',
                    'evaluations' => [],
                    'notes' => []
                ];
            }
            $matieresNotes[$matCode]['evaluations'][] = [
                'type' => $n['type_evaluation_code'],
                'note' => (float)$n['valeur_note'],
                'observations' => $n['observations'] ?? ''
            ];
            $matieresNotes[$matCode]['notes'][] = (float)$n['valeur_note'];
        }

        $totalPoints = 0;
        $totalCoefficients = 0;
        foreach ($matieresNotes as $k => &$m) {
            $nb = count($m['notes']);
            $moyenneMatiere = $nb > 0 ? (array_sum($m['notes']) / $nb) : 0;
            $m['moyenne'] = round($moyenneMatiere, 2);
            $m['points'] = round($moyenneMatiere * $m['coefficient'], 2);

            if ($m['moyenne'] >= 16) $m['appreciation'] = 'Très Bien';
            elseif ($m['moyenne'] >= 14) $m['appreciation'] = 'Bien';
            elseif ($m['moyenne'] >= 12) $m['appreciation'] = 'Assez Bien';
            elseif ($m['moyenne'] >= 10) $m['appreciation'] = 'Passable';
            elseif ($m['moyenne'] >= 8)  $m['appreciation'] = 'Insuffisant';
            else $m['appreciation'] = 'Très Insuffisant';

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

        $rang = 1;
        $totalElevesClasse = 1;
        $moyenneMinClasse = $moyenneGenerale;
        $moyenneMaxClasse = $moyenneGenerale;
        $moyenneMoyClasse = $moyenneGenerale;

        if (!empty($item['classe_code']) && !empty($selectedSemestreCode)) {
            $sqlClassmates = "
                SELECT i.code_inscription,
                       n.valeur_note,
                       COALESCE(comp.coefficient, 1.00) AS coefficient
                FROM " . TABLES::INSCRIPTIONS . " i
                INNER JOIN " . TABLES::NOTES . " n ON n.inscription_code = i.code_inscription
                INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
                LEFT JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON (cnf.composition_code = comp.code_composition AND cnf.niveau_code = cl.niveau_code AND cnf.filiere_code = cl.filiere_code)
                LEFT JOIN " . TABLES::COMPOSITION_MATIERES . " cm ON (cm.composition_code = comp.code_composition AND cm.composition_niveau_filiere_code = cnf.code_composition_niveau_filiere)
                INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = COALESCE(comp.matiere_code, cm.matiere_code)
                WHERE i.classe_code = ? AND comp.semestre_code = ? AND n.statut_note = '" . STATUTS::ACTIF . "'
                  AND (comp.statut_composition IS NULL OR LOWER(TRIM(comp.statut_composition)) != '" . STATUTS::ANNULE . "')
                  AND (i.statut_inscription != '" . STATUTS::ANNULE . "' OR i.statut_inscription IS NULL)
            ";
            $stmtClass = $db->prepare($sqlClassmates);
            $stmtClass->execute([$item['classe_code'], $selectedSemestreCode]);
            $classNotes = $stmtClass->fetchAll(PDO::FETCH_ASSOC);

            $etudiantsMoyennes = [];
            foreach ($classNotes as $cn) {
                $insc = $cn['code_inscription'];
                if (!isset($etudiantsMoyennes[$insc])) {
                    $etudiantsMoyennes[$insc] = ['total_pts' => 0, 'total_coef' => 0];
                }
                $etudiantsMoyennes[$insc]['total_pts'] += ($cn['valeur_note'] * $cn['coefficient']);
                $etudiantsMoyennes[$insc]['total_coef'] += $cn['coefficient'];
            }

            if (!empty($etudiantsMoyennes)) {
                $classeMoyennesArray = [];
                foreach ($etudiantsMoyennes as $inscCode => $stats) {
                    if ($stats['total_coef'] > 0) {
                        $classeMoyennesArray[$inscCode] = round($stats['total_pts'] / $stats['total_coef'], 2);
                    }
                }

                if (!empty($classeMoyennesArray)) {
                    arsort($classeMoyennesArray);
                    $totalElevesClasse = count($classeMoyennesArray);
                    $moyenneMaxClasse = max($classeMoyennesArray);
                    $moyenneMinClasse = min($classeMoyennesArray);
                    $moyenneMoyClasse = round(array_sum($classeMoyennesArray) / $totalElevesClasse, 2);

                    $pos = 1;
                    foreach ($classeMoyennesArray as $inscCode => $moy) {
                        if ($inscCode === $item['code_inscription']) {
                            $rang = $pos;
                            break;
                        }
                        $pos++;
                    }
                }
            }
        }

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
            'totalElevesClasse' => $totalElevesClasse,
            'moyenneMinClasse' => $moyenneMinClasse,
            'moyenneMaxClasse' => $moyenneMaxClasse,
            'moyenneMoyClasse' => $moyenneMoyClasse,
            'heuresAbsences' => $heuresAbsences
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

        // 2. Matières assignées / évaluées pour cette classe
        $stmtM = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere, 
                   COALESCE(comp.coefficient, 1.00) AS coefficient
            FROM " . TABLES::NOTES . " n
            INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
            INNER JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = n.inscription_code
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
            LEFT JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON (cnf.composition_code = comp.code_composition AND cnf.niveau_code = cl.niveau_code AND cnf.filiere_code = cl.filiere_code)
            LEFT JOIN " . TABLES::COMPOSITION_MATIERES . " cm ON (cm.composition_code = comp.code_composition AND cm.composition_niveau_filiere_code = cnf.code_composition_niveau_filiere)
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = COALESCE(comp.matiere_code, cm.matiere_code)
            WHERE i.classe_code = ? AND comp.semestre_code = ? AND n.statut_note = '" . STATUTS::ACTIF . "'
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtM->execute([$selectedClasseCode, $selectedSemestreCode]);
        $matieres = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        // Fallback dynamique: matières ayant des notes pour cette classe
        if (empty($matieres)) {
            $stmtMatNotes = $db->prepare("
                SELECT DISTINCT m.code_matiere, m.libelle_matiere, 1.00 AS coefficient
                FROM " . TABLES::NOTES . " n
                INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
                INNER JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = n.inscription_code
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
                LEFT JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON (cnf.composition_code = comp.code_composition AND cnf.niveau_code = cl.niveau_code AND cnf.filiere_code = cl.filiere_code)
                LEFT JOIN " . TABLES::COMPOSITION_MATIERES . " cm ON (cm.composition_code = comp.code_composition AND cm.composition_niveau_filiere_code = cnf.code_composition_niveau_filiere)
                INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = COALESCE(comp.matiere_code, cm.matiere_code)
                WHERE i.classe_code = ? AND comp.semestre_code = ? AND n.statut_note = '" . STATUTS::ACTIF . "'
                ORDER BY m.libelle_matiere ASC
            ");
            $stmtMatNotes->execute([$selectedClasseCode, $selectedSemestreCode]);
            $matieres = $stmtMatNotes->fetchAll(PDO::FETCH_ASSOC);

            if (empty($matieres)) {
                $matieres = $db->query("
                    SELECT code_matiere, libelle_matiere, 1.00 as coefficient 
                    FROM " . TABLES::MATIERES . " 
                    WHERE statut_matiere = '" . STATUTS::ACTIF . "' 
                    ORDER BY libelle_matiere ASC
                ")->fetchAll(PDO::FETCH_ASSOC);
            }
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

        // 4. Batch query: Récupération en 1 seule requête de toutes les moyennes par matière pour tous les étudiants
        $stmtAllNotes = $db->prepare("
            SELECT n.inscription_code, COALESCE(comp.matiere_code, cm.matiere_code) AS matiere_code, AVG(n.valeur_note) AS moy_mat
            FROM " . TABLES::NOTES . " n
            INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = n.composition_code
            INNER JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = n.inscription_code
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
            LEFT JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON (cnf.composition_code = comp.code_composition AND cnf.niveau_code = cl.niveau_code AND cnf.filiere_code = cl.filiere_code)
            LEFT JOIN " . TABLES::COMPOSITION_MATIERES . " cm ON (cm.composition_code = comp.code_composition AND cm.composition_niveau_filiere_code = cnf.code_composition_niveau_filiere)
            WHERE i.classe_code = ? AND comp.semestre_code = ? AND n.statut_note = '" . STATUTS::ACTIF . "'
              AND (comp.statut_composition IS NULL OR LOWER(TRIM(comp.statut_composition)) != '" . STATUTS::ANNULE . "')
            GROUP BY n.inscription_code, COALESCE(comp.matiere_code, cm.matiere_code)
        ");
        $stmtAllNotes->execute([$selectedClasseCode, $selectedSemestreCode]);
        $allNotesRows = $stmtAllNotes->fetchAll(PDO::FETCH_ASSOC);

        $notesByInscAndMat = [];
        foreach ($allNotesRows as $nr) {
            $notesByInscAndMat[$nr['inscription_code']][$nr['matiere_code']] = (float)$nr['moy_mat'];
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
                $coef = (float)$m['coefficient'];

                if (isset($notesByInscAndMat[$inscCode][$matCode])) {
                    $moyMat = round($notesByInscAndMat[$inscCode][$matCode], 2);
                    $pts = round($moyMat * $coef, 2);
                    $totalPointsEtud += $pts;
                    $totalCoefEtud += $coef;
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

