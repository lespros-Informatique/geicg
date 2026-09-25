<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Validator.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/inscriptions/ModelInscription.php';
require_once __DIR__ . '/../controllers/inscriptions/InscriptionController.php';

$_SESSION[USERS_AUTH] = [
    'code_user' => 'USR-00000001',
    'id_user' => 1,
    'roles' => ['ROLE_SUPERADMIN']
];

$controller = new InscriptionController();
$validator = new Validator();

$db = (new Database())->getCon();
$etu = $db->query("SELECT id_etudiant, code_etudiant FROM etudiants LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if ($etu) {
    $encrypted = $validator->crypter($etu['id_etudiant']);
    try {
        $_GET['pdf'] = '1';
        ob_start();
        $controller->imprimerFiche($encrypted);
        $pdfContent = ob_get_clean();
        
        $pdfPath = __DIR__ . '/fiche_test.pdf';
        file_put_contents($pdfPath, $pdfContent);
        echo "SUCCESS! PDF written to $pdfPath (" . strlen($pdfContent) . " bytes)\n";
    } catch (Throwable $t) {
        if (ob_get_level()) ob_end_clean();
        echo "ERROR: " . $t->getMessage() . "\n" . $t->getTraceAsString() . "\n";
    }
} else {
    echo "No student found in DB\n";
}
