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

    public function getAllWithCategory(?string $pressingCode = null): array
    {
        try {
            $sql = "
                SELECT a.*, COALESCE(cat.libelle_categorie_article, a.categorie_article_code) as libelle_categorie
                FROM {$this->table} a
                LEFT JOIN " . TABLES::CATEGORIES_ARTICLES . " cat ON a.categorie_article_code = cat.code_categorie_article
            ";
            if ($pressingCode !== null && $pressingCode !== '') {
                $sql .= " WHERE a.pressing_code = ? ORDER BY cat.libelle_categorie_article ASC, a.libelle_article ASC";
                $stmt = $this->getCon()->prepare($sql);
                $stmt->execute([$pressingCode]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
            $sql .= " ORDER BY cat.libelle_categorie_article ASC, a.libelle_article ASC";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelArticle::getAllWithCategory error: " . $e->getMessage());
            return [];
        }
    }
}
