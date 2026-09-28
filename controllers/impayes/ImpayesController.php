<?php

class ImpayesController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelImpayes();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_IMPAYES', 'MANAGE_IMPAYES', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS']);

        $annees = $this->getAccessibleAnnees();
        $niveaux = (new ModelNiveau())->getAll();
        $classes = (new ModelClasse())->getAll();

        if (isset($_GET['annee_code']) && !empty($_GET['annee_code'])) {
            $selectedAnneeCode = trim($_GET['annee_code']);
            foreach ($annees as $a) {
                if ($a['code_annee'] === $selectedAnneeCode) {
                    $_SESSION['annee_active_code'] = $a['code_annee'];
                    $_SESSION['annee_active_libelle'] = $a['libelle_annee'];
                    break;
                }
            }
        } else {
            $selectedAnneeCode = $_SESSION['annee_active_code'] ?? null;
        }

        $canSendRelance = $this->hasPermission(['SEND_RELANCES', 'MANAGE_IMPAYES']);
        $canRecordPayment = $this->hasPermission(['RECORD_PAIEMENTS', 'MANAGE_PAIEMENTS', 'MANAGE_PAYMENTS']);

        $this->loadView('../views/impayes/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode,
            'canSendRelance' => $canSendRelance,
            'canRecordPayment' => $canRecordPayment
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_IMPAYES', 'MANAGE_IMPAYES', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS']);

        $anneeCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $severite = $_GET['severite'] ?? 'ALL';

        $result = $this->model->getOverdueStudentsDetailed($anneeCode, $niveauCode, $classeCode, $severite);
        $rawList = $result['list'] ?? [];
        $data = [];

        foreach ($rawList as $item) {
            $id = $item['id_inscription'];
            $idCrypte = $this->validator->crypter($id);
            $codeCrypte = $this->validator->crypter($item['code_inscription']);

            $data[] = array_merge($item, [
                'encrypted_id' => $idCrypte,
                'encrypted_code' => $codeCrypte
            ]);
        }

        $this->json([
            'status' => 1,
            'success' => true,
            'data' => $data,
            'kpis' => $result['kpis'] ?? []
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['SEND_RELANCES', 'MANAGE_IMPAYES']);

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['code_relance'])) {
            $data['code_relance'] = $this->validator->generateCode('relances_impayes', 'code_relance', 'REL-', 8);
        }
        $data['statut_relance'] = 'envoye';
        $data['created_at_relance'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['annee_code'] = $anneeCode;
        $data['etablissement_code'] = $etabCode;

        $cols = $this->model->getCon()->query("DESCRIBE relances_impayes")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success('Relance d\'impayé enregistrée et journalisée avec succès!');
        } else {
            $this->error('Erreur lors de l\'enregistrement de la relance');
        }
    }

    public function addGrouped()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->requirePermission(['SEND_RELANCES', 'MANAGE_IMPAYES']);

        $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
        $anneeCode = $this->getActiveAnneeCode();
        $etabCode = $this->getActiveEtablissementCode();

        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true);
        if (!is_array($items) || empty($items)) {
            $this->error('Aucun étudiant sélectionné pour la relance.');
            return;
        }

        $niveauRelance = $_POST['niveau_relance'] ?? 'rappel_amiable';
        $canalRelance = $_POST['canal_relance'] ?? 'sms';
        $messageTemplate = $_POST['message_relance'] ?? '';

        $cols = $this->model->getCon()->query("DESCRIBE relances_impayes")->fetchAll(PDO::FETCH_COLUMN);
        $colsFlip = array_flip($cols);

        $savedCount = 0;
        foreach ($items as $item) {
            $codeRelance = $this->validator->generateCode('relances_impayes', 'code_relance', 'REL-', 8);
            
            $msg = $messageTemplate;
            $msg = str_replace('{NOM_ETUDIANT}', $item['student_name'] ?? '', $msg);
            $msg = str_replace('{MONTANT_ECHU}', number_format((float)($item['montant_echu'] ?? 0), 0, ',', ' ') . ' FCFA', $msg);
            $msg = str_replace('{CLASSE}', $item['classe'] ?? '', $msg);

            $data = [
                'code_relance' => $codeRelance,
                'etudiant_code' => $item['etudiant_code'] ?? '',
                'inscription_code' => $item['inscription_code'] ?? '',
                'niveau_relance' => $niveauRelance,
                'canal_relance' => $canalRelance,
                'montant_impaye' => (float)($item['montant_echu'] ?? 0),
                'telephone_destinataire' => $item['phone'] ?? '',
                'message_relance' => $msg,
                'statut_relance' => 'envoye',
                'user_code' => $userCode,
                'annee_code' => $anneeCode,
                'etablissement_code' => $etabCode,
                'created_at_relance' => date('Y-m-d H:i:s')
            ];

            $filteredData = array_intersect_key($data, $colsFlip);
            if ($this->model->create($filteredData)) {
                $savedCount++;
            }
        }

        if ($savedCount > 0) {
            $this->success("Relance groupée transmise et journalisée avec succès pour {$savedCount} destinataire(s)!");
        } else {
            $this->error("Erreur lors de l'enregistrement de la relance groupée");
        }
    }

    public function imprimerListeImpayes()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_IMPAYES', 'MANAGE_IMPAYES', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        require_once __DIR__ . '/../../core/PdfService.php';

        $anneeCode = $_GET['annee_code'] ?? $_SESSION['annee_active_code'] ?? null;
        $niveauCode = $_GET['niveau_code'] ?? 'ALL';
        $classeCode = $_GET['classe_code'] ?? 'ALL';
        $severite = $_GET['severite'] ?? 'ALL';

        $result = $this->model->getOverdueStudentsDetailed($anneeCode, $niveauCode, $classeCode, $severite);

        $anneeLibelle = 'Toutes';
        if (!empty($anneeCode)) {
            $stmt = $this->model->getCon()->prepare("SELECT libelle_annee FROM annees WHERE code_annee = ?");
            $stmt->execute([$anneeCode]);
            $anneeLibelle = $stmt->fetchColumn() ?: $anneeCode;
        }

        $niveauLibelle = 'Tous';
        if (!empty($niveauCode) && $niveauCode !== 'ALL') {
            $stmt = $this->model->getCon()->prepare("SELECT libelle_niveau FROM niveaux WHERE code_niveau = ?");
            $stmt->execute([$niveauCode]);
            $niveauLibelle = $stmt->fetchColumn() ?: $niveauCode;
        }

        $classeLibelle = 'Toutes';
        if (!empty($classeCode) && $classeCode !== 'ALL') {
            $stmt = $this->model->getCon()->prepare("SELECT libelle_classe FROM classes WHERE code_classe = ?");
            $stmt->execute([$classeCode]);
            $classeLibelle = $stmt->fetchColumn() ?: $classeCode;
        }

        $severiteLibelle = 'Tous les retards';
        if ($severite === 'leger') $severiteLibelle = 'Retard Léger (1-15j)';
        elseif ($severite === 'modere') $severiteLibelle = 'Retard Modéré (16-30j)';
        elseif ($severite === 'critique') $severiteLibelle = 'Retard Critique (>30j)';

        $dataView = [
            'list' => $result['list'] ?? [],
            'kpis' => $result['kpis'] ?? [],
            'annee_libelle' => $anneeLibelle,
            'niveau_libelle' => $niveauLibelle,
            'classe_libelle' => $classeLibelle,
            'severite_libelle' => $severiteLibelle
        ];

        $html = PdfService::renderTemplate('liste_impayes.php', $dataView);
        $filename = 'Liste_Impayes_' . date('Ymd_His') . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'Liste des Impayés & Retards de Versements'
        ]);
    }

    public function imprimerRappelPdf($param)
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_IMPAYES', 'MANAGE_IMPAYES', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        require_once __DIR__ . '/../../core/PdfService.php';

        try {
            $id = $this->validator->decrypter($param);
        } catch (Exception $e) {
            $id = $param;
        }

        $db = $this->model->getCon();
        $stmt = $db->prepare("
            SELECT 
                i.id_inscription,
                i.code_inscription,
                i.montant_scolarite_inscription,
                i.affectation_etat,
                i.annee_code,
                e.code_etudiant,
                e.matricule_etudiant,
                e.nom_etudiant,
                e.prenom_etudiant,
                e.telephone_etudiant,
                e.photo_etudiant,
                c.libelle_classe,
                c.filiere_code,
                c.niveau_code,
                f.libelle_filiere,
                f.type_filiere,
                n.libelle_niveau,
                a.libelle_annee,
                p.nom_pere, p.telephone_pere,
                p.nom_mere, p.telephone_mere,
                p.nom_tuteur, p.telephone_tuteur
            FROM inscriptions i
            JOIN etudiants e ON i.etudiant_code = e.code_etudiant
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            LEFT JOIN filieres f ON c.filiere_code = f.code_filiere
            LEFT JOIN niveaux n ON c.niveau_code = n.code_niveau
            LEFT JOIN annees a ON a.code_annee = i.annee_code
            LEFT JOIN parents p ON p.etudiant_code = e.code_etudiant
            WHERE i.id_inscription = ? OR i.code_inscription = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $id]);
        $ins = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ins) {
            $this->error('Inscription introuvable pour ce rappel.');
            return;
        }

        // Paiements
        $stmtPay = $db->prepare("SELECT SUM(montant_paiement) as total_paye FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
        $stmtPay->execute([$ins['code_inscription']]);
        $totalPaye = (float)($stmtPay->fetchColumn() ?: 0);

        require_once __DIR__ . '/../../models/frais_annexes/ModelFraisAnnexe.php';
        $modelFA = new ModelFraisAnnexe();
        $typeFiliere = $ins['type_filiere'] ?? 'TERTIAIRE';
        $fraisAnnexesDus = (float)$modelFA->getMontantByTypeFiliere($typeFiliere, $ins['annee_code'], $ins['niveau_code'], 'inscription');

        // Scolarité due
        $stmtSco = $db->prepare("SELECT montant_scolarite FROM scolarites WHERE filiere_code = ? AND niveau_code = ? AND (annee_code = ? OR annee_code IS NULL OR annee_code = '') AND statut_scolarite = 'actif' LIMIT 1");
        $stmtSco->execute([$ins['filiere_code'], $ins['niveau_code'], $ins['annee_code']]);
        $scolariteDue = (float)($stmtSco->fetchColumn() ?: $ins['montant_scolarite_inscription']);

        $totalAttendu = $scolariteDue + $fraisAnnexesDus;
        $restePayer = max(0, $totalAttendu - $totalPaye);
        $isAffecte = ($ins['affectation_etat'] === 'affecte' || $ins['affectation_etat'] === 'oui');

        $nomParent = '-';
        $contactParent = $ins['telephone_etudiant'] ?? '-';
        if (!empty($ins['nom_tuteur'])) {
            $nomParent = trim($ins['nom_tuteur']) . ' (Tuteur)';
            if (!empty($ins['telephone_tuteur'])) $contactParent = $ins['telephone_tuteur'];
        } elseif (!empty($ins['nom_pere'])) {
            $nomParent = trim($ins['nom_pere']) . ' (Père)';
            if (!empty($ins['telephone_pere'])) $contactParent = $ins['telephone_pere'];
        } elseif (!empty($ins['nom_mere'])) {
            $nomParent = trim($ins['nom_mere']) . ' (Mère)';
            if (!empty($ins['telephone_mere'])) $contactParent = $ins['telephone_mere'];
        }

        $userNom = $_SESSION[USERS_AUTH]['nom_user'] ?? 'La Caissière';
        $userPrenom = $_SESSION[USERS_AUTH]['prenom_user'] ?? '';
        $caissierNom = trim($userNom . ' ' . $userPrenom);

        $toBase64 = function($paths) {
            foreach ($paths as $p) {
                if (empty($p)) continue;
                $pClean = ltrim($p, '/');
                $candidates = [
                    $pClean,
                    __DIR__ . '/../../' . $pClean,
                    __DIR__ . '/../../public/' . $pClean
                ];
                foreach ($candidates as $cand) {
                    if (file_exists($cand) && is_file($cand)) {
                        $ext = pathinfo($cand, PATHINFO_EXTENSION);
                        $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/jpeg');
                        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($cand));
                    }
                }
            }
            return '';
        };

        $logoSrc = $toBase64([
            'public/assets/images/logo/logo_eicg.jpg',
            'assets/images/logo/logo_eicg.jpg',
            'public/uploads/logos/logo_1787358264.jpg'
        ]);

        $photoSrc = $toBase64([
            $ins['photo_etudiant'] ?? '',
            $ins['photo_inscription'] ?? '',
            'public/assets/images/placeholders/etudiant.png',
            'assets/images/placeholders/etudiant.png'
        ]);

        $dataView = [
            'annee_libelle' => $ins['libelle_annee'] ?? date('Y') . '-' . (date('Y') + 1),
            'code_rappel' => 'RAP-' . strtoupper(substr(md5($ins['code_inscription']), 0, 8)),
            'code_barre_val' => 'RAP-' . strtoupper(substr(md5($ins['code_inscription']), 0, 8)),
            'code_inscription' => $ins['code_inscription'],
            'statut_affectation' => $isAffecte ? 'AFFECTE' : 'NON AFFECTE',
            'matricule_etudiant' => $ins['matricule_etudiant'] ?? 'N/A',
            'filiere_niveau' => ($ins['libelle_filiere'] ?? '') . ' - ' . ($ins['libelle_niveau'] ?? ''),
            'nom_prenom_etudiant' => strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
            'contact_etudiant' => $contactParent,
            'photo_etudiant' => $ins['photo_etudiant'] ?? $ins['photo_inscription'] ?? '',
            'photo_src' => $photoSrc,
            'logo_src' => $logoSrc,
            'total_scolarite' => $totalAttendu,
            'montant_paye' => $totalPaye,
            'reste_payer' => $restePayer,
            'montant_exigible_du' => $restePayer,
            'montant_exigible_lettres' => PdfService::numberToWordsFrench($restePayer),
            'delai_rigueur' => date('d/m/Y', strtotime('+7 days')),
            'caissier_nom' => $caissierNom,
            'ref_caiss' => 'REF-' . date('YmdHis'),
            'date_impression' => date('d/m/Y H:i:s')
        ];

        $html = PdfService::renderTemplate('rappel_scolarite.php', $dataView);
        $filename = 'Rappel_Scolarite_' . ($ins['matricule_etudiant'] ?? $ins['code_inscription']) . '.pdf';
        PdfService::generate($html, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Rappel de Scolarité - ' . $dataView['nom_prenom_etudiant']
        ]);
    }

    public function imprimerRappelsMasse()
    {
        $this->requireAuth();
        $this->requirePermission(['VIEW_IMPAYES', 'MANAGE_IMPAYES', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS']);
        require_once __DIR__ . '/../../core/PdfService.php';
        require_once __DIR__ . '/../../models/frais_annexes/ModelFraisAnnexe.php';

        $rawIds = $_GET['ids'] ?? '';
        if (empty($rawIds)) {
            $this->error('Aucun étudiant sélectionné pour l\'impression en masse.');
            return;
        }

        $ids = array_filter(array_map('trim', explode(',', $rawIds)));
        if (empty($ids)) {
            $this->error('Aucun identifiant valide fourni.');
            return;
        }

        $db = $this->model->getCon();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $db->prepare("
            SELECT 
                i.id_inscription,
                i.code_inscription,
                i.montant_scolarite_inscription,
                i.affectation_etat,
                i.annee_code,
                e.code_etudiant,
                e.matricule_etudiant,
                e.nom_etudiant,
                e.prenom_etudiant,
                e.telephone_etudiant,
                e.photo_etudiant,
                c.libelle_classe,
                c.filiere_code,
                c.niveau_code,
                f.libelle_filiere,
                f.type_filiere,
                n.libelle_niveau,
                a.libelle_annee,
                p.nom_pere, p.telephone_pere,
                p.nom_mere, p.telephone_mere,
                p.nom_tuteur, p.telephone_tuteur
            FROM inscriptions i
            JOIN etudiants e ON i.etudiant_code = e.code_etudiant
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            LEFT JOIN filieres f ON c.filiere_code = f.code_filiere
            LEFT JOIN niveaux n ON c.niveau_code = n.code_niveau
            LEFT JOIN annees a ON a.code_annee = i.annee_code
            LEFT JOIN parents p ON p.etudiant_code = e.code_etudiant
            WHERE i.id_inscription IN ({$placeholders}) OR i.code_inscription IN ({$placeholders})
            ORDER BY e.nom_etudiant ASC
        ");

        $execParams = array_merge($ids, $ids);
        $stmt->execute($execParams);
        $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($inscriptions)) {
            $this->error('Aucune inscription trouvée pour ces identifiants.');
            return;
        }

        $modelFA = new ModelFraisAnnexe();
        $userNom = $_SESSION[USERS_AUTH]['nom_user'] ?? 'La Caissière';
        $userPrenom = $_SESSION[USERS_AUTH]['prenom_user'] ?? '';
        $caissierNom = trim($userNom . ' ' . $userPrenom);

        $htmlPages = [];

        foreach ($inscriptions as $ins) {
            $stmtPay = $db->prepare("SELECT SUM(montant_paiement) as total_paye FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
            $stmtPay->execute([$ins['code_inscription']]);
            $totalPaye = (float)($stmtPay->fetchColumn() ?: 0);

            $typeFiliere = $ins['type_filiere'] ?? 'TERTIAIRE';
            $fraisAnnexesDus = (float)$modelFA->getMontantByTypeFiliere($typeFiliere, $ins['annee_code'], $ins['niveau_code'], 'inscription');

            $stmtSco = $db->prepare("SELECT montant_scolarite FROM scolarites WHERE filiere_code = ? AND niveau_code = ? AND (annee_code = ? OR annee_code IS NULL OR annee_code = '') AND statut_scolarite = 'actif' LIMIT 1");
            $stmtSco->execute([$ins['filiere_code'], $ins['niveau_code'], $ins['annee_code']]);
            $scolariteDue = (float)($stmtSco->fetchColumn() ?: $ins['montant_scolarite_inscription']);

            $totalAttendu = $scolariteDue + $fraisAnnexesDus;
            $restePayer = max(0, $totalAttendu - $totalPaye);
            $isAffecte = ($ins['affectation_etat'] === 'affecte' || $ins['affectation_etat'] === 'oui');

            $nomParent = '-';
            $contactParent = $ins['telephone_etudiant'] ?? '-';
            if (!empty($ins['nom_tuteur'])) {
                $nomParent = trim($ins['nom_tuteur']) . ' (Tuteur)';
                if (!empty($ins['telephone_tuteur'])) $contactParent = $ins['telephone_tuteur'];
            } elseif (!empty($ins['nom_pere'])) {
                $nomParent = trim($ins['nom_pere']) . ' (Père)';
                if (!empty($ins['telephone_pere'])) $contactParent = $ins['telephone_pere'];
            } elseif (!empty($ins['nom_mere'])) {
                $nomParent = trim($ins['nom_mere']) . ' (Mère)';
                if (!empty($ins['telephone_mere'])) $contactParent = $ins['telephone_mere'];
            }

            $photoSrc = $toBase64([
                $ins['photo_etudiant'] ?? '',
                $ins['photo_inscription'] ?? '',
                'public/assets/images/placeholders/etudiant.png',
                'assets/images/placeholders/etudiant.png'
            ]);

            $dataView = [
                'annee_libelle' => $ins['libelle_annee'] ?? date('Y') . '-' . (date('Y') + 1),
                'code_rappel' => 'RAP-' . strtoupper(substr(md5($ins['code_inscription']), 0, 8)),
            'code_barre_val' => 'RAP-' . strtoupper(substr(md5($ins['code_inscription']), 0, 8)),
                'code_inscription' => $ins['code_inscription'],
                'statut_affectation' => $isAffecte ? 'AFFECTE' : 'NON AFFECTE',
                'matricule_etudiant' => $ins['matricule_etudiant'] ?? 'N/A',
                'filiere_niveau' => ($ins['libelle_filiere'] ?? '') . ' - ' . ($ins['libelle_niveau'] ?? ''),
                'nom_prenom_etudiant' => strtoupper($ins['nom_etudiant'] ?? '') . ' ' . ucwords(strtolower($ins['prenom_etudiant'] ?? '')),
                'contact_etudiant' => $contactParent,
                'photo_etudiant' => $ins['photo_etudiant'] ?? $ins['photo_inscription'] ?? '',
                'photo_src' => $photoSrc,
                'logo_src' => $logoSrc,
                'total_scolarite' => $totalAttendu,
                'montant_paye' => $totalPaye,
                'reste_payer' => $restePayer,
                'montant_exigible_du' => $restePayer,
                'montant_exigible_lettres' => PdfService::numberToWordsFrench($restePayer),
                'delai_rigueur' => date('d/m/Y', strtotime('+7 days')),
                'caissier_nom' => $caissierNom,
                'ref_caiss' => 'REF-' . date('YmdHis'),
                'date_impression' => date('d/m/Y H:i:s')
            ];

            $htmlPages[] = PdfService::renderTemplate('rappel_scolarite.php', $dataView);
        }

        $fullHtml = implode('<div style="page-break-after: always;"></div>', $htmlPages);
        $filename = 'Rappels_Scolarite_Masse_' . date('Ymd_His') . '.pdf';
        PdfService::generate($fullHtml, $filename, [
            'orientation' => 'P',
            'format' => 'A4',
            'title' => 'Rappels de Scolarité en Masse (' . count($inscriptions) . ')'
        ]);
    }
}

