<?php

require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../core/PrincipalRoute.php';

$to = 'treykasanogo@gmail.com';
$subject = 'Test d\'envoi E-mail Officiel - GROUPE EICG';

echo "Tentative d'envoi d'e-mail de test vers : {$to}...\n";

$result = MailerService::sendTemplate(
    $to,
    $subject,
    'welcome_credentials',
    [
        'userNom'      => 'Trey Kasanogo',
        'userEmail'    => $to,
        'userPassword' => 'TempPass' . rand(1000, 9999),
        'userFonction' => 'Administrateur Test',
        'loginUrl'     => RACINE . 'user/connexion'
    ]
);

echo "Statut d'envoi : " . ($result['status'] ? "SUCCESS ✅" : "ÉCHEC ❌") . "\n";
echo "Message retourné : " . $result['message'] . "\n";
