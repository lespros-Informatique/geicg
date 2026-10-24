<?php
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = (new Database())->getCon();

    // 1. Vérifier si VIEW_CAISSE_ANNEXE existe déjà
    $stmt = $db->prepare("SELECT id_permission FROM permissions WHERE code_permission = ?");
    $stmt->execute(['VIEW_CAISSE_ANNEXE']);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        $stmtIns = $db->prepare("
            INSERT INTO permissions (code_permission, libelle_permission, module_permission, statut_permission, created_at_permission)
            VALUES (?, ?, ?, 'actif', NOW())
        ");
        $stmtIns->execute([
            'VIEW_CAISSE_ANNEXE',
            'Consulter la Caisse Annexe & Bilan des Frais Annexes',
            'FINANCE'
        ]);
        $permId = $db->lastInsertId();
    } else {
        $permId = $existing['id_permission'];
    }

    // 2. Vérifier si MANAGE_CAISSE_ANNEXE existe déjà
    $stmtM = $db->prepare("SELECT id_permission FROM permissions WHERE code_permission = ?");
    $stmtM->execute(['MANAGE_CAISSE_ANNEXE']);
    $existingM = $stmtM->fetch(PDO::FETCH_ASSOC);

    if (!$existingM) {
        $stmtInsM = $db->prepare("
            INSERT INTO permissions (code_permission, libelle_permission, module_permission, statut_permission, created_at_permission)
            VALUES (?, ?, ?, 'actif', NOW())
        ");
        $stmtInsM->execute([
            'MANAGE_CAISSE_ANNEXE',
            'Gérer les Encaissements de la Caisse Annexe',
            'FINANCE'
        ]);
        $permIdM = $db->lastInsertId();
    } else {
        $permIdM = $existingM['id_permission'];
    }

    // 3. Attribuer aux rôles autorisés
    $rolesToAssign = [
        'ROLE_SUPERADMIN',
        'all_control',
        'ROLE_DIR_GENERAL',
        'ROLE_COMPTABLE',
        'ROLE_CAISSIER'
    ];

    $assigned = [];
    foreach (['VIEW_CAISSE_ANNEXE', 'MANAGE_CAISSE_ANNEXE'] as $permCode) {
        foreach ($rolesToAssign as $role) {
            $check = $db->prepare("SELECT id FROM role_permissions WHERE role_code = ? AND permission_code = ?");
            $check->execute([$role, $permCode]);
            if (!$check->fetch()) {
                $insRp = $db->prepare("INSERT INTO role_permissions (role_code, permission_code) VALUES (?, ?)");
                $insRp->execute([$role, $permCode]);
                $assigned[] = "{$role} -> {$permCode}";
            }
        }
    }

    echo json_encode([
        'status' => 1,
        'message' => 'Permissions synchronisées avec succès en base de données',
        'perm_view_id' => $permId,
        'perm_manage_id' => $permIdM ?? null,
        'assigned' => $assigned
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'status' => 0,
        'error' => $e->getMessage()
    ]);
}
