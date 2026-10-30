<?php

class ModelAccessoire extends BaseModel
{
    protected string $table = TABLES::ACCESSOIRES;
    protected string $primaryKey = 'id_accessoire';
    protected ?string $statusField = 'statut_accessoire';
    protected ?string $createdAtField = 'created_at_accessoire';

    public function getAll(?string $anneeCode = null): array
    {
        $sql = "
            SELECT a.*,
                   n.libelle_niveau,
                   n.slug_niveau
            FROM " . TABLES::ACCESSOIRES . " a
            LEFT JOIN " . TABLES::NIVEAUX . " n ON n.code_niveau = a.niveau_code
            ORDER BY a.id_accessoire DESC
        ";
        try {
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all accessoires: " . $e->getMessage());
            return [];
        }
    }

    public function getDistributions(string $filter = 'all', ?string $anneeCode = null, ?string $classeCode = null): array
    {
        $db = $this->getCon();
        $where = "WHERE (ai.statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR ai.statut_accessoire_inscription IS NULL)";
        $params = [];

        if (!empty($anneeCode)) {
            $where .= " AND ai.annee_code = ?";
            $params[] = $anneeCode;
        }

        if (!empty($classeCode)) {
            $where .= " AND i.classe_code = ?";
            $params[] = $classeCode;
        }

        $sql = "
            SELECT 
                   i.code_inscription,
                   e.code_etudiant,
                   COALESCE(e.matricule_etudiant, '-') as matricule_etudiant,
                   COALESCE(e.nom_etudiant, '') as nom_etudiant,
                   COALESCE(e.prenom_etudiant, '') as prenom_etudiant,
                   COALESCE(e.photo_etudiant, '') as photo_etudiant,
                   COALESCE(e.telephone_etudiant, '-') as telephone_etudiant,
                   CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) as nom_complet,
                   COALESCE(c.libelle_classe, 'Non assignée') as libelle_classe,
                   COUNT(ai.id_accessoire_inscription) as total_kits,
                   SUM(CASE WHEN ai.etat_retrait = 'retire' THEN 1 ELSE 0 END) as total_retires,
                   SUM(CASE WHEN ai.etat_retrait = 'en_attente' THEN 1 ELSE 0 END) as total_en_attente,
                   GROUP_CONCAT(
                       CONCAT(
                           ai.id_accessoire_inscription, ':::',
                           COALESCE(a.libelle_accessoire, 'Kit'), ':::',
                           ai.etat_retrait, ':::',
                           COALESCE(DATE_FORMAT(ai.date_retrait, '%d/%m/%Y %H:%i'), ''), ':::',
                           COALESCE(ai.accessoire_code, '')
                       ) ORDER BY ai.id_accessoire_inscription ASC SEPARATOR '|||'
                   ) as kits_details
            FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " ai
            LEFT JOIN " . TABLES::ACCESSOIRES . " a ON a.code_accessoire = ai.accessoire_code
            LEFT JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = ai.inscription_code
            LEFT JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN " . TABLES::CLASSES . " c ON c.code_classe = i.classe_code
            {$where}
            GROUP BY i.code_inscription, e.code_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, e.photo_etudiant, e.telephone_etudiant, c.libelle_classe
        ";

        if ($filter === 'en_attente') {
            $sql .= " HAVING SUM(CASE WHEN ai.etat_retrait = 'en_attente' THEN 1 ELSE 0 END) > 0";
        } elseif ($filter === 'retire') {
            $sql .= " HAVING SUM(CASE WHEN ai.etat_retrait = 'en_attente' THEN 1 ELSE 0 END) = 0";
        }

