<?php

class ModelPermission extends BaseModel
{
    protected string $table = 'permissions';
    protected string $primaryKey = 'id_permission';
    protected ?string $statusField = 'statut_permission';
}
