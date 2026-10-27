<?php

class ModelPaiement extends BaseModel
{
    protected string $table = 'paiements';
    protected string $primaryKey = 'id_paiement';
    protected ?string $statusField = 'statut_paiement';
    protected ?string $createdAtField = 'date_paiement';

    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null, ?string $dateDebut = null, ?string $dateFin = null, ?string $canalPaiement = null): array
    {
        $where = "WHERE (p.categorie_paiement IS NULL OR p.categorie_paiement != 'FRAIS_ANNEXES') AND (p.tranche_code IS NULL OR p.tranche_code != 'FRAIS_ANNEXES')";
        $params = [];
        if (!empty($anneeCode) && $anneeCode !== 'ALL') {
            $where .= " AND (p.annee_code = ? OR ins.annee_code = ?)";
            $params[] = $anneeCode;
            $params[] = $anneeCode;
        }
        if (!empty($niveauCode) && $niveauCode !== 'ALL') {
            $where .= " AND cl.niveau_code = ?";
            $params[] = $niveauCode;
        }
        if (!empty($classeCode) && $classeCode !== 'ALL') {
            $where .= " AND ins.classe_code = ?";
            $params[] = $classeCode;
        }
        if (!empty($canalPaiement) && $canalPaiement !== 'ALL') {
            $where .= " AND p.canal_paiement = ?";
            $params[] = $canalPaiement;
        }
        if (!empty($dateDebut)) {
            $where .= " AND DATE(p.date_paiement) >= ?";
            $params[] = $dateDebut;
        }
        if (!empty($dateFin)) {
            $where .= " AND DATE(p.date_paiement) <= ?";
            $params[] = $dateFin;
        }

        $sql = "
            SELECT p.*, 
                   e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
                   TRIM(CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, ''))) as etudiant_nom,
                   cl.libelle_classe, cl.niveau_code,
                   t.libelle_tranche, t.montant_tranche
            FROM paiements p
            LEFT JOIN inscriptions ins ON ins.code_inscription = p.inscription_code
            LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
            LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
            LEFT JOIN tranches_scolarite t ON t.code_tranche = p.tranche_code
            {$where}
            ORDER BY p.id_paiement DESC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById($id): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT p.*, 
                   e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant, e.photo_etudiant, e.code_etudiant,
                   ins.affectation_etat, ins.photo_inscription,
                   TRIM(CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, ''))) as etudiant_nom,
                   cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                   a.libelle_annee,
                   u.nom_user as nom_caissier, u.prenom_user as prenom_caissier,
                   ins.montant_scolarite_inscription,
                   t.libelle_tranche, t.montant_tranche, t.date_limite as date_limite_tranche
            FROM paiements p
            LEFT JOIN inscriptions ins ON ins.code_inscription = p.inscription_code
            LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
            LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
            LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
            LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
            LEFT JOIN annees a ON (a.code_annee = p.annee_code OR a.code_annee = ins.annee_code)
            LEFT JOIN users u ON u.code_user = p.user_code
            LEFT JOIN tranches_scolarite t ON t.code_tranche = p.tranche_code
            WHERE p.id_paiement = ? OR p.code_paiement = ?
            LIMIT 1
        ");
        $num = is_numeric($id) ? (int)$id : 0;
        $code = (string)$id;
        $stmt->execute([$num, $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    /**
     * Récupère la liste des paiements pour une inscription
     */
    public function getPaiementsByInscription(string $inscriptionCode, bool $excludeAnnule = true): array
    {
        $sql = "SELECT id_paiement, montant_paiement, date_paiement, tranche_code, categorie_paiement 
                FROM " . TABLES::PAIEMENTS . " 
                WHERE inscription_code = ?";
        if ($excludeAnnule) {
            $sql .= " AND statut_paiement != 'annule'";
        }
        $sql .= " ORDER BY date_paiement ASC";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute([$inscriptionCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Récupère les données d'inscription nécessaires pour un paiement
     */
    public function getInscriptionForPaiement(string $inscriptionCode): ?array
    {
        $sql = "SELECT i.*, c.filiere_code, c.niveau_code, c.libelle_classe, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
                FROM " . TABLES::INSCRIPTIONS . " i
                LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
                LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
                WHERE i.code_inscription = ? LIMIT 1";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute([$inscriptionCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Annule un paiement de scolarité
     */
    public function annulerPaiement(int $idPaiement, string $codePaiement, string $motif = ''): array
    {
        $db = $this->getCon();
        try {
            $stmt = $db->prepare("SELECT * FROM " . TABLES::PAIEMENTS . " WHERE id_paiement = ? OR code_paiement = ? LIMIT 1");
            $stmt->execute([$idPaiement, $codePaiement]);
            $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$paiement) {
                return ['success' => false, 'message' => "Paiement introuvable."];
            }

            if ($paiement['statut_paiement'] === 'annule') {
                return ['success' => false, 'message' => "Ce paiement est déjà annulé."];
            }

            $stmtUp = $db->prepare("UPDATE " . TABLES::PAIEMENTS . " SET statut_paiement = 'annule', motif_annulation = ?, updated_at_paiement = NOW() WHERE id_paiement = ?");
            $ok = $stmtUp->execute([$motif, $paiement['id_paiement']]);

            if ($ok) {
                return [
                    'success' => true,
                    'message' => "Le paiement a été annulé avec succès.",
                    'paiement' => $paiement
                ];
            }
            return ['success' => false, 'message' => "Erreur lors de l'annulation du paiement."];
        } catch (Exception $e) {
            error_log("ModelPaiement::annulerPaiement error: " . $e->getMessage());
            return ['success' => false, 'message' => "Erreur lors de l'annulation du paiement."];
        }
    }

    /**
     * Récupère la photo de l'étudiant / inscription rattachée
     */
    public function getPhotoInscription(string $inscriptionCode): ?array
    {
        $stmt = $this->getCon()->prepare("
            SELECT i.photo_inscription, e.photo_etudiant 
            FROM " . TABLES::INSCRIPTIONS . " i 
            LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code 
            WHERE i.code_inscription = ? LIMIT 1
        ");
        $stmt->execute([$inscriptionCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
