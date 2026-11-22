<?php

/**
 * Modèle de gestion des Délibérations & Décisions Annuelles Officielles
 * Permet de figer les moyennes annuelles et décisions du conseil de classe / jury,
 * d'éviter tout recalcul superflu après délibération et de prendre en charge les rachats/décisions manuelles.
 */
class ModelDeliberation extends BaseModel
{
    protected string $table = TABLES::DELIBERATIONS;
    protected string $primaryKey = 'id_deliberation';
    protected ?string $statusField = 'statut_deliberation';
    protected ?string $createdAtField = 'date_deliberation';

    /**
     * Retourne la délibération (provisoire ou validée) associée à une inscription
     */
    public function getByInscription(string $inscriptionCode): ?array
    {
        if (trim($inscriptionCode) === '') {
            return null;
        }
        try {
            $stmt = $this->getCon()->prepare("
                SELECT d.*,
                       TRIM(CONCAT(COALESCE(u.prenom_user, ''), ' ', COALESCE(u.nom_user, ''))) AS delibere_par_nom
                FROM `" . TABLES::DELIBERATIONS . "` d
                LEFT JOIN `" . TABLES::USERS . "` u ON u.code_user = d.user_code
                WHERE d.inscription_code = ?
                LIMIT 1
            ");
            $stmt->execute([$inscriptionCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelDeliberation::getByInscription error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retourne uniquement la délibération officiellement VALIDÉE (figée) d'une inscription — lecture O(1)
     */
    public function getValidatedByInscription(string $inscriptionCode): ?array
    {
        if (trim($inscriptionCode) === '') {
            return null;
        }
        try {
            $stmt = $this->getCon()->prepare("
                SELECT *
                FROM `" . TABLES::DELIBERATIONS . "`
                WHERE `inscription_code` = ?
                  AND `statut_deliberation` = '" . STATUTS::VALIDE . "'
                LIMIT 1
            ");
            $stmt->execute([$inscriptionCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelDeliberation::getValidatedByInscription error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Charge en lot (map indexée par inscription_code) les délibérations d'une classe pour une année
     */
    public function getByClasseAndAnnee(string $classeCode, string $anneeCode): array
    {
        if (trim($classeCode) === '' || trim($anneeCode) === '') {
            return [];
        }
        try {
            $stmt = $this->getCon()->prepare("
                SELECT d.*,
                       TRIM(CONCAT(COALESCE(u.prenom_user, ''), ' ', COALESCE(u.nom_user, ''))) AS delibere_par_nom
                FROM `" . TABLES::DELIBERATIONS . "` d
                LEFT JOIN `" . TABLES::USERS . "` u ON u.code_user = d.user_code
                WHERE d.classe_code = ? AND d.annee_code = ?
            ");
            $stmt->execute([$classeCode, $anneeCode]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $map = [];
            foreach ($rows as $row) {
                $map[$row['inscription_code']] = $row;
            }
            return $map;
        } catch (Exception $e) {
            error_log("ModelDeliberation::getByClasseAndAnnee error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Synthèse de l'état de délibération d'une classe (statut figé, date, statistiques)
     */
    public function getClasseDeliberationSummary(string $classeCode, string $anneeCode): array
    {
        $summary = [
            'is_delibere' => false,
            'has_provisoire' => false,
            'total_deliberes' => 0,
            'total_valides' => 0,
            'nb_admis' => 0,
            'nb_ajournes' => 0,
            'nb_redoublants' => 0,
            'nb_exclus' => 0,
            'nb_en_attente' => 0,
            'nb_rachetes' => 0,
            'last_date_deliberation' => null,
            'delibere_par_nom' => ''
        ];

        $map = $this->getByClasseAndAnnee($classeCode, $anneeCode);
        if (empty($map)) {
            return $summary;
        }

        $summary['total_deliberes'] = count($map);
        $latestTs = 0;

        foreach ($map as $row) {
            if (($row['statut_deliberation'] ?? '') === STATUTS::VALIDE) {
                $summary['total_valides']++;
            } else {
                $summary['has_provisoire'] = true;
            }

            $dec = $row['decision_finale'] ?? 'en_attente';
            if ($dec === 'admis') $summary['nb_admis']++;
            elseif ($dec === 'ajourne') $summary['nb_ajournes']++;
            elseif ($dec === 'redouble') $summary['nb_redoublants']++;
            elseif ($dec === 'exclu') $summary['nb_exclus']++;
            else $summary['nb_en_attente']++;

            if (!empty($row['est_rachete'])) {
                $summary['nb_rachetes']++;
            }

            $refDate = !empty($row['updated_at_deliberation']) ? $row['updated_at_deliberation'] : ($row['date_deliberation'] ?? null);
            if ($refDate) {
                $ts = strtotime($refDate);
                if ($ts !== false && $ts >= $latestTs) {
                    $latestTs = $ts;
                    $summary['last_date_deliberation'] = $refDate;
                    $summary['delibere_par_nom'] = trim((string)($row['delibere_par_nom'] ?? ''));
                }
            }
        }

        $summary['is_delibere'] = ($summary['total_valides'] > 0 && !$summary['has_provisoire']);
        return $summary;
    }

    /**
     * Calcule et fige officiellement la délibération annuelle de toute une classe (1 seule passe)
     * Utilise la formule annuelle officielle de ModelBulletin (1A / 2A + barèmes S2) et date PHP.
     */
    public function delibererClasse(
        string $classeCode,
        string $anneeCode,
        string $userCode,
        string $etabCode,
        bool $preserveManualOverrides = true
    ): array {
        $db = $this->getCon();
        if (trim($classeCode) === '' || trim($anneeCode) === '') {
            return ['success' => false, 'message' => 'Classe et année académique requises pour la délibération.'];
        }

        require_once __DIR__ . '/ModelBulletin.php';
        $modelBulletin = new ModelBulletin();

        // Récupérer le dernier semestre de l'année (S2 / bilan annuel)
        $semestres = $modelBulletin->getActiveSemestres($anneeCode);
        if (empty($semestres)) {
            $semestres = (new ModelSemestre())->getAll($anneeCode);
        }
        $lastSemestreCode = !empty($semestres) ? (string)(end($semestres)['code_semestre'] ?? '') : null;

        // Récupérer toutes les inscriptions actives de la classe
        $stmtIns = $db->prepare("
            SELECT id_inscription, code_inscription, etudiant_code, classe_code, annee_code, etablissement_code
            FROM `" . TABLES::INSCRIPTIONS . "`
            WHERE classe_code = ? AND annee_code = ?
              AND (statut_inscription != '" . STATUTS::ANNULE . "' OR statut_inscription IS NULL)
            ORDER BY id_inscription ASC
        ");
        $stmtIns->execute([$classeCode, $anneeCode]);
        $inscriptions = $stmtIns->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($inscriptions)) {
            return ['success' => false, 'message' => 'Aucun étudiant inscrit trouvé dans cette classe pour cette année.'];
        }

        $existingMap = $this->getByClasseAndAnnee($classeCode, $anneeCode);
        $modelConfig = new ModelConfiguration();
        $baremesBulletin = $modelConfig->getBaremesBulletin(true);
        $validator = new Validator();
        $now = date('Y-m-d H:i:s');

        $countSaved = 0;
        $countAdmis = 0;
        $countAjournes = 0;

        try {
            $db->beginTransaction();

            $sqlUpsert = "
                INSERT INTO `" . TABLES::DELIBERATIONS . "` (
                    code_deliberation, inscription_code, etudiant_code, classe_code, annee_code, etablissement_code,
                    moyenne_s1, moyenne_s2, moyenne_examen, moyenne_annuelle, rang_annuel, effectif_classe,
                    mention_finale, code_distinction_sanction, decision_finale, libelle_decision,
                    est_rachete, observation_jury, statut_deliberation, user_code, date_deliberation, updated_at_deliberation
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, '" . STATUTS::VALIDE . "', ?, ?, ?
                )
                ON DUPLICATE KEY UPDATE
                    classe_code = VALUES(classe_code),
                    etablissement_code = VALUES(etablissement_code),
                    moyenne_s1 = VALUES(moyenne_s1),
                    moyenne_s2 = VALUES(moyenne_s2),
                    moyenne_examen = VALUES(moyenne_examen),
                    moyenne_annuelle = VALUES(moyenne_annuelle),
                    rang_annuel = VALUES(rang_annuel),
                    effectif_classe = VALUES(effectif_classe),
                    mention_finale = VALUES(mention_finale),
                    code_distinction_sanction = VALUES(code_distinction_sanction),
                    decision_finale = VALUES(decision_finale),
                    libelle_decision = VALUES(libelle_decision),
                    est_rachete = VALUES(est_rachete),
                    observation_jury = VALUES(observation_jury),
                    statut_deliberation = '" . STATUTS::VALIDE . "',
                    user_code = VALUES(user_code),
                    updated_at_deliberation = VALUES(updated_at_deliberation)
            ";
            $stmtUpsert = $db->prepare($sqlUpsert);

            $effectifTotal = count($inscriptions);

            foreach ($inscriptions as $ins) {
                $idIns = (int)$ins['id_inscription'];
                $codeIns = (string)$ins['code_inscription'];
                $codeEtu = (string)$ins['etudiant_code'];
                $etabIns = !empty($ins['etablissement_code']) ? (string)$ins['etablissement_code'] : $etabCode;

                $calc = $modelBulletin->calculateBulletinData($idIns, $lastSemestreCode, false);

                $moyS1 = ($calc && isset($calc['moy_semestre1']) && $calc['moy_semestre1'] !== null) ? round((float)$calc['moy_semestre1'], 2) : null;
                $moyS2 = ($calc && isset($calc['moy_semestre2']) && $calc['moy_semestre2'] !== null) ? round((float)$calc['moy_semestre2'], 2) : null;
                $moyEx = ($calc && isset($calc['moy_examen']) && $calc['moy_examen'] !== null) ? round((float)$calc['moy_examen'], 2) : null;

                $hasEvaluations = $calc && (
                    ($calc['totalCoefficients'] ?? 0) > 0
                    || $moyS1 !== null
                    || $moyS2 !== null
                    || $moyEx !== null
                );

                $moyAnn = null;
                $rangAnn = null;
                $mentionFinale = 'Non évalué';
                $codeDist = 'AUCUN';
                $decisionFinale = 'en_attente';
                $libelleDecision = 'En attente de notes';

                if ($hasEvaluations) {
                    $moyAnn = isset($calc['moyenneRecap']) ? round((float)$calc['moyenneRecap'], 2) : round((float)($calc['moyenneGenerale'] ?? 0), 2);
                    $rangAnn = isset($calc['rangRecap']) && is_numeric($calc['rangRecap'])
                        ? (int)$calc['rangRecap']
                        : (isset($calc['rang']) && is_numeric($calc['rang']) ? (int)$calc['rang'] : null);

                    $evalAnn = $modelConfig->evaluateNote((float)$moyAnn, $baremesBulletin);
                    $mentionFinale = !empty($evalAnn['mention_bulletin']) && $evalAnn['mention_bulletin'] !== '-'
                        ? (string)$evalAnn['mention_bulletin']
                        : (!empty($calc['mention']) ? (string)$calc['mention'] : '-');
                    $codeDist = !empty($evalAnn['code_distinction_sanction'])
                        ? (string)$evalAnn['code_distinction_sanction']
                        : (!empty($calc['code_distinction_sanction']) ? (string)$calc['code_distinction_sanction'] : 'AUCUN');
                    $baremeDecision = !empty($evalAnn['appreciation_conseil_s2']) && $evalAnn['appreciation_conseil_s2'] !== '-'
                        ? (string)$evalAnn['appreciation_conseil_s2']
                        : (!empty($evalAnn['appreciation_conseil']) && $evalAnn['appreciation_conseil'] !== '-' ? (string)$evalAnn['appreciation_conseil'] : '');

                    if ($moyAnn >= 10.0) {
                        $decisionFinale = 'admis';
                        $libelleDecision = $baremeDecision !== ''
                            ? $baremeDecision
                            : 'Admis(e) en classe supérieure';
                    } else {
                        $decisionFinale = 'ajourne';
                        $libelleDecision = $baremeDecision !== ''
                            ? $baremeDecision
                            : 'Ajourné(e) / Redoublement';
                    }
                }

                // Préserver un rachat / ajustement manuel du jury si existant et demandé
                $estRachete = 0;
                $observationJury = null;
                $existing = $existingMap[$codeIns] ?? null;
                if ($preserveManualOverrides && $existing) {
                    $observationJury = $existing['observation_jury'] ?? null;
                    if (!empty($existing['est_rachete']) || in_array($existing['decision_finale'] ?? '', ['redouble', 'exclu'], true)) {
                        $estRachete = !empty($existing['est_rachete']) ? 1 : 0;
                        $decisionFinale = (string)$existing['decision_finale'];
                        if (!empty($existing['libelle_decision'])) {
                            $libelleDecision = (string)$existing['libelle_decision'];
                        }
                    }
                }

                $codeDelib = $existing['code_deliberation'] ?? $validator->generateCode(TABLES::DELIBERATIONS, 'code_deliberation', 'DEL-', 8);
                $effectifClasse = !empty($calc['totalElevesClasse']) && is_numeric($calc['totalElevesClasse'])
                    ? (int)$calc['totalElevesClasse']
                    : $effectifTotal;

                $stmtUpsert->execute([
                    $codeDelib,
                    $codeIns,
                    $codeEtu,
                    $classeCode,
                    $anneeCode,
                    $etabIns,
                    $moyS1,
                    $moyS2,
                    $moyEx,
                    $moyAnn,
                    $rangAnn,
                    $effectifClasse,
                    $mentionFinale,
                    $codeDist,
                    $decisionFinale,
                    $libelleDecision,
                    $estRachete,
                    $observationJury,
                    $userCode,
                    $now,
                    $now
                ]);

                $countSaved++;
                if ($decisionFinale === 'admis') {
                    $countAdmis++;
                } elseif (in_array($decisionFinale, ['ajourne', 'redouble', 'exclu'], true)) {
                    $countAjournes++;
                }
            }

            $db->commit();

            return [
                'success' => true,
                'message' => "Délibération annuelle validée et figée avec succès pour {$countSaved} étudiant(s) ({$countAdmis} admis, {$countAjournes} ajourné(s)/autres).",
                'count_saved' => $countSaved,
                'count_admis' => $countAdmis,
                'count_ajournes' => $countAjournes,
                'date_deliberation' => $now
            ];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("ModelDeliberation::delibererClasse error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la validation de la délibération : ' . $e->getMessage()];
        }
    }

    /**
     * Ajuste manuellement la décision officielle du jury pour un étudiant (rachat, redoublement, exclusion, observation)
     */
    public function updateDecisionIndividuelle(
        string $inscriptionCode,
        string $decisionFinale,
        string $libelleDecision,
        int $estRachete,
        ?string $observationJury,
        string $userCode
    ): bool {
        if (!in_array($decisionFinale, STATUTS::DECISIONS_DELIBERATION, true)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $cleanLibelle = trim($libelleDecision);
        if ($cleanLibelle === '') {
            $cleanLibelle = self::defaultLabelForDecision($decisionFinale, $estRachete === 1);
        }
        $cleanObs = ($observationJury !== null && trim($observationJury) !== '') ? trim($observationJury) : null;

        try {
            $stmt = $this->getCon()->prepare("
                UPDATE `" . TABLES::DELIBERATIONS . "`
                SET `decision_finale` = ?,
                    `libelle_decision` = ?,
                    `est_rachete` = ?,
                    `observation_jury` = ?,
                    `statut_deliberation` = '" . STATUTS::VALIDE . "',
                    `user_code` = ?,
                    `updated_at_deliberation` = ?
                WHERE `inscription_code` = ?
            ");
            return $stmt->execute([
                $decisionFinale,
                $cleanLibelle,
                $estRachete ? 1 : 0,
                $cleanObs,
                $userCode,
                $now,
                $inscriptionCode
            ]);
        } catch (Exception $e) {
            error_log("ModelDeliberation::updateDecisionIndividuelle error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Repasse la délibération d'une classe en statut 'provisoire' (réouverture avant clôture d'année)
     */
    public function reouvrirClasse(string $classeCode, string $anneeCode, string $userCode): bool
    {
        try {
            $stmt = $this->getCon()->prepare("
                UPDATE `" . TABLES::DELIBERATIONS . "`
                SET `statut_deliberation` = 'provisoire',
                    `user_code` = ?,
                    `updated_at_deliberation` = ?
                WHERE `classe_code` = ? AND `annee_code` = ?
            ");
            return $stmt->execute([$userCode, date('Y-m-d H:i:s'), $classeCode, $anneeCode]);
        } catch (Exception $e) {
            error_log("ModelDeliberation::reouvrirClasse error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Libellé métier par défaut selon le code de décision
     */
    public static function defaultLabelForDecision(string $decisionFinale, bool $estRachete = false): string
    {
        switch ($decisionFinale) {
            case 'admis':
                return $estRachete ? 'Admis(e) par rachat du jury' : 'Admis(e) en classe supérieure';
            case 'redouble':
                return 'Redoublement';
            case 'exclu':
                return 'Exclu(e)';
            case 'ajourne':
                return 'Ajourné(e) / Redoublement';
            default:
                return 'En attente';
        }
    }

    /**
     * Retourne la couleur/badge standardisée (success, warning, danger, info, secondary) pour une décision
     */
    public static function badgeTypeForDecision(string $decisionFinale): string
    {
        switch ($decisionFinale) {
            case 'admis':
                return 'success';
            case 'ajourne':
            case 'redouble':
                return 'warning';
            case 'exclu':
                return 'danger';
            default:
                return 'info';
        }
    }
}
