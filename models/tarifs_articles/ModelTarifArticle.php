<?php

class ModelTarifArticle extends BaseModel
{
    protected string $table = 'tarifs_articles';
    protected string $primaryKey = 'id_tarif';
    protected ?string $statusField = 'statut_tarif';
    protected ?string $createdAtField = 'created_at_tarif';
}
