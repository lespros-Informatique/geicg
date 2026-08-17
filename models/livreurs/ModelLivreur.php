<?php

class ModelLivreur extends BaseModel
{
    protected string $table = 'livreurs';
    protected string $primaryKey = 'id_livreur';
    protected ?string $statusField = 'statut_livreur';
    protected ?string $createdAtField = 'created_at_livreur';
}
