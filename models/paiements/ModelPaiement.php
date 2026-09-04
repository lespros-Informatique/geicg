<?php

class ModelPaiement extends BaseModel
{
    protected string $table = 'paiements';
    protected string $primaryKey = 'id_paiement';
    protected ?string $statusField = 'statut_paiement';
    protected ?string $createdAtField = 'date_paiement';

    public function getAll(?string $anneeCode = null): array
    {
        $where = "";
        $params = [];
        if (!empty($anneeCode)) {
            $where = "WHERE (p.annee_code = ? OR ins.annee_code = ?)";
            $params = [$anneeCode, $anneeCode];
        }

        $sql = "
            SELECT p.*, 
                   e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
                   TRIM(CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, ''))) as etudiant_nom,
                   cl.libelle_classe,
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
                   e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant,
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
            WHERE p.id_paiement = ?
            LIMIT 1
        ");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }
}
