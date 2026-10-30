<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#18385F">
    <title><?= htmlspecialchars(TITLE) ?></title>
    <link rel="icon" type="image/png" href="<?= RACINE ?>public/assets/images/logo/logo_eicg.jpg">
    <link rel="apple-touch-icon" href="<?= RACINE ?>public/assets/images/logo/logo_eicg.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Merriweather:wght@400;700&family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        (function() {
            try {
                var map = { navy: '#18385F', bordeaux: '#5C0808', royal: '#2563EB', emerald: '#047857' };
                var topbar = localStorage.getItem('theme_topbar') || 'light';
                var sidebar = localStorage.getItem('theme_sidebar') || 'light';
                var content = localStorage.getItem('theme_content') || 'light';
                var primary = localStorage.getItem('theme_primary') || 'navy';
                var font = localStorage.getItem('theme_font') || 'inter';
                var fontsize = localStorage.getItem('theme_fontsize') || 'normal';
                var radius = localStorage.getItem('theme_radius') || 'normal';
                
                document.documentElement.setAttribute('data-theme-topbar', topbar);
                document.documentElement.setAttribute('data-theme-sidebar', sidebar);
                document.documentElement.setAttribute('data-theme-content', content);
                document.documentElement.setAttribute('data-theme-primary', primary);
                document.documentElement.setAttribute('data-theme-font', font);
                document.documentElement.setAttribute('data-theme-fontsize', fontsize);
                document.documentElement.setAttribute('data-theme-radius', radius);

                if (map[primary]) {
                    document.documentElement.style.setProperty('--primary-color', map[primary]);
                    document.documentElement.style.setProperty('--primary', map[primary]);
                    document.documentElement.style.setProperty('--btn-primary-bg', map[primary]);
                    document.documentElement.style.setProperty('--secondary-color', map[primary]);
                    document.documentElement.style.setProperty('--sidebar-active-text', map[primary]);
                }
            } catch(e) {}
        })();
    </script>
    <link rel="stylesheet" href="<?= RACINE ?>public/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../assets/css/style.css') ?: time() ?>">
    <link rel="stylesheet" href="<?= RACINE ?>public/assets/css/geicg-components.css?v=<?= @filemtime(__DIR__ . '/../assets/css/geicg-components.css') ?: time() ?>">
    <link rel="stylesheet" href="<?= RACINE ?>public/assets/css/toastr.min.css?v=<?= @filemtime(__DIR__ . '/../assets/css/toastr.min.css') ?: time() ?>">
    <link rel="stylesheet" href="<?= RACINE ?>public/assets/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="<?= RACINE ?>public/assets/css/sidebar.css?v=<?= @filemtime(__DIR__ . '/../assets/css/sidebar.css') ?: time() ?>">

    <script src="<?= RACINE ?>public/assets/js/jquery.min.js"></script>
    <script src="<?= RACINE ?>public/assets/js/geicg-app.js?v=<?= @filemtime(__DIR__ . '/../assets/js/geicg-app.js') ?: time() ?>"></script>
    <script src="<?= RACINE ?>public/json/func.js?v=<?= @filemtime(__DIR__ . '/../../public/json/func.js') ?: time() ?>"></script>
    <script src="<?= RACINE ?>public/assets/js/toastr.min.js"></script>
    <script src="<?= RACINE ?>public/assets/js/sweetalert2.all.min.js"></script>
    <script src="<?= RACINE ?>public/assets/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Configuration & Bridge Toastr vers le modèle showToast de semestre/list
        (function() {
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    preventDuplicates: true,
                    timeOut: 4500,
                    extendedTimeOut: 1500,
                    showEasing: "swing",
                    hideEasing: "linear",
                    showMethod: "fadeIn",
                    hideMethod: "fadeOut"
                };
            }

            // Définition de window.toastr unifié avec showToast (Modèle semestre/list)
            window.toastr = window.toastr || {};
            var origSuccess = window.toastr.success;
            var origError = window.toastr.error;
            var origWarning = window.toastr.warning;
            var origInfo = window.toastr.info;

            window.toastr.success = function(msg, title) {
                if (typeof showToast === 'function') {
                    showToast(msg, 'success', title);
                } else if (typeof origSuccess === 'function') {
                    origSuccess(msg, title);
                }
            };
            window.toastr.error = function(msg, title) {
                if (typeof showToast === 'function') {
                    showToast(msg, 'error', title);
                } else if (typeof origError === 'function') {
                    origError(msg, title);
                }
            };
            window.toastr.warning = function(msg, title) {
                if (typeof showToast === 'function') {
                    showToast(msg, 'warning', title);
                } else if (typeof origWarning === 'function') {
                    origWarning(msg, title);
                }
            };
            window.toastr.info = function(msg, title) {
                if (typeof showToast === 'function') {
                    showToast(msg, 'info', title);
                } else if (typeof origInfo === 'function') {
                    origInfo(msg, title);
                }
            };
        })();
    </script>
    <style>
        /* TOASTR VISIBILITY & PREMIUM DESIGN SAFEGUARD */
        #toast-container {
            position: fixed !important;
            top: 24px !important;
            right: 24px !important;
            z-index: 99999999 !important;
            pointer-events: none !important;
            max-width: 440px !important;
            width: calc(100% - 48px) !important;
        }
        #toast-container > div,
        #toast-container > .toast {
            position: relative !important;
            pointer-events: auto !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            transform: none !important;
            opacity: 1 !important;
            visibility: visible !important;
            margin: 0 0 12px 0 !important;
            padding: 14px 18px 14px 52px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border-radius: 12px !important;
            font-family: inherit !important;
            font-size: 13.5px !important;
            font-weight: 600 !important;
            line-height: 1.45 !important;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.18), 0 4px 12px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            background-position: 16px center !important;
            background-repeat: no-repeat !important;
            background-size: 24px 24px !important;
            transition: transform 0.2s ease, opacity 0.2s ease !important;
        }
        #toast-container > .toast:hover,
        #toast-container > div:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.24), 0 6px 14px rgba(0, 0, 0, 0.12) !important;
            opacity: 1 !important;
        }
        #toast-container > .toast-success { background-color: #059669 !important; color: #FFFFFF !important; }
        #toast-container > .toast-error { background-color: #DC2626 !important; color: #FFFFFF !important; }
        #toast-container > .toast-warning { background-color: #D97706 !important; color: #FFFFFF !important; }
        #toast-container > .toast-info { background-color: #2563EB !important; color: #FFFFFF !important; }
        #toast-container .toast-message {
            color: #FFFFFF !important;
            font-size: 13.5px !important;
            font-weight: 600 !important;
            word-break: break-word !important;
            line-height: 1.45 !important;
            flex: 1 !important;
        }
        #toast-container .toast-title {
            font-size: 14px !important;
            font-weight: 800 !important;
            color: #FFFFFF !important;
            margin-bottom: 3px !important;
        }
        #toast-container .toast-close-button {
            position: relative !important;
            right: -4px !important;
            top: 0 !important;
            color: #FFFFFF !important;
            opacity: 0.85 !important;
            font-size: 22px !important;
            font-weight: 700 !important;
            background: transparent !important;
            border: none !important;
            cursor: pointer !important;
            padding: 0 4px !important;
            line-height: 1 !important;
            margin-left: 10px !important;
        }
        #toast-container .toast-close-button:hover { opacity: 1 !important; }
        /* Désactiver les flèches d'incrémentation/décrémentation sur input[type=number] */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }
        input[type="number"] {
            -moz-appearance: textfield !important;
            appearance: textfield !important;
        }

        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            min-height: 42px !important;
            border: 1px solid #CBD5E1 !important;
            border-radius: 8px !important;
            padding: 4px 10px !important;
            background-color: #FFFFFF !important;
            transition: all 0.2s ease !important;
            box-sizing: border-box !important;
        }
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default .select2-selection--multiple:focus,
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--multiple {
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
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #EFF6FF !important;
            border: 1px solid #BFDBFE !important;
            color: #1E3A5F !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            border-radius: 6px !important;
            padding: 2px 8px !important;
            margin-top: 3px !important;
            margin-right: 4px !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #1E3A5F !important;
            margin-right: 6px !important;
            border-right: 1px solid #BFDBFE !important;
            padding-right: 4px !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #EF4444 !important;
            background: transparent !important;
        }
        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
            font-size: 13px !important;
            font-family: inherit !important;
            margin-top: 4px !important;
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

        /* RÈGLE GLOBALE D'IMPRESSION : MASQUAGE STRICT NAVBAR, TOPBAR, SIDEBAR, NAV ET DROPDOWNS */
        @media print {
            header,
            header *,
            .topbar,
            .topbar *,
            aside,
            aside *,
            .sidebar,
            .sidebar *,
            nav,
            nav *,
            .main-nav,
            .main-nav *,
            .no-print,
            .no-print *,
            .dropdown-panel,
            .dropdown-panel *,
            .js-toast-container,
            .swal2-container {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                height: 0 !important;
                min-height: 0 !important;
                max-height: 0 !important;
                width: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                position: absolute !important;
                top: -9999px !important;
                left: -9999px !important;
            }
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

    if ($currentUserCode !== '') {
        try {
            $userRoles = $_SESSION[USERS_AUTH]['roles'] ?? (!empty($currentRoleCode) ? [$currentRoleCode] : []);
            if (empty($userRoles)) {
                $userRoles = (new ModelUser())->getUserRoleCodes($currentUserCode);
            }
            $currentRoles = $userRoles;
            $isSuperAdmin = in_array(ROLES::SUPER_ADMIN, $currentRoles, true) || in_array(ROLES::DIR_GENERAL, $currentRoles, true);
        } catch (\Throwable $e) {
            $currentRoles = [];
        }
    }

    $currentUserName  = $_SESSION[USERS_AUTH]['nom_user'] ?? ($_SESSION[USERS_AUTH]['nom'] ?? ($_SESSION[USERS_AUTH]['login_user'] ?? 'Utilisateur'));
    $currentUserEmail = $_SESSION[USERS_AUTH]['email_user'] ?? ($_SESSION[USERS_AUTH]['email'] ?? '');
    $currentUserPhoto = !empty($_SESSION[USERS_AUTH]['photo_user']) ? RACINE . 'public/assets/images/users/' . $_SESSION[USERS_AUTH]['photo_user'] : 'https://ui-avatars.com/api/?name=' . urlencode($currentUserName) . '&background=1E3A5F&color=fff';

    $recentAdminNotifs = [];
    $unreadNotifsCount = 0;

    ?>
</head>
<body>
    <input type="hidden" id="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
    <div class="js-toast-container"></div>