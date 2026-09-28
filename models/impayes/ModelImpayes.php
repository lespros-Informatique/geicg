<?php

class ModelImpayes extends BaseModel
{
    protected string $table = 'relances_impayes';
    protected string $primaryKey = 'id_relance';
    protected ?string $createdAtField = 'created_at_relance';

    public function getAll(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null): array
    {
        try {
            $sql = "SELECT r.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, cl.libelle_classe, n.libelle_niveau 
                    FROM relances_impayes r
                    LEFT JOIN etudiants e ON r.etudiant_code = e.code_etudiant
                    LEFT JOIN inscriptions ins ON (ins.code_inscription = r.inscription_code OR (ins.etudiant_code = r.etudiant_code AND ins.statut_inscription != 'annule'))
                    LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                    LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code";
            $conditions = [];
            $params = [];
            if (!empty($anneeCode)) {
                $conditions[] = "r.annee_code = ?";
                $params[] = $anneeCode;
            }
            if (!empty($niveauCode)) {
                $conditions[] = "cl.niveau_code = ?";
                $params[] = $niveauCode;
            }
            if (!empty($classeCode)) {
                $conditions[] = "ins.classe_code = ?";
                $params[] = $classeCode;
            }
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            $sql .= " GROUP BY r.id_relance ORDER BY r.id_relance DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all relances_impayes: " . $e->getMessage());
            return [];
        }
    }

    public function getOverdueStudentsDetailed(?string $anneeCode = null, ?string $niveauCode = null, ?string $classeCode = null, ?string $severiteFilter = 'ALL'): array
    {
        try {
            $db = $this->getCon();
            require_once __DIR__ . '/../frais_annexes/ModelFraisAnnexe.php';
            $modelFA = new ModelFraisAnnexe();

            $where = "WHERE i.statut_inscription != 'annule'";
            $params = [];
            if (!empty($anneeCode)) {
                $where .= " AND i.annee_code = ?";
                $params[] = $anneeCode;
            }
            if (!empty($niveauCode) && $niveauCode !== 'ALL') {
                $where .= " AND c.niveau_code = ?";
                $params[] = $niveauCode;
            }
            if (!empty($classeCode) && $classeCode !== 'ALL') {
                $where .= " AND i.classe_code = ?";
                $params[] = $classeCode;
            }

            $sqlInscr = "
                SELECT 
                    i.id_inscription,
                    i.code_inscription,
                    i.statut_inscription,
                    i.affectation_etat,
                    i.montant_scolarite_inscription,
                    i.photo_inscription,
                    i.annee_code,
                    e.code_etudiant,
                    e.matricule_etudiant,
                    e.nom_etudiant,
                    e.prenom_etudiant,
                    e.telephone_etudiant,
                    e.photo_etudiant,
                    c.code_classe,
                    c.libelle_classe,
                    c.filiere_code,
                    c.niveau_code,
                    f.libelle_filiere,
                    f.slug_filiere,
                    f.type_filiere,
                    n.libelle_niveau,
                    n.slug_niveau,
                    p.nom_pere, p.telephone_pere,
                    p.nom_mere, p.telephone_mere,
                    p.nom_tuteur, p.telephone_tuteur
                FROM inscriptions i
                JOIN etudiants e ON i.etudiant_code = e.code_etudiant
                LEFT JOIN classes c ON i.classe_code = c.code_classe
                LEFT JOIN filieres f ON c.filiere_code = f.code_filiere
                LEFT JOIN niveaux n ON c.niveau_code = n.code_niveau
                LEFT JOIN parents p ON p.etudiant_code = e.code_etudiant
                {$where}
                ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
            ";
            $stmtInscr = $db->prepare($sqlInscr);
            $stmtInscr->execute($params);
            $inscriptions = $stmtInscr->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Totaux paiements effectués
            $stmtPay = $db->prepare("
                SELECT inscription_code, SUM(montant_paiement) as total_paye
                FROM paiements
                WHERE statut_paiement != 'annule'
                GROUP BY inscription_code
            ");
            $stmtPay->execute();
            $paymentsGrouped = [];
            foreach ($stmtPay->fetchAll(PDO::FETCH_ASSOC) as $pRow) {
                $paymentsGrouped[$pRow['inscription_code']] = (float)$pRow['total_paye'];
            }

            // Scolarités & Tranches
            $stmtSco = $db->prepare("SELECT filiere_code, niveau_code, affectation_etat, montant_scolarite FROM scolarites WHERE statut_scolarite = 'actif'");
            $stmtSco->execute();
            $scolariteGrid = $stmtSco->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $stmtTranches = $db->prepare("SELECT * FROM tranches_scolarite WHERE statut_tranche = 'actif' ORDER BY date_limite ASC, id_tranche ASC");
            $stmtTranches->execute();
            $tranchesGrid = $stmtTranches->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $today = date('Y-m-d');
            $todayTime = strtotime($today);

            $results = [];
            $kpiTotalImpayesEchus = 0;
            $kpiTotalEtudiantsRetard = 0;
            $kpiTotalAffectes = 0;
            $kpiTotalNonAffectes = 0;
            $totalJoursAccumules = 0;

            foreach ($inscriptions as $ins) {
                $codeIns = $ins['code_inscription'];
                $filiereCode = $ins['filiere_code'] ?? '';
                $nCode = $ins['niveau_code'] ?? '';
                $aCode = $ins['annee_code'] ?? '';
                $affRaw = $ins['affectation_etat'] ?? 'non';
                $isAffecte = ($affRaw === 'affecte' || $affRaw === 'oui');
                $targetAff = $isAffecte ? 'affecte' : 'non_affecte';
                $typeFiliere = $ins['type_filiere'] ?? 'TERTIAIRE';

                // Calcul du montant de scolarité
                $scolariteDue = 0;
                foreach ($scolariteGrid as $sg) {
                    $sgAff = ($sg['affectation_etat'] === 'affecte' || $sg['affectation_etat'] === 'oui') ? 'affecte' : 'non_affecte';
                    if ($sg['filiere_code'] === $filiereCode && $sg['niveau_code'] === $nCode && $sgAff === $targetAff) {
                        $scolariteDue = (float)$sg['montant_scolarite'];
                        break;
                    }
                }
                if ($scolariteDue <= 0) {
                    $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);
                }

                $fraisAnnexesDus = (float)$modelFA->getMontantByTypeFiliere($typeFiliere, $aCode, $nCode, 'inscription');
                $totalAttendu = $scolariteDue + $fraisAnnexesDus;
                $totalEncaisse = $paymentsGrouped[$codeIns] ?? 0;
                $soldeRestant = max(0, $totalAttendu - $totalEncaisse);

                // Si le solde global est nul ou négatif, l'étudiant a tout réglé
                if ($soldeRestant <= 0) {
                    continue;
                }

                // Filtrer les tranches associées à l'étudiant
                $studentTranches = array_filter($tranchesGrid, function($t) use ($filiereCode, $nCode, $aCode) {
                    $matchFil = empty($t['filiere_code']) || $t['filiere_code'] === $filiereCode;
                    $matchNiv = empty($t['niveau_code']) || $t['niveau_code'] === $nCode;
                    $matchAnn = empty($t['annee_code']) || $t['annee_code'] === $aCode;
                    return $matchFil && $matchNiv && $matchAnn;
                });

                $isOverdue = false;
                $echeanceOverdue = null;
                $cumulAttenduTranche = 0;
                $montantEchu = 0;
                $retardJoursMax = 0;

                if (!empty($studentTranches)) {
                    foreach ($studentTranches as $t) {
                        $cumulAttenduTranche += (float)$t['montant_tranche'];
                        $dateLimite = $t['date_limite'];
                        $limiteTime = strtotime($dateLimite);

                        // Si la date limite est passée et que les versements sont insuffisants
                        if ($limiteTime < $todayTime && $totalEncaisse < $cumulAttenduTranche) {
                            $isOverdue = true;
                            $diffSec = $todayTime - $limiteTime;
                            $jRetard = max(1, (int)floor($diffSec / 86400));

                            if ($jRetard > $retardJoursMax) {
                                $retardJoursMax = $jRetard;
                                $echeanceOverdue = $t;
                            }
                            $manquantTranche = $cumulAttenduTranche - $totalEncaisse;
                            $montantEchu = max($montantEchu, min($soldeRestant, $manquantTranche));
                        }
                    }
                }

                // En cas de solde restant positif et absence de grille spécifique de tranches, ou reliquat échu
                if (!$isOverdue && $soldeRestant > 0) {
                    // Considérer comme impayé global en attente de régularisation
                    $isOverdue = true;
                    $montantEchu = $soldeRestant;
                    $retardJoursMax = 1;
                }

                if (!$isOverdue) {
                    continue;
                }

                // Sévérité du retard
                $severiteCode = 'leger';
                $severiteLibelle = 'Retard Léger';
                $badgeClass = 'badge-warning';

                if ($retardJoursMax > 30) {
                    $severiteCode = 'critique';
                    $severiteLibelle = 'Retard Critique';
                    $badgeClass = 'badge-danger';
                } elseif ($retardJoursMax > 15) {
                    $severiteCode = 'modere';
                    $severiteLibelle = 'Retard Modéré';
                    $badgeClass = 'badge-orange';
                }

                if ($severiteFilter !== 'ALL' && !empty($severiteFilter) && $severiteFilter !== $severiteCode) {
                    continue;
                }

                // Déterminer les contacts du parent/tuteur
                $nomParent = '-';
                $telParent = '-';

                if (!empty($ins['nom_tuteur'])) {
                    $nomParent = trim($ins['nom_tuteur']) . ' (Tuteur)';
                    $telParent = $ins['telephone_tuteur'] ?? '-';
                } elseif (!empty($ins['nom_pere'])) {
                    $nomParent = trim($ins['nom_pere']) . ' (Père)';
                    $telParent = $ins['telephone_pere'] ?? '-';
                } elseif (!empty($ins['nom_mere'])) {
                    $nomParent = trim($ins['nom_mere']) . ' (Mère)';
                    $telParent = $ins['telephone_mere'] ?? '-';
                }

                if ($telParent === '-' || empty($telParent)) {
                    $telParent = !empty($ins['telephone_etudiant']) ? $ins['telephone_etudiant'] : 'N/A';
                }

                // Initiales & Photo
                $nomStr = trim($ins['nom_etudiant'] ?? '');
                $prenomStr = trim($ins['prenom_etudiant'] ?? '');
                $iNom = !empty($nomStr) ? mb_substr($nomStr, 0, 1, 'UTF-8') : '';
                $iPrenom = !empty($prenomStr) ? mb_substr($prenomStr, 0, 1, 'UTF-8') : '';
                $initiales = strtoupper($iNom . $iPrenom);

                $kpiTotalImpayesEchus += $montantEchu;
                $kpiTotalEtudiantsRetard++;
                if ($isAffecte) {
                    $kpiTotalAffectes++;
                } else {
                    $kpiTotalNonAffectes++;
                }
                $totalJoursAccumules += $retardJoursMax;

                $photoRaw = !empty($ins['photo_etudiant']) ? trim($ins['photo_etudiant']) : (!empty($ins['photo_inscription']) ? trim($ins['photo_inscription']) : '');
                $photoUrl = '';
                if (!empty($photoRaw)) {
                    if (str_starts_with($photoRaw, 'http') || str_starts_with($photoRaw, 'data:')) {
                        $photoUrl = $photoRaw;
                    } else {
                        $photoUrl = RACINE . ltrim($photoRaw, '/');
                    }
                }

                $displayTrancheLibelle = 'Scolarité';
                $displayTrancheDate = '-';

                if ($echeanceOverdue && !empty($echeanceOverdue['libelle_tranche'])) {
                    $displayTrancheLibelle = $echeanceOverdue['libelle_tranche'];
                    $displayTrancheDate = !empty($echeanceOverdue['date_limite']) ? date('d/m/Y', strtotime($echeanceOverdue['date_limite'])) : '-';
                } elseif (!empty($studentTranches)) {
                    $firstT = reset($studentTranches);
                    if (!empty($firstT['libelle_tranche'])) {
                        $displayTrancheLibelle = $firstT['libelle_tranche'];
                    }
                    if (!empty($firstT['date_limite'])) {
                        $displayTrancheDate = date('d/m/Y', strtotime($firstT['date_limite']));
                    }
                }

                $results[] = [
                    'id_inscription' => (int)$ins['id_inscription'],
                    'code_inscription' => $codeIns,
                    'code_etudiant' => $ins['code_etudiant'],
                    'matricule' => $ins['matricule_etudiant'] ?? 'N/A',
                    'nom' => strtoupper($ins['nom_etudiant'] ?? ''),
                    'prenom' => ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
                    'nom_complet' => strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
                    'telephone_etudiant' => $ins['telephone_etudiant'] ?? 'N/A',
                    'initiales' => !empty($initiales) ? $initiales : 'E',
                    'photo' => $photoUrl,
                    'classe' => $ins['libelle_classe'] ?? 'N/A',
                    'niveau' => !empty($ins['slug_niveau']) ? $ins['slug_niveau'] : ($ins['libelle_niveau'] ?? 'N/A'),
                    'filiere' => !empty($ins['slug_filiere']) ? $ins['slug_filiere'] : ($ins['libelle_filiere'] ?? 'N/A'),
                    'slug_filiere' => $ins['slug_filiere'] ?? '',
                    'slug_niveau' => $ins['slug_niveau'] ?? '',
                    'regime' => $isAffecte ? 'Affecté' : 'Privé',
                    'nom_parent' => $nomParent,
                    'telephone_parent' => $telParent,
                    'scolarite_due' => $scolariteDue,
                    'frais_annexes_dus' => $fraisAnnexesDus,
                    'total_attendu' => $totalAttendu,
                    'total_paye' => $totalEncaisse,
                    'solde_restant' => $soldeRestant,
                    'montant_echu' => $montantEchu,
                    'echeance_libelle' => $displayTrancheLibelle,
                    'echeance_date' => $displayTrancheDate,
                    'retard_jours' => $retardJoursMax,
                    'severite_code' => $severiteCode,
                    'severite_libelle' => $severiteLibelle,
                    'badge_class' => $badgeClass
                ];
            }

            $moyenneJoursRetard = ($kpiTotalEtudiantsRetard > 0) ? round($totalJoursAccumules / $kpiTotalEtudiantsRetard) : 0;

            // Nombre de relances effectuées le mois courant
            $stmtCountRelances = $db->prepare("SELECT COUNT(*) as cnt FROM relances_impayes WHERE MONTH(created_at_relance) = MONTH(CURRENT_DATE()) AND YEAR(created_at_relance) = YEAR(CURRENT_DATE())");
            $stmtCountRelances->execute();
            $relancesMois = (int)($stmtCountRelances->fetchColumn() ?: 0);

            return [
                'list' => $results,
                'kpis' => [
                    'total_impayes_echus' => $kpiTotalImpayesEchus,
                    'total_etudiants_retard' => $kpiTotalEtudiantsRetard,
                    'total_affectes' => $kpiTotalAffectes,
                    'total_non_affectes' => $kpiTotalNonAffectes,
                    'retard_moyen_jours' => $moyenneJoursRetard,
                    'total_relances_mois' => $relancesMois
                ]
            ];
        } catch (Exception $e) {
            error_log("Error in getOverdueStudentsDetailed: " . $e->getMessage());
            return [
                'list' => [],
                'kpis' => [
                    'total_impayes_echus' => 0,
                    'total_etudiants_retard' => 0,
                    'total_affectes' => 0,
                    'total_non_affectes' => 0,
                    'retard_moyen_jours' => 0,
                    'total_relances_mois' => 0
                ]
            ];
        }
    }
}

