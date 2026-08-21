<?php

class ModelTranche extends BaseModel
{
    protected string $table = 'tranches_scolarite';
    protected string $primaryKey = 'id_tranche';
    protected ?string $statusField = 'statut_tranche';
    protected ?string $createdAtField = 'created_at_tranche';
}
