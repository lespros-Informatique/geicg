<?php

class ModelMaquette extends BaseModel
{
    protected string $table = TABLES::MAQUETTES_PEDAGOGIQUES;
    protected string $primaryKey = 'id_maquette';
    protected ?string $statusField = 'statut_maquette';
    protected ?string $createdAtField = 'created_at_maquette';
    protected ?string $updatedAtField = 'updated_at_maquette';

    /**
     * Récupère les filières actives regroupées par code_niveau (via filiere_cycles et classes)
     */
    public function getFilieresByNiveauMap(): array
    {
        $db = $this->getCon();
        $sql = "
            SELECT DISTINCT
                src.niveau_code,
                f.code_filiere,
                f.libelle_filiere,
                f.slug_filiere
            FROM (
                SELECT fc.niveau_code, fc.filiere_code
                FROM " . TABLES::FILIERE_CYCLES . " fc
                WHERE (fc.statut_filiere_cycle = '" . STATUTS::ACTIF . "' OR fc.statut_filiere_cycle IS NULL)
                  AND fc.niveau_code IS NOT NULL AND fc.niveau_code != ''
                  AND fc.filiere_code IS NOT NULL AND fc.filiere_code != ''
                UNION
                SELECT cl.niveau_code, cl.filiere_code
                FROM " . TABLES::CLASSES . " cl
                WHERE (cl.statut_classe = '" . STATUTS::ACTIF . "' OR cl.statut_classe IS NULL)
                  AND cl.niveau_code IS NOT NULL AND cl.niveau_code != ''
                  AND cl.filiere_code IS NOT NULL AND cl.filiere_code != ''
            ) src
            INNER JOIN " . TABLES::FILIERES . " f ON f.code_filiere = src.filiere_code
            WHERE (f.statut_filiere = '" . STATUTS::ACTIF . "' OR f.statut_filiere IS NULL)
            ORDER BY f.libelle_filiere ASC
        ";

        try {
            $stmt = $db->query($sql);
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $map = [];
            foreach ($rows as $row) {
                $niv = $row['niveau_code'];
                if (!isset($map[$niv])) {
                    $map[$niv] = [];
                }
                $map[$niv][] = [
                    'code_filiere'    => $row['code_filiere'],
                    'libelle_filiere' => $row['libelle_filiere'],
                    'slug_filiere'    => $row['slug_filiere'] ?? ''
                ];
            }
            return $map;
        } catch (Exception $e) {
            error_log("ModelMaquette::getFilieresByNiveauMap error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère la liste des matières configurées dans la maquette pour une filière, un niveau et une année
     */
    public function getMaquetteForFiliereNiveau(string $filiereCode, string $niveauCode, ?string $anneeCode = null): array
    {
        $db = $this->getCon();
        $anneeCode = $anneeCode ?: (Session::get('annee_active_code') ?: '');

        $sql = "
            SELECT *
            FROM " . VIEWS::MAQUETTES_COMPLETES . "
            WHERE filiere_code = ? AND niveau_code = ?
              AND (annee_code = ? OR ? = '')
            ORDER BY 
                CASE WHEN categorie_matiere = '" . CATEGORIES_MATIERES::GENERALE . "' THEN 1 ELSE 2 END ASC,
                libelle_matiere ASC
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$filiereCode, $niveauCode, $anneeCode, $anneeCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelMaquette::getMaquetteForFiliereNiveau error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère la maquette pédagogique directement à partir du code classe
     */
    public function getMaquetteForClasse(string $classeCode, ?string $anneeCode = null): array
    {
        $db = $this->getCon();
        try {
            $stmtCl = $db->prepare("SELECT filiere_code, niveau_code, annee_code FROM " . TABLES::CLASSES . " WHERE code_classe = ? LIMIT 1");
            $stmtCl->execute([$classeCode]);
            $cl = $stmtCl->fetch(PDO::FETCH_ASSOC);
            if (!$cl) return [];

            $targetAnnee = !empty($anneeCode) ? $anneeCode : ($cl['annee_code'] ?? Session::get('annee_active_code'));
            return $this->getMaquetteForFiliereNiveau($cl['filiere_code'], $cl['niveau_code'], $targetAnnee);
        } catch (Exception $e) {
            error_log("ModelMaquette::getMaquetteForClasse error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère la liste filtrée pour DataTable
     */
    public function getFilteredList(?string $anneeCode = null, ?string $filiereCode = null, ?string $niveauCode = null, ?string $categorie = null): array
    {
        $db = $this->getCon();
        $sql = "
            SELECT v.*, f.slug_filiere, n.slug_niveau
            FROM " . VIEWS::MAQUETTES_COMPLETES . " v
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = v.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = v.niveau_code
            WHERE 1=1
        ";
        $params = [];

        if (!empty($anneeCode)) {
            $sql .= " AND v.annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($filiereCode)) {
            $sql .= " AND (v.filiere_code = ? OR f.slug_filiere = ?)";
            $params[] = $filiereCode;
            $params[] = $filiereCode;
        }
        if (!empty($niveauCode)) {
            $sql .= " AND (v.niveau_code = ? OR n.slug_niveau = ?)";
            $params[] = $niveauCode;
            $params[] = $niveauCode;
        }
        if (!empty($categorie)) {
            $sql .= " AND v.categorie_matiere = ?";
            $params[] = strtoupper(trim($categorie));
        }

        $sql .= " ORDER BY v.libelle_annee DESC, v.libelle_filiere ASC, v.libelle_niveau ASC, v.categorie_matiere ASC, v.libelle_matiere ASC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelMaquette::getFilteredList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Enregistre (insertion ou mise à jour) une matière dans la maquette
     */
    public function saveMatiereToMaquette(array $data): array
    {
        $db = $this->getCon();

        $filiereCode = trim($data['filiere_code'] ?? '');
        $niveauCode = trim($data['niveau_code'] ?? '');
        $matiereCode = trim($data['matiere_code'] ?? '');
        $anneeCode = trim($data['annee_code'] ?? (Session::get('annee_active_code') ?: ''));
        $categorie = in_array(strtoupper(trim($data['categorie_matiere'] ?? '')), [CATEGORIES_MATIERES::GENERALE, CATEGORIES_MATIERES::PROFESSIONNELLE]) 
                     ? strtoupper(trim($data['categorie_matiere'])) 
                     : CATEGORIES_MATIERES::GENERALE;
        $coefficient = (float)($data['coefficient'] ?? 1.00);
        if ($coefficient <= 0) $coefficient = 1.00;
        $statut = ($data['statut_maquette'] ?? STATUTS::ACTIF) === STATUTS::INACTIF ? STATUTS::INACTIF : STATUTS::ACTIF;
        $userCode = $data['user_code'] ?? Session::get('user_code') ?? null;
        $etablissementCode = $data['etablissement_code'] ?? Session::get('etablissement_code') ?? 'ETA-052CG';
        $idMaquette = !empty($data['id_maquette']) ? (int)$data['id_maquette'] : null;

        if (empty($filiereCode) || empty($niveauCode) || empty($matiereCode) || empty($anneeCode)) {
            return ['status' => 0, 'message' => 'Filière, Niveau, Matière et Année académique sont obligatoires.'];
        }

        try {
            if ($idMaquette) {
                // Vérifier doublon éventuel sur une autre ligne
                $chk = $db->prepare("SELECT id_maquette FROM " . TABLES::MAQUETTES_PEDAGOGIQUES . " WHERE annee_code = ? AND filiere_code = ? AND niveau_code = ? AND matiere_code = ? AND id_maquette != ? LIMIT 1");
                $chk->execute([$anneeCode, $filiereCode, $niveauCode, $matiereCode, $idMaquette]);
                if ($chk->fetch()) {
                    return ['status' => 0, 'message' => 'Cette matière est déjà enregistrée dans la maquette pour cette filière et ce niveau.'];
                }

                $stmt = $db->prepare("
                    UPDATE " . TABLES::MAQUETTES_PEDAGOGIQUES . "
                    SET filiere_code = ?, niveau_code = ?, matiere_code = ?, annee_code = ?,
                        categorie_matiere = ?, coefficient = ?, statut_maquette = ?, user_code = ?
                    WHERE id_maquette = ?
                ");
                $stmt->execute([
                    $filiereCode, $niveauCode, $matiereCode, $anneeCode,
                    $categorie, $coefficient, $statut, $userCode,
                    $idMaquette
                ]);

                return ['status' => 1, 'message' => 'Matière mise à jour dans la maquette avec succès.'];
            } else {
                // Insertion : vérifier unicité
                $chk = $db->prepare("SELECT id_maquette FROM " . TABLES::MAQUETTES_PEDAGOGIQUES . " WHERE annee_code = ? AND filiere_code = ? AND niveau_code = ? AND matiere_code = ? LIMIT 1");
                $chk->execute([$anneeCode, $filiereCode, $niveauCode, $matiereCode]);
                if ($chk->fetch()) {
                    return ['status' => 0, 'message' => 'Cette matière est déjà présente dans la maquette de cette filière pour cette année.'];
                }

                $codeMaquette = 'MAQ-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

                $stmt = $db->prepare("
                    INSERT INTO " . TABLES::MAQUETTES_PEDAGOGIQUES . "
                    (code_maquette, annee_code, filiere_code, niveau_code, matiere_code, categorie_matiere, coefficient, etablissement_code, statut_maquette, user_code)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $codeMaquette, $anneeCode, $filiereCode, $niveauCode, $matiereCode,
                    $categorie, $coefficient, $etablissementCode, $statut, $userCode
                ]);

                return ['status' => 1, 'message' => 'Matière ajoutée à la maquette pédagogique avec succès.', 'code_maquette' => $codeMaquette];
            }
        } catch (Exception $e) {
            error_log("ModelMaquette::saveMatiereToMaquette error: " . $e->getMessage());
            return ['status' => 0, 'message' => 'Erreur technique lors de l\'enregistrement : ' . $e->getMessage()];
        }
    }

    /**
     * Dupliquer la maquette d'une année source vers une année cible
     */
    public function duplicateMaquette(string $sourceAnneeCode, string $targetAnneeCode, ?string $filiereCode = null, ?string $niveauCode = null): array
    {
        $db = $this->getCon();
        $userCode = Session::get('user_code');
        $etablissementCode = Session::get('etablissement_code') ?: 'ETA-052CG';

        if (empty($sourceAnneeCode) || empty($targetAnneeCode)) {
            return ['status' => 0, 'message' => 'Année source et année cible requises.'];
        }
        if ($sourceAnneeCode === $targetAnneeCode) {
            return ['status' => 0, 'message' => 'L\'année source et l\'année cible doivent être différentes.'];
        }

        try {
            $sqlSource = "SELECT * FROM " . TABLES::MAQUETTES_PEDAGOGIQUES . " WHERE annee_code = ?";
            $params = [$sourceAnneeCode];
            if (!empty($filiereCode)) {
                $sqlSource .= " AND filiere_code = ?";
                $params[] = $filiereCode;
            }
            if (!empty($niveauCode)) {
                $sqlSource .= " AND niveau_code = ?";
                $params[] = $niveauCode;
            }

            $stmtSource = $db->prepare($sqlSource);
            $stmtSource->execute($params);
            $items = $stmtSource->fetchAll(PDO::FETCH_ASSOC);

            if (empty($items)) {
                return ['status' => 0, 'message' => 'Aucune maquette trouvée pour l\'année source sélectionnée.'];
            }

            $imported = 0;
            $skipped = 0;

            $stmtCheck = $db->prepare("SELECT id_maquette FROM " . TABLES::MAQUETTES_PEDAGOGIQUES . " WHERE annee_code = ? AND filiere_code = ? AND niveau_code = ? AND matiere_code = ? LIMIT 1");
            $stmtInsert = $db->prepare("
                INSERT INTO " . TABLES::MAQUETTES_PEDAGOGIQUES . "
                (code_maquette, annee_code, filiere_code, niveau_code, matiere_code, categorie_matiere, coefficient, etablissement_code, statut_maquette, user_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($items as $row) {
                $stmtCheck->execute([$targetAnneeCode, $row['filiere_code'], $row['niveau_code'], $row['matiere_code']]);
                if ($stmtCheck->fetch()) {
                    $skipped++;
                    continue;
                }

                $newCode = 'MAQ-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                $stmtInsert->execute([
                    $newCode,
                    $targetAnneeCode,
                    $row['filiere_code'],
                    $row['niveau_code'],
                    $row['matiere_code'],
                    $row['categorie_matiere'],
                    $row['coefficient'],
                    $etablissementCode,
                    $row['statut_maquette'] ?? STATUTS::ACTIF,
                    $userCode
                ]);
                $imported++;
            }

            return [
                'status' => 1,
                'message' => "Reconduction terminée : {$imported} matière(s) reconduite(s), {$skipped} déjà existante(s).",
                'imported' => $imported,
                'skipped' => $skipped
            ];
        } catch (Exception $e) {
            error_log("ModelMaquette::duplicateMaquette error: " . $e->getMessage());
            return ['status' => 0, 'message' => 'Erreur lors de la reconduction : ' . $e->getMessage()];
        }
    }
}

