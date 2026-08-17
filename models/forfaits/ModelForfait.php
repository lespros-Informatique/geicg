<?php

class ModelForfait extends BaseModel
{
    protected string $table = 'forfaits';
    protected string $primaryKey = 'id_forfait';
    protected ?string $statusField = 'statut_forfait';
    protected ?string $createdAtField = 'created_at_forfait';
}
