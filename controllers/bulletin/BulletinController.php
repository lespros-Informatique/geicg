<?php

class BulletinController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelBulletin();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_BULLETINS', 'VIEW_NOTES']);
        $annees = $this->getAccessibleAnnees();
        $niveaux = (new ModelNiveau())->getAll();
        $classes = (new ModelClasse())->getAll();
        $selectedAnneeCode = $_SESSION['annee_active_code'] ?? null;

        $this->loadView('../views/bulletin/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_BULLETINS', 'VIEW_NOTES']);
        $anneeCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;

        $sql = "SELECT i.*, 
                       CONCAT(e.nom_etudiant, ' ', e.prenom_etudiant) AS etudiant_nom,
                       e.matricule_etudiant,
                       cl.libelle_classe AS classe_nom,
                       a.libelle_annee AS annee_nom,
                       (SELECT COUNT(*) FROM notes n WHERE n.inscription_code = i.code_inscription AND n.statut_note = 'actif') AS nb_notes
                FROM inscriptions i
                LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = i.classe_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code";

        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "i.annee_code = ?";
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
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY i.id_inscription DESC";

        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($items as $i) {
            $id = $i['id_inscription'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function calculateBulletinData(int $id, ?string $selectedSemestreCode = null): ?array
    {
        $db = $this->model->getCon();
        $stmt = $db->prepare("
            SELECT i.*, 
                   e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.sexe_etudiant,
                   e.date_naissance_etudiant, e.lieu_naissance_etudiant, e.telephone_etudiant,
                   e.email_etudiant, e.photo_etudiant, e.lieu_residence_etudiant,
                   cl.libelle_classe, cl.code_classe,
                   f.libelle_filiere, niv.libelle_niveau,
                   a.libelle_annee, a.code_annee
            FROM inscriptions i
            LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN classes cl ON cl.code_classe = i.classe_code
            LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
            LEFT JOIN niveaux niv ON niv.code_niveau = cl.niveau_code
            LEFT JOIN annees a ON a.code_annee = i.annee_code
            WHERE i.id_inscription = ?
        ");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            return null;
        }

        $semestres = $db->query("
            SELECT * FROM semestres WHERE statut_semestre = 'actif' ORDER BY id_semestre ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($selectedSemestreCode)) {
            $stmtCheck = $db->prepare("
                SELECT semestre_code FROM notes 
                WHERE inscription_code = ? AND statut_note = 'actif' 
                LIMIT 1
            ");
            $stmtCheck->execute([$item['code_inscription']]);
            $foundSem = $stmtCheck->fetchColumn();
            $selectedSemestreCode = $foundSem ?: ($semestres[0]['code_semestre'] ?? '');
        }

        $sqlNotes = "
            SELECT n.*, 
                   m.libelle_matiere, 
                   COALESCE(em.coefficient, 1.00) AS coefficient, 
                   m.code_matiere,
                   CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS nom_enseignant
            FROM notes n
            LEFT JOIN matieres m ON m.code_matiere = n.matiere_code
            LEFT JOIN enseignant_matiere em ON (em.matiere_code = n.matiere_code AND em.classe_code = ?)
            LEFT JOIN enseignants ens ON ens.code_enseignant = em.enseignant_code
            LEFT JOIN users u ON u.code_user = ens.code_enseignant
            WHERE n.inscription_code = ? AND n.semestre_code = ? AND n.statut_note = 'actif'
            ORDER BY m.libelle_matiere ASC, n.type_evaluation_code ASC
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
                       COALESCE(em.coefficient, 1.00) AS coefficient
                FROM inscriptions i
                INNER JOIN notes n ON n.inscription_code = i.code_inscription
                INNER JOIN matieres m ON m.code_matiere = n.matiere_code
                LEFT JOIN enseignant_matiere em ON (em.matiere_code = n.matiere_code AND em.classe_code = i.classe_code)
                WHERE i.classe_code = ? AND n.semestre_code = ? AND n.statut_note = 'actif'
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
            FROM absences
            WHERE inscription_code = ?
        ");
        $stmtAbs->execute([$item['code_inscription']]);
        $absData = $stmtAbs->fetch(PDO::FETCH_ASSOC);
        $totalAbsHeures = (float)($absData['total_abs'] ?? 0);
        $absNonJustHeures = (float)($absData['abs_non_justifiees'] ?? 0);
        $heuresAbsences = $totalAbsHeures > 0 ? ($totalAbsHeures . ' H' . ($absNonJustHeures > 0 ? ' (dont ' . $absNonJustHeures . ' H non justifiées)' : '')) : '0 H';

        $encryptedId = $this->validator->crypter($id);

        return [
            'item' => $item,
            'encryptedId' => $encryptedId,
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

    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_BULLETINS', 'VIEW_NOTES']);
        try {
            $id = $this->validator->decrypter($details);
            $selectedSemestreCode = $_GET['semestre'] ?? '';
            $calc = $this->calculateBulletinData($id, $selectedSemestreCode);
            if (!$calc) {
                $this->renderNotFound("Le bulletin demandé est introuvable.");
                return;
            }
            $this->loadView('../views/bulletin/details.php', $calc);
        } catch (Exception $e) {
            error_log("BulletinController::details error: " . $e->getMessage());
            $this->renderNotFound("Le bulletin demandé est introuvable.");
            return;
        }
    }

    public function imprimerBulletinPdf($details)
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_BULLETINS', 'GENERATE_BULLETINS', 'VIEW_NOTES']);
        require_once __DIR__ . '/../../core/PdfService.php';

        try {
            $id = $this->validator->decrypter($details);
            $selectedSemestreCode = $_GET['semestre'] ?? '';
            $calc = $this->calculateBulletinData($id, $selectedSemestreCode);
            if (!$calc) {
                $this->renderNotFound("Le bulletin demandé est introuvable.");
                return;
            }

            $item = $calc['item'];
            $semestreLibelle = $calc['selectedSemestreCode'];
            foreach ($calc['semestres'] as $s) {
                if ($s['code_semestre'] === $calc['selectedSemestreCode']) {
                    $semestreLibelle = $s['libelle_semestre'];
                    break;
                }
            }

            $nomComplet = trim(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? ''));

            $dataView = [
                'annee_libelle' => $item['libelle_annee'] ?? '-',
                'photo_etudiant' => $item['photo_etudiant'] ?? '',
                'nom_prenom_etudiant' => $nomComplet ?: '-',
                'matricule_etudiant' => $item['matricule_etudiant'] ?? '-',
                'date_naissance' => !empty($item['date_naissance_etudiant']) ? date('d/m/Y', strtotime($item['date_naissance_etudiant'])) : '-',
                'lieu_naissance' => $item['lieu_naissance_etudiant'] ?? '-',
                'sexe_etudiant' => $item['sexe_etudiant'] ?? '-',
                'filiere_libelle' => $item['libelle_filiere'] ?? '-',
                'niveau_libelle' => $item['libelle_niveau'] ?? '-',
                'classe_libelle' => $item['libelle_classe'] ?? '-',
                'effectif_classe' => $calc['totalElevesClasse'] ?? '-',
                'statut_affectation' => (!empty($item['affectation_etat']) && $item['affectation_etat'] === 'oui') ? 'AFFECTÉ(E)' : 'NON AFFECTÉ(E)',
                'intitule_periode' => strtoupper($semestreLibelle),
                'matieres_notes' => $calc['matieresNotes'],
                'totalPoints' => $calc['totalPoints'],
                'totalCoefficients' => $calc['totalCoefficients'],
                'moyenneGenerale' => $calc['moyenneGenerale'],
                'mention' => $calc['mention'],
                'decision' => $calc['decision'],
                'rang' => $calc['rang'],
                'totalElevesClasse' => $calc['totalElevesClasse'],
                'moyenneMinClasse' => $calc['moyenneMinClasse'],
                'moyenneMaxClasse' => $calc['moyenneMaxClasse'],
                'moyenneMoyClasse' => $calc['moyenneMoyClasse'],
                'heures_absences' => $calc['heuresAbsences'] ?? '0 H'
            ];

            $html = PdfService::renderTemplate('bulletin_notes.php', $dataView);
            if (isset($_GET['html'])) {
                echo $html;
                return;
            }

            $filename = 'Bulletin_' . ($item['matricule_etudiant'] ?? 'Etudiant') . '_' . date('Ymd_His') . '.pdf';
            PdfService::generate($html, $filename, [
                'orientation' => 'P',
                'format' => 'A4',
                'title' => 'Bulletin de Notes - ' . $nomComplet,
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_top' => 6,
                'margin_bottom' => 6
            ]);
        } catch (Exception $e) {
            error_log("BulletinController::imprimerBulletinPdf error: " . $e->getMessage());
            $this->renderNotFound("Erreur lors de la génération du bulletin PDF.");
        }
    }

    public function formulaire()
    {
        $this->requireAuth();
        header('Location: ' . RACINE . 'bulletin/list');
        exit();
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->details($details);
    }

    private function calculatePvClasseData(string $selectedClasseCode, string $selectedSemestreCode): array
    {
        $db = $this->model->getCon();
        $anneeCode = $this->getActiveAnneeCode();
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
            FROM classes cl
            LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
            LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
            WHERE cl.code_classe = ?
        ");
        $stmtC->execute([$selectedClasseCode]);
        $classeInfo = $stmtC->fetch(PDO::FETCH_ASSOC);

        // 2. Matières assignées à cette classe
        $stmtM = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere, 
                   COALESCE(em.coefficient, 1.00) AS coefficient
            FROM matieres m
            INNER JOIN enseignant_matiere em ON em.matiere_code = m.code_matiere
            WHERE em.classe_code = ? AND (em.annee_code = ? OR em.annee_code IS NULL OR ? = '')
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtM->execute([$selectedClasseCode, $anneeCode, $anneeCode]);
        $matieres = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        // Fallback dynamique: matières ayant des notes pour cette classe
        if (empty($matieres)) {
            $stmtMatNotes = $db->prepare("
                SELECT DISTINCT m.code_matiere, m.libelle_matiere, 1.00 AS coefficient
                FROM notes n
                INNER JOIN inscriptions i ON i.code_inscription = n.inscription_code
                INNER JOIN matieres m ON m.code_matiere = n.matiere_code
                WHERE i.classe_code = ? AND n.semestre_code = ? AND n.statut_note = 'actif'
                ORDER BY m.libelle_matiere ASC
            ");
            $stmtMatNotes->execute([$selectedClasseCode, $selectedSemestreCode]);
            $matieres = $stmtMatNotes->fetchAll(PDO::FETCH_ASSOC);

            if (empty($matieres)) {
                $matieres = $db->query("
                    SELECT code_matiere, libelle_matiere, 1.00 as coefficient 
                    FROM matieres 
                    WHERE statut_matiere = 'actif' 
                    ORDER BY libelle_matiere ASC
                ")->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        // 3. Étudiants inscrits dans cette classe pour l'année active
        $stmtE = $db->prepare("
            SELECT i.code_inscription, e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.sexe_etudiant
            FROM inscriptions i
            INNER JOIN etudiants e ON e.code_etudiant = i.etudiant_code
            WHERE i.classe_code = ? AND (i.annee_code = ? OR ? IS NULL OR ? = '') AND i.statut_inscription = 'actif'
            ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
        ");
        $stmtE->execute([$selectedClasseCode, $anneeCode, $anneeCode, $anneeCode]);
        $etudiants = $stmtE->fetchAll(PDO::FETCH_ASSOC);

        // 4. Batch query: Récupération en 1 seule requête de toutes les moyennes par matière pour tous les étudiants
        $stmtAllNotes = $db->prepare("
            SELECT n.inscription_code, n.matiere_code, AVG(n.valeur_note) AS moy_mat
            FROM notes n
            INNER JOIN inscriptions i ON i.code_inscription = n.inscription_code
            WHERE i.classe_code = ? AND n.semestre_code = ? AND n.statut_note = 'actif'
            GROUP BY n.inscription_code, n.matiere_code
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

    public function pvClasse($param = null)
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_BULLETINS', 'VIEW_NOTES']);
        $db = $this->model->getCon();

        $classes = (new ModelClasse())->getAll();
        $semestres = $db->query("SELECT * FROM semestres WHERE statut_semestre = 'actif' ORDER BY id_semestre ASC")->fetchAll(PDO::FETCH_ASSOC);

        $selectedClasseCode = $_GET['classe_code'] ?? '';
        if (empty($selectedClasseCode) && !empty($param)) {
            try {
                $selectedClasseCode = $this->validator->decrypter($param);
            } catch (Exception $e) {
                $selectedClasseCode = $param;
            }
        }
        if (empty($selectedClasseCode) && !empty($classes)) {
            $selectedClasseCode = $classes[0]['code_classe'] ?? '';
        }

        $selectedSemestreCode = $_GET['semestre_code'] ?? ($semestres[0]['code_semestre'] ?? '');

        $pvData = $this->calculatePvClasseData($selectedClasseCode, $selectedSemestreCode);
        $encryptedClasseCode = !empty($selectedClasseCode) ? $this->validator->crypter($selectedClasseCode) : '';

        $this->loadView('../views/bulletin/pv_classe.php', array_merge($pvData, [
            'classes' => $classes,
            'semestres' => $semestres,
            'selectedClasseCode' => $selectedClasseCode,
            'selectedSemestreCode' => $selectedSemestreCode,
            'encryptedClasseCode' => $encryptedClasseCode
        ]));
    }

    public function imprimerPvPdf($param = null)
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_BULLETINS', 'GENERATE_BULLETINS', 'VIEW_NOTES']);
        require_once __DIR__ . '/../../core/PdfService.php';

        $db = $this->model->getCon();
        $selectedClasseCode = $_GET['classe_code'] ?? '';
        if (empty($selectedClasseCode) && !empty($param)) {
            try {
                $selectedClasseCode = $this->validator->decrypter($param);
            } catch (Exception $e) {
                $selectedClasseCode = $param;
            }
        }

        $semestres = $db->query("SELECT * FROM semestres WHERE statut_semestre = 'actif' ORDER BY id_semestre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $selectedSemestreCode = $_GET['semestre'] ?? ($_GET['semestre_code'] ?? ($semestres[0]['code_semestre'] ?? ''));

        $selectedSemestreLibelle = $selectedSemestreCode;
        foreach ($semestres as $s) {
            if ($s['code_semestre'] === $selectedSemestreCode) {
                $selectedSemestreLibelle = $s['libelle_semestre'];
                break;
            }
        }

        $pvData = $this->calculatePvClasseData($selectedClasseCode, $selectedSemestreCode);
        if (empty($pvData['classeInfo'])) {
            $this->renderNotFound("Classe introuvable pour la génération du PV.");
            return;
        }

        $dataView = array_merge($pvData, [
            'selectedClasseCode' => $selectedClasseCode,
            'selectedSemestreCode' => $selectedSemestreCode,
            'selectedSemestreLibelle' => $selectedSemestreLibelle
        ]);

        $html = PdfService::renderTemplate('pv_classe_pdf.php', $dataView);
        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $className = preg_replace('/[^A-Za-z0-9_\-]/', '_', $pvData['classeInfo']['libelle_classe'] ?? 'Classe');
        $filename = 'PV_Deliberation_' . $className . '_' . date('Ymd_His') . '.pdf';

        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'PV de Délibération - ' . ($pvData['classeInfo']['libelle_classe'] ?? ''),
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8
        ]);
    }
}
