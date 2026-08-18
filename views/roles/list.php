<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="shield" style="color: #2563EB;"></i> Rôles & Niveaux d'Accès
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">Gestion des profils d'utilisateurs et des privilèges attribués</p>
        </div>
      </div>

      <div class="card" style="border-radius: 14px; padding: 20px;">
        <div class="mobile-list-container"></div>
        <div class="table-responsive-mobile">
          <table class="table" id="dataTable" style="width: 100%;">
            <thead>
              <tr>
                <th>N°</th>
                <th>Code</th>
                <th>Libellé Rôle</th>
                <th>Description</th>
                <th>Permissions</th>
                <th>Statut</th>
                <th style="text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <script src="<?= RACINE ?>json/mobile-list.js"></script>
      <script src="<?= RACINE ?>json/entities/roles.js?v=2"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
