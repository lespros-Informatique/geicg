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
                <?php
                  $resolvedArtPressing = $currentPressingCode ?: ($article['pressing_code'] ?? 'PRS-001');
                  $categories = isset($categories) ? $categories : [];
                ?>
                <input type="hidden" id="pressing_code" name="pressing_code" value="<?= htmlspecialchars($resolvedArtPressing) ?>">

                <div class="form-field">
                  <label for="libelle_article">Libellé de l'article</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('file-text'); ?></span>
                    <input type="text" class="form-control" id="libelle_article" name="libelle_article"
                           value="<?= htmlspecialchars($article['libelle_article'] ?? '') ?>" required placeholder="ex: Chemise manche longue">
                  </div>
                  <div class="error-message" id="libelleError"></div>
                </div>

                <div class="form-field">
                  <label for="categorie_article_code">Catégorie</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('tag'); ?></span>
                    <select class="form-control" id="categorie_article_code" name="categorie_article_code" required>
                      <option value="">-- Choisir une catégorie --</option>
                      <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['code_categorie_article']) ?>" <?= ($article['categorie_article_code'] ?? '') === $cat['code_categorie_article'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($cat['libelle_categorie_article']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="error-message" id="categorieError"></div>
                </div>

                <div class="form-field">
                  <label for="description_article">Description</label>
                  <textarea class="form-control" id="description_article" name="description_article" rows="2" placeholder="Description ou précisions sur l'article"><?= htmlspecialchars($article['description_article'] ?? '') ?></textarea>
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

<script>
$(document).ready(function() {
  if ($.fn.select2) {
    $('#categorie_article_code').select2({
      placeholder: '-- Choisir une catégorie --',
      allowClear: true,
      width: '100%'
    });
  }

  $('.formEditArticle').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const btn = form.find('.btnEditArticle');
    const isEdit = $('#id_article').val() !== '';
    const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');
    const url = isEdit ? (baseApi + 'article/edit') : (baseApi + 'article/add');

    if (typeof loading === 'function') {
      loading(btn, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');
    }

    $.ajax({
      url: url,
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      success: function(rep) {
        if (typeof loading === 'function') {
          loading(btn, false, '<i data-lucide="save"></i> Sauvegarder');
        }
        if (rep.status) {
          if (typeof showToast === 'function') showToast(rep.message || 'Article enregistré avec succès !', 'success');
          setTimeout(function() {
            window.location.href = baseApi + 'article/list';
          }, 700);
        } else {
          if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        if (typeof loading === 'function') {
          loading(btn, false, '<i data-lucide="save"></i> Sauvegarder');
        }
        let msg = 'Erreur serveur';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        if (typeof showToast === 'function') showToast(msg, 'error');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
