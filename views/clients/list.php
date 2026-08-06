<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <h1>Clients</h1>
        <button class="btn-primary" data-modal="client"><i data-lucide="plus"></i> Ajouter client</button>
      </div>

      <div class="card">
         <div class="mobile-list-container"></div>
         <div class="table-responsive-mobile">
            <table class="table" id="dataTable">
                <thead>
                <tr>
                  <th>N°</th><th>Code</th><th>Nom</th><th>Téléphone</th><th>Quartier</th><th>Statut</th><th>Actions</th>
                </tr>
              </thead>
             <tbody></tbody>
          </table>
         </div>
      </div>

      <script src="<?= RACINE ?>json/mobile-list.js"></script>
      <script src="<?= RACINE ?>json/entities/clients.js?v=3"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
