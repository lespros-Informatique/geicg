<?php

class ModelDocument extends BaseModel
{
    protected string $table = 'documents';
    protected string $primaryKey = 'id_document';
    protected ?string $statusField = 'statut_document';
    protected ?string $createdAtField = 'created_at_document';

    public function getDocumentsList(): array
    {
        try {
            $sql = "SELECT d.*, 
                           COALESCE(f.libelle_filiere, d.filiere_code, 'Toutes les filières') as libelle_filiere, 
                           COALESCE(n.libelle_niveau, d.niveaux_code, 'Tous les niveaux') as libelle_niveau,
                           DATE_FORMAT(d.created_at_document, '%d/%m/%Y') as date_creation_formatee
                    FROM documents d
                    LEFT JOIN filieres f ON (f.code_filiere = d.filiere_code OR f.libelle_filiere = d.filiere_code)
                    LEFT JOIN niveaux n ON (n.code_niveau = d.niveaux_code OR n.libelle_niveau = d.niveaux_code)
                    ORDER BY d.id_document DESC";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelDocument::getDocumentsList error: " . $e->getMessage());
            return [];
        }
    }
}
