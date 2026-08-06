<?php
require_once __DIR__ . '/../../public/inc/header.php';
$article = isset($article) ? $article : [];
$csrfToken = Validator::generateCsrfToken();
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($article['id_article']) ? 'Modifier l\'article' : 'Ajouter un article' ?></h1>
          <p class="page-subtitle">Gestion des articles</p>
        </div>
        <a href="<?= RACINE ?>article/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour Ã  la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations de l'article</h2>
          </div>
          <?php if (isset($article['statut_article'])): ?>
            <span class="badge-status <?= $article['statut_article'] == 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= $article['statut_article'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditArticle">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_article" name="id_article" value="<?= htmlspecialchars($article['id_article'] ?? '') ?>">

             <div class="form-grid">
               <div class="form-field">
                 <label for="libelle_article">LibellÃ©</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('file-text'); ?></span>
                   <input type="text" class="form-control" id="libelle_article" name="libelle_article"
                          value="<?= htmlspecialchars($article['libelle_article'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="libelleError"></div>
               </div>

               <div class="form-field">
                 <label for="pressing_code">Pressing</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                   <input type="text" class="form-control" id="pressing_code" name="pressing_code"
                          value="<?= htmlspecialchars($article['pressing_code'] ?? 'PRS-001') ?>">
                 </div>
                 <div class="error-message" id="pressingError"></div>
               </div>

               <div class="form-field">
                 <label for="categorie_article_code">CatÃ©gorie</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('tag'); ?></span>
                   <input type="text" class="form-control" id="categorie_article_code" name="categorie_article_code"
                          value="<?= htmlspecialchars($article['categorie_article_code'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="categorieError"></div>
               </div>

               <div class="form-field">
                 <label for="description_article">Description</label>
                 <textarea class="form-control" id="description_article" name="description_article"><?= htmlspecialchars($article['description_article'] ?? '') ?></textarea>
                 <div class="error-message" id="descriptionError"></div>
               </div>

               <?php if (isset($article['statut_article'])): ?>
               <div class="form-field">
                 <label for="actif">Statut</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="actif" name="actif">
                     <option value="1" <?= ($article['statut_article'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                     <option value="0" <?= ($article['statut_article'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                   </select>
                 </div>
                 <div class="error-message" id="actifError"></div>
               </div>
               <?php endif; ?>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditArticle">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>article/list" class="btn btn-secondary">
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

<script src="<?= RACINE ?>json/entities/articles.js?v=4"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
