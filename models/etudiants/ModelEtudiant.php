<?php

class ModelEtudiant extends BaseModel
{
    protected string $table = 'etudiants';
    protected string $primaryKey = 'id_etudiant';
    protected ?string $statusField = 'statut_etudiant';
    protected ?string $createdAtField = 'created_at_etudiant';

    /**
     * Génère automatiquement le matricule officiel de l'étudiant
     * Règle : 1ère lettre Nom + 1ère lettre Prénom (Majuscule) - Ordre ID / GEB / 2 1ères lettres Filière + 2 derniers chiffres Année
     * Exemple : MN-123/GEB/ID26 (XX-123/GEB/AA26)
     */
    public function generateMatricule(string $nom, string $prenom, ?string $classeCode = null, ?string $anneeCode = null): string
    {
        $pdo = $this->getCon();

        // 1. Première lettre du Nom et Première lettre du Prénom en Majuscule
        $nomClean = trim($nom);
        $prenomClean = trim($prenom);
        $lettreNom = !empty($nomClean) ? strtoupper(mb_substr($nomClean, 0, 1, 'UTF-8')) : 'X';
        $lettrePrenom = !empty($prenomClean) ? strtoupper(mb_substr($prenomClean, 0, 1, 'UTF-8')) : 'X';
        $initiales = $lettreNom . $lettrePrenom;

        // 2. Ordre / Incrément last ID
        $lastId = (int)$pdo->query("SELECT MAX(id_etudiant) FROM etudiants")->fetchColumn() ?: 0;
        $nextOrder = $lastId + 1;

        // 3. Sigle Établissement
        $sigleEtab = 'GEB';

        // 4. Deux premières lettres de la Filière en Majuscule
        $filiereCodeLetters = 'GE';
        if (!empty($classeCode)) {
            $stmtCls = $pdo->prepare("
                SELECT f.code_filiere, f.libelle_filiere 
                FROM classes cl 
                JOIN filieres f ON f.code_filiere = cl.filiere_code 
                WHERE cl.code_classe = ? LIMIT 1
            ");
            $stmtCls->execute([$classeCode]);
            $filRow = $stmtCls->fetch(PDO::FETCH_ASSOC);
            if ($filRow) {
                $codeFil = str_replace('FIL-', '', $filRow['code_filiere'] ?? '');
                if (!empty($codeFil)) {
                    $filiereCodeLetters = strtoupper(substr($codeFil, 0, 2));
                } else {
                    $filiereCodeLetters = strtoupper(substr($filRow['libelle_filiere'] ?? 'GE', 0, 2));
                }
            }
        }

        // 5. Deux derniers chiffres de l'année (ex: 2026 -> 26, 2025-2026 -> 26)
        $anneeSuffix = date('y');
        if (!empty($anneeCode)) {
            $stmtAnn = $pdo->prepare("SELECT libelle_annee, date_debut_annee FROM annees WHERE code_annee = ? LIMIT 1");
            $stmtAnn->execute([$anneeCode]);
            $annRow = $stmtAnn->fetch(PDO::FETCH_ASSOC);
            if ($annRow) {
                if (!empty($annRow['libelle_annee']) && preg_match('/(\d{4})/', $annRow['libelle_annee'], $m)) {
                    $anneeSuffix = substr($m[1], -2);
                } elseif (!empty($annRow['date_debut_annee'])) {
                    $anneeSuffix = date('y', strtotime($annRow['date_debut_annee']));
                }
            }
        }

        // Format: XX-123/GEB/AA26
        $matricule = sprintf('%s-%d/%s/%s%s', $initiales, $nextOrder, $sigleEtab, $filiereCodeLetters, $anneeSuffix);

        // Vérifier l'unicité
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM etudiants WHERE matricule_etudiant = ?");
        $stmtCheck->execute([$matricule]);
        if ((int)$stmtCheck->fetchColumn() > 0) {
            $matricule = sprintf('%s-%d%02d/%s/%s%s', $initiales, $nextOrder, rand(1, 99), $sigleEtab, $filiereCodeLetters, $anneeSuffix);
        }

        return $matricule;
    }

    /**
     * Récupère la liste des étudiants avec leurs inscriptions annuelles et filtres dynamiques
     */
    public function getFilteredRegistry(array $filters = []): array
    {
        $pdo = $this->getCon();
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['annee_code']) && $filters['annee_code'] !== 'ALL') {
            $where[] = "i.annee_code = ?";
            $params[] = $filters['annee_code'];
        }

        if (!empty($filters['niveau_code']) && $filters['niveau_code'] !== 'ALL') {
            $where[] = "cl.niveau_code = ?";
            $params[] = $filters['niveau_code'];
        }

        if (!empty($filters['filiere_code']) && $filters['filiere_code'] !== 'ALL') {
            $where[] = "cl.filiere_code = ?";
            $params[] = $filters['filiere_code'];
        }

        if (!empty($filters['classe_code']) && $filters['classe_code'] !== 'ALL') {
            $where[] = "i.classe_code = ?";
            $params[] = $filters['classe_code'];
        }

        if (!empty($filters['statut_etudiant']) && $filters['statut_etudiant'] !== 'ALL') {
            $where[] = "e.statut_etudiant = ?";
            $params[] = $filters['statut_etudiant'];
        }

        $whereClause = implode(" AND ", $where);

        $sql = "
            SELECT 
                e.id_etudiant,
                e.code_etudiant,
                e.matricule_etudiant,
                e.matricule_menet,
                e.matricule_mesrs,
                e.nom_etudiant,
                e.prenom_etudiant,
                e.sexe_etudiant,
                e.telephone_etudiant,
                e.email_etudiant,
                e.photo_etudiant,
                e.statut_etudiant,
                i.id_inscription,
                i.code_inscription,
                i.statut_inscription,
                i.montant_scolarite_inscription,
                i.created_at_inscription,
                a.code_annee,
                a.libelle_annee,
                cl.code_classe,
                cl.libelle_classe,
                f.code_filiere,
                f.libelle_filiere,
                n.code_niveau,
                n.libelle_niveau
            FROM etudiants e
            JOIN inscriptions i ON i.etudiant_code = e.code_etudiant
            JOIN annees a ON a.code_annee = i.annee_code
            JOIN classes cl ON cl.code_classe = i.classe_code
            JOIN filieres f ON f.code_filiere = cl.filiere_code
            JOIN niveaux n ON n.code_niveau = cl.niveau_code
            WHERE {$whereClause}
            ORDER BY a.libelle_annee DESC, cl.libelle_classe ASC, e.nom_etudiant ASC, e.prenom_etudiant ASC
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelEtudiant::getFilteredRegistry error: " . $e->getMessage());
            return [];
        }
    }
}
