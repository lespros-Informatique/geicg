<?php
  $currentPressingCodeNav = $_SESSION['CURRENT_PRESSING_CODE'] ?? null;
  $quotaWarning = null;

  if ($currentPressingCodeNav) {
      try {
          $dbNav = (new Database())->getCon();
          $stmtNavQ = $dbNav->prepare("
              SELECT f.code_forfait, f.libelle_forfait, f.limite_pressings_forfait
              FROM abonnements_pressings ab
              JOIN forfaits f ON ab.forfait_code = f.code_forfait
              WHERE ab.pressing_code = ? AND ab.statut_abonnement_pressing = 'actif'
              ORDER BY ab.id_abonnement_pressing DESC LIMIT 1
          ");
          $stmtNavQ->execute([$currentPressingCodeNav]);
          $subNav = $stmtNavQ->fetch(PDO::FETCH_ASSOC);

          if ($subNav && (int)$subNav['limite_pressings_forfait'] > 0) {
              $limitNav = (int)$subNav['limite_pressings_forfait'];
              $stmtNavCount = $dbNav->prepare("
                  SELECT COUNT(*) FROM commandes 
                  WHERE pressing_code = ? 
                  AND MONTH(created_at_commande) = MONTH(CURRENT_DATE())
                  AND YEAR(created_at_commande) = YEAR(CURRENT_DATE())
              ");
              $stmtNavCount->execute([$currentPressingCodeNav]);
              $countNav = (int)$stmtNavCount->fetchColumn();
              $remainingNav = max(0, $limitNav - $countNav);

              if ($remainingNav <= 10) {
                  $quotaWarning = [
                      'limit' => $limitNav,
                      'count' => $countNav,
                      'remaining' => $remainingNav,
                      'forfait' => $subNav['libelle_forfait']
                  ];
              }
          }
      } catch (Exception $e) {}
  }
?>

<header class="topbar">
    <div class="topbar-left">
        <button class="btn-icon mobile-menu-btn" id="mobileMenuBtn" style="display:none;">
            <i data-lucide="menu"></i>
        </button>
        <div class="search-wrapper search-wrapper--desktop">
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" id="globalSearch" placeholder="Rechercher commandes, clients, produits..." autocomplete="off">
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
        <div class="quick-actions">
            <button class="btn-icon" id="quickActionsBtn" title="Actions rapides">
                <i data-lucide="zap"></i>
            </button>
            <div class="dropdown-panel" id="quickActionsPanel">
                <div class="dropdown-header">
                    <h3>Actions rapides</h3>
                </div>
                <div class="dropdown-grid">
                    <?php if (isset($isSuperAdmin) && $isSuperAdmin): ?>
                        <a href="<?= RACINE ?>pressing/list" class="dropdown-card">
                            <i data-lucide="store"></i>
                            <span>Pressings</span>
                        </a>
                        <a href="<?= RACINE ?>abonnement/list" class="dropdown-card">
                            <i data-lucide="credit-card"></i>
                            <span>Abonnements</span>
                        </a>
                        <a href="<?= RACINE ?>user/list" class="dropdown-card">
                            <i data-lucide="users"></i>
                            <span>Utilisateurs</span>
                        </a>
                    <?php elseif (isset($isLivreur) && $isLivreur): ?>
                        <a href="<?= RACINE ?>mission/list" class="dropdown-card">
                            <i data-lucide="truck"></i>
                            <span>Mes Missions</span>
                        </a>
                        <a href="<?= RACINE ?>mission/carte" class="dropdown-card">
                            <i data-lucide="map"></i>
                            <span>Carte Tournées</span>
                        </a>
                        <a href="<?= RACINE ?>notification/list" class="dropdown-card">
                            <i data-lucide="bell"></i>
                            <span>Notifications</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= RACINE ?>commande/list" class="dropdown-card">
                            <i data-lucide="shopping-cart"></i>
                            <span>Commandes</span>
                        </a>
                        <a href="<?= RACINE ?>client/list" class="dropdown-card">
                            <i data-lucide="users"></i>
                            <span>Clients</span>
                        </a>
                        <a href="<?= RACINE ?>tarif/list" class="dropdown-card">
                            <i data-lucide="tag"></i>
                            <span>Tarifs</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
                <div class="dropdown-footer">
                    <a href="<?= RACINE ?>notification/list">Toutes les notifications</a>
                </div>
            </div>
        </div>
        <div class="admin-profile" id="profileBtn">
            <img src="<?= htmlspecialchars($currentUserPhoto ?? '') ?>" alt="<?= htmlspecialchars($currentUserName ?? 'Admin') ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
            <span><?= htmlspecialchars($currentUserName ?? 'Admin') ?> <i data-lucide="chevron-down"></i></span>
            <div class="dropdown-panel" id="profilePanel">
                <div class="profile-header">
                    <img src="<?= htmlspecialchars($currentUserPhoto ?? '') ?>" alt="<?= htmlspecialchars($currentUserName ?? 'Admin') ?>" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <strong><?= htmlspecialchars($currentUserName ?? 'Admin') ?></strong>
                        <small style="color: #64748B; display: block;"><?= htmlspecialchars($currentUserEmail ?? '') ?></small>
                    </div>
                </div>
                <a href="<?= RACINE ?>user/profil" class="dropdown-item"><i data-lucide="user"></i> Mon profil</a>
                <?php if (!empty($isPressing)): ?>
                    <a href="<?= RACINE ?>pressing/config" class="dropdown-item"><i data-lucide="store"></i> Mon Pressing</a>
                <?php endif; ?>
                <hr>
                <a href="<?= RACINE ?>user/decon" class="dropdown-item logout"><i data-lucide="log-out"></i> Déconnexion</a>
            </div>
        </div>
    </div>
</header>

<?php if (!empty($isPressing) && empty($isSubscriptionActive)): ?>
<div class="subscription-inactive-banner" style="background: #FEF2F2; border-bottom: 2px solid #EF4444; color: #991B1B; padding: 10px 24px; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: space-between; gap: 14px; box-shadow: 0 2px 6px rgba(239,68,68,0.1); flex-wrap: wrap;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <i data-lucide="alert-triangle" style="width: 20px; height: 20px; color: #DC2626; flex-shrink: 0;"></i>
        <span>
            <strong>Compte Pressing Inactif / Suspendu :</strong> Vous n'avez aucun abonnement actif ou votre période a expiré. Les actions opérationnelles (traitement de commandes, gestion des tarifs et catalogue, livreurs) sont temporairement verrouillées.
        </span>
    </div>
    <a href="<?= RACINE ?>abonnement/list" class="btn btn-sm btn-primary" style="background: #DC2626; border-color: #DC2626; color: #FFF; white-space: nowrap; font-weight: 700; padding: 6px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i data-lucide="credit-card" style="width: 14px; height: 14px;"></i> Souscrire / Renouveler
    </a>
</div>
<?php endif; ?>

<?php if (!empty($quotaWarning)): ?>
<div class="quota-warning-banner" style="background: <?= $quotaWarning['remaining'] === 0 ? '#FEF2F2' : '#FFFBEB' ?>; border-bottom: 2px solid <?= $quotaWarning['remaining'] === 0 ? '#EF4444' : '#F59E0B' ?>; color: <?= $quotaWarning['remaining'] === 0 ? '#991B1B' : '#92400E' ?>; padding: 10px 24px; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: space-between; gap: 14px; box-shadow: 0 2px 6px rgba(245,158,11,0.1); flex-wrap: wrap;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <i data-lucide="<?= $quotaWarning['remaining'] === 0 ? 'eye-off' : 'alert-triangle' ?>" style="width: 20px; height: 20px; color: <?= $quotaWarning['remaining'] === 0 ? '#DC2626' : '#D97706' ?>; flex-shrink: 0;"></i>
        <span>
            <?php if ($quotaWarning['remaining'] === 0): ?>
                <strong>Quota Mensuel Atteint (<?= $quotaWarning['count'] ?>/<?= $quotaWarning['limit'] ?>) :</strong> Votre pressing est temporairement masqué des recherches Lavex jusqu'au mois prochain. Passez à la formule supérieure pour réapparaître immédiatement !
            <?php else: ?>
                <strong>Alerte Quota (<?= $quotaWarning['count'] ?>/<?= $quotaWarning['limit'] ?>) :</strong> Il ne vous reste plus que <strong><?= $quotaWarning['remaining'] ?> commande(s)</strong> disponible(s) ce mois-ci sur votre <?= htmlspecialchars($quotaWarning['forfait']) ?>.
            <?php endif; ?>
        </span>
    </div>
    <a href="<?= RACINE ?>abonnement/list" class="btn btn-sm btn-primary" style="background: #2563EB; border-color: #2563EB; color: #FFF; white-space: nowrap; font-weight: 700; padding: 6px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i> Passer au Forfait Supérieur
    </a>
</div>
<?php endif; ?>