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

    $recentAdminNotifs = [];
    $unreadNotifsCount = 0;
    try {
        $notifModel = new ModelNotification();
        $notifStats = $notifModel->getStats($currentPressingCode, $currentLivreurCode);
        $unreadNotifsCount = $notifStats['non_lues'] ?? 0;
        $recentAdminNotifs = $notifModel->getAllWithClient($currentPressingCode, $currentLivreurCode, 5);
    } catch (Exception $e) {
        $recentAdminNotifs = [];
        $unreadNotifsCount = 0;
    }

    $targetOneSignalCode = !empty($currentPressingCode) ? $currentPressingCode : (!empty($currentLivreurCode) ? $currentLivreurCode : ($currentUserCode ?? ''));
    ?>
    <script>
      window.hasAdminUserCode = <?= !empty($targetOneSignalCode) ? 'true' : 'false' ?>;
      window.adminUserCode = "<?= $targetOneSignalCode ?>";
      window.ONESIGNAL_APP_ID = "<?= defined('ONESIGNAL_APP_ID') ? ONESIGNAL_APP_ID : '' ?>";
      console.log('[OneSignal Admin Debug] Variables initiales:', {
        appId: window.ONESIGNAL_APP_ID,
        hasAdminUserCode: window.hasAdminUserCode,
        adminUserCode: window.adminUserCode,
        browserPermission: (typeof Notification !== 'undefined' ? Notification.permission : 'non supporte')
      });
    </script>
    <!-- OneSignal Web Push SDK -->
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
      window.OneSignalDeferred = window.OneSignalDeferred || [];
      OneSignalDeferred.push(async function(OneSignal) {
        console.log('[OneSignal Admin Debug] Execution de OneSignalDeferred...');
        try {
          const appId = window.ONESIGNAL_APP_ID;
          if (!appId) {
            console.warn('[OneSignal Admin Debug] ONESIGNAL_APP_ID est vide !');
            return;
          }
          console.log('[OneSignal Admin Debug] Appel de OneSignal.init()...');
          await OneSignal.init({
            appId: appId,
            notifyButton: { enable: false },
            allowLocalhostAsSecureOrigin: true,
            serviceWorkerParam: { scope: '/' },
            serviceWorkerPath: 'OneSignalSDKWorker.js'
          });
          console.log('[OneSignal Admin Debug] OneSignal.init() reussi !');

          if (window.hasAdminUserCode && window.adminUserCode) {
            console.log('[OneSignal Admin Debug] Connexion de l\'utilisateur OneSignal:', window.adminUserCode);
            if (typeof OneSignal.login === 'function') {
              await OneSignal.login(window.adminUserCode);
              console.log('[OneSignal Admin Debug] OneSignal.login() reussi.');
            }

            const currentPermission = typeof Notification !== 'undefined' ? Notification.permission : 'unknown';
            console.log('[OneSignal Admin Debug] Permission actuelle du navigateur:', currentPermission);

            if (currentPermission === 'default') {
              console.log('[OneSignal Admin Debug] Demande d\'autorisation Push en cours...');
              if (typeof OneSignal.Notifications !== 'undefined' && typeof OneSignal.Notifications.requestPermission === 'function') {
                await OneSignal.Notifications.requestPermission();
                console.log('[OneSignal Admin Debug] requestPermission() invoque.');
              } else if (typeof OneSignal.Slidedown !== 'undefined' && typeof OneSignal.Slidedown.promptPush === 'function') {
                await OneSignal.Slidedown.promptPush();
                console.log('[OneSignal Admin Debug] promptPush() invoque.');
              }
            } else {
              console.log('[OneSignal Admin Debug] Statut de permission du navigateur:', currentPermission);
            }
          } else {
            console.warn('[OneSignal Admin Debug] Aucun code utilisateur trouve (hasAdminUserCode=false).');
          }
        } catch (e) {
          console.error('[OneSignal Admin Debug] Erreur pendant l\'initialisation:', e);
        }
      });
    </script>
</head>
<body>
    <input type="hidden" id="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
    <div class="js-toast-container"></div>