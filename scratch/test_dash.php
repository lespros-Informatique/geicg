<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Validator.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../models/home/ModelHome.php';

unset($_SESSION['annee_active_code']);
unset($_SESSION['annee_active_libelle']);

$modelHome = new ModelHome();
$stats = $modelHome->getStats();

echo "=== FULL STATS RESULT ===\n";
print_r($stats);
