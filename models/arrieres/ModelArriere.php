<?php

require_once __DIR__ . '/../../config/Database.php';

class ModelArriere extends Database
{
    /**
     * Récupère la liste des étudiants ayant des arriérés de scolarité sur des années antérieures.
     */
    public function getArrieresList(?string $anneeActiveCode = null, ?string $anneeOrigineCode = null, ?string $classeCode = null, ?string $statutFilter = null): array
    {
        $db = $this->getCon();
        $anneeActiveCode = $anneeActiveCode ?? ($_SESSION['annee_active_code'] ?? '');

        $where = "WHERE i_orig.statut_inscription != 'annule'";
        $params = [];

        if (!empty($anneeActiveCode)) {
            $where .= " AND i_orig.annee_code != ?";
            $params[] = $anneeActiveCode;
        }

        if (!empty($anneeOrigineCode)) {
            $where .= " AND i_orig.annee_code = ?";
            $params[] = $anneeOrigineCode;
        }

        if (!empty($classeCode)) {
            $where .= " AND (i_act.classe_code = ? OR i_orig.classe_code = ?)";
            $params[] = $classeCode;
            $params[] = $classeCode;
        }

        $sql = "
            SELECT 
                e.code_etudiant,
                COALESCE(e.matricule_etudiant, '-') as matricule_etudiant,
                COALESCE(e.nom_etudiant, '') as nom_etudiant,
                COALESCE(e.prenom_etudiant, '') as prenom_etudiant,
                COALESCE(e.photo_etudiant, '') as photo_etudiant,
                COALESCE(e.telephone_etudiant, '-') as telephone_etudiant,
                CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) as nom_complet,
                COALESCE(c_act.libelle_classe, 'Non assignée') as classe_actuelle,
                SUM(i_orig.montant_scolarite_inscription) as total_initial_du,
                SUM(COALESCE(p_sub.total_paye, 0)) as total_deja_paye,
                SUM(i_orig.montant_scolarite_inscription - COALESCE(p_sub.total_paye, 0)) as total_solde_arriere,
                GROUP_CONCAT(
                    CONCAT(
                        i_orig.code_inscription, ':::',
                        COALESCE(ann_orig.libelle_annee, i_orig.annee_code), ':::',
                        COALESCE(c_orig.libelle_classe, '-'), ':::',
                        i_orig.montant_scolarite_inscription, ':::',
                        COALESCE(p_sub.total_paye, 0), ':::',
                        (i_orig.montant_scolarite_inscription - COALESCE(p_sub.total_paye, 0))
                    ) ORDER BY i_orig.id_inscription ASC SEPARATOR '|||'
                ) as details_arrieres
            FROM inscriptions i_orig
            INNER JOIN etudiants e ON e.code_etudiant = i_orig.etudiant_code
            LEFT JOIN classes c_orig ON c_orig.code_classe = i_orig.classe_code
            LEFT JOIN annees ann_orig ON ann_orig.code_annee = i_orig.annee_code
            LEFT JOIN inscriptions i_act ON i_act.etudiant_code = e.code_etudiant AND i_act.id_inscription = (
                SELECT MAX(id_inscription) FROM inscriptions WHERE etudiant_code = e.code_etudiant
            )
            LEFT JOIN classes c_act ON c_act.code_classe = i_act.classe_code
            LEFT JOIN (
                SELECT inscription_code, SUM(montant_paiement) as total_paye
                FROM paiements
                WHERE statut_paiement = 'confirme'
                GROUP BY inscription_code
            ) p_sub ON p_sub.inscription_code = i_orig.code_inscription
            {$where}
            GROUP BY e.code_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, e.photo_etudiant, e.telephone_etudiant, c_act.libelle_classe
            HAVING total_solde_arriere > 0
            ORDER BY total_solde_arriere DESC
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as &$row) {
                $items = [];
                if (!empty($row['details_arrieres'])) {
                    $rawItems = explode('|||', $row['details_arrieres']);
                    foreach ($rawItems as $raw) {
                        $parts = explode(':::', $raw);
                        if (count($parts) >= 6) {
                            $solde = (float)($parts[5] ?? 0);
                            if ($solde > 0) {
                                $items[] = [
                                    'inscription_code' => $parts[0] ?? '',
                                    'libelle_annee' => $parts[1] ?? '',
                                    'libelle_classe' => $parts[2] ?? '',
                                    'montant_initial' => (float)($parts[3] ?? 0),
                                    'total_paye' => (float)($parts[4] ?? 0),
                                    'solde_restant' => $solde
                                ];
                            }
                        }
                    }
                }
                $row['items'] = $items;
            }

            return $rows;
        } catch (Exception $e) {
            error_log("ModelArriere::getArrieresList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcule les statistiques globales sur les arriérés des années antérieures.
     */
    public function getStats(?string $anneeActiveCode = null): array
    {
        $db = $this->getCon();
        $anneeActiveCode = $anneeActiveCode ?? ($_SESSION['annee_active_code'] ?? '');

        $sql = "
            SELECT 
                COUNT(DISTINCT i_orig.etudiant_code) as nb_etudiants_debiteurs,
                SUM(i_orig.montant_scolarite_inscription) as total_initial_du,
                SUM(COALESCE(p_sub.total_paye, 0)) as total_recouvre,
                SUM(i_orig.montant_scolarite_inscription - COALESCE(p_sub.total_paye, 0)) as total_solde_arriere
            FROM inscriptions i_orig
            LEFT JOIN (
                SELECT inscription_code, SUM(montant_paiement) as total_paye
                FROM paiements
                WHERE statut_paiement = 'confirme'
                GROUP BY inscription_code
            ) p_sub ON p_sub.inscription_code = i_orig.code_inscription
            WHERE i_orig.annee_code != ?
              AND i_orig.statut_inscription != 'annule'
            HAVING total_solde_arriere > 0
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$anneeActiveCode]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$res) {
                return [
                    'nb_etudiants' => 0,
                    'total_du' => 0,
                    'total_recouvre' => 0,
                    'total_solde' => 0,
                    'taux_recouvrement' => 0
                ];
            }

            $totalDu = (float)($res['total_initial_du'] ?? 0);
            $totalRecouvre = (float)($res['total_recouvre'] ?? 0);
            $totalSolde = (float)($res['total_solde_arriere'] ?? 0);
            $taux = ($totalDu > 0) ? round(($totalRecouvre / $totalDu) * 100, 1) : 0;

            return [
                'nb_etudiants' => (int)($res['nb_etudiants_debiteurs'] ?? 0),
                'total_du' => $totalDu,
                'total_recouvre' => $totalRecouvre,
                'total_solde' => $totalSolde,
                'taux_recouvrement' => $taux
            ];
        } catch (Exception $e) {
            error_log("ModelArriere::getStats error: " . $e->getMessage());
            return [
                'nb_etudiants' => 0,
                'total_du' => 0,
                'total_recouvre' => 0,
                'total_solde' => 0,
                'taux_recouvrement' => 0
            ];
        }
    }

    /**
     * Récupère le détail complet des arriérés d'un étudiant spécifié par son code_etudiant.
     */
    public function getArrieresEtudiantDetail(string $etudiantCode, ?string $anneeActiveCode = null): array
    {
        $db = $this->getCon();
        $anneeActiveCode = $anneeActiveCode ?? ($_SESSION['annee_active_code'] ?? '');

        $sql = "
            SELECT 
                i.code_inscription,
                i.annee_code,
                ann.libelle_annee,
                c.libelle_classe,
                i.montant_scolarite_inscription,
                COALESCE(SUM(CASE WHEN p.statut_paiement = 'confirme' THEN p.montant_paiement ELSE 0 END), 0) as total_paye,
                (i.montant_scolarite_inscription - COALESCE(SUM(CASE WHEN p.statut_paiement = 'confirme' THEN p.montant_paiement ELSE 0 END), 0)) as solde_restant
            FROM inscriptions i
            LEFT JOIN annees ann ON ann.code_annee = i.annee_code
            LEFT JOIN classes c ON c.code_classe = i.classe_code
            LEFT JOIN paiements p ON p.inscription_code = i.code_inscription
            WHERE i.etudiant_code = ?
              AND i.annee_code != ?
              AND i.statut_inscription != 'annule'
            GROUP BY i.code_inscription, i.annee_code, ann.libelle_annee, c.libelle_classe, i.montant_scolarite_inscription
            HAVING solde_restant > 0
            ORDER BY i.id_inscription ASC
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$etudiantCode, $anneeActiveCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelArriere::getArrieresEtudiantDetail error: " . $e->getMessage());
            return [];
        }
    }
}
