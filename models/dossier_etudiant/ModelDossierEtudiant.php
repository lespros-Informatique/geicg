<?php

class ModelDossierEtudiant extends BaseModel
{
    protected string $table = 'dossier_etudiant';
    protected string $primaryKey = 'id_dossier_etudiant';
    protected ?string $statusField = null;
    protected ?string $createdAtField = 'created_at_dossier_etudiant';

    /**
     * Récupère le dossier complet (pièces exigées et leur statut de dépôt) pour une inscription
     */
    public function getDossierByInscription(string $inscriptionCode): array
    {
        $sql = "
            SELECT 
                pfc.piece_code,
                pf.libelle_piece,
                pf.description_piece,
                de.id_dossier_etudiant,
                de.code_dossier_etudiant,
                COALESCE(de.statut_depot, 'en_attente') AS statut_depot,
                de.date_depot,
                de.fichier_joint,
                de.observations,
                de.created_at_dossier_etudiant
            FROM inscriptions i
            JOIN classes cl ON cl.code_classe = i.classe_code
            JOIN niveaux n ON n.code_niveau = cl.niveau_code
            JOIN piece_fournir_cycle pfc ON pfc.cycle_code = (
                CASE 
                    WHEN n.code_niveau LIKE '%BTS%' THEN 'CYC-BTS'
                    WHEN n.code_niveau LIKE '%L%'   THEN 'CYC-LICENCE'
                    WHEN n.code_niveau LIKE '%M%'   THEN 'CYC-MASTER'
                    ELSE 'CYC-BTS'
                END
            ) AND pfc.statut_piece_cycle = 'actif'
            JOIN pieces_fournir pf ON pf.code_piece_fournir = pfc.piece_code
            LEFT JOIN dossier_etudiant de ON de.inscription_code = i.code_inscription AND de.piece_code = pfc.piece_code
            WHERE i.code_inscription = ?
            ORDER BY pf.libelle_piece ASC
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$inscriptionCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelDossierEtudiant::getDossierByInscription error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Met à jour ou insère le statut de dépôt d'une pièce
     */
    public function saveStatutPiece(string $inscriptionCode, string $etudiantCode, string $pieceCode, string $statut, ?string $observations = null, ?string $userCode = null): bool
    {
        $sql = "
            INSERT INTO dossier_etudiant (
                code_dossier_etudiant, inscription_code, etudiant_code, piece_code,
                statut_depot, date_depot, observations, user_code, etablissement_code, created_at_dossier_etudiant
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, '5454544456', NOW())
            ON DUPLICATE KEY UPDATE 
                statut_depot = VALUES(statut_depot),
                date_depot = CASE WHEN VALUES(statut_depot) = 'depose' THEN NOW() ELSE date_depot END,
                observations = VALUES(observations),
                user_code = VALUES(user_code),
                updated_at_dossier_etudiant = NOW()
        ";
        try {
            $codeDossier = (new Validator())->generateCode('dossier_etudiant', 'code_dossier_etudiant', 'DOS-', 8);
            $dateDepot = ($statut === 'depose') ? date('Y-m-d H:i:s') : null;
            $stmt = $this->getCon()->prepare($sql);
            return $stmt->execute([
                $codeDossier,
                $inscriptionCode,
                $etudiantCode,
                $pieceCode,
                $statut,
                $dateDepot,
                $observations,
                $userCode
            ]);
        } catch (Exception $e) {
            error_log("ModelDossierEtudiant::saveStatutPiece error: " . $e->getMessage());
            return false;
        }
    }
}
