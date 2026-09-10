<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../models/compositions/ModelComposition.php';

$model = new ModelComposition();
$all = $model->getAll();
if (!empty($all)) {
    $comp = $all[0];
    echo "Composition: " . $comp['libelle_composition'] . " (code: " . $comp['code_composition'] . ")\n";
    $targets = $model->getTargetClasses($comp['code_composition']);
    echo "Target Classes Count: " . count($targets) . "\n";
    print_r($targets);
}
