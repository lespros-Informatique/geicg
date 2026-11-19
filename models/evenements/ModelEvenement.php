<?php

class ModelEvenement extends BaseModel
{
    protected string $table = TABLES::EVENEMENTS;
    protected string $primaryKey = 'id_evenement';
    protected ?string $statusField = 'statut_evenement';
    protected ?string $createdAtField = 'date_creation_evenement';
}
