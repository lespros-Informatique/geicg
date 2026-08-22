<?php

class ModelBulletin extends BaseModel
{
    protected string $table = 'bulletins';
    protected string $primaryKey = 'id_bulletin';
    protected ?string $statusField = 'statut_bulletin';
    protected ?string $createdAtField = 'created_at_bulletin';
}

