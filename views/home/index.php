<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <h1 class="page-title">Tableau de Bord</h1>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon users">
            <i data-lucide="users"></i>
          </div>
          <div class="stat-info">
            <h3>Utilisateurs</h3>
            <p class="stat-value" data-stat="users">0</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon clients">
            <i data-lucide="contact"></i>
          </div>
          <div class="stat-info">
            <h3>Clients</h3>
            <p class="stat-value" data-stat="clients">0</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon products">
            <i data-lucide="file-text"></i>
          </div>
          <div class="stat-info">
            <h3>Articles</h3>
            <p class="stat-value" data-stat="articles">0</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon delivered">
            <i data-lucide="clipboard-list"></i>
          </div>
          <div class="stat-info">
            <h3>Commandes</h3>
            <p class="stat-value" data-stat="commandes">0</p>
          </div>
        </div>
      </div>

      <div class="recent-orders">
        <div class="card">
          <h2>Commandes récentes</h2>
          <div class="mobile-list-container" id="recentOrdersMobile"></div>
          <div class="table-responsive-mobile">
            <table class="table" id="recentOrdersTable">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Client</th>
                  <th>Montant</th>
                  <th>Statut</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="<?= RACINE ?>json/mobile-list.js"></script>
<script src="<?= RACINE ?>json/dashboard.js"></script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
