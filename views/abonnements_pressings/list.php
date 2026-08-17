<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <h1 class="page-title">Abonnements Pressings</h1>
      <div class="card">
        <div class="table-responsive-mobile">
          <table class="table" id="dataTable">
            <thead>
              <tr>
                <th>Code</th>
                <th>Pressing</th>
                <th>Forfait</th>
                <th>Montant</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <script src="<?= RACINE ?>json/mobile-list.js"></script>
      <script src="<?= RACINE ?>json/entities/abonnements_pressings.js?v=1"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
