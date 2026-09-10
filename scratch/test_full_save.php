<?php
require_once __DIR__ . '/../core/PrincipalRoute.php';

$db = (new Database())->getCon();

$_POST = [
    'csrf_token' => Validator::generateCsrfToken(),
    'annee_code' => 'ANN-BKFX6BV1',
    'semestre_code' => 'SEM-RT6EISWJ',
    'coefficient' => '3.00',
    'date_composition' => '2026-11-20',
    'libelle_composition' => 'Examen de Test Global 2026',
    'niveaux' => [
        [
            'niveau_code' => 'NIV-JJ4ZUKTY',
            'filiere_codes' => ['FIL-BP2COHHA']
        ]
    ]
];

$_SESSION[USERS_AUTH]['code_user'] = 'ADM-TEST';
$_SESSION['annee_active_code'] = 'ANN-BKFX6BV1';
$_SESSION['etablissement_active_code'] = '5454544456';

$controller = new CompositionController();
// Test add execution logic directly
$anneeCode = $_POST['annee_code'];
$userCode = $_SESSION[USERS_AUTH]['code_user'];
$etabCode = $_SESSION['etablissement_active_code'];

$data = $_POST;
unset($data['csrf_token']);

$semestres = (new ModelSemestre())->getAll();
$matieres = (new ModelMatiere())->getAll();
if (empty($data['type_composition'])) $data['type_composition'] = 'COMPOSITION';
if (empty($data['date_composition'])) $data['date_composition'] = date('Y-m-d');
if (empty($data['heure_debut'])) $data['heure_debut'] = '08:00';
if (empty($data['heure_fin'])) $data['heure_fin'] = '11:00';
if (empty($data['semestre_code'])) $data['semestre_code'] = !empty($semestres) ? $semestres[0]['code_semestre'] : 'SEM-1';
if (empty($data['matiere_code'])) $data['matiere_code'] = !empty($matieres) ? $matieres[0]['code_matiere'] : 'MAT-GEN';

$data['statut_composition'] = $data['statut_composition'] ?? 'programme';
$data['created_at_composition'] = date('Y-m-d H:i:s');

$modelComp = new ModelComposition();
$cols = $modelComp->getCon()->query("DESCRIBE compositions")->fetchAll(PDO::FETCH_COLUMN);

$targetClasses = ['CLA-CY7NH052']; // test class
foreach ($targetClasses as $cCode) {
    $singleData = $data;
    $singleData['classe_code'] = $cCode;
    $singleData['user_code'] = $userCode;
    $singleData['etablissement_code'] = $etabCode;
    $singleData['annee_code'] = $anneeCode;
    $singleData['code_composition'] = (new Validator())->generateCode('compositions', 'code_composition', 'CMP-', 8);
    
    $filteredData = array_intersect_key($singleData, array_flip($cols));
    $inserted = $modelComp->create($filteredData);
    echo "Insert Success: " . ($inserted ? "YES" : "NO") . "\n";
}

$lastRecord = $db->query("SELECT * FROM compositions ORDER BY id_composition DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo "\n=== LAST INSERTED RECORD ===\n";
print_r($lastRecord);
