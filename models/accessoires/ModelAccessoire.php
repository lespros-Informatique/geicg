<?php

class ModelAccessoire extends BaseModel
{
    protected string $table = 'accessoires';
    protected string $primaryKey = 'id_accessoire';
    protected ?string $statusField = 'statut_accessoire';
    protected ?string $createdAtField = 'created_at_accessoire';
}
