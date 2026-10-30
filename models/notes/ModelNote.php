<?php

class ModelNote extends BaseModel
{
    protected string $table = TABLES::NOTES;
    protected string $primaryKey = 'id_note';
    protected ?string $statusField = 'statut_note';
    protected ?string $createdAtField = 'created_at_note';

    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null): array
    {
        $sql = "SELECT * FROM " . VIEWS::NOTES_COMPLETES . "";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "inscription_classe_code = ?";
            $params[] = $classeCode;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY id_note DESC ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all notes: " . $e->getMessage());
            return [];
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

        // 1. Si une composition est spécifiée
        if (!empty($compositionCode)) {
            $stmtCompMat = $db->prepare("
                SELECT DISTINCT m.code_matiere, m.libelle_matiere, 
                       COALESCE(em.coefficient, comp.coefficient, 1.00) AS coefficient,
                       TRIM(CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, ''))) AS prof_nom,
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
                GROUP BY m.code_matiere, m.libelle_matiere, coefficient, prof_nom
                ORDER BY m.libelle_matiere ASC
            ");
            $stmtCompMat->execute([$compositionCode, $classeCode]);
            $compItems = $stmtCompMat->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($compItems)) {
                return $compItems;
            }
        }

        // 2. Matières selon l'emploi du temps
        $stmtEdt = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   COALESCE(em.coefficient, 1.00) AS coefficient,
                   TRIM(GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ')) AS prof_nom,
                   1 AS is_assigned
            FROM " . TABLES::EMPLOIS_TEMPS . " edt
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = edt.matiere_code
            LEFT JOIN " . TABLES::ENSEIGNANT_MATIERE . " em ON (em.classe_code = edt.classe_code AND em.matiere_code = edt.matiere_code)
            LEFT JOIN " . TABLES::USERS . " u ON (u.code_user = edt.enseignant_code OR u.code_user = em.enseignant_code)
            WHERE edt.classe_code = ? 
              AND (edt.annee_code = ? OR ? IS NULL OR ? = '')
              AND (edt.statut_emploi = 'actif' OR edt.statut_emploi IS NULL)
            GROUP BY m.code_matiere, m.libelle_matiere, em.coefficient
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtEdt->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        $edtItems = $stmtEdt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($edtItems)) {
            return $edtItems;
        }

        // 3. Matières affectées à la classe via enseignant_matiere
        $stmtEm = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   COALESCE(em.coefficient, 1.00) AS coefficient,
                   TRIM(GROUP_CONCAT(DISTINCT CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) SEPARATOR ', ')) AS prof_nom,
                   1 AS is_assigned
            FROM " . TABLES::ENSEIGNANT_MATIERE . " em
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = em.matiere_code
            LEFT JOIN " . TABLES::USERS . " u ON u.code_user = em.enseignant_code
            WHERE em.classe_code = ?
              AND (em.annee_code = ? OR ? IS NULL OR ? = '')
              AND (em.statut_enseignant_matiere = 'actif' OR em.statut_enseignant_matiere IS NULL)
            GROUP BY m.code_matiere, m.libelle_matiere, em.coefficient
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtEm->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        $emItems = $stmtEm->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($emItems)) {
            return $emItems;
        }

        // 4. Matières liées au Niveau & Filière (composition_niveau_filiere)
        $stmtCnf = $db->prepare("
            SELECT DISTINCT m.code_matiere, m.libelle_matiere,
                   COALESCE(comp.coefficient, 1.00) AS coefficient,
                   '' AS prof_nom,
                   1 AS is_assigned
            FROM " . TABLES::COMPOSITION_MATIERES . " cm
            INNER JOIN " . TABLES::COMPOSITION_NIVEAU_FILIERE . " cnf ON cnf.code_composition_niveau_filiere = cm.composition_niveau_filiere_code
            INNER JOIN " . TABLES::CLASSES . " cl ON (cl.niveau_code = cnf.niveau_code AND cl.filiere_code = cnf.filiere_code)
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = cm.matiere_code
            INNER JOIN " . TABLES::COMPOSITIONS . " comp ON comp.code_composition = cm.composition_code
            WHERE cl.code_classe = ?
              AND (comp.annee_code = ? OR ? IS NULL OR ? = '')
            GROUP BY m.code_matiere, m.libelle_matiere, comp.coefficient
            ORDER BY m.libelle_matiere ASC
        ");
        $stmtCnf->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
        $cnfItems = $stmtCnf->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($cnfItems)) {
            return $cnfItems;
        }

        // 5. Repli : Catalogue des matières actives
        return $this->getActiveMatieresCatalogue();
    }

    public function getActiveMatieresCatalogue(): array
    {
        $stmtAll = $this->getCon()->query("
            SELECT code_matiere, libelle_matiere, 1.00 AS coefficient, '' AS prof_nom, 0 AS is_assigned
            FROM " . TABLES::MATIERES . " 
            WHERE statut_matiere = 'actif'
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
            WHERE i.classe_code = ? AND (i.annee_code = ? OR ? IS NULL OR ? = '') AND (i.statut_inscription != 'annule' OR i.statut_inscription IS NULL)
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
                     WHERE n.composition_code = ? AND n.statut_note = 'actif'";
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
                SET valeur_note = ?, observations = ?, updated_at_note = NOW(), statut_note = 'actif'
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
