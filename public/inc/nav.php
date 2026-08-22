<?php
  // Récupération des années académiques pour le sélecteur rapide
  $activeAnneeLibelle = $_SESSION['annee_active_libelle'] ?? '2025-2026';
?>
<?php if (!empty($_SESSION['flash_success'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof showToast === 'function') {
        showToast(<?= json_encode($_SESSION['flash_success']) ?>, 'success');
      }
    });
  </script>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof showToast === 'function') {
        showToast(<?= json_encode($_SESSION['flash_error']) ?>, 'error');
      }
    });
  </script>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<header class="topbar">
    <div class="topbar-left">
        <button class="btn-icon mobile-menu-btn" id="mobileMenuBtn" title="Menu mobile">
            <i data-lucide="menu"></i>
        </button>
        <div class="search-wrapper search-wrapper--desktop">
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" id="globalSearch" placeholder="Rechercher étudiants, inscriptions, matières, cours..." autocomplete="off">
                <button class="search-clear" id="searchClear" type="button">
                    <i data-lucide="x" style="width:14px;height:14px;"></i>
                </button>
                <span class="search-shortcut" id="searchShortcut">Ctrl K</span>
            </div>
            <div class="search-results" id="searchResults"></div>
        </div>
        <button class="btn-icon search-toggle" id="searchToggle" title="Rechercher">
            <i data-lucide="search"></i>
        </button>
        <div class="search-wrapper search-wrapper--mobile" id="searchMobile">
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" id="globalSearchMobile" placeholder="Rechercher..." autocomplete="off">
                <button class="search-clear" id="searchClearMobile" type="button">
                    <i data-lucide="x" style="width:14px;height:14px;"></i>
                </button>
            </div>
            <div class="search-results" id="searchResultsMobile"></div>
        </div>
    </div>
    <div class="topbar-actions">
        <div class="quick-actions" style="position: relative;">
            <button class="btn-icon" id="quickActionsBtn" title="Actions rapides">
                <i data-lucide="zap"></i>
            </button>
            <div class="dropdown-panel" id="quickActionsPanel">
                <div class="dropdown-header">
                    <h3>Raccourcis GEICG</h3>
                </div>
                <div class="dropdown-grid">
                    <a href="<?= RACINE ?>inscription/add" class="dropdown-card">
                        <i data-lucide="user-plus"></i>
                        <span>Nouvelle Inscription</span>
                    </a>
                    <a href="<?= RACINE ?>paiement/add" class="dropdown-card">
                        <i data-lucide="credit-card"></i>
                        <span>Nouveau Paiement</span>
                    </a>
                    <a href="<?= RACINE ?>note/add" class="dropdown-card">
                        <i data-lucide="edit-3"></i>
                        <span>Saisir Notes</span>
                    </a>
                    <a href="<?= RACINE ?>etudiant/list" class="dropdown-card">
                        <i data-lucide="users"></i>
                        <span>Étudiants</span>
                    </a>
                    <a href="<?= RACINE ?>emploi/list" class="dropdown-card">
                        <i data-lucide="calendar"></i>
                        <span>Emplois du temps</span>
                    </a>
                    <a href="<?= RACINE ?>bulletin/list" class="dropdown-card">
                        <i data-lucide="file-text"></i>
                        <span>Bulletins</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="theme-wrapper" style="position: relative;">
            <button class="btn-icon" id="themeBtn" title="Thèmes">
                <i data-lucide="palette"></i>
            </button>
            <div class="dropdown-panel" id="themePanel" style="min-width:320px; padding: 16px;">
                <div class="theme-panel-header">
                    <h3><i data-lucide="sun" style="width:16px;height:16px;"></i> Apparence</h3>
                    <button class="theme-panel-close" id="themePanelClose"><i data-lucide="x" style="width:16px;height:16px;"></i></button>
                </div>
                <div class="theme-section">
                    <div class="theme-section-label">Barre supérieure</div>
                    <div class="theme-options" id="topbarOptions">
                        <div class="theme-option active" data-value="light">
                            <div class="theme-swatch" style="background: #FFFFFF; border-color: #E5E7EB;"></div>
                            <span class="theme-option-label">Claire</span>
                        </div>
                        <div class="theme-option" data-value="dark">
                            <div class="theme-swatch" style="background: #1F2937;"></div>
                            <span class="theme-option-label">Sombre</span>
                        </div>
                    </div>
                </div>
                <div class="theme-section">
                    <div class="theme-section-label">Barre latérale</div>
                    <div class="theme-options" id="sidebarOptions">
                        <div class="theme-option active" data-value="light">
                            <div class="theme-swatch" style="background: #FFFFFF; border-color: #E5E7EB;"></div>
                            <span class="theme-option-label">Claire</span>
                        </div>
                        <div class="theme-option" data-value="dark">
                            <div class="theme-swatch" style="background: #0F172A;"></div>
                            <span class="theme-option-label">Sombre</span>
                        </div>
                    </div>
                </div>
                <div class="theme-section">
                    <div class="theme-section-label">Contenu principal</div>
                    <div class="theme-options" id="contentOptions">
                        <div class="theme-option active" data-value="light">
                            <div class="theme-swatch" style="background: #F8F9FA; border-color: #E5E7EB;"></div>
                            <span class="theme-option-label">Clair</span>
                        </div>
                        <div class="theme-option" data-value="dark">
                            <div class="theme-swatch" style="background: #111827;"></div>
                            <span class="theme-option-label">Sombre</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="notification-wrapper">
            <button class="btn-icon" id="notificationBtn" title="Notifications">
                <i data-lucide="bell"></i>
                <?php if (isset($unreadNotifsCount) && $unreadNotifsCount > 0): ?>
                    <span class="badge"><?= $unreadNotifsCount ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-panel" id="notificationPanel" style="width: 320px;">
                <div class="dropdown-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Notifications</h3>
                    <span style="font-size: 11px; color: #64748B;"><?= isset($recentAdminNotifs) ? count($recentAdminNotifs) : 0 ?> récente(s)</span>
                </div>
                <div class="notification-list">
                    <?php if (empty($recentAdminNotifs)): ?>
                        <div style="padding: 16px; text-align: center; color: #94A3B8; font-size: 13px;">
                            Aucune notification récente
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentAdminNotifs as $notif): ?>
                            <div class="notification-card" style="<?= empty($notif['lu_notification']) ? 'background: #F8FAFC;' : '' ?>">
                                <i data-lucide="bell" class="icon-primary" style="width: 18px; height: 18px;"></i>
                                <div class="notification-content">
                                    <strong><?= htmlspecialchars($notif['titre_notification'] ?? 'Notification') ?></strong>
                                    <p style="margin: 2px 0 0; font-size: 12px; color: #475569;"><?= htmlspecialchars($notif['message_notification'] ?? '') ?></p>
                                    <small style="color: #94A3B8; font-size: 10px;"><?= !empty($notif['created_at_notification']) ? date('d/m H:i', strtotime($notif['created_at_notification'])) : '' ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="admin-profile" id="profileBtn">
            <span class="avatar-circle" style="width: 32px; height: 32px; border-radius: 50%; background: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                <?= strtoupper(substr($currentUserName ?? 'A', 0, 1)) ?>
            </span>
            <span><?= htmlspecialchars($currentUserName ?? 'Utilisateur') ?> <i data-lucide="chevron-down"></i></span>
            <div class="dropdown-panel" id="profilePanel">
                <div class="profile-header">
                    <div>
                        <strong><?= htmlspecialchars($currentUserName ?? 'Utilisateur') ?></strong>
                        <small style="color: #64748B; display: block;"><?= htmlspecialchars($currentUserEmail ?? '') ?></small>
                    </div>
                </div>
                <a href="<?= RACINE ?>user/profil" class="dropdown-item"><i data-lucide="user"></i> Mon profil</a>
                <hr>
                <a href="<?= RACINE ?>user/decon" class="dropdown-item logout"><i data-lucide="log-out"></i> Déconnexion</a>
            </div>
        </div>
    </div>
</header>