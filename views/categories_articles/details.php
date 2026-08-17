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
          <h1>Détails de la catégorie</h1>
          <p class="page-subtitle">Informations complètes</p>
        </div>
        <a href="<?= RACINE ?>categorie/list" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Retour</a>
      </div>

      <div class="detail-card">
        <div class="detail-card-header"><h2>Catégorie</h2></div>
        <div class="detail-card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Code</span>
              <span class="info-value code-badge"><?= htmlspecialchars($categorie['code_categorie_article'] ?? '') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Libellé</span>
              <span class="info-value"><?= htmlspecialchars($categorie['libelle_categorie_article'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Description</span>
              <span class="info-value"><?= htmlspecialchars($categorie['description_categorie_article'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Icône</span>
              <span class="info-value"><?= htmlspecialchars($categorie['icon_categorie_article'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Statut</span>
              <span class="info-value">
                <span class="badge-status <?= ($categorie['statut_categorie_article'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
                  <?= htmlspecialchars($categorie['statut_categorie_article'] ?? '') ?>
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
