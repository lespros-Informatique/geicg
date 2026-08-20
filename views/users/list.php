<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <h1>Utilisateurs</h1>
        <button class="btn-primary" data-modal="utilisateur"><i data-lucide="plus"></i> Ajouter utilisateur</button>
      </div>

      <div class="card">
         <div class="mobile-list-container"></div>
         <div class="table-responsive-mobile">
            <table class="table" id="dataTable">
               <thead>
               <tr>
                 <th>N°</th><th>Code</th><th>Nom</th><th>Prénom</th><th>Téléphone</th><th>Rôle</th><th>Statut</th><th>Actions</th>
               </tr>
             </thead>
             <tbody></tbody>
          </table>
         </div>
      </div>

      <script src="<?= RACINE ?>json/mobile-list.js"></script>
      <script src="<?= RACINE ?>json/entities/users.js?v=<?= time() ?>"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>