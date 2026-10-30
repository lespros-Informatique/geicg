<?php

class BulletinController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelBulletin();
    }

    public function list()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        $annees = $this->getAccessibleAnnees();
        $niveaux = (new ModelNiveau())->getAll();
        $classes = (new ModelClasse())->getAll();
        $selectedAnneeCode = Session::get('annee_active_code');

        $this->loadView('../views/bulletin/list.php', [
            'annees' => $annees,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'selectedAnneeCode' => $selectedAnneeCode
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        $anneeCode = $_GET['annee_code'] ?? Session::get('annee_active_code') ?? null;
        $niveauCode = $_GET['niveau_code'] ?? null;
        $classeCode = $_GET['classe_code'] ?? null;

        $items = $this->model->getInscriptionsWithNotes($anneeCode, $niveauCode, $classeCode);

        $data = [];
        foreach ($items as $i) {
            $id = $i['id_inscription'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function calculateBulletinData(int $id, ?string $selectedSemestreCode = null): ?array
    {
        $calc = $this->model->calculateBulletinData($id, $selectedSemestreCode);
        if (!$calc) {
            return null;
        }
        $calc['encryptedId'] = $this->validator->crypter($id);
        return $calc;
    }


    public function details($details)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        try {
            $id = $this->validator->decrypter($details);
            $selectedSemestreCode = $_GET['semestre'] ?? '';
            $calc = $this->calculateBulletinData($id, $selectedSemestreCode);
            if (!$calc) {
                $this->renderNotFound("Le bulletin demandé est introuvable.");
                return;
            }
            $this->loadView('../views/bulletin/details.php', $calc);
        } catch (Exception $e) {
            error_log("BulletinController::details error: " . $e->getMessage());
            $this->renderNotFound("Le bulletin demandé est introuvable.");
            return;
        }
    }

    public function imprimerBulletinPdf($details)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::GENERATE_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        require_once __DIR__ . '/../../core/PdfService.php';

        try {
            $id = $this->validator->decrypter($details);
            $selectedSemestreCode = $_GET['semestre'] ?? '';
            $calc = $this->calculateBulletinData($id, $selectedSemestreCode);
            if (!$calc) {
                $this->renderNotFound("Le bulletin demandé est introuvable.");
                return;
            }

            $item = $calc['item'];
            $semestreLibelle = $calc['selectedSemestreCode'];
            foreach ($calc['semestres'] as $s) {
                if ($s['code_semestre'] === $calc['selectedSemestreCode']) {
                    $semestreLibelle = $s['libelle_semestre'];
                    break;
                }
            }

            $nomComplet = trim(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? ''));

            $dataView = [
                'annee_libelle' => $item['libelle_annee'] ?? '-',
                'photo_etudiant' => $item['photo_etudiant'] ?? '',
                'nom_prenom_etudiant' => $nomComplet ?: '-',
                'matricule_etudiant' => $item['matricule_etudiant'] ?? '-',
                'date_naissance' => !empty($item['date_naissance_etudiant']) ? date('d/m/Y', strtotime($item['date_naissance_etudiant'])) : '-',
                'lieu_naissance' => $item['lieu_naissance_etudiant'] ?? '-',
                'sexe_etudiant' => $item['sexe_etudiant'] ?? '-',
                'filiere_libelle' => $item['libelle_filiere'] ?? '-',
                'niveau_libelle' => $item['libelle_niveau'] ?? '-',
                'classe_libelle' => $item['libelle_classe'] ?? '-',
                'effectif_classe' => $calc['totalElevesClasse'] ?? '-',
                'statut_affectation' => (!empty($item['affectation_etat']) && $item['affectation_etat'] === 'oui') ? 'AFFECTÉ(E)' : 'NON AFFECTÉ(E)',
                'intitule_periode' => strtoupper($semestreLibelle),
                'matieres_notes' => $calc['matieresNotes'],
                'totalPoints' => $calc['totalPoints'],
                'totalCoefficients' => $calc['totalCoefficients'],
                'moyenneGenerale' => $calc['moyenneGenerale'],
                'mention' => $calc['mention'],
                'decision' => $calc['decision'],
                'rang' => $calc['rang'],
                'totalElevesClasse' => $calc['totalElevesClasse'],
                'moyenneMinClasse' => $calc['moyenneMinClasse'],
                'moyenneMaxClasse' => $calc['moyenneMaxClasse'],
                'moyenneMoyClasse' => $calc['moyenneMoyClasse'],
                'heures_absences' => $calc['heuresAbsences'] ?? '0 H'
            ];

            $html = PdfService::renderTemplate('bulletin_notes.php', $dataView);
            if (isset($_GET['html'])) {
                echo $html;
                return;
            }

            $filename = 'Bulletin_' . ($item['matricule_etudiant'] ?? 'Etudiant') . '_' . date('Ymd_His') . '.pdf';
            PdfService::generate($html, $filename, [
                'orientation' => 'P',
                'format' => 'A4',
                'title' => 'Bulletin de Notes - ' . $nomComplet,
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_top' => 6,
                'margin_bottom' => 6
            ]);
        } catch (Exception $e) {
            error_log("BulletinController::imprimerBulletinPdf error: " . $e->getMessage());
            $this->renderNotFound("Erreur lors de la génération du bulletin PDF.");
        }
    }

    public function formulaire()
    {
        $this->requireAuth();
        header('Location: ' . RACINE . 'bulletin/list');
        exit();
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->details($details);
    }

    private function calculatePvClasseData(string $selectedClasseCode, string $selectedSemestreCode): array
    {
        return $this->model->calculatePvClasseData($selectedClasseCode, $selectedSemestreCode, $this->getActiveAnneeCode());
    }

    public function pvClasse($param = null)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::VIEW_NOTES]);

        $classes = (new ModelClasse())->getAll();
        $semestres = $this->model->getActiveSemestres();

        $selectedClasseCode = $_GET['classe_code'] ?? '';
        if (empty($selectedClasseCode) && !empty($param)) {
            try {
                $selectedClasseCode = $this->validator->decrypter($param);
            } catch (Exception $e) {
                $selectedClasseCode = $param;
            }
        }
        if (empty($selectedClasseCode) && !empty($classes)) {
            $selectedClasseCode = $classes[0]['code_classe'] ?? '';
        }

        $selectedSemestreCode = $_GET['semestre_code'] ?? ($semestres[0]['code_semestre'] ?? '');

        $pvData = $this->calculatePvClasseData($selectedClasseCode, $selectedSemestreCode);
        $encryptedClasseCode = !empty($selectedClasseCode) ? $this->validator->crypter($selectedClasseCode) : '';

        $this->loadView('../views/bulletin/pv_classe.php', array_merge($pvData, [
            'classes' => $classes,
            'semestres' => $semestres,
            'selectedClasseCode' => $selectedClasseCode,
            'selectedSemestreCode' => $selectedSemestreCode,
            'encryptedClasseCode' => $encryptedClasseCode
        ]));
    }

    public function imprimerPvPdf($param = null)
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::VIEW_BULLETINS, PERMISSIONS::GENERATE_BULLETINS, PERMISSIONS::VIEW_NOTES]);
        require_once __DIR__ . '/../../core/PdfService.php';

        $selectedClasseCode = $_GET['classe_code'] ?? '';
        if (empty($selectedClasseCode) && !empty($param)) {
            try {
                $selectedClasseCode = $this->validator->decrypter($param);
            } catch (Exception $e) {
                $selectedClasseCode = $param;
            }
        }

        $semestres = $this->model->getActiveSemestres();
        $selectedSemestreCode = $_GET['semestre'] ?? ($_GET['semestre_code'] ?? ($semestres[0]['code_semestre'] ?? ''));

        $selectedSemestreLibelle = $selectedSemestreCode;
        foreach ($semestres as $s) {
            if ($s['code_semestre'] === $selectedSemestreCode) {
                $selectedSemestreLibelle = $s['libelle_semestre'];
                break;
            }
        }

        $pvData = $this->calculatePvClasseData($selectedClasseCode, $selectedSemestreCode);
        if (empty($pvData['classeInfo'])) {
            $this->renderNotFound("Classe introuvable pour la génération du PV.");
            return;
        }

        $dataView = array_merge($pvData, [
            'selectedClasseCode' => $selectedClasseCode,
            'selectedSemestreCode' => $selectedSemestreCode,
            'selectedSemestreLibelle' => $selectedSemestreLibelle
        ]);

        $html = PdfService::renderTemplate('pv_classe_pdf.php', $dataView);
        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $className = preg_replace('/[^A-Za-z0-9_\-]/', '_', $pvData['classeInfo']['libelle_classe'] ?? 'Classe');
        $filename = 'PV_Deliberation_' . $className . '_' . date('Ymd_His') . '.pdf';

        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'PV de Délibération - ' . ($pvData['classeInfo']['libelle_classe'] ?? ''),
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8
        ]);
    }
}
