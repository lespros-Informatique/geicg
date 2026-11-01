<?php

class ModelMaquette extends BaseModel
{
    protected string $table = TABLES::MAQUETTES_PEDAGOGIQUES;
    protected string $primaryKey = 'id_maquette';
    protected ?string $statusField = 'statut_maquette';
    protected ?string $createdAtField = 'created_at_maquette';
    protected ?string $updatedAtField = 'updated_at_maquette';

    /**
     * Récupère la liste des matières configurées dans la maquette pour une filière, un niveau et une année
     */
    public function getMaquetteForFiliereNiveau(string $filiereCode, string $niveauCode, ?string $anneeCode = null): array
    {
        $db = $this->getCon();
        $anneeCode = $anneeCode ?: (Session::get('annee_active_code') ?: '');

        $sql = "
            SELECT mq.*, 
                   m.libelle_matiere,
                   f.libelle_filiere,
                   n.libelle_niveau,
                   a.libelle_annee
            FROM " . TABLES::MAQUETTES_PEDAGOGIQUES . " mq
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = mq.matiere_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = mq.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = mq.niveau_code
            LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = mq.annee_code
            WHERE mq.filiere_code = ? AND mq.niveau_code = ?
              AND (mq.annee_code = ? OR ? = '')
            ORDER BY 
                CASE WHEN mq.categorie_matiere = 'GENERALE' THEN 1 ELSE 2 END ASC,
                m.libelle_matiere ASC
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
            SELECT mq.*, 
                   m.libelle_matiere,
                   f.libelle_filiere,
                   n.libelle_niveau,
                   a.libelle_annee
            FROM " . TABLES::MAQUETTES_PEDAGOGIQUES . " mq
            INNER JOIN " . TABLES::MATIERES . " m ON m.code_matiere = mq.matiere_code
            LEFT JOIN " . TABLES::FILIERES . " f ON f.code_filiere = mq.filiere_code
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = mq.niveau_code
            LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = mq.annee_code
            WHERE 1=1
        ";
        $params = [];

        if (!empty($anneeCode)) {
            $sql .= " AND mq.annee_code = ?";
            $params[] = $anneeCode;
        }
        if (!empty($filiereCode)) {
            $sql .= " AND mq.filiere_code = ?";
            $params[] = $filiereCode;
        }
        if (!empty($niveauCode)) {
            $sql .= " AND mq.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($categorie)) {
            $sql .= " AND mq.categorie_matiere = ?";
            $params[] = strtoupper(trim($categorie));
        }

        $sql .= " ORDER BY a.libelle_annee DESC, f.libelle_filiere ASC, n.libelle_niveau ASC, mq.categorie_matiere ASC, m.libelle_matiere ASC";

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
        $categorie = in_array(strtoupper(trim($data['categorie_matiere'] ?? '')), ['GENERALE', 'PROFESSIONNELLE']) 
                     ? strtoupper(trim($data['categorie_matiere'])) 
                     : 'GENERALE';
        $coefficient = (float)($data['coefficient'] ?? 1.00);
        if ($coefficient <= 0) $coefficient = 1.00;
        $volumeHoraire = (int)($data['volume_horaire'] ?? 0);
        $statut = ($data['statut_maquette'] ?? 'actif') === 'inactif' ? 'inactif' : 'actif';
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
                        categorie_matiere = ?, coefficient = ?, volume_horaire = ?, statut_maquette = ?, user_code = ?
                    WHERE id_maquette = ?
                ");
                $stmt->execute([
                    $filiereCode, $niveauCode, $matiereCode, $anneeCode,
                    $categorie, $coefficient, $volumeHoraire, $statut, $userCode,
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
                    (code_maquette, annee_code, filiere_code, niveau_code, matiere_code, categorie_matiere, coefficient, volume_horaire, etablissement_code, statut_maquette, user_code)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $codeMaquette, $anneeCode, $filiereCode, $niveauCode, $matiereCode,
                    $categorie, $coefficient, $volumeHoraire, $etablissementCode, $statut, $userCode
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
                (code_maquette, annee_code, filiere_code, niveau_code, matiere_code, categorie_matiere, coefficient, volume_horaire, etablissement_code, statut_maquette, user_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                    $row['volume_horaire'] ?? 0,
                    $etablissementCode,
                    $row['statut_maquette'] ?? 'actif',
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
