<?php

require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../core/PrincipalRoute.php';

echo "=======================================================\n";
echo "SIMULATION DE CRÉATION D'UTILISATEUR & ENVOI DE MAIL\n";
echo "=======================================================\n\n";

$db = (new Database())->getCon();

$nom = 'KASANOGO';
$prenom = 'Trey';
$email = 'treykasanogo@gmail.com';
$telephone = '0700000000';
$rawPassword = 'Eicg' . rand(100, 999) . '!';
$hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

// 1. Vérification si l'utilisateur existe déjà
$stmtCheck = $db->prepare("SELECT id_user, code_user FROM users WHERE email_user = ? LIMIT 1");
$stmtCheck->execute([$email]);
$existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if ($existingUser) {
    echo "[1/3] Utilisateur existant décelé en base : Code={$existingUser['code_user']} (ID={$existingUser['id_user']}). Mise à jour du mot de passe...\n";
    $code_user = $existingUser['code_user'];
    $stmtUpdate = $db->prepare("UPDATE users SET password_user = ?, nom_user = ?, prenom_user = ?, telephone_user = ?, statut_user = 'actif' WHERE id_user = ?");
    $stmtUpdate->execute([$hashedPassword, $nom, $prenom, $telephone, $existingUser['id_user']]);
} else {
    echo "[1/3] Génération du nouveau compte utilisateur...\n";
    $validator = new Validator();
    $code_user = $validator->generateCode('users', 'code_user', 'USR-', 8);
    
    // Obtenir le premier etablissement_code
    $etabCode = $db->query("SELECT code_etablissement FROM etablissements LIMIT 1")->fetchColumn() ?: 'ETA-DEFAULT';

    $stmtInsert = $db->prepare("
        INSERT INTO users (code_user, nom_user, prenom_user, email_user, telephone_user, sexe_user, password_user, etablissement_code, statut_user, created_at_user)
        VALUES (?, ?, ?, ?, ?, 'M', ?, ?, 'actif', NOW())
    ");
    $stmtInsert->execute([$code_user, $nom, $prenom, $email, $telephone, $hashedPassword, $etabCode]);
    echo "      Compte inséré en BDD avec succès ! Code : {$code_user}\n";
}

// 2. Attribution d'un rôle d'accès
$roleCode = $db->query("SELECT code_role FROM roles WHERE statut_role = 'actif' ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 'ROLE_SUPERADMIN';
$stmtRole = $db->prepare("INSERT IGNORE INTO user_roles (user_code, role_code) VALUES (?, ?)");
$stmtRole->execute([$code_user, $roleCode]);
echo "[2/3] Rôle [{$roleCode}] attribué à l'utilisateur [{$code_user}].\n";

// 3. Trigger d'envoi automatique d'e-mail avec MailerService
echo "[3/3] Déclenchement de l'envoi d'e-mail d'activation & identifiants à {$email}...\n";

$mailRes = MailerService::sendTemplate(
    $email,
    'Vos Identifiants d\'Accès Officiels - GROUPE EICG',
    'welcome_credentials',
    [
        'userNom'      => $nom . ' ' . $prenom,
        'userEmail'    => $email,
        'userPassword' => $rawPassword,
        'userFonction' => 'Administrateur Système',
        'loginUrl'     => RACINE . 'user/connexion'
    ]
);

echo "\n=======================================================\n";
if ($mailRes['status']) {
    echo "RÉSULTAT DE LA SIMULATION : SUCCÈS TOTAL ✅\n";
    echo "Identifiant (Email) : {$email}\n";
    echo "Mot de passe généré : {$rawPassword}\n";
    echo "Message SMTP        : {$mailRes['message']}\n";
} else {
    echo "RÉSULTAT DE LA SIMULATION : ÉCHEC ❌\n";
    echo "Erreur d'envoi mail  : {$mailRes['message']}\n";
}
echo "=======================================================\n";