        $sql .= " ORDER BY i.id_inscription DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as &$row) {
                $items = [];
                if (!empty($row['kits_details'])) {
                    $rawItems = explode('|||', $row['kits_details']);
                    foreach ($rawItems as $raw) {
                        $parts = explode(':::', $raw);
                        if (count($parts) >= 3) {
                            $items[] = [
                                'id_accessoire_inscription' => (int)($parts[0] ?? 0),
                                'libelle_accessoire' => $parts[1] ?? 'Kit',
                                'etat_retrait' => $parts[2] ?? 'en_attente',
                                'date_retrait_formatee' => $parts[3] ?? '',
                                'accessoire_code' => $parts[4] ?? ''
                            ];
                        }
                    }
                }
                $row['items'] = $items;
                $row['pourcentage'] = ($row['total_kits'] > 0) ? round(($row['total_retires'] / $row['total_kits']) * 100) : 0;
            }
            unset($row);

            return $rows;
        } catch (Exception $e) {
            error_log("ModelAccessoire::getDistributions error: " . $e->getMessage());
            return [];
        }
    }

    public function getBonRemiseData(string $inscriptionCode): array
    {
        $db = $this->getCon();
        $stmt = $db->prepare("
            SELECT 
                i.code_inscription, i.created_at_inscription AS date_inscription, i.statut_inscription,
                e.code_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, e.telephone_etudiant, e.photo_etudiant,
                cl.libelle_classe, cl.filiere_code,
                a.libelle_annee,
                et.libelle_etablissement AS nom_etablissement, et.logo_etablissement, et.telephone_etablissement, et.adresse_etablissement
            FROM " . TABLES::INSCRIPTIONS . " i
            JOIN " . TABLES::ETUDIANTS . " e ON e.code_etudiant = i.etudiant_code
            JOIN " . TABLES::CLASSES . " cl ON cl.code_classe = i.classe_code
            LEFT JOIN " . TABLES::ANNEES . " a ON a.code_annee = i.annee_code
            LEFT JOIN " . TABLES::ETABLISSEMENTS . " et ON et.code_etablissement = i.etablissement_code
            WHERE i.code_inscription = ?
            LIMIT 1
        ");
        $stmt->execute([$inscriptionCode]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return [];
        }

        $stmtKits = $db->prepare("
            SELECT 
                ai.id_accessoire_inscription,
                ai.code_accessoire_inscription,
                ai.etat_retrait,
                ai.date_retrait,
                DATE_FORMAT(ai.date_retrait, '%d/%m/%Y %H:%i') as date_retrait_formatee,
                DATE_FORMAT(ai.created_at_accessoire_inscription, '%d/%m/%Y') as date_attribution_formatee,
                a.libelle_accessoire
            FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " ai
            JOIN " . TABLES::ACCESSOIRES . " a ON a.code_accessoire = ai.accessoire_code
            WHERE ai.inscription_code = ?
              AND (ai.statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR ai.statut_accessoire_inscription IS NULL)
            ORDER BY a.libelle_accessoire ASC
        ");
        $stmtKits->execute([$inscriptionCode]);
        $kits = $stmtKits->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $kitsRemis = [];
        $kitsEnAttente = [];

        foreach ($kits as $k) {
            if ($k['etat_retrait'] === 'retire') {
                $kitsRemis[] = $k;
            } else {
                $kitsEnAttente[] = $k;
            }
        }

        return [
            'student' => $student,
            'kits' => $kits,
            'kits_remis' => $kitsRemis,
            'kits_en_attente' => $kitsEnAttente,
            'total_kits' => count($kits),
            'total_remis' => count($kitsRemis),
            'total_en_attente' => count($kitsEnAttente),
            'pourcentage' => (count($kits) > 0) ? round((count($kitsRemis) / count($kits)) * 100) : 0
        ];
    }

    public function getStats(?string $anneeCode = null): array
    {
        $db = $this->getCon();
        $whereAnnee = "";
        $params = [];
        if (!empty($anneeCode)) {
            $whereAnnee = " AND annee_code = ?";
            $params[] = $anneeCode;
        }

        $stmtTot = $db->prepare("SELECT COUNT(*) FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " WHERE (statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR statut_accessoire_inscription IS NULL)" . $whereAnnee);
        $stmtTot->execute($params);
        $total = (int)($stmtTot->fetchColumn() ?: 0);

        $stmtAtt = $db->prepare("SELECT COUNT(*) FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " WHERE (statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR statut_accessoire_inscription IS NULL) AND etat_retrait = 'en_attente'" . $whereAnnee);
        $stmtAtt->execute($params);
        $enAttente = (int)($stmtAtt->fetchColumn() ?: 0);

        $stmtRet = $db->prepare("SELECT COUNT(*) FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " WHERE (statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR statut_accessoire_inscription IS NULL) AND etat_retrait = 'retire'" . $whereAnnee);
        $stmtRet->execute($params);
        $retire = (int)($stmtRet->fetchColumn() ?: 0);

        $totalTypes = (int)$db->query("SELECT COUNT(*) FROM " . TABLES::ACCESSOIRES . " WHERE statut_accessoire = '" . STATUTS::ACTIF . "'")->fetchColumn();
        $taux = $total > 0 ? round(($retire / $total) * 100) : 0;

        return [
            'total' => $total,
            'en_attente' => $enAttente,
            'retires' => $retire,
            'retire' => $retire,
            'taux_retrait' => $taux,
            'taux' => $taux,
            'total_types' => $totalTypes
        ];
    }

    public function getAccessoireInscription(int $id, string $code = ''): ?array
    {
        $db = $this->getCon();
        if ($id > 0) {
            $stmt = $db->prepare("SELECT * FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " WHERE id_accessoire_inscription = ? LIMIT 1");
            $stmt->execute([$id]);
        } else {
            $stmt = $db->prepare("SELECT * FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " WHERE code_accessoire_inscription = ? LIMIT 1");
            $stmt->execute([$code]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateEtatRetrait(int $id, string $newEtat, ?string $dateRetrait): bool
    {
        $stmt = $this->getCon()->prepare("
            UPDATE " . TABLES::ACCESSOIRE_INSCRIPTION . " 
            SET etat_retrait = ?, date_retrait = ? 
            WHERE id_accessoire_inscription = ?
        ");
        return $stmt->execute([$newEtat, $dateRetrait, $id]);
    }

    public function updateAllKitsForInscription(string $inscriptionCode, string $targetEtat, ?string $dateRetrait): bool
    {
        $stmt = $this->getCon()->prepare("
            UPDATE " . TABLES::ACCESSOIRE_INSCRIPTION . " 
            SET etat_retrait = ?, date_retrait = ? 
            WHERE inscription_code = ?
              AND (statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR statut_accessoire_inscription IS NULL)
        ");
        return $stmt->execute([$targetEtat, $dateRetrait, $inscriptionCode]);
    }

    public function getKitsByEtudiant(string $etudiantCode, ?string $anneeCode = null): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT ai.*, a.libelle_accessoire,
                   DATE_FORMAT(ai.date_retrait, '%d/%m/%Y %H:%i') as date_retrait_formatee,
                   DATE_FORMAT(ai.created_at_accessoire_inscription, '%d/%m/%Y') as date_attribution_formatee
            FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " ai
            JOIN " . TABLES::ACCESSOIRES . " a ON a.code_accessoire = ai.accessoire_code
            JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = ai.inscription_code
            WHERE i.etudiant_code = ?
              AND (ai.annee_code = ? OR ? = '')
              AND (ai.statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR ai.statut_accessoire_inscription IS NULL)
            ORDER BY ai.id_accessoire_inscription DESC
        ");
        $stmt->execute([$etudiantCode, $anneeCode ?: '', $anneeCode ?: '']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAllActiveKits(): array
    {
        return $this->getCon()->query("SELECT code_accessoire, libelle_accessoire FROM " . TABLES::ACCESSOIRES . " WHERE statut_accessoire = '" . STATUTS::ACTIF . "' ORDER BY libelle_accessoire ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getLatestInscriptionForStudent(string $etudiantCode): ?array
    {
        $stmt = $this->getCon()->prepare("SELECT code_inscription FROM " . TABLES::INSCRIPTIONS . " WHERE etudiant_code = ? ORDER BY id_inscription DESC LIMIT 1");
        $stmt->execute([$etudiantCode]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function checkKitAlreadyAttributed(string $etudiantCode, string $insCode, string $accCode, ?string $anneeCode = null): ?array
    {
        $stmt = $this->getCon()->prepare("
            SELECT ai.id_accessoire_inscription, a.libelle_accessoire 
            FROM " . TABLES::ACCESSOIRE_INSCRIPTION . " ai
            LEFT JOIN " . TABLES::ACCESSOIRES . " a ON a.code_accessoire = ai.accessoire_code
            JOIN " . TABLES::INSCRIPTIONS . " i ON i.code_inscription = ai.inscription_code
            WHERE (i.etudiant_code = ? OR ai.inscription_code = ?)
              AND ai.accessoire_code = ? 
              AND (ai.annee_code = ? OR ? = '')
              AND (ai.statut_accessoire_inscription = '" . STATUTS::ACTIF . "' OR ai.statut_accessoire_inscription IS NULL)
            LIMIT 1
        ");
        $stmt->execute([$etudiantCode, $insCode, $accCode, $anneeCode ?: '', $anneeCode ?: '']);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function insertAccessoireInscription(array $data): bool
    {
        $stmt = $this->getCon()->prepare("
            INSERT INTO " . TABLES::ACCESSOIRE_INSCRIPTION . "
            (code_accessoire_inscription, inscription_code, accessoire_code, annee_code, statut_accessoire_inscription, etat_retrait, date_retrait, user_code, etablissement_code, created_at_accessoire_inscription)
            VALUES (?, ?, ?, ?, 'actif', ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['code_accessoire_inscription'],
            $data['inscription_code'],
            $data['accessoire_code'],
            $data['annee_code'],
            $data['etat_retrait'] ?? 'en_attente',
            $data['date_retrait'] ?? null,
            $data['user_code'] ?? null,
            $data['etablissement_code'] ?? null
        ]);
    }
}
