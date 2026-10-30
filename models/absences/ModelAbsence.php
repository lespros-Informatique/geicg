<?php

class ModelAbsence extends BaseModel
{
    protected string $table = TABLES::ABSENCES;
    protected string $primaryKey = 'id_absence';
    protected ?string $createdAtField = 'created_at_absence';

    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null): array
    {
        $sql = "SELECT * FROM " . VIEWS::ABSENCES_COMPLETES . "";
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "(annee_code = ? OR inscription_annee_code = ?)";
            $params[] = $anneeCode;
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode)) {
            $conditions[] = "classe_code = ?";
            $params[] = $classeCode;
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

    public function getFormDataDependencies(): array
    {
        require_once __DIR__ . '/../classes/ModelClasse.php';
        require_once __DIR__ . '/../matieres/ModelMatiere.php';

        $classes = (new ModelClasse())->getAll();
        $matieres = (new ModelMatiere())->getAll();

        $sql = "
            SELECT DISTINCT e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
                   i.code_inscription, i.classe_code, cl.libelle_classe
            FROM " . TABLES::ETUDIANTS . " e
            LEFT JOIN " . TABLES::INSCRIPTIONS . " i ON i.etudiant_code = e.code_etudiant
            LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
            ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC
        ";
        $etudiantsDetails = $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'classes' => $classes,
            'matieres' => $matieres,
            'etudiantsDetails' => $etudiantsDetails
        ];
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
        try {
            $stmt = $this->getCon()->prepare("
                SELECT i.code_inscription, e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
                FROM " . TABLES::INSCRIPTIONS . " i
                INNER JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
                WHERE i.classe_code = ? AND (i.annee_code = ? OR ? IS NULL OR ? = '') AND (i.statut_inscription != 'annule' OR i.statut_inscription IS NULL)
                ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
            ");
            $stmt->execute([$classeCode, $anneeCode, $anneeCode, $anneeCode]);
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
                WHERE ins.classe_code = ? AND abs.date_absence = ?
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
        $sql = "SELECT code_inscription FROM " . TABLES::INSCRIPTIONS . " WHERE etudiant_code = ?";
        $params = [$etudiantCode];
        if (!empty($classeCode)) {
            $sql .= " AND classe_code = ?";
            $params[] = $classeCode;
        }
        if (!empty($anneeCode)) {
            $sql .= " AND (annee_code = ? OR annee_code = '' OR annee_code IS NULL)";
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
        string $etabCode
    ): bool {
        $db = $this->getCon();
        $validator = new Validator();
        $db->beginTransaction();

        try {
            $matCodeNorm = $matiereCode ?? '';
            $stmtCheck = $db->prepare("
                SELECT id_absence FROM " . TABLES::ABSENCES . " 
                WHERE inscription_code = ? AND date_absence = ? AND (matiere_code = ? OR (matiere_code = '' AND ? = ''))
            ");
            $stmtUpd = $db->prepare("
                UPDATE " . TABLES::ABSENCES . " 
                SET duree_heures = ?, justifiee = ?, motif = ?
                WHERE id_absence = ?
            ");
            $stmtIns = $db->prepare("
                INSERT INTO " . TABLES::ABSENCES . " (
                    code_absence, inscription_code, emploi_code, matiere_code, date_absence, 
                    duree_heures, justifiee, motif, annee_code, etablissement_code, created_at_absence
                ) VALUES (?, ?, '', ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmtDel = $db->prepare("DELETE FROM " . TABLES::ABSENCES . " WHERE id_absence = ?");

            // Pré-chargement en 1 seule requête des inscriptions de la classe
            $inscriptionsByEtudiant = [];
            if (!empty($classeCode)) {
                foreach ($this->getStudentsByClasse($classeCode, $anneeCode) as $st) {
                    $inscriptionsByEtudiant[$st['code_etudiant']] = $st['code_inscription'];
                }
            }

            foreach ($absencesData as $etudiantCode => $info) {
                $isAbsent = !empty($info['is_absent']);
                $justifiee = !empty($info['justifiee']) ? $info['justifiee'] : 'non';
                $motif = trim($info['motif'] ?? ($info['motif_absence'] ?? ''));

                $inscCode = $inscriptionsByEtudiant[$etudiantCode]
                    ?? $this->getInscriptionCodeByEtudiant($etudiantCode, $classeCode, $anneeCode);
                if (empty($inscCode)) {
                    continue;
                }

                $stmtCheck->execute([$inscCode, $dateAbsence, $matCodeNorm, $matCodeNorm]);
                $existingId = $stmtCheck->fetchColumn();

                if ($isAbsent) {
                    if ($existingId) {
                        $stmtUpd->execute([$dureeHeures, $justifiee, $motif, $existingId]);
                    } else {
                        $codeAbs = $validator->generateCode(TABLES::ABSENCES, 'code_absence', 'ABS-', 8);
                        $stmtIns->execute([
                            $codeAbs,
                            $inscCode,
                            $matCodeNorm,
                            $dateAbsence,
                            $dureeHeures,
                            $justifiee,
                            $motif,
                            $anneeCode,
                            $etabCode
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
