<?php

class ModelScolarite extends BaseModel
{
    protected string $table = 'scolarites';
    protected string $primaryKey = 'id_scolarite';
    protected ?string $statusField = 'statut_scolarite';
    protected ?string $createdAtField = 'created_at_scolarite';
}
