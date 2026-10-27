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
            JOIN piece_fournir_cycle pfc ON (pfc.cycle_code = fc.cycle_code OR pfc.cycle_code IS NULL OR pfc.cycle_code = '') 
                 AND (pfc.niveau_code = cl.niveau_code OR pfc.niveau_code IS NULL OR pfc.niveau_code = '')
                 AND pfc.statut_piece_cycle = 'actif'
            JOIN pieces_fournir pf ON pf.code_piece_fournir = pfc.piece_code
            LEFT JOIN dossier_etudiant de ON de.inscription_code = i.code_inscription AND de.piece_code = pfc.piece_code
            WHERE i.code_inscription = ?
            GROUP BY pfc.piece_code
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
        $etabCode = Session::get('etablissement_active_code') ?? '';
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

    /**
     * Récupère la liste des étudiants/inscriptions avec filtres pour le tableau de suivi des dossiers
     */
    public function getInscriptionsForDossiers(?string $anneeCode = null, ?string $classeCode = null): array
    {
        try {
            $sql = "
                SELECT 
                    i.code_inscription,
                    i.created_at_inscription AS date_inscription,
                    i.statut_inscription,
                    e.code_etudiant,
                    e.matricule_etudiant,
                    e.nom_etudiant,
                    e.prenom_etudiant,
                    e.photo_etudiant,
                    e.telephone_etudiant,
                    cl.code_classe,
                    cl.libelle_classe,
                    cl.filiere_code
                FROM inscriptions i
                JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                JOIN classes cl ON cl.code_classe = i.classe_code
                WHERE 1=1
            ";

            $params = [];
            if (!empty($anneeCode)) {
                $sql .= " AND i.annee_code = ?";
                $params[] = $anneeCode;
            }
            if (!empty($classeCode)) {
                $sql .= " AND i.classe_code = ?";
                $params[] = $classeCode;
            }

            $sql .= " GROUP BY i.code_inscription ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC, e.prenom_etudiant ASC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelDossierEtudiant::getInscriptionsForDossiers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les informations complètes de l'étudiant pour le récépissé de dépôt
     */
    public function getRecepisseStudent(string $inscriptionCode): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("
                SELECT 
                    i.code_inscription, i.created_at_inscription AS date_inscription, i.statut_inscription,
                    e.code_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, e.telephone_etudiant, e.photo_etudiant,
                    cl.libelle_classe, cl.filiere_code,
                    a.libelle_annee,
                    et.libelle_etablissement AS nom_etablissement, et.logo_etablissement, et.telephone_etablissement, et.adresse_etablissement
                FROM inscriptions i
                JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                JOIN classes cl ON cl.code_classe = i.classe_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code
                LEFT JOIN etablissements et ON et.code_etablissement = i.etablissement_code
                WHERE i.code_inscription = ?
                LIMIT 1
            ");
            $stmt->execute([$inscriptionCode]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            return $student ?: null;
        } catch (Exception $e) {
            error_log("ModelDossierEtudiant::getRecepisseStudent error: " . $e->getMessage());
            return null;
        }
    }
}
