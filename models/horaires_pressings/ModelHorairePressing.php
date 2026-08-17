<?php

class ModelHorairePressing extends BaseModel
{
    protected string $table = 'horaires_pressings';
    protected string $primaryKey = 'id_horaire';
    protected ?string $createdAtField = 'created_at';
}
