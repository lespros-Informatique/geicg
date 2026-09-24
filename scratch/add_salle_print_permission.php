<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = (new Database())->getCon();
    
    // 1. Permission PRINT_SALLES
    $stmt = $db->prepare("SELECT id_permission FROM permissions WHERE code_permission = ?");
    $stmt->execute(['PRINT_SALLES']);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing) {
        $db->exec("INSERT INTO permissions (id_permission, code_permission, libelle_permission, module_permission, statut_permission, created_at_permission) 
                   VALUES (126, 'PRINT_SALLES', 'Imprimer le Répertoire des Salles de Cours', 'ACADEMIQUE', 'actif', NOW())");
        echo "Permission PRINT_SALLES created with ID 126.\n";
    } else {
        echo "Permission PRINT_SALLES already exists with ID " . $existing['id_permission'] . ".\n";
    }

    // 2. Assigner aux rôles
    $rolesToAssign = ['ROLE_SUPERADMIN', 'all_control', 'ROLE_DIR_GENERAL', 'ROLE_DIR_ETUDES', 'ROLE_SCOLARITE'];
    $stmtCheck = $db->prepare("SELECT id FROM role_permissions WHERE role_code = ? AND permission_code = ?");
    $stmtInsert = $db->prepare("INSERT INTO role_permissions (role_code, permission_code) VALUES (?, ?)");

    foreach ($rolesToAssign as $role) {
        $stmtCheck->execute([$role, 'PRINT_SALLES']);
        if (!$stmtCheck->fetch()) {
            $stmtInsert->execute([$role, 'PRINT_SALLES']);
            echo "Assigned to $role.\n";
        }
    }
    echo "DB RBAC Update DONE!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
