<?php

class ModelVille extends BaseModel
{
    protected string $table = 'villes';
    protected string $primaryKey = 'id_ville';
    protected ?string $statusField = 'statut_ville';
    protected ?string $createdAtField = 'created_at_ville';
}
