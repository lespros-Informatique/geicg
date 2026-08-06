<?php

class ModelArticle extends BaseModel
{
    protected string $table = 'articles_pressings';
    protected string $primaryKey = 'id_article';
    protected ?string $statusField = 'statut_article';
    protected ?string $createdAtField = 'created_at_article';

    public function __construct()
    {
        parent::__construct();
    }

    public function getByPressing(string $pressingCode): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE pressing_code = ? ORDER BY {$this->createdAtField} DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ModelArticle::getByPressing error: " . $e->getMessage());
            return [];
        }
    }

    public function getByCategory(string $categoryCode): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE categorie_article_code = ? AND statut_article = 'actif' ORDER BY libelle_article ASC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$categoryCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ModelArticle::getByCategory error: " . $e->getMessage());
            return [];
        }
    }
}
