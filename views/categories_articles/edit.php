<?php
require_once __DIR__ . '/../../public/inc/header.php';
$categorie = isset($categorie) ? $categorie : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($categorie['id_categorie_article']) ? 'Modifier la catégorie' : 'Ajouter une catégorie' ?></h1>
          <p class="page-subtitle">Gestion des catégories</p>
        </div>
        <a href="<?= RACINE ?>categorie/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations de la catégorie</h2>
          </div>
          <?php if (isset($categorie['statut_categorie_article'])): ?>
            <span class="badge-status <?= $categorie['statut_categorie_article'] == 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= $categorie['statut_categorie_article'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditCategorie">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_categorie_article" name="id_categorie_article" value="<?= htmlspecialchars($categorie['id_categorie_article'] ?? '') ?>">

             <div class="form-grid">
               <div class="form-field">
                 <label for="libelle_categorie_article">Libellé</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('file-text'); ?></span>
                   <input type="text" class="form-control" id="libelle_categorie_article" name="libelle_categorie_article"
                          value="<?= htmlspecialchars($categorie['libelle_categorie_article'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="libelleError"></div>
               </div>

               <div class="form-field">
                 <label for="description_categorie_article">Description</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('file-text'); ?></span>
                   <textarea class="form-control" id="description_categorie_article" name="description_categorie_article"><?= htmlspecialchars($categorie['description_categorie_article'] ?? '') ?></textarea>
                 </div>
                 <div class="error-message" id="descriptionError"></div>
               </div>

               <div class="form-field">
                 <label for="icon_categorie_article">Icône</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('image'); ?></span>
                   <input type="text" class="form-control" id="icon_categorie_article" name="icon_categorie_article"
                          value="<?= htmlspecialchars($categorie['icon_categorie_article'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="iconError"></div>
               </div>

               <?php if (isset($categorie['statut_categorie_article'])): ?>
               <div class="form-field">
                 <label for="actif">Statut</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="actif" name="actif">
                     <option value="1" <?= ($categorie['statut_categorie_article'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                     <option value="0" <?= ($categorie['statut_categorie_article'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                   </select>
                 </div>
                 <div class="error-message" id="actifError"></div>
               </div>
               <?php endif; ?>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditCategorie">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>categorie/list" class="btn btn-secondary">
                <i data-lucide="x"></i>
                Annuler
              </a>
            </div>
          </form>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="<?= RACINE ?>json/entities/categories.js?v=4"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
