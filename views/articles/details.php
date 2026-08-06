<?php
require_once __DIR__ . '/../../public/inc/header.php';
$article = isset($article) ? $article : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1>DÃ©tails de l'article</h1>
          <p class="page-subtitle">Informations complÃ¨tes</p>
        </div>
        <a href="<?= RACINE ?>article/list" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Retour</a>
      </div>

      <div class="detail-card">
        <div class="detail-card-header"><h2>Article</h2></div>
        <div class="detail-card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Code</span>
              <span class="info-value code-badge"><?= htmlspecialchars($article['code_article'] ?? '') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">LibellÃ©</span>
              <span class="info-value"><?= htmlspecialchars($article['libelle_article'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Description</span>
              <span class="info-value"><?= htmlspecialchars($article['description_article'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Statut</span>
              <span class="info-value">
                <span class="badge-status <?= ($article['statut_article'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
                  <?= htmlspecialchars($article['statut_article'] ?? '') ?>
                </span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
