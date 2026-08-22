<?php
require_once __DIR__ . '/../../public/inc/header.php';
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="contact" style="color: #2563EB;"></i> Clients Marketplace
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">
            <?= $isSuperAdmin ? 'Consultation en lecture seule des clients inscrits sur le réseau LAVEX' : 'Gestion de vos clients et carnet d\'adresses' ?>
          </p>
        </div>

        <?php if (!$isSuperAdmin): ?>
          <button class="btn btn-primary" data-modal="client" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
            <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Ajouter client
          </button>
        <?php endif; ?>
      </div>

      <div class="card" style="border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="mobile-list-container"></div>
        <div class="table-responsive-mobile">
          <table class="table" id="dataTable" style="width: 100%;">
            <thead>
              <tr>
                <th>N°</th>
                <th>Code</th>
                <th>Nom & Prénoms</th>
                <th>Téléphone</th>
                <th>Quartier</th>
                <th>Statut</th>
                <th style="text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <script>
        window.IS_SUPER_ADMIN = <?= $isSuperAdmin ? 'true' : 'false' ?>;
      </script>
      <script src="<?= RACINE ?>public/json/mobile-list.js"></script>
      <script src="<?= RACINE ?>public/json/entities/clients.js?v=5"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
