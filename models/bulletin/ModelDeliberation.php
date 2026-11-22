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
     * Charge en 1 seule requête SQL (via VIEWS::SYNTHESE_MOYENNES_ANNUELLES) les moyennes S1, S2, Examen et Annuelle
     * de tous les étudiants d'une classe, indexées par inscription_code.
     */
    public function getSyntheseAnnuelleByClasse(string $classeCode, string $anneeCode): array
    {
        if (trim($classeCode) === '' || trim($anneeCode) === '') {
            return [];
        }
        try {
            $stmt = $this->getCon()->prepare("
                SELECT *
                FROM `" . VIEWS::SYNTHESE_MOYENNES_ANNUELLES . "`
                WHERE `classe_code` = ? AND `annee_code` = ?
            ");
            $stmt->execute([$classeCode, $anneeCode]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $map = [];
            foreach ($rows as $r) {
                $map[$r['inscription_code']] = $r;
            }
            return $map;
        } catch (Exception $e) {
            error_log("ModelDeliberation::getSyntheseAnnuelleByClasse error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcule et fige officiellement la délibération annuelle de toute une classe (1 seule requête via VIEWS::SYNTHESE_MOYENNES_ANNUELLES)
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

        $syntheseMap = $this->getSyntheseAnnuelleByClasse($classeCode, $anneeCode);
        $existingMap = $this->getByClasseAndAnnee($classeCode, $anneeCode);
        $modelConfig = new ModelConfiguration();
        $baremesBulletin = $modelConfig->getBaremesBulletin(true);
        $validator = new Validator();
        $now = date('Y-m-d H:i:s');
        $effectifTotal = count($inscriptions);

        // Pré-calculer les rangs annuels (avec ex-æquo) depuis la synthèse SQL
        $rankedCodes = [];
        foreach ($inscriptions as $ins) {
            $c = (string)$ins['code_inscription'];
            $syn = $syntheseMap[$c] ?? null;
            if ($syn && !empty($syn['has_notes']) && $syn['moyenne_annuelle'] !== null) {
                $rankedCodes[$c] = round((float)$syn['moyenne_annuelle'], 2);
            }
        }
        arsort($rankedCodes);
        $ranksMap = [];
        $pos = 0;
        $curRank = 0;
        $prevKey = null;
        foreach ($rankedCodes as $c => $mVal) {
            $pos++;
            $k = number_format($mVal, 2, '.', '');
            if ($prevKey === null || $k !== $prevKey) {
                $curRank = $pos;
                $prevKey = $k;
            }
            $ranksMap[$c] = $curRank;
        }

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

            foreach ($inscriptions as $ins) {
                $codeIns = (string)$ins['code_inscription'];
                $codeEtu = (string)$ins['etudiant_code'];
                $etabIns = !empty($ins['etablissement_code']) ? (string)$ins['etablissement_code'] : $etabCode;

                $syn = $syntheseMap[$codeIns] ?? null;
                $moyS1 = ($syn && $syn['moyenne_s1'] !== null) ? round((float)$syn['moyenne_s1'], 2) : null;
                $moyS2 = ($syn && $syn['moyenne_s2'] !== null) ? round((float)$syn['moyenne_s2'], 2) : null;
                $moyEx = ($syn && $syn['moyenne_examen'] !== null) ? round((float)$syn['moyenne_examen'], 2) : null;
                $hasEvaluations = $syn && !empty($syn['has_notes']) && $syn['moyenne_annuelle'] !== null;

                $moyAnn = null;
                $rangAnn = null;
                $mentionFinale = 'Non évalué';
                $codeDist = 'AUCUN';
                $decisionFinale = 'en_attente';
                $libelleDecision = 'En attente de notes';

                if ($hasEvaluations) {
                    $moyAnn = round((float)$syn['moyenne_annuelle'], 2);
                    $rangAnn = $ranksMap[$codeIns] ?? null;

                    $evalAnn = $modelConfig->evaluateNote((float)$moyAnn, $baremesBulletin);
                    $mentionFinale = !empty($evalAnn['mention_bulletin']) && $evalAnn['mention_bulletin'] !== '-'
                        ? (string)$evalAnn['mention_bulletin']
                        : '-';
                    $codeDist = !empty($evalAnn['code_distinction_sanction'])
                        ? (string)$evalAnn['code_distinction_sanction']
                        : 'AUCUN';
                    $baremeDecision = !empty($evalAnn['appreciation_conseil_s2']) && $evalAnn['appreciation_conseil_s2'] !== '-'
                        ? (string)$evalAnn['appreciation_conseil_s2']
                        : (!empty($evalAnn['appreciation_conseil']) && $evalAnn['appreciation_conseil'] !== '-' ? (string)$evalAnn['appreciation_conseil'] : '');

                    if ($moyAnn >= 10.0) {
                        $decisionFinale = 'admis';
                        $libelleDecision = $baremeDecision !== ''
                            ? $baremeDecision
                            : 'Admis(e) d\'office';
                    } else {
                        $decisionFinale = 'redouble';
                        $libelleDecision = $baremeDecision !== ''
                            ? $baremeDecision
                            : 'Redoublement (Recalé)';
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
                    $effectifTotal,
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
     * Charge en lot les absences annuelles (total heures, justifiées, injustifiées) par inscription
     */
    public function getAbsencesByInscriptions(array $inscriptionCodes, string $anneeCode): array
    {
        $codes = array_values(array_unique(array_filter($inscriptionCodes)));
        if (empty($codes)) {
            return [];
        }
        try {
            $placeholders = implode(',', array_fill(0, count($codes), '?'));
            $sql = "
                SELECT inscription_code,
                       COALESCE(SUM(duree_heures), 0) AS total_heures,
                       COALESCE(SUM(CASE WHEN justifiee = 'oui' THEN duree_heures ELSE 0 END), 0) AS heures_justifiees,
                       COALESCE(SUM(CASE WHEN justifiee = 'non' THEN duree_heures ELSE 0 END), 0) AS heures_injustifiees
                FROM `" . TABLES::ABSENCES . "`
                WHERE inscription_code IN ({$placeholders})
            ";
            $params = $codes;
            if (trim($anneeCode) !== '') {
                $sql .= " AND annee_code = ?";
                $params[] = $anneeCode;
            }
            $sql .= " GROUP BY inscription_code";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $map = [];
            foreach ($rows as $r) {
                $map[$r['inscription_code']] = [
                    'total_heures' => round((float)$r['total_heures'], 2),
                    'heures_justifiees' => round((float)$r['heures_justifiees'], 2),
                    'heures_injustifiees' => round((float)$r['heures_injustifiees'], 2)
                ];
            }
            return $map;
        } catch (Exception $e) {
            error_log("ModelDeliberation::getAbsencesByInscriptions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Convertit un choix d'interface (5 décisions du conseil) vers les colonnes BDD (decision_finale, est_rachete)
     */
    public static function mapChoiceToFields(string $choice): array
    {
        switch ($choice) {
            case 'admis_office':
                return ['decision_finale' => 'admis', 'est_rachete' => 0];
            case 'admis_grace':
                return ['decision_finale' => 'admis', 'est_rachete' => 1];
            case 'redouble':
                return ['decision_finale' => 'redouble', 'est_rachete' => 0];
            case 'ajourne':
                return ['decision_finale' => 'ajourne', 'est_rachete' => 0];
            case 'exclu':
                return ['decision_finale' => 'exclu', 'est_rachete' => 0];
            default:
                return ['decision_finale' => 'en_attente', 'est_rachete' => 0];
        }
    }

    /**
     * Déduit le choix d'interface (5 décisions du conseil) à partir d'une ligne de délibération existante
     */
    public static function choiceFromRecord(array $record): string
    {
        $dec = (string)($record['decision_finale'] ?? 'en_attente');
        $rachete = !empty($record['est_rachete']);
        if ($dec === 'admis') {
            return $rachete ? 'admis_grace' : 'admis_office';
        }
        if ($dec === 'redouble' || $dec === 'ajourne') {
            return 'redouble';
        }
        if ($dec === 'exclu') {
            return 'exclu';
        }
        return 'en_attente';
    }

    /**
     * Prépare toutes les données de la séance de Conseil de Classe & Délibération Annuelle pour une classe
     * - Si la délibération est VALIDÉE (figée), lit directement les données figées (O(1), zéro recalcul).
     * - Sinon (non délibérée ou provisoire), calcule en direct les moyennes annuelles (S1, S2, Examen, Annuelle)
     *   et fusionne les décisions brouillon déjà saisies s'il en existe.
     */
    public function getConseilClasseData(string $classeCode, string $anneeCode): array
    {
        $empty = [
            'classeInfo' => null,
            'anneeLibelle' => '-',
            'est_premiere_annee' => false,
            'lastSemestreCode' => '',
            'summary' => $this->getClasseDeliberationSummary('', ''),
            'rows' => []
        ];

        if (trim($classeCode) === '' || trim($anneeCode) === '') {
            return $empty;
        }

        $db = $this->getCon();
        $classeInfo = (new ModelClasse())->getClasseWithDetails($classeCode);
        if (!$classeInfo) {
            return $empty;
        }

        $anneeObj = (new ModelAnnee())->getByCode($anneeCode);
        $anneeLibelle = $anneeObj['libelle_annee'] ?? $anneeCode;

        require_once __DIR__ . '/ModelBulletin.php';
        $modelBulletin = new ModelBulletin();

        $estPremiereAnnee = $modelBulletin->isPremiereAnnee(
            $classeInfo['slug_niveau'] ?? null,
            $classeInfo['libelle_niveau'] ?? null
        );

        $semestres = $modelBulletin->getActiveSemestres($anneeCode);
        if (empty($semestres)) {
            $semestres = (new ModelSemestre())->getAll($anneeCode);
        }
        $lastSemestreCode = !empty($semestres) ? (string)(end($semestres)['code_semestre'] ?? '') : '';

        // Étudiants inscrits dans la classe pour cette année
        $stmtEtu = $db->prepare("
            SELECT id_inscription, code_inscription, etudiant_code, etudiant_nom, matricule_etudiant, sexe_etudiant
            FROM " . VIEWS::BULLETIN_INSCRIPTIONS_SYNTHESE . "
            WHERE classe_code = ? AND annee_code = ?
              AND (statut_inscription != '" . STATUTS::ANNULE . "' OR statut_inscription IS NULL)
            ORDER BY etudiant_nom ASC
        ");
        $stmtEtu->execute([$classeCode, $anneeCode]);
        $inscriptions = $stmtEtu->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $existingMap = $this->getByClasseAndAnnee($classeCode, $anneeCode);
        $summary = $this->getClasseDeliberationSummary($classeCode, $anneeCode);
        $isFrozen = !empty($summary['is_delibere']);

        $inscCodes = array_column($inscriptions, 'code_inscription');
        $absencesMap = $this->getAbsencesByInscriptions($inscCodes, $anneeCode);
        // Lecture en 1 seule requête SQL depuis VIEWS::SYNTHESE_MOYENNES_ANNUELLES si non figé
        $syntheseMap = !$isFrozen ? $this->getSyntheseAnnuelleByClasse($classeCode, $anneeCode) : [];

        $modelConfig = new ModelConfiguration();
        $baremesBulletin = $modelConfig->getBaremesBulletin(true);

        $rows = [];
        $effectifTotal = count($inscriptions);

        foreach ($inscriptions as $ins) {
            $idIns = (int)$ins['id_inscription'];
            $codeIns = (string)$ins['code_inscription'];
            $existing = $existingMap[$codeIns] ?? null;
            $abs = $absencesMap[$codeIns] ?? ['total_heures' => 0.0, 'heures_justifiees' => 0.0, 'heures_injustifiees' => 0.0];

            $moyS1 = null;
            $moyS2 = null;
            $moyEx = null;
            $moyAnn = null;
            $rangAnn = null;
            $mentionFinale = 'Non évalué';
            $codeDist = 'AUCUN';
            $hasNotes = false;
            $autoChoice = 'en_attente';
            $autoLibelle = 'En attente de notes';

            if ($isFrozen && $existing && ($existing['statut_deliberation'] ?? '') === STATUTS::VALIDE) {
                // Lecture directe des valeurs figées (O(1) sans recalculer les bulletins)
                $moyS1 = $existing['moyenne_s1'] !== null ? round((float)$existing['moyenne_s1'], 2) : null;
                $moyS2 = $existing['moyenne_s2'] !== null ? round((float)$existing['moyenne_s2'], 2) : null;
                $moyEx = $existing['moyenne_examen'] !== null ? round((float)$existing['moyenne_examen'], 2) : null;
                $moyAnn = $existing['moyenne_annuelle'] !== null ? round((float)$existing['moyenne_annuelle'], 2) : null;
                $rangAnn = $existing['rang_annuel'] !== null ? (int)$existing['rang_annuel'] : null;
                $mentionFinale = !empty($existing['mention_finale']) ? (string)$existing['mention_finale'] : 'Non évalué';
                $codeDist = !empty($existing['code_distinction_sanction']) ? (string)$existing['code_distinction_sanction'] : 'AUCUN';
                $hasNotes = ($moyAnn !== null);
            } else {
                // Lecture directe en batch depuis VIEWS::SYNTHESE_MOYENNES_ANNUELLES (1 requête SQL pour toute la classe)
                $syn = $syntheseMap[$codeIns] ?? null;
                $moyS1 = ($syn && $syn['moyenne_s1'] !== null) ? round((float)$syn['moyenne_s1'], 2) : null;
                $moyS2 = ($syn && $syn['moyenne_s2'] !== null) ? round((float)$syn['moyenne_s2'], 2) : null;
                $moyEx = ($syn && $syn['moyenne_examen'] !== null) ? round((float)$syn['moyenne_examen'], 2) : null;
                $hasNotes = $syn && !empty($syn['has_notes']) && $syn['moyenne_annuelle'] !== null;

                if ($hasNotes) {
                    $moyAnn = round((float)$syn['moyenne_annuelle'], 2);
                    $evalAnn = $modelConfig->evaluateNote((float)$moyAnn, $baremesBulletin);
                    $mentionFinale = !empty($evalAnn['mention_bulletin']) && $evalAnn['mention_bulletin'] !== '-'
                        ? (string)$evalAnn['mention_bulletin']
                        : '-';
                    $codeDist = !empty($evalAnn['code_distinction_sanction'])
                        ? (string)$evalAnn['code_distinction_sanction']
                        : 'AUCUN';
                }
            }

            if ($hasNotes && $moyAnn !== null) {
                if ($moyAnn >= 10.0) {
                    $autoChoice = 'admis_office';
                    $autoLibelle = 'Admis(e) d\'office';
                } else {
                    $autoChoice = 'redouble';
                    $autoLibelle = 'Redoublement (Recalé)';
                }
            }

            // Décision active : si un enregistrement (brouillon ou validé) existe, on reprend le choix du conseil,
            // sinon on pré-remplit avec la proposition automatique (modifiable cas par cas avant validation)
            if ($existing) {
                $decisionChoice = self::choiceFromRecord($existing);
                $decisionFinale = (string)($existing['decision_finale'] ?? 'en_attente');
                $estRachete = !empty($existing['est_rachete']) ? 1 : 0;
                $libelleDecision = !empty($existing['libelle_decision'])
                    ? (string)$existing['libelle_decision']
                    : self::defaultLabelForDecision($decisionFinale, $estRachete === 1);
                $observationJury = (string)($existing['observation_jury'] ?? '');
                $statutDelib = (string)($existing['statut_deliberation'] ?? 'provisoire');
                $hasSavedRecord = true;
            } else {
                $decisionChoice = $autoChoice;
                $mapped = self::mapChoiceToFields($decisionChoice);
                $decisionFinale = $mapped['decision_finale'];
                $estRachete = $mapped['est_rachete'];
                $libelleDecision = self::defaultLabelForDecision($decisionFinale, $estRachete === 1);
                $observationJury = '';
                $statutDelib = 'non_delibere';
                $hasSavedRecord = false;
            }

            $rows[] = [
                'id_inscription' => $idIns,
                'code_inscription' => $codeIns,
                'etudiant_code' => (string)$ins['etudiant_code'],
                'matricule' => (string)($ins['matricule_etudiant'] ?? ''),
                'nom_prenom' => (string)($ins['etudiant_nom'] ?? ''),
                'sexe' => (string)($ins['sexe_etudiant'] ?? ''),
                'moyenne_s1' => $moyS1,
                'moyenne_s2' => $moyS2,
                'moyenne_examen' => $moyEx,
                'moyenne_annuelle' => $moyAnn,
                'rang_annuel' => $rangAnn,
                'effectif_classe' => $effectifTotal,
                'mention_finale' => $mentionFinale,
                'code_distinction_sanction' => $codeDist,
                'has_notes' => $hasNotes,
                'absences_total' => $abs['total_heures'],
                'absences_justifiees' => $abs['heures_justifiees'],
                'absences_injustifiees' => $abs['heures_injustifiees'],
                'auto_choice' => $autoChoice,
                'auto_libelle' => $autoLibelle,
                'decision_choice' => $decisionChoice,
                'decision_finale' => $decisionFinale,
                'est_rachete' => $estRachete,
                'libelle_decision' => $libelleDecision,
                'observation_jury' => $observationJury,
                'statut_deliberation' => $statutDelib,
                'has_saved_record' => $hasSavedRecord
            ];
        }

        // Trier par moyenne annuelle décroissante (évalués d'abord, puis non évalués par ordre alphabétique)
        usort($rows, function ($a, $b) {
            $aEval = !empty($a['has_notes']) && $a['moyenne_annuelle'] !== null ? 1 : 0;
            $bEval = !empty($b['has_notes']) && $b['moyenne_annuelle'] !== null ? 1 : 0;
            if ($aEval !== $bEval) {
                return $bEval <=> $aEval;
            }
            if ($aEval === 1 && $b['moyenne_annuelle'] !== $a['moyenne_annuelle']) {
                return $b['moyenne_annuelle'] <=> $a['moyenne_annuelle'];
            }
            return strcmp((string)$a['nom_prenom'], (string)$b['nom_prenom']);
        });

        // Recalcul propre des rangs avec ex-æquo si non figé ou rang manquant
        $evalIndex = 0;
        $currentRank = 0;
        $prevKey = null;
        $countsMoy = [];
        foreach ($rows as $rc) {
            if (!empty($rc['has_notes']) && $rc['moyenne_annuelle'] !== null) {
                $k = number_format((float)$rc['moyenne_annuelle'], 2, '.', '');
                $countsMoy[$k] = ($countsMoy[$k] ?? 0) + 1;
            }
        }
        foreach ($rows as &$r) {
            if (!empty($r['has_notes']) && $r['moyenne_annuelle'] !== null) {
                $evalIndex++;
                $k = number_format((float)$r['moyenne_annuelle'], 2, '.', '');
                if ($prevKey === null || $k !== $prevKey) {
                    $currentRank = $evalIndex;
                    $prevKey = $k;
                }
                if (!$isFrozen || $r['rang_annuel'] === null) {
                    $r['rang_annuel'] = $currentRank;
                }
                $r['ex_aequo'] = (($countsMoy[$k] ?? 0) > 1);
            } else {
                $r['rang_annuel'] = null;
                $r['ex_aequo'] = false;
            }
        }
        unset($r);

        return [
            'classeInfo' => $classeInfo,
            'anneeLibelle' => $anneeLibelle,
            'est_premiere_annee' => $estPremiereAnnee,
            'lastSemestreCode' => $lastSemestreCode,
            'summary' => $summary,
            'rows' => $rows
        ];
    }

    /**
     * Enregistre en masse les décisions individuelles du Conseil de Classe (en brouillon 'provisoire' ou clôturé 'valide').
     * Recalcule les moyennes et rangs côté serveur pour garantir l'intégrité des données.
     */
    public function saveSessionDeliberation(
        string $classeCode,
        string $anneeCode,
        array $decisionsInput,
        string $mode,
        string $userCode,
        string $etabCode
    ): array {
        $db = $this->getCon();
        if (trim($classeCode) === '' || trim($anneeCode) === '') {
            return ['success' => false, 'message' => 'Classe et année académique requises.'];
        }

        $targetStatut = ($mode === STATUTS::VALIDE) ? STATUTS::VALIDE : 'provisoire';

        require_once __DIR__ . '/ModelBulletin.php';
        $modelBulletin = new ModelBulletin();

        $semestres = $modelBulletin->getActiveSemestres($anneeCode);
        if (empty($semestres)) {
            $semestres = (new ModelSemestre())->getAll($anneeCode);
        }
        $lastSemestreCode = !empty($semestres) ? (string)(end($semestres)['code_semestre'] ?? '') : null;

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

        // Indexer les décisions soumises par inscription_code
        $inputMap = [];
        foreach ($decisionsInput as $item) {
            if (!is_array($item)) continue;
            $code = trim((string)($item['inscription_code'] ?? ''));
            if ($code !== '') {
                $inputMap[$code] = $item;
            }
        }

        $syntheseMap = $this->getSyntheseAnnuelleByClasse($classeCode, $anneeCode);
        $existingMap = $this->getByClasseAndAnnee($classeCode, $anneeCode);
        $modelConfig = new ModelConfiguration();
        $baremesBulletin = $modelConfig->getBaremesBulletin(true);
        $validator = new Validator();
        $now = date('Y-m-d H:i:s');
        $effectifTotal = count($inscriptions);

        // Pré-calculer les moyennes annuelles de tous les inscrits via VIEWS::SYNTHESE_MOYENNES_ANNUELLES (1 seule requête)
        $computed = [];
        foreach ($inscriptions as $ins) {
            $codeIns = (string)$ins['code_inscription'];
            $syn = $syntheseMap[$codeIns] ?? null;

            $moyS1 = ($syn && $syn['moyenne_s1'] !== null) ? round((float)$syn['moyenne_s1'], 2) : null;
            $moyS2 = ($syn && $syn['moyenne_s2'] !== null) ? round((float)$syn['moyenne_s2'], 2) : null;
            $moyEx = ($syn && $syn['moyenne_examen'] !== null) ? round((float)$syn['moyenne_examen'], 2) : null;
            $hasNotes = $syn && !empty($syn['has_notes']) && $syn['moyenne_annuelle'] !== null;

            $moyAnn = null;
            $mentionFinale = 'Non évalué';
            $codeDist = 'AUCUN';
            $baremeDecision = '';

            if ($hasNotes) {
                $moyAnn = round((float)$syn['moyenne_annuelle'], 2);
                $evalAnn = $modelConfig->evaluateNote((float)$moyAnn, $baremesBulletin);
                $mentionFinale = !empty($evalAnn['mention_bulletin']) && $evalAnn['mention_bulletin'] !== '-'
                    ? (string)$evalAnn['mention_bulletin']
                    : '-';
                $codeDist = !empty($evalAnn['code_distinction_sanction'])
                    ? (string)$evalAnn['code_distinction_sanction']
                    : 'AUCUN';
                $baremeDecision = !empty($evalAnn['appreciation_conseil_s2']) && $evalAnn['appreciation_conseil_s2'] !== '-'
                    ? (string)$evalAnn['appreciation_conseil_s2']
                    : (!empty($evalAnn['appreciation_conseil']) && $evalAnn['appreciation_conseil'] !== '-' ? (string)$evalAnn['appreciation_conseil'] : '');
            }

            $computed[$codeIns] = [
                'ins' => $ins,
                'moy_s1' => $moyS1,
                'moy_s2' => $moyS2,
                'moy_ex' => $moyEx,
                'moy_ann' => $moyAnn,
                'has_notes' => $hasNotes,
                'mention_finale' => $mentionFinale,
                'code_dist' => $codeDist,
                'bareme_decision' => $baremeDecision,
                'rang_annuel' => null
            ];
        }

        // Calculer les rangs annuels (avec ex-æquo)
        $rankedCodes = array_keys(array_filter($computed, fn($c) => $c['has_notes'] && $c['moy_ann'] !== null));
        usort($rankedCodes, fn($a, $b) => $computed[$b]['moy_ann'] <=> $computed[$a]['moy_ann']);
        $pos = 0;
        $curRank = 0;
        $prevM = null;
        foreach ($rankedCodes as $cCode) {
            $pos++;
            $mKey = number_format((float)$computed[$cCode]['moy_ann'], 2, '.', '');
            if ($prevM === null || $mKey !== $prevM) {
                $curRank = $pos;
                $prevM = $mKey;
            }
            $computed[$cCode]['rang_annuel'] = $curRank;
        }

        $counts = [
            'saved' => 0,
            'admis_office' => 0,
            'admis_grace' => 0,
            'redouble' => 0,
            'exclu' => 0,
            'en_attente' => 0
        ];

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
                    ?, ?, ?, ?, ?, ?
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
                    statut_deliberation = VALUES(statut_deliberation),
                    user_code = VALUES(user_code),
                    updated_at_deliberation = VALUES(updated_at_deliberation)
            ";
            $stmtUpsert = $db->prepare($sqlUpsert);

            foreach ($computed as $codeIns => $cData) {
                $ins = $cData['ins'];
                $existing = $existingMap[$codeIns] ?? null;
                $submitted = $inputMap[$codeIns] ?? null;

                // Résoudre le choix de décision
                if ($submitted && !empty($submitted['decision_choice'])) {
                    $choice = trim((string)$submitted['decision_choice']);
                } elseif ($existing) {
                    $choice = self::choiceFromRecord($existing);
                } elseif ($cData['has_notes'] && $cData['moy_ann'] !== null) {
                    $choice = ($cData['moy_ann'] >= 10.0) ? 'admis_office' : 'redouble';
                } else {
                    $choice = 'en_attente';
                }

                if (!in_array($choice, ['admis_office', 'admis_grace', 'redouble', 'exclu', 'en_attente'], true)) {
                    $choice = 'en_attente';
                }

                $mapped = self::mapChoiceToFields($choice);
                $decisionFinale = $mapped['decision_finale'];
                $estRachete = (int)$mapped['est_rachete'];

                // Libellé officiel
                $customLib = $submitted && isset($submitted['libelle_decision']) ? trim((string)$submitted['libelle_decision']) : '';
                if ($customLib !== '') {
                    $libelleDecision = $customLib;
                } elseif ($choice === 'admis_office' && $cData['bareme_decision'] !== '' && $cData['moy_ann'] !== null && $cData['moy_ann'] >= 10.0) {
                    $libelleDecision = $cData['bareme_decision'];
                } else {
                    $libelleDecision = self::defaultLabelForDecision($decisionFinale, $estRachete === 1);
                }

                $obsRaw = $submitted && array_key_exists('observation_jury', $submitted)
                    ? trim((string)$submitted['observation_jury'])
                    : (string)($existing['observation_jury'] ?? '');
                $observationJury = ($obsRaw !== '') ? $obsRaw : null;

                $codeDelib = $existing['code_deliberation'] ?? $validator->generateCode(TABLES::DELIBERATIONS, 'code_deliberation', 'DEL-', 8);
                $etabIns = !empty($ins['etablissement_code']) ? (string)$ins['etablissement_code'] : $etabCode;

                $stmtUpsert->execute([
                    $codeDelib,
                    $codeIns,
                    (string)$ins['etudiant_code'],
                    $classeCode,
                    $anneeCode,
                    $etabIns,
                    $cData['moy_s1'],
                    $cData['moy_s2'],
                    $cData['moy_ex'],
                    $cData['moy_ann'],
                    $cData['rang_annuel'],
                    $effectifTotal,
                    $cData['mention_finale'],
                    $cData['code_dist'],
                    $decisionFinale,
                    $libelleDecision,
                    $estRachete,
                    $observationJury,
                    $targetStatut,
                    $userCode,
                    $now,
                    $now
                ]);

                $counts['saved']++;
                $counts[$choice]++;
            }

            $db->commit();

            $msg = ($targetStatut === STATUTS::VALIDE)
                ? "Délibération annuelle clôturée et figée pour {$counts['saved']} étudiant(s) ({$counts['admis_office']} admis d'office, {$counts['admis_grace']} par grâce, {$counts['redouble']} redoublement(s), {$counts['exclu']} exclu(s))."
                : "Brouillon de délibération enregistré pour {$counts['saved']} étudiant(s). Les décisions restent modifiables jusqu'à la clôture.";

            return [
                'success' => true,
                'message' => $msg,
                'statut_deliberation' => $targetStatut,
                'counts' => $counts,
                'date_deliberation' => $now
            ];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("ModelDeliberation::saveSessionDeliberation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement de la délibération : ' . $e->getMessage()];
        }
    }

    /**
     * Libellé métier par défaut selon le code de décision
     */
    public static function defaultLabelForDecision(string $decisionFinale, bool $estRachete = false): string
    {
        switch ($decisionFinale) {
            case 'admis':
                return $estRachete ? 'Admis(e) par grâce (Rachat)' : 'Admis(e) d\'office';
            case 'redouble':
                return 'Redoublement (Recalé)';
            case 'exclu':
                return 'Exclu(e)';
            case 'ajourne':
                return 'Ajourné(e)';
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
