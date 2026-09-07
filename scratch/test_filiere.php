<?php
require_once __DIR__ . '/../core/PrincipalRoute.php';

$filieres = (new ModelFiliere())->getAll();
echo "Filieres count: " . count($filieres) . "\n";
foreach ($filieres as $f) {
    echo "- " . $f['code_filiere'] . ": " . $f['libelle_filiere'] . "\n";
}
