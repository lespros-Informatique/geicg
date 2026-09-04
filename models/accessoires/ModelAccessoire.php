<?php

class ModelAccessoire extends BaseModel
{
    protected string $table = 'accessoires';
    protected string $primaryKey = 'id_accessoire';
    protected ?string $statusField = 'statut_accessoire';
    protected ?string $createdAtField = 'created_at_accessoire';

    public function getAll(?string $anneeCode = null): array
    {
        $sql = "SELECT * FROM accessoires";
        $params = [];
        if (!empty($anneeCode)) {
            $sql .= " WHERE (annee_code = ? OR annee_code IS NULL OR annee_code = '')";
            $params[] = $anneeCode;
        }
        $sql .= " ORDER BY id_accessoire DESC";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all accessoires: " . $e->getMessage());
            return [];
        }
    }

    public function getDistributions(string $filter = 'all', ?string $anneeCode = null): array
    {
        $db = $this->getCon();
        $where = "WHERE (ai.statut_accessoire_inscription = 'actif' OR ai.statut_accessoire_inscription IS NULL)";
        $params = [];

        if (!empty($anneeCode)) {
            $where .= " AND (ai.annee_code = ? OR ai.annee_code IS NULL OR ai.annee_code = '')";
            $params[] = $anneeCode;
        }

        $sql = "
            SELECT 
                   i.code_inscription,
                   e.code_etudiant,
                   COALESCE(e.matricule_etudiant, '-') as matricule_etudiant,
                   COALESCE(e.nom_etudiant, '') as nom_etudiant,
                   COALESCE(e.prenom_etudiant, '') as prenom_etudiant,
                   COALESCE(e.telephone_etudiant, '-') as telephone_etudiant,
                   CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) as nom_complet,
                   COALESCE(c.libelle_classe, 'Non assignée') as libelle_classe,
                   COUNT(ai.id_accessoire_inscription) as total_kits,
                   SUM(CASE WHEN ai.etat_retrait = 'retire' THEN 1 ELSE 0 END) as total_retires,
                   SUM(CASE WHEN ai.etat_retrait = 'en_attente' THEN 1 ELSE 0 END) as total_en_attente,
                   GROUP_CONCAT(
                       CONCAT(
                           ai.id_accessoire_inscription, ':::',
                           COALESCE(a.libelle_accessoire, 'Kit'), ':::',
                           ai.etat_retrait, ':::',
                           COALESCE(DATE_FORMAT(ai.date_retrait, '%d/%m/%Y %H:%i'), '')
                       ) ORDER BY ai.id_accessoire_inscription ASC SEPARATOR '|||'
                   ) as kits_details
            FROM accessoire_inscription ai
            LEFT JOIN accessoires a ON a.code_accessoire = ai.accessoire_code
            LEFT JOIN inscriptions i ON i.code_inscription = ai.inscription_code
            LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN classes c ON c.code_classe = i.classe_code
            {$where}
            GROUP BY i.code_inscription, e.code_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, e.telephone_etudiant, c.libelle_classe
        ";

        if ($filter === 'en_attente') {
            $sql .= " HAVING SUM(CASE WHEN ai.etat_retrait = 'en_attente' THEN 1 ELSE 0 END) > 0";
        } elseif ($filter === 'retire') {
            $sql .= " HAVING SUM(CASE WHEN ai.etat_retrait = 'en_attente' THEN 1 ELSE 0 END) = 0";
        }

        $sql .= " ORDER BY i.id_inscription DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStats(?string $anneeCode = null): array
    {
        $db = $this->getCon();
        $whereAnnee = "";
        $params = [];
        if (!empty($anneeCode)) {
            $whereAnnee = " AND (annee_code = ? OR annee_code IS NULL OR annee_code = '')";
            $params[] = $anneeCode;
        }

        $stmtTot = $db->prepare("SELECT COUNT(*) FROM accessoire_inscription WHERE (statut_accessoire_inscription = 'actif' OR statut_accessoire_inscription IS NULL)" . $whereAnnee);
        $stmtTot->execute($params);
        $total = (int)($stmtTot->fetchColumn() ?: 0);

        $stmtAtt = $db->prepare("SELECT COUNT(*) FROM accessoire_inscription WHERE (statut_accessoire_inscription = 'actif' OR statut_accessoire_inscription IS NULL) AND etat_retrait = 'en_attente'" . $whereAnnee);
        $stmtAtt->execute($params);
        $enAttente = (int)($stmtAtt->fetchColumn() ?: 0);

        $stmtRet = $db->prepare("SELECT COUNT(*) FROM accessoire_inscription WHERE (statut_accessoire_inscription = 'actif' OR statut_accessoire_inscription IS NULL) AND etat_retrait = 'retire'" . $whereAnnee);
        $stmtRet->execute($params);
        $retire = (int)($stmtRet->fetchColumn() ?: 0);

        $totalTypes = (int)$db->query("SELECT COUNT(*) FROM accessoires WHERE statut_accessoire = 'actif'")->fetchColumn();
        $taux = $total > 0 ? round(($retire / $total) * 100) : 0;

        return [
            'total' => $total,
            'en_attente' => $enAttente,
            'retire' => $retire,
            'taux' => $taux,
            'total_types' => $totalTypes
        ];
    }
}
