<?php

class ModelClient extends BaseModel
{
    protected string $table = 'clients';
    protected string $primaryKey = 'id_client';
    protected ?string $statusField = 'statut_client';
    protected ?string $createdAtField = 'created_at_client';

    public function __construct()
    {
        parent::__construct();
    }
}
