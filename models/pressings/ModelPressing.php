<?php

class ModelPressing extends BaseModel
{
    protected string $table = 'pressings';
    protected string $primaryKey = 'id_pressing';
    protected ?string $statusField = 'statut_pressing';
    protected ?string $createdAtField = 'created_at_pressing';
}
