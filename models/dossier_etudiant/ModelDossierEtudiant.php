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
            LEFT JOIN filiere_cycles fc ON fc.filiere_code = cl.filiere_code
            JOIN piece_fournir_cycle pfc ON (pfc.cycle_code = fc.cycle_code OR pfc.cycle_code IS NULL OR pfc.cycle_code = '') AND pfc.statut_piece_cycle = 'actif'
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
     * Met à jour ou insère le statut de dépôt d'une pièce avec observations et fichier joint éventuel
     */
    public function saveStatutPiece(
        string $inscriptionCode, 
        string $etudiantCode, 
        string $pieceCode, 
        string $statut, 
        ?string $observations = null, 
        ?string $userCode = null,
        ?string $fichierJoint = null
    ): bool {
        $etabCode = $_SESSION['etablissement_active_code'] ?? '';
        if (empty($etabCode)) {
            try {
                $stmtEtab = $this->getCon()->query("SELECT code_etablissement FROM etablissements ORDER BY id_etablissement DESC LIMIT 1");
                $etabCode = $stmtEtab ? (string)$stmtEtab->fetchColumn() : '';
            } catch (Exception $e) {
                $etabCode = '';
            }
        }

        $fkErr = ForeignKeyValidator::validate($this->getCon(), 'dossier_etudiant', [
            'inscription_code' => $inscriptionCode,
            'etudiant_code' => $etudiantCode,
            'piece_code' => $pieceCode,
            'etablissement_code' => $etabCode
        ]);
        if ($fkErr !== null) {
            $this->lastError = $fkErr;
            error_log('[ModelDossierEtudiant::saveStatutPiece] FK Error: ' . $fkErr);
            return false;
        }

        $sql = "
            INSERT INTO dossier_etudiant (
                code_dossier_etudiant, inscription_code, etudiant_code, piece_code,
                statut_depot, date_depot, fichier_joint, observations, user_code, etablissement_code, created_at_dossier_etudiant
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                statut_depot = VALUES(statut_depot),
                date_depot = CASE WHEN VALUES(statut_depot) = 'depose' THEN NOW() ELSE date_depot END,
                fichier_joint = COALESCE(VALUES(fichier_joint), fichier_joint),
                observations = VALUES(observations),
                user_code = VALUES(user_code),
                etablissement_code = VALUES(etablissement_code),
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
                $fichierJoint,
                $observations,
                $userCode,
                $etabCode
            ]);
        } catch (Exception $e) {
            error_log("ModelDossierEtudiant::saveStatutPiece error: " . $e->getMessage());
            return false;
        }
    }
}
