<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getCon();

$missingPerms = [
    ["VIEW_DASHBOARD_EXECUTIVE", "Afficher le Tableau de Bord Exécutif & Synthèse", "DASHBOARD"],
    ["MANAGE_IMPAYES", "Gérer & Traiter les Relances des Impayés", "FINANCE"],
    ["VIEW_IMPAYES", "Consulter l'Échéancier & la Liste des Impayés", "FINANCE"],
    ["SEND_RELANCES", "Envoyer des Relances d'Impayés aux Parents", "FINANCE"],
    ["OUVERTURE_CAISSE", "Ouvrir une Session de Caisse", "FINANCE"],
    ["CLOTURE_CAISSE", "Clôturer & Valider la Caisse", "FINANCE"],
    ["MANAGE_CAISSE", "Gérer l'Administration des Caisses", "FINANCE"],
    ["MANAGE_PAIEMENTS", "Gérer & Valider les Règlements", "FINANCE"],
    ["MANAGE_TYPES_DEPENSE", "Créer & Modifier les Catégories de Dépense", "FINANCE"],
    ["VIEW_TYPES_DEPENSE", "Consulter les Catégories de Dépense", "FINANCE"],
    ["MANAGE_SALLES", "Créer & Modifier les Salles de Classe", "ACADEMIQUE"],
    ["VIEW_SALLES", "Consulter le Répertoire des Salles", "ACADEMIQUE"],
    ["MANAGE_UE", "Créer & Modifier les Unités d'Enseignement (UE)", "ACADEMIQUE"],
    ["VIEW_UE", "Consulter les Unités d'Enseignement (UE)", "ACADEMIQUE"],
    ["MANAGE_PARENTS", "Créer & Modifier les Profils Parents / Tuteurs", "SCOLAIRITE"],
    ["VIEW_PARENTS", "Consulter le Répertoire des Parents / Tuteurs", "SCOLAIRITE"],
    ["MANAGE_EVENTS", "Créer & Gérer les Événements de la Grande École", "COMMUNICATION"],
    ["MANAGE_GALLERY", "Gérer les Médias & Albums de la Galerie", "COMMUNICATION"],
    ["VIEW_GALLERY", "Consulter la Galerie Médias & Albums", "COMMUNICATION"],
    ["MANAGE_COMMUNICATION", "Administration du Module Communication & Alertes", "COMMUNICATION"],
    ["MANAGE_ACCOUNTS", "Administration des Comptes d'Accès Utilisateurs", "ADMINISTRATION"],
    ["VIEW_FONCTIONS", "Consulter le Répertoire des Postes & Fonctions", "ADMINISTRATION"],
    ["CONFIG_SYSTEM", "Configuration Avancée & Paramètres Système", "ADMINISTRATION"]
];

$stmtInsertPerm = $db->prepare("INSERT INTO permissions (code_permission, libelle_permission, module_permission, statut_permission, created_at_permission) VALUES (?, ?, ?, 'actif', NOW()) ON DUPLICATE KEY UPDATE libelle_permission = VALUES(libelle_permission)");

$stmtInsertRolePerm = $db->prepare("INSERT IGNORE INTO role_permissions (role_code, permission_code) VALUES (?, ?)");

foreach ($missingPerms as $p) {
    $stmtInsertPerm->execute([$p[0], $p[1], $p[2]]);
    echo "Inserted permission: {$p[0]}\n";
}

$allPermCodes = $db->query("SELECT code_permission FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
$existingRoles = $db->query("SELECT code_role FROM roles")->fetchAll(PDO::FETCH_COLUMN);

foreach ($existingRoles as $rCode) {
    if (strpos($rCode, "ADMIN") !== false || strpos($rCode, "SUPER") !== false || $rCode === "ROLE-ADMIN") {
        foreach ($allPermCodes as $pCode) {
            $stmtInsertRolePerm->execute([$rCode, $pCode]);
        }
    }
}

echo "All missing permissions added and mapped to admin roles successfully!\n";
