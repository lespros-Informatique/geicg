<?php

class ModelNotification extends BaseModel
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id_notification';
    protected ?string $statusField = 'statut_notification';
    protected ?string $createdAtField = 'created_at_notification';
}
