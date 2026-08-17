<?php

class ModelMission extends BaseModel
{
    protected string $table = 'missions';
    protected string $primaryKey = 'id_mission';
    protected ?string $statusField = 'statut_mission';
    protected ?string $createdAtField = 'created_at_mission';
}
