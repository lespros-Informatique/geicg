<?php

class ModelService extends BaseModel
{
    protected string $table = 'services';
    protected string $primaryKey = 'id_service';
    protected ?string $statusField = 'statut_service';
    protected ?string $createdAtField = 'created_at_service';
}
