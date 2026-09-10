<?php
require_once __DIR__ . '/../core/PrincipalRoute.php';

// Prepare test POST data imitating the form submission
$_POST = [
    'csrf_token' => Validator::generateCsrfToken(),
    'annee_code' => 'ANN-BKFX6BV1',
    'type_composition' => 'COMPOSITION',
    'libelle_composition' => 'Test Composition Multi-Niveaux 2026',
    'coefficient' => '2.50',
    'matiere_code' => 'MAT-ALGO',
    'semestre_code' => 'SEM-1',
    'date_composition' => '2026-10-15',
    'heure_debut' => '09:00',
    'heure_fin' => '12:00',
    'salle_code' => 'SAL-A1',
    'statut_composition' => 'programme',
    'niveaux' => [
        [
            'niveau_code' => 'NIV-JJ4ZUKTY', // BTS 1ere Année
            'filiere_codes' => ['FIL-BP2COHHA', 'FIL-4PJHLXCQ'] // IDA, RIT
        ],
        [
            'niveau_code' => 'NIV-357F4VHQ', // BTS 2eme Annee
            'filiere_codes' => ['FIL-BP2COHHA'] // IDA
        ]
    ]
];

$_SESSION[USERS_AUTH]['code_user'] = 'ADM-TEST';
$_SESSION['annee_active_code'] = 'ANN-BKFX6BV1';
$_SESSION['etablissement_active_code'] = '5454544456';

$db = (new Database())->getCon();
$beforeCount = (int)$db->query("SELECT COUNT(*) FROM compositions")->fetchColumn();

// Instantiate controller and test class resolution
$allClasses = (new ModelClasse())->getAll();
$targetClasses = [];
foreach ($_POST['niveaux'] as $row) {
    $nivCode = $row['niveau_code'] ?? '';
    $filCodes = $row['filiere_codes'] ?? [];
    if (is_string($filCodes)) $filCodes = array_filter(explode(',', $filCodes));
    if (empty($nivCode)) continue;

    foreach ($allClasses as $c) {
        if (($c['niveau_code'] ?? '') === $nivCode) {
            if (empty($filCodes) || in_array($c['filiere_code'] ?? '', $filCodes)) {
                $targetClasses[] = $c['code_classe'];
            }
        }
    }
}
$targetClasses = array_unique(array_filter($targetClasses));

echo "Resolved Target Classes Count: " . count($targetClasses) . "\n";
print_r($targetClasses);
