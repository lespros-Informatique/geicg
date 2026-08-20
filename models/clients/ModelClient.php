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

    public function getByPressing(string $pressingCode): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT DISTINCT c.* 
            FROM " . TABLES::CLIENTS . " c
            LEFT JOIN " . TABLES::COMMANDES . " cmd ON cmd.client_code = c.code_client
            WHERE c.pressing_code = ? OR cmd.pressing_code = ?
            ORDER BY c.created_at_client DESC
        ");
        $stmt->execute([$pressingCode, $pressingCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
