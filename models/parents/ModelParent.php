<?php

class ModelParent extends BaseModel
{
    protected string $table = TABLES::PARENTS;
    protected string $primaryKey = 'id_parent';
    protected ?string $statusField = 'statut_parent';
    protected ?string $createdAtField = 'created_at_parent';

    public function getParentDetails(int $id, ?string $anneeCode = null): ?array
    {
        try {
            $targetAnnee = $anneeCode ?: (string)Session::get('annee_active_code', '');
            $stmt = $this->getCon()->prepare("
                SELECT p.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant,
                       cl.libelle_classe
                FROM " . TABLES::PARENTS . " p
                LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = p.etudiant_code
                LEFT JOIN " . TABLES::INSCRIPTIONS . " ins ON (ins.etudiant_code = e.code_etudiant AND ins.statut_inscription != '" . STATUTS::ANNULE . "')
                LEFT JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = ins.classe_code
                WHERE p.id_parent = ?
                ORDER BY (ins.annee_code = ?) DESC, ins.id_inscription DESC
                LIMIT 1
            ");
            $stmt->execute([$id, $targetAnnee]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            return $item ?: null;
        } catch (Exception $e) {
            error_log("ModelParent::getParentDetails error: " . $e->getMessage());
            return null;
        }
    }
}
