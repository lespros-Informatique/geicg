<?php
require_once __DIR__ . '/../../public/inc/header.php';
$pressing = isset($pressing) ? $pressing : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1>Détails du pressing</h1>
          <p class="page-subtitle">Informations complètes</p>
        </div>
        <a href="<?= RACINE ?>pressing/list" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Retour</a>
      </div>

      <div class="detail-card">
        <div class="detail-card-header"><h2>Pressing</h2></div>
        <div class="detail-card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Code</span>
              <span class="info-value code-badge"><?= htmlspecialchars($pressing['code_pressing'] ?? '') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Libellé</span>
              <span class="info-value"><?= htmlspecialchars($pressing['libelle_pressing'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Téléphone</span>
              <span class="info-value"><?= htmlspecialchars($pressing['telephone_pressing'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Email</span>
              <span class="info-value"><?= htmlspecialchars($pressing['email_pressing'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Adresse</span>
              <span class="info-value"><?= htmlspecialchars($pressing['adresse_pressing'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Statut</span>
              <span class="info-value">
                <span class="badge-status <?= ($pressing['statut_pressing'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
                  <?= htmlspecialchars($pressing['statut_pressing'] ?? '') ?>
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
