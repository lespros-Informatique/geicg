<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1E3A5F">
    <title>LAVEX Admin - Dashboard</title>
    <link rel="stylesheet" href="<?= RACINE ?>public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <input type="hidden" id="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
    <div class="js-toast-container"></div>

    <?php
    $currentUserCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
    $currentRoles = [];
    $isSuperAdmin = false;
    $isPressing = false;
    $isLivreur = false;
    $currentPressingCode = null;
    $currentLivreurCode = null;

    if ($currentUserCode !== '') {
        try {
            $db = (new Database())->getCon();
            $stmt = $db->prepare("SELECT role_code, pressing_code FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ? AND statut_user_pressing = 'actif'");
            $stmt->execute([$currentUserCode]);
            $currentRoles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $isSuperAdmin = in_array(ROLES::SUPER_ADMIN, $currentRoles, true);
            $isPressing = in_array(ROLES::PRESSING, $currentRoles, true);
            $isLivreur = in_array(ROLES::LIVREUR, $currentRoles, true);

            if ($isPressing) {
                $stmtP = $db->prepare("SELECT pressing_code FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ? AND statut_user_pressing = 'actif' LIMIT 1");
                $stmtP->execute([$currentUserCode]);
                $currentPressingCode = $stmtP->fetchColumn() ?: null;
            }

            if ($isLivreur) {
                $stmtL = $db->prepare("SELECT l.code_livreur FROM " . TABLES::LIVREURS . " l INNER JOIN " . TABLES::USERS . " u ON l.user_code = u.code_user WHERE u.code_user = ? AND l.statut_livreur = 'actif' LIMIT 1");
                $stmtL->execute([$currentUserCode]);
                $currentLivreurCode = $stmtL->fetchColumn() ?: null;
            }
        } catch (Exception $e) {
            $currentRoles = [];
        }
    }
    ?>