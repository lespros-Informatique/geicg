<?php
require_once __DIR__ . '/../../public/inc/header.php';
// var_dump(RACINE . 'public/assets/images/articles/');
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
          <h1>Articles</h1>
        <a href="<?= RACINE ?>article/formulaire" class="btn btn-primary"><i data-lucide="plus"></i> Ajouter article</a>
      </div>

      <div class="card">
         <div class="mobile-list-container"></div>
         <div class="table-responsive-mobile">
             <table class="table" id="dataTable">
                <thead>
                 <tr>
                   <th>N°</th><th>Code</th><th>Libellé</th><th>Catégorie</th><th>Pressing</th><th>Statut</th><th>Actions</th>
                 </tr>
              </thead>
              <tbody></tbody>
           </table>
         </div>
      </div>

      <script src="<?= RACINE ?>json/mobile-list.js"></script>
      <script src="<?= RACINE ?>json/entities/articles.js?v=4"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
