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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            border: 1px solid #CBD5E1 !important;
            border-radius: 8px !important;
            padding: 6px 12px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #FFFFFF !important;
            transition: all 0.2s ease !important;
        }
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #1E3A5F !important;
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.12) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1E293B !important;
            font-size: 14px !important;
            padding-left: 0 !important;
            line-height: 28px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }
        .select2-dropdown {
            border: 1px solid #CBD5E1 !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            z-index: 100000 !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #E2E8F0 !important;
            border-radius: 6px !important;
            padding: 8px 12px !important;
            font-size: 13px !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #1E3A5F !important;
            color: #FFFFFF !important;
        }
        .select2-container--default .select2-results__option--selected {
            background-color: #EFF6FF !important;
            color: #1E3A5F !important;
            font-weight: 600 !important;
        }
    </style>
    <script>
        window.RACINE = '<?= RACINE ?>';
        window.LINK = '<?= RACINE ?>';
    </script>
</head>
<body>
    <input type="hidden" id="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
    <div class="js-toast-container"></div>

    <?php
    $currentUserCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';
    $currentRoleCode = $_SESSION[USERS_AUTH]['role_code'] ?? '';
    $currentRoles = [];
    $isSuperAdmin = false;
    $isPressing = false;
    $isLivreur = false;
    $currentPressingCode = null;
    $currentLivreurCode = null;

    if ($currentUserCode !== '') {
        try {
            $db = (new Database())->getCon();

            if ($currentRoleCode === '') {
                $stmtR = $db->prepare("SELECT role_code FROM " . TABLES::USERS . " WHERE code_user = ? LIMIT 1");
                $stmtR->execute([$currentUserCode]);
                $currentRoleCode = $stmtR->fetchColumn() ?: '';
            }

            if ($currentRoleCode !== '') {
                $currentRoles[] = $currentRoleCode;
            }

            $isSuperAdmin = in_array(ROLES::SUPER_ADMIN, $currentRoles, true);
            $isPressing = in_array(ROLES::PRESSING, $currentRoles, true);
            $isLivreur = in_array(ROLES::LIVREUR, $currentRoles, true);

            if ($isPressing) {
                $stmtP = $db->prepare("SELECT pressing_code FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ? AND statut_user_pressing = 'actif' LIMIT 1");
                $stmtP->execute([$currentUserCode]);
                $currentPressingCode = $stmtP->fetchColumn() ?: null;
            }

            if ($isLivreur) {
                $stmtL = $db->prepare("SELECT l.code_livreur FROM " . TABLES::LIVREURS . " l WHERE l.user_code = ? AND l.statut_livreur = 'actif' LIMIT 1");
                $stmtL->execute([$currentUserCode]);
                $currentLivreurCode = $stmtL->fetchColumn() ?: null;
            }
        } catch (Exception $e) {
            $currentRoles = [];
        }
    }

    $currentUserName  = $_SESSION[USERS_AUTH]['nom_user'] ?? ($_SESSION[USERS_AUTH]['nom'] ?? ($_SESSION[USERS_AUTH]['login_user'] ?? 'Utilisateur'));
    $currentUserEmail = $_SESSION[USERS_AUTH]['email_user'] ?? ($_SESSION[USERS_AUTH]['email'] ?? '');
    $currentUserPhoto = !empty($_SESSION[USERS_AUTH]['photo_user']) ? RACINE . 'public/assets/images/users/' . $_SESSION[USERS_AUTH]['photo_user'] : 'https://ui-avatars.com/api/?name=' . urlencode($currentUserName) . '&background=1E3A5F&color=fff';

    // Notifications récentes
    $recentAdminNotifs = [];
    $unreadNotifsCount = 0;
    try {
        if (isset($db)) {
            $stmtN = $db->query("SELECT * FROM " . TABLES::NOTIFICATIONS . " ORDER BY id_notification DESC LIMIT 5");
            $recentAdminNotifs = $stmtN->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $unreadNotifsCount = (int)$db->query("SELECT COUNT(*) FROM " . TABLES::NOTIFICATIONS . " WHERE lu_notification = 0")->fetchColumn();
        }
    } catch (Exception $e) {
        $recentAdminNotifs = [];
    }
    ?>