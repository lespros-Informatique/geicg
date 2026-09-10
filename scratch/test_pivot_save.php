<?php
require_once __DIR__ . '/../core/PrincipalRoute.php';

$db = (new Database())->getCon();

$_POST = [
    'csrf_token' => Validator::generateCsrfToken(),
    'annee_code' => 'ANN-BKFX6BV1',
    'semestre_code' => 'SEM-RT6EISWJ',
    'coefficient' => '3.50',
    'date_composition' => '2026-11-25',
    'libelle_composition' => 'Composition Finale 2026 Normalisée',
    'niveaux' => [
        [
            'niveau_code' => 'NIV-JJ4ZUKTY',
            'filiere_codes' => ['FIL-BP2COHHA', 'FIL-4PJHLXCQ']
        ],
        [
            'niveau_code' => 'NIV-357F4VHQ',
            'filiere_codes' => ['FIL-BP2COHHA']
        ]
    ]
];

$_SESSION[USERS_AUTH]['code_user'] = 'ADM-TEST';
$_SESSION['annee_active_code'] = 'ANN-BKFX6BV1';
$_SESSION['etablissement_active_code'] = '5454544456';

$controller = new CompositionController();
$modelComp = new ModelComposition();

$codeComp = 'CMP-TESTPIVOT';

$compData = [
    'code_composition' => $codeComp,
    'libelle_composition' => $_POST['libelle_composition'],
    'semestre_code' => $_POST['semestre_code'],
    'date_composition' => $_POST['date_composition'],
    'coefficient' => $_POST['coefficient'],
    'statut_composition' => 'programme',
    'annee_code' => $_POST['annee_code'],
    'etablissement_code' => $_SESSION['etablissement_active_code'],
    'user_code' => $_SESSION[USERS_AUTH]['code_user'],
    'created_at_composition' => date('Y-m-d H:i:s')
];

$cols = $db->query("DESCRIBE compositions")->fetchAll(PDO::FETCH_COLUMN);
$filtered = array_intersect_key($compData, array_flip($cols));
$inserted = $modelComp->create($filtered);

echo "Master Composition Created: " . ($inserted ? "YES" : "NO") . "\n";

// Test target items resolution
$allClasses = (new ModelClasse())->getAll();
$targetItems = [];
foreach ($_POST['niveaux'] as $row) {
    $nivCode = $row['niveau_code'] ?? '';
    $filCodes = $row['filiere_codes'] ?? [];
    if (empty($nivCode) || empty($filCodes)) continue;
    foreach ($allClasses as $c) {
        if (($c['niveau_code'] ?? '') === $nivCode) {
            if (in_array($c['filiere_code'] ?? '', $filCodes)) {
                $targetItems[] = [
                    'niveau_code' => $c['niveau_code'],
                    'filiere_code' => $c['filiere_code'],
                    'classe_code' => $c['code_classe']
                ];
            }
        }
    }
}

$pivotSaved = $modelComp->saveTargetClasses($codeComp, $targetItems);
echo "Pivot Target Classes Saved: " . ($pivotSaved ? "YES (" . count($targetItems) . " classes)" : "NO") . "\n";

$savedPivotRows = $modelComp->getTargetClasses($codeComp);
echo "\n=== SAVED PIVOT ROWS IN COMPOSITION_CLASSES ===\n";
print_r($savedPivotRows);
