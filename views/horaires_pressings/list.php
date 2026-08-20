<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
          <h1>Horaires pressings</h1>
        <a href="<?= RACINE ?>horaire/formulaire" class="btn btn-primary"><i data-lucide="plus"></i> Ajouter horaire</a>
      </div>

      <div class="card">
         <div class="mobile-list-container"></div>
          <div class="table-responsive-mobile">
              <table class="table" id="dataTable" data-superadmin="<?= !empty($isSuperAdmin) ? '1' : '0' ?>">
                  <thead>
                   <tr>
                     <th>N°</th>
                     <?php if (!empty($isSuperAdmin)): ?>
                       <th>Pressing</th>
                     <?php endif; ?>
                     <th>Jour</th>
                     <th>Ouverture</th>
                     <th>Fermeture</th>
                     <th>Statut</th>
                     <th>Actions</th>
                   </tr>
                </thead>
                <tbody></tbody>
             </table>
           </div>
       </div>

      <script src="<?= RACINE ?>json/mobile-list.js"></script>
      <script src="<?= RACINE ?>json/entities/horaires.js?v=5"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
