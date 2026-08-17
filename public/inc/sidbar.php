<aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
          <div class="logo"> <?= LOGO ?> </div>
          <button class="sidebar-toggle" id="sidebarToggle">
              <i data-lucide="menu"></i>
          </button>
      </div>
      <nav class="sidebar-nav">
          <a href="<?= RACINE ?>" class="nav-item">
              <i data-lucide="layout-dashboard"></i> <span>Tableau de bord</span>
          </a>

          <?php if ($isSuperAdmin || $isPressing): ?>
          <div class="nav-section">
              <div class="nav-section-title">
                  <i data-lucide="users"></i> <span>Gestion</span>
              </div>
              <?php if ($isSuperAdmin): ?>
              <a href="<?= RACINE ?>user/list" class="nav-item sub">
                  <i data-lucide="user"></i> <span>Utilisateurs</span>
              </a>
              <?php endif; ?>
              <a href="<?= RACINE ?>client/list" class="nav-item sub">
                  <i data-lucide="contact"></i> <span>Clients</span>
              </a>
          </div>
          <?php endif; ?>

          <?php if ($isSuperAdmin || $isPressing): ?>
          <div class="nav-section">
              <div class="nav-section-title">
                  <i data-lucide="file-text"></i> <span>Catalogue</span>
              </div>
              <a href="<?= RACINE ?>article/list" class="nav-item sub">
                  <i data-lucide="file-text"></i> <span>Articles</span>
              </a>
              <a href="<?= RACINE ?>service/list" class="nav-item sub">
                  <i data-lucide="briefcase"></i> <span>Services</span>
              </a>
              <a href="<?= RACINE ?>categorie/list" class="nav-item sub">
                  <i data-lucide="tag"></i> <span>Catégories</span>
              </a>
              <a href="<?= RACINE ?>tarif/list" class="nav-item sub">
                  <i data-lucide="dollar-sign"></i> <span>Tarifs</span>
              </a>
          </div>
          <?php endif; ?>

          <?php if ($isSuperAdmin || $isPressing): ?>
          <div class="nav-section">
              <div class="nav-section-title">
                  <i data-lucide="shopping-cart"></i> <span>Commercial</span>
              </div>
              <a href="<?= RACINE ?>commande/list" class="nav-item sub">
                  <i data-lucide="clipboard-list"></i> <span>Commandes</span>
              </a>
              <a href="<?= RACINE ?>paiement/list" class="nav-item sub">
                  <i data-lucide="wallet"></i> <span>Paiements</span>
              </a>
          </div>
          <?php endif; ?>

          <div class="nav-section">
              <div class="nav-section-title">
                  <i data-lucide="truck"></i> <span>Pressing & Livraison</span>
              </div>
              <?php if ($isSuperAdmin): ?>
              <a href="<?= RACINE ?>pressing/list" class="nav-item sub">
                  <i data-lucide="map-pin"></i> <span>Pressings</span>
              </a>
              <?php endif; ?>
              <?php if ($isSuperAdmin || $isPressing): ?>
              <a href="<?= RACINE ?>livreur/list" class="nav-item sub">
                  <i data-lucide="user"></i> <span>Livreurs</span>
              </a>
              <?php endif; ?>
              <a href="<?= RACINE ?>mission/list" class="nav-item sub">
                  <i data-lucide="briefcase"></i> <span>Missions</span>
              </a>
              <?php if ($isSuperAdmin || $isPressing): ?>
              <a href="<?= RACINE ?>horaire/list" class="nav-item sub">
                  <i data-lucide="clock"></i> <span>Horaires</span>
              </a>
              <?php endif; ?>
          </div>
      </nav>
  </aside>
