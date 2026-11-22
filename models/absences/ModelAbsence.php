<?php

class ModelAbsence extends BaseModel
{
    protected string $table = TABLES::ABSENCES;
    protected string $primaryKey = 'id_absence';
    protected ?string $createdAtField = 'created_at_absence';

    public function getAll(
        ?string $anneeCode = null,
        ?string $niveauCode = null,
        ?string $classeCode = null,
        ?string $semestreCode = null,
        ?string $dateDebut = null,
        ?string $dateFin = null
    ): array
    {
        if (empty($anneeCode)) {
            return [];
        }

        $sql = "SELECT * FROM " . VIEWS::ABSENCES_COMPLETES . "";
        $conditions = [];
        $params = [];
        $conditions[] = "(annee_code = ? OR inscription_annee_code = ?)";
        $params[] = $anneeCode;
        $params[] = $anneeCode;
        if (!empty($semestreCode)) {
            $conditions[] = "semestre_code = ?";
            $params[] = $semestreCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "classe_code = ?";
            $params[] = $classeCode;
        }
        if (!empty($dateDebut)) {
            $conditions[] = "date_absence >= ?";
            $params[] = $dateDebut;
        }
        if (!empty($dateFin)) {
            $conditions[] = "date_absence <= ?";
            $params[] = $dateFin;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY date_absence DESC, id_absence DESC ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelAbsence::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function resolveSemestreCodeForDate(string $anneeCode, ?string $dateAbsence = null): ?string
    {
        if (empty($anneeCode)) {
            return null;
        }

        try {
            $db = $this->getCon();
            if (!empty($dateAbsence)) {
                $stmtDate = $db->prepare("
                    SELECT code_semestre
                    FROM " . TABLES::SEMESTRES . "
                    WHERE annee_code = ?
                      AND date_debut_semestre IS NOT NULL
                      AND date_fin_semestre IS NOT NULL
                      AND ? BETWEEN date_debut_semestre AND date_fin_semestre
                    ORDER BY id_semestre ASC
                    LIMIT 1
                ");
                $stmtDate->execute([$anneeCode, $dateAbsence]);
                $matched = $stmtDate->fetchColumn();
                if ($matched) {
                    return (string)$matched;
                }
            }

            $stmtActive = $db->prepare("
                SELECT code_semestre
                FROM " . TABLES::SEMESTRES . "
                WHERE annee_code = ?
                ORDER BY (statut_semestre = '" . STATUTS::ACTIF . "') DESC, id_semestre DESC
                LIMIT 1
            ");
            $stmtActive->execute([$anneeCode]);
            $fallback = $stmtActive->fetchColumn();
            return $fallback ? (string)$fallback : null;
        } catch (Exception $e) {
            error_log("ModelAbsence::resolveSemestreCodeForDate: " . $e->getMessage());
            return null;
        }
    }

    public function getFormDataDependencies(?string $anneeCode = null): array
    {
        require_once __DIR__ . '/../classes/ModelClasse.php';
        require_once __DIR__ . '/../matieres/ModelMatiere.php';
        require_once __DIR__ . '/../semestres/ModelSemestre.php';

        $targetYear = $anneeCode ?? Session::get('annee_active_code', '');
        $classes = (new ModelClasse())->getAll($targetYear);
        $matieres = (new ModelMatiere())->getAll();
        $semestres = (new ModelSemestre())->getAll(!empty($targetYear) && $targetYear !== 'ALL' ? $targetYear : null);
        usort($semestres, static fn($a, $b) => ((int)($a['id_semestre'] ?? 0)) <=> ((int)($b['id_semestre'] ?? 0)));

        $activeSemestreCode = '';
        foreach ($semestres as $sem) {
            if (($sem['statut_semestre'] ?? '') === STATUTS::ACTIF) {
                $activeSemestreCode = (string)$sem['code_semestre'];
                break;
            }
        }
        if ($activeSemestreCode === '' && !empty($semestres)) {
            $activeSemestreCode = (string)($semestres[0]['code_semestre'] ?? '');
        }

        $params = [];
        $yearCondition = '';
        if (!empty($targetYear) && $targetYear !== 'ALL') {
            $yearCondition = " AND i.annee_code = ?";
            $params[] = $targetYear;
        }

        $sql = "
            SELECT DISTINCT e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
                   i.code_inscription, i.classe_code, cl.libelle_classe
            FROM " . TABLES::ETUDIANTS . " e
            INNER JOIN " . TABLES::INSCRIPTIONS . " i ON i.etudiant_code = e.code_etudiant
                AND i.statut_inscription != '" . STATUTS::ANNULE . "'" . $yearCondition . "
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
            ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        $etudiantsDetails = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'classes' => $classes,
            'matieres' => $matieres,
            'semestres' => $semestres,
            'activeSemestreCode' => $activeSemestreCode,
            'etudiantsDetails' => $etudiantsDetails
        ];
    }

    public function getFilterLabels(
        ?string $anneeCode = null,
        ?string $semestreCode = null,
        ?string $niveauCode = null,
        ?string $classeCode = null
    ): array {
        $labels = [
            'annee' => '',
            'semestre' => 'Tous',
            'niveau' => 'Tous',
            'classe' => 'Toutes'
        ];

        $db = $this->getCon();
        try {
            if (!empty($anneeCode)) {
                $stmt = $db->prepare("SELECT libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
                $stmt->execute([$anneeCode]);
                $val = $stmt->fetchColumn();
                if ($val) $labels['annee'] = (string)$val;
            }
            if (!empty($semestreCode)) {
                $stmt = $db->prepare("SELECT libelle_semestre FROM " . TABLES::SEMESTRES . " WHERE code_semestre = ? LIMIT 1");
                $stmt->execute([$semestreCode]);
                $val = $stmt->fetchColumn();
                if ($val) $labels['semestre'] = (string)$val;
            }
            if (!empty($niveauCode)) {
                $stmt = $db->prepare("SELECT libelle_niveau FROM " . TABLES::NIVEAUX . " WHERE code_niveau = ? LIMIT 1");
                $stmt->execute([$niveauCode]);
                $val = $stmt->fetchColumn();
                if ($val) $labels['niveau'] = (string)$val;
            }
            if (!empty($classeCode)) {
                $stmt = $db->prepare("SELECT libelle_classe FROM " . TABLES::CLASSES . " WHERE code_classe = ? LIMIT 1");
                $stmt->execute([$classeCode]);
                $val = $stmt->fetchColumn();
                if ($val) $labels['classe'] = (string)$val;
            }
        } catch (Exception $e) {
            error_log("ModelAbsence::getFilterLabels: " . $e->getMessage());
        }

        return $labels;
    }

    public function getByIdWithDetails($id): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT *, etu_nom AS nom_etudiant, etu_prenom AS prenom_etudiant
                FROM " . VIEWS::ABSENCES_COMPLETES . "
                WHERE id_absence = ? OR code_absence = ?
                LIMIT 1
            ");
            $stmt->execute([$id, (string)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelAbsence::getByIdWithDetails error: " . $e->getMessage());
            return null;
        }
    }

    public function getStudentsByClasse(string $classeCode, string $anneeCode): array
    {
        if (empty($anneeCode)) {
            return [];
        }

        try {
            $stmt = $this->getCon()->prepare("
                SELECT i.code_inscription, e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
                FROM " . TABLES::INSCRIPTIONS . " i
                INNER JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
                WHERE i.classe_code = ? AND i.annee_code = ? AND (i.statut_inscription != '" . STATUTS::ANNULE . "' OR i.statut_inscription IS NULL)
                ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
            ");
            $stmt->execute([$classeCode, $anneeCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelAbsence::getStudentsByClasse error: " . $e->getMessage());
            return [];
        }
    }

    public function getExistingAbsences(string $classeCode, string $dateAbsence, ?string $matiereCode = null): array
    {
        try {
            $sqlAbs = "
                SELECT abs.*, ins.etudiant_code, abs.motif as motif_absence
                FROM " . TABLES::ABSENCES . " abs
                INNER JOIN " . TABLES::INSCRIPTIONS . " ins ON ins.code_inscription = abs.inscription_code
                WHERE ins.classe_code = ? AND abs.date_absence = ? AND ins.statut_inscription != '" . STATUTS::ANNULE . "'
            ";
            $paramsAbs = [$classeCode, $dateAbsence];
            if (!empty($matiereCode)) {
                $sqlAbs .= " AND abs.matiere_code = ?";
                $paramsAbs[] = $matiereCode;
            }
            $stmtAbs = $this->getCon()->prepare($sqlAbs);
            $stmtAbs->execute($paramsAbs);
            $rows = $stmtAbs->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $existingAbsences = [];
            foreach ($rows as $r) {
                $existingAbsences[$r['etudiant_code']] = $r;
            }
            return $existingAbsences;
        } catch (Exception $e) {
            error_log("ModelAbsence::getExistingAbsences error: " . $e->getMessage());
            return [];
        }
    }

    public function getInscriptionCodeByEtudiant(string $etudiantCode, ?string $classeCode = null, ?string $anneeCode = null): ?string
    {
        $sql = "SELECT code_inscription FROM " . TABLES::INSCRIPTIONS . " WHERE etudiant_code = ? AND statut_inscription != '" . STATUTS::ANNULE . "'";
        $params = [$etudiantCode];
        if (!empty($classeCode)) {
            $sql .= " AND classe_code = ?";
            $params[] = $classeCode;
        }
        if (!empty($anneeCode)) {
            $sql .= " AND annee_code = ?";
            $params[] = $anneeCode;
        }
        $sql .= " ORDER BY id_inscription DESC LIMIT 1";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() ?: null;
    }

    public function saveBatchAbsences(
        string $classeCode,
        string $dateAbsence,
        ?string $matiereCode,
        float $dureeHeures,
        array $absencesData,
        string $anneeCode,
        string $etabCode,
        ?string $semestreCode = null
    ): bool {
        $db = $this->getCon();
        $db->beginTransaction();

        try {
            $matCodeNorm = $matiereCode ?? '';
            $semestreCodeNorm = !empty($semestreCode)
                ? trim($semestreCode)
                : $this->resolveSemestreCodeForDate($anneeCode, $dateAbsence);
            $now = date('Y-m-d H:i:s');

            // 1. Pré-chargement en 1 requête des inscriptions de la classe
            $inscriptionsByEtudiant = [];
            if (!empty($classeCode)) {
                foreach ($this->getStudentsByClasse($classeCode, $anneeCode) as $st) {
                    $inscriptionsByEtudiant[$st['code_etudiant']] = $st['code_inscription'];
                }
            }

            // Compléter en 1 seule requête batch les éventuels étudiants absents du pré-chargement classe
            $missingEtudiants = [];
            foreach (array_keys($absencesData) as $etuCode) {
                if (!isset($inscriptionsByEtudiant[$etuCode])) {
                    $missingEtudiants[] = (string)$etuCode;
                }
            }
            if (!empty($missingEtudiants)) {
                $phEtu = implode(',', array_fill(0, count($missingEtudiants), '?'));
                $sqlMiss = "
                    SELECT etudiant_code, code_inscription
                    FROM " . TABLES::INSCRIPTIONS . "
                    WHERE etudiant_code IN ({$phEtu})
                      AND (statut_inscription != '" . STATUTS::ANNULE . "' OR statut_inscription IS NULL)
                ";
                $missParams = $missingEtudiants;
                if (!empty($classeCode)) {
                    $sqlMiss .= " AND classe_code = ?";
                    $missParams[] = $classeCode;
                }
                if (!empty($anneeCode)) {
                    $sqlMiss .= " AND annee_code = ?";
                    $missParams[] = $anneeCode;
                }
                $sqlMiss .= " ORDER BY id_inscription ASC";
                $stmtMiss = $db->prepare($sqlMiss);
                $stmtMiss->execute($missParams);
                foreach ($stmtMiss->fetchAll(PDO::FETCH_ASSOC) ?: [] as $mr) {
                    $inscriptionsByEtudiant[$mr['etudiant_code']] = $mr['code_inscription'];
                }
            }

            // Collecter tous les codes d'inscription concernés par le lot
            $targetInscCodes = [];
            foreach (array_keys($absencesData) as $etuCode) {
                if (!empty($inscriptionsByEtudiant[$etuCode])) {
                    $targetInscCodes[] = $inscriptionsByEtudiant[$etuCode];
                }
            }
            $targetInscCodes = array_values(array_unique($targetInscCodes));

            // 2. Pré-chargement en 1 seule requête des absences déjà enregistrées pour (date, matière) — zéro SELECT en boucle !
            $existingByInsc = [];
            if (!empty($targetInscCodes)) {
                $phInsc = implode(',', array_fill(0, count($targetInscCodes), '?'));
                $stmtPreload = $db->prepare("
                    SELECT id_absence, inscription_code
                    FROM " . TABLES::ABSENCES . "
                    WHERE inscription_code IN ({$phInsc})
                      AND date_absence = ?
                      AND (matiere_code = ? OR (matiere_code = '' AND ? = ''))
                ");
                $stmtPreload->execute(array_merge($targetInscCodes, [$dateAbsence, $matCodeNorm, $matCodeNorm]));
                foreach ($stmtPreload->fetchAll(PDO::FETCH_ASSOC) ?: [] as $exRow) {
                    $existingByInsc[$exRow['inscription_code']] = (int)$exRow['id_absence'];
                }
            }

            $stmtUpd = $db->prepare("
                UPDATE " . TABLES::ABSENCES . " 
                SET duree_heures = ?, justifiee = ?, motif = ?, semestre_code = ?
                WHERE id_absence = ?
            ");
            $stmtIns = $db->prepare("
                INSERT INTO " . TABLES::ABSENCES . " (
                    code_absence, inscription_code, emploi_code, matiere_code, date_absence, 
                    duree_heures, justifiee, motif, annee_code, semestre_code, etablissement_code, created_at_absence
                ) VALUES (?, ?, '', ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtDel = $db->prepare("DELETE FROM " . TABLES::ABSENCES . " WHERE id_absence = ?");

            $usedCodes = [];
            foreach ($absencesData as $etudiantCode => $info) {
                $isAbsent = !empty($info['is_absent']);
                $justifiee = !empty($info['justifiee']) ? $info['justifiee'] : 'non';
                $motif = trim($info['motif'] ?? ($info['motif_absence'] ?? ''));

                $inscCode = $inscriptionsByEtudiant[$etudiantCode] ?? null;
                if (empty($inscCode)) {
                    continue;
                }

                $existingId = $existingByInsc[$inscCode] ?? null;

                if ($isAbsent) {
                    if ($existingId) {
                        $stmtUpd->execute([$dureeHeures, $justifiee, $motif, $semestreCodeNorm, $existingId]);
                    } else {
                        do {
                            $codeAbs = 'ABS-' . strtoupper(bin2hex(random_bytes(6)));
                        } while (isset($usedCodes[$codeAbs]));
                        $usedCodes[$codeAbs] = true;

                        $stmtIns->execute([
                            $codeAbs,
                            $inscCode,
                            $matCodeNorm,
                            $dateAbsence,
                            $dureeHeures,
                            $justifiee,
                            $motif,
                            $anneeCode,
                            $semestreCodeNorm,
                            $etabCode,
                            $now
                        ]);
                    }
                } else {
                    if ($existingId) {
                        $stmtDel->execute([$existingId]);
                    }
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("ModelAbsence::saveBatchAbsences error: " . $e->getMessage());
            throw $e;
        }
    }
}
