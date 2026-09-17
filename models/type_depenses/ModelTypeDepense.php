<?php

class ModelTypeDepense extends BaseModel
{
    protected string $table = 'type_depenses';
    protected string $primaryKey = 'id_type_depense';
    protected ?string $statusField = 'statut_type_depense';
    protected ?string $createdAtField = null;

    /**
     * Récupère tous les types de dépenses. Si la table est vide, initialise automatiquement les catégories par défaut.
     */
    public function getAll(): array
    {
        $pdo = $this->getCon();
        try {
            $count = (int)$pdo->query("SELECT COUNT(*) FROM `{$this->table}`")->fetchColumn();
            if ($count === 0) {
                $this->initDefaultTypes();
            }

            $sql = "SELECT * FROM `{$this->table}` ORDER BY `{$this->primaryKey}` ASC";
            return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all type_depenses error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Initialise les catégories de dépenses standard par défaut
     */
    public function initDefaultTypes(): void
    {
        $pdo = $this->getCon();
        $etabCode = $_SESSION['etablissement_active_code'] ?? '5454544456';
        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '5wBEh2OfI00frxk8ITPf';

        $defaults = [
            ['code' => 'TYP-DESK001', 'libelle' => 'Fournitures & Consommables de Bureau'],
            ['code' => 'TYP-RENT002', 'libelle' => 'Loyer & Charges Locatives'],
            ['code' => 'TYP-ELEC003', 'libelle' => 'Électricité, Eau & Énergie'],
            ['code' => 'TYP-SALR004', 'libelle' => 'Salaires, Honoraires & Rémunérations'],
            ['code' => 'TYP-MAINT05', 'libelle' => 'Maintenance & Entretien des Locaux'],
            ['code' => 'TYP-ITCOMP6', 'libelle' => 'Matériel Informatique & Équipements'],
            ['code' => 'TYP-COMM007', 'libelle' => 'Téléphone, Internet & Communication'],
            ['code' => 'TYP-TRAN008', 'libelle' => 'Transport & Frais de Déplacement'],
            ['code' => 'TYP-BANK009', 'libelle' => 'Frais Bancaires & Services Financiers'],
            ['code' => 'TYP-MISC010', 'libelle' => 'Dépenses Diverses & Imprévus']
        ];

        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE `code_type_depense` = ?");
        $stmtInsert = $pdo->prepare("
            INSERT INTO `{$this->table}` (code_type_depense, libelle_type_depense, etablissement_code, user_code)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($defaults as $d) {
            $stmtCheck->execute([$d['code']]);
            if ((int)$stmtCheck->fetchColumn() === 0) {
                $stmtInsert->execute([$d['code'], $d['libelle'], $etabCode, $userCode]);
            }
        }
    }
}
