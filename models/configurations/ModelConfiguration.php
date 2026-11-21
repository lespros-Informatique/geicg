<?php

class ModelConfiguration extends BaseModel
{
    protected string $table = TABLES::CONFIGURATIONS;
    protected string $primaryKey = 'id_configuration';
    protected ?string $createdAtField = 'updated_at';

    /**
     * Récupère la configuration institutionnelle active
     */
    public function getConfig(): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} ASC LIMIT 1";
            $stmt = $this->pdo->getCon()->query($sql);
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
            return $row ?: [];
        } catch (Exception $e) {
            error_log("ModelConfiguration::getConfig error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Enregistre ou met à jour la configuration unique
     */
    public function saveConfig(array $data): bool
    {
        try {
            $existing = $this->getConfig();
            $filtered = $this->filterData($data);

            if (!empty($existing) && !empty($existing[$this->primaryKey])) {
                $id = (int)$existing[$this->primaryKey];
                return $this->update($filtered, $id);
            } else {
                return (bool)$this->create($filtered);
            }
        } catch (Exception $e) {
            error_log("ModelConfiguration::saveConfig error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les paliers actifs du barème de notation / mentions du bulletin
     */
    public function getBaremesBulletin(bool $onlyActive = true): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT * FROM " . TABLES::BAREMES_BULLETIN;
            if ($onlyActive) {
                $sql .= " WHERE statut_bareme = '" . STATUTS::ACTIF . "'";
            }
            $sql .= " ORDER BY note_min DESC, ordre_affichage ASC";
            $stmt = $db->query($sql);
            return $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
        } catch (Exception $e) {
            error_log("ModelConfiguration::getBaremesBulletin error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Évalue dynamiquement une moyenne selon les paliers de la table baremes_bulletin
     */
    public function evaluateNote(float $note, ?array $baremes = null): array
    {
        if ($baremes === null) {
            $baremes = $this->getBaremesBulletin(true);
        }

        $rounded = round($note, 2);
        foreach ($baremes as $b) {
            $min = (float)($b['note_min'] ?? 0);
            $max = (float)($b['note_max'] ?? 20);
            if ($rounded >= $min && $rounded <= $max) {
                return [
                    'appreciation_matiere'      => $b['appreciation_matiere'] ?? '',
                    'mention_bulletin'          => $b['mention_bulletin'] ?? '',
                    'appreciation_conseil'      => $b['appreciation_conseil'] ?? '',
                    'appreciation_conseil_s2'   => !empty($b['appreciation_conseil_s2']) ? $b['appreciation_conseil_s2'] : ($b['appreciation_conseil'] ?? ''),
                    'code_distinction_sanction' => $b['code_distinction_sanction'] ?? 'AUCUN'
                ];
            }
        }

        // Si la note dépasse la borne ou tombe entre deux décimales (ex: 15.995), prendre le palier dont note_min <= $note
        foreach ($baremes as $b) {
            $min = (float)($b['note_min'] ?? 0);
            if ($note >= $min) {
                return [
                    'appreciation_matiere'      => $b['appreciation_matiere'] ?? '',
                    'mention_bulletin'          => $b['mention_bulletin'] ?? '',
                    'appreciation_conseil'      => $b['appreciation_conseil'] ?? '',
                    'appreciation_conseil_s2'   => !empty($b['appreciation_conseil_s2']) ? $b['appreciation_conseil_s2'] : ($b['appreciation_conseil'] ?? ''),
                    'code_distinction_sanction' => $b['code_distinction_sanction'] ?? 'AUCUN'
                ];
            }
        }

        return [
            'appreciation_matiere'      => '-',
            'mention_bulletin'          => '-',
            'appreciation_conseil'      => '-',
            'appreciation_conseil_s2'   => '-',
            'code_distinction_sanction' => 'AUCUN'
        ];
    }

    /**
     * Enregistre les paliers du barème de bulletin soumis depuis /etablissement/config
     */
    public function saveBaremesBulletin(array $rows): bool
    {
        $db = $this->pdo->getCon();
        try {
            $db->beginTransaction();

            $allowedCodes = ['AUCUN', 'FELICITATIONS', 'ENCOURAGEMENTS', 'HONNEUR', 'AVERTISSEMENT', 'BLAME'];
            $keptIds = [];
            $ordre = 1;

            foreach ($rows as $r) {
                $noteMin = isset($r['note_min']) ? round((float)str_replace(',', '.', (string)$r['note_min']), 2) : 0.0;
                $noteMax = isset($r['note_max']) ? round((float)str_replace(',', '.', (string)$r['note_max']), 2) : 20.0;
                $apprMat = trim((string)($r['appreciation_matiere'] ?? ''));
                $mention = trim((string)($r['mention_bulletin'] ?? ''));
                $apprCons = trim((string)($r['appreciation_conseil'] ?? ''));
                $apprConsS2 = trim((string)($r['appreciation_conseil_s2'] ?? ''));
                if ($apprConsS2 === '') {
                    $apprConsS2 = $apprCons;
                }
                $codeDist = strtoupper(trim((string)($r['code_distinction_sanction'] ?? 'AUCUN')));
                if (!in_array($codeDist, $allowedCodes, true)) {
                    $codeDist = 'AUCUN';
                }

                if ($apprMat === '' && $mention === '' && $apprCons === '' && $apprConsS2 === '') {
                    continue;
                }

                $idBareme = !empty($r['id_bareme']) ? (int)$r['id_bareme'] : 0;
                if ($idBareme > 0) {
                    $stmtUpd = $db->prepare("
                        UPDATE " . TABLES::BAREMES_BULLETIN . "
                        SET note_min = ?, note_max = ?, appreciation_matiere = ?, mention_bulletin = ?,
                            appreciation_conseil = ?, appreciation_conseil_s2 = ?, code_distinction_sanction = ?, ordre_affichage = ?, statut_bareme = 'actif'
                        WHERE id_bareme = ?
                    ");
                    $stmtUpd->execute([$noteMin, $noteMax, $apprMat, $mention, $apprCons, $apprConsS2, $codeDist, $ordre, $idBareme]);
                    $keptIds[] = $idBareme;
                } else {
                    $codeBareme = 'BAR-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
                    $stmtIns = $db->prepare("
                        INSERT INTO " . TABLES::BAREMES_BULLETIN . "
                        (code_bareme, note_min, note_max, appreciation_matiere, mention_bulletin, appreciation_conseil, appreciation_conseil_s2, code_distinction_sanction, ordre_affichage, statut_bareme)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif')
                    ");
                    $stmtIns->execute([$codeBareme, $noteMin, $noteMax, $apprMat, $mention, $apprCons, $apprConsS2, $codeDist, $ordre]);
                    $keptIds[] = (int)$db->lastInsertId();
                }
                $ordre++;
            }

            if (!empty($keptIds)) {
                $placeholders = implode(',', array_fill(0, count($keptIds), '?'));
                $stmtDel = $db->prepare("DELETE FROM " . TABLES::BAREMES_BULLETIN . " WHERE id_bareme NOT IN ({$placeholders})");
                $stmtDel->execute($keptIds);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("ModelConfiguration::saveBaremesBulletin error: " . $e->getMessage());
            return false;
        }
    }
}

