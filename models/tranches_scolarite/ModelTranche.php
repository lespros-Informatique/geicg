<?php

class ModelTranche extends BaseModel
{
    protected string $table = TABLES::TRANCHES_SCOLARITE;
    protected string $primaryKey = 'id_tranche';
    protected ?string $statusField = 'statut_tranche';
    protected ?string $createdAtField = 'created_at_tranche';

    public function getAll(?string $anneeCode = null, ?string $filiereCode = null, ?string $niveauCode = null, ?string $statut = null): array
    {
        $conditions = [];
        $params = [];
        if (!empty($anneeCode)) {
            $conditions[] = "(t.annee_code = ? OR s.annee_code = ?)";
            $params[] = $anneeCode;
            $params[] = $anneeCode;
        }
        if (!empty($filiereCode)) {
            $conditions[] = "(s.filiere_code = ? OR t.filiere_code = ?)";
            $params[] = $filiereCode;
            $params[] = $filiereCode;
        }
        if (!empty($niveauCode)) {
            $conditions[] = "(s.niveau_code = ? OR t.niveau_code = ?)";
            $params[] = $niveauCode;
            $params[] = $niveauCode;
        }
        if (!empty($statut)) {
            $conditions[] = "t.statut_tranche = ?";
            $params[] = $statut;
        }

        $where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "
            SELECT t.*, 
                   s.montant_scolarite, 
                   s.affectation_etat,
                   f.libelle_filiere, 
                   n.libelle_niveau, 
                   a.libelle_annee
            FROM " . TABLES::TRANCHES_SCOLARITE . " t
            LEFT JOIN " . TABLES::SCOLARITES . " s ON t.scolarite_code = s.code_scolarite
            LEFT JOIN " . TABLES::FILIERES . " f ON (s.filiere_code = f.code_filiere OR t.filiere_code = f.code_filiere)
            LEFT JOIN " . TABLES::NIVEAUX . " n ON (s.niveau_code = n.code_niveau OR t.niveau_code = n.code_niveau)
            LEFT JOIN " . TABLES::ANNEES . " a ON (s.annee_code = a.code_annee OR t.annee_code = a.code_annee)
            {$where}
            ORDER BY f.libelle_filiere ASC, n.libelle_niveau ASC, t.date_limite ASC, t.id_tranche ASC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById($id): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT t.*, 
                   s.montant_scolarite, 
                   s.affectation_etat,
                   f.libelle_filiere, 
                   n.libelle_niveau, 
                   a.libelle_annee
            FROM " . TABLES::TRANCHES_SCOLARITE . " t
            LEFT JOIN " . TABLES::SCOLARITES . " s ON t.scolarite_code = s.code_scolarite
            LEFT JOIN " . TABLES::FILIERES . " f ON (s.filiere_code = f.code_filiere OR t.filiere_code = f.code_filiere)
            LEFT JOIN " . TABLES::NIVEAUX . " n ON (s.niveau_code = n.code_niveau OR t.niveau_code = n.code_niveau)
            LEFT JOIN " . TABLES::ANNEES . " a ON (s.annee_code = a.code_annee OR t.annee_code = a.code_annee)
            WHERE t.id_tranche = ? OR t.code_tranche = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    /**
     * Valide le cumul des tranches par rapport au montant total de scolarité
     */
    public function validateCumul(string $scolariteCode, float $montantNouveau, ?int $excludeId = null): ?string
    {
        if (empty($scolariteCode)) {
            return 'Veuillez sélectionner une grille de scolarité valide.';
        }

        $scolariteModel = new ModelScolarite();
        $scolarite = $scolariteModel->getByElement('code_scolarite', $scolariteCode);

        if (!$scolarite) {
            return 'La scolarité sélectionnée est introuvable.';
        }

        $montantTotalScolarite = (float)($scolarite['montant_scolarite'] ?? 0);

        if ($montantNouveau <= 0) {
            return 'Le montant de la tranche doit être supérieur à 0 FCFA.';
        }

        $db = $this->getCon();
        $sql = "SELECT SUM(montant_tranche) FROM " . TABLES::TRANCHES_SCOLARITE . " WHERE scolarite_code = ? AND statut_tranche = 'actif'";
        $params = [$scolariteCode];

        if ($excludeId !== null) {
            $sql .= " AND id_tranche != ?";
            $params[] = (int)$excludeId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $sommeExistante = (float)($stmt->fetchColumn() ?: 0);

        $cumulFutur = $sommeExistante + $montantNouveau;

        if ($cumulFutur > $montantTotalScolarite) {
            $resteAutorise = max(0, $montantTotalScolarite - $sommeExistante);
            $totalFmt = number_format($montantTotalScolarite, 0, ',', ' ');
            $cumulFmt = number_format($cumulFutur, 0, ',', ' ');
            $resteFmt = number_format($resteAutorise, 0, ',', ' ');

            return "Impossible d'enregistrer : Le montant total de la scolarité ($totalFmt FCFA) est inférieur au cumul des tranches ($cumulFmt FCFA). Montant maximum autorisé pour cette tranche : $resteFmt FCFA.";
        }

        return null;
    }

    /**
     * Récupère une année par son code
     */
    public function getAnneeByCode(string $code): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT code_annee, libelle_annee FROM " . TABLES::ANNEES . " WHERE code_annee = ? LIMIT 1");
            $stmt->execute([$code]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }
}
