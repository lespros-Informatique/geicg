<?php

class ModelAbonnementPressing extends BaseModel
{
    protected string $table = 'abonnements_pressings';
    protected string $primaryKey = 'id_abonnement_pressing';
    protected ?string $statusField = 'statut_abonnement_pressing';
    protected ?string $createdAtField = 'created_at_abonnement_pressing';
}
