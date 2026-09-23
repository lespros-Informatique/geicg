<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = (new Database())->getCon();
    
    // Vérifier si la permission existe déjà
    $stmt = $db->prepare("SELECT id_permission FROM permissions WHERE code_permission = ?");
    $stmt->execute(['PRINT_FILIERES']);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing) {
        $db->exec("INSERT INTO permissions (id_permission, code_permission, libelle_permission, module_permission, statut_permission, created_at_permission) 
                   VALUES (125, 'PRINT_FILIERES', 'Imprimer le Catalogue des Filières de Formation', 'ACADEMIQUE', 'actif', NOW())");
        echo "Permission PRINT_FILIERES created with ID 125.\n";
    } else {
        echo "Permission PRINT_FILIERES already exists with ID " . $existing['id_permission'] . ".\n";
    }

    // Assigner aux rôles
    $rolesToAssign = ['ROLE_SUPERADMIN', 'all_control', 'ROLE_DIR_GENERAL', 'ROLE_DIR_ETUDES', 'ROLE_SCOLARITE'];
    $stmtCheck = $db->prepare("SELECT id FROM role_permissions WHERE role_code = ? AND permission_code = ?");
    $stmtInsert = $db->prepare("INSERT INTO role_permissions (role_code, permission_code) VALUES (?, ?)");

    foreach ($rolesToAssign as $role) {
        $stmtCheck->execute([$role, 'PRINT_FILIERES']);
        if (!$stmtCheck->fetch()) {
            $stmtInsert->execute([$role, 'PRINT_FILIERES']);
            echo "Assigned to $role.\n";
        }
    }
    echo "DONE!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
