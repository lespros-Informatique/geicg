<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Validator.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../models/paiements/ModelPaiement.php';
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../controllers/paiements/PaiementController.php';

$_SESSION[USERS_AUTH] = [
    'id_user' => 1,
    'code_user' => 'U001',
    'role' => 'ROLE_SUPERADMIN',
    'role_code' => 'ROLE_SUPERADMIN',
    'permissions' => ['VIEW_PAIEMENTS', 'RECORD_PAIEMENTS'],
    'etablissement_code' => 'ETA-052CG'
];

$_GET['annee_code'] = 'ANN-BYZMK6PA';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

ob_start();
$ctrl = new PaiementController();
$ctrl->apiComptabiliteEtudiants();
$out = ob_get_clean();

$data = json_decode($out, true);
echo "FIRST STUDENT IN TABLE:\n";
print_r($data['data'][0] ?? []);
