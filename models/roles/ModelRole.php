<?php

class ModelRole extends BaseModel
{
    protected string $table = 'roles';
    protected string $primaryKey = 'id_role';
    protected ?string $statusField = 'statut_role';
}
